<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\Email;
use App\Models\Charge;
use App\Models\Saving;
use App\Models\Setting;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\GeneralLedger;
use App\Http\Traites\UserTraite;
use App\Http\Traites\AuditTraite;
use App\Http\Traites\SavingTraite;
use App\Models\SavingsTransaction;
use Illuminate\Support\Facades\DB;
use App\Models\NotificationPayload;
use App\Http\Traites\TransferTraite;
use App\Models\SavingsTransactionGL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Cache\LockTimeoutException;

class TransactionsController extends Controller
{
     use SavingTraite;
    use AuditTraite;
    use UserTraite;
    use TransferTraite;
    private $murl, $mapikey,$msercetkey,$macctno,$url,$apikey;
    
    public function __construct(){
        
        $this->middleware('auth'); 

        if(env('APP_MODE') == "test"){
            $this->murl = env('MONNIFY_SANDBOX_URL');
            $this->mapikey = env('MONNIFY_SANDBOX_API_KEY');
            $this->msercetkey = env('MONNIFY_SANDBOX_SECRET_KEY');
            $this->macctno = env('MONNIFY_SANDBOX_ACCOUNT_NUMBER');
        }else{
            $this->murl = env('MONNIFY_LIVE_URL');
            $this->mapikey = env('MONNIFY_LIVE_API_KEY');
            $this->msercetkey = env('MONNIFY_LIVE_SECRET_KEY');
            $this->macctno = env('MONNIFY_LIVE_ACCOUNT_NUMBER');
              $this->url = env('WIRELESS_URL');
            $this->apikey = env('WIRELESS_API_KEY');
        }
    }

    public function bank_transactions(){
        return view('deposit.bank_transfer')->with('banks',Bank::orderBy('bank_name','ASC')->get());
    }

    public function VerifyBankAccount(Request $r)
    {
        $lock= Cache::lock('vrrybnkacc-'.mt_rand('1111','9999'),5);
             
             if($lock->get()){
         $this->logInfo("validating Bank Account",$r->all());

        $response =  Http::get($this->murl."v1/disbursements/account/validate?accountNumber=".$r->account_number."&bankCode=".$r->bank_code);

//return $response;
        if($response["responseCode"] == '0'){
            $accountName = explode(" ",$response["responseBody"]["accountName"]);

            $firstName = count($accountName) < 3 ? $accountName[1] : $accountName[2]." ".$accountName[1];
           $lastName =  $accountName[0];

             $vdata = [
                "first_name" => $firstName,
                "last_name" => $lastName,
                "bankCode" => $r->bank_code
             ];
             
             $this->logInfo("Bank Account verified", $vdata);
              
            return response()->json(['status' => '1','msg' => 'Bank Account Verified Successfully','data' => $vdata]);
        }else{
            return response()->json(['status' => '0','msg' => 'Failed to Verify Bank Account','data' => []]);
        }
        
        $lock->release();
         }//lock
    }

   public function transferToBankAccount(Request $r){
       
        $lock = Cache::lock('trnbnkacct-'.mt_rand('1111','9999'),5);
            
            if($lock->get()){
                
                DB::beginTransaction();
                
        $this->logInfo("bank transfer via bankTranfer",$r->all());

        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;
        

        $validation = Validator::make($r->all(), [
            "amount" => "required|numeric|gt:0|max:1000000",
            "destination_account" => "required",
            "receipient_name" => "required|string",
            "bank_code" => "required",
        ]);

        if ($validation->fails()) {
            $ra = array("status" => false, "message" => $validation->errors()->all()[0]);
            return response()->json($ra, 406);
        }
        
        $trxref = $this->generatetrnxref('bk');

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails('1','1',$usern,'customer','Transfer via BankTransfer');

        $getsetvalue = new Setting();
        
        
        $dacct = $r->receipient_name.','.$r->destination_account.','.$r->bank_code;

        $cust = Customer::where('id',$r->customerid)->first();

        $desc = empty($r->description) ? "From ".$cust->first_name." ".$cust->last_name : $r->description;
      
        $tcharge = Charge::select('amount')->where('id',$getsetvalue->getsettingskey('transfer_charge'))->first();
        $ocharge = Charge::select('amount')->where('id',$getsetvalue->getsettingskey('othercharges'))->first();
        $bankcharger = $getsetvalue->getsettingskey('bankcharge');
        
        $charge = $tcharge->amount + $bankcharger + $ocharge->amount;
        $totalAmount = $r->amount + $charge;
        $tchargeamt = $tcharge->amount + $bankcharger;

        $compbal = $this->validatecompanybalance($totalAmount,'combal');
        if($compbal["status"] == false){
    
            $this->logInfo("validating company balance",$compbal);
        
        return response()->json($compbal,406);
    }

        //initiate transaction
        $this->create_saving_transaction(Auth::user()->id,$r->customerid,$branch,$r->amount,
        'debit','core','0',$r->slipno,null,$getsetvalue->getsettingskey('payoption'),$dacct,$trxref,$desc,'pending','2','trnsfer',$usern);
       

        $chkcres = $this->checkCustomerRestriction($r->customerid);
        if($chkcres == true){
    
            $this->tracktrails('1','1',$usern,'customer','Account Restricted');
            
            $this->logInfo("","Customer Account Restricted");
            
            return ['status' => '0', 'msg' => 'Your Account Has Been Restricted. Please contact support'];
        }

        $chklien = $this->checkCustomerLienStatus($r->customerid);
            if($chklien['status'] == true && $chklien['lien'] == 2){
                
                $this->tracktrails('1','1',$usern,'customer','Account has been lien');
                
                $this->logInfo("Account lien",$chklien);
                
             return ['status' => '0', 'msg' => 'Your Account Has Been Lien('.$chklien['messages'].')...please contact support'];
            }
            
        $validateuserbalance = $this->validatecustomerbalance($r->customerid,$totalAmount);
        if($validateuserbalance["status"] == false){

            $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
            
            $this->logInfo("customer balance",$validateuserbalance);
            
            return ['status' => '0', 'msg' => $validateuserbalance["message"]];
        }

        if($getsetvalue->getsettingskey('withdrawal_limit') == 0 || $r->amount < $getsetvalue->getsettingskey('withdrawal_limit')){

            $transaction = SavingsTransaction::where('reference_no',$trxref)->where('amount',$r->amount)->first();

            if ($transaction) {
                if($transaction->status == "approved" || $transaction->status == "failed"){

                    return ["status" => '0', 'msg' => "Transaction has already been completed...Please Reinitiate Transaction"];

                }else{

          
                     $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->lockForUpdate()->first();
                    $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->lockForUpdate()->first();
                    
                  //transfer charges Gl
                  $glaccttrr = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('glcharges'))->lockForUpdate()->first();
                    
                  if($glaccttrr->status == '1'){
                  $this->gltransaction('withdrawal',$glaccttrr,$tchargeamt,null);
                  $this->create_saving_transaction_gl(null,$glaccttrr->id,$cust->branch_id, $tchargeamt,'credit','core',$trxref,$this->generatetrnxref('trnxchrg'),'transfer charges','approved',$usern);
                  }
                 
                  //other charges Gl
                  $otherglacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('othrchargesgl'))->lockForUpdate()->first();
                  
                  if($otherglacct->status == '1'){
                  $this->gltransaction('withdrawal',$otherglacct,$ocharge->amount,null); 
                  $this->create_saving_transaction_gl(null,$otherglacct->id,$cust->branch_id, $ocharge->amount,'credit','core',$trxref,$this->generatetrnxref('otc'),'others charges fees','approved',$usern);
                  }
         

                   $debitCustomer = $this->DebitCustomerandcompanyGlAcct($r->customerid,$totalAmount,$r->amount,'10897866','py','Bank Transfer via asset matrix payout','core',$usern);

                   $this->logInfo("debit customer response",$debitCustomer);
                    
                    //if($cust->account_type == '1'){//saving acct GL
                        
                        if($glsavingdacct->status == '1'){
                        $this->gltransaction('deposit',$glsavingdacct,$totalAmount,null);
                    $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $totalAmount,'debit','core',$trxref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                        }
                        
                    // }elseif($cust->account_type == '2'){//current acct GL
                        
                    //     if($glcurrentacct->status == '1'){
                    //     $this->gltransaction('deposit',$glcurrentacct,$totalAmount,null);
                    // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $totalAmount,'debit','core',$trxref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                    //     }
                        
                    // }

                    if (!$debitCustomer["status"]) {
                        return ["status" => '0', 'msg' => $debitCustomer['message']];
                    }
 
                   $bank = Bank::where('bank_code', $r->bank_code)->first();
 
                                       
                    $url= env('ASSETMATRIX_BASE_URL')."banktransfer-payout";
                    
                    $bankTransfer = $this->bankTransferviaPayout($url,$r->amount,$r->destination_account,$r->bank_code,env('SETTLEMENT_ACCOUNT_USERNAME'),$trxref,$desc);
 
                    //return $bankTransfer;
                    $this->logInfo("bank transfer response log",$bankTransfer);

                    $description = empty($r->description) ? "trnsf" : $r->description;
                   $updtdescription = $description."/".$r->receipient_name."/".$r->destination_account."-".$bank->bank_name;

                    if ($bankTransfer["status"] == true) {
                   
                        $this->updateTransactionAndAddTrnxcharges(null,$cust->id,$cust->branch_id,$charge,'debit','core','0',null,null,null,$trxref,
                        $updtdescription,"charges",'approved','10',$usern,'');
                            
                   
                            $famt = " N".number_format($r->amount,2);
                            $dbalamt = " N".number_format($debitCustomer['balance'],2);
                            $bdecs1 =  $updtdescription;
        
                            $msg = "Debit Amt: ".$famt."<br> Desc: ".$bdecs1."<br> Avail Bal: ".$dbalamt."<br> Date:" . date('Y-m-d') . "<br> Ref: " .$trxref;
                            $smsmsg = "Debit Amt: ".$famt."\n Desc: ".$bdecs1." \n Avail Bal: ".$dbalamt."\n Date: ".date("Y-m-d")."\n Ref: ".$trxref;
                         
                            if($cust->enable_sms_alert){
                            $this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                            }
                        if($cust->enable_email_alert){
                            Email::create([
                                'user_id' => $cust->id,
                                'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert',
                                'message' => $msg,
                                'recipient' => $cust->email,
                            ]);
        
                        Mail::send(['html' => 'mails.sendmail'],[
                            'msg' => $msg,
                            'type' => 'Debit Transaction'
                        ],function($mail)use($getsetvalue,$cust){
                            $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                            $mail->to($cust->email);
                        $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
                    });
                        }
               
             return ['status' => 'success', 'msg' => 'Bank Transfer Successful'];
                   
          }elseif($bankTransfer["status"] == false){

                     //FAILED TRANSACTION    
                     $this->updateTransactionAndAddTrnxcharges(null,$cust->id,$cust->branch_id,$charge,'debit','core','0',null,null,null,$trxref,
                     $updtdescription,"charges",'failed','10',$usern,'');
                  
                  $this->tracktrails('1','1',$usern,'customer','Bank Transfer Failed');
                  
                $reverd = $this->ReverseDebitTrnxandcompanyGlAcct($cust->id,$totalAmount,$r->amount,$trxref,'10897866','asm','Transaction reversed','core','trnsfer',$usern,'');
                
                //reverse transfer charges Gl
                if($glaccttrr->status == '1'){
                 $this->gltransaction('deposit',$glaccttrr,$tchargeamt,null);
                $this->create_saving_transaction_gl(null,$glaccttrr->id,null, $tchargeamt,'debit','core',$trxref,$this->generatetrnxref('trnxchrg'),'reversed transfer charges','approved',$usern);
                }
               
                //reverse other charges Gl
                if($otherglacct->status == '1'){
                 $this->gltransaction('deposit',$otherglacct,$ocharge->amount,null); 
                $this->create_saving_transaction_gl(null,$otherglacct->id,$cust->branch_id, $ocharge->amount,'debit','core',$trxref,$this->generatetrnxref('otc'),'reversed others charges fees','approved',$usern);
                }
        
                 //reverse saving acct and current acct Gl
                 if($cust->account_type == '1'){//saving acct GL
                             
                    if($glsavingdacct->status == '1'){
                $this->gltransaction('withdrawal',$glsavingdacct,$totalAmount,null);
                $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $totalAmount,'credit','core',$trxref,$this->generatetrnxref('Cr'),'customer credited','approved',$usern);
                    }
                    
                }elseif($cust->account_type == '2'){//current acct GL
                
                    if($glcurrentacct->status == '1'){
                    $this->gltransaction('withdrawal',$glcurrentacct,$totalAmount,null);
                $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $totalAmount,'credit','core',$trxref,$this->generatetrnxref('Cr'),'customer credited','approved',$usern);
                    }
                }
        
                       $msg = "Credit Amt: N".number_format($totalAmount,2)."<br> Desc: Debit Transaction Reversal <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$trxref;
                       $smsmsg = "Credit Amt: N".number_format($totalAmount,2)."\n Desc: Debit Transaction Reversal \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date: ".date("Y-m-d")."\n Ref: ".$trxref;
                         
                       if($cust->enable_sms_alert){
                       $this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                       }

                    if($cust->enable_email_alert){
                     Email::create([
                        'user_id' =>  $cust->id,
                        'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
                        'message' => $msg,
                        'recipient' => $cust->email,
                    ]);
        
                     Mail::send(['html' => 'mails.sendmail'],[
                         'msg' => $msg,
                         'type' => 'Credit Transaction'
                     ],function($mail)use($getsetvalue,$cust){
                         $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                           $mail->to($cust->email);
                         $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
                     });
                    }
                    
                    DB::commit();

                   return ['status' => '0', 'msg' => 'Bank Transfer Failed'];
                   
              
                       }else{
                               
                            return ['status' => 'success', 'msg' => "Transaction Completed"];
                        }
                }
            } else {
                return ["status" => '0', 'msg' => "Invalid Transaction Reference,Please Reinitiate Transaction"];
            }

        }else{
            $uptrn = SavingsTransaction::where('reference_no',$trxref)->first();
            $uptrn->destination_account = $dacct;
            $uptrn->is_approve = '0';
            $uptrn->approve_by = null;
            $uptrn->save();

            $glacctgl = GeneralLedger::select('id','status')->where('gl_code','10897866')->first();
            
            if($glacctgl->status == '1'){
            $this->create_saving_transaction_gl(Auth::user()->id,$glacctgl->id,$branch,$r->amount,'credit','core',$trxref,$this->generatetrnxref('py'),$desc,'pending',$usern);
            }
            
            DB::commit();

            return array(
                'status' => 'success',
                'msg' => 'Withdrawal Posted...Awaiting Approval'
            );
        }
        
        $lock->release();

        DB::rollBack();

        }//lock
    }


    public function transferToBankAccountViaMonnify(Request $r){
        
        $lock= Cache::lock('mnoiftrnfbnkacc-'.mt_rand('1111','9999'),5);
       
       if($lock->release()){
           
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $validation = Validator::make($r->all(), [
            "amount" => "required|numeric|gt:0|max:1000000",
            "destination_account" => "required",
            "receipient_name" => "required|string",
            "bank_code" => "required",
        ]);

        if ($validation->fails()) {
            $ra = array("status" => false, "message" => $validation->errors()->all()[0]);
            return response()->json($ra, 406);
        }

        $this->logInfo("bank transfer via monnify",$r->all());
       
        $trxref = $this->generatetrnxref('m');

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;

        $this->tracktrails('1','1',$usern,'customer','Transfer to Bank Via Monnify');
        $getsetvalue = new Setting();

        $dacct = $r->receipient_name.','.$r->destination_account.','.$r->bank_code;

        $cust = Customer::where('id',$r->customerid)->first();

        $desc = empty($r->description) ? "From ".$cust->first_name." ".$cust->last_name : $r->description;
        
        $tcharge = Charge::select('amount')->where('id',$getsetvalue->getsettingskey('transfer_charge'))->first();
        $ocharge = Charge::select('amount')->where('id',$getsetvalue->getsettingskey('othercharges'))->first();
        $monnifycharge = $getsetvalue->getsettingskey('monnifycharge');

        $totalAmount = $r->amount + $tcharge->amount + $monnifycharge + $ocharge->amount;
        $monify = $r->amount + $monnifycharge;
        $charge = $tcharge->amount + $monnifycharge + $ocharge->amount;

        $compbal = $this->validatecompanybalance($totalAmount,'combal');
        if($compbal["status"] == false){
    
            $this->logInfo("validating company balance",$compbal);
        
        return response()->json($compbal,406);
        }

        //initiate transaction
        $this->create_saving_transaction(Auth::user()->id,$r->customerid,$branch,$r->amount,
        'debit','core','0',$r->slipno,null,$getsetvalue->getsettingskey('payoption'),$dacct,$trxref,$desc,'pending','2','trnsfer',$usern);
      
           //verify monnify account balance
           $monfybal = $this->validateMonnifyBalance($this->macctno,$monify);
           //return $monfybal;
           $this->logInfo("monnify balance",$monfybal);
           
           if ($monfybal["status"] == false) {
              return ['status' => '0','msg' => $monfybal['message']];
            }
        
                $chkcres = $this->checkCustomerRestriction($r->customerid);
                 if($chkcres == true){
             
                     $this->tracktrails('1','1',$usern,'customer','Account Restricted');
                     
                     $this->logInfo("","Customer Account Restricted");
                     
                     return ['status' => '0', 'msg' => 'Your Account Has Been Restricted. Please contact support'];
                 }

                 $chklien = $this->checkCustomerLienStatus($r->customerid);
                     if($chklien['status'] == true && $chklien['lien'] == 2){
                         
                         $this->tracktrails('1','1',$usern,'customer','Account has been lien');
                         
                         $this->logInfo("Account lien",$chklien);
                         
                      return ['status' => '0', 'msg' => 'Your Account Has Been Lien('.$chklien['messages'].')...please contact support'];
                     }
                     
                 $validateuserbalance = $this->validatecustomerbalance($r->customerid,$totalAmount);
                 if($validateuserbalance["status"] == false){
         
                     $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
                     
                     $this->logInfo("customer balance",$validateuserbalance);
                     
                     return ['status' => '0', 'msg' => $validateuserbalance["message"]];
                 }
         
                //  $validateTransferAmount = $this->validateTransfer($totalAmount,$getsetvalue->getsettingskey('online_transfer'),$r->customerid);
         
                //  if ($validateTransferAmount['status'] == false) {
                     
                //      $this->logInfo("online transfer",$validateTransferAmount);
                     
                //      return ['status' => '0','msg' => $validateTransferAmount['message']];
                //  }

        if($getsetvalue->getsettingskey('withdrawal_limit') == 0 || $totalAmount < $getsetvalue->getsettingskey('withdrawal_limit')){

            $transaction = SavingsTransaction::where('reference_no',$trxref)->where('amount',$r->amount)->first();

            if ($transaction) {
                if($transaction->status == "approved" || $transaction->status == "failed"){

                    return ["status" => '0', 'msg' => "Transaction has already been completed...Please Reinitiate Transaction"];

                }else{

          
                     $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->first();
                    $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->first();
                    
                    //transfer charges Gl
                    $trglacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('glcharges'))->first();
                    
                    if($trglacct->status == '1'){
                    $this->gltransaction('withdrawal',$trglacct,$tcharge->amount,null);
                    $this->create_saving_transaction_gl(null,$trglacct->id,$cust->branch_id, $tcharge->amount,'credit','core',$trxref,$this->generatetrnxref('trnxchrg'),'transfer charges','approved',$usern);
                    }
                   
                    //other charges Gl
                    $otherglacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('othrchargesgl'))->first();
                    
                    if($otherglacct->status == '1'){
                    $this->gltransaction('withdrawal',$otherglacct,$ocharge->amount,null); 
                    $this->create_saving_transaction_gl(null,$otherglacct->id,$cust->branch_id, $ocharge->amount,'credit','core',$trxref,$this->generatetrnxref('otc'),'others charges fees','approved',$usern);
                    }
         

                   $debitCustomer = $this->DebitCustomerandcompanyGlAcct($r->customerid,$totalAmount,$monify,'10794478','m','Bank Transfer via monnify','core',$usern);

                   $this->logInfo("debit customer response",$debitCustomer);
                    
                    if($cust->account_type == '1'){//saving acct GL
                        
                        if($glsavingdacct->status == '1'){
                        $this->gltransaction('deposit',$glsavingdacct,$totalAmount,null);
                    $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $totalAmount,'debit','core',$trxref,$this->generatetrnxref('W'),'customer debited','approved',$usern.'(c)');
                        }
                        
                    }elseif($cust->account_type == '2'){//current acct GL
                        
                        if($glcurrentacct->status == '1'){
                        $this->gltransaction('deposit',$glcurrentacct,$totalAmount,null);
                    $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $totalAmount,'debit','core',$trxref,$this->generatetrnxref('W'),'customer debited','approved',$usern.'(c)');
                        }
                        
                    }

                    if (!$debitCustomer["status"]) {
                       
                        $this->updateTransactionAndAddTrnxcharges(null, $cust->id,$cust->branch_id,$charge,'debit','core','0',null,null,null,$trxref,
                          "failed Transaction","failed Transaction",'failed','10',$usern,$dacct);
                          
                            $this->tracktrails('1','1',$usern,'customer','Transaction Failed');
 
                     $reverd = $this->ReverseDebitTrnxandcompanyGlAcct(Auth::user()->id,$totalAmount,$monify,$trxref,'10794478','m','Transaction reversed','core','trnsfer',$usern,$dacct);
                     
                     //reverse transfer charges Gl
                     if($trglacct->status == '1'){
                      $this->gltransaction('deposit',$trglacct,$tcharge->amount,null);
                     $this->create_saving_transaction_gl(null,$trglacct->id,$cust->branch_id, $tcharge->amount,'debit','core',$trxref,$this->generatetrnxref('trnxchrg'),'reversed transfer charges','approved',$usern.'(c)');
                    }
                    
                     //reverse other charges Gl
                     if($otherglacct->status == '1'){
                      $this->gltransaction('deposit',$otherglacct,$ocharge->amount,null); 
                     $this->create_saving_transaction_gl(null,$otherglacct->id,$cust->branch_id, $ocharge->amount,'debit','core',$trxref,$this->generatetrnxref('otc'),'reversed others charges fees','approved',$usern.'(c)');
                     }
                     
                         //reverse saving acct and current acct Gl
                      if(Auth::user()->account_type == '1'){//saving acct GL
                         
                         if($glsavingdacct->status == '1'){
                     $this->gltransaction('withdrawal',$glsavingdacct,$totalAmount,null);
                     $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $totalAmount,'credit','core',$trxref,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
                         }
                         
                     }elseif(Auth::user()->account_type == '2'){//current acct GL
                     
                         if($glcurrentacct->status == '1'){
                         $this->gltransaction('withdrawal',$glcurrentacct,$totalAmount,null);
                     $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $totalAmount,'credit','core',$trxref,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
                         }
                         
                     }
                    
                        return ['status' => '0','msg' => $debitCustomer['message']];
                    }

                    $turl = $this->murl."v2/disbursements/single";
                  $bankTransfer = $this->monnifyTranfer($turl,$this->mapikey,$this->msercetkey,$r->amount,$trxref,
                                                        $desc,$r->bank_code,$r->destination_account,$this->macctno,$r->receipient_name);

                   //return $bankTransf;
                   $this->logInfo("bank transfer response log via monnify",$bankTransfer);
                   $description = empty($r->description) ? "trnsf" : $r->description;
                   $updtdescription = $description."/".$r->receipient_name."/".$r->destination_account."-".$bankTransfer["responseBody"]["destinationBankName"];

                   $dacct2 = $r->receipient_name."/".$r->destination_account."-".$bankTransfer["responseBody"]["destinationBankName"];

                    if($bankTransfer["responseCode"] == "0"){
                        if($bankTransfer["responseBody"]["status"] == "SUCCESS"){

                            $this->updateTransactionAndAddTrnxcharges(null,$r->customerid,$cust->branch_id,$charge,'debit','core','0',null,null,null,$trxref,
                                   $updtdescription,"charges",'approved','10',$usern,$dacct2);
                                      
                              
                           $famt = " N".number_format($totalAmount,2);
                           $dbalamt = " N".number_format($debitCustomer['balance'],2);
                           $bdecs1 =  $updtdescription;
                            
                           $smsmsg = "Debit Amt: ".$famt."\n Desc: ".$bdecs1." \n Avail Bal: ".$dbalamt."\n Date: ".date("Y-m-d")."\n Ref: ".$trxref;
                         
                            if($cust->enable_sms_alert){
                            $this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                            }

                           if($cust->enable_email_alert){
                           $msg = "Debit Amt: ".$famt."<br> Desc: ".$bdecs1."<br> Avail Bal: ".$dbalamt."<br> Date:" . date('Y-m-d') . "<br> Ref: " .$trxref;
                           Email::create([
                               'user_id' =>  $cust->id,
                               'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert',
                               'message' => $msg,
                               'recipient' => $cust->email,
                           ]);
                  
                         Mail::send(['html' => 'mails.sendmail'],[
                              'msg' => $msg,
                               'type' => 'Debit Transaction'
                          ],function($mail)use($getsetvalue,$cust){
                           $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                           $mail->to($cust->email);
                         $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
                     });
                    }

                        return ['status' => 'success', 'msg' => 'Bank Transfer Successful'];
                              
                     }else{
                                //FAILED TRANSACTION    
                                $this->updateTransactionAndAddTrnxcharges(null, $r->customerid,$cust->branch_id,$charge,'debit','core','0',null,null,null,$trxref,
                                $updtdescription,"charges",'failed','10',$usern,$dacct2);
                             
                             $this->tracktrails('1','1',$usern,'customer','Transaction Failed');
       
                           $reverd = $this->ReverseDebitTrnxandcompanyGlAcct($r->customerid,$totalAmount,$monify,$trxref,'10794478','m','Transaction reversed','core','trnsfer',$usern,$dacct2);
                           
                           //reverse transfer charges Gl
                           if($trglacct->status == '1'){
                            $this->gltransaction('deposit',$trglacct,$tcharge->amount,null);
                           $this->create_saving_transaction_gl(null,$trglacct->id,$cust->branch_id, $tcharge->amount,'debit','core',$trxref,$this->generatetrnxref('trnxchrg'),'reversed transfer charges','approved',$usern);
                           }
                          
                           //reverse other charges Gl
                           if($otherglacct->status == '1'){
                            $this->gltransaction('deposit',$otherglacct,$ocharge->amount,null); 
                           $this->create_saving_transaction_gl(null,$otherglacct->id,$cust->branch_id, $ocharge->amount,'debit','core',$trxref,$this->generatetrnxref('otc'),'reversed others charges fees','approved',$usern);
                           }
                               
                               //reverse saving acct and current acct Gl
                            if(Auth::user()->account_type == '1'){//saving acct GL
                               
                               if($glsavingdacct->status == '1'){
                           $this->gltransaction('withdrawal',$glsavingdacct,$totalAmount,null);
                           $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $totalAmount,'credit','core',$trxref,$this->generatetrnxref('D'),'customer debited','approved',$usern);
                               }
                               
                           }elseif(Auth::user()->account_type == '2'){//current acct GL
                               
                               if($glcurrentacct->status == '1'){
                               $this->gltransaction('withdrawal',$glcurrentacct,$totalAmount,null);
                           $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $totalAmount,'credit','core',$trxref,$this->generatetrnxref('D'),'customer debited','approved',$usern);
                               }
                               
                           }
                           
                           $smsmsg = "Credit Amt: N".number_format($totalAmount,2)."\n Desc: Debit Transaction Reversal \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date: ".date("Y-m-d")."\n Ref: ".$trxref;
                         
                            if($cust->enable_sms_alert){
                            $this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                            }

                           if($cust->enable_email_alert){
                                  $msg = "Credit Amt: N".number_format($totalAmount,2)."<br> Desc: Debit Transaction Reversal <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$trxref;
                                Email::create([
                                   'user_id' =>  $cust->id,
                                   'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
                                   'message' => $msg,
                                   'recipient' => $cust->email,
                               ]);
                   
                                Mail::send(['html' => 'mails.sendmail'],[
                                    'msg' => $msg,
                                    'type' => 'Credit Transaction'
                                ],function($mail)use($getsetvalue,$cust){
                                    $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                                      $mail->to($cust->email);
                                    $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
                                });
                            }
                              return ['status' => '0', 'msg' => 'Bank Transfer Failed'];
                              
                         }
                    }
               }
            } else {
                return ["status" => '0', 'msg' => "Invalid Transaction Reference,Please Reinitiate Transaction"];
            }
        }else{
            $uptrn = SavingsTransaction::where('reference_no',$trxref)->first();
            $uptrn->destination_account = $dacct;
            $uptrn->is_approve = '0';
            $uptrn->approve_by = null;
            $uptrn->save();

            $glacctgl = GeneralLedger::select('id','status')->where('gl_code','10794478')->first();
            
            if($glacctgl->status == '1'){
            $this->create_saving_transaction_gl(Auth::user()->id,$glacctgl->id,$branch,$r->amount,'credit','core',$trxref,$this->generatetrnxref('m'),$desc,'pending',$usern);
            }
            
            return array(
                'status' => 'success',
                'msg' => 'Withdrawal Posted...Awaiting Approval'
            );
        }
        
        $lock->release();
         }//lock
    }
    
     public function transferToBankAccountViawireless(Request $r){
         
         $lock = Cache::lock('wirlstrnfbnkacc-'.mt_rand('1111','9999'),5);
            
            if($lock->get()){  
                
        $this->logInfo("bank transfer via wireless",$r->all());
       
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails('1','1',$usern,'customer','Transfer to Bank Via wireless');


        $validation = Validator::make($r->all(), [
            "amount" => "required|numeric|gt:0|max:1000000",
            "destination_account" => "required",
            "receipient_name" => "required|string",
            "bank_code" => "required",
        ]);
        

        if ($validation->fails()) {
            $ra = array("status" => false, "message" => $validation->errors()->all()[0]);
            return response()->json($ra, 406);
        }

        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $getsetvalue = new Setting();
        
        $trxref = $this->generatetrnxref('wlv');

        $dacct = $r->receipient_name.','.$r->destination_account.','.$r->bank_code;

        $cust = Customer::where('id',$r->customerid)->first();

        $desc = empty($r->description) ? "From ".$cust->first_name." ".$cust->last_name : $r->description;
        
        //initiate transaction
        $this->create_saving_transaction(Auth::user()->id,$r->customerid,$branch,$r->amount,
        'debit','core','0',$r->slipno,null,$getsetvalue->getsettingskey('payoption'),$dacct,$trxref,$desc,'pending','2','trnsfer',$usern);
      
        $tcharge = Charge::select('amount')->where('id',$getsetvalue->getsettingskey('transfer_charge'))->first();
        $ocharge = Charge::select('amount')->where('id',$getsetvalue->getsettingskey('othercharges'))->first();
        $wirelesscharge = 15;

        $totalAmount = $r->amount + $tcharge->amount + $wirelesscharge + $ocharge->amount - 5;
        $wireless = $r->amount + $wirelesscharge;
       
        $charge = $tcharge->amount + $ocharge->amount + $wirelesscharge - 5;

          //verify wireless account balance
          $wirelessbal = $this->validateWirelessBalance($wireless);
          //return $monfybal;
          $this->logInfo("wireless balance",$wirelessbal);
          
          if ($wirelessbal["status"] == false) {
             return [ "status" => "0", 'msg' => $wirelessbal['message']];
           }
       
               $chkcres = $this->checkCustomerRestriction($cust->id);
                if($chkcres == true){
            
                    $this->tracktrails('1','1',$usern,'customer','Account Restricted');
                    
                    $this->logInfo("","Customer Account Restricted");
                    
                    return ['status' => '0', 'msg' => 'Your Account Has Been Restricted. Please contact support'];
                }

                $chklien = $this->checkCustomerLienStatus($cust->id);
                    if($chklien['status'] == true && $chklien['lien'] == 2){
                        
                        $this->tracktrails('1','1',$usern,'customer','Account has been lien');
                        
                        $this->logInfo("Account lien",$chklien);
                        
                     return ['status' => '0', 'msg' => 'Your Account Has Been Lien('.$chklien['messages'].')...please contact support'];
                    }
                    
                $validateuserbalance = $this->validatecustomerbalance($cust->id,$totalAmount);
                if($validateuserbalance["status"] == false){
        
                    $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
                    
                    $this->logInfo("customer balance",$validateuserbalance);
                    
                    return ["status" => "0", "msg" => $validateuserbalance['message']];
                }

 if($getsetvalue->getsettingskey('withdrawal_limit') == 0 || $totalAmount < $getsetvalue->getsettingskey('withdrawal_limit')){

       $transaction = SavingsTransaction::where('reference_no', $trxref)->where('amount',$r->amount)->first();

        if ($transaction) {
            if($transaction->status == "approved" || $transaction->status == "failed"){
                return response()->json(["status" => false, 'message' => "Transaction has already been completed...Please Initiate Transaction"], 409);
            }else{
               
                     $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->first();
                    $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->first();
                    
                    //transfer charges Gl
                    $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('glcharges'))->first();
                    
                    if($glacct->status == '1'){
                    $this->gltransaction('withdrawal',$glacct,$tcharge->amount,null);
                    $this->create_saving_transaction_gl(null,$glacct->id,$cust->branch_id, $tcharge->amount,'credit','core',$trxref,$this->generatetrnxref('trnxchrg'),'transfer charges','approved',$usern);
                    }
                   
                    //other charges Gl
                    $otherglacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('othrchargesgl'))->first();
                    
                    if($otherglacct->status == '1'){
                        $this->gltransaction('withdrawal',$otherglacct,$ocharge->amount,null); 
                    $this->create_saving_transaction_gl(null,$otherglacct->id,$cust->branch_id, $ocharge->amount,'credit','core',$trxref,$this->generatetrnxref('otc'),'others charges fees','approved',$usern);
                    
                    }
                    
                   $debitCustomer = $this->DebitCustomerandcompanyGlAcct($cust->id,$totalAmount,$wireless,'10899792','wlv','Bank Transfer via wireless','core',$usern);

                   $this->logInfo("debit customer response",$debitCustomer);
                    
                    if($cust->account_type == '1'){//saving acct GL
                    
                        if($glsavingdacct->status == '1'){
                        $this->gltransaction('deposit',$glsavingdacct,$totalAmount,null);
                    $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $totalAmount,'debit','core',$trxref,$this->generatetrnxref('D'),'customer debited','approved',$usern);
                        }
                        
                    }elseif($cust->account_type == '2'){//current acct GL
                    
                    if($glcurrentacct->status == '1'){
                        $this->gltransaction('deposit',$glcurrentacct,$totalAmount,null);
                    $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $totalAmount,'debit','core',$trxref,$this->generatetrnxref('D'),'customer debited','approved',$usern);
                        }
                        
                    }
                    
                   if (!$debitCustomer["status"]) {
                       
                       $this->updateTransactionAndAddTrnxcharges(null, $cust->id,$cust->branch_id,$charge,'debit','core','0',null,null,null,$trxref,
                         "failed Transaction","failed Transaction",'failed','10',$usern,$dacct);
                         
                           $this->tracktrails('1','1',$usern,'customer','Transaction Failed');

                    $reverd = $this->ReverseDebitTrnxandcompanyGlAcct($cust->id,$totalAmount,$wireless,$trxref,'10899792','m','Transaction reversed','core','trnsfer',$usern,$dacct);
                    
                    //reverse transfer charges Gl
                    if($glacct->status == '1'){
                     $this->gltransaction('deposit',$glacct,$tcharge->amount,null);
                    $this->create_saving_transaction_gl(null,$glacct->id,$cust->branch_id, $tcharge->amount,'debit','core',$trxref,$this->generatetrnxref('trnxchrg'),'reversed transfer charges','approved',$usern);
                    }
                   
                    //reverse other charges Gl
                    if($otherglacct->status == '1'){
                     $this->gltransaction('deposit',$otherglacct,$ocharge->amount,null); 
                    $this->create_saving_transaction_gl(null,$otherglacct->id,$cust->branch_id, $ocharge->amount,'debit','core',$trxref,$this->generatetrnxref('otc'),'reversed others charges fees','approved',$usern);
                    }
                        //reverse saving acct and current acct Gl
                     if($cust->account_type == '1'){//saving acct GL
                     
                        if($glsavingdacct->status == '1'){
                    $this->gltransaction('withdrawal',$glsavingdacct,$totalAmount,null);
                    $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $totalAmount,'credit','core',$trxref,$this->generatetrnxref('D'),'customer debited','approved',$usern);
                        }
                        
                    }elseif($cust->account_type == '2'){//current acct GL
                    
                        if($glcurrentacct->status == '1'){
                        $this->gltransaction('withdrawal',$glcurrentacct,$totalAmount,null);
                    $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $totalAmount,'credit','core',$trxref,$this->generatetrnxref('D'),'customer debited','approved',$usern);
                        }
                        
                    }
                   
                       return response()->json($debitCustomer);
                   }

                   $bank = Bank::where('bank_code', $r->bank_code)->first();

                   $newdescription = empty($r->description) ? "From " .$usern : $r->description;
                   
                     $this->logInfo("transfer Url",$this->url."bank-transfer");
                     
                  //wireless verify transfer
                   $bankTransfer = $this->WirelessTransfer($this->apikey,$r->amount,$trxref,$r->bank_code,$r->destination_account,$r->receipient_name,$newdescription);
                
                   //return $bankTransfer;
                  $this->logInfo("bank transfer response log via wireless verify",$bankTransfer);
                  
                   //logInfo($bankTransfer, "Monnify Transfer Response");
                   $description = empty($r->description) ? "trnsf" : $r->description;
                   $updtdescription = $description."/".$r->receipient_name."/".$r->destination_account."-".$bank->bank_name;

                   $dacct2 = $r->receipient_name."/".$r->destination_account."-".$bank->bank_name;

               //if ($bankTransfer["status"] == "00") {
                   if($bankTransfer["status"] == "00"){

                     $this->updateTransactionAndAddTrnxcharges(null, $cust->id,$cust->branch_id,$charge,'debit','core','0',null,null,null,$trxref,
                            $updtdescription,"charges",'approved','10',$usern,$dacct2);
                       
                    $famt = " N".number_format($totalAmount,2);
                    $dbalamt = " N".number_format($debitCustomer['balance'],2);
                    $bdecs1 =  $updtdescription;

                    $msg = "Debit Amt: ".$famt."<br> Desc: ".$bdecs1."<br> Avail Bal: ".$dbalamt."<br> Date:" . date('Y-m-d') . "<br> Ref: " . $trxref;
                    $smsmsg = "Debit Amt: ".$famt."\n Desc: ".$bdecs1."\n Avail Bal: ".$dbalamt."\n Date:" . date('Y-m-d') . "\n Ref: " . $trxref;
                    
                    if($cust->enable_sms_alert){
                    $this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                   }

                    if($cust->enable_email_alert){
                    Email::create([
                        'user_id' =>  $cust->id,
                        'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert',
                        'message' => $msg,
                        'recipient' => $cust->email,
                    ]);
           
                  Mail::send(['html' => 'mails.sendmail'],[
                       'msg' => $msg,
                        'type' => 'Debit Transaction'
                   ],function($mail)use($getsetvalue,$cust){
                    $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                    $mail->to($cust->email);
                  $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
              });
                   }
                   
                 return response()->json(['status' => true, 'message' => 'Bank Transfer Successful']);
                       
              }else{
                         //FAILED TRANSACTION    
                         $this->updateTransactionAndAddTrnxcharges(null, $cust->id,$cust->branch_id,$charge,'debit','core','0',null,null,null,$trxref,
                         $updtdescription,"charges",'failed','10',$usern,$dacct2);
                      
                      $this->tracktrails('1','1',$usern,'customer','Transaction Failed');

                    $reverd = $this->ReverseDebitTrnxandcompanyGlAcct($cust->id,$totalAmount,$wireless,$trxref,'10899792','m','Transaction reversed','core','trnsfer',$usern,$dacct);
                    
                    //reverse transfer charges Gl
                    if($glacct->status == '1'){
                     $this->gltransaction('deposit',$glacct,$tcharge->amount,null);
                    $this->create_saving_transaction_gl(null,$glacct->id,$cust->branch_id, $tcharge->amount,'debit','core',$trxref,$this->generatetrnxref('trnxchrg'),'reversed transfer charges','approved',$usern);
                    }
                   
                    //reverse other charges Gl
                    if($otherglacct->status == '1'){
                     $this->gltransaction('deposit',$otherglacct,$ocharge->amount,null); 
                    $this->create_saving_transaction_gl(null,$otherglacct->id,$cust->branch_id, $ocharge->amount,'debit','core',$trxref,$this->generatetrnxref('otc'),'reversed others charges fees','approved',$usern);
                    }
                        
                        //reverse saving acct and current acct Gl
                     if($cust->account_type == '1'){//saving acct GL
                     
                        if($glsavingdacct->status == '1'){
                    $this->gltransaction('withdrawal',$glsavingdacct,$totalAmount,null);
                    $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $totalAmount,'credit','core',$trxref,$this->generatetrnxref('D'),'customer debited','approved',$usern);
                        }
                        
                    }elseif($cust->account_type == '2'){//current acct GL
                    
                        if($glcurrentacct->status == '1'){
                        $this->gltransaction('withdrawal',$glcurrentacct,$totalAmount,null);
                    $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $totalAmount,'credit','core',$trxref,$this->generatetrnxref('D'),'customer debited','approved',$usern);
                        }
                    }
                    
                           $msg = "Credit Amt: N".number_format($totalAmount,2)."<br> Desc: Debit Transaction Reversal <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$trxref;
                           $smsmsg = "Credit Amt: N".number_format($totalAmount,2)."\n Desc: Debit Transaction Reversal \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date: ".date("Y-m-d")."\n Ref: ".$trxref;
                           
                           if($cust->enable_sms_alert){
                           $this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                           }

                           if($cust->enable_email_alert){
                         Email::create([
                            'user_id' =>  $cust->id,
                            'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
                            'message' => $msg,
                            'recipient' => $cust->email,
                        ]);
            
                         Mail::send(['html' => 'mails.sendmail'],[
                             'msg' => $msg,
                             'type' => 'Credit Transaction'
                         ],function($mail)use($getsetvalue,$cust){
                             $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                               $mail->to($cust->email);
                             $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
                         });
                           }
             
                       return response()->json(['status' =>false, 'message' => 'Bank Transfer Failed'], 406);
                       
                  }

            //   }else{
                       
            //         return response()->json(['status' => false, 'message' => "Transaction Completed"], 406);
            //     }
            }
            
        } else {
            return response()->json(["status" => false, 'message' => "Invalid Transaction Reference,Please Reinitiate Transaction"], 400);
        }

    }else{
        $uptrn = SavingsTransaction::where('reference_no',$trxref)->first();
        $uptrn->destination_account = $dacct;
        $uptrn->is_approve = '0';
        $uptrn->approve_by = null;
        $uptrn->save();

        $glacctgl = GeneralLedger::select('id','status')->where('gl_code','10794478')->first();
        
        if($glacctgl->status == '1'){
        $this->create_saving_transaction_gl(Auth::user()->id,$glacctgl->id,$branch,$r->amount,'credit','core',$trxref,$this->generatetrnxref('wl'),$desc,'pending',$usern);
        }
        
            return array(
                'status' => 'success',
                'msg' => 'Withdrawal Posted...Awaiting Approval'
            );
        }
        
        $lock->release();
          }
    }

public function approve_transactions($ref,$cusid){
         
  $lock = Cache::lock('appvtrhynsx-'.mt_rand('1111','9999'),4);
 
    try {

        $lock->block(2);

        DB::beginTransaction();
        
        $getsetvalue = new Setting();
        $cmdclicked = request()->btnType;

$branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;
            
         $usern = Auth::user()->last_name." ".Auth::user()->first_name;
         
        $trnx = SavingsTransaction::where('customer_id',$cusid)
                                    ->where('status','pending')
                                    ->where('reference_no',$ref)
                                    ->orWhere('slip',$ref)
                                    ->first();
  
        $trnxGL = SavingsTransactionGL::where('status','pending')
                                        ->where('slip',$ref)
                                        ->orWhere('reference_no',$ref)
                                        ->first();
// dd($trnxGL);
//   $transactions = SavingsTransaction::where('device','core')->orderBy('id','DESC')->first();
//  $time_is_ok = true;
//  if ($transactions) {
//      $dbtimestamp = strtotime($transactions->created_at);
//      if (time() - $dbtimestamp < 1 * 60) {
//          $time_is_ok = false;
//      }
//  }

// if ($time_is_ok) {
 //if($trnx && $trnxGL){

 if($trnx->status == "approved" ||  $trnxGL->status == "approved"){

     return ['status' => 'success', 'msg' => 'Transaction already approved'];

 }

  if(empty($trnx) || empty($trnxGL)){
    //for saving transaction
           if($trnx){
            $trnx->status = "declined";
            $trnx->is_approve = "1";
            $trnx->approve_by = $usern;
            $trnx->approve_date = Carbon::now();
            $trnx->save();
           }
                     
           //for saving transactionGL
           if($trnxGL){
            $trnxGL->status = "declined";
            $trnxGL->approved_by = $usern;
            $trnxGL->approve_date = Carbon::now();
            $trnxGL->save();
           }

             return ['status' => 'success', 'msg' => 'Transaction Declined, Please Repost Transaction'];
        }

     
        $desc = $trnx->notes;
        $customeracct2 = Saving::lockForUpdate()->where('customer_id',$cusid)->first();
        $cust = Customer::where('id',$cusid)->first();

        $glacct = GeneralLedger::select('id','status','account_balance','gl_type')->where('id',$trnxGL->general_ledger_id)->lockForUpdate()->first();
            
          //  dd($glacct);
        $glsavingdacct = GeneralLedger::select('id','status','account_balance','gl_type')->where('gl_code','20993097')->lockForUpdate()->first();
       $glcurrentacct = GeneralLedger::select('id','status','account_balance','gl_type')->where('gl_code','20639526')->lockForUpdate()->first();   
            

       if($trnx->transfer_type == "1"){//main bank

            $tcharge = Charge::select('amount')->where('id',$getsetvalue->getsettingskey('transfer_charge'))->lockForUpdate()->first();
        $ocharge = Charge::select('amount')->where('id',$getsetvalue->getsettingskey('othercharges'))->lockForUpdate()->first();
        $bankcharger = $getsetvalue->getsettingskey('bankcharge');
        
        $charge = $tcharge->amount + $bankcharger + $ocharge->amount;
        $totalAmount = $trnx->amount + $charge;
        $tchargeamt = $tcharge->amount + $bankcharger;

            if($cmdclicked == "approve"){
                $chkcres = $this->checkCustomerRestriction($cusid);
                if($chkcres == true){
            
                    $this->tracktrails('1','1',$usern,'customer','Account Restricted');
                    
                    $this->logInfo("","Customer Account Restricted");
                    
                    return ['status' => '0', 'msg' => 'Your Account Has Been Restricted. Please contact support'];
                }
        
                $chklien = $this->checkCustomerLienStatus($cusid);
                    if($chklien['status'] == true && $chklien['lien'] == 2){
                        
                        $this->tracktrails('1','1',$usern,'customer','Account has been lien');
                        
                        $this->logInfo("Account lien",$chklien);
                        
                     return ['status' => '0', 'msg' => 'Your Account Has Been Lien('.$chklien['messages'].')...please contact support'];
                    }
                    
                $validateuserbalance = $this->validatecustomerbalance($cusid,$totalAmount);
                if($validateuserbalance["status"] == false){
        
                    $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
                    
                    $this->logInfo("customer balance",$validateuserbalance);
                    
                    return ['status' => '0', 'msg' => $validateuserbalance["message"]];
                }

                $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->first();
                $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->first();
                
              //transfer charges Gl
              $tglacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('glcharges'))->first();
                
              if($tglacct->status == '1'){
              $this->gltransaction('withdrawal',$tglacct,$tchargeamt,null);
              $this->create_saving_transaction_gl(null,$tglacct->id,$cust->branch_id, $tchargeamt,'credit','core',$ref,$this->generatetrnxref('trnxchrg'),'transfer charges','approved',$usern);
              }
             
              //other charges Gl
              $otherglacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('othrchargesgl'))->first();
              
              if($otherglacct->status == '1'){
              $this->gltransaction('withdrawal',$otherglacct,$ocharge->amount,null); 
              $this->create_saving_transaction_gl(null,$otherglacct->id,$cust->branch_id, $ocharge->amount,'credit','core',$ref,$this->generatetrnxref('otc'),'others charges fees','approved',$usern);
              }
     

               $debitCustomer = $this->DebitCustomerandcompanyGlAcct($cusid,$totalAmount,$trnx->amount,$getsetvalue->getsettingskey('assetmtx'),'py','Bank Transfer via payout','core',$usern);

               $this->logInfo("debit customer response",$debitCustomer);
                
                if($cust->account_type == '1'){//saving acct GL
                    
                    if($glsavingdacct->status == '1'){
                    $this->gltransaction('deposit',$glsavingdacct,$totalAmount,null);
                $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $totalAmount,'debit','core',$ref,$this->generatetrnxref('W'),'customer debited','approved',$usern.'(c)');
                    }
                    
                }elseif($cust->account_type == '2'){//current acct GL
                    
                    if($glcurrentacct->status == '1'){
                    $this->gltransaction('deposit',$glcurrentacct,$totalAmount,null);
                $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $totalAmount,'debit','core',$ref,$this->generatetrnxref('W'),'customer debited','approved',$usern.'(c)');
                    }
                    
                }

                if (!$debitCustomer["status"]) {
                    return ["status" => '0', 'msg' => $debitCustomer['message']];
                }

                $ds = explode(',',$trnx->destination_account);

                
               $bank = Bank::where('bank_code', $ds[2])->first();

                                   
                $url= env('ASSETMATRIX_BASE_URL')."banktransfer-payout";
                
                $bankTransfer = $this->bankTransferviaPayout($url,$trnx->amount,$ds[1],$ds[2],env('SETTLEMENT_ACCOUNT_USERNAME'),$trnx->reference_no,$trnx->notes);

                //return $bankTransfer;
                $this->logInfo("bank transfer response log",$bankTransfer);
                $description = is_null($trnx->notes) ? "trnsf" : $trnx->notes;
                $updtdescription = $description."/".$ds[0]."/".$ds[1]."-".$bank->bank_name;
                $dacct2 = $ds[0]."/".$ds[1]."-".$bank->bank_name;

                if ($bankTransfer["status"] == true) {
                   
                    $this->updateTransactionAndAddTrnxcharges(null,$cust->id,null,$charge,'debit','core','0',null,null,null,$trnx->reference_no,
                    $updtdescription,"charges",'approved','10',$usern,'');
                        
                //for saving transaction
                $trnx->approve_by = $usern;
                $trnx->approve_date = Carbon::now();
                $trnx->save();
                
              //for saving transactionGL
                $trnxGL->amount = $trnx->amount;
                $trnxGL->status = "approved";
                $trnxGL->approved_by = $usern;
                $trnxGL->approve_date = Carbon::now();
                $trnxGL->save();

                        $famt = " N".number_format($trnx->amount,2);
                        $dbalamt = " N".number_format($debitCustomer['balance'],2);
                        $bdecs1 =  $updtdescription;
    
                        $msg = "Debit Amt: ".$famt."<br> Desc: ".$bdecs1."<br> Avail Bal: ".$dbalamt."<br> Date:" . date('Y-m-d') . "<br> Ref: " .$trnx->reference_no;
                        $smsmsg = "Debit Amt: ".$famt."\n Desc: ".$bdecs1." \n Avail Bal: ".$dbalamt."\n Date: ".date("Y-m-d")."\n Ref: ".$trnx->reference_no;
                         
                        if($cust->enable_sms_alert){
                        $this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                        }
                    if($cust->enable_email_alert){
                        Email::create([
                            'user_id' => $cust->id,
                            'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert',
                            'message' => $msg,
                            'recipient' => $cust->email,
                        ]);
    
                    Mail::send(['html' => 'mails.sendmail'],[
                        'msg' => $msg,
                        'type' => 'Debit Transaction'
                    ],function($mail)use($getsetvalue,$cust){
                        $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                        $mail->to($cust->email);
                    $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
                });
                    }
           
                    DB::commit();

         return ['status' => 'success', 'msg' => 'Bank Transfer Successful'];
               
      }elseif($bankTransfer["status"] == false){

                 //FAILED TRANSACTION    
                 $this->updateTransactionAndAddTrnxcharges(null,$cust->id,null,$charge,'debit','core','0',null,null,null,$trnx->reference_no,
                 $updtdescription,"charges",'failed','10',$usern,'');
              
              $this->tracktrails('1','1',$usern,'customer','Bank Transfer Failed');
              
            $reverd = $this->ReverseDebitTrnxandcompanyGlAcct($cust->id,$totalAmount,$trnx->amount,$trnx->reference_no,$getsetvalue->getsettingskey('assetmtx'),'m','Transaction reversed','core','trnsfer',$usern,'');
            
            //reverse transfer charges Gl
            if($tglacct->status == '1'){
             $this->gltransaction('deposit',$tglacct,$tchargeamt,null);
            $this->create_saving_transaction_gl(null,$tglacct->id,$cust->branch_id, $tchargeamt,'debit','core',$ref,$this->generatetrnxref('trnxchrg'),'reversed transfer charges','approved',$usern);
            }
           
            //reverse other charges Gl
            if($otherglacct->status == '1'){
             $this->gltransaction('deposit',$otherglacct,$ocharge->amount,null); 
            $this->create_saving_transaction_gl(null,$otherglacct->id,$cust->branch_id, $ocharge->amount,'debit','core',$ref,$this->generatetrnxref('otc'),'reversed others charges fees','approved',$usern);
            }
    
             //reverse saving acct and current acct Gl
             if($cust->account_type == '1'){//saving acct GL
                         
                if($glsavingdacct->status == '1'){
            $this->gltransaction('withdrawal',$glsavingdacct,$totalAmount,null);
            $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $totalAmount,'credit','core',$ref,$this->generatetrnxref('Cr'),'customer credited','approved',$usern);
                }
                
            }elseif($cust->account_type == '2'){//current acct GL
            
                if($glcurrentacct->status == '1'){
                $this->gltransaction('withdrawal',$glcurrentacct,$totalAmount,null);
            $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $totalAmount,'credit','core',$ref,$this->generatetrnxref('Cr'),'customer credited','approved',$usern);
                }
            }
            
            //for saving transaction
                $trnx->approve_by = $usern;
                $trnx->approve_date = Carbon::now();
                $trnx->save();
                
              //for saving transactionGL
                
                $trnxGL->status = "approved";
                $trnxGL->approved_by = $usern;
                $trnxGL->approve_date = Carbon::now();
                $trnxGL->save();

                DB::commit();

                   $msg = "Credit Amt: N".number_format($totalAmount,2)."<br> Desc: Debit Transaction Reversal <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$trnx->reference_no;
                   $smsmsg = "Credit Amt: N".number_format($totalAmount,2)."\n Desc: Debit Transaction Reversal \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date: ".date("Y-m-d")."\n Ref: ".$trnx->reference_no;
                         
                   if($cust->enable_sms_alert){
                   $this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                   }


                if($cust->enable_email_alert){
                 Email::create([
                    'user_id' => $cust->id,
                    'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
                    'message' => $msg,
                    'recipient' => $cust->email,
                ]);
    
                 Mail::send(['html' => 'mails.sendmail'],[
                     'msg' => $msg,
                     'type' => 'Credit Transaction'
                 ],function($mail)use($getsetvalue,$cust){
                     $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                       $mail->to($cust->email);
                     $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
                 });
                }
                
               return ['status' => '0', 'msg' => 'Bank Transfer Failed'];
               
                }
            }elseif($cmdclicked == "declined"){
                //for saving transaction
                $trnx->status = "declined";
                $trnx->is_approve = "1";
                $trnx->approve_by = $usern;
                $trnx->approve_date = Carbon::now();
                $trnx->save();
                
              //for saving transactionGL
                $trnxGL->status = "declined";
                $trnxGL->approved_by = $usern;
                $trnxGL->approve_date = Carbon::now();
                $trnxGL->save();

                DB::commit();

                return ['status' => 'success', 'msg' => 'Transaction Declined'];
            }
            

        }elseif($trnx->transfer_type == "2"){//monnify

            $tcharge = Charge::select('amount')->where('id',$getsetvalue->getsettingskey('transfer_charge'))->first();
            $ocharge = Charge::select('amount')->where('id',$getsetvalue->getsettingskey('othercharges'))->first();
            $monnifycharge = $getsetvalue->getsettingskey('monnifycharge');
    
            $totalAmount = $trnx->amount + $tcharge->amount + $monnifycharge + $ocharge->amount;
            $monify = $trnx->amount + $monnifycharge;
            $charge = $tcharge->amount + $monnifycharge + $ocharge->amount;

            if($cmdclicked == "approve"){

                //verify monnify account balance
           $monfybal = $this->validateMonnifyBalance($this->macctno,$monify);
           //return $monfybal;
           $this->logInfo("monnify balance",$monfybal);
           
           if ($monfybal["status"] == false) {
                return ['status' => '0','msg' => $monfybal['message']];
            }
        
                $chkcres = $this->checkCustomerRestriction($cusid);
                 if($chkcres == true){
             
                     $this->tracktrails('1','1',$usern,'customer','Account Restricted');
                     
                     $this->logInfo("","Customer Account Restricted");
                    
                     return ['status' => false,'msg' => 'Your Account Has Been Restricted. Please contact support'];

                 }

                 $chklien = $this->checkCustomerLienStatus($cusid);
                     if($chklien['status'] == true && $chklien['lien'] == 2){
                         
                         $this->tracktrails('1','1',$usern,'customer','Account has been lien');
                         
                         $this->logInfo("Account lien",$chklien);
                         
                      return ['status' => false,'msg' =>'Your Account Has Been Lien('.$chklien['messages'].')...please contact support'];
                     }
                     
                 $validateuserbalance = $this->validatecustomerbalance($cusid,$totalAmount);
                 if($validateuserbalance["status"] == false){
         
                     $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
                     
                     $this->logInfo("customer balance",$validateuserbalance);
                     
                     return ['status' => false,'msg' => $validateuserbalance["message"]];
                 }
         
                //  $validateTransferAmount = $this->validateTransfer($totalAmount,$getsetvalue->getsettingskey('online_transfer'),$cusid);
         
                //  if ($validateTransferAmount['status'] == false) {
                     
                //      $this->logInfo("online transfer",$validateTransferAmount);
                    
                //      return redirect()->route('approvdata')->with('error',$validateTransferAmount['message']);
                //  }

                 //transfer charges Gl
                 $glaccttr = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('glcharges'))->first();
                    
                    if($glaccttr->status == '1'){
                 $this->gltransaction('withdrawal',$glaccttr,$tcharge->amount,null);
                 $this->create_saving_transaction_gl(null,$glaccttr->id,$cust->branch_id, $tcharge->amount,'credit','core',$ref,$this->generatetrnxref('trnxchrg'),'transfer charges','approved',$usern.'(c)');
                    }
                
                 //other charges Gl
                 $otherglacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('othrchargesgl'))->first();
                 
                 if($otherglacct->status == '1'){
                 $this->gltransaction('withdrawal',$otherglacct,$ocharge->amount,null); 
                 $this->create_saving_transaction_gl(null,$otherglacct->id,$cust->branch_id, $ocharge->amount,'credit','core',$ref,$this->generatetrnxref('otc'),'others charges fees','approved',$usern.'(c)');
                 }
                 
                 $mamtgl = $glacct->account_balance - $monify;
                 $glacct->account_balance = $mamtgl;
                 $glacct->save();
 
                 //customers
                  $mamt = $customeracct2->account_balance - $totalAmount;
                 $customeracct2->account_balance = $mamt;
                 $customeracct2->save();
 

                 //if($cust->account_type == '1'){//saving acct GL
                         
                    if($glsavingdacct->status == '1'){
                     $this->gltransaction('deposit',$glsavingdacct,$totalAmount,null);
                 $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $totalAmount,'debit','core',$ref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                    }
                    
                //  }elseif($cust->account_type == '2'){//current acct GL
                     
                //      if($glcurrentacct->status == '1'){
                //      $this->gltransaction('deposit',$glcurrentacct,$totalAmount,null);
                //  $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $totalAmount,'debit','core',$ref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                //      }
                     
                //  }

                  $ds = explode(',',$trnx->destination_account);
                 
                  $turl = $this->murl."v2/disbursements/single";
                  $bankTransfer = $this->monnifyTranfer($turl,$this->mapikey,$this->msercetkey,$trnx->amount,$trnx->reference_no,
                                                        $trnx->notes,$ds[2],$ds[1],$this->macctno,$ds[0]);
                                                        

                  //return $bankTransf;
                  $this->logInfo("bank transfer response log via monnify",$bankTransfer);
                  $description = is_null($trnx->notes) ? "trnsf" : $trnx->notes;
                  $updtdescription = $description."/".$ds[0]."/".$ds[1]."-".$bankTransfer["responseBody"]["destinationBankName"];
                  $dacct2 = $ds[0]."/".$ds[1]."-".$bankTransfer["responseBody"]["destinationBankName"];

                  if($bankTransfer["responseCode"] == "0"){
                    if($bankTransfer["responseBody"]["status"] == "SUCCESS"){

                        $this->updateTransactionAndAddTrnxcharges(null,$cusid,null,$charge,'debit','core','0',null,null,null,$ref,
                               $updtdescription,"charges",'approved','10',$usern,$dacct2);
                               
                               //for saving transaction
                            $trnx->approve_by = $usern;
                            $trnx->approve_date = Carbon::now();
                            $trnx->save();
                            
                          //for saving transactionGL
                            $trnxGL->amount = $monify;
                            $trnxGL->status = "approved";
                            $trnxGL->approved_by = $usern;
                            $trnxGL->approve_date = Carbon::now();
                            $trnxGL->save();

                            DB::commit();

                       $famt = " N".number_format($totalAmount,2);
                       $dbalamt = " N".number_format($customeracct2->account_balance,2);
                       $bdecs1 =  $updtdescription;
   
                       $smsmsg = "Debit Amt: ".$famt."\n Desc:".$bdecs1." \n Avail Bal: N".$dbalamt."\n Date: ".date("Y-m-d")."\n Ref: ".$ref;
                        if($cust->enable_sms_alert){
                        $this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                        }

                  if($cust->enable_email_alert){
                    $msg = "Debit Amt: ".$famt."<br> Desc: ".$bdecs1."<br> Avail Bal: ".$dbalamt."<br> Date:" . date('Y-m-d') . "<br> Ref: " .$ref;
                    Email::create([
                        'user_id' =>  Auth::user()->id,
                        'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert',
                        'message' => $msg,
                        'recipient' => $cust->email,
                    ]);
           
                  Mail::send(['html' => 'mails.sendmail'],[
                       'msg' => $msg,
                        'type' => 'Debit Transaction'
                   ],function($mail)use($getsetvalue,$cust){
                    $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                    $mail->to($cust->email);
                  $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
              });
                  }

                    return ['status' => 'success','msg' => 'Bank Transfer Successful'];
                          
                 }else{
                            //FAILED TRANSACTION    
                            $this->updateTransactionAndAddTrnxcharges(null, $cusid,null,$charge,'debit','core','0',null,null,null,$ref,
                            $updtdescription,"charges",'failed','10',$usern,$dacct2);
                         
                         $this->tracktrails('1','1',$usern,'customer','Transaction Failed');
   
                       $reverd = $this->ReverseDebitTrnxandcompanyGlAcct($cusid,$totalAmount,$monify,$ref,$getsetvalue->getsettingskey('moniepointgl'),'m','Transaction reversed','core','trnsfer',$usern.'(c)',$dacct2);
                       
                       //reverse transfer charges Gl
                       if($glaccttr->status == '1'){
                        $this->gltransaction('deposit',$glaccttr,$tcharge->amount,null);
                       $this->create_saving_transaction_gl(null,$glaccttr->id,$cust->branch_id, $tcharge->amount,'debit','core',$ref,$this->generatetrnxref('trnxchrg'),'reversed transfer charges','approved',$usern.'(c)');
                       }
                      
                       //reverse other charges Gl
                       if($otherglacct->status == '1'){
                        $this->gltransaction('deposit',$otherglacct,$ocharge->amount,null); 
                       $this->create_saving_transaction_gl(null,$otherglacct->id,$cust->branch_id, $ocharge->amount,'debit','core',$ref,$this->generatetrnxref('otc'),'reversed others charges fees','approved',$usern.'(c)');
                       }
                           
                           //reverse saving acct and current acct Gl
                        //if($cust->account_type == '1'){//saving acct GL
                           
                           if($glsavingdacct->status == '1'){
                       $this->gltransaction('withdrawal',$glsavingdacct,$totalAmount,null);
                       $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $totalAmount,'credit','core',$ref,$this->generatetrnxref('D'),'customer debited','approved',$usern);
                           }
                           
                    //    }elseif($cust->account_type == '2'){//current acct GL
                           
                    //        if($glcurrentacct->status == '1'){
                    //        $this->gltransaction('withdrawal',$glcurrentacct,$totalAmount,null);
                    //    $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $totalAmount,'credit','core',$ref,$this->generatetrnxref('D'),'customer debited','approved',$usern);
                    //        }
                           
                    //    }
                       
                          //for saving transaction
                            $trnx->approve_by = $usern;
                            $trnx->approve_date = Carbon::now();
                            $trnx->save();
                            
                          //for saving transactionGL
                            $trnxGL->status = "approved";
                            $trnxGL->approved_by = $usern;
                            $trnxGL->approve_date = Carbon::now();
                            $trnxGL->save();
                            
                            $smsmsg = "Debit Amt: N".number_format($totalAmount,2)."\n Desc: Debit Transaction Reversal \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date: ".date("Y-m-d")."\n Ref: ".$ref;
                         
                            if($cust->enable_sms_alert){
                            $this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                            }
            
                           if($cust->enable_email_alert){
                            $msg = "Credit Amt: N".number_format($totalAmount,2)."<br> Desc: Debit Transaction Reversal <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$ref;
                            Email::create([
                               'user_id' =>  Auth::user()->id,
                               'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
                               'message' => $msg,
                               'recipient' => $cust->email,
                           ]);
               
                            Mail::send(['html' => 'mails.sendmail'],[
                                'msg' => $msg,
                                'type' => 'Credit Transaction'
                            ],function($mail)use($getsetvalue,$cust){
                                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                                  $mail->to($cust->email);
                                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
                            });
                           }
                
                          return ['status' => false, 'msg' => 'Bank Transfer Failed'];
                          
                     }
                }

            }elseif($cmdclicked == "declined"){
                //for saving transaction
                $trnx->status = "declined";
                $trnx->is_approve = "1";
                $trnx->approve_by = $usern;
                $trnx->approve_date = Carbon::now();
                $trnx->save();
                
              //for saving transactionGL
                $trnxGL->status = "declined";
                $trnxGL->approved_by = $usern;
                $trnxGL->approve_date = Carbon::now();
                $trnxGL->save();

                DB::commit();

                return ['status' => 'success','msg' => 'Transaction Declined'];
            }

        }elseif($trnx->transfer_type == "3"){//nibbspay
            if($cmdclicked == "approve"){

            }elseif($cmdclicked == "declined"){
                //for saving transaction
                $trnx->status = "declined";
                $trnx->is_approve = "1";
                $trnx->approve_by = $usern;
                $trnx->approve_date = Carbon::now();
                $trnx->save();
                
              //for saving transactionGL
                $trnxGL->status = "declined";
                $trnxGL->approved_by = $usern;
                $trnxGL->approve_date = Carbon::now();
                $trnxGL->save();

                return ['status' => 'success','msg' => 'Transaction Declined'];
            }
            

        }elseif($trnx->transfer_type == "4"){//wireless verify

            $tcharge = Charge::select('amount')->where('id',$getsetvalue->getsettingskey('transfer_charge'))->first();
            $ocharge = Charge::select('amount')->where('id',$getsetvalue->getsettingskey('othercharges'))->first();
            $wirelesscharge = 15;
    
            $totalAmount = $trnx->amount + $tcharge->amount + $wirelesscharge + $ocharge->amount - 5;
            $wireless = $trnx->amount + $wirelesscharge;
           
            $charge = $tcharge->amount + $ocharge->amount + $wirelesscharge - 5;

            if($cmdclicked == "approve"){
                  //verify wireless account balance
              $wirelessbal = $this->validateWirelessBalance($wireless);
              //return $monfybal;
              $this->logInfo("wireless balance",$wirelessbal);
              
              if ($wirelessbal["status"] == false) {
                 return response()->json($wirelessbal, 406);
               }
           
                   $chkcres = $this->checkCustomerRestriction($cusid);
                    if($chkcres == true){
                
                        $this->tracktrails('1','1',$usern,'customer','Account Restricted');
                        
                        $this->logInfo("","Customer Account Restricted");
                        
                        return response()->json(['status' => false, 'message' => 'Your Account Has Been Restricted. Please contact support'],406);
                    }

                    $chklien = $this->checkCustomerLienStatus($cusid);
                        if($chklien['status'] == true && $chklien['lien'] == 2){
                            
                            $this->tracktrails('1','1',$usern,'customer','Account has been lien');
                            
                            $this->logInfo("Account lien",$chklien);
                            
                         return response()->json(['status' => false, 'message' => 'Your Account Has Been Lien('.$chklien['messages'].')...please contact support']);
                        }
                        
                    $validateuserbalance = $this->validatecustomerbalance($cusid,$totalAmount);
                    if($validateuserbalance["status"] == false){
            
                        $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
                        
                        $this->logInfo("customer balance",$validateuserbalance);
                        
                        return ['status' => false,'msg' => $validateuserbalance["message"]];
                    }
            
                  
            
                     $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->first();
                    $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->first();
                    
                    //transfer charges Gl
                    $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('glcharges'))->first();
                    
                    if($glacct->status == '1'){
                    $this->gltransaction('withdrawal',$glacct,$tcharge->amount,null);
                    $this->create_saving_transaction_gl(null,$glacct->id,$cust->branch_id, $tcharge->amount,'credit','core',$ref,$this->generatetrnxref('trnxchrg'),'transfer charges','approved',$usern);
                    }
                   
                    //other charges Gl
                    $otherglacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('othrchargesgl'))->first();
                    
                    if($otherglacct->status == '1'){
                        $this->gltransaction('withdrawal',$otherglacct,$ocharge->amount,null); 
                    $this->create_saving_transaction_gl(null,$otherglacct->id,$cust->branch_id, $ocharge->amount,'credit','core',$ref,$this->generatetrnxref('otc'),'others charges fees','approved',$usern);
                    }

                    $wamtgl = $glacct->account_balance - $wireless;
                    $glacct->account_balance = $wamtgl;
                    $glacct->save();
    
                    //customers
                     $wamt = $customeracct2->account_balance - $totalAmount;
                    $customeracct2->account_balance = $wamt;
                    $customeracct2->save();

                   // if($cust->account_type == '1'){//saving acct GL
                       
                       if($glsavingdacct->status == '1'){
                       $this->gltransaction('deposit',$glsavingdacct,$totalAmount,null);
                   $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $totalAmount,'debit','core',$ref,$this->generatetrnxref('D'),'customer debited','approved',$usern);
                       }
                       
                //    }elseif($cust->account_type == '2'){//current acct GL
                   
                //    if($glcurrentacct->status == '1'){
                //        $this->gltransaction('deposit',$glcurrentacct,$totalAmount,null);
                //    $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $totalAmount,'debit','core',$ref,$this->generatetrnxref('D'),'customer debited','approved',$usern);
                //        }
                       
                //    }

                   $ds = explode(',',$trnx->destination_account);

                   $bank = Bank::where('bank_code', $ds[2])->first();

                   $newdescription = !is_null($trnx->notes) ? "From " .$usern : $trnx->notes;
                   
                     $this->logInfo("transfer Url",$this->url."bank-transfer");
                     
                  //wireless verify transfer
                   $bankTransfer = $this->WirelessTransfer($this->apikey,$trnx->amount,$ref,$ds[2],$ds[1],$ds[0],$newdescription);
                
                   //return $bankTransfer;
                  $this->logInfo("bank transfer response log via wireless verify",$bankTransfer);
                  
                   //logInfo($bankTransfer, "Monnify Transfer Response");
                   $description = !is_null($trnx->notes) ? "trnsf" : $trnx->notes;
                   $updtdescription = $description."/".$ds[0]."/".$ds[1]."-".$bank->bank_name;
                   $dacct2 = $ds[0]."/".$ds[1]."-".$bank->bank_name;

                   if($bankTransfer["status"] == "00"){

                    $this->updateTransactionAndAddTrnxcharges(null, $cusid,null,$charge,'debit','core','0',null,null,null,$ref,
                           $updtdescription,"charges",'approved','10',$usern,$dacct2);                       
                      
                              //for saving transaction
                              $trnx->approve_by = $usern;
                              $trnx->approve_date = Carbon::now();
                              $trnx->save();
                              
                            //for saving transactionGL
                              $trnxGL->amount = $wireless;
                              $trnxGL->status = "approved";
                              $trnxGL->approved_by = $usern;
                              $trnxGL->approve_date = Carbon::now();
                              $trnxGL->save();
  
                         $famt = " N".number_format($totalAmount,2);
                         $dbalamt = " N".number_format($customeracct2->account_balance,2);
                         $bdecs1 =  $updtdescription;

                   $msg = "Debit Amt: ".$famt."<br> Desc: ".$bdecs1."<br> Avail Bal: ".$dbalamt."<br> Date:" . date('Y-m-d') . "<br> Ref: " . $ref;
                   $smsmsg = "Debit Amt: ".$famt."\n Desc: ".$bdecs1."\n Avail Bal: ".$dbalamt."\n Date:" . date('Y-m-d') . "\n Ref: " . $ref;
                   
                   if($cust->enable_sms_alert){
                   $this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                   }

                   if($cust->enable_email_alert){
                   Email::create([
                       'user_id' =>  $cust->id,
                       'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert',
                       'message' => $msg,
                       'recipient' => $cust->email,
                   ]);
          
                 Mail::send(['html' => 'mails.sendmail'],[
                      'msg' => $msg,
                       'type' => 'Debit Transaction'
                  ],function($mail)use($getsetvalue,$cust){
                   $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                   $mail->to($cust->email);
                 $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
             });
                  }
                  
                return ['status' => 'success', 'msg' => 'Bank Transfer Successful'];
                      
             }else{
                        //FAILED TRANSACTION    
                        $this->updateTransactionAndAddTrnxcharges(null,$cusid,null,$charge,'debit','core','0',null,null,null,$ref,
                        $updtdescription,"charges",'failed','10',$usern,$dacct2);
                     
                     $this->tracktrails('1','1',$usern,'customer','Transaction Failed');

                   $reverd = $this->ReverseDebitTrnxandcompanyGlAcct(Auth::user()->id,$totalAmount,$wireless,$ref,'10899792','m','Transaction reversed','core','trnsfer',$usern,$dacct2);
                   
                   //reverse transfer charges Gl
                   if($glacct->status == '1'){
                    $this->gltransaction('deposit',$glacct,$tcharge->amount,null);
                   $this->create_saving_transaction_gl(null,$glacct->id,$cust->branch_id, $tcharge->amount,'debit','core',$ref,$this->generatetrnxref('trnxchrg'),'reversed transfer charges','approved',$usern);
                   }
                  
                   //reverse other charges Gl
                   if($otherglacct->status == '1'){
                    $this->gltransaction('deposit',$otherglacct,$ocharge->amount,null); 
                   $this->create_saving_transaction_gl(null,$otherglacct->id,$cust->branch_id, $ocharge->amount,'debit','core',$ref,$this->generatetrnxref('otc'),'reversed others charges fees','approved',$usern);
                   }
                       
                       //reverse saving acct and current acct Gl
                   // if($cust->account_type == '1'){//saving acct GL
                    
                       if($glsavingdacct->status == '1'){
                   $this->gltransaction('withdrawal',$glsavingdacct,$totalAmount,null);
                   $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $totalAmount,'credit','core',$ref,$this->generatetrnxref('D'),'customer debited','approved',$usern);
                       }
                       
                //    }elseif($cust->account_type == '2'){//current acct GL
                   
                //        if($glcurrentacct->status == '1'){
                //        $this->gltransaction('withdrawal',$glcurrentacct,$totalAmount,null);
                //    $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $totalAmount,'credit','core',$ref,$this->generatetrnxref('D'),'customer debited','approved',$usern);
                //        }
                //    }
                   
                   //for saving transaction
                      $trnx->approve_by = $usern;
                      $trnx->approve_date = Carbon::now();
                      $trnx->save();
                              
                    //for saving transactionGL
                      $trnxGL->amount = $wireless;
                      $trnxGL->status = "approved";
                      $trnxGL->approved_by = $usern;
                      $trnxGL->approve_date = Carbon::now();
                      $trnxGL->save();
                          
                      DB::commit();
                      
                          $msg = "Credit Amt: N".number_format($totalAmount,2)."<br> Desc: Debit Transaction Reversal <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$ref;
                          $smsmsg = "Credit Amt: N".number_format($totalAmount,2)."\n Desc: Debit Transaction Reversal \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date: ".date("Y-m-d")."\n Ref: ".$ref;
                         
                          if($cust->enable_sms_alert){
                          $this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                          }

                          if($cust->enable_email_alert){
                        Email::create([
                           'user_id' =>  $cust->id,
                           'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
                           'message' => $msg,
                           'recipient' => $cust->email,
                       ]);
           
                        Mail::send(['html' => 'mails.sendmail'],[
                            'msg' => $msg,
                            'type' => 'Credit Transaction'
                        ],function($mail)use($getsetvalue,$cust){
                            $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                              $mail->to($cust->email);
                            $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
                        });
                          }
            
                      return ['status' => false, 'msg' => 'Bank Transfer Failed'];
                      
                 }

            }elseif($cmdclicked == "declined"){
                //for saving transaction
                $trnx->status = "declined";
                $trnx->is_approve = "1";
                $trnx->approve_by = $usern;
                $trnx->approve_date = Carbon::now();
                $trnx->save();
                
              //for saving transactionGL
                $trnxGL->status = "declined";
                $trnxGL->approved_by = $usern;
                $trnxGL->approve_date = Carbon::now();
                $trnxGL->save();

                DB::commit();

                return ['status' => 'success','msg' => 'Transaction Declined'];
            }

        }elseif($trnx->transfer_type == "cgl"){
            
            $cglded = $glacct->account_balance - $trnxGL->amount;
            $cgladd = $glacct->account_balance + $trnxGL->amount;
           
            if($cmdclicked == "approve"){
                
                $validateuserbalance = $this->validatecustomerbalance($cusid,$trnx->amount);
                if($validateuserbalance["status"] == false){
                    $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
                    $this->logInfo("customer balance",$validateuserbalance);
                    return ['status' => '0', 'msg' => $validateuserbalance["message"]];
                }
                
                $cglammt = $customeracct2->account_balance - $trnx->amount;
                $customeracct2->account_balance = $cglammt;
                $customeracct2->save();

                if(!is_null($cust->exchangerate_id)){
                    $this->checkforeigncurrncy($cust->exchangerate_id,$trnx->amount,$ref,'debit');
               }else{
               // if($cust->account_type == '1'){//saving acct GL
                        
                        if($glsavingdacct->status == '1'){
                    $this->gltransaction('deposit',$glsavingdacct,$trnx->amount,null);
                $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $trnx->amount,'debit','core',$ref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                        }
                        
                // }elseif($cust->account_type == '2'){//current acct GL
                    
                //     if($glcurrentacct->status == '1'){
                //     $this->gltransaction('deposit',$glcurrentacct,$trnx->amount,null);
                // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $trnx->amount,'debit','core',$ref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                //     }
                    
                // }
            }

                if($glacct->gl_type == "asset"){
                   
                         $glacct->account_balance = $cglded;
                       $glacct->save();
                    
                    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an asset account');
                    
                }elseif($glacct->gl_type == "liability"){
                   
                         $glacct->account_balance = $cgladd;
                       $glacct->save();
                    
    
                    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to a liability account');
                    
                
                 }elseif($glacct->gl_type == "capital"){
                    
                         $glacct->account_balance = $cgladd;
                       $glacct->save();
                    
        
                    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to a capital account');
                
                 }elseif($glacct->gl_type == "income"){
                  
                         $glacct->account_balance = $cgladd;
                       $glacct->save();
                    
        
                    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an income account');
                     
                 }elseif($glacct->gl_type == "expense"){
                    
                     $glacct->account_balance = $cglded;
                       $glacct->save();
                     
                    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an expense account');
               
                }
                 //for saving transaction
                 $trnx->status = "approved";
                 $trnx->is_approve = "1";
                 $trnx->approve_by = $usern;
                 $trnx->approve_date = Carbon::now();
                 $trnx->save();
                 
               //for saving transactionGL
                 $trnxGL->status = "approved";
                 $trnxGL->approved_by = $usern;
                 $trnxGL->approve_date = Carbon::now();
                 $trnxGL->save();

                 DB::commit();

                 $de = !is_null($desc) ? $desc : "Debit Transaction";
                 $smsmsg = "Debit Amt: N".number_format($trnx->amount,2)."\n Desc: ".$de." \n Avail Bal: N".number_format($customeracct2->account_balance,2)."\n Date: ".date("Y-m-d")."\n Ref: ".$ref;
                         
                 if($cust->enable_sms_alert){
                 $this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                 }
 
                 if($cust->enable_email_alert){
                            $msg = "Debit Amt: N".number_format($trnx->amount,2)."<br> Desc: ".$de." <br>Avail Bal: N".number_format($customeracct2->account_balance,2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$ref;
                            Email::create([
                               'user_id' =>  Auth::user()->id,
                               'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert',
                               'message' => $msg,
                               'recipient' => $cust->email,
                           ]);
               
                            Mail::send(['html' => 'mails.sendmail'],[
                                'msg' => $msg,
                                'type' => 'Debit Transaction'
                            ],function($mail)use($getsetvalue,$cust){
                                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                                  $mail->to($cust->email);
                                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
                            });
                           }
                           
                 return ['status' => 'success','msg' => 'Transaction Approved successfully'];

            }elseif($cmdclicked == "declined"){
                
                 //for saving transaction
                 $trnx->status = "declined";
                 $trnx->is_approve = "1";
                 $trnx->approve_by = $usern;
                 $trnx->approve_date = Carbon::now();
                 $trnx->save();
                 
               //for saving transactionGL
                 $trnxGL->status = "declined";
                 $trnxGL->approved_by = $usern;
                 $trnxGL->approve_date = Carbon::now();
                 $trnxGL->save();

                 DB::commit();

                 return ['status' => 'success','msg' => 'Transaction Declined'];
            }

        }elseif($trnx->transfer_type == "glc"){
            
            $glcded =  $glacct->account_balance - $trnxGL->amount;
            $glcadd =  $glacct->account_balance + $trnxGL->amount;
            
         if($cmdclicked == "approve"){
            if($glacct->gl_type == "asset"){
       
                $glacct->account_balance = $glcadd;
                $glacct->save();
            
           
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an account');

        
        }elseif($glacct->gl_type == "liability"){
           
            $glacct->account_balance = $glcded;
            $glacct->save();

           
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited liability account');
           
        }elseif($glacct->gl_type == "capital"){
          
            $glacct->account_balance = $glcded;
            $glacct->save();

            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited capital account');
          
        }elseif($glacct->gl_type == "income"){
          
            $glacct->account_balance = $glcded;
            $glacct->save();

            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debit income account');
            
        }elseif($glacct->gl_type == "expense"){
         
            
                $glacct->account_balance = $glcadd;
                $glacct->save();
            

            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited expense account');
        
        }
            
            $ammtglc = $customeracct2->account_balance + $trnx->amount;
            $customeracct2->account_balance = $ammtglc;
            $customeracct2->save();

           
         if(!is_null($cust->exchangerate_id)){
                $this->checkforeigncurrncy($cust->exchangerate_id,$trnx->amount,$ref,'credit');
           }else{
           // if($cust->account_type == '1'){//saving acct GL
                        
            if($glsavingdacct->status == '1'){
                $this->gltransaction('withdrawal',$glsavingdacct,$trnx->amount,null);
            $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $trnx->amount,'credit','core',$ref,$this->generatetrnxref('D'),'customer credited','approved',$usern);
            }
            
            // }elseif($cust->account_type == '2'){//current acct GL
                
            //     if($glcurrentacct->status == '1'){
            //     $this->gltransaction('withdrawal',$glcurrentacct,$trnx->amount,null);
            // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $trnx->amount,'credit','core',$ref,$this->generatetrnxref('D'),'customer credited','approved',$usern);
            //     }
                
            // }
        }

                //for saving transaction
                $trnx->status = "approved";
                  $trnx->is_approve = "1";
                  $trnx->approve_by = $usern;
                  $trnx->approve_date = Carbon::now();
                  $trnx->save();
                  
                //for saving transactionGL
                  $trnxGL->status = "approved";
                  $trnxGL->approved_by = $usern;
                  $trnxGL->approve_date = Carbon::now();
                  $trnxGL->save();

                $this->checkOutstandingCustomerLoan($cust->id,$trnx->amount);//check if customer has an outstanding loan
                
                DB::commit();

                  $de = !is_null($desc) ? $desc : "Credit Transaction";
                  $smsmsg = "Credit Amt: N".number_format($trnx->amount,2)."\n Desc: ".$de." \n Avail Bal: N".number_format($customeracct2->account_balance,2)."\n Date: ".date("Y-m-d")."\n Ref: ".$ref;
                         
                  if($cust->enable_sms_alert){
                    $this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                  }

                  
                if($cust->enable_email_alert){
                            $msg = "Credit Amt: N".number_format($trnx->amount,2)."<br> Desc: ".$de." <br>Avail Bal: N".number_format($customeracct2->account_balance,2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$ref;
                            Email::create([
                               'user_id' =>  Auth::user()->id,
                               'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
                               'message' => $msg,
                               'recipient' => $cust->email,
                           ]);
               
                            Mail::send(['html' => 'mails.sendmail'],[
                                'msg' => $msg,
                                'type' => 'Credit Transaction'
                            ],function($mail)use($getsetvalue,$cust){
                                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                                  $mail->to($cust->email);
                                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
                            });
                           }
                           
                return ['status' =>'success', 'msg' => 'Transaction Approved successfully'];

            }elseif($cmdclicked == "declined"){
                 //for saving transactio
                 $trnx->status = "declined";
                 $trnx->is_approve = "1";
                 $trnx->approve_by = $usern;
                 $trnx->approve_date = Carbon::now();
                 $trnx->save();
                 
               //for saving transactionGL
                 $trnxGL->status = "declined";
                 $trnxGL->approved_by = $usern;
                 $trnxGL->approve_date = Carbon::now();
                 $trnxGL->save();

                 DB::commit();

                 return ['status' => 'success','msg' => 'Transaction Approved successfully'];
            }

        }elseif($trnx->transfer_type == "tcp"){

            if($cmdclicked == "approve"){

                $tcpgl = $glacct->account_balance + $trnxGL->amount;
                $glacct->account_balance = $tcpgl;
                  $glacct->save();

                    $tcpamt = $customeracct2->account_balance + $trnx->amount;
                 $customeracct2->account_balance = $tcpamt;
                $customeracct2->save();
                
              
                
                //for saving transaction  $cust->branch_id 
                $trnx->status = "approved";
                  $trnx->is_approve = "1";
                  $trnx->approve_by = $usern;
                  $trnx->approve_date = Carbon::now();
                  $trnx->save();
                  
                //for saving transactionGL
                  $trnxGL->status = "approved";
                  $trnxGL->approved_by = $usern;
                  $trnxGL->approve_date = Carbon::now();
                  $trnxGL->save();

            if(!is_null($cust->exchangerate_id)){
                    $this->checkforeigncurrncy($cust->exchangerate_id,$trnx->amount,$ref,'credit');
               }else{
                 // if($cust->account_type == '1'){//saving acct GL
                        
                        if($glsavingdacct->status == '1'){
                    $this->gltransaction('withdrawal',$glsavingdacct,$trnx->amount,null);
                $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $trnx->amount,'credit','core',$ref,$this->generatetrnxref('C'),'customer credited','approved',$usern);
                        }
                        
                // }elseif($cust->account_type == '2'){//current acct GL
                    
                //     if($glcurrentacct->status == '1'){
                //     $this->gltransaction('withdrawal',$glcurrentacct,$trnx->amount,null);
                // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $trnx->amount,'credit','core',$ref,$this->generatetrnxref('C'),'customer credited','approved',$usern);
                //     }
                    
                // }
            }

            $this->checkOutstandingCustomerLoan($cust->id,$trnx->amount);//check if customer has an outstanding loan

                DB::commit();

                $de = !is_null($desc) ? $desc : "Credit Transaction";
                $smsmsg = "Credit Amt: N".number_format($trnx->amount,2)."\n Desc: ".$de." \n Avail Bal: N".number_format($customeracct2->account_balance,2)."\n Date: ".date("Y-m-d")."\n Ref: ".$ref;
                         
                if($cust->enable_sms_alert){
                $this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                }


                    if($cust->enable_email_alert){
                            $msg = "Credit Amt: N".number_format($trnx->amount,2)."<br> Desc: ".$de." <br>Avail Bal: N".number_format($customeracct2->account_balance,2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$ref;
                            Email::create([
                               'user_id' =>  Auth::user()->id,
                               'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
                               'message' => $msg,
                               'recipient' => $cust->email,
                           ]);
               
                            Mail::send(['html' => 'mails.sendmail'],[
                                'msg' => $msg,
                                'type' => 'Credit Transaction'
                            ],function($mail)use($getsetvalue,$cust){
                                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                                  $mail->to($cust->email);
                                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
                            });
                           }
                           
                return ['status' => 'success', 'msg' => 'Transaction Approved successfully'];

            }elseif($cmdclicked == "declined"){
                //for saving transaction
                $trnx->status = "declined";
                  $trnx->is_approve = "1";
                  $trnx->approve_by = $usern;
                  $trnx->approve_date = Carbon::now();
                  $trnx->save();

                  //for saving transactionGL
                  $trnxGL->status = "declined";
                  $trnxGL->approved_by = $usern;
                  $trnxGL->approve_date = Carbon::now();
                  $trnxGL->save();

                  DB::commit();

                  return ['status' => 'success','msg' => 'Transaction Declined'];

            }

        }elseif($trnx->transfer_type == "ctp"){

            if($cmdclicked == "approve"){
                
                $validateuserbalance = $this->validatecustomerbalance($cusid,$trnx->amount);
                if($validateuserbalance["status"] == false){
                    $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
                    $this->logInfo("customer balance",$validateuserbalance);
                    return ['status' => '0', 'msg' => $validateuserbalance["message"]];
                }
                
                $ctpamtgl = $glacct->account_balance - $trnxGL->amount;
                $glacct->account_balance = $ctpamtgl;
                $glacct->save();

                //customers
                $ctpamt = $customeracct2->account_balance - $trnx->amount;
                $customeracct2->account_balance = $ctpamt;
                $customeracct2->save();
            
          if(!is_null($cust->exchangerate_id)){
                    $this->checkforeigncurrncy($cust->exchangerate_id,$trnx->amount,$ref,'debit');
            }else{
              //  if($cust->account_type == '1'){//saving acct GL
                        
                  if($glsavingdacct->status == '1'){
                    $this->gltransaction('deposit',$glsavingdacct,$trnx->amount,null);
                $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $trnx->amount,'debit','core',$ref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                  }
                  
                // }elseif($cust->account_type == '2'){//current acct GL
                    
                //     if($glcurrentacct->status == '1'){
                //     $this->gltransaction('deposit',$glcurrentacct,$trnx->amount,null);
                // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $trnx->amount,'debit','core',$ref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                //     }
                    
                // }
            }

              //for saving transaction
              $trnx->status = "approved";
                $trnx->is_approve = "1";
                $trnx->approve_by = $usern;
                $trnx->approve_date = Carbon::now();
                $trnx->save();
                
              //for saving transactionGL
                $trnxGL->status = "approved";
                $trnxGL->approved_by = $usern;
                $trnxGL->approve_date = Carbon::now();
                $trnxGL->save();
                
               DB::commit();

                $de = !is_null($desc) ? $desc : "Debit Transaction";
                $smsmsg = "Debit Amt: N".number_format($trnx->amount,2)."\n Desc: ".$de." \n Avail Bal: N".number_format($customeracct2->account_balance,2)."\n Date: ".date("Y-m-d")."\n Ref: ".$ref;
                         
                if($cust->enable_sms_alert){
                $this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                }

                if($cust->enable_email_alert){
                            $msg = "Debit Amt: N".number_format($trnx->amount,2)."<br> Desc: ".$de." <br>Avail Bal: N".number_format($customeracct2->account_balance,2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$ref;
                            Email::create([
                               'user_id' =>  Auth::user()->id,
                               'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert',
                               'message' => $msg,
                               'recipient' => $cust->email,
                           ]);
               
                            Mail::send(['html' => 'mails.sendmail'],[
                                'msg' => $msg,
                                'type' => 'Debit Transaction'
                            ],function($mail)use($getsetvalue,$cust){
                                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                                  $mail->to($cust->email);
                                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
                            });
                           }
                           
                return['status' => 'success','msg' => 'Transaction Approved successfully'];

            }elseif($cmdclicked == "declined"){
                 //for saving transaction
                 $trnx->status = "declined";
                 $trnx->is_approve = "1";
                 $trnx->approve_by = $usern;
                 $trnx->approve_date = Carbon::now();
                 $trnx->save();
                 
               //for saving transactionGL
                 $trnxGL->status = "declined";
                 $trnxGL->approved_by = $usern;
                 $trnxGL->approve_date = Carbon::now();
                 $trnxGL->save();

                 DB::commit();

                 return ['status' => 'success','msg' => 'Transaction Declined'];

            }

        }elseif($trnx->transfer_type == "deposit"){

            if($cmdclicked == "approve"){

                    $damtgl = $glacct->account_balance + $trnxGL->amount;
                 $glacct->account_balance = $damtgl;
                $glacct->save();
                
                $damt = $customeracct2->account_balance + $trnx->amount;
                $customeracct2->account_balance = $damt;
                   $customeracct2->save();
                
              
           if(!is_null($cust->exchangerate_id)){
                    $this->checkforeigncurrncy($cust->exchangerate_id,$trnx->amount,$ref,'credit');
               }else{
               //if($cust->account_type == '1'){//saving acct GL
                        
                 if($glsavingdacct->status == '1'){
                    $this->gltransaction('withdrawal',$glsavingdacct,$trnx->amount,null);
                $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $trnx->amount,'credit','core',$ref,$this->generatetrnxref('D'),'customer credited','approved',$usern);
                 }
                 
                // }elseif($cust->account_type == '2'){//current acct GL
                    
                //     if($glcurrentacct->status == '1'){
                //     $this->gltransaction('withdrawal',$glcurrentacct,$trnx->amount,null);
                // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $trnx->amount,'credit','core',$ref,$this->generatetrnxref('D'),'customer credited','approved',$usern);
                //     }
                    
                // }
            }
                //for saving transaction
              $trnx->status = "approved";
              $trnx->is_approve = "1";
              $trnx->approve_by = $usern;
              $trnx->approve_date = Carbon::now();
              $trnx->save();
              
            //for saving transactionGL
              $trnxGL->status = "approved";
              $trnxGL->approved_by = $usern;
              $trnxGL->approve_date = Carbon::now();
              $trnxGL->save();
                
              $this->checkOutstandingCustomerLoan($cust->id,$trnx->amount);//check if customer has an outstanding loan
              
              DB::commit();

              $de = !is_null($desc) ? $desc : "Credit Transaction";
              $smsmsg = "Credit Amt: N".number_format($trnx->amount,2)."\n Desc: ".$de." \n Avail Bal: N".number_format($customeracct2->account_balance,2)."\n Date: ".date("Y-m-d")."\n Ref: ".$ref;
                         
              if($cust->enable_sms_alert){
              $this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
              }

                if($cust->enable_email_alert){
                            $msg = "Credit Amt: N".number_format($trnx->amount,2)."<br> Desc: ".$de." <br>Avail Bal: N".number_format($customeracct2->account_balance,2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$ref;
                            Email::create([
                               'user_id' =>  Auth::user()->id,
                               'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
                               'message' => $msg,
                               'recipient' => $cust->email,
                           ]);
               
                            Mail::send(['html' => 'mails.sendmail'],[
                                'msg' => $msg,
                                'type' => 'Credit Transaction'
                            ],function($mail)use($getsetvalue,$cust){
                                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                                  $mail->to($cust->email);
                                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
                            });
                           }
                           
                return ['status' => 'success','msg' => 'Transaction Approved successfully'];

            }elseif($cmdclicked == "declined"){
                 //for saving transaction
                 $trnx->status = "declined";
                 $trnx->is_approve = "1";
                 $trnx->approve_by = $usern;
                 $trnx->approve_date = Carbon::now();
                 $trnx->save();
                 
               //for saving transactionGL
                 $trnxGL->status = "declined";
                 $trnxGL->approved_by = $usern;
                 $trnxGL->approve_date = Carbon::now();
                 $trnxGL->save();

                 DB::commit();

                 return ['status' => 'success','msg' => 'Transaction Declined'];
            }
            
        }elseif($trnx->transfer_type == "withdrawal"){
            if($cmdclicked == "approve"){
                
                $validateuserbalance = $this->validatecustomerbalance($cusid,$trnx->amount);
                if($validateuserbalance["status"] == false){
                    $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
                    $this->logInfo("customer balance",$validateuserbalance);
                    return ['status' => '0', 'msg' => $validateuserbalance["message"]];
                }
                
                $wamtgl = $glacct->account_balance - $trnxGL->amount;
                $glacct->account_balance = $wamtgl;
                $glacct->save();

                //customers
                 $wamt = $customeracct2->account_balance - $trnx->amount;
                $customeracct2->account_balance = $wamt;
                $customeracct2->save();

             if(!is_null($cust->exchangerate_id)){
                 $this->checkforeigncurrncy($cust->exchangerate_id,$trnx->amount,$ref,'debit');
            }else{
                //if($cust->account_type == '1'){//saving acct GL
                        
                        if($glsavingdacct->status == '1'){
                    $this->gltransaction('deposit',$glsavingdacct,$trnx->amount,null);
                $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $trnx->amount,'debit','core',$ref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                        }
                        
                // }elseif($cust->account_type == '2'){//current acct GL
                    
                //     if($glcurrentacct->status == '1'){
                //     $this->gltransaction('deposit',$glcurrentacct,$trnx->amount,null);
                // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $trnx->amount,'debit','core',$ref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                //     }
                    
                // }
             }

                //for saving transaction
              $trnx->status = "approved";
              $trnx->is_approve = "1";
              $trnx->approve_by = $usern;
              $trnx->approve_date = Carbon::now();
              $trnx->save();
              
            //for saving transactionGL
              $trnxGL->status = "approved";
              $trnxGL->approved_by = $usern;
              $trnxGL->approve_date = Carbon::now();
              $trnxGL->save();

              DB::commit();

              $de = !is_null($desc) ? $desc : "Debit Transaction";
              $smsmsg = "Debit Amt: N".number_format($trnx->amount,2)."\n Desc: ".$de." \n Avail Bal: N".number_format($customeracct2->account_balance,2)."\n Date: ".date("Y-m-d")."\n Ref: ".$ref;
                         
            if($cust->enable_sms_alert){
            $this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
            }
            if($cust->enable_email_alert){
                            $msg = "Debit Amt: N".number_format($trnx->amount,2)."<br> Desc: ".$de." <br>Avail Bal: N".number_format($customeracct2->account_balance,2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$ref;
                            Email::create([
                               'user_id' =>  Auth::user()->id,
                               'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert',
                               'message' => $msg,
                               'recipient' => $cust->email,
                           ]);
               
                            Mail::send(['html' => 'mails.sendmail'],[
                                'msg' => $msg,
                                'type' => 'Debit Transaction'
                            ],function($mail)use($getsetvalue,$cust){
                                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                                  $mail->to($cust->email);
                                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
                            });
                           }
                           
                return ['status' => 'success', 'msg' => 'Transaction Approved successfully'];

            }elseif($cmdclicked == "declined"){
                 //for saving transaction
                 $trnx->status = "declined";
                 $trnx->is_approve = "1";
                 $trnx->approve_by = $usern;
                 $trnx->approve_date = Carbon::now();
                 $trnx->save();
                 
               //for saving transactionGL
                 $trnxGL->status = "declined";
                 $trnxGL->approved_by = $usern;
                 $trnxGL->approve_date = Carbon::now();
                 $trnxGL->save();

                 DB::commit();

                 return ['status' => 'success','msg' => 'Transaction Declined'];

            }
            
        }elseif($trnx->transfer_type == "actoac"){
            
            if($cmdclicked == "approve"){

                $validateuserbalance = $this->validatecustomerbalance($cusid,$trnx->amount,$branch);
                if($validateuserbalance["status"] == false){
                    $this->tracktrails(Auth::user()->id,$branch,$usern,'customer',$validateuserbalance["message"]);
                    $this->logInfo("customer balance",$validateuserbalance);
                    return ['status' => '0', 'msg' => $validateuserbalance["message"]];
                }
                
                $wamtgl = $glacct->account_balance - $trnxGL->amount;
                $glacct->account_balance = $wamtgl;
                $glacct->save();

                //customers
                 $wamt = $customeracct2->account_balance - $trnx->amount;
                $customeracct2->account_balance = $wamt;
                $customeracct2->save();

             if(!is_null($cust->exchangerate_id)){
                 $this->checkforeigncurrncy($cust->exchangerate_id,$trnx->amount,$ref,'debit');
            }else{
              //  if($cust->account_type == '1'){//saving acct GL
                        
                        if($glsavingdacct->status == '1'){
                    $this->gltransaction('deposit',$glsavingdacct,$trnx->amount,null);
                $this->create_saving_transaction_gl(Auth::user()->id,$glsavingdacct->id,$cust->branch_id,$trnx->amount,'debit','core',$ref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                        }
                        
                // }elseif($cust->account_type == '2'){//current acct GL
                    
                //     if($glcurrentacct->status == '1'){
                //     $this->gltransaction('deposit',$glcurrentacct,$trnx->amount,null);
                // $this->create_saving_transaction_gl(Auth::user()->id,$glcurrentacct->id,$cust->branch_id,$trnx->amount,'debit','core',$ref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                //     }
                    
                // }
             }

             $ds = explode(',',$trnx->destination_account);

             DB::commit();

              $de = !is_null($desc) ? $desc : "Debit Transaction";
              $smsmsg = "Debit Amt: N".number_format($trnx->amount,2)."\n Desc: ".$de." \n Avail Bal: N".number_format($customeracct2->account_balance,2)."\n Date: ".date("Y-m-d h:ia")."\n Ref: ".$ref;
                         
            if($cust->enable_sms_alert){
            $this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
            }
            if($cust->enable_email_alert){
                            $msg = "Debit Amt: N".number_format($trnx->amount,2)."<br> Desc: ".$de." <br>Avail Bal: N".number_format($customeracct2->account_balance,2)."<br>Date: ".date("Y-m-d h:ia")."<br>Ref: ".$ref;
                            Email::create([
                               'user_id' =>  Auth::user()->id,
                               'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert',
                               'message' => $msg,
                               'recipient' => $cust->email
                           ]);
               
                            Mail::send(['html' => 'mails.sendmail'],[
                                'msg' => $msg,
                                'type' => 'Debit Transaction'
                            ],function($mail)use($getsetvalue,$cust){
                                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                                  $mail->to($cust->email);
                                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
                            });
                           }
                           
                           
                     $this->credit_account_transfer($ds[0],$ds[1],$trnx->amount,$glacct,$trnx->notes,$branch);

                         //for saving transaction
                            $trnx->status = "approved";
                            $trnx->is_approve = "1";
                            $trnx->approve_by = $usern;
                            $trnx->approve_date = Carbon::now();
                            $trnx->save();
                            
                            //for saving transactionGL
                            $trnxGL->status = "approved";
                            $trnxGL->approved_by = $usern;
                            $trnxGL->approve_date = Carbon::now();
                            $trnxGL->save();
                            
                return ['status' => 'success', 'msg' => 'Transaction Approved successfully'];

            }elseif($cmdclicked == "declined"){
                 //for saving transaction
                 $trnx->status = "declined";
                 $trnx->is_approve = "1";
                 $trnx->approve_by = $usern;
                 $trnx->approve_date = Carbon::now();
                 $trnx->save();
                 
               //for saving transactionGL
                 $trnxGL->status = "declined";
                 $trnxGL->approved_by = $usern;
                 $trnxGL->approve_date = Carbon::now();
                 $trnxGL->save();

                 DB::commit();

                 return ['status' => 'success','msg' => 'Transaction Declined'];

            }
            
        }elseif($trnx->transfer_type == "ovd"){//overdraft
            
                $bal = $customeracct2->account_balance;
                $overdftbal = $trnx->amount > $customeracct2->account_balance ? $trnx->amount - $customeracct2->account_balance : $trnx->amount;

            $cglded = $glacct->account_balance - $overdftbal;
            $cgladd = $glacct->account_balance + $overdftbal;
            
            if($cmdclicked == "approve"){

             
                $cglammt = $customeracct2->account_balance - $trnx->amount;
                $customeracct2->account_balance = $cglammt;
                $customeracct2->save();

             if($bal > 0){

                if(!is_null($cust->exchangerate_id)){
                    $this->checkforeigncurrncy($cust->exchangerate_id,$bal,$ref,'debit');
               }else{
                //if($cust->account_type == '1'){//saving acct GL
                        
                        if($glsavingdacct->status == '1'){
                    $this->gltransaction('deposit',$glsavingdacct, $bal,null);
                $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $trnx->amount,'debit','core',$ref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                        }
                        
                // }elseif($cust->account_type == '2'){//current acct GL
                    
                //     if($glcurrentacct->status == '1'){
                //     $this->gltransaction('deposit',$glcurrentacct,$trnx->amount,null);
                // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $trnx->amount,'debit','core',$ref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                //     }
                    
                // }
            }

         }

                if($glacct->gl_type == "asset"){
                   
                    $glacct->account_balance = $cglded;
                       $glacct->save();
                    
                    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an asset account');
                    
                }elseif($glacct->gl_type == "liability"){
                   
                         $glacct->account_balance = $cgladd;
                       $glacct->save();
                    
    
                    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to a liability account');
                    
                
                 }elseif($glacct->gl_type == "capital"){
                    
                         $glacct->account_balance = $cgladd;
                       $glacct->save();
                    
        
                    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to a capital account');
                
                 }elseif($glacct->gl_type == "income"){
                  
                         $glacct->account_balance = $cgladd;
                       $glacct->save();
                    
        
                    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an income account');
                     
                 }elseif($glacct->gl_type == "expense"){
                    
                     $glacct->account_balance = $cglded;
                       $glacct->save();
                     
                    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an expense account');
               
                }
                 //for saving transaction
                 $trnx->status = "approved";
                 $trnx->is_approve = "1";
                 $trnx->approve_by = $usern;
                 $trnx->approve_date = Carbon::now();
                 $trnx->save();
                 
               //for saving transactionGL
                 $trnxGL->status = "approved";
                 $trnxGL->approved_by = $usern;
                 $trnxGL->approve_date = Carbon::now();
                 $trnxGL->save();
                      
                 DB::commit();

                 return ['status' => 'success','msg' => 'Transaction Approved successfully'];

            }elseif($cmdclicked == "declined"){
                 //for saving transaction
                 $trnx->status = "declined";
                 $trnx->is_approve = "1";
                 $trnx->approve_by = $usern;
                 $trnx->approve_date = Carbon::now();
                 $trnx->save();
                 
               //for saving transactionGL
                 $trnxGL->status = "declined";
                 $trnxGL->approved_by = $usern;
                 $trnxGL->approve_date = Carbon::now();
                 $trnxGL->save();

                 DB::commit();

                 return ['status' => 'success','msg' => 'Transaction Declined'];
            }
        }
        
    // }else{
    //         return ['status' => false,'msg' => 'Transaction Already Approved'];
    //     }
        
        // } else {
        //     return ["status" => false, 'msg' => "Please wait. To avoid duplicates Transaction"];
        // }
        
    } catch (LockTimeoutException $e) {

        DB::rollBack();

        $this->logInfo($e->getMessage(), "DB approval Error");
        
        return ['status' => false, 'msg' => $e->getMessage()];

    } finally {
        optional($lock)->release();
    }
        
}

 public function credit_account_transfer($nme,$cid,$amount,$glacct,$desc,$branch){

    DB::beginTransaction();

    $cust = Customer::where('acctno',$cid)->first();

    $customeracct2 = Saving::lockForUpdate()->where('customer_id',$cust->id)->first();
    
    
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;

        $getsetvalue = new Setting();    
    $convrtamt = 0;

    
        $glsavingdacct = GeneralLedger::select('id','status','account_balance','gl_type')->where('gl_code','20993097')->lockForUpdate()->first();
       $glcurrentacct = GeneralLedger::select('id','status','account_balance','gl_type')->where('gl_code','20639526')->lockForUpdate()->first();   
            

    $tref = $this->generatetrnxref('cr');
    
         $runt = $customeracct2->account_balance + $amount;
         $customeracct2->account_balance = $runt;
       $customeracct2->save();
    

        $this->create_saving_transaction(Auth::user()->id,$cust->id,$branch,$amount,'credit','core','0',null,null,null,
                                        null,$tref,$desc,'approved','1','trnsfer',$usern);

    if(!is_null($cust->exchangerate_id)){
       $this->checkforeigncurrncy($cust->exchangerate_id,$amount,$tref,'credit');
    }else{
       // if($cust->account_type == '1'){//saving acct GL
    
            if($glsavingdacct->status == '1'){         
         $this->gltransaction('withdrawal',$glsavingdacct,$amount,null);
     $this->create_saving_transaction_gl(null,$glsavingdacct->id,$cust->branch_id, $amount,'credit','core',$tref,$this->generatetrnxref('D'),'customer credited','approved',$usern);
            } 
            
    //  }elseif($cust->account_type == '2'){//current acct GL
     
    //      if($glcurrentacct->status == '1'){
    //      $this->gltransaction('withdrawal',$glcurrentacct,$amount,null);
    //  $this->create_saving_transaction_gl(null,$glcurrentacct->id,$cust->branch_id, $amount,'credit','core',$tref,$this->generatetrnxref('D'),'customer credited','approved',$usern);
         
    //      }
    //  }

    }

    $this->checkOutstandingCustomerLoan($cust->id,$amount,$branch);//check if customer has an outstanding loan

    $this->tracktrails(Auth::user()->id,$cust->branch_id,$usern,'account transfer','deposited to an account','');

if($glacct->status == '1'){
    $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$cust->branch_id,$amount,'debit','core',$tref,$this->generatetrnxref('tcp'),$desc,'approved',$usern);

     $this->gltransaction('withdrawal',$glacct,$amount,$branch);
}

DB::commit();

$smsmsg = "Credit Amt: N".number_format($amount,2)."\n Desc: ".$desc." \n Avail Bal: ".number_format($customeracct2->account_balance,2)."\n Date: ".date("Y-m-d h:ia")."\n Ref: ".$tref;
                     
if($cust->enable_sms_alert){
$this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
}

if($cust->enable_email_alert){
     $msg =  "Credit Amt: N".number_format($amount,2)."<br> Desc: ".$desc." <br>Avail Bal: N". number_format($customeracct2->account_balance,2)."<br> Date: ".date("Y-m-d h:ia")."<br>Ref: ".$tref;
     Email::create([
            'user_id' => $cust->id,
            'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
            'message' => $msg,
            'recipient' => $cust->email
        ]);

        Mail::send(['html' => 'mails.sendmail'],[
            'msg' => $msg,
            'type' => 'Credit Transaction',
        ],function($mail)use($getsetvalue,$cust){
            $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
             $mail->to($cust->email);
            $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
        });
    }

    DB::rollBack();
        //}
}

    public function validateMonnifyBalance($accountno,$amout){
        $response = [];
        
        $this->logInfo("check balance Url",$this->murl."v2/disbursements/wallet-balance?accountNumber=".$accountno);

        $authbasic = base64_encode($this->mapikey.":".$this->msercetkey);
           $checkbalanace = Http::withHeaders([
               "Authorization" => "Basic ".$authbasic
           ])->get($this->murl."v2/disbursements/wallet-balance?accountNumber=".$accountno)->json();
           
           $this->logInfo("validating monnify balance",$checkbalanace);
           //return $checkbalanace;
      
           if($checkbalanace["responseBody"]["availableBalance"] < $amout){
                $response = ["status" => false, 'message' => "Switcher Error... Please contact support"];
           }else{
                $response = ["status" => true,'message' => "Amount is Valid",];
           }
           return $response;
   }
   
     public function validateWirelessBalance($amout){
    //return $this->url."verification/get-wallet-balance";
     $response = [];
     
     $this->logInfo("check balance Url",$this->url."verification/get-wallet-balance");

        $checkbalanace = Http::withHeaders([
            "ApiKey" => $this->apikey
        ])->get($this->url."verification/get-wallet-balance")->json();
        
        $this->logInfo("validating wireless balance",$checkbalanace);
        //return $checkbalanace;
   
        if($checkbalanace["data"]["balance"] < $amout){
             $response = ["status" => false, 'message' => "Switcher Error... Please contact support"];
        }else{
             $response = ["status" => true,'message' => "Amount is Valid",];
        }
        return $response;
}
    
    public function approve_GLtransactions($ref){
        
   // $getsetvalue = new Setting();
   
   $lock = Cache::lock('appgltrns-'.mt_rand('1111','9999'),3);
   
       if($lock->get()){
           
        DB::beginTransaction();

    $cmdclicked = request()->btnType;

$branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;
        
     $usern = Auth::user()->last_name." ".Auth::user()->first_name;
     
     $trnx = SavingsTransactionGL::where('status','pending')
                                ->where('reference_no',$ref)
                                ->where('slip',null)
                                ->first();

    $trnxGL = SavingsTransactionGL::where('status','pending')
                                    ->where('slip',$trnx->reference_no)
                                    ->first();

 $glacct = GeneralLedger::select('id','status','account_balance','gl_type')->where('id',$trnx->general_ledger_id)->lockForUpdate()->first();
 $glacct2 = GeneralLedger::select('id','status','account_balance','gl_type')->where('id',$trnxGL->general_ledger_id)->lockForUpdate()->first();


    if($cmdclicked == "approve"){
        $glcded =  $glacct->account_balance - $trnx->amount;
        $glcadd =  $glacct->account_balance + $trnx->amount;
      
        if($glacct->gl_type == "asset"){
   
            $glacct->account_balance = $glcadd;
            $glacct->save();
        
       
        $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an account');

    
    }elseif($glacct->gl_type == "liability"){
       
        $glacct->account_balance = $glcded;
        $glacct->save();

       
        $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited liability account');
       
    }elseif($glacct->gl_type == "capital"){
      
        $glacct->account_balance = $glcded;
        $glacct->save();

        $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited capital account');
      
    }elseif($glacct->gl_type == "income"){
      
        $glacct->account_balance = $glcded;
        $glacct->save();

        $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debit income account');
        
    }elseif($glacct->gl_type == "expense"){
        
            $glacct->account_balance = $glcadd;
            $glacct->save();
        

        $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited expense account');
    
    }

    $this->creditGLtrnsx($glacct2,$trnxGL->amount,$branch,$usern);

    $trnx->status = "approved";
    $trnx->approved_by = $usern;
    $trnx->approve_date = Carbon::now();
    $trnx->save();
    
    //for saving transactionGL
    $trnxGL->status = "approved";
    $trnxGL->approved_by = $usern;
    $trnxGL->approve_date = Carbon::now();
    $trnxGL->save();
    
    DB::commit();

    return ['status' => 'success', 'msg' => 'Transaction Approved successfully'];

    }elseif($cmdclicked == "declined"){
            //for saving transactionGL
            $trnx->status = "declined";
            $trnx->approved_by = $usern;
            $trnx->approve_date = Carbon::now();
            $trnx->save();
            
            //for saving transactionGL
            $trnxGL->status = "declined";
            $trnxGL->approved_by = $usern;
            $trnxGL->approve_date = Carbon::now();
            $trnxGL->save();

            DB::commit();

            return ['status' => 'success','msg' => 'Transaction Declined'];
        }
        
        $lock->get();
   }//lock
   
   DB::rollBack();
}

 public function creditGLtrnsx($glacct,$amount,$branch,$usern){
    $glcded =  $glacct->account_balance - $amount;
    $glcadd =  $glacct->account_balance + $amount;
  
    if($glacct->gl_type == "asset"){

        $glacct->account_balance = $glcded;
        $glacct->save();
    
   
    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','credited asset account');


}elseif($glacct->gl_type == "liability"){
   
    $glacct->account_balance = $glcadd;
    $glacct->save();

   
    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','credit liability account');
   
}elseif($glacct->gl_type == "capital"){
  
    $glacct->account_balance = $glcadd;
    $glacct->save();

    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','credit capital account');
  
}elseif($glacct->gl_type == "income"){
  
    $glacct->account_balance = $glcadd;
    $glacct->save();

    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','credit income account');
    
}elseif($glacct->gl_type == "expense"){
    
        $glacct->account_balance = $glcded;
        $glacct->save();
    

    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','credit expense account');

}
 }
 
}//endclass

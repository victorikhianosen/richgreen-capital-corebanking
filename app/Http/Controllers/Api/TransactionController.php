<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traites\AuditTraite;
use App\Http\Traites\SavingTraite;
use App\Http\Traites\UserTraite;
use App\Http\Traites\TransferTraite;
use App\Models\Bank;
use App\Models\Charge;
use App\Models\Customer;
use App\Models\Email;
use App\Models\GeneralLedger;
use App\Models\Saving;
use App\Models\SavingsTransaction;
use App\Models\ProvidusKey;
use App\Models\Setting;
use App\Models\NotificationPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    use SavingTraite; 
    use AuditTraite;
    use UserTraite;
    use TransferTraite;
    
     private $murl, $mapikey,$msercetkey,$macctno;
    
    public function __construct(){

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
        }
    }
    
    public function getAllBanks(){
        $banknames =[];
        $banks = Bank::orderBy('bank_name', 'ASC')->get();
         foreach($banks as $bank){
            array_push($banknames,["bank_name" => $bank["bank_name"],"bank_code" => $bank["bank_code"],"bank_logo" => url('/').'/'.$bank["bank_logo"]]);
        } 
        return response()->json(['status' => true, 'message' => 'Banks Fetched Successfully','data' => $banknames]);
    }
    
     public function verifyBankAccount(Request $r)
    {
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
              
            return response()->json(['status' => true,'message' => 'Bank Account Verified Successfully','data' => $vdata]);
        }else{
            return response()->json(['status' => false,'message' => 'Failed to Verify Bank Account']);
        }
        
    }

    public function get_transactionHistory(){
        $transach = SavingsTransaction::where('customer_id',Auth::user()->id)
                                     ->orderBy('created_at','DESC')->get();

        $this->logInfo("transaction history", $transach);
 
        return response()->json(['status' => true, 'message' => 'Transaction Statement Fetched','data' => $transach]);
    }
    
    public function get_transactionStatement(){
        $transac = SavingsTransaction::where('customer_id',Auth::user()->id)
                            ->whereBetween('created_at',[request()->fromdate,request()->todate])
                            ->orderBy('created_at','DESC')->get();

        return response()->json(['status' => true, 'message' => 'Transaction Statement Fetched','data' => $transac]);
    }

    public function initiateTransaction(Request $request)
    {

        $lock = Cache::lock('initrnx-'.mt_rand('1111','9999'),4);

        if($lock->get()){

        $this->logInfo("initializing transaction",$request->all());
        
        $validation = Validator::make($request->all(), [
            "amount" => "required|numeric|gt:0",
            "transaction_type" => "required|string",
            "description" => "required|string",
            "platform" => "required|string"
        ]);

        if ($validation->fails()) {
            $ra = array("status" => false, "message" => $validation->errors()->all()[0]);
            return response()->json($ra, 406);
        }

       
        if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $request->description)) {
            return response()->json(['status' => false, 'message' => "No special character allowed in narration"],406);
        }

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails('1','1',$usern,'customer','transaction initialized');

        $getsetvalue = new Setting();
        
        $description = $request->description;


        $tcharge = Charge::select('amount')->where('id',$getsetvalue->getsettingskey('transfer_charge'))->first();
        $ocharge = Charge::select('amount')->where('id',$getsetvalue->getsettingskey('othercharges'))->first();
        $monnifycharge = $getsetvalue->getsettingskey('monnifycharge');

        $charge = $tcharge->amount + $monnifycharge + $ocharge->amount;

        $amount = 0;
        $totalAmount = 0;

        $amount = $request->amount;
        $totalAmount = $amount + $charge;

        if ($request->amount > 0) {

            $compbal = $this->validatecompanybalance($totalAmount,"combal");
            if($compbal["status"] == false){
        
                $this->logInfo("validating company balance",$compbal);
            
            return response()->json($compbal,406);
        }
    
            if ($request->transaction_type == "bank") {

$trnxid =  $this->generatetrnxref('bnk');
         //return response()->json(["status" => false, "message" => "We are experiencing downtime; service will be restored soon, Sorry for the inconvenience"], 406);}

                
                    $description = $request->description ?? "You sent payment to ".$request->destination_account;
                
                    $chkcres = $this->checkCustomerRestriction(Auth::user()->id);
                    if($chkcres == true){
                
                        $this->tracktrails('1','1',$usern,'customer','Account Restricted');
            
                          $this->logInfo("","Cutomer Account Restricted");
             
                        return response()->json(['status' => false, 'message' => 'Your Account Has Been Restricted. Please contact support'],406);
                    }
                    
                        $chklien = $this->checkCustomerLienStatus(Auth::user()->id);
                        if($chklien['status'] == true && $chklien['lien'] == 2){
                            
                            $this->tracktrails('1','1',$usern,'customer','Account has been lien');
                            
                             $this->logInfo("validating lien status",$chklien);
                            
                         return response()->json(['status' => false, 'message' => 'Your Account Has Been Lien('.$chklien['messages'].')...please contact support']);
                        }

                 $validateuserbalance = $this->validatecustomerbalance(Auth::user()->id,$totalAmount);
                    if($validateuserbalance["status"] == false){
            
                        $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
            
                             $this->logInfo("Cutomer Account balance",$validateuserbalance);
                             
                        return response()->json($validateuserbalance,406);
                    }

                    $validateTransferAmount = $this->validateTransfer($amount,$getsetvalue->getsettingskey('online_transfer'),Auth::user()->id);
            
                    if ($validateTransferAmount['status'] == false) {
                        
                          $this->logInfo("online transfer",$validateTransferAmount);
                         
                        return response()->json($validateTransferAmount, 400);
                    }

                    $this->create_saving_transaction(null, Auth::user()->id,null,$amount,'debit',$request->platform,'0',null,null,null,null,
                    $trnxid,$description,'pending','2','trnsfer','');
                    
                return response()->json(
                    [
                        "status" => true,
                        'message' => "Transaction initialized",
                        'data' => [
                            'charge' => $charge,
                            'platform' => $request->platform,
                           'transaction_reference' => $trnxid,
                            'transactionTypeId' => $request->transaction_type,
                            'transfer_amount' => $amount,
                            'total' => $totalAmount
                        ]
                    ]
                );


                // return response()->json(["status" => false, 'message' => "Insufficient Balance ", 'data' => $userWallet->balance], 400);
            } elseif ($request->transaction_type == "wallet") {
                
            $trnxid =  $this->generatetrnxref('w');

                $description = $request->description;
    
                 if(Auth::user()->acctno == $request->destination_account){
                return response()->json(['status' => false, 'message' => 'Cannot transfer to self'],406);
            }
            
                $chkcres = $this->checkCustomerRestriction(Auth::user()->id);
                    if($chkcres == true){
                
                        $this->tracktrails('1','1',$usern,'customer','Account Restricted');
            
                         $this->logInfo("","Cutomer Account Restricted");
             
                        return response()->json(['status' => false, 'message' => 'Your Account Has Been Restricted. Please contact support'],406);
                    }
                    
                        $chklien = $this->checkCustomerLienStatus(Auth::user()->id);
                        if($chklien['status'] == true && $chklien['lien'] == 2){
                            
                            $this->tracktrails('1','1',$usern,'customer','Account has been lien');
                            
                            $this->logInfo("validating lien status",$chklien);
                            
                         return response()->json(['status' => false, 'message' => 'Your Account Has Been Lien('.$chklien['messages'].')...please contact support']);
                        }

                 $validateuserbalance = $this->validatecustomerbalance(Auth::user()->id,$totalAmount);
                    if($validateuserbalance["status"] == false){
            
                        $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
            
                             $this->logInfo("Cutomer Account balance",$validateuserbalance);
                        return response()->json($validateuserbalance,406);
                    }

                    $validateTransferAmount = $this->validateTransfer($amount,$getsetvalue->getsettingskey('online_transfer'),Auth::user()->id);
            
                    if ($validateTransferAmount['status'] == false) {
                        
                          $this->logInfo("online transfer",$validateTransferAmount);
                         
                        return response()->json($validateTransferAmount, 400);
                    }

                $this->create_saving_transaction(null, Auth::user()->id,null,$request->amount,'debit',$request->platform,'0',null,null,null,null,
                $trnxid,$description,'pending','2','trnsfer','');


                return response()->json(
                    [
                        "status" => true,
                        'message' => "Transaction initialized",
                        'data' => [
                            'charge' => 0,
                            'platform' => $request->platform,
                            'transaction_reference' => $trnxid,
                            'transactionTypeId' => $request->transaction_type,
                            'transfer_amount' => $amount,
                            'total' => $amount
                        ]
                    ]
                );


                // return response()->json(["status" => false, 'message' => "Insufficient Balance ", 'data' => $userWallet->balance], 400);
            } else {
                return response()->json(["status" => false, 'message' => "Invalid Transaction Type"], 400);
            }
        } else {
            return response()->json(["status" => false, 'message' => "Invalid Transaction Amount"], 400);
        }

        $lock->release();
    }//lock
  }
    
    
    public function transferToBankAccount(Request $request)
    {

        $lock = Cache::lock('bnktrnx-'.mt_rand('1111','9999'),4);
        if($lock->get()){
       // return $request->all();
       $this->logInfo("bank transfer",$request->all());
       
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails('1','1',$usern,'customer','Transfer to Bank Via payout');


        $validation = Validator::make($request->all(), [
            "amount" => "required|numeric|gt:0|max:1000000",
            "destination_account" => "required",
            "transaction_reference" => "required|string",
            "receipient_name" => "required|string",
            "bank_code" => "required",
            "transaction_pin" => "required|numeric|gt:0",
            "platform" => "required|string"
        ]);
        

        if ($validation->fails()) {
            $ra = array("status" => false, "message" => $validation->errors()->all()[0]);
            return response()->json($ra, 406);
        }

        $getsetvalue = new Setting();
        
        $this->saveBeneficiary($request->beneficiary,$request->userid,$request->receipient_name,$request->destination_account,$request->bank_name,$request->bank_code,'bank');

        $description = $request->description;

        $tcharge = Charge::select('amount')->where('id',$getsetvalue->getsettingskey('transfer_charge'))->first();
        $ocharge = Charge::select('amount')->where('id',$getsetvalue->getsettingskey('othercharges'))->first();
        $bankcharger = $getsetvalue->getsettingskey('bankcharge');
        
        $charge = $tcharge->amount + $bankcharger + $ocharge->amount;
        $totalAmount = $request->amount + $charge;
        $tchargeamt = $tcharge->amount + $bankcharger;

       $transaction = SavingsTransaction::where('reference_no', $request->transaction_reference)->where('amount',$request->amount)->first();

        if ($transaction) {
            if($transaction->status == "approved" || $transaction->status == "failed"){
                return response()->json(["status" => false, 'message' => "Transaction has already been completed...Please Initiate Transaction"], 409);
            }else{
           
                $compbal = $this->validatecompanybalance($totalAmount,"combal");
                if($compbal["status"] == false){
            
                    $this->logInfo("validating company balance",$compbal);
                
                return response()->json($compbal,406);
            }

            
               $chkcres = $this->checkCustomerRestriction(Auth::user()->id);
               if($chkcres == true){
           
                   $this->tracktrails('1','1',$usern,'customer','Account Restricted');
                   
                    $this->logInfo("","Customer Account Restricted");
                   
                   return response()->json(['status' => false, 'message' => 'Your Account Has Been Restricted. Please contact support'],406);
               }

               $chklien = $this->checkCustomerLienStatus(Auth::user()->id);
                   if($chklien['status'] == true && $chklien['lien'] == 2){
  
                       
                       $this->tracktrails('1','1',$usern,'customer','Account has been lien');
                       
                        $this->logInfo("Account lien",$chklien);
                       
                    return response()->json(['status' => false, 'message' => 'Your Account Has Been Lien('.$chklien['messages'].')...please contact support']);
                   }
                   
                   $validateuserpin = $this->validatetrnxpin($request->transaction_pin,Auth::user()->id);
                   if($validateuserpin["status"] == false){
           
                       $this->tracktrails('1','1',$usern,'customer',$validateuserpin["message"]);
                       
                       $this->logInfo("Customer pin validation",$validateuserpin);
                       
                       return response()->json($validateuserpin,406);
                   }

                   
               $validateuserbalance = $this->validatecustomerbalance(Auth::user()->id,$totalAmount);
               if($validateuserbalance["status"] == false){
       
                   $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
                   
                   $this->logInfo("customer balance",$validateuserbalance);
                   
                   return response()->json($validateuserbalance,406);
               }
       
               $validateTransferAmount = $this->validateTransfer($request->amount,$getsetvalue->getsettingskey('online_transfer'),Auth::user()->id);
       
               if ($validateTransferAmount['status'] == false) {
                   
                   $this->logInfo("online transfer",$validateTransferAmount);
                   
                   return response()->json($validateTransferAmount, 400);
               }
            
               $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->first();
               $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->first();
               
                    //transfer charges Gl
                    $glaccttrr = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('glcharges'))->first();
                    
                    if($glaccttrr->status == '1'){
                    $this->gltransaction('withdrawal',$glaccttrr,$tchargeamt,null);
                    $this->create_saving_transaction_gl(null,$glaccttrr->id,null, $tchargeamt,'credit',$request->platform,null,$this->generatetrnxref('trnxchrg'),'transfer charges','approved',$usern.'(c)');
                    }
                   
                    //other charges Gl
                    $otherglacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('othrchargesgl'))->first();
                    
                    if($otherglacct->status == '1'){
                    $this->gltransaction('withdrawal',$otherglacct,$ocharge->amount,null); 
                    $this->create_saving_transaction_gl(null,$otherglacct->id,null, $ocharge->amount,'credit',$request->platform,null,$this->generatetrnxref('otc'),'others charges fees','approved',$usern.'(c)');
                    }
         

                   $debitCustomer = $this->DebitCustomerandcompanyGlAcct(Auth::user()->id,$totalAmount,$request->amount,$getsetvalue->getsettingskey('outwardoptiongl'),'asm','Bank Transfer via assetmatix payout',$request->platform,$usern.'(c)');

                   if(Auth::user()->account_type == '1'){//saving acct GL
                    
                    if($glsavingdacct->status == '1'){
                    $this->gltransaction('deposit',$glsavingdacct,$totalAmount,null);
                $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $totalAmount,'debit',$request->platform,null,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
                    }
                    
                }elseif(Auth::user()->account_type == '2'){//current acct GL
                
                if($glcurrentacct->status == '1'){
                    $this->gltransaction('deposit',$glcurrentacct,$totalAmount,null);
                $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $totalAmount,'debit',$request->platform,null,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
                    }
                    
                }

                   if (!$debitCustomer["status"]) {
                        $this->updateTransactionAndAddTrnxcharges(null, Auth::user()->id,null,$charge,'debit',$request->platform,'0',null,null,null,$request->transaction_reference,
                            "failed Transaction","failed Transaction",'failed','10',$usern,'');
                        
                        $this->tracktrails('1','1',$usern,'customer','Bank Transfer Failed');
                        
                    $reverd = $this->ReverseDebitTrnxandcompanyGlAcct(Auth::user()->id,$totalAmount,$request->amount,$request->transaction_reference,$getsetvalue->getsettingskey('outwardoptiongl'),'asm','Transaction reversed',$request->platform,'trnsfer',$usern.'(c)','');
                    
                    //reverse transfer charges Gl
                    if($glaccttrr->status == '1'){
                        $this->gltransaction('deposit',$glaccttrr,$tchargeamt,null);
                    $this->create_saving_transaction_gl(null,$glaccttrr->id,null, $tchargeamt,'debit',$request->platform,null,$this->generatetrnxref('trnxchrg'),'reversed transfer charges','approved',$usern.'(c)');
                    }
                    
                    //reverse other charges Gl
                    if($otherglacct->status == '1'){
                        $this->gltransaction('deposit',$otherglacct,$ocharge->amount,null); 
                    $this->create_saving_transaction_gl(null,$otherglacct->id,null, $ocharge->amount,'debit',$request->platform,null,$this->generatetrnxref('otc'),'reversed others charges fees','approved',$usern.'(c)');
                    }
            
                        //reverse saving acct and current acct Gl
                        if(Auth::user()->account_type == '1'){//saving acct GL
                                    
                        if($glsavingdacct->status == '1'){
                    $this->gltransaction('withdrawal',$glsavingdacct,$totalAmount,null);
                    $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $totalAmount,'credit',$request->platform,null,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
                        }
                        
                    }elseif(Auth::user()->account_type == '2'){//current acct GL
                    
                        if($glcurrentacct->status == '1'){
                        $this->gltransaction('withdrawal',$glcurrentacct,$totalAmount,null);
                    $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $totalAmount,'credit',$request->platform,null,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
                        }
                    }
                     return response()->json(['status' => false, 'message' => $debitCustomer["message"]]);
                   }

                  $bank = Bank::where('bank_code', $request->bank_code)->first();

                   $newdescription = empty($request->description) ? "From " .$usern : $request->description;
                   
                   $url= env('ASSETMATRIX_BASE_URL')."banktransfer-payout";
                   
                   $bankTransfer = $this->bankTransferviaPayout($url,$request->amount,$request->destination_account,$request->bank_code,env('SETTLEMENT_ACCOUNT_USERNAME'),$request->transaction_reference,$newdescription);

                   //return $bankTransfer;
                   $this->logInfo("bank transfer response log",$bankTransfer);
                  
                   //logInfo($bankTransfer, "Monnify Transfer Response");
                   $description = empty($request->description) ? "trnsf" : $request->description;
                   $updtdescription = $description."/".$request->receipient_name."/".$request->destination_account."-".$bank->bank_name;
                   
               if ($bankTransfer["status"] == true) {
                   
                  //companybal
                  $this->debitcreditCompanyBalance($request->amount,"debit","combal");

                $this->updateTransactionAndAddTrnxcharges(null, Auth::user()->id,null,$charge,'debit',$request->platform,'0',null,null,null,$request->transaction_reference,
                $updtdescription,"charges",'approved','10',$usern,'');
            
           
                    $famt = " N".number_format($request->amount,2);
                    $dbalamt = " N".number_format($debitCustomer['balance'],2);
                    $bdecs1 =  $updtdescription;

                    $msg = "Debit Amt: ".$famt."<br> Desc: ".$bdecs1."<br> Avail Bal: ".$dbalamt."<br> Date:" . date('Y-m-d') . "<br> Ref: " . $request->transaction_reference;
                
                    $smsmsg = "Debit Amt: ".$famt."\n Desc: ".$bdecs1." \n Avail Bal: ".$dbalamt."\n Date: ".date("Y-m-d")."\n Ref: ".$request->transaction_reference;
                         
                    if(Auth::user()->enable_sms_alert){
                    $this->sendSms(Auth::user()->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                    }

                if(Auth::user()->enable_email_alert){
                    Email::create([
                        'user_id' =>  Auth::user()->id,
                        'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert',
                        'message' => $msg,
                        'recipient' => Auth::user()->email,
                    ]);

                Mail::send(['html' => 'mails.sendmail'],[
                    'msg' => $msg,
                        'type' => 'Debit Transaction'
                ],function($mail)use($getsetvalue){
                    $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                    $mail->to(Auth::user()->email);
                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
            });
                }
       
     return response()->json(['status' => true, 'message' => 'Bank Transfer Successful']);
           
  }elseif($bankTransfer["status"] == false){
             //FAILED TRANSACTION    
             $this->updateTransactionAndAddTrnxcharges(null, Auth::user()->id,null,$charge,'debit',$request->platform,'0',null,null,null,$request->transaction_reference,
             $updtdescription,"charges",'failed','10',$usern,'');
          
          $this->tracktrails('1','1',$usern,'customer','Bank Transfer Failed');
          
        // $reverd = $this->ReverseDebitTrnxandcompanyGlAcct(Auth::user()->id,$totalAmount,$request->amount,$request->transaction_reference,$getsetvalue->getsettingskey('outwardoptiongl'),'asm','Transaction reversed',$request->platform,'trnsfer',$usern.'(c)','');
        
        //reverse transfer charges Gl
        if($glaccttrr->status == '1'){
         $this->gltransaction('deposit',$glaccttrr,$tchargeamt,null);
        $this->create_saving_transaction_gl(null,$glaccttrr->id,null, $tchargeamt,'debit',$request->platform,null,$this->generatetrnxref('trnxchrg'),'reversed transfer charges','approved',$usern.'(c)');
        }
       
        //reverse other charges Gl
        if($otherglacct->status == '1'){
         $this->gltransaction('deposit',$otherglacct,$ocharge->amount,null); 
        $this->create_saving_transaction_gl(null,$otherglacct->id,null, $ocharge->amount,'debit',$request->platform,null,$this->generatetrnxref('otc'),'reversed others charges fees','approved',$usern.'(c)');
        }

         //reverse saving acct and current acct Gl
         if(Auth::user()->account_type == '1'){//saving acct GL
                     
            if($glsavingdacct->status == '1'){
        $this->gltransaction('withdrawal',$glsavingdacct,$totalAmount,null);
        $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $totalAmount,'credit',$request->platform,null,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
            }
            
        }elseif(Auth::user()->account_type == '2'){//current acct GL
        
            if($glcurrentacct->status == '1'){
            $this->gltransaction('withdrawal',$glcurrentacct,$totalAmount,null);
        $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $totalAmount,'credit',$request->platform,null,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
            }
        }

        //        $msg = "Credit Amt: N".number_format($totalAmount,2)."<br> Desc: Debit Transaction Reversal <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$request->transaction_reference;
            
        //        $smsmsg = "Debit Amt: N".number_format($totalAmount,2)."\n Desc: Debit Transaction Reversal \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date: ".date("Y-m-d")."\n Ref: ".$request->transaction_reference;
                         
        //        if(Auth::user()->enable_sms_alert){
        //        $this->sendSms(Auth::user()->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
        //        }

        //     if(Auth::user()->enable_email_alert){
        //      Email::create([
        //         'user_id' =>  Auth::user()->id,
        //         'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
        //         'message' => $msg,
        //         'recipient' => Auth::user()->email,
        //     ]);

        //      Mail::send(['html' => 'mails.sendmail'],[
        //          'msg' => $msg,
        //          'type' => 'Credit Transaction'
        //      ],function($mail)use($getsetvalue){
        //          $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
        //            $mail->to(Auth::user()->email);
        //          $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
        //      });
        //     }
            
           return response()->json(['status' =>false, 'message' => 'Bank Transfer Failed'], 406);
           
      
               }else{
                       
                    return response()->json(['status' => false, 'message' => "Transaction Completed"], 406);
                }
            }
            
        } else {
            return response()->json(["status" => false, 'message' => "Invalid Transaction Reference,Please Reinitiate Transaction"], 400);
        }

        $lock->release();
    }//lock
 }

    public function transferToWalletAccount(Request $request)
    {
        $lock = Cache::lock('walltnx-'.mt_rand('1111','9999'),4);

    if($lock->get()){

         $this->logInfo("wallet transfer",$request->all());
       
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails('1','1',$usern,'customer','Transfer to Wallet');


        $validation = Validator::make($request->all(), [
            "amount" => "required|numeric|gt:0|max:1000000",
            "destination_account" => "required",
            "transaction_reference" => "required|string",
           "receipient_name" => "required|string",
            "transaction_pin" => "required|numeric|gt:0",
            "platform" => "required|string"
        ]);
        

        if ($validation->fails()) {
            $ra = array("status" => false, "message" => $validation->errors()->all()[0]);
            return response()->json($ra, 406);
        }

        $getsetvalue = new Setting();
        
        $this->saveBeneficiary($request->beneficiary,Auth::user()->id,$request->receipient_name,$request->destination_account,null,null,'wallet');
 
        $description = empty($request->description) ? "trnf" : $request->description;

$transaction = SavingsTransaction::where('reference_no', $request->transaction_reference)->where('amount',$request->amount)->first();

if ($transaction) {
    if($transaction->status == "approved" || $transaction->status == "failed"){
        return response()->json(["status" => false, 'message' => "Transaction has already been completed...Please Initiate Transaction"], 409);
    }else{
        
         if(Auth::user()->acctno == $request->destination_account){
                return response()->json(['status' => false, 'message' => 'Cannot transfer to self'],406);
            }
            
            $compbal = $this->validatecompanybalance($request->amount,"combal");
            if($compbal["status"] == false){
        
                $this->logInfo("validating company balance",$compbal);
            
            return response()->json($compbal,406);
        }
    
        $chkcres = $this->checkCustomerRestriction(Auth::user()->id);
                    if($chkcres == true){
                
                        $this->tracktrails('1','1',$usern,'customer','Account Restricted');
                        
                         $this->logInfo("","Customer Account Restricted");
                        
                        return response()->json(['status' => false, 'message' => 'Your Account Has Been Restricted. Please contact support'],406);
                    }

                    $chklien = $this->checkCustomerLienStatus(Auth::user()->id);
                        if($chklien['status'] == true && $chklien['lien'] == 2){
       
                            
                            $this->tracktrails('1','1',$usern,'customer','Account has been lien');
                            
                             $this->logInfo("Account lien",$chklien);
                            
                         return response()->json(['status' => false, 'message' => 'Your Account Has Been Lien('.$chklien['messages'].')...please contact support']);
                        }
                        
                        $validateuserpin = $this->validatetrnxpin($request->transaction_pin,Auth::user()->id);
                        if($validateuserpin["status"] == false){
                
                            $this->tracktrails('1','1',$usern,'customer',$validateuserpin["message"]);
                            
                             $this->logInfo("Customer pin validation",$validateuserpin);
                             
                            return response()->json($validateuserpin,406);
                        }

                        
                    $validateuserbalance = $this->validatecustomerbalance(Auth::user()->id,$request->amount);
                    if($validateuserbalance["status"] == false){
            
                        $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
                        
                         $this->logInfo("customer balance",$validateuserbalance);
                        
                        return response()->json($validateuserbalance,406);
                    }
            
                    $validateTransferAmount = $this->validateTransfer($request->amount,$getsetvalue->getsettingskey('online_transfer'),Auth::user()->id);
            
                    if ($validateTransferAmount['status'] == false) {
                        
                         $this->logInfo("online transfer",$validateTransferAmount);
                        
                        return response()->json($validateTransferAmount, 400);
                    }

            $updtdescription = $description."/".$request->receipient_name."/".$request->destination_account;
            
             
            $debitCustomer = $this->DebitCustomerandcompanyGlAcct(Auth::user()->id,$request->amount,$request->amount,'10733842','w',$updtdescription,$request->platform,$usern.'(c)');
                


             $this->logInfo("debit customer response",$debitCustomer);
            
            if ($debitCustomer["status"]==true) {
             
            //companybal
            $this->debitcreditCompanyBalance($request->amount,"debit","combal");

            // $bank = banks::where('bank_code', $request->bank_code)->first();
            if ($debitCustomer["status"]==false && $debitCustomer["glstatus"]==1) {
                 $customeracctbal = Saving::where('customer_id',Auth::user()->id)->first();
                  $customeracctbal->account_balance += $request->amount;
                  $customeracctbal->save();
                  
                   $this->updateTransactionAndAddTrnxcharges(null, Auth::user()->id,null,0,'credit',$request->platform,'0',null,null,null,$request->transaction_reference,
            $updtdescription,"debit reversal",'approved','10',$usern.'(c)','');
            
                   return response()->json(["status" => false, 'message' => $debitCustomer["message"]], 400);
            }

            $this->updateTransactionAndAddTrnxcharges(null, Auth::user()->id,null,0,'debit',$request->platform,'0',null,null,null,$request->transaction_reference,
            $updtdescription,"charges",'approved','10',$usern.'(c)','');
    

            $msg =  "Debit Amt: N".number_format($request->amount,2)."<br> Desc: ".$updtdescription." <br>Avail Bal: N". number_format($debitCustomer["balance"],2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$request->transaction_reference;
            
            $smsmsg = "Debit Amt: N".number_format($request->amount,2)."\n Desc: ".$updtdescription." \n Avail Bal: N".number_format($debitCustomer["balance"],2)."\n Date: ".date("Y-m-d")."\n Ref: ".$request->transaction_reference;
                         
               if(Auth::user()->enable_sms_alert){
               $this->sendSms(Auth::user()->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
               }
            
            if(Auth::user()->enable_email_alert){
         Email::create([
                'user_id' => Auth::user()->id,
                'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
                'message' => $msg,
                'recipient' => Auth::user()->email,
            ]);

            Mail::send(['html' => 'mails.sendmail'],[
                'msg' => $msg,
                'type' => 'Debit Transaction'
            ],function($mail)use($getsetvalue){
                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                 $mail->to(Auth::user()->email);
                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
            });
            }
            
            $this->credit_account_transfer($request,$request->destination_account,'10733842',$request->transaction_reference);

                return response()->json(["status" => true, 'message' => "Wallet Transfer Successful", 'data' => ['balance' => $debitCustomer['balance']]]);
            } else {
                
                 $this->updateTransactionAndAddTrnxcharges(null, Auth::user()->id,null,0,'debit',$request->platform,'0',null,null,null,$request->transaction_reference,
                         $updtdescription,"failed Transaction",'failed','10',$usern.'(c)','');
                         
                  $customeracctbal = Saving::where('customer_id',Auth::user()->id)->first();
                  $customeracctbal->account_balance += $request->amount;
                  $customeracctbal->save();
                  
                  $this->create_saving_transaction(null,Auth::user()->id,1,$request->amount,
                 'credit',$request->platform,'0',null,null,null,null, $request->transaction_reference,'debit reversal','approved','4','trnsfer',$usern);
            
                return response()->json(["status" => false, 'message' => "Wallet Transfer failed"], 400);
            }
        }
     }else {
          return response()->json(["status" => false, 'message' => "Invalid Transaction Reference,Please Reinitiate Transaction"], 400);
         }

         $lock->release();
        }//lock
    }

    public function credit_account_transfer($r,$acctno,$glacct,$trx){

        $cust = Customer::where('acctno',$acctno)->first();
        $customeracct2 = Saving::where('customer_id', $cust->id)->first();
        
         $usern = Auth::user()->last_name." ".Auth::user()->first_name;

         $desc =  "You recieve payment from ".$usern." - ".$acctno;

        $getsetvalue = new Setting();
        
        if($customeracct2->account_balance <= 0){
            $customeracct2->account_balance = $r->amount;
           $customeracct2->save();
        }else{
             $customeracct2->account_balance += $r->amount;
           $customeracct2->save();
        }
       

        $this->create_saving_transaction(Auth::user()->id,$cust->id,null,$r->amount,
        'credit',$r->platform,'0',$r->slipno,null,null,null,$trx,$desc,'approved','1','trnsfer',$usern);

        $glacctc = GeneralLedger::select('id','status','account_balance')->where('gl_code',$glacct)->first();
        
        $this->tracktrails(Auth::user()->id,null,$usern,'wallet account transfer','deposited to an account');

if($glacctc->status == '1'){
        $this->gltransaction('withdrawal',$glacctc,$r->amount,null);

        $this->create_saving_transaction_gl(Auth::user()->id,$glacctc->id,null,$r->amount,'debit',$r->platform,null,$this->generatetrnxref('w'),$desc,'approved',$usern);
}

         $msg =  "Credit Amt: N".number_format($r->amount,2)."<br> Desc: ".$desc." <br>Avail Bal: N". number_format($customeracct2->account_balance,2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trx;
         
         $smsmsg = "Credit Amt: N".number_format($r->amount,2)."\n Desc: ".$desc." \n Avail Bal: N".number_format($customeracct2->account_balance,2)."\n Date: ".date("Y-m-d")."\n Ref: ".$trx;
                         
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
    
    }

    public function save_notification_payload(Request $r){
        $this->logInfo("credit customer response",$r->all());
        
          NotificationPayload::create([
              'body' => json_encode($r->all())
          ]);
          
          $getsetvalue = new Setting();

          $customer = Customer::where('acctno',$r->accountNumber)->first();

          $branch = !is_null($customer->branch_id) ? $customer->branch_id : null;

          $trxref = $r->sessionId;
          
            $chekclosed = $this->checkClosedCustomer($customer->id);
            if($chekclosed == true){
                $this->logInfo("customer account is closed","");

                return response()->json(["requestSuccessful" => true,"sessionId" => $trxref, "responseMessage" => "rejected transaction", "responseCode" => "02"]);

            }

          $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->first();
          $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->first();
           $amexpenseglacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','50792058')->first();//am expenseGL
           
          
         if($getsetvalue->getsettingskey('enable_virtual_ac') == '1' && $getsetvalue->getsettingskey('inwardoption') == "1"){
             $provkey = ProvidusKey::first();

              $seetlid = $r->settlementId;
              
           $transaction = SavingsTransaction::where('slip',$seetlid)->where('amount',$r->transactionAmount)->first();

             $authSignature = $r->header('X-Auth-Signature');
             
          if($authSignature == $provkey->signature_key){
        
            if($transaction){

                return response()->json(["requestSuccessful"=> true,"sessionId" => $trxref,"responseMessage" => "Duplicate Transaction", "responseCode" => "01"]);
       
            }else{
                
                if(empty($customer)){
                  return response()->json(["requestSuccessful" => true,"sessionId" => $trxref, "responseMessage" => "rejected transaction", "responseCode" => "02"]);
                }

                
                $desc = $r->tranRemarks == "" ? "inward transaction" : $r->tranRemarks;

            $customeracct = $this->CreditCustomerAccount($customer->id,$r->settledAmount,$getsetvalue->getsettingskey('inwardoptiongl'),$trxref,$seetlid,$desc,$branch);
           
            $this->checkOutstandingCustomerLoan($customer->id,$r->amount);//check if customer has an outstanding loan
                
            if($customer->account_type == '1'){//saving acct GL
            
                if($glsavingdacct->status == '1'){
                $this->gltransaction('withdrawal',$glsavingdacct,$r->settledAmount,null);
            $this->create_saving_transaction_gl(null,$glsavingdacct->id,$customer->branch_id, $r->settledAmount,'credit',null,null,$this->generatetrnxref('CR'),'customer credited','approved',null);
                }
                
            }elseif($customer->account_type == '2'){//current acct GL
            
            if($glcurrentacct->status == '1'){
                $this->gltransaction('withdrawal',$glcurrentacct,$r->settledAmount,null);
            $this->create_saving_transaction_gl(null,$glcurrentacct->id,$customer->branch_id, $r->settledAmount,'credit',null,null,$this->generatetrnxref('CR'),'customer credited','approved',null);
                }
                
            }
  
            //  if($amexpenseglacct->status == '1'){
            //   $this->gltransaction('withdrawal',$amexpenseglacct,$r->tranxfee,null);
            //   $this->create_saving_transaction_gl(null,$amexpenseglacct->id,null, $r->tranxfee,'debit',null,null,$this->generatetrnxref('D'),'debit asset matrix expense','approved',null);
            //   }
            
            //   $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('assetmtx'))->first();
  
            //   if($glacct->status == '1'){
            //       $this->gltransaction('deposit',$glacct,$r->tranxfee,null);
            //         $this->create_saving_transaction_gl(null,$glacct->id,null,$r->tranxfee,'credit',null,null,$this->generatetrnxref('C'),'inward Tranx expenses','approved',null); 
            //         }
                    
            $msg =  "Credit Amt: N".number_format($r->settledAmount,2)."<br> Desc: ".$desc." <br>Avail Bal: N". number_format($customeracct['balance'],2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trxref;
            $smsmsg = "Credit Amt: N".number_format($r->settledAmount,2)."\n Desc: ".$desc." \n Avail Bal: N".number_format($customeracct['balance'],2)."\n Date: ".date("Y-m-d")."\n Ref: ".$trxref;
                         
               if($customer->enable_sms_alert){
               $this->sendSms($customer->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
               }

            if($customer->enable_email_alert){
              Email::create([
                     'user_id' => $customer->id,
                     'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
                     'message' => $msg,
                     'recipient' => $customer->email,
                 ]);
     
                 Mail::send(['html' => 'mails.sendmail'],[
                     'msg' => $msg,
                     'type' => 'Credit Transaction'
                 ],function($mail)use($getsetvalue,$customer){
                     $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                      $mail->to($customer->email);
                     $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
                 });
              }
              
              
              return response()->json([ "requestSuccessful"=> true,"responseMessage" => "success", "responseCode" => "00"]);
            }
            
          }else{
             return response()->json([ "requestSuccessful"=> false,"responseMessage" => "Invalid Auth Signation Format", "responseCode" => "02"]);
          }

         }elseif($getsetvalue->getsettingskey('enable_virtual_ac') == '1' && $getsetvalue->getsettingskey('inwardoption') == "2"){

            $desc = $r->narration == "" ? "inward transaction" : $r->narration;
            
             $customeracct = $this->CreditCustomerAccount($customer->id,$r->amount,$getsetvalue->getsettingskey('inwardoptiongl'),$trxref,null,$desc,$branch);
         
             $this->checkOutstandingCustomerLoan($customer->id,$r->amount);//check if customer has an outstanding loan
                    
          if($customer->account_type == '1'){//saving acct GL
          
              if($glsavingdacct->status == '1'){
              $this->gltransaction('withdrawal',$glsavingdacct,$r->amount,null);
          $this->create_saving_transaction_gl(null,$glsavingdacct->id,$customer->branch_id, $r->amount,'credit',null,null,$this->generatetrnxref('CR'),'customer credited','approved',null);
              }
              
          }elseif($customer->account_type == '2'){//current acct GL
          
          if($glcurrentacct->status == '1'){
              $this->gltransaction('withdrawal',$glcurrentacct,$r->amount,null);
          $this->create_saving_transaction_gl(null,$glcurrentacct->id,$customer->branch_id, $r->amount,'credit',null,null,$this->generatetrnxref('CR'),'customer credited','approved',null);
              }
              
          }

                  if($amexpenseglacct->status == '1'){
            $this->gltransaction('withdrawal',$amexpenseglacct,$r->tranxfee,null);
            $this->create_saving_transaction_gl(null,$amexpenseglacct->id,$customer->branch_id, $r->tranxfee,'debit',null,null,$this->generatetrnxref('D'),'debit asset matrix expense','approved',null);
            }
          
          //substract tranxfee from GL 10897866
            $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','10897866')->first();

            if($glacct->status == '1'){
                $this->gltransaction('deposit',$glacct,$r->tranxfee,null);
                  $this->create_saving_transaction_gl(null,$glacct->id,$customer->branch_id,$r->tranxfee,'credit',null,null,$this->generatetrnxref('C'),'inward Tranx expenses','approved',null); 
                  }
                  
          $msg =  "Credit Amt: N".number_format($r->amount,2)."<br> Desc: ".$desc." <br>Avail Bal: N". number_format($customeracct['balance'],2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trxref;
          $smsmsg = "Credit Amt: N".number_format($r->amount,2)."\n Desc: ".$desc." \n Avail Bal: N".number_format($customeracct['balance'],2)."\n Date: ".date("Y-m-d")."\n Ref: ".$trxref;
                         
          if($customer->enable_sms_alert){
          $this->sendSms($customer->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
          }

          if($customer->enable_email_alert){
            Email::create([
                   'user_id' => $customer->id,
                   'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
                   'message' => $msg,
                   'recipient' => $customer->email,
               ]);
   
               Mail::send(['html' => 'mails.sendmail'],[
                   'msg' => $msg,
                   'type' => 'Credit Transaction'
               ],function($mail)use($getsetvalue,$customer){
                   $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                    $mail->to($customer->email);
                   $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
               });
       }
         }


      }

    public function verifyWalletAccount(request $r){
        $this->logInfo("verifying wallet account",$r->all());

        $validation = Validator::make($r->all(), [
            "account_number" => 'required',
        ]);
        if ($validation->fails()) {
            $ra = array("status" => false, "message" => $validation->errors()->all()[0]);
            return response()->json($ra, 406);
        }

        $cust = Customer::where('acctno',$r->account_number)->first();

        if($cust){
            if($cust->id == Auth::user()->id){
                return response()->json(['status' => false,'message' => "Cannot Transfer to Self",]);
            }
            
        $ver =   ['status' => true,'message' => "Account Verified Successfully",'data' => ['first_name' => $cust->first_name,'last_name' => $cust->last_name,]];

             $this->logInfo("verified",$ver);
 
            return response()->json($ver);
        }else{
            return response()->json(['status' => false, 'message' => "Failed to verify Wallet account",]);
        }
        
    }

    public function bvn_verify(Request $r){
        $validate = Validator::make($r->all(),[
            'bvn' => ['required','numeric','digits:11']
        ]);

        if($validate->fails()){
            return response()->json(['status' => false, 'message' =>  $validate->errors()->all()[0]]);
        }

        $getsetvalue = new Setting();

        $url= $getsetvalue->getsettingskey('bvnroute') == '1' ? env('ASSETMATRIX_BASE_URL')."verification" : env('WIRELESS_URL')."verification";
        $payload = $getsetvalue->getsettingskey('bvnroute') == '1' ? ["verification_number" => $r->bvn,"verification_type" => "bvn"] : [ "type" => "bvn","validation_number" => $r->bvn];
        $headers = $getsetvalue->getsettingskey('bvnroute') == '1' ? ["PublicKey" => env('PUBLIC_KEY'),"EncryptKey" => env('ENCRYPT_KEY'),"Content-Type" => "application/json"] : [ "Authorization" => "Bearer ".env('WIRELESS_API_KEY'), "Content-Type" => "application/json"];
     
        $response = Http::withHeaders($headers)->post($url,$payload)->json();
      

        if($response['status'] == true || $response['status'] == "00"){
            return response()->json(["status" => true,"message" => "BVN Verified Successfully", "data" => $response['data']]);
        }else {
            return response()->json(["status" => false, "message" => "BVN Verification Failed"]);
        }
    }
}//endclass

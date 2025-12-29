<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traites\AuditTraite;
use App\Http\Traites\SavingTraite;
use App\Http\Traites\TransferTraite;
use App\Http\Traites\UserTraite;
use App\Models\Bank;
use App\Models\Charge;
use App\Models\Email;
use App\Models\GeneralLedger;
use App\Models\SavingsTransaction;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class WirelessController extends Controller
{
    use SavingTraite;
    use AuditTraite;
    use UserTraite;
    use TransferTraite;
    
    private $url;
    private $apikey;
    private $acctno;
    public function __construct()
    {
        if(env('APP_MODE') == "live"){
             $this->url = env('WIRELESS_URL');
            $this->apikey = env('WIRELESS_API_KEY');
            $this->acctno = env('WIRELESS_LIVE_ACCOUNT_NUMBER');
        }
    }  


    public function VeriyBankAccount(Request $r)
    {
         $this->logInfo("validating Bank Account",$r->all());
         
        $response =  Http::withHeaders([
            "ApiKey" => $this->apikey
        ])->post($this->url."verify-bank-account",[
            "account_number" => $r->account_number,
            "bank_code" => $r->bank_code,
            "show_bvn" => false
        ]);

   //return $response;

        if($response["status"] == '00'){
                 $accountName = explode(" ",$response["data"]["account_name"]);
    
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
   
  public function initiateTransaction(Request $request)
    {
        $lock = Cache::lock('initltrnx-'.mt_rand('1111','9999'),5);

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

        $wirelesscharge = 15;
       
        $charge = $tcharge->amount + $ocharge->amount + $wirelesscharge - 5;
        
        $amount = 0;
        $totalAmount = 0;

        $amount = $request->amount;
        $totalAmount = $amount + $charge;
        $wireless = $request->amount + $wirelesscharge;

        if ($request->amount > 0) {

            $compbal = $this->validatecompanybalance($totalAmount,"combal");
            if($compbal["status"] == false){
        
                $this->logInfo("validating company balance",$compbal);
            
            return response()->json($compbal,406);
        }
    

            if ($request->transaction_type == "bank") { 

        $trnxid =  $this->generatetrnxref('wlv');
         //return response()->json(["status" => false, "message" => "We are experiencing downtime; service will be restored soon, Sorry for the inconvenience"], 406);}

                
                    $description = $request->description ?? "You sent payment to ".$request->destination_account;
                    
                        //verify monnify account balance
                      $wirelesbal = $this->validateWirelessBalance($wireless);
                      //return $monfybal;
                      $this->logInfo("wireless verify balance",$wirelesbal);
                      
                      if ($wirelesbal["status"] == false) {
                         return response()->json($wirelesbal, 406);
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
                            
                         return response()->json(['status' => false, 'message' => 'Your Account Has Been Lien('.$chklien['message'].')...please contact support']);
                        }

                 $validateuserbalance = $this->validatecustomerbalance(Auth::user()->id,$totalAmount);
                    if($validateuserbalance["status"] == false){
            
                        $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
            
                            $this->logInfo("Cutomer Account balance",$validateuserbalance);
                        return response()->json($validateuserbalance,406);
                    }

                    $validateTransferAmount = $this->validateTransfer($totalAmount,$getsetvalue->getsettingskey('online_transfer'),Auth::user()->id);
            
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
        $lock = Cache::lock('apitrnbnkac-'.mt_rand('1111','9999'),5);
            
        if($lock->get()){
      //return $request->all();
       $this->logInfo("bank transfer via wireless",$request->all());
       
       $usern = Auth::user()->last_name." ".Auth::user()->first_name;
       $this->tracktrails('1','1',$usern,'customer','Transfer to Bank Via wireless');


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
       $wirelesscharge = 15;

       $totalAmount = $request->amount + $tcharge->amount + $wirelesscharge + $ocharge->amount - 5;
       $wireless = $request->amount + $wirelesscharge;
      
       $charge = $tcharge->amount + $ocharge->amount + $wirelesscharge - 5;

       $validateuserpin = $this->validatetrnxpin($request->transaction_pin,Auth::user()->id);
       if($validateuserpin["status"] == false){

           $this->tracktrails('1','1',$usern,'customer',$validateuserpin["message"]);

           return response()->json($validateuserpin,406);
       }

      $transaction = SavingsTransaction::where('reference_no', $request->transaction_reference)->where('amount',$request->amount)->first();

       if ($transaction) {
           if($transaction->status == "approved" || $transaction->status == "failed"){
               return response()->json(["status" => false, 'message' => "Transaction has already been completed...Please Initiate Transaction"], 409);
           }else{

               $compbal = $this->validatecompanybalance($request->amount,"combal");
               if($compbal["status"] == false){
           
                   $this->logInfo("validating company balance",$compbal);
               
               return response()->json($compbal,406);
           }
       
                //verify monnify account balance
             $wirelessbal = $this->validateWirelessBalance($wireless);
             //return $monfybal;
             $this->logInfo("wireless balance",$wirelessbal);
             
             if ($wirelessbal["status"] == false) {
                return response()->json($wirelessbal, 406);
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
                       
                   $validateuserbalance = $this->validatecustomerbalance(Auth::user()->id,$totalAmount);
                   if($validateuserbalance["status"] == false){
           
                       $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
                       
                       $this->logInfo("customer balance",$validateuserbalance);
                       
                       return response()->json($validateuserbalance,406);
                   }
           
                   $validateTransferAmount = $this->validateTransfer($totalAmount,$getsetvalue->getsettingskey('online_transfer'),Auth::user()->id);
           
                   if ($validateTransferAmount['status'] == false) {
                       
                       $this->logInfo("online transfer",$validateTransferAmount);
                       
                       return response()->json($validateTransferAmount, 400);
                   }
           
                    $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->first();
                   $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->first();
                   
                   //transfer charges Gl
                   $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('glcharges'))->first();
                   
                   if($glacct->status == '1'){
                   $this->gltransaction('withdrawal',$glacct,$tcharge->amount,null);
                   $this->create_saving_transaction_gl(null,$glacct->id,null, $tcharge->amount,'credit',$request->platform,$request->transaction_reference,$this->generatetrnxref('trnxchrg'),'transfer charges','approved',$usern);
                   }
                  
                   //other charges Gl
                   $otherglacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('othrchargesgl'))->first();
                   
                   if($otherglacct->status == '1'){
                       $this->gltransaction('withdrawal',$otherglacct,$ocharge->amount,null); 
                   $this->create_saving_transaction_gl(null,$otherglacct->id,null, $ocharge->amount,'credit',$request->platform,$request->transaction_reference,$this->generatetrnxref('otc'),'others charges fees','approved',$usern);
                   
                   }
                   
                  $debitCustomer = $this->DebitCustomerandcompanyGlAcct(Auth::user()->id,$totalAmount,$wireless,$getsetvalue->getsettingskey('outwardoptiongl'),'wlv','Bank Transfer via wireless',$request->platform,$usern);

                  $this->logInfo("debit customer response",$debitCustomer);
                   
                   if(Auth::user()->account_type == '1'){//saving acct GL
                   
                       if($glsavingdacct->status == '1'){
                       $this->gltransaction('deposit',$glsavingdacct,$totalAmount,null);
                   $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $totalAmount,'debit',$request->platform,$request->transaction_reference,$this->generatetrnxref('D'),'customer debited','approved',$usern);
                       }
                       
                   }elseif(Auth::user()->account_type == '2'){//current acct GL
                   
                   if($glcurrentacct->status == '1'){
                       $this->gltransaction('deposit',$glcurrentacct,$totalAmount,null);
                   $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $totalAmount,'debit',$request->platform,$request->transaction_reference,$this->generatetrnxref('D'),'customer debited','approved',$usern);
                       }
                       
                   }
                   
                   $dacct = $request->receipient_name.','.$request->destination_account.','.$request->bank_code;

                  

                  $bank = Bank::where('bank_code', $request->bank_code)->first();

                  $newdescription = empty($request->description) ? "From " .$usern : $request->description;
                  
                    $this->logInfo("transfer Url",$this->url."bank-transfer");
                    
                 //wireless verify transfer
                  $bankTransfer = $this->WirelessTransfer($this->apikey,$request->amount,$request->transaction_reference,$request->bank_code,$request->destination_account,$request->receipient_name,$newdescription."-".Auth::user()->last_name." ".Auth::user()->first_name);
               
                  //return $bankTransfer;
                 $this->logInfo("bank transfer response log via wireless verify",$bankTransfer);
                 
                  //logInfo($bankTransfer, "Monnify Transfer Response");
                  $description = empty($request->description) ? "trnsf" : $request->description;
                  $updtdescription = $description."/".$request->receipient_name."/".$request->destination_account."-".$bank->bank_name;

                  $dacct2 = $request->receipient_name."/".$request->destination_account."-".$bank->bank_name;

              //if ($bankTransfer["status"] == "00") {
                  if($bankTransfer["status"] == "00"){

                    //companybal
                $this->debitcreditCompanyBalance($request->amount,"debit","combal");
                
                    $this->updateTransactionAndAddTrnxcharges(null, Auth::user()->id,null,$charge,'debit',$request->platform,'0',null,null,null,$request->transaction_reference,
                           $updtdescription,"charges",'approved','10',$usern,$dacct2);
                       
                      
                   $famt = " N".number_format($totalAmount,2);
                   $dbalamt = " N".number_format($debitCustomer['balance'],2);
                   $bdecs1 =  $updtdescription;

                   $msg = "Debit Amt: ".$famt."<br> Desc: ".$bdecs1."<br> Avail Bal: ".$dbalamt."<br> Date:" . date('Y-m-d') . "<br> Ref: " . $request->transaction_reference;
                   $smsmsg = "Debit Amt: ".$famt."\n Desc: ".$bdecs1."\n Avail Bal: ".$dbalamt."\n Date:" . date('Y-m-d') . "\n Ref: " . $request->transaction_reference;
                       
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
                      
             }else{
                        //FAILED TRANSACTION    
                        $this->updateTransactionAndAddTrnxcharges(null, Auth::user()->id,null,$charge,'debit',$request->platform,'0',null,null,null,$request->transaction_reference,
                        $updtdescription,"charges",'failed','10',$usern.'(c)',$dacct2);
                     
                     $this->tracktrails('1','1',$usern,'customer','Transaction Failed');

                   // $reverd = $this->ReverseDebitTrnxandcompanyGlAcct(Auth::user()->id,$totalAmount,$wireless,$request->transaction_reference,$getsetvalue->getsettingskey('outwardoptiongl'),'wlv','Transaction reversed',$request->platform,'trnsfer',$usern.'(c)',$dacct);
                   
                   //reverse transfer charges Gl
                   if($glacct->status == '1'){
                    $this->gltransaction('deposit',$glacct,$tcharge->amount,null);
                   $this->create_saving_transaction_gl(null,$glacct->id,null, $tcharge->amount,'debit',$request->platform,$request->transaction_reference,$this->generatetrnxref('trnxchrg'),'reversed transfer charges','approved',$usern.'(c)');
                   }
                  
                   //reverse other charges Gl
                   if($otherglacct->status == '1'){
                    $this->gltransaction('deposit',$otherglacct,$ocharge->amount,null); 
                   $this->create_saving_transaction_gl(null,$otherglacct->id,null, $ocharge->amount,'debit',$request->platform,$request->transaction_reference,$this->generatetrnxref('otc'),'reversed others charges fees','approved',$usern.'(c)');
                   }
                       
                       //reverse saving acct and current acct Gl
                    if(Auth::user()->account_type == '1'){//saving acct GL
                    
                       if($glsavingdacct->status == '1'){
                   $this->gltransaction('withdrawal',$glsavingdacct,$totalAmount,null);
                   $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $totalAmount,'credit',$request->platform,$request->transaction_reference,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
                       }
                       
                   }elseif(Auth::user()->account_type == '2'){//current acct GL
                   
                       if($glcurrentacct->status == '1'){
                       $this->gltransaction('withdrawal',$glcurrentacct,$totalAmount,null);
                   $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $totalAmount,'credit',$request->platform,$request->transaction_reference,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
                       }
                   }
                   
                   //        $msg = "Credit Amt: N".number_format($totalAmount,2)."<br> Desc: Debit Transaction Reversal <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$request->transaction_reference;
                   //        $smsmsg = "Credit Amt: N".number_format($totalAmount,2)."\n Desc: Debit Transaction Reversal \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date: ".date("Y-m-d")."\n Ref: ".$request->transaction_reference;
   
                   //        if(Auth::user()->enable_sms_alert){
                   //            $this->sendSms(Auth::user()->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                   //            }
                          
                   //        if(Auth::user()->enable_email_alert){
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
                   //        }
            
                      return response()->json(['status' =>false, 'message' => 'Bank Transfer Failed'], 406);
                      
                 }

           //   }else{
                      
           //         return response()->json(['status' => false, 'message' => "Transaction Completed"], 406);
           //     }
           }
           
       } else {
           return response()->json(["status" => false, 'message' => "Invalid Transaction Reference,Please Reinitiate Transaction"], 400);
       }

       $lock->release();
        }//lock
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
}

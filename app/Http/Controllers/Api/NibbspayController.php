<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traites\AuditTraite;
use App\Http\Traites\SavingTraite;
use App\Http\Traites\TransferTraite;
use App\Http\Traites\UserTraite;
use App\Models\Charge;
use App\Models\Email;
use App\Models\GeneralLedger;
use App\Models\SavingsTransaction;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class NibbspayController extends Controller
{
    use SavingTraite;
    use AuditTraite;
    use UserTraite;
    use TransferTraite;
    
    private $url;
    private $clientkey;
    private $sercetkey;
    private $acctno;
    private $clientcode;
    private $billerid;
    private $authricod;
    private $inititcod;
    private $acctname;
    public $trxnid;
    public function __construct()
    {
        if(env('APP_MODE') == "test"){
            $this->url = env('NIBBSPAY_SANDBOX_URL');
            $this->clientkey = env('NIBBSPAY_SANDBOX_CLIENT_KEY');
            $this->sercetkey = env('NIBBSPAY_SANDBOX_SECRET_KEY');
            $this->acctno = env('SANDBOX_SETTLEMENT_ACCOUNT_NUMBER');
            $this->acctname = env('SANDBOX_SETTLEMENT_ACCOUNT_NAME');
            $this->clientcode = env('SANDBOX_CLIENT_CODE');
            $this->billerid = env('SANDBOX_BILLER_ID');
            $this->authricod = env('SANDBOX_AUTHORIZATION_CODE');
            $this->inititcod = env('SANDBOX_INSTITUTION_CODE');
        }else{
            $this->url = env('NIBBSPAY_LIVE_URL');
            $this->clientkey = env('NIBBSPAY_LIVE_CLIENT_KEY');
            $this->sercetkey = env('NIBBSPAY_LIVE_SECRET_KEY');
            $this->acctno = env('LIVE_SETTLEMENT_ACCOUNT_NUMBER');
            $this->acctname = env('LIVE_SETTLEMENT_ACCOUNT_NAME');
            $this->clientcode = env('LIVE_CLIENT_CODE');
            $this->billerid = env('LIVE_BILLER_ID');
            $this->authricod = env('LIVE_AUTHORIZATION_CODE');
            $this->inititcod = env('LIVE_INSTITUTION_CODE');
        }
    }
    
    public function VeriyBankAccount(Request $r)
    {
         $this->logInfo("validating Bank Account",$r->all());
         
         $trnxid = date("ymdhis")."".mt_rand("000000000000","999999999999");
         
        $response =  Http::post($this->url."nip/nameenquiry",[
            "accountNumber" =>  $r->account_number,
            "channelCode" => "2",
            "destinationInstitutionCode" => $r->bank_code,
            "transactionId" => $this->clientcode."".$trnxid
        ]);

   return $response;

        if($response["responseCode"] == '00'){
                 $accountName = explode(" ",$response["accountName"]);
    
          $firstName = count($accountName) < 3 ? $accountName[1] : $accountName[2]." ".$accountName[1];
         $lastName =  $accountName[0];
     
             $vdata = [
                "first_name" => $firstName,
                "last_name" => $lastName,
                "bankCode" => $r->bank_code,
             ];
             
             $this->trxnid = $response["transactionId"];
             $this->logInfo("Bank Account verified", $vdata);
              
            return response()->json(['status' => true,'message' => 'Bank Account Verified Successfully','data' => $vdata]);
        }else{
            return response()->json(['status' => false,'message' => 'Failed to Verify Bank Account']);
        }
        
    }

    public function initiateTransaction(Request $request)
    {

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

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails('1','1',$usern,'customer','transaction initialized');

        $getsetvalue = new Setting();
        
        $description = $request->description;


        $tcharge = Charge::select('amount')->where('id',$getsetvalue->getsettingskey('transfer_charge'))->first();
        $ocharge = Charge::select('amount')->where('id',$getsetvalue->getsettingskey('othercharges'))->first();
        $monnifycharge = $getsetvalue->getsettingskey('monnifycharge');
 
        $charge = (float)$tcharge->amount + (float)$ocharge->amount;

        $amount = 0;
        $totalAmount = 0;

        $amount = $request->amount;
        $totalAmount = $amount + $charge;

        if ($request->amount > 0) {

            if ($request->transaction_type == "bank") { 

$trnxid =  date("ymdhis")."".mt_rand("000000000000","999999999999");
         //return response()->json(["status" => false, "message" => "We are experiencing downtime; service will be restored soon, Sorry for the inconvenience"], 406);}

                
                    $description = $request->description ?? "You sent payment to ".$request->destination_account;
                    
                        //verify monnify account balance
                      $settlementbal = $this->validateSettlementBalance($this->url,$totalAmount,$this->acctno,$this->acctname,$this->authricod,$this->inititcod,$this->billerid,$trnxid);
                      //return $monfybal;
                      $this->logInfo("settlement balance",$settlementbal);
                      
                      if ($settlementbal["status"] == false) {
                         return response()->json($settlementbal, 406);
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
                    $trnxid,$description,'pending','trnsfer','');
                    
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

                 $validateuserbalance = $this->validatecustomerbalance(Auth::user()->id,$amount);
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
                $trnxid,$description,'pending','trnsfer','');


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
    }

    public function transferToBankAccount(Request $request)
    {
       // return $request->all();
       $this->logInfo("bank transfer",$request->all());
       
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails('1','1',$usern,'customer','Transfer to Bank Via Monnify');


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
        
        $description = $request->description;

        $tcharge = Charge::select('amount')->where('id',$getsetvalue->getsettingskey('transfer_charge'))->first();
        $ocharge = Charge::select('amount')->where('id',$getsetvalue->getsettingskey('othercharges'))->first();
        $monnifycharge = $getsetvalue->getsettingskey('monnifycharge');


        $totalAmount = (float)$request->amount + (float)$tcharge->amount + (float)$monnifycharge + (float)$ocharge->amount;
        $monify = (float)$request->amount + (float)$monnifycharge;
        $charge = (float)$tcharge->amount + (float)$monnifycharge + (float)$ocharge->amount;

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
                 //verify monnify account balance
              $monfybal = $this->validateMonnifyBalance($this->acctno,$monify);
              //return $monfybal;
              $this->logInfo("monnify balance",$monfybal);
              
              if ($monfybal["status"] == false) {
                 return response()->json($monfybal, 406);
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
                    $this->create_saving_transaction_gl(null,$glacct->id,null, $tcharge->amount,'credit',$request->platform,null,$this->generatetrnxref('trnxchrg'),'transfer charges','approved',$usern.'(c)');
                    }
                   
                    //other charges Gl
                    $otherglacct = GeneralLedger::select('id','account_balance')->where('gl_code',$getsetvalue->getsettingskey('othrchargesgl'))->first();
                    
                    if($otherglacct->status == '1'){
                        $this->gltransaction('withdrawal',$otherglacct,$ocharge->amount,null); 
                    $this->create_saving_transaction_gl(null,$otherglacct->id,null, $ocharge->amount,'credit',$request->platform,null,$this->generatetrnxref('otc'),'others charges fees','approved',$usern.'(c)');
                    
                    }
                    
         

                   $debitCustomer = $this->DebitCustomerandcompanyGlAcct(Auth::user()->id,$totalAmount,$monify,$getsetvalue->getsettingskey('moniepointgl'),'m','Bank Transfer via monnify',$request->platform,$usern.'(c)');

                   $this->logInfo("debit customer response",$debitCustomer);
                    
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
                    
                    $dacct = $request->receipient_name.','.$request->destination_account.','.$request->bank_code;

                   if (!$debitCustomer["status"]) {
                       
                       $this->updateTransactionAndAddTrnxcharges(null, Auth::user()->id,null,$charge,'debit',$request->platform,'0',null,null,null,$request->transaction_reference,
                         "failed Transaction","failed Transaction",'failed',$usern.'(c)',$dacct);
                         
                           $this->tracktrails('1','1',$usern,'customer','Transaction Failed');

                    $reverd = $this->ReverseDebitTrnxandcompanyGlAcct(Auth::user()->id,$totalAmount,$monify,$request->transaction_reference,$getsetvalue->getsettingskey('moniepointgl'),'m','Transaction reversed',$request->platform,'trnsfer',$usern.'(c)',$dacct);
                    
                    //reverse transfer charges Gl
                    if($glacct->status == '1'){
                     $this->gltransaction('deposit',$glacct,$tcharge->amount,null);
                    $this->create_saving_transaction_gl(null,$glacct->id,null, $tcharge->amount,'debit',$request->platform,null,$this->generatetrnxref('trnxchrg'),'reversed transfer charges','approved',$usern.'(c)');
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
                   
                       return response()->json($debitCustomer);
                   }

                  // $bank = banks::where('bank_code', $request->bank_code)->first();

                   $newdescription = empty($request->description) ? "From " .$usern : $request->description;
                 
                   $bankTransfer = $this->NibbsPayTransfer($this->url,$request->amount,$request->receipient_name,$request->destination_account,$usern,Auth::user()->acctno,$request->transaction_reference,
                                                            $newdescription,$this->billerid,$request->bank_code,$this->trxnid,$this->trxnid,$this->acctname,$this->acctno);
                   

                   //return $bankTransfer;
                  $this->logInfo("bank transfer response log via nibbspay",$bankTransfer);
                  
                   //logInfo($bankTransfer, "Monnify Transfer Response");
                   $description = empty($request->description) ? "trnsf" : $request->description;
                   $updtdescription = $description."/".$request->receipient_name."/".$request->destination_account."-".$bankTransfer["responseBody"]["destinationBankName"];

                   $dacct2 = $request->receipient_name."/".$request->destination_account."-".$bankTransfer["responseBody"]["destinationBankName"];

               if ($bankTransfer["responseCode"] == "0") {
                   if($bankTransfer["responseBody"]["status"] == "SUCCESS"){

                     $this->updateTransactionAndAddTrnxcharges(null, Auth::user()->id,null,$charge,'debit',$request->platform,'0',null,null,null,$request->transaction_reference,
                            $updtdescription,"charges",'approved',$usern.'(c)',$dacct2);


                $this->saveBeneficiary($request->beneficiary,$request->userid,$request->receipient_name,$request->destination_account,$request->bank_name,$request->bank_code,'bank');
                        
                       
                    $famt = " N".number_format($totalAmount,2);
                    $dbalamt = " N".number_format($debitCustomer['balance'],2);
                    $bdecs1 =  $updtdescription;

                    $msg = "Debit Amt: ".$famt."<br> Desc: ".$bdecs1."<br> Avail Bal: ".$dbalamt."<br> Date:" . date('Y-m-d') . "<br> Ref: " . $request->transaction_reference;
                    $smsmsg = nl2br("Debit Amt: ".$famt."\n Desc: ".$bdecs1."\n Avail Bal: ".$dbalamt."\n Date:" . date('Y-m-d') . "\n Ref: " . $request->transaction_reference);
                    
                    $this->sendSms(Auth::user()->phone,$smsmsg);//send sms

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
                         $updtdescription,"charges",'failed',$usern.'(c)',$dacct2);
                      
                      $this->tracktrails('1','1',$usern,'customer','Transaction Failed');

                    $reverd = $this->ReverseDebitTrnxandcompanyGlAcct(Auth::user()->id,$totalAmount,$monify,$request->transaction_reference,$getsetvalue->getsettingskey('moniepointgl'),'m','Transaction reversed',$request->platform,'trnsfer',$usern.'(c)',$dacct);
                    
                    //reverse transfer charges Gl
                    if($glacct->status == '1'){
                     $this->gltransaction('deposit',$glacct,$tcharge->amount,null);
                    $this->create_saving_transaction_gl(null,$glacct->id,null, $tcharge->amount,'debit',$request->platform,null,$this->generatetrnxref('trnxchrg'),'reversed transfer charges','approved',$usern.'(c)');
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
                    
                           $smsmsg = nl2br("Credit Amt: N".number_format($totalAmount,2)."\n Desc: Debit Transaction Reversal \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date: ".date("Y-m-d")."\n Ref: ".$request->transaction_reference);
                           $this->sendSms(Auth::user()->phone,$smsmsg);//send sms
                           
                       if(Auth::user()->enable_email_alert){
                        $msg = "Credit Amt: N".number_format($totalAmount,2)."<br> Desc: Debit Transaction Reversal <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$request->transaction_reference;
                        Email::create([
                           'user_id' =>  Auth::user()->id,
                           'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
                           'message' => $msg,
                           'recipient' => Auth::user()->email,
                       ]);
           
                        Mail::send(['html' => 'mails.sendmail'],[
                            'msg' => $msg,
                            'type' => 'Credit Transaction'
                        ],function($mail)use($getsetvalue){
                            $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                              $mail->to(Auth::user()->email);
                            $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
                        });
                       }
             
                       return response()->json(['status' =>false, 'message' => 'Bank Transfer Failed'], 406);
                       
                  }

               }else{
                       
                    return response()->json(['status' => false, 'message' => "Transaction Completed"], 406);
                }
            }
            
        } else {
            return response()->json(["status" => false, 'message' => "Invalid Transaction Reference,Please Reinitiate Transaction"], 400);
        }
    }
}//endclass

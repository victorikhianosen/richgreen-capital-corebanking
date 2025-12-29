<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traites\AuditTraite;
use App\Http\Traites\SavingTraite;
use App\Http\Traites\UserTraite;
use App\Models\Email;
use App\Models\GeneralLedger;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class GiftbillController extends Controller
{
    use SavingTraite;
    use AuditTraite;
    use UserTraite;
 
    private $giftbillsurl;
    private $giftbillmerchantid;
    private $giftbillapikey;
    private $giftbillencrptkey;

    public function __construct()
    {
      if(env('APP_MODE') == "test"){
            $this->giftbillsurl = env('giftbills_test_url');
              $this->giftbillmerchantid = env('giftbills_test_merchantId');
            $this->giftbillapikey = env('giftbills_test_api_key');
            $this->giftbillencrptkey = env('giftbills_test_encrypt_key');
        }else{
            $this->giftbillsurl = env('giftbills_live_url');
              $this->giftbillmerchantid = env('giftbills_live_merchantId');
            $this->giftbillapikey = env('giftbills_live_api_key');
            $this->giftbillencrptkey = env('giftbills_live_encrypt_key');
         }
    }
    
    public function get_betting_companies()
    {
        $endpoint = $this->giftbillsurl."betting";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->giftbillapikey,
            'content_type' => 'application/json',
            'MerchantId' => $this->giftbillmerchantid
        ])->get($endpoint)->json();

        if($response['success'] == true){
            
             $this->logInfo("","Betting companies fetched successfully");
             
            return response()->json(['status' => true, 'message' => 'Data Fetched Successfully', 'data' => $response['data']]);
        }else{
            return response()->json(['status' => true, 'message' => 'Failed To Fetched Data', 'data' => []]);
        }
    }

    public function verify_betting_account(Request $r)
    {
          $this->logInfo("validating Betting Account",$r->all());
         
        $validate = Validator::make($r->all(),[
            'customerid' => ['required','string',],
            'betting_provider' => ['required','string'],
        ]);

        if($validate->fails()){
            return response()->json(['status' => false,'message' => $validate->errors()->all()[0]],406);
        }

        $endpoint = $this->giftbillsurl."betting/validate";

        $body=[
            "provider" => $r->betting_provider,
            "customerId" => $r->customerid
        ];
           
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->giftbillapikey,
            'content_type' => 'application/json',
            'MerchantId' => $this->giftbillmerchantid,
        ])->post($endpoint,$body)->json();
  
             $this->logInfo("giftbills api response",$response);
   
        if($response["success"] == true){
            return response()->json(["status" => true,"message" => "Betting Account Verified", "data" => $response["data"]]);
        }else{
            return response()->json(["status" => false,"message" => "Failed To Verify Betting Account"]);
        }
    }

    public function topup_betting(Request $r)
    {
        $lock = Cache::lock('tpbent-'.mt_rand('1111','9999'),3);

 if($lock->get()){

         $this->logInfo("Betting topup",$r->all());
 
        $validate = Validator::make($r->all(),[
            'customerid' => ['required','string',],
            'betting_provider' => ['required','string'],
            'amount' => ['required','string','numeric','gt:0'],
            'pin' => ['required','string','numeric','min:4','digits:4'],
            'platform' => ['required','string']
        ]);

        if($validate->fails()){
            return response()->json(['status' => false,'message' => $validate->errors()->all()[0]],406);
        }
        
         $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        
         $compbal = $this->validatecompanybalance($r->amount,"vas");
            if($compbal["status"] == false){
        
                $this->logInfo("validating company balance",$compbal);
            
            return response()->json($compbal,406);
        }

        $chkcres = $this->checkCustomerRestriction(Auth::user()->id);
        if($chkcres == true){
           
            $this->tracktrails('1','1',$usern,'customer','Account Restricted');

            $this->logInfo("","customer account restricted");
            
            return response()->json(['status' => false, 'message' => 'Your Account Has Been Restricted. Please contact support'],406);
        }    
        
         $chklien = $this->checkCustomerLienStatus(Auth::user()->id);
            if($chklien['status'] == true && $chklien['lien'] == 2){
                
                $this->tracktrails('1','1',$usern,'customer','Account has been lien');
                
                 $this->logInfo("validating lien status",$chklien);
                
             return response()->json(['status' => false, 'message' => 'Your Account Has Been Lien('.$chklien['messages'].')...please contact support']);
            }

        $validateuserpin = $this->validatetrnxpin($r->pin,Auth::user()->id);
        if($validateuserpin["status"] == false){
            
            $this->tracktrails('1','1',$usern,'customer',$validateuserpin["message"]);

             $this->logInfo("validating customer pin",$validateuserpin);
            
            return response()->json($validateuserpin,406);
        }

        $validateuserbalance = $this->validatecustomerbalance(Auth::user()->id,$r->amount);
        if($validateuserbalance["status"] == false){

            $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);

            $this->logInfo("validating customer balance",$validateuserbalance);

            return response()->json($validateuserbalance,406);
        }
        
        $trxref = $this->generatetrnxref('bet');

        $getsetvalue = new Setting();

        $percentage = $this->getUtilityPercentage();
        $prec = array();
         foreach($percentage as $percent){
             if($percent["service"] == $r->betting_provider){
                 $prec = $percent;
             }
         }
 
         $percentincome = $r->amount * $prec["value"] / 100;
         $totamount = $r->amount - $percentincome;
 
           $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->first();
           $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->first();
                    
         $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('giftbill_income'))->first();
         
          if($glacct->status == '1'){
           $this->create_saving_transaction_gl(null,$glacct->id,null, $percentincome,'credit',$r->platform,null,$this->generatetrnxref('gb'),'betting percentage','approved',$usern.'(c)');
           $this->gltransaction('withdrawal',$glacct,$percentincome,null); 
          }
          
        $customerbal = $this->DebitCustomerandcompanyGlAcct(Auth::user()->id,$r->amount,$totamount,$getsetvalue->getsettingskey('giftbill_account'),'gb','Betting Topup',$r->platform,$usern.'(c)');

         $this->logInfo("debit customer response", $customerbal);
         
          if(Auth::user()->account_type == '1'){//saving acct GL
          
                 if($glsavingdacct->status == '1'){
                $this->gltransaction('deposit',$glsavingdacct,$r->amount,null);
            $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $r->amount,'debit',$r->platform,null,$this->generatetrnxref('W'),'customer debited','approved',$usern.'(c)');
                 }
                 
            }elseif(Auth::user()->account_type == '2'){//current acct GL
            
                 if($glcurrentacct->status == '1'){
                $this->gltransaction('deposit',$glcurrentacct,$r->amount,null);
            $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $r->amount,'debit',$r->platform,null,$this->generatetrnxref('W'),'customer debited','approved',$usern.'(c)');
                 }
                 
            }

         
            
        $reference = Str::random(16);
        $endpoint = $this->giftbillsurl."betting/topup";
        $body=[
           "amount" =>  $r->amount,
            "customerId" => $r->customerid,
            "provider" => $r->betting_provider,
            "reference" => $reference
        ];

        $json = '{"amount":"' . $r->amount . '","customerId":"' . $r->customerid . '","provider":"' . $r->betting_provider . '","reference":"' . $reference . '"}';

        $signature = hash_hmac("sha512",$json, $this->giftbillencrptkey);
//   return  $signature;
        $response = Http::connectTimeout("60")->withHeaders([
            'Authorization' => 'Bearer '.$this->giftbillapikey,
            'content_type' => 'application/json',
            'MerchantId' =>  $this->giftbillmerchantid,
            'Encryption' => $signature
        ])->post($endpoint,$body)->json();

        $this->logInfo("Betting topup response",$response);
    
        if($response["success"] == true && $response["code"] == "00000"){

            if(isset($response["data"]["errorMsg"])){
                return response()->json(['status' =>false, 'message' => $response["data"]["errorMsg"]]);
            }

                //companybal
                $this->debitcreditCompanyBalance($r->amount,"debit","vas");

            $this->create_saving_transaction(null, Auth::user()->id,null,$r->amount,
              'debit',$r->platform,'0',null,null,null,null,$trxref,"Betting Topup Successful",'approved','13','utility',$usern.'(c)');

              $this->tracktrails('1','1',$usern,'customer',"Betting Topup Successful, Order No: ".$response["data"]["orderNo"]."Reference no: ".$response["data"]["reference"]);


             $msg = "Debit Amt: N".number_format($r->amount,2)."<br> Desc: Betting Topup Successful <br>Avail Bal: N".number_format($customerbal["balance"],2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trxref;
             
             $smsmsg = "Debit Amt: N".number_format($r->amount,2)."\n Desc: Betting Topup Successful \n Avail Bal: N".number_format($customerbal["balance"],2)."\n Date:" . date('Y-m-d') . "\n Ref: " . $trxref;
            
             if(Auth::user()->enable_sms_alert){
                 $this->sendSms(Auth::user()->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                 }

             if(Auth::user()->enable_email_alert){
              Email::create([
                'user_id' =>  Auth::user()->id,
                'subject' =>ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert',
                'message' =>  $msg,
                'recipient' => Auth::user()->email,
            ]);

              Mail::send(['html' => 'mails.sendmail'],[
                'msg' =>  $msg,
                'type' => 'Debit Transaction'
            ],function($mail)use($getsetvalue){
                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                 $mail->to(Auth::user()->email);
                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
            });
        }
            
            return response()->json(["status" => true,"message" => "Betting Topup Successful",$response["data"]]);

        }else{

            $this->create_saving_transaction(null, Auth::user()->id,null,$r->amount,
                   'debit',$r->platfrom,'0',null,null,null,null,$trxref,'Betting Topup Failed','failed','13','utility',$usern.'(c)');

                if($glacct->status == '1'){
                   $this->create_saving_transaction_gl(null,$glacct->id,null, $percentincome,'debit',$r->platform,null,$this->generatetrnxref('gb'),'betting percentage reversed','approved',$usern.'(c)');
                   $this->gltransaction('deposit',$glacct,$percentincome,null); 
                }
            
        //         $reverd = $this->ReverseDebitTrnxandcompanyGlAcct(Auth::user()->id,$r->amount,$totamount,$trxref,$getsetvalue->getsettingskey('giftbill_account'),'gb','Betting Transaction reversed(giftbills)',$r->platform,'utility',$usern.'(c)',null);
                            
        //           //reverse saving acct and current acct Gl
        //              if(Auth::user()->account_type == '1'){//saving acct GL
                     
        //                  if($glsavingdacct->status == '1'){
        //             $this->gltransaction('withdrawal',$glsavingdacct,$r->amount,null);
        //             $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $r->amount,'credit',$r->platform,null,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
        //                  }
                         
        //             }elseif(Auth::user()->account_type == '2'){//current acct GL
                    
        //                  if($glcurrentacct->status == '1'){
        //                 $this->gltransaction('withdrawal',$glcurrentacct,$r->amount,null);
        //             $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $r->amount,'credit',$r->platform,null,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
        //                  }
                         
        //             }
                
        //         $this->tracktrails('1','1',$usern,'customer',"Betting Topup Failed");

        //              $msg = "Credit Amt: N".number_format($r->amount,2)."<br> Desc: Debit Transaction Reversal <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$trxref;
        
        //         $smsmsg = "Credit Amt: N".number_format($r->amount,2)."\n Desc: Debit Transaction Reversal \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date:" . date('Y-m-d') . "\n Ref: " . $trxref;
            
        //         if(Auth::user()->enable_sms_alert){
        //             $this->sendSms(Auth::user()->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
        //             }

        //        if(Auth::user()->enable_email_alert){
        //         Email::create([
        //             'user_id' =>  Auth::user()->id,
        //             'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
        //             'message' => $msg,
        //             'recipient' => Auth::user()->email,
        //         ]);

        //         Mail::send(['html' => 'mails.sendmail'],[
        //             'msg' => $msg,
        //             'type' => 'Credit Transaction'
        //         ],function($mail)use($getsetvalue){
        //             $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
        //               $mail->to(Auth::user()->email);
        //             $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
        //         });
        // }
        
            return response()->json(["status" => false,"message" => "Betting Topup Failed"]);
        }
        
        $lock->release();
    }//lock
}

}//endclass

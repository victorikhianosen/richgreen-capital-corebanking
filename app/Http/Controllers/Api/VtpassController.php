<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traites\AuditTraite;
use App\Http\Traites\SavingTraite;
use App\Http\Traites\UserTraite;
use App\Http\Traites\VtpassTraite;
use App\Models\Email;
use App\Models\GeneralLedger;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class VtpassController extends Controller
{
    use VtpassTraite;
    use SavingTraite;
    use AuditTraite;
    use UserTraite;
    
    private $vtpassurl;
    
    public function __construct()
    {
      if(env('APP_MODE') == "test"){
        $this->vtpassurl = env('vtpass_test_url');
      }else{
        $this->vtpassurl = env('vtpass_live_url');
      }
    }
    
    public function verify_smartcard_number(Request $r){ //verify smart card
    
         $this->logInfo("verifying smart card",$r->all());
    
        $validate = Validator::make($r->all(),[
            'smartcard_number' => ['required','string','numeric'],
            'service_type' => ['required','string'],
        ]);

        if($validate->fails()){
            return response()->json(['status' => false,'message' => $validate->errors()->all()[0]],406);
        }
        
        $endpoint = $this->vtpassurl."merchant-verify";

        $body=[
            'billersCode' => $r->smartcard_number,
            'serviceID' => $r->service_type
        ];
         $response = $this->vtpassposturl($endpoint,$body);
        //   return  $response; 
        
        $this->logInfo("",$response);

         if($response['code'] == "000"){
            if(isset($response['content']['error'])){
                return response()->json(['status' => false,'message' => $response['content']['error']]);
            }
            return response()->json(['status' => true,'message' => 'Smart Card Verified','data' => $response['content']]);
         }
    }

    //get cable subciptions
    public function get_subcriptions(Request $r)
    {
        if(empty($r->service_type)){
            return response()->json(['status' => false,'message' => "Sorry parameter is empty"],406);
        }

        $endpoint = $this->vtpassurl."service-variations?serviceID=".$r->service_type;

         $response = $this->vtpassgeturl($endpoint);

         if($response['response_description'] == "000"){
            return response()->json(['status' => true,'message' => 'subcriptions fetched','data' => $response['content']['varations']]);
         }else{
            return response()->json(['status' => false,'message' => 'failed to fetch subcriptions','data' => []]);
         }
    }

    //pay cable tv
    public function pay_cable_tv(Request $r){
       
        $lock = Cache::lock('cabltv-'.mt_rand('1111','9999'),5);

        if($lock->get()){

        $trnxid = $this->vtpassrequestid();
        $endpoint = $this->vtpassurl."pay";
     //  return $trnxid;
        $getsetvalue = new Setting();
        
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;

        $this->logInfo("cable subcription",$r->all());
 
  $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->first();
 $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->first();
                    
        if($r->service_type == "showmax"){

            $validate = Validator::make($r->all(),[
                'amount' => ['required','string','numeric','gt:0'],
                'service_type' => ['required','string'],
                'phone_number' => ['required','string','numeric'],
                'subcription_plan' => ['required','string'],
                'pin' => ['required','string','numeric','min:4','digits:4'],
                'platform' => ['required','string']

            ]);
    
    
            if($validate->fails()){
                return response()->json(['status' => false,'message' => $validate->errors()->all()[0]],406);
            }
            
            
            $compbal = $this->validatecompanybalance($r->amount,"vas");
            if($compbal["status"] == false){
        
                $this->logInfo("validating company balance",$compbal);
            
            return response()->json($compbal,406);
        }

            $chkcres = $this->checkCustomerRestriction(Auth::user()->id);
            if($chkcres == true){
    
                $this->tracktrails('1','1',$usern,'customer','Account Restricted');
    
                $this->logInfo("Customer Account Restricted",'');
                
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
        
        $getsetvalue = new Setting();

        $percentage = $this->getUtilityPercentage();
            $prec = array();
             foreach($percentage as $percent){
                 if($percent["service"] == $r->service_type){
                     $prec = $percent;
                 }
             }
     
             $percentincome = $r->amount * $prec["value"] / 100;
             $totamount = $r->amount - $percentincome;
     
             $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('vtpass_income'))->first();
           
             if($glacct->status == '1'){
               $this->create_saving_transaction_gl(null,$glacct->id,null,$percentincome,'credit',$r->platform,null,$this->generatetrnxref('vt'),'vtpass '.$r->service_type.' percentage','approved',$usern.'(c)');
               $this->gltransaction('withdrawal',$glacct,$percentincome,null); 
             }
             
        $customerbal = $this->DebitCustomerandcompanyGlAcct(Auth::user()->id,$r->amount,$totamount,$getsetvalue->getsettingskey('vtpass_account'),'vt','Cable TV Subscription('.$r->service_type.')',$r->platform,$usern.'(c)');

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
                    
                   
            $showmaxbody = [
                'request_id' => $trnxid,
                'serviceID' => $r->service_type,
                'billersCode' => $r->phone_number,
                'variation_code' => $r->subcription_plan,
                'phone' => $r->phone_number,
                'amount' => $r->amount
            ];

            $response = $this->vtpassposturl($endpoint,$showmaxbody);

            $this->logInfo("vtpass showmax response",$response);
                
              //return  $response; 
             if($response['code'] == "000"){

                if(isset($response['content']['error'])){
                    return response()->json(['status' => false,'message' => $response['content']['error']]);
                }

                
                //companybal
                $this->debitcreditCompanyBalance($r->amount,"debit","vas");
                
                $description = "Cable TV Subscription($r->service_type) worth ".$r->amount;

                $this->create_saving_transaction(null, Auth::user()->id,null,$r->amount,'debit',$r->platform,'0',null,null,null,null,$trnxid,$description,'approved','16','utility',$usern.'(c)');


                $this->tracktrails('1','1',$usern,'customer','Cable TV Subscription Purchased Successfully('.$r->service_type.')');

               $msg =  "Debit Amt: N".number_format($r->amount,2)."<br> Desc: Cable TV Subscription Successful(".$r->service_type.")  <br>Avail Bal: N".number_format($customerbal["balance"],2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trnxid;
               
               $smsmsg = "Debit Amt: N".number_format($r->amount,2)."\n Desc: Cable TV Subscription Successful(".$r->service_type.") \n Avail Bal: N".number_format($customerbal["balance"],2)."\n Date:" . date('Y-m-d') . "\n Ref: " . $trnxid;
    
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
 
                return response()->json(['status' => true,'message' => 'Cable TV Subscription Purchased Successfully'],201);

             }else{

                $this->create_saving_transaction(null, Auth::user()->id,null,$r->amount,
                'debit',$r->platform,'0',null,null,null,null,$trnxid,'Cable TV Subscription Purchased Failed','failed','16','utility',$usern.'(c)');

        if($glacct->status == '1'){
                $this->create_saving_transaction_gl(null,$glacct->id,null, $percentincome,'debit',$r->platform,null,$this->generatetrnxref('vt'),'vtpass '.$r->service_type.' percentage reversed','approved',$usern.'(c)');
                $this->gltransaction('deposit',$glacct,$percentincome,null); 
        }
            //  $reverd = $this->ReverseDebitTrnxandcompanyGlAcct(Auth::user()->id,$r->amount,$totamount,$trnxid,$getsetvalue->getsettingskey('vtpass_account'),'vt','CableTv Transaction reversed('.$r->service_type.')',$r->platform,'utility',$usern.'(c)',null);
            
            //     //reverse saving acct and current acct Gl
            //          if(Auth::user()->account_type == '1'){//saving acct GL
                     
            //          if($glsavingdacct->status == '1'){
            //         $this->gltransaction('withdrawal',$glsavingdacct,$r->amount,null);
            //         $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $r->amount,'credit',$r->platform,null,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
            //         }

            //         }elseif(Auth::user()->account_type == '2'){//current acct GL
                    
            //             if($glcurrentacct->status == '1'){
            //             $this->gltransaction('withdrawal',$glcurrentacct,$r->amount,null);
            //         $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $r->amount,'credit',$r->platform,null,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
            //             }
                        
            //         }
                    
            //  $this->tracktrails('1','1',$usern,'customer','Cable TV Subscription Purchased Failed('.$r->service_type.')');

            //    $msg = "Credit Amt: N".number_format($r->amount,2)."<br> Desc: Debit Transaction Reversal(".$r->service_type.") <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$trnxid;
             
            //    $smsmsg = "Credit Amt: N".number_format($r->amount,2)."\n Desc: Debit Transaction Reversal(".$r->service_type.") \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date:" . date('Y-m-d') . "\n Ref: " . $trnxid;
    
            // if(Auth::user()->enable_sms_alert){
            //     $this->sendSms(Auth::user()->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
            //     }

            //  if(Auth::user()->enable_email_alert){
            //  Email::create([
            //     'user_id' =>  Auth::user()->id,
            //     'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
            //     'message' => $msg,
            //     'recipient' => Auth::user()->email,
            // ]);

            //  Mail::send(['html' => 'mails.sendmail'],[
            //      'msg' => $msg,
            //      'type' => 'Credit Transaction'
            //  ],function($mail)use($getsetvalue){
            //      $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
            //        $mail->to(Auth::user()->email);
            //      $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
            //  });
             
            //  }
 
                return response()->json(['status' => false,'message' => 'Cable TV Subscription Purchased Failed'],406);
             }

        }elseif($r->service_type == "dstv" || $r->service_type == "gotv" || $r->service_type == "startimes"){
           
                $validate = Validator::make($r->all(),[
                    'smartcard_number' => ['required','string','numeric'],
                    'amount' => ['required','string','numeric','gt:0'],
                    'service_type' => ['required','string'],
                    'subcription_plan' => ['required','string'],
                    'pin' => ['required','string','numeric','min:4','digits:4'],
                    'platform' => ['required','string']
                ]);
        
                if($validate->fails()){
                    return response()->json(['status' => false,'message' => $validate->errors()->all()[0]],406);
                }
    
                $compbal = $this->validatecompanybalance($r->amount,"vas");
                if($compbal["status"] == false){
            
                    $this->logInfo("validating company balance",$compbal);
                
                return response()->json($compbal,406);
            }
    
                $chkcres = $this->checkCustomerRestriction(Auth::user()->id);
                if($chkcres == true){
        
                    $this->tracktrails('1','1',$usern,'customer','Account Restricted');
                    
                    $this->logInfo("Customer Account Restricted",'');
         
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
                    
                    $this->logInfo("validating Customer balance",$validateuserbalance);
                    
                    return response()->json($validateuserbalance,406);
                }
    
                $getsetvalue = new Setting();
    
                $percentage = $this->getUtilityPercentage();
                $prec = array();
                 foreach($percentage as $percent){
                     if($percent["service"] == $r->service_type){
                         $prec = $percent;
                     }
                 }
         
                 $percentincome = $r->amount * $prec["value"] / 100;
                 $totamount = $r->amount - $percentincome;
         
                 $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('vtpass_income'))->first();
                 
                 if($glacct->status == '1'){
                   $this->create_saving_transaction_gl(null,$glacct->id,null, $percentincome,'credit',$r->platform,null,$this->generatetrnxref('vt'),'vtpass '.$r->service_type.' percentage','approved',$usern.'(c)');
                  
                   $this->gltransaction('withdrawal',$glacct,$percentincome,null); 
                 }
                 
                $customerbal = $this->DebitCustomerandcompanyGlAcct(Auth::user()->id,$r->amount,$totamount,$getsetvalue->getsettingskey('vtpass_account'),'vt','Cable TV Subscription('.$r->service_type.')',$r->platform,$usern.'(c)');
        
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
            
             
                $cblbody = [
                    'request_id' => $trnxid,
                    'serviceID' => $r->service_type,
                    'billersCode' => $r->smartcard_number,
                    'variation_code' => $r->subcription_plan,
                    'amount' => $r->amount,
                    'phone' => $r->phone,
                    'subscription_type' => 'change',
                ];
    
                $response = $this->vtpassposturl($endpoint,$cblbody);
    
                $this->logInfo("vtpass ".$r->service_type." response",$response);
                   //  return $response; 
                 if($response["code"] == "000"){
    
                    if(isset($response["content"]["error"])){
                        return response()->json(['status' => false,'message' => $response['content']['error']]);
                    }
                    
                //companybal
                $this->debitcreditCompanyBalance($r->amount,"debit","vas");

                       $description = "Cable TV Subscription(".$r->service_type.") worth ".$r->amount;
    
                    $this->create_saving_transaction(null, Auth::user()->id,null,$r->amount,'debit',$r->platform,'0',null,null,null,null,$trnxid,$description,'approved','16','utility',$usern.'(c)');
    
                    $this->tracktrails('1','1',$usern,'customer','Cable TV Subscription Purchased Successfully('.$r->service_type.')');
    
                    $msg =  "Debit Amt: N".number_format($r->amount,2)."<br> Desc: Cable TV Subscription Successful(".$r->service_type.")  <br>Avail Bal: N".number_format($customerbal["balance"],2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trnxid;
                   
                    $smsmsg = "Debit Amt: N".number_format($r->amount,2)."\n Desc: Cable TV Subscription Successful(".$r->service_type.") \n Avail Bal: N".number_format($customerbal["balance"],2)."\n Date:" . date('Y-m-d') . "\n Ref: " . $trnxid;
        
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
                 
                    return response()->json(['status' => true,'message' => 'Cable TV Subscription Purchased Successfully'],201);
    
                 }else{
                    
                    $this->create_saving_transaction(null, Auth::user()->id,null,$r->amount,
                    'debit',$r->platform,'0',null,null,null,null,$trnxid,'Cable TV Subscription Purchased Failed('.$r->service_type.')','failed','16','utility',$usern.'(c)');
                    
                    if($glacct->status == '1'){
                    $this->create_saving_transaction_gl(null,$glacct->id,null, $percentincome,'debit',$r->platform,null,$this->generatetrnxref('vt'),'vtpass '.$r->service_type.' percentage reversed','approved',$usern.'(c)');
                    $this->gltransaction('deposit',$glacct,$percentincome,null); 
                    }
                    
            //      $reverd = $this->ReverseDebitTrnxandcompanyGlAcct(Auth::user()->id,$r->amount,$totamount,$trnxid,$getsetvalue->getsettingskey('vtpass_account'),'vt','CableTv Transaction reversed('.$r->service_type.')',$r->platform,'utility',$usern.'(c)',null);
                
            //     //reverse saving acct and current acct Gl
            //              if(Auth::user()->account_type == '1'){//saving acct GL
                         
            //                 if($glsavingdacct->status == '1'){
            //             $this->gltransaction('withdrawal',$glsavingdacct,$r->amount,null);
            //             $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $r->amount,'credit',$r->platform,null,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
            //                 }
                            
            //             }elseif(Auth::user()->account_type == '2'){//current acct GL
                            
            //                 if($glcurrentacct->status == '1'){
            //                 $this->gltransaction('withdrawal',$glcurrentacct,$r->amount,null);
            //             $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $r->amount,'credit',$r->platform,null,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
            //                 }
                            
            //             }
                        
            //      $this->tracktrails('1','1',$usern,'customer','Cable TV Subscription Purchased Failed('.$r->service_type.')');
    
            //     $msg = "Credit Amt: N".number_format($r->amount,2)."<br> Desc: Debit Transaction Reversal(".$r->service_type.") <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$trnxid;
                
            //     $smsmsg = "Credit Amt: N".number_format($r->amount,2)."\n Desc: Debit Transaction Reversal(".$r->service_type.") \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date:" . date('Y-m-d') . "\n Ref: " . $trnxid;
        
            //     if(Auth::user()->enable_sms_alert){
            //         $this->sendSms(Auth::user()->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
            //         }
    
            //     if(Auth::user()->enable_email_alert){
            //      Email::create([
            //          'user_id' =>  Auth::user()->id,
            //          'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
            //          'message' => $msg,
            //          'recipient' => Auth::user()->email,
            //      ]);
    
        
            //      Mail::send(['html' => 'mails.sendmail'],[
            //          'msg' => $msg,
            //          'type' => 'Credit Transaction'
            //      ],function($mail)use($getsetvalue){
            //          $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
            //            $mail->to(Auth::user()->email);
            //          $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
            //      });
            // }
                    return response()->json(['status' => false,'message' => 'Cable TV Subscription Purchased Failed'],406);
                 }
        
        }

        $lock->release();
    }//lock
  }

    public function buy_airtime(Request $r){

      $lock = Cache::lock('byairt-'.mt_rand('1111','9999'),5);

      if($lock->get()){

        $this->logInfo("Airtime topup",$r->all());
            
        $validate = Validator::make($r->all(),[
            'phone_number' => ['required','string','numeric'],
            'amount' => ['required','string','numeric','gt:0'],
            'network_provider' => ['required','string'],
            'pin' => ['required','string','numeric','min:4','digits:4'],
            'platform' => ['required','string']
        ]);

        if($validate->fails()){
            return response()->json(['status' => false,'message' => $validate->errors()->all()[0]],406);
        }

        $trnxid = $this->vtpassrequestid();

        $getsetvalue = new Setting();
        
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;

        $compbal = $this->validatecompanybalance($r->amount,"vas");
        if($compbal["status"] == false){
    
            $this->logInfo("validating company balance",$compbal);
        
        return response()->json($compbal,406);
    }

        $chkcres = $this->checkCustomerRestriction(Auth::user()->id);
            if($chkcres == true){
    
                $this->tracktrails('1','1',$usern,'customer','Account Restricted');
                
                $this->logInfo("customer account restricted",'');
     
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

            $percentage = $this->getUtilityPercentage();
            //return $percentage;
            $prec = array();
             foreach($percentage as $percent){
                 if($percent["service"] == $r->network_provider){
                     $prec = $percent;
                 } 
             }
     
            //  return $prec;

             $percentincome = $r->amount * $prec["value"] / 100;
             $totamount = $r->amount - $percentincome;
             
              $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->first();
                    $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->first();
     
             $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('vtpass_income'))->first();
             
             if($glacct->status == '1'){
               $this->create_saving_transaction_gl(null,$glacct->id,null, $percentincome,'credit',$r->platform,null,$this->generatetrnxref('vt'),'vtpass airtime percentage','approved',$usern.'(c)');
               $this->gltransaction('withdrawal',$glacct,$percentincome,null); 
             }
             
            $customerbal = $this->DebitCustomerandcompanyGlAcct(Auth::user()->id,$r->amount,$totamount,$getsetvalue->getsettingskey('vtpass_account'),'vt','Purchase of Airtime --'.$r->phone_number,$r->platform,$usern.'(c)');
           
            $this->logInfo("debit customer response", $customerbal);
            
             if(Auth::user()->account_type == '1'){//saving acct GL
                        
                    $this->gltransaction('deposit',$glsavingdacct,$r->amount,null);
                $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $r->amount,'debit',$r->platform,null,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
                    
                }elseif(Auth::user()->account_type == '2'){//current acct GL
                    
                    $this->gltransaction('deposit',$glcurrentacct,$r->amount,null);
                $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $r->amount,'debit',$r->platform,null,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
                    
                }
        
            
             

        $endpoint = $this->vtpassurl."pay";

        $body = [
            'request_id' => $trnxid,
            'serviceID' => $r->network_provider,
            'amount' => $r->amount,
            'phone' => $r->phone_number,
        ];

        $response = $this->vtpassposturl($endpoint,$body);

        $this->logInfo("Airtime response",$response);
 
        if($response['code'] == "000"){
 
            //companybal
            $this->debitcreditCompanyBalance($r->amount,"debit","vas");
            
            $description = "Purchase of Airtime worth ".$r->amount." --".$r->phone_number." -trxid:".$response["content"]["transactions"]["transactionId"];

            $this->create_saving_transaction(null, Auth::user()->id,null,$r->amount,'debit',$r->platform,'0',null,null,null,null,$trnxid,$description,'approved','14','utility',$usern.'(c)');

            $this->tracktrails('1','1',$usern,'customer',$description);

             $msg = "Debit Amt: N".number_format($r->amount,2)."<br> Desc: Airtime Purchased successfully  <br>Avail Bal: N".number_format($customerbal["balance"],2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trnxid;
            
             $smsmsg = "Debit Amt: N".number_format($r->amount,2)."\n Desc: Airtime Purchased successfully \n Avail Bal: N".number_format($customerbal["balance"],2)."\n Date:" . date('Y-m-d') . "\n Ref: " . $trnxid;
    
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
           
            return response()->json(['status' => true,'message' => 'Airtime Purchased successfully']);

         }else{

            $this->create_saving_transaction(null, Auth::user()->id,null,$r->amount,
            'debit',$r->platform,'0',null,null,null,null,$trnxid,'Failed to Purchase Airtime','failed','14','utility',$usern.'(c)');
            
            if($glacct->status == '1'){
            $this->create_saving_transaction_gl(null,$glacct->id,null, $percentincome,'debit',$r->platform,null,$this->generatetrnxref('vt'),'vtpass airtime percentage reversed','approved',$usern.'(c)');
            $this->gltransaction('deposit',$glacct,$percentincome,null); 
            }
            
        //  $reverd = $this->ReverseDebitTrnxandcompanyGlAcct(Auth::user()->id,$r->amount,$totamount,$trnxid,$getsetvalue->getsettingskey('vtpass_account'),'vt','Airtime Purchased Transaction reversed',$r->platform,'utility',$usern.'(c)',null);
                     
        //      //reverse saving acct and current acct Gl
        //      if(Auth::user()->account_type == '1'){//saving acct GL
                
        //         if($glsavingdacct->status == '1'){
        //     $this->gltransaction('withdrawal',$glsavingdacct,$r->amount,null);
        //     $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $r->amount,'credit',$r->platform,null,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
        //         }
                
        //     }elseif(Auth::user()->account_type == '2'){//current acct GL
                
        //         if($glcurrentacct->status == '1'){
        //         $this->gltransaction('withdrawal',$glcurrentacct,$r->amount,null);
        //     $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $r->amount,'credit',$r->platform,null,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
        //         }
                
        //     }
                    
        //  $this->tracktrails('1','1',$usern,'customer','Failed to Purchased Airtime');
 
        //  $msg = "Credit Amt: N".number_format($r->amount,2)."<br> Desc: Debit Transaction Reversal <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$trnxid;
        
        //  $smsmsg = "Credit Amt: N".number_format($r->amount,2)."\n Desc: Debit Transaction Reversal \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date:" . date('Y-m-d') . "\n Ref: " . $trnxid;
    
        //  if(Auth::user()->enable_sms_alert){
        //      $this->sendSms(Auth::user()->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
        //      }

        // if(Auth::user()->enable_email_alert){
        //  Email::create([
        //      'user_id' =>  Auth::user()->id,
        //      'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
        //      'message' => $msg,
        //      'recipient' => Auth::user()->email,
        //  ]);

        //  Mail::send(['html' => 'mails.sendmail'],[
        //      'msg' => $msg,
        //      'type' => 'Credit Transaction'
        //     ],function($mail)use($getsetvalue){
        //      $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
        //        $mail->to(Auth::user()->email);
        //      $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
        //  });
        //  }
         
            return response()->json(['status' => false,'message' => 'Failed to Purchase Airtime']);
         }

         $lock->release();
       }//lock
    }
    
    public function getdatabundles($networktype){
        if(empty($networktype)){
            return response()->json(['status' => false,'message' => "Sorry parameter is empty"],406);
        }

        $endpoint = $this->vtpassurl."service-variations?serviceID=".$networktype;

         $response = $this->vtpassgeturl($endpoint);

         if($response['response_description'] == "000"){
            return response()->json(['status' => true,'message' => 'Data Bundles fetched successfully','data' => $response['content']['varations']]);
         }else{
            return response()->json(['status' => false,'message' => 'failed to fetch Data Bundles','data' => []]);
         }
    }

    public function getDataBundlesList($networkProvider)
    {
        $endpoint = $this->vtpassurl."service-variations?serviceID=".$networkProvider;

        $response = $this->vtpassgeturl($endpoint);

        if ($response['response_description'] == "000") {
            return ['status' => true, 'message' => 'Data Bundles fetched successfully', 'data' => $response['content']['varations']];
        } else {
            return ['status' => false, 'message' => 'Failed to fetch Data bundles', 'data' => []];
        }
    }

    public function buy_data_bundle(Request $r)
    {
        $lock = Cache::lock('bydatbn-'.mt_rand('1111','9999'),5);
            if($lock->get()){

        $this->logInfo("buy data bundle",$r->all());
        $validate = Validator::make($r->all(),[
            'phone_number' => ['required','string','numeric'],
            'network_provider' => ['required','string'],
            'data_plan' => ['required','string'],
            'pin' => ['required','string','numeric','min:4','digits:4'],
            'platform' => ['required','string']
        ]);

        if($validate->fails()){
            return response()->json(['status' => false,'message' => $validate->errors()->all()[0]],406);
        }
        
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;

        $getsetvalue = new Setting();

        $trnxid = $this->vtpassrequestid();
       
        $chkcres = $this->checkCustomerRestriction(Auth::user()->id);
        if($chkcres == true){
            
            $this->tracktrails('1','1',$usern,'customer','Account Restricted');

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
            
            $this->logInfo("validating lcustomer pin",$validateuserpin);
            
            return response()->json($validateuserpin,406);
        }

        $selectedbundle = array();
        $isplanvalid = false;
       $databundles = $this->getDataBundlesList($r->network_provider);
        // return $databundles;
       if ($databundles['status'] == false) {
           return response()->json(["status" => false, 'message' => "Failed to get Data bundles"], 400);
       }

         foreach($databundles["data"] as $databundle){
            if($databundle["variation_code"] == $r->data_plan){
               $selectedbundle = $databundle;
               $isplanvalid = true;
            }
         }
       
         if (!$isplanvalid) {
           return response()->json(["status" => false, 'message' => "Invalid Data bundles"], 400);
       }

       $compbal = $this->validatecompanybalance($selectedbundle["variation_amount"],"vas");
       if($compbal["status"] == false){
   
           $this->logInfo("validating company balance",$compbal);
       
       return response()->json($compbal,406);
   }

       $validateuserbalance = $this->validatecustomerbalance(Auth::user()->id, $selectedbundle["variation_amount"]);
        if($validateuserbalance["status"] == false){

            $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
            
            $this->logInfo("validating custome balance",$validateuserbalance);
            
            return response()->json($validateuserbalance,406);
        }
        
       $percentage = $this->getUtilityPercentage();
       $prec = array();
        foreach($percentage as $percent){
            if($percent["service"] == $r->network_provider){
                $prec = $percent;
            }
        }

        $percentincome = $selectedbundle["variation_amount"] * $prec["value"] / 100;
        $totamount = $selectedbundle["variation_amount"] - $percentincome;
        
         $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->first();
        $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->first();

        $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('vtpass_income'))->first();

    if($glacct->status == '1'){
          $this->create_saving_transaction_gl(null,$glacct->id,null,$percentincome,'credit',$r->platform,null,$this->generatetrnxref('vt'),'vtpass data percentage','approved',$usern.'(c)');
          $this->gltransaction('withdrawal',$glacct, $percentincome,null); 
        }

        $customerbal = $this->DebitCustomerandcompanyGlAcct(Auth::user()->id,$selectedbundle["variation_amount"],$totamount,$getsetvalue->getsettingskey('vtpass_account'),'vt','Data Bundles Purchased '.$r->phone_number,$r->platform,$usern.'(c)');

        $this->logInfo("debit customer response", $customerbal);
        
         
             if(Auth::user()->account_type == '1'){//saving acct GL
                        
                        if($glsavingdacct->status == '1'){
                    $this->gltransaction('deposit',$glsavingdacct,$selectedbundle["variation_amount"],null);
                $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $selectedbundle["variation_amount"],'debit',$r->platform,null,$this->generatetrnxref('W'),'customer debited','approved',$usern.'(c)');
                        }
                        
                }elseif(Auth::user()->account_type == '2'){//current acct GL
                    
                    if($glcurrentacct->status == '1'){
                    $this->gltransaction('deposit',$glcurrentacct,$selectedbundle["variation_amount"],null);
                $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $selectedbundle["variation_amount"],'debit',$r->platform,null,$this->generatetrnxref('W'),'customer debited','approved',$usern.'(c)');
                    }
                    
                }
    
        
        $endpoint = $this->vtpassurl."pay";

        $body = [
            'request_id' => $trnxid,
            'serviceID' => $r->network_provider,
            'billersCode' => $r->phone_number,
            'variation_code' => $r->data_plan,
            'amount' => $selectedbundle["variation_amount"],
            'phone' => $r->phone_number,
        ];

        $response = $this->vtpassposturl($endpoint,$body);
        
        $this->logInfo("data bundle response",$response);

        if($response['code'] == "000"){

                //companybal
                $this->debitcreditCompanyBalance($selectedbundle["variation_amount"],"debit","vas");

            $description = "Data Bundles Purchased worth ".$selectedbundle["variation_amount"]." --".$r->phone_number." -trxid:".$trnxid;

            $this->create_saving_transaction(null, Auth::user()->id,null,$selectedbundle["variation_amount"],'debit',$r->platform,'0',null,null,null,null,$trnxid,$description,'approved','15','utility',$usern.'(c)');

            $this->tracktrails('1','1',$usern,'customer',$description);

           
            $msg = "Debit Amt: N".number_format($selectedbundle["variation_amount"],2)."<br> Desc: Data Bundles Purchased successfully <br>Avail Bal: N".number_format($customerbal["balance"],2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trnxid;
       
            $smsmsg = "Debit Amt: N".number_format($selectedbundle["variation_amount"],2)."\n Desc:Data Bundles Purchased successfully \n Avail Bal: N".number_format($customerbal["balance"],2)."\n Date:" . date('Y-m-d') . "\n Ref: " . $trnxid;
    
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

            return response()->json(['status' => true, 'message' => 'Data Purchased successfully']);

         }else{

            $this->create_saving_transaction(null, Auth::user()->id,null,$selectedbundle["variation_amount"],
            'debit',$r->platform,'0',null,null,null,null,$trnxid,'Failed to Purchased Data Bundles','failed','15','utility',$usern.'(c)');
            
            if($glacct->status == '1'){
            $this->create_saving_transaction_gl(null,$glacct->id,null, $percentincome,'debit',$r->platform,null,$this->generatetrnxref('vt'),'vtpass percentage','approved',$usern.'(c)');
            $this->gltransaction('deposit',$glacct,$percentincome,null); 
            }
            
        //  $reverd = $this->ReverseDebitTrnxandcompanyGlAcct(Auth::user()->id,$selectedbundle["variation_amount"],$totamount,$trnxid,$getsetvalue->getsettingskey('vtpass_account'),'vt','Data Bundles Purchased Transaction reversed',$r->platform,'utility',$usern.'(c)',null);
           
        //     //reverse saving acct and current acct Gl
        //      if(Auth::user()->account_type == '1'){//saving acct GL
                
        //         if($glsavingdacct->status == '1'){
        //     $this->gltransaction('withdrawal',$glsavingdacct,$selectedbundle["variation_amount"],null);
        //     $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $selectedbundle["variation_amount"],'credit',$r->platform,null,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
        //         }
                
        //     }elseif(Auth::user()->account_type == '2'){//current acct GL
                
        //         if($glcurrentacct->status == '1'){
        //         $this->gltransaction('withdrawal',$glcurrentacct,$selectedbundle["variation_amount"],null);
        //     $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $selectedbundle["variation_amount"],'credit',$r->platform,null,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
        //         }
                
        //     }
         
        //  $this->tracktrails('1','1',$usern,'customer','Failed to Purchased Data Bundles');

        //  $msg = "Credit Amt: N".number_format($selectedbundle["variation_amount"],2)."<br> Desc: Debit Transaction Reversal <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$trnxid;
        
        //  $smsmsg = "Credit Amt: N".number_format($selectedbundle["variation_amount"],2)."\n Desc: Debit Transaction Reversal \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date:" . date('Y-m-d') . "\n Ref: " . $trnxid;
    
        //  if(Auth::user()->enable_sms_alert){
        //      $this->sendSms(Auth::user()->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
        //      }

        // if(Auth::user()->enable_email_alert){
        //  Email::create([
        //      'user_id' =>  Auth::user()->id,
        //      'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
        //      'message' => $msg,
        //      'recipient' => Auth::user()->email,
        //  ]);

        //  Mail::send(['html' => 'mails.sendmail'],[
        //      'msg' => $msg,
        //      'type' => 'Credit Transaction'
        //     ],function($mail)use($getsetvalue){
        //      $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
        //        $mail->to(Auth::user()->email);
        //      $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
        //  });
        // }
        
            return response()->json(['status' => false, 'message' => 'Failed to Purchased Data Bundles','data' => []]);
         }

         $lock->release();
        }//lock
    }

    public function verify_meter_number(Request $r)
    {
        $this->logInfo("validating meter number",$r->all());
        
        $validate = Validator::make($r->all(),[
            'meter_number' => ['required','string','numeric'],
            'service_provider' => ['required','string'],
            'meter_type' => ['required','string'],
        ]);

        if($validate->fails()){
            return response()->json(['status' => false,'message' => $validate->errors()->all()[0]],406);
        }

        $trnxid = $this->vtpassrequestid();
       
        $endpoint = $this->vtpassurl."merchant-verify";

        $body = [
            'billersCode' => $r->meter_number,
            'serviceID' => $r->service_provider,
            'type' => $r->meter_type,
        ];

        $response = $this->vtpassposturl($endpoint,$body);
        
        $this->logInfo("meter response",$response);
  //return $response;
        if($response['code'] == "000"){

            if(isset($response["content"]["error"])){
                return response()->json(['status' => false,
                'message' => $response["content"]["error"]]);
            }

            return response()->json(['status' => true,
            'message' => 'Meter Number Verified Successfully',
             'data' =>[
                'meter_name' => trim($response['content']['Customer_Name']),
                'address' => trim($response['content']['Address']),
                'meter_type' => trim($r->meter_type),
                'meter_number' => $r->service_provider == "abuja-electric" ? $response['content']['MeterNumber'] : $response['content']['Meter_Number'],
            ]]);

         }else{
            return response()->json(['status' => false, 'message' => 'failed to verified meter Number','data' => []]);
         }
    }

    public function pay_electricty(Request $r)
    {
        $lock = Cache::lock('pyele-'.mt_rand('1111','9999'),5);
            if($lock->get()){
        
        $this->logInfo("buy electricity",$r->all());
        
        $validate = Validator::make($r->all(),[
            'meter_number' => ['required','string','numeric'],
            'amount' => ['required','string','numeric','gt:0'],
            'phone_number' => ['required','string','numeric'],
            'service_provider' => ['required','string'],
            'meter_type' => ['required','string'],
            'platform' => ['required','string']
        ]);

          
        if($validate->fails()){
            return response()->json(['status' => false,'message' => $validate->errors()->all()[0]],406);
        }
        
          $usern = Auth::user()->last_name." ".Auth::user()->first_name;

           
        if($r->amount < "500"){
            return response()->json(['status' => false,'message' => 'Invalid Amount Entered... amount must be 500 and above'],406);
        }

        $trnxid = $this->vtpassrequestid();
      
        $compbal = $this->validatecompanybalance($r->amount,"vas");
        if($compbal["status"] == false){
    
            $this->logInfo("validating company balance",$compbal);
        
        return response()->json($compbal,406);
        }

        $chkcres = $this->checkCustomerRestriction(Auth::user()->id);
        if($chkcres == true){
    
            $this->tracktrails('1','1',$usern,'customer','Account Restricted');

            $this->logInfo("Customer Account Restricted",'');
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
            
            $this->logInfo("validating custome balance",$validateuserbalance);
            
            return response()->json($validateuserbalance,406);
        }

        $getsetvalue = new Setting();

        $percentage = $this->getUtilityPercentage();
        $prec = array();
         foreach($percentage as $percent){
             if($percent["service"] == $r->service_provider){
                 $prec = $percent;
             }
         }
 
         $percentincome = $r->amount * $prec["value"] / 100;
         $totamount = $r->amount - $percentincome;
         
          $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->first();
        $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->first();
 
         $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('vtpass_income'))->first();
         
         if($glacct->status == '1'){
           $this->create_saving_transaction_gl(null,$glacct->id,null,$percentincome,'credit',$r->platform,null,$this->generatetrnxref('vt'),'vtpass electricity percentage','approved',$usern.'(c)');
           $this->gltransaction('withdrawal',$glacct,$percentincome,null); 
         }
         
        $customerbal = $this->DebitCustomerandcompanyGlAcct(Auth::user()->id,$r->amount,$totamount,$getsetvalue->getsettingskey('vtpass_account'),'vt','Purchase of Electricity Unit',$r->platform,$usern.'(c)');

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
    
             
        $endpoint = $this->vtpassurl."pay";

        $body = [
            "request_id" => $trnxid,
            "billersCode" => $r->meter_number,
            "serviceID" => $r->service_provider,
            "variation_code" => $r->meter_type,
            "amount" => $r->amount,
            "phone" => $r->phone_number
        ];

        $response = $this->vtpassposturl($endpoint,$body); 
        
        $this->logInfo('',$response);
        
    //return $response;
        if($response['code'] == "000"){
            $description =  $r->meter_type == "prepaid" ? "Purchased Electricity Token: ".$response["purchased_code"] : "Purchased Electricity Worth Amount: ".$r->amount;
             $unit = $r->meter_type == "prepaid" ? "Unit: ".$response['units'] : "";

            if ($response['content']['transactions']['status'] == 'pending') {
                return response()->json(
                    [
                        "status" => true,
                        'message' => "Transaction is Processing. you will receive a token shortly"
                    ]
                );
            } 

                 //companybal
                 $this->debitcreditCompanyBalance($r->amount,"debit","vas");

            $returnResponse = $r->meter_type == 'prepaid' ? [
                'token' => $response["purchased_code"],
                'purchased_units' => $unit
            ] : [];

            $this->create_saving_transaction(null, Auth::user()->id,null,$r->amount,'debit',$r->platform,'0',null,null,null,null,
            $trnxid,$description,'approved','17','utility',$usern);

            $this->tracktrails('1','1',$usern,'customer',$description);
    
            $msg = "Debit Amt: N".number_format($r->amount,2)."<br> Desc: Electricity Unit Purchased Successfully <br>".$description." <br>Avail Bal: N".number_format($customerbal["balance"])."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trnxid;
            
            $smsmsg = "Debit Amt: N".number_format($r->amount,2)."\n Desc: Electricity Unit Purchased Successfully \n Avail Bal: N".number_format($customerbal["balance"],2)."\n Date:" . date('Y-m-d') . "\n Ref: " . $trnxid;
    
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
        
            return response()->json(['status' => true, 'message' => 'Electricity Unit Purchased Successfully', 'data' =>  $returnResponse]);

         }else{

            
            $this->create_saving_transaction(null, Auth::user()->id,null,$r->amount,
            'debit',$r->platform,'0',null,null,null,null,$trnxid,'Failed to Purchase Electricity Unit','failed','17','utility',$usern.'(c)');

if($glacct->status == '1'){
            $this->create_saving_transaction_gl(null,$glacct->id,null, $percentincome,'debit',$r->platform,null,$this->generatetrnxref('vt'),'vtpass electricity percentage reversed','approved',$usern.'(c)');
            $this->gltransaction('deposit',$glacct,$percentincome,null); 
}

        //  $reverd = $this->ReverseDebitTrnxandcompanyGlAcct(Auth::user()->id,$r->amount,$totamount,$trnxid,$getsetvalue->getsettingskey('vtpass_account'),'vt','Electricity Unit Purchased Transaction reversed',$r->platform,'utility',$usern.'(c)',null);
        
        //     //reverse saving acct and current acct Gl
        //              if(Auth::user()->account_type == '1'){//saving acct GL
                        
        //                 if($glsavingdacct->status == '1'){
        //             $this->gltransaction('withdrawal',$glsavingdacct,$r->amount,null);
        //             $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $r->amount,'credit',$r->platform,null,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
        //                 }
                        
        //             }elseif(Auth::user()->account_type == '2'){//current acct GL
                        
        //                 if($glcurrentacct->status == '1'){
        //                 $this->gltransaction('withdrawal',$glcurrentacct,$r->amount,null);
        //             $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $r->amount,'credit',$r->platform,null,$this->generatetrnxref('D'),'customer debited','approved',$usern.'(c)');
        //                 }
                        
        //             }
        
        //  $this->tracktrails('1','1',$usern,'customer','Failed to Purchase Electricity Unit');

        //   $msg = "Credit Amt: N".number_format($r->amount,2)."<br> Desc: Debit Transaction Reversal for Electricity Purchase <br>Avail Bal: N".number_format($reverd["balance"],2)."<br>Date: ".date("Y-m-d")."<br>Ref: ".$trnxid;
        //   $smsmsg = "Credit Amt: N".number_format($r->amount,2)."\n Desc: Debit Transaction Reversal for Electricity Purchase \n Avail Bal: N".number_format($reverd["balance"],2)."\n Date:" . date('Y-m-d') . "\n Ref: " . $trnxid;
    
        // if(Auth::user()->enable_sms_alert){
        //     $this->sendSms(Auth::user()->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
        //     }

        //  if(Auth::user()->enable_email_alert){
        //  Email::create([
        //      'user_id' =>  Auth::user()->id,
        //      'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
        //      'message' => $msg,
        //      'recipient' => Auth::user()->email,
        //  ]);

        //  Mail::send(['html' => 'mails.sendmail'],[
        //      'msg' => $msg,
        //      'type' => 'Credit Transaction'
        //     ],function($mail)use($getsetvalue){
        //      $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
        //        $mail->to(Auth::user()->email);
        //      $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
        //  });
        //  }
         
            return response()->json(['status' => false,'message' => 'Failed to Purchase Electricity Unit','data' => []]);
         }

         $lock->release();
         }//lock
    }


}//endclass

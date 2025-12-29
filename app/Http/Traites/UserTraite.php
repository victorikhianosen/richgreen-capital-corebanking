<?php
namespace App\Http\Traites;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Setting;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

trait UserTraite
{
    public function generatTwofactorcode($userid){
        // dd($userid);
        $user = User::where('id',$userid)->first();
       
        if(is_null($user->is_2fa_enable)){
    
         $getsetvalue = Setting::first();
 
           $google2fa = new Google2FA(request());
           $secretKey = $google2fa->generateSecretKey();
    
           $qrCodeUrl = $google2fa->getQRCodeUrl($getsetvalue->getsettingskey('company_name'),$user->email, $secretKey);
    
             $user->timestamps = false;
             $user->two_factor_code = $secretKey;
             $user->two_factor_expire_at = Carbon::now();
             $user->save();
    
            session()->put('2FA',[
                'secret' => $secretKey,
                'qrcodeurl' => $qrCodeUrl
            ]);
    
        }
 }

    public function resetTwoFactorcode($userid){
        $user = User::where('id',$userid)->first();
      
         $user->timestamps = false;
         $user->two_factor_code = Null;
         $user->two_factor_expire_at = Null;
         $user->save();
    }

    public function sendtwofactormail($code,$fname,$lname,$email){
        $getsetvalue = new Setting();

        Mail::send(['html' => 'mails.sendmail'],[
            'msg' => 'Hi '.$fname.' '.$lname.'<br><br> Your Code is '.$code.' please do not share code with anyone only use it on '.config('name','BanqPro').' <br><br> these code expires in 5 minutes.',
            'type' => 'Authentication Code'
        ], function($mail) use($email,$getsetvalue) {
            $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
            $mail->to($email);
            $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Two Factor Authentication Code');
        });
    }
    
     public function logInfo($title = "No Title", $content=null)
    {
        Log::info("*************");
        Log::info($title);
        Log::info("**************************");
        Log::info($content);
    }
    
     public function sendSms($recipient,$msg,$typ){
        $getsetvalue = new Setting();
          $cmpname = ucwords($getsetvalue->getsettingskey('sms_sender'));
       
        if($getsetvalue->getsettingskey('sms_enabled') == "1"){
            if($typ == "vtpass"){
             $responses = Http::withHeaders([
                 "Content-Type" => "application/json",
                 "X-Token" => $getsetvalue->getsettingskey('sms_public_key'),
                 "X-Secret" => $getsetvalue->getsettingskey('sms_secret_key')
                 ])->get($getsetvalue->getsettingskey('sms_baseurl')."?sender=".$cmpname."&recipient=".$recipient."&message=".$msg."&responsetype=json");
 
             $this->logInfo("vtpass sms", $responses);
 
         }elseif($typ == "termii"){
             $responses = Http::withHeaders([
                 "Content-Type" => "application/json" 
                 ])->post($getsetvalue->getsettingskey('sms_baseurl')."sms/send",[
                 "api_key" => $getsetvalue->getsettingskey('sms_public_key'),
                 "to" => "234".substr($recipient,1),
                 "from" => $cmpname,
                 "sms" => $msg,
                 "type" => "plain",
                 "channel" => "generic"
                 ]);
                 
             $this->logInfo("termii sms", $responses);
         }
        }
     }
     
       public function diffbtwdate($start,$end){
          $startdate = Carbon::parse($start);
          $enddate = Carbon::parse($end);

          $nmdays = $startdate->diffInDays($enddate);

          return  $nmdays;
     }
}//endclass

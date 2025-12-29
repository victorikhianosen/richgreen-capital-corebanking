<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traites\AuditTraite;
use App\Http\Traites\SavingTraite;
use App\Http\Traites\UserTraite;
use App\Models\Customer;
use App\Models\Email;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use SavingTraite;
    use AuditTraite;
    use UserTraite;

    public function register(Request $r){
        
        $this->logInfo("Account registration log",$r->all());
        
        $validator = Validator::make($r->all(), [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone' => 'required|string',
            'email' => 'required|email:rfc,dns|string',
            'dob' => 'required|date',
            'gender' => 'required|string',
            'username' => 'required|string',
            'pin' => 'required|numeric|gt:0|min:4',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => false, "message" => $validator->errors()->all()[0]], 406);
        }

        $otpCode = $this->generateOTP();

        $getsetvalue = new Setting();
    if ($em = Customer::where('email', $r->email)->first()) {
          $ra = array("status" => false, "message" => "Account with these email already exist");
          $this->logInfo("current user",$ra);
          return response()->json($ra, 409);
      }
      if($phn = Customer::where('phone', $r->phone)->first()){
           $ra = array("status" => false, "message" => "Account with these phone number already exist");
           $this->logInfo("current user",$ra);
          return response()->json($ra, 409);
      }
      if($u = Customer::where('username', $r->username)->first()){
          $ra = array("status" => false, "message" => "Account with these username already exist");
          $this->logInfo("current user",$ra);
          return response()->json($ra, 409);
      }
      
    if(!empty($r->bvn)){
          if($u = Customer::where('bvn',$r->bvn)->first()){
            $ra = array("status" => false, "message" => "Account with this bvn already exists");
            $this->logInfo("current user",$ra);
            return response()->json($ra, 409);
        }
    }
     
        $refe = Str::random(6);
        //substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1, 8)), 1, 7);
        $account_number = '101'.mt_rand('1111111','9999999');

        $userdata = [
            'first_name' => $r->first_name,
            'last_name' => $r->last_name,
            'email' => $r->email,
            'phone' => $r->phone,
            'gender' => strtolower($r->gender),
            'dob' => $r->dob,
            'account_type' => '1',
            'acctno' => $account_number,
            'username' => $r->username,
            'password' => Hash::make($r->password),
            'pin' => Hash::make($r->pin),
            'otp' => $otpCode,
            'otp_expiration_date' => Carbon::now()->addMinutes(10),
            'referral_code' => strtolower($refe),
            'transfer_limit' => '500000',
            'reg_date' => Carbon::now(),
            'source' => 'online',
            'status' => '1',
            'enable_email_alert' => '1',
             'enable_sms_alert' => '1',
         ];
         
         $cusid = Customer::create($userdata);
         //adding to savings
         $this->create_account(null,$cusid->id,'1');

       $msg = "welcome ".$r->last_name." ".$r->first_name." below is account details <br>Username:".$r->username."<br>Password: ".$r->password."
         <br>Transaction Pin: ".$r->pin."<br> Your Account No: ".$account_number."<br> Bank: ".ucwords($getsetvalue->getsettingskey('company_name'))."<br><br> Below is your OTP <br> ".$otpCode;
         Email::create([
            'user_id' => $cusid->id,
            'subject' => ucwords($getsetvalue->getsettingskey('company_name'))." Account Registration",
            'message' => $msg,
            'recipient' => $r->email,
        ]);

           
            Mail::send(['html' => 'mails.sendmail'],[
                'msg' => $msg,
             'type' => 'Registration Successful'
            ],function($mail)use($getsetvalue,$r){
                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                 $mail->to($r->email);
                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name'))." Account Registration");
            });
         

         $usern = $r->last_name." ".$r->first_name;
         $this->tracktrails('1','1',$usern,'customer','new customer account created');

        if($cusid){
            
            if($getsetvalue->getsettingskey('enable_virtual_ac') == '1'){
                $response= Http::withHeaders([
                      "PublicKey" => env('PUBLIC_KEY'),
                      "EncryptKey" => env('ENCRYPT_KEY'),
                      "Content-Type" => "application/json",
                      "Accept" => "application/json"
                  ])->post(env('VIRTUAL_ACCOUNT_URL'),[
                     "settlement_accountno" => env('SETTLEMENT_ACCOUNT'),
                       "account_name" =>  $r->last_name." ".$r->first_name ,
                       "accountno" =>  $account_number
                  ]);
           }
           
            $this->logInfo("Account Registration Successful",$userdata);

            return response()->json(["status" => true, "message" => 'Account Created Successfully'],201);
         }else{
            $this->logInfo("","Account Registration Failed");
            return response()->json(["status" => true, "message" => 'Failed to Created Account'],201);
         }
    }


 public function existingAccount(Request $r){
        $this->logInfo("Verifying Exiting Account",$r->all());
        
        $validation = Validator::make($r->all(), [
            "account_number" => 'required|string|max:10',
        ]);
        
        if ($validation->fails()) {
            $ra = array("status" => false, "message" => $validation->errors()->all()[0]);
            return response()->json($ra, 406);
        }
        $otpCode = $this->generateOTP();

        $getsetvalue = new Setting();

        $exacct = Customer::where('acctno',$r->account_number)->first();
                            
            $passwrd = mt_rand("11111111","99999999");
            
        if($exacct){
           if($exacct->phone_verify == 0){
                $exacct->username = $r->account_number;
            $exacct->password = Hash::make($passwrd);
            $exacct->status = '1';
            $exacct->enable_email_alert = '1';
             $exacct->enable_sms_alert = '1';
             $exacct->phone_verify = '1';
            $exacct->save();

        $ver =   ['status' => true, 'acctexist' => 1,'email' => $exacct->email,'message' => "Account Verified Successfully"];

             $this->logInfo("verified",$ver);
             
               
             $msg = "welcome ".$exacct->last_name." ".$exacct->first_name." <br>Below is your Login Details <br> Username: ".$r->account_number." Password: ".$passwrd;
             
            if(!is_null($exacct->email)){
                 Email::create([
                'user_id' => $exacct->id,
                'subject' => ucwords($getsetvalue->getsettingskey('company_name'))." Account Confirmation",
                'message' => $msg,
                'recipient' => $exacct->email,
            ]);
    
               
                Mail::send(['html' => 'mails.sendmail'],[
                    'msg' => $msg,
                 'type' => 'Account Confirmation'
                ],function($mail)use($getsetvalue,$exacct){
                    $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                     $mail->to($exacct->email);
                    $mail->subject(ucwords($getsetvalue->getsettingskey('company_name'))." Account Confirmation");
                });
            }

            return response()->json($ver);
        }else{
            return response()->json(['status' => false, 'message' => "Account already linked...Please Login",]);
        }
           }else{
                return response()->json(['status' => false, 'message' => "Account Number Not Found",]);
           }
        
    }
}//endclass

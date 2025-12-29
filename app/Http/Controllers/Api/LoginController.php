<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traites\LastloginTraite;
use App\Http\Traites\UserTraite;
use App\Http\Traites\SavingTraite;
use App\Models\Customer;
use App\Models\Saving;
use App\Models\Email;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    use LastloginTraite;
    use SavingTraite;
    use UserTraite;
    
    public function login(Request $r){
        
         $this->logInfo("customer login",$r->all());
         
        $validate = Validator::make($r->all(), [
            'username' => ['required', 'string'],
            'password' => ['required', 'string']
        ]);


        if ($validate->fails()) {
            return response()->json(["status" => false, "message" => "Username or Password fields is required"], 406);
        }

        $getsetvalue = new Setting();

        if(Auth::guard('customer')->attempt(['username' => $r->username, 'password' => $r->password])){
           
            $user = Customer::where('username', $r->username)->first();

            if ($user->status == "6"){
                return response()->json(['status' => false, 'message' => 'Your Account Has Been Blocked. Please contact support'], 401);
              }elseif($user->status == "5"){
                  return response()->json(['status' => false, 'message' => 'Your Account Has Blocked Due to Fraudulent Attack. Please contact support or visit any of our branches'], 401);
              }elseif($user->status == "4"){
                  return response()->json(['status' => false, 'message' => 'Your Account Has Been Restricted. Please contact support'], 401);
              }elseif($user->status == "2"){
                  return response()->json(['status' => false, 'message' => 'Your Account Has Been Closed. Please contact support'], 401);
              }elseif($user->status == "8"){
                  return response()->json(['status' => false, 'message' => 'Your Account is Dormant or Inactive. Please contact support or visit any of our branches'], 401);
              }elseif($user->status == "7"){
                  return response()->json(['status' => false, 'message' => 'Your Account Is Currently Being Reviewed And Will Be Approved Soon'], 401);
              }

            if(Auth::guard('customer')->user()->phone_verify == 1){
                $user->failed_logins = NUll;
                $user->last_login = Carbon::now();
                $user->save();

                $savings = Saving::where('customer_id',Auth::guard('customer')->user()->id)->first();

                $ulocation = 'uploads';
                $userDetails = [
                    "userid" => Auth::guard('customer')->user()->id,
                   "first_name" => Auth::guard('customer')->user()->first_name,
                   "last_name" => Auth::guard('customer')->user()->last_name,
                   "phone" => Auth::guard('customer')->user()->phone,
                   "profilepic" => url('/')."/".Auth::guard('customer')->user()->photo,
                   "username" => Auth::guard('customer')->user()->username,
                   "bvn" => Auth::guard('customer')->user()->bvn,
                   "nin" => Auth::guard('customer')->user()->nin,
                   "email" => Auth::guard('customer')->user()->email,
                   "address" => Auth::guard('customer')->user()->address,
                   "sex" => Auth::guard('customer')->user()->sex,
                   "accountno" => Auth::guard('customer')->user()->acctno,
                   "balance" => $savings->account_balance,
                   "currency" => $getsetvalue->getsettingskey('currency_symbol'),
               ];

               $usertoken = Auth::guard('customer')->user();
                $accessToken = $usertoken->createToken('customerToken',['customer'])->plainTextToken;
                //createToken('authToken')->accessToken;
                return response(['status' => true, 'message' => 'Login Successful', 'data' => ['user' => $userDetails, 'access_token' => $accessToken]]);

            }else{
                $otpCode = $this->generateOTP();

                $userauth = Customer::where('id', Auth::guard('customer')->user()->id)->first();
                $userauth->otp = $otpCode;
                $userauth->otp_expiration_date = Carbon::now()->addMinutes(5);
                $userauth->save();

                $msg = "You request for OTP: ".$otpCode."<br> Do not share with anyone.";
                 Email::create([
                     'user_id' => Auth::guard('customer')->user()->id,
                     'subject' => ucwords($getsetvalue->getsettingskey('company_name'))." OTP Confirmation",
                     'message' => $msg,
                     'recipient' => Auth::guard('customer')->user()->email,
                 ]);
                 
                Mail::send(['html' => 'mails.sendmail'],[
                    'msg' => $msg,
                    'type' => "OTP Confirmation"
                ],function($mail)use($getsetvalue,$userauth){
                    $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                     $mail->to($userauth->email);
                    $mail->subject(ucwords($getsetvalue->getsettingskey('company_name'))." OTP Confirmation");
                });
                
              return response()->json(['status' => false, 'message' => 'Account Pending Verification', 'data' => '203'], 203);
            }

        }else{
                $user = Customer::where('username', $r->username)->first();
            if ($user) {
                if ($user->failed_logins >= 3){
                    $user->status = 6;
                    $user->save();
                    return response()->json(['status' => false, 'message' => 'Your Account has been blocked. Please contact admin to unlock Account'], 401);
                }else{
                    $user->failed_logins += 1;
                $user->save();
                } 

                return response()->json(['status' => false, 'message' => 'Invalid Login credentials. Your account will be deactivated after '.(4 - $user->failed_logins).' attempts'], 401);
            } else {
                return response()->json(['status' => false, 'message' => 'Invalid Login Credentials'], 401);
            }
        }
    }

public function logout_customer(){
    Auth::user()->currentAccessToken()->delete();
    return response()->json(['status' => true, 'message' => 'Logout Successfull'], 201);
}

}//endclass

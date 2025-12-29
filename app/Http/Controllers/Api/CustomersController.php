<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traites\AuditTraite;
use App\Http\Traites\UserTraite;
use App\Http\Traites\SavingTraite;
use App\Models\Beneficiary;
use App\Models\Customer;
use App\Models\Saving;
use App\Models\Setting;
use Carbon\Carbon;
use App\Models\Email;
use App\Models\FixedDeposit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use Mockery\Expectation;

class CustomersController extends Controller
{
    use SavingTraite;
    use AuditTraite;
    use UserTraite;
    
    public function get_customers_details(){
        $this->logInfo("","get user details");
        try {
            $getsetvalue = new Setting();
            $location = 'uploads';
             
               $fixeddeposit = [];
               
            //  $personaccessToken = PersonalAccessToken::findToken(Request()->header('Authorization'));
            //$user = $personaccessToken->tokenable;
            $user = Auth::user();

             $savings = Saving::where('customer_id',$user->id)->first();
            
            $fixdposits = FixedDeposit::where('customer_id',$user->id)->get();
                foreach($fixdposits as $fixdpo){
                    $interestm = str_replace("_"," ",$fixdpo->interest_method);
                    array_push($fixeddeposit,["fd_code" => $fixdpo->fixed_deposit_code, "principal" => $fixdpo->principal,"release_date" => $fixdpo->release_date,"maturity_date" => $fixdpo->maturity_date, "interest_method" => $interestm,"interest_rate" => $fixdpo->interest_rate]);
                }
                
            $userDetails = [
                "userid" => $user->id,
               "first_name" => $user->first_name,
               "last_name" => $user->last_name,
               "dob" => $user->dob,
               "phone" => $user->phone,
               "profilepic" => url('/')."/".$user->photo,
               "username" => $user->username,
               "bvn" => $user->bvn,
               "nin" => $user->nin,
               "email" => $user->email,
               "address" => $user->address,
               "sex" => $user->sex,
               "referral" => $user->referral_code,
               "valid_id" => url('/')."/".$user->upload_id,
               "signature" => url('/')."/".$user->signature,
               "accountno" => $user->acctno,
               "balance" => $savings->account_balance,
               "currency" => $getsetvalue->getsettingskey('currency_symbol'),
               "fixeddeposit" => $fixeddeposit
           ];
           return response()->json(['status' => true, 'message' => 'Customers Details Fetched successfully', 'data' => $userDetails]);
    
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []], 500);
        }
    }

    //verify otp for forget password
    public function verify_otp(Request $r){
         $this->logInfo("verifying otp",$r->all());
         
        $validate = Validator::make($r->all(), [
            'otp_code' => ['required','string','min:4','max:4']
        ]);

        if ($validate->fails()) {
            $ra = array("status" => false, "message" => $validate->errors()->all()[0]);
            return response()->json($ra, 406);
        }

        $votp = Customer::where('otp', $r->otp_code)->first();
         
        if(!empty($votp)){
            if($votp->otp_expiration_date > Carbon::now()){
              return response()->json(['status' => true, 'message' => 'OTP Verified successfully']);
            }else {
                return response(['status' => false, 'message' => 'OTP Expired']);
            }

            $usern = $votp->last_name." ".$votp->first_name;
            $this->tracktrails('1','1',$usern,'customer','OTP verification');
   
        }else{
            return response()->json(['status' => false, 'message' => 'Invalid OTP']);
        }

    }

    //verify otp for new and existing register customer
    public function confirm_otp(Request $r){
        
         $this->logInfo("comfirming otp",$r->all());
         
        $validate = Validator::make($r->all(), [
            'otp_code' => ['required','string','min:4','max:4']
        ]);

        if ($validate->fails()) {
            $ra = array("status" => false, "message" => $validate->errors()->all()[0]);
            return response()->json($ra, 406);
        }

        $votp = Customer::where('otp', $r->otp_code)->first();

        if(!empty($votp)){
            if($votp->otp_expiration_date > Carbon::now()){
                if(!empty($r->account_exist) && $r->account_exist == "1"){
                    $username = substr($votp->phone, 1);
                    $password = mt_rand('11111111','99999999');
                    $pin = mt_rand('1111','9999');
        
                $votp->status = "1";
                $votp->phone_verify = "1";
                $votp->username = $username;
                $votp->password = Hash::make($password);
                $votp->pin = Hash::make($pin);
                $votp->save();

                $getsetvalue = new Setting();

                $msg = "welcome ".$votp->last_name." ".$votp->first_name." Your login credentials are: <br>Username: ".$username."<br>Password: ".$password."
                <br>Transaction Pin: ".$pin."<br>Account Number: ".$votp->acctno."<br><br> Kindly reset your password and after login from the setting.";
                Email::create([
                   'user_id' =>  $votp->id,
                   'subject' => "Online Banking Login Credential",
                   'message' => $msg,
                   'recipient' => $r->email,
                 ]);
       
                   Mail::send(['html' => 'mails.sendmail'],[
                       'msg' => $msg,
                    'type' => 'Online Banking Login Credential'
                   ],function($mail)use($getsetvalue, $votp){
                       $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                        $mail->to($votp->email);
                       $mail->subject("Online Banking Login Credential");
                   });
               }else{

                $votp->phone_verify = "1";
                $votp->save();

               }
              return response()->json(['status' => true, 'message' => 'OTP Confirmed Successfully']);
            }else {
                return response(['status' => false, 'message' => 'OTP Expired']);
            }
            $usern = $votp->last_name." ".$votp->first_name;
            $this->tracktrails('1','1',$usern,'customer','OTP Confirmation');
        }else{
            return response()->json(['status' => true, 'message' => 'Invalid OTP']);
        }

    }

    public function forgetpassword(Request $r){
       $this->logInfo("forget password",$r->all());
         
        $validate = Validator::make($r->all(), [
            'email' => ['required','string','email']
        ]);

        if ($validate->fails()) {
            $ra = array("status" => false, "message" => $validate->errors()->all()[0]);
            return response()->json($ra, 406);
        }

        $getsetvalue = new Setting();

        $otpCode = $this->generateOTP();

       $chkemail = Customer::where('email', $r->email)->first();

        if(!empty($chkemail)){
            $chkemail->otp = $otpCode;
            $chkemail->otp_expiration_date = Carbon::now()->addMinutes(10);
            $chkemail->failed_logins = 0;
            $chkemail->save();


         Email::create([
            'user_id' => $chkemail->id,
            'subject' => "forget Password",
            'message' => "Hi ".$chkemail->last_name." ".$chkemail->first_name."<br><br> Below is your OTP: ".$otpCode,
            'recipient' => $r->email,
        ]);
        
            Mail::send(['html' => 'mails.sendmail'],[
                'msg' => "Hi ".$chkemail->last_name." ".$chkemail->first_name."<br><br> Below is your OTP: ".$otpCode." <br><br>Kindly upload your Valid ID and link BVN.",
                'type' => 'Forget Password'
            ],function($mail)use($getsetvalue,$r){
                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                 $mail->to($r->email);
                $mail->subject("forget Password");
            });

            $usern = $chkemail->last_name." ".$chkemail->first_name;
            $this->tracktrails('1','1',$usern,'customer','OTP Sent Successfully to '.$r->email);

            return response()->json(['status' => true, 'message' => 'OTP Sent Successfully']);
        }else{
            return response()->json(['status' => false, 'message' => 'Email is not linked to this account, please visit our branch or contact support'], 400);
        }
    }

    public function resend_otp(Request $r){
        
         $this->logInfo("otp resent",$r->all()); 
         
        $validate = Validator::make($r->all(), [
            'email' => ['required','string','email']
        ]);

        if ($validate->fails()) {
            return response()->json(["status" => false, "message" => $validate->errors()->all()[0]], 406);
        }

        $getsetvalue = new Setting();

        $otpCode = $this->generateOTP();

       $chkemail = Customer::where('email', $r->email)->first();

        if(!empty($chkemail)){
            $chkemail->otp = $otpCode;
            $chkemail->otp_expiration_date = Carbon::now()->addMinutes(10);
            $chkemail->failed_logins = 0;
            $chkemail->save();

          Email::create([
            'user_id' => $chkemail->id,
            'subject' => "OTP Confirmation",
            'message' => "Hi ".$chkemail->last_name." ".$chkemail->first_name."<br><br> Below is your OTP: ".$otpCode,
            'recipient' => $r->email,
        ]);
        
            Mail::send(['html' => 'mails.sendmail'],[
                'msg' => "Hi ".$chkemail->last_name." ".$chkemail->first_name."<br><br> Below is your OTP: ".$otpCode." <br><br>Kindly upload your Valid ID and link BVN.",
                'type' => 'OTP Confirmation'
            ],function($mail)use($getsetvalue,$r){
                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                 $mail->to($r->email);
                $mail->subject("OTP Confirmation");
            });

            $usern = $chkemail->last_name." ".$chkemail->first_name;
            $this->tracktrails('1','1',$usern,'customer','OTP Resent Successfully to '.$r->email);

            return response()->json(['status' => true, 'message' => 'OTP Sent Successfully']);
        }else{
            return response()->json(['status' => false, 'message' => 'Email is not linked to this account, please visit our branch or contact support'], 400);
        }
    }

    public function reset_password(Request $r){
        
         $this->logInfo("password reset request",$r->all());
         
        $validate = Validator::make($r->all(), [
            'otpcode' => ['required','numeric','gt:0'],
            'password' => ['required','string','min:8'],
        ]);

        if ($validate->fails()) {
            return response()->json(["status" => false, "message" => $validate->errors()->all()[0]], 406);
        }

        $chkotp = Customer::where('otp', $r->otpcode)->first();
        if(!empty($chkotp)){
            $chkotp->otp = Null;
            $chkotp->failed_logins = Null;
            $chkotp->password = Hash::make($r->password);
            $chkotp->save();


            $usern = $chkotp->last_name." ".$chkotp->first_name;
            $this->tracktrails('1','1',$usern,'customer','Password Reset Successfully');

            return response()->json(['status' => true, 'message' => 'Password Reset Successfully']);

        }else{
            return response()->json(['status' => false, 'message' => 'Invalid OTP supplied']);
        }
    }

    public function change_password(Request $r){
        
        $validate = Validator::make($r->all(), [
            'current_password' => 'required',
            'new_password' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json(["status" => false, "message" => $validate->errors()->all()[0]], 406);
        }

        $customerAccunt = Customer::where('id', Auth::user()->id)->first();

        if (!empty($customerAccunt)) {

            $customerAccunt->password = Hash::make($r->new_password);
            $customerAccunt->updated_at = Carbon::now();
            $customerAccunt->save();

            $usern = $customerAccunt->last_name." ".$customerAccunt->first_name;
            $this->tracktrails('1','1',$usern,'customer','Password Changed Successfully');

            return response(['status' => true, 'message' => 'Password changed successfully']);
        } else {
            return response(['status' => false, 'message' => 'Invalid current password provided']);
        }
    }

    public function reset_pin(Request $r){
        $validate = Validator::make($r->all(), [
            'current_pin' => 'required|max:4|min:4',
            'new_pin' => 'required|max:4|min:4',
        ]);

        if ($validate->fails()) {
            return response()->json(["status" => false, "message" => $validate->errors()->all()[0]], 406);
        }

        $customerAccunt = Customer::where('id', Auth::user()->id)->first();

        if ($r->new_pin == $r->current_pin) {   
            
            return response()->json(["status" => false, "message" => "New Pin cannot be same as Current Pin"], 406);

        }elseif(Hash::check($r->current_pin, $customerAccunt->pin)) {

            $customerAccunt->pin = Hash::make($r->new_pin);
            $customerAccunt->save();

            $usern = $customerAccunt->last_name." ".$customerAccunt->first_name;
            $this->tracktrails('1','1',$usern,'customer','Pin Reset Successfully');

            return response()->json(["status" => true, "message" => "Pin Reset Successfully"], 200);
        
        }else{
            return response()->json(["status" => false, "message" => "Invalid Pin"], 406);
       }
    }

    public function update_profile(Request $r){
        
         $this->logInfo("profile update log",$r->all());
         
        $validate = Validator::make($r->all(), [
            'phone_number' => ['required','numeric'],
            'email' => ['required','email'],
            'dob' => ['required','string'],
        ]);

        if ($validate->fails()) {
            return response()->json(["status" => false, "message" => $validate->errors()->all()[0]], 406);
        }


        $user = Customer::where('id',Auth::user()->id)->first();

        $user->phone = $r->phone_number;
        $user->email = $r->email;
        $user->dob = $r->dob;
        $user->gender = $r->gender;
        $user->bvn = $r->bvn;
        $user->nin = $r->nin;
        $user->save();

        if($user){
            $usern = $user->last_name." ".$user->first_name;
            $this->tracktrails('1','1',$usern,'customer','Profile Updated Successfully');

           return response(['status' => true, 'message' => 'Profile Updated Successfully', 'data' => $user]);
            } else {
                return response(['status' => false, 'message' => 'Failed to Update Profile']);
            }
    }

    public function uploadFile(Request $request)
    {
         $this->logInfo("","file upload request log");
       //return $request->file('image_file');
        $validate = Validator::make($request->all(), [
            'image_file' => 'required|mimes:jpg,jpeg,png|max:5512',
            'type' => 'required',
        ]);

        if ($validate->fails()) {
            $ra = array("status" => false, "message" => $validate->errors()->all()[0]);
            return response()->json($ra, 406);
        }

        $userAccunt = Customer::where('id',Auth::user()->id)->first();
        //$request->user();
        $pathLocation = "uploads";
        
    if ($userAccunt) {
        if ($request->type == "passport") {
            if($request->hasFile('image_file')){
                $photoid = $request->file('image_file');
                $newphotoid = time()."_".$photoid->getClientOriginalName();
                $photoid->move($pathLocation,$newphotoid);
                
                $userAccunt->photo = $pathLocation.'/'.$newphotoid;
             }
             $userAccunt->save();
             return response()->json(["status" => true, "message" => "File Upload successful"]);

        } else if ($request->type == "valid_id") {
            if($request->hasFile('image_file')){
                $photovalid = $request->file('image_file');
                $newphotovalid = time()."_".$photovalid->getClientOriginalName();
                $photovalid->move($pathLocation,$newphotovalid);
                
                    $userAccunt->upload_id = $pathLocation.'/'.$newphotovalid;
             }
              $userAccunt->save();
             return response()->json(["status" => true, "message" => "File Upload successful"]);

        } else if ($request->type == "signature") {
            if($request->hasFile('image_file')){
                $photsignturn = $request->file('image_file');
                $newphotsignturn = time()."_".$photsignturn->getClientOriginalName();
                $photsignturn->move($pathLocation,$newphotsignturn);
                
               $userAccunt->signature = $pathLocation.'/'.$newphotsignturn;
             }
              $userAccunt->save();
             return response()->json(["status" => true, "message" => "File Upload successful"]);

        }

        $usern = $userAccunt->last_name." ".$userAccunt->first_name;
        $this->tracktrails('1','1',$usern,'customer','file Upload Successfully');

    }else{
        return response()->json(["status" => false, "message" => "Failed to upload File"], 400);
    }
 }
 
  public function getbeneficiary(){
      
   $getbenesbnk = Beneficiary::where('customer_id',Auth::user()->id)->where('type','bank')->get();
                                          
   $getbeneswallt =  Beneficiary::where('customer_id',Auth::user()->id)->where('type','wallet')->get();
                                             
    if(!empty($getbenesbnk) || !empty($getbeneswallt)){
        return response()->json(['status' => true, 'bankdata' => $getbenesbnk,'walletdata' =>  $getbeneswallt]);
    }else{
        return response()->json(['status' => false,'message' => 'No Saved beneficiary']);
    }
}
}//endclass

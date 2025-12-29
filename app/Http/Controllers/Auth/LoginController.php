<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Traites\LastloginTraite;
use App\Http\Traites\UserTraite;
use App\Models\Setting;

class LoginController extends Controller
{
    use LastloginTraite;
    use UserTraite;
    public function __construct()
    {
       $this->middleware('guest')->except('logout'); 
    }

    public function login_account(Request $r){
         $this->logInfo("account login via core banking",$r->all());
         
        $this->validate($r,[
          'username' => ['required','string'],
          'password' => ['required','string']
        ]);
        
        $getsetvalue = new Setting();

        $cd = mt_rand('100000','999999');
        
        if(Auth::attempt(['username' => $r->username,'password' => $r->password,'status' => "1"])){
            $this->lastlogin();
            $user = Auth::user();
            
            if($getsetvalue->getsettingskey('enable_2FA') == "1"){
                
                $this->generatTwofactorcode(Auth::user()->id);
               // $this->sendtwofactormail($cd,$user->firstname,$user->lastname,$user->email);
                
            }
            
            //   if(Auth::user()->hasRole('super admin')){
            //          return redirect()->intended(route('branchpage'));
            //     }else{
                    
            //     }
            return redirect()->intended(route('dashboard'));
           
          
        }else{
            return redirect("/")->withInput($r->only('username'))->with('error','Invalid Credentials or Account Deactivated.');
        }
    }

    public function logout()
    {
        session()->forget('branchid');
         session()->forget('subw');
        
        Auth::guard('web')->logout();
        return redirect()->route('welcome');
    }
    
}

<?php

namespace App\Http\Controllers;

use App\Http\Traites\UserTraite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class VerifyTwoAuthController extends Controller
{
    use UserTraite;
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
       // session()->flash("success","Please check your email an authentication code");
        //return view('twostepauth')->with("success","Please check your email for an authentication code");

        $secret = Auth::user()->two_factor_code;
        //session()->has('2FA') ? session()->get('2FA')['secret'] : null;
        $codeurl = session()->has('2FA') ? session()->get('2FA')['qrcodeurl'] : null;

        return view('twostepauth')->with("sercretkey",$secret)
                                    ->with("qrcodurl",$codeurl);
    }

public function store(Request $r){

     $this->validate($r,[
            'two_step_code.*' => ['required','numeric']
           ]);

      $user = Auth::user();

    //   if($user->two_factor_code == $r->two_step_code){
    //     $this->resetTwoFactorcode($user->id);
    //     if(Auth::user()->hasRole('super admin')){
    //       return redirect()->intended(route('dashboard'));
    //       }else{
    //           return redirect()->intended(route('dashboard'));
    //       }
    //   }
    //   return redirect()->back()->with('error','Sorry...Please enter correct authentication code sent to your email');
    
    $code = implode($r->two_step_code);

      $google2fa = new Google2FA();

     $valid = $google2fa->verifyKey($user->two_factor_code, $code);

     if($valid){

            if(is_null($user->is_2fa_enable)){//enable google 2FA
                $user2fa = User::where('id',$user->id)->first();
                $user2fa->is_2fa_enable = '1';
                $user2fa->save();
            }

            $r->session()->forget('2FA');

         $r->session()->put('2fa_verified', true);
        
         return redirect()->intended(route('dashboard'));

        //   if(Auth::user()->hasRole('super admin')){
         
        // }else{
        //     return redirect()->intended(route('dashboard'));
        // }

     }else{

        return redirect()->route('verify.index')->with('error','Sorry...Invalid authentication code');

     }
    
}

    public function resendcode(){
         $cd = mt_rand('100000','999999');
        $user = Auth::user();
        $this->generatTwofactorcode($user->id,$cd);
        $this->sendtwofactormail($cd,$user->firstname,$user->lastname,$user->email); 
        return redirect()->back()->with('success','Two factor authetication code has been resent');
    }
}

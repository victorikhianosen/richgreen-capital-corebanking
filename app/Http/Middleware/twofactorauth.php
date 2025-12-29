<?php

namespace App\Http\Middleware;

use App\Http\Traites\UserTraite;
use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class twofactorauth
{
    use UserTraite;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        $getsetvalue = Setting::first();

if($getsetvalue->getsettingskey('enable_2FA') == '1'){

        if(Auth::check() && $user->two_factor_code){

           if(!$request->session()->get('2fa_verified'))
           {
               return redirect()->route('verify.index');
           }
     
          
        }
    }
        return $next($request);
    }
}

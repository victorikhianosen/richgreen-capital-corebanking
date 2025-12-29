<?php

namespace App\Http\Middleware;

use App\Models\SubcriptionLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CheckSubcription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $checkSub = SubcriptionLog::whereDate('expiration_date','>=',Carbon::now()->toDateString())->where('is_active','1')->orderBy('created_at','DESC')->first();
        if(!$checkSub){
            return redirect()->route('makesubcriptionpayment');
        }
        return $next($request);
    }
}

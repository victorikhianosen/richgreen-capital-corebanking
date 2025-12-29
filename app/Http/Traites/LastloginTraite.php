<?php 
namespace App\Http\Traites;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

trait LastloginTraite{
    public function lastlogin()
    {
       $user = User::findorfail(Auth::user()->id);

       $user->last_login = Carbon::now();
       $user->save();
    }
}
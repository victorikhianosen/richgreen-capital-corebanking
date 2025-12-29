<?php
namespace App\Http\Traites;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

trait VtpassTraite{

    public function vtpassposturl($url,$body)
    {
        $AuthToken =  base64_encode(env('vtpass_username').":".env('vtpass_password'));
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $AuthToken
        ])->post($url,$body);

        return $response->json();
    }
    
    public function vtpassgeturl($url)
    {
        $AuthToken =  base64_encode(env('vtpass_username').":".env('vtpass_password'));
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $AuthToken
        ])->get($url);

        return $response->json();
    }

    public function vtpassrequestid(){

        date_default_timezone_set('Africa/lagos');
        $date = date("Ymd");
        $time = date("Hi");
        $ranstr = Str::random(6);
        $reqid = $date."".$time."".$ranstr;

        return $reqid;
    } 
}
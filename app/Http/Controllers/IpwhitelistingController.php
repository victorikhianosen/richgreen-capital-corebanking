<?php

namespace App\Http\Controllers;

use App\Http\Traites\AuditTraite;
use App\Http\Traites\UserTraite;
use App\Models\Ipwhitelisting;
use Illuminate\Http\Request;

class IpwhitelistingController extends Controller
{

    use AuditTraite;
    use UserTraite;

    public function __construct()
    {
       $this->middleware('auth'); 
    }

    public function manage_ipaddress(){
        return view('users.ipwhitelist')->with('whitlists',Ipwhitelisting::orderBy('created_at','DESC')->get());
    }

    public function store_ipaddress(Request $r){
        $this->logInfo('saving ip address',$r->all());

        $this->validate($r,[
           'company_name' => ['required','string'],
           'ip_address' => ['required','string'],
        ]);

        if($r->savetyp == "create"){

            Ipwhitelisting::create([
               'company_name' => $r->company_name,
               'ip_address' => $r->ip_address,
           ]);

        }elseif($r->savetyp == "update"){
           $ip = Ipwhitelisting::where('id',$r->ipid)->first();
           $ip->company_name = $r->company_name;
               $ip->ip_address = $r->ip_address;
               $ip->save();
        }

        return redirect()->back()->with('success','Record Saved Successfully');
   }

   public function delete_ipaddress($id){
    Ipwhitelisting::findorfail($id)->delete();

    return redirect()->back()->with('success','Record Deleted Successfully');

}

}//endclass

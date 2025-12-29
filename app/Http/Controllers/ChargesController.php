<?php

namespace App\Http\Controllers;

use App\Http\Traites\AuditTraite;
use App\Http\Traites\UserTraite;
use App\Models\Charge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChargesController extends Controller
{
    use AuditTraite;
    use UserTraite;
    
    public function __construct()
    {
       $this->middleware('auth'); 
    }
    
    public function manage_charges_fee(){
        return view('deposit.manage_charges')->with('charges',Charge::all());
    }

    public function charges_fee_create(){
        return view('deposit.charges_create');
    }

    public function charges_fee_store(Request $r){
        $this->logInfo("creating charges",$r->all());
        
        $this->validate($r,[
            'charge_name' => ['required','string'],
            'charge_amount' => ['required','string'],
        ]);

        Charge::create([
            'chargename' => $r->charge_name,
            'amount' => $r->charge_amount,
            'description' => $r->description
        ]);

        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'deposit/charges','created charges');

        return redirect()->route('charges.index')->with('success','Record Created');
    }

    public function charges_fee_edit($id){
        return view('deposit.charges_edit')->with('ed',Charge::findorfail($id));
    }

    public function charges_fee_update(Request $r,$id){
        $this->logInfo("updating charges",$r->all());
        
        $this->validate($r,[
            'charge_name' => ['required','string'],
            'charge_amount' => ['required','string'],
        ]);
        $che = Charge::findorfail($id);
        $che->update([
            'chargename' => $r->charge_name,
            'amount' => $r->charge_amount,
            'description' => $r->description
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'deposit/charges','updated charges');

        return redirect()->route('charges.index')->with('success','Record Updated');
    }

    public function charges_fee_delete($id){
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        Charge::findorfail($id)->delete();

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'deposit/charges','deleted charges');

        return redirect()->route('charges.index')->with('success','Record Deleted');
    }
}

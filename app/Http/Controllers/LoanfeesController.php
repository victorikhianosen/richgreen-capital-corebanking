<?php

namespace App\Http\Controllers;

use App\Models\LoanFee;
use Illuminate\Http\Request;
use App\Models\GeneralLedger;
use App\Http\Traites\AuditTraite;
use Illuminate\Support\Facades\Auth;

class LoanfeesController extends Controller
{
    use AuditTraite;
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function manage_loan_fee(){
        return view('loan.loanfee.index')->with('getloanfees',LoanFee::all());
    }

    public function loan_fee_create()
    {
        return view('loan.loanfee.create')->with('data',GeneralLedger::where('gl_type','income')->get());
    }

    public function loan_fee_store(Request $r)
    {
       
        $this->validate($r,[
            'name' => ['required','string'],
            'loan_fee_type' => ['required','string'],
            'glcode' => ['required','string'] 
        ]);


             LoanFee::create([
                'name' => $r->name,
                'loan_fee_type' => $r->loan_fee_type,
                'gl_code' => $r->glcode
            ]);
    
                    $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

            $usern = Auth::user()->last_name." ".Auth::user()->first_name;
            $this->tracktrails(Auth::user()->id,$branch,$usern,'loan/loan-fee','created a loan fee');
        
       
        return  redirect()->route('loan.fee.index')->with('success','Record Created');
    }

    public function loan_fee_edit($id)
    {
        return view('loan.loanfee.edit')->with('ed',LoanFee::findorfail($id))
                                        ->with('data',GeneralLedger::where('gl_type','income')->get());
    }

    public function loan_fee_update(Request $r,$id)
    {
        $this->validate($r,[
            'name' => ['required','string'],
            'loan_fee_type' => ['required','string'],
              'glcode' => ['required','string'] 
        ]);

        $loanfee = LoanFee::findorfail($id);
     $loanfee->update([
           'name' => $r->name,
           'loan_fee_type' => $r->loan_fee_type,
           'gl_code' => $r->glcode
        ]);

         $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'loan/loan-fees','updated a loan fees');

        return redirect()->route('loan.fee.index')->with('success','Record Updated');
    }

    public function loan_fee_delete($id)
    {
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
        LoanFee::findorfail($id)->delete();

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'loan/loan-fees','deleted a loan fees');
        
        return redirect()->back()->with('success','Record Deleted');
    }
}

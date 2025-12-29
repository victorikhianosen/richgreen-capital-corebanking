<?php

namespace App\Http\Controllers;

use App\Models\LoanFee;
use App\Models\LoanFeeMeta;
use App\Models\LoanProduct;
use Illuminate\Http\Request;
use App\Models\GeneralLedger;
use App\Http\Traites\AuditTraite;
use Illuminate\Support\Facades\Auth;

class LoanProductController extends Controller
{
    use AuditTraite;
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function manage_loan_product(){
        return view('loan.loanproduct.index')->with('getproducts',LoanProduct::all());
    }

    public function loan_product_create()
    {
        return view('loan.loanproduct.create')->with('loanfees',LoanFee::all())
                                                ->with('asstsgl',GeneralLedger::where('gl_type','asset')->orderBy('gl_name','ASC')->get())
                                                ->with('incomegl',GeneralLedger::where('gl_type','income')->orderBy('gl_name','ASC')->get());
    }

    public function loan_product_store(Request $r)
    {
        $this->validate($r,[
            'product_name' => ['required','string'],
            'interest_method' => ['required','string'],
            'interest_period' => ['required','string'],
            'default_loan_duration' => ['required','string'],
            'repayment_cycle' => ['required','string'],
        ]);

        $loanfees = LoanFee::all();
        if(count($loanfees) < 1){
            return redirect()->back()->with('error','Please add a loan fee to continue');
        }else{
            $ckprod = LoanProduct::where('name',strtolower($r->product_name))->first();
            if($ckprod){
                return redirect()->back()->with('error','product name already exist');
            }else{
                $loanproduct = LoanProduct::firstOrCreate([
                'user_id' => Auth::user()->id,
                'name' => strtolower($r->product_name),
                'gl_code' => $r->product_gl_type,
                'interest_gl' => $r->interest_glcode,
                'incomefee_gl' => $r->incomefee_glcode,
                'loan_disbursed_by' => $r->disburseby,
                'minimum_principal' => $r->minimum_principal,
                'default_principal' => $r->default_principal,
                'maximum_principal' => $r->maximum_principal,
                'interest_method' => $r->interest_method,
                'default_interest_rate' => $r->default_interest_rate,
                'interest_period' => $r->interest_period,
                'minimum_interest_rate' => $r->minimum_interest_rate,
                'maximum_interest_rate' => $r->maximum_interest_rate,
                'override_interest' => $r->override_interest,
                'override_interest_amount' => $r->override_interest_amount,
                'default_loan_duration' => $r->default_loan_duration,
                'default_loan_duration_type' => $r->default_loan_duration_type,
                'repayment_cycle' => $r->repayment_cycle,
                'repayment_order' => $r->repayment_order,
                'grace_on_interest_charged' => $r->grace_on_interest_charged,
                'enable_late_repayment_penalty' => $r->enable_late_repayment_penalty == '1' ? $r->enable_late_repayment_penalty : '0',
                'enable_after_maturity_date_penalty' => $r->enable_after_maturity_date_penalty == '1' ? $r->enable_after_maturity_date_penalty : '0',
                'late_repayment_penalty_type' => $r->enable_late_repayment_penalty == '1' ? $r->late_repayment_penalty_type : 'percentage',
                'late_repayment_penalty_calculate' => $r->enable_late_repayment_penalty == '1' ? $r->late_repayment_penalty_calculate : 'overdue_principal',
                'late_repayment_penalty_amount' => $r->enable_late_repayment_penalty == '1' ? $r->late_repayment_penalty_amount : Null,
                'late_repayment_penalty_grace_period' => $r->enable_late_repayment_penalty == '1' ? $r->late_repayment_penalty_grace_period : Null,
                'late_repayment_penalty_recurring' => $r->enable_late_repayment_penalty == '1' ? $r->late_repayment_penalty_recurring : Null,
                'after_maturity_date_penalty_type' => $r->enable_after_maturity_date_penalty == '1' ? $r->after_maturity_date_penalty_type: 'percentage',
                'after_maturity_date_penalty_calculate' => $r->enable_after_maturity_date_penalty == '1' ? $r->after_maturity_date_penalty_calculate : 'overdue_principal',
                'after_maturity_date_penalty_amount' => $r->enable_after_maturity_date_penalty == '1' ? $r->after_maturity_date_penalty_amount : Null,
                'after_maturity_date_penalty_grace_period' => $r->enable_after_maturity_date_penalty == '1' ? $r->after_maturity_date_penalty_grace_period : Null,
                'after_maturity_date_penalty_recurring' => $r->enable_after_maturity_date_penalty == '1' ? $r->after_maturity_date_penalty_recurring : Null,
            ]);
    
            if(!empty($r->loanfees)){
                foreach($r->loanfees as $key => $loanfee){
                    LoanFeeMeta::create([
                        'user_id' => Auth::user()->id,
                        'parent_id' => $loanproduct->id,
                        'loan_fee_id' => $loanfee,
                        'category' => 'loan_product',
                        'value' => !empty($r->loan_fees_amount[$key]) ? $r->loan_fees_amount[$key] : '0',
                        'loan_fees_schedule' => !empty($r->loan_fees_schedule[$key]) ? $r->loan_fees_schedule[$key] : ($r->loan_fees_amount[$key] == '0' || empty($r->loan_fees_amount[$key]) ? 'charge_fees_on_first_payment' : '')
                    ]);
                }
            }
                    $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

            $usern = Auth::user()->last_name." ".Auth::user()->first_name;
            $this->tracktrails(Auth::user()->id,$branch,$usern,'loan/loan-product','created a loan product with id '.$loanproduct->id);
            }
            
        }
       
        return redirect()->route('loan.product.index')->with('success','Record Created');
    }

    public function loan_product_edit($id)
    {
        return view('loan.loanproduct.edit')->with('loanfees',LoanFee::all())
                                            ->with('ed',LoanProduct::findorfail($id))
                                              ->with('asstsgl',GeneralLedger::where('gl_type','asset')->orderBy('gl_name','ASC')->get())
                                                ->with('incomegl',GeneralLedger::where('gl_type','income')->orderBy('gl_name','ASC')->get());
    }

    public function loan_product_update(Request $r,$id)
    {
        $this->validate($r,[
            'product_name' => ['required','string'],
            'interest_method' => ['required','string'],
            'interest_period' => ['required','string'],
            'default_loan_duration' => ['required','string'],
            'repayment_cycle' => ['required','string'],
        ]);

        $loanproduct = LoanProduct::findorfail($id);
     $loanproduct->update([
        'name' => $r->product_name,
        'gl_code' => $r->product_gl_type,
        'interest_gl' => $r->interest_glcode,
        'incomefee_gl' => $r->incomefee_glcode,
        'loan_disbursed_by' => $r->disburseby,
        'minimum_principal' => $r->minimum_principal,
        'default_principal' => $r->default_principal,
        'maximum_principal' => $r->maximum_principal,
        'interest_method' => $r->interest_method,
        'default_interest_rate' => $r->default_interest_rate,
        'interest_period' => $r->interest_period,
        'minimum_interest_rate' => $r->minimum_interest_rate,
        'maximum_interest_rate' => $r->maximum_interest_rate,
        'override_interest' => $r->override_interest,
        'override_interest_amount' => $r->override_interest_amount,
        'default_loan_duration' => $r->default_loan_duration,
        'default_loan_duration_type' => $r->default_loan_duration_type,
        'repayment_cycle' => $r->repayment_cycle,
        'repayment_order' => $r->repayment_order,
        'grace_on_interest_charged' => $r->grace_on_interest_charged,
        'enable_late_repayment_penalty' => $r->enable_late_repayment_penalty == '1' ? $r->enable_late_repayment_penalty : '0',
        'enable_after_maturity_date_penalty' => $r->enable_after_maturity_date_penalty == '1' ? $r->enable_after_maturity_date_penalty : '0',
        'late_repayment_penalty_type' => $r->enable_late_repayment_penalty == '1' ? $r->late_repayment_penalty_type : 'percentage',
        'late_repayment_penalty_calculate' => $r->enable_late_repayment_penalty == '1' ? $r->late_repayment_penalty_calculate : 'overdue_principal',
        'late_repayment_penalty_amount' => $r->enable_late_repayment_penalty == '1' ? $r->late_repayment_penalty_amount : Null,
        'late_repayment_penalty_grace_period' => $r->enable_late_repayment_penalty == '1' ? $r->late_repayment_penalty_grace_period : Null,
        'late_repayment_penalty_recurring' => $r->enable_late_repayment_penalty == '1' ? $r->late_repayment_penalty_recurring : Null,
        'after_maturity_date_penalty_type' => $r->enable_after_maturity_date_penalty == '1' ? $r->after_maturity_date_penalty_type: 'percentage',
        'after_maturity_date_penalty_calculate' => $r->enable_after_maturity_date_penalty == '1' ? $r->after_maturity_date_penalty_calculate : 'overdue_principal',
        'after_maturity_date_penalty_amount' => $r->enable_after_maturity_date_penalty == '1' ? $r->after_maturity_date_penalty_amount : Null,
        'after_maturity_date_penalty_grace_period' => $r->enable_after_maturity_date_penalty == '1' ? $r->after_maturity_date_penalty_grace_period : Null,
        'after_maturity_date_penalty_recurring' => $r->enable_after_maturity_date_penalty == '1' ? $r->after_maturity_date_penalty_recurring : Null,
        ]);

        if(!empty($r->loanfees)){
        foreach($r->loanfees as $key => $loanfee){
            LoanFeeMeta::where('loan_fee_id',$loanfee)
                        ->where('parent_id',$id)->update([
                'user_id' => Auth::user()->id,
                'parent_id' => $loanproduct->id,
                'loan_fee_id' => $loanfee,
                'category' => 'loan_product',
                'value' => !empty($r->loan_fees_amount[$key]) ? $r->loan_fees_amount[$key] : '0',
                'loan_fees_schedule' => !empty($r->loan_fees_schedule[$key]) ? $r->loan_fees_schedule[$key] : ($r->loan_fees_amount[$key] == '0' || empty($r->loan_fees_amount[$key]) ? 'charge_fees_on_first_payment' : '')
            ]);
         }
        }
         $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'loan/loan-product','updated a loan product with id '.$loanproduct->id);

        return redirect()->route('loan.product.index')->with('success','Record Updated');
    }

    public function loan_product_delete($id)
    {
        LoanProduct::findorfail($id)->delete();

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,Auth::user()->branch_id,$usern,'loan/loan-product','deleted a loan product with id');
        
        return redirect()->back()->with('success','Record Deleted');
    }
    
    //fetch loan product details via ajax
     public function loan_products_details(){
        $lprod = LoanProduct::findorfail(request()->proidval);
        if(!empty($lprod)){
            return array(
                'status' => '1',
                'principal' => $lprod->default_principal,
                'maxprincipal' => $lprod->maximum_principal,
                'duration' => $lprod->default_loan_duration,
                'durtype' => $lprod->default_loan_duration_type,
                'interestmethod' => $lprod->interest_method,
                'interestrate' => $lprod->default_interest_rate,
                'interestperiod' => $lprod->interest_period
                );
        }else{
            return array(
                'status' => '0',
                'msg' => "Product details not available",
                );
        }
    }
}//endclass
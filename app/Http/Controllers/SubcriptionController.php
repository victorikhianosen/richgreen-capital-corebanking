<?php

namespace App\Http\Controllers;

use App\Http\Traites\AuditTraite;
use App\Http\Traites\SavingTraite;
use App\Http\Traites\UserTraite;
use App\Models\AccountCategory;
use App\Models\GeneralLedger;
use App\Models\SubcriptionLog;
use App\Models\SubcriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SubcriptionController extends Controller
{
    use SavingTraite;
    use AuditTraite;
    use UserTraite;

    public function __construct()
    {
       $this->middleware('auth'); 
    }

    public function manage_plan(){
        return view('billing.manage_plan')->with('plans',SubcriptionPlan::all());
    }

    public function store_plan(Request $r){
         $this->validate($r,[
            'package_name' => ['required','string'],
            'duration' => ['required','string'],
            'price' => ['required','string','numeric'],
            'vat' => ['required','string'],
         ]);

         if($r->savetyp == "create"){

            SubcriptionPlan::create([
                'package_name' => $r->package_name,
                'duration' => $r->duration,
                'vat' => $r->vat,
                'price' => $r->price
            ]);

         }elseif($r->savetyp == "update"){
            $plan = SubcriptionPlan::where('id',$r->planid)->first();
            $plan->package_name = $r->package_name;
                $plan->duration = $r->duration;
                $plan->vat = $r->vat;
                $plan->price = $r->price;
                $plan->save();
         }

         return redirect()->back()->with('success','Record Saved Successfully');
    }

    public function delete_plan($id){
        SubcriptionPlan::findorfail($id)->delete();

        return redirect()->back()->with('success','Record Deleted Successfully');

    }

    public function view_subcription_payment(){
        return view('billing.view_payments')->with('payments',SubcriptionLog::orderBy('created_at','DESC')->get())
                                            ->with('spye',SubcriptionLog::where('is_active','1')->first());
    }
    
    public function print_payment_receipt($id){
        return view('billing.print_payment_receipt')->with('recipt',SubcriptionLog::findorfail($id));
    }

    public function make_subcription_payment(){

        // return $getwrnddate;
        return view('billing.make_payment')->with('plans',SubcriptionPlan::all())
                                          ->with('gls',GeneralLedger::whereIn('account_category_id',['32','17'])->where('status',1)->orderBy('gl_name','ASC')->get())
                                         ->with('activesub',SubcriptionLog::orderBy('created_at', 'desc')->first());
    }

    public function store_subcription_payment(Request $r){

        $this->logInfo("subcription payment log",$r->all());
        
        $decs = 'payment for '.strtolower($r->package_name).' package';

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;

        $glacctexp = GeneralLedger::select('id','status','account_balance')->where('gl_code','50486638')->first();

        $this->gltransaction('withdrawal',$glacctexp,$r->total,null);
        $this->create_saving_transaction_gl(null,$glacctexp->id,null,$r->total,'debit',null,null,$this->generatetrnxref('sub'),$decs,'approved', $usern);
       
        $glacctcr = GeneralLedger::select('id','status','account_balance')->where('gl_code',$r->glaccount)->first();

        $this->gltransaction('deposit',$glacctcr,$r->total,null);
        $this->create_saving_transaction_gl(null,$glacctcr->id,null,$r->total,'credit',null,null,$this->generatetrnxref('sub'),$decs,'approved', $usern);

        $days = $r->duration - 2;

        SubcriptionLog::create([
            'subcription' => $r->package_name,
            'amount_paid' => $r->price,
            'vat' => $r->vat,
            'total_paid' => $r->total,
            'expense_account' => '50486638',
            'credit_account' => $r->glaccount,
            'warning_date' => Carbon::now()->addDays($days),
            'expiration_date' => Carbon::now()->addDays($r->duration),
            'payment_date' => Carbon::now(),
            'note' => $decs,
            'is_active' => '1',
            'paymentref' => $r->paymentref
        ]);

       
        $this->tracktrails('1','1',$usern,'customer',$decs);

        return redirect()->route('makesubcriptionpayment')->with('success','Payment Completed Successfully');
    }


    public function checkGlaccount(){
        $glcd = GeneralLedger::where('gl_code',request()->glcodeval)->where('status','1')->first();
        if(empty($glcd)){
            return array(
                'status' => '0',
                'msg' => 'invalid or inactive Gl Account'
            );
        }else{
            if($glcd->account_balance >= request()->amount){
               $accat = AccountCategory::where('id',$glcd->account_category_id)->first();
            if($accat){
                if($accat->name == "due from banks in nigeria" || $accat->name == "cash balances"){
                    return array(
                        'status' => '1',
                       'name' => ucwords($glcd->gl_name),
                       'glcode' => $glcd->gl_code,
                       'bal' => number_format($glcd->account_balance,2),
                       'glid' => $glcd->id
                    );
                }else{
                    return array(
                        'status' => '0',
                        'msg' => 'Gl Account must be due from banks in nigeria or cash balances'
                    );
                }
            }else{
                return array(
                    'status' => '0',
                    'msg' => 'invalid account category'
                );
            } 
            }else{
                return array(
                    'status' => '0',
                    'msg' => 'insufficient fund'
                );
            }
            
        }
    }
}//endclas

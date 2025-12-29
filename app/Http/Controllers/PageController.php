<?php

namespace App\Http\Controllers;

use App\Http\Traites\LoanTraite;
use App\Models\Audittrail;
use App\Models\AccountType;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\GeneralLedger;
use App\Models\LoanFee;
use App\Models\LoanRepayment;
use App\Models\Saving;
use App\Models\SavingsTransaction;
use App\Models\SavingsTransactionGL;
use App\Models\SubcriptionLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class PageController extends Controller
{
    use LoanTraite;
    public function __construct()
    {
       $this->middleware('auth'); 
    }

   public function dashboard(){ 
        $loans_released_monthly = array();
        $loan_collections_monthly = array();
        $amountcollected = 0;
        
        $subwarn = SubcriptionLog::where('is_active',1)->first();
        
        if($subwarn->warning_date <= Carbon::now()->toDateString()){
            session()->put('subw',[
                'msg' => 'Please your subcription will expire on the '.date("d-M-Y",strtotime($subwarn->expiration_date))
            ]);
        }
        
        if(!empty(request()->branchid)){
            $branch = Branch::findorfail(request()->branchid);
        session()->put('branchid',[
            'bid' => $branch->id,
            'bname' => $branch->branch_name
        ]);
        }
    
    $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;
         $lo = "";
          global $amt;
         $lomonth_array =array();
            foreach (Loan::where('status','disbursed')->get() as $mth) {
                
              $month_no = date('m',strtotime($mth->created_at));
               $month_name = date('M Y',strtotime($mth->created_at));
               $lomonth_array[$month_no] = $month_name; 
               
            }
            
            foreach ($lomonth_array as $month_no => $lomth) {
              $amt = round(Loan::whereMonth('created_at',$month_no)->where('status','disbursed')->sum('principal'),2);
              $lo .= "{month:'".$lomth."',principal:".$amt."}, ";
            }//loan
            
             //loan repayment  
             $loankeyid=array();
             $rpymonth_array = array();
             $repy = "";
            foreach (Loan::where('status','disbursed')->get() as $key) {
              $loankeyid[] = $key->id;
            }
            
            foreach (LoanRepayment::whereIn('loan_id', $loankeyid)->get() as $repaymnt) {
              $month_no = date('m',strtotime($repaymnt->created_at));
               $month_name = date('M Y',strtotime($repaymnt->created_at));
               $rpymonth_array[$month_no] = $month_name; 
            }
            
             foreach ($lomonth_array as $month_no => $repymth) {
              $loancollected = round(LoanRepayment::whereMonth('created_at',$month_no)->sum('amount'),2);
              $repy .= "{month:'".$repymth."',amount:".$loancollected."}, ";
            }

            // $amount2 = round($amountcollected, 2);
          
          $active_account = Customer::where('status','1')->count();
          $pending_account = Customer::where('status','7')->count();
          $closed_account = Customer::where('status','2')->count();
          $dom_account = Customer::where('status','8')->count();
          $savingacct = Customer::where('account_type','1')->where('status','1')->get()->count();
          $currentacct = Customer::where('account_type','2')->where('status','1')->get()->count();
          $sysusers = User::where('role_id','!=',1)->orWhere('role_id',null)->get()->count();
          
       $monthlycount = array();
      $month_name_array = array();
      $monthly_array_data = array();
      
      $getdata = array();
      $getmonths = $this->getallmonths();
          if (!empty($getmonths)) {
             foreach ($getmonths as $month_no => $month_name) {
                 array_push($monthlycount, $this->getallmonthlycount($month_no));
                 array_push($month_name_array,$month_name);
                 
            //   array($getdata,$month_name_array,$monthlycount);
            }
          }
          
           $rec = "";
          
          $results=array_combine($month_name_array,$monthlycount);
         
          foreach($results as $key => $data){
             $rec .= "{month:'".$key."',success:".$data['success'].",failed:".$data['failed'].",total:".$data['total']."}, ";
          }
    
   
       // $loans_released_monthly = json_encode(array('month' => date("M Y"),'amount' => $amount));
        //$loan_collections_monthly = json_encode(array('month' => date("M Y"),'amount' => $amount2));
      
       
        return view('dashboard')->with('loans_released_monthly',substr($lo,0,-2))
                                 ->with('loan_collections_monthly',substr($repy,0,-2))
                                 ->with('transactions',substr($rec,0,-2))
                                 ->with('sysusers',$sysusers)
                                 ->with('active_accounts',$active_account)
                                 ->with('pending_accounts',$pending_account)
                                 ->with('closed_accounts',$closed_account)
                                 ->with('domant_accounts',$dom_account)
                                 ->with('savingacct',$savingacct)
                                 ->with('currentacct',$currentacct);
    }

    public function branchpage(){
        return view('branchpage')->with('getbranches',Branch::orderBy('created_at','DESC')->get());
    }

    public function profile(){
        return view('profile');
    }

    public function loan_calculator(){
        return view('loan.loancalculator.loan_calculator')->with('loanfees',LoanFee::all());
    }

    public function loan_calculator_show(){
        return view('loan.loancalculator.loan_calculator_show')->with('request',request());
    }

    public function loan_calculator_print(){
        $getsetvalue = new Setting();
        if (request()->pdf == "Download as PDF") {
            $data = [
                'title' => $getsetvalue->getsettingskey('company_name')." Loan BreakDown",
                'date' => date('m/d/Y'),
                'request' => request()
            ];
            
            $pdf = PDF::loadView("loan.loancalculator.print", $data);
            return $pdf->download($getsetvalue->getsettingskey("company_name")." Loan BreakDown.pdf");

        }elseif (request()->print == "Print") {
            return view('loan.loancalculator.print')->with('request',request());
        }
    }

    //get user details 
public function getuser_details(){
    $user = User::findorfail(request()->uid);

    return array(
        'name' => $user->last_name." ".$user->first_name,
        'email' => $user->email,
        'gender' => $user->gender,
        'phone' => $user->phone,
        'addr' => $user->address
    );
}

public function get_account_details(){
    $csudtls = Customer::where('acctno',request()->acno)->where('status','1')->first();
    if(empty($csudtls)){
        return array(
            'status' => '0',
            'msg' => 'invalid account number or account is inactive'
        );
    }else{
        $getbal = Saving::where('customer_id',$csudtls->id)->first();
        return array(
            'status' => '1',
           'name' => ucwords($csudtls->title." ".$csudtls->last_name." ".$csudtls->first_name),
           'acnum' => $csudtls->acctno,
           'bal' => number_format($getbal->account_balance,2),
           'custmerid' => $csudtls->id,
           'msg' => 'account number verified'
        ); 
    }  
}

public function gl_getcode(){
    if(request()->glactyp == "1"){
        $optins= array(); 
        $actyps = GeneralLedger::where('gl_type',request()->actypval)->where('status','1')->get();
        $optins[] .= "<option selected disabled>Select GL Account</option>";
        foreach($actyps as  $actyp){
            $optins[] .="<option value='".$actyp->gl_code."'>".ucwords($actyp->gl_name)."</option>";
        }
        return $optins;

    }elseif(request()->vault == "1"){

        $glcd = GeneralLedger::where('gl_code',request()->glval)->where('status','1')->first();
        if(empty($glcd)){
            return array(
                'status' => '0'
            );
        }else{
            return array(
                'status' => '1',
               'name' => ucwords($glcd->gl_name),
               'glcode' => $glcd->gl_code,
               'bal' => number_format($glcd->account_balance,2),
               'glid' => $glcd->id
            ); 
        }
    }else{
        $glcd = GeneralLedger::where('gl_code',request()->glcodeval)->where('status','1')->first();
        if(empty($glcd)){
            return array(
                'status' => '0'
            );
        }else{
            return array(
                'status' => '1',
               'name' => ucwords($glcd->gl_name),
               'glcode' => $glcd->gl_code,
               'bal' => number_format($glcd->account_balance,2),
               'glid' => $glcd->id
            ); 
        }
    }
     
}

public function get_transaction_slip(){
    $gettranslip = SavingsTransaction::where('slip',request()->slipno)->orWhere('reference_no',request()->slipno)->first();

    if(empty($gettranslip)){
        return array(
            'status' => '0',
            'msg' => 'slip or reference number not found'
        );
    }elseif($gettranslip->transfer_type == "cgl" || $gettranslip->transfer_type == "glc"){
        return array(
            'status' => '0',
            'msg' => 'Cannot perform reversal..Please use a Gl Reversal'
           );  
      }else{
        return array(
            'status' => '1',
           'name' => ucwords($gettranslip->customer->title." ".$gettranslip->customer->last_name." ".$gettranslip->customer->first_name),
           'acnum' => $gettranslip->customer->acctno,
           'txrtype' =>   $gettranslip->type,
           'amount' => $gettranslip->amount,
           'txrdate' => date("d-m-y",strtotime($gettranslip->created_at))." at ".date("h:ia",strtotime($gettranslip->created_at)),
           'custmerid' => $gettranslip->customer_id,
           'slipno' => $gettranslip->slip,
           'msg' => 'verified'
        ); 
    }  
}

public function audit_trail(){
    return view('audit_trail')->with('audits',Audittrail::orderBy('created_at','DESC')->get());
}

  //get all months from tables
     public function getallmonths(){
        $month_array = array();
          $trndata = SavingsTransaction::select('created_at')->orderBy('created_at','ASC')->get();
          
            foreach ($trndata as $data) {
              $month_no = date('m',strtotime($data['created_at']));
               $month_name = date('M Y',strtotime($data['created_at']));
               $month_array[$month_no] = $month_name; 
            }
      return $month_array;
    }

 //get all the monthly count of the status
    public function getallmonthlycount($month){
          $alldata = array();
            $successtran = SavingsTransaction::whereMonth('created_at',$month)->where('status','approved')->sum('amount');
            $failedtran = SavingsTransaction::whereMonth('created_at',$month)->where('status','failed')->sum('amount');
         $alltran = SavingsTransaction::whereMonth('created_at',$month)->sum('amount');
         //$failedtran = Transaction::whereMonth('created_at',$month)->where('status','failed')->get();
     
     $total = $successtran + $failedtran;
       return  array(
           'success' => $successtran,
           'failed' => $failedtran,
           'total' => $total,
       );
    }
}//endclass

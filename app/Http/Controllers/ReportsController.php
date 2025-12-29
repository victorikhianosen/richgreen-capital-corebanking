<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Loan;
use App\Models\Fxmgmt;
use App\Models\Saving;
use App\Models\Sector;
use App\Models\Payroll;
use App\Models\Setting;
use App\Models\Customer;
use App\Models\Expenses;
use App\Models\OtherIncome;
use App\Models\Exchangerate;
use App\Models\FixedDeposit;
use App\Models\LoanSchedule;
use Illuminate\Http\Request;
use App\Models\GeneralLedger;
use App\Models\LoanRepayment;
use App\Models\Accountofficer;
use App\Http\Traites\LoanTraite;
use App\Http\Traites\SavingTraite;
use App\Models\SavingsTransaction;
use Illuminate\Support\Facades\DB;
use App\Models\NotificationPayload;
use App\Models\SavingsTransactionGL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SavingsBalanceExport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use function PHPUnit\Framework\returnSelf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;

class ReportsController extends Controller
{
   use LoanTraite;
   use SavingTraite;
   public function __construct()
   {
      $this->middleware('auth'); 
   }
    public function balancesheet(){
        
      if(request()->bsheettyp == "1"){
          
           $capital = $this->total_capital(request()->datefrom, request()->dateto);
      $expenses = $this->total_expenses(request()->datefrom, request()->dateto);
      $payroll = $this->total_payroll(request()->datefrom, request()->dateto);
      $principal = $this->loans_total_principal(request()->datefrom, request()->dateto);
      $other_income = $this->total_other_income(request()->datefrom, request()->dateto);
      $deposits = $this->total_savings_deposits(request()->datefrom, request()->dateto);
     $withdrawals = $this->total_savings_withdrawals(request()->datefrom, request()->dateto);
     $principal_paid = $this->loans_total_paid_item('principal', request()->datefrom, request()->dateto);
     $interest_paid = $this->loans_total_paid_item('interest', request()->datefrom, request()->dateto);
     $fees_paid = $this->loans_total_paid_item('fees', request()->datefrom, request()->dateto);
     $penalty_paid = $this->loans_total_paid_item('penalty', request()->datefrom, request()->dateto);
     $total_payments = $expenses + $payroll + $principal + $withdrawals;
     $total_receipts = $principal_paid + $fees_paid + $interest_paid + $penalty_paid + $other_income + $deposits+$capital;
     $cash_balance = $total_receipts - $total_payments;
     
       if(request()->filter == "true"){
          return view('reports.balance_sheet')->with('capital',$capital)
                                             ->with('expenses',$expenses)
                                             ->with('payroll', $payroll)
                                             ->with('principal',$principal)
                                             ->with('otherincome',$other_income)
                                             ->with('deposits',$deposits)
                                             ->with('withdrawals',$withdrawals)
                                             ->with('principalpaid',$principal_paid)
                                             ->with('interest_paid',$interest_paid)
                                             ->with('fees_paid',$fees_paid)
                                             ->with('penalty_paid',$penalty_paid)
                                             ->with('total_payments',$total_payments)
                                             ->with('total_receipts',$total_receipts)
                                             ->with('cash_balance',$cash_balance);
       }else{
        return view('reports.balance_sheet');
       }
       
      }elseif(request()->bsheettyp == "2"){
                  return view('reports.balance_sheet2');
      }
    }

    public function trialbalance(){
      return view('reports.trial_balance');
 }

    public function print_balancesheet(){

      $capital = $this->total_capital(request()->datefrom, request()->dateto);
      $expenses = $this->total_expenses(request()->datefrom, request()->dateto);
      $payroll = $this->total_payroll(request()->datefrom, request()->dateto);
      $principal = $this->loans_total_principal(request()->datefrom, request()->dateto);
      $other_income = $this->total_other_income(request()->datefrom, request()->dateto);
      $deposits = $this->total_savings_deposits(request()->datefrom, request()->dateto);
     $withdrawals = $this->total_savings_withdrawals(request()->datefrom, request()->dateto);
     $principal_paid = $this->loans_total_paid_item('principal', request()->datefrom, request()->dateto);
     $interest_paid = $this->loans_total_paid_item('interest', request()->datefrom, request()->dateto);
     $fees_paid = $this->loans_total_paid_item('fees', request()->datefrom, request()->dateto);
     $penalty_paid = $this->loans_total_paid_item('penalty', request()->datefrom, request()->dateto);
     $total_payments = $expenses + $payroll + $principal + $withdrawals;
     $total_receipts = $principal_paid + $fees_paid + $interest_paid + $penalty_paid + $other_income + $deposits+$capital;
     $cash_balance = $total_receipts - $total_payments;

      return view('reports.print_balancesheet')->with('capital',$capital)
                                          ->with('expenses',$expenses)
                                          ->with('payroll', $payroll)
                                          ->with('principal',$principal)
                                          ->with('otherincome',$other_income)
                                          ->with('deposits',$deposits)
                                          ->with('withdrawals',$withdrawals)
                                          ->with('principalpaid',$principal_paid)
                                          ->with('interest_paid',$interest_paid)
                                          ->with('fees_paid',$fees_paid)
                                          ->with('penalty_paid',$penalty_paid)
                                          ->with('total_payments',$total_payments)
                                          ->with('total_receipts',$total_receipts)
                                          ->with('cash_balance',$cash_balance);
    }
    
    public function callover(){
            //$branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

      //return dd(request()->all());
      if(request()->filter == true && request()->callovertype == '1'){
         if (request()->type == 'all' && empty(request()->type)) {
             
            $alldata = SavingsTransaction::whereBetween('created_at', [request()->datefrom,request()->dateto])->get();
            
            return view('reports.call_over')->with('data',$alldata);
            
         } else {
             
            if(request()->type = "deposit"){
                  $typdata = SavingsTransaction::whereBetween('created_at', [request()->datefrom,request()->dateto])
                                          ->whereIn('type',["deposit","credit"])->get();

                return view('reports.call_over')->with('data',$typdata);
                
            }elseif(request()->type = "withdrawal"){
                  $typdata = SavingsTransaction::whereBetween('created_at', [request()->datefrom,request()->dateto])
                                          ->whereIn('type',["debit","withdrawal"])->get();

                return view('reports.call_over')->with('data',$typdata);
            
            }else{
                
                  $typdata = SavingsTransaction::whereBetween('created_at', [request()->datefrom,request()->dateto])
                                          ->where('type',request()->type)->get();

                return view('reports.call_over')->with('data',$typdata);
            }
        }
        
      }elseif(request()->filter == true && request()->callovertype == '2'){
          
          if(request()->gl == '1'){
              
                     if (request()->status == 'all' || empty(request()->status)) {
                        $alldatastatus = SavingsTransactionGL::whereBetween('created_at', [date("Y-m-d",strtotime(request()->datefrom)),date("Y-m-d",strtotime(request()->dateto))])->get();
                       
                        return view('reports.call_over')->with('data', $alldatastatus);
                        
                     } else {
                         
                        $statusdata = SavingsTransactionGL::whereBetween('created_at', [date("Y-m-d",strtotime(request()->datefrom)),date("Y-m-d",strtotime(request()->dateto))])
                                                         ->where('status',request()->status)->get();
                                                         
                            return view('reports.call_over')->with('data',$statusdata);
                    }
        
          }else{
                if (request()->status == 'all' || empty(request()->status)) {
            $alldatastatus = SavingsTransaction::whereBetween('created_at', [date("Y-m-d",strtotime(request()->datefrom)),date("Y-m-d",strtotime(request()->dateto))])->get();
           
            return view('reports.call_over')->with('data', $alldatastatus);
            
         } else {
             
            $statusdata = SavingsTransaction::whereBetween('created_at', [date("Y-m-d",strtotime(request()->datefrom)),date("Y-m-d",strtotime(request()->dateto))])
                                             ->where('status',request()->status)->get();
                                             
                return view('reports.call_over')->with('data',$statusdata);
        }
          }
      }else{
         return view('reports.call_over');
      }
      
    }

   public function notificationpayload(){
         return view("reports.virtual_inwardTrnx")->with('paylods', NotificationPayload::orderBy('created_at','DESC')->get());
    }

    public function cashflow(){
      $capital = $this->total_capital(request()->datefrom, request()->dateto);
      $expenses = $this->total_expenses(request()->datefrom, request()->dateto);
      $payroll = $this->total_payroll(request()->datefrom, request()->dateto);
      $principal = $this->loans_total_principal(request()->datefrom, request()->dateto);
      $other_income = $this->total_other_income(request()->datefrom, request()->dateto);
      $deposits = $this->total_savings_deposits(request()->datefrom, request()->dateto);
      $withdrawals = $this->total_savings_withdrawals(request()->datefrom, request()->dateto);
      $principal_paid = $this->loans_total_paid_item('principal', request()->datefrom, request()->dateto);
     $interest_paid = $this->loans_total_paid_item('interest', request()->datefrom, request()->dateto);
     $fees_paid = $this->loans_total_paid_item('fees', request()->datefrom, request()->dateto);
     $penalty_paid = $this->loans_total_paid_item('penalty', request()->datefrom, request()->dateto);

      $rev_deposits = $this->rev_total_savings_deposits(request()->datefrom, request()->dateto);
        $fixed_deposit = $this->total_fixed_deposit(request()->datefrom, request()->dateto);
        $investment = $this->total_investment(request()->datefrom, request()->dateto);
        $wht = $this->total_wht(request()->datefrom, request()->dateto);
        $rev_fixed_deposit = $this->rev_total_fixed_deposit(request()->datefrom, request()->dateto);
        $rev_withdrawals = $this->rev_total_savings_withdrawals(request()->datefrom, request()->dateto);

        $total_payments = $expenses + $payroll + $principal + $withdrawals - $rev_withdrawals;

        $total_receipts = $principal_paid + $fees_paid + $interest_paid + $penalty_paid + $other_income + $deposits + $fixed_deposit - $rev_deposits - $rev_fixed_deposit + $investment + $capital;
      
       $cash_balance = $total_receipts - $total_payments;

        if(request()->filter == "true"){
         return view('reports.cash_flow')->with('capital',$capital)
                                            ->with('expenses',$expenses)
                                            ->with('payroll', $payroll)
                                            ->with('principal',$principal)
                                            ->with('other_income',$other_income)
                                            ->with('deposits',$deposits)
                                            ->with('withdrawals',$withdrawals)
                                            ->with('principal_paid',$principal_paid)
                                            ->with('interest_paid',$interest_paid)
                                            ->with('fees_paid',$fees_paid)
                                            ->with('penalty_paid',$penalty_paid)
                                            ->with('total_payments',$total_payments)
                                            ->with('total_receipts',$total_receipts)
                                            ->with('rev_withdrawals',$rev_withdrawals)
                                            ->with('rev_deposits',$rev_deposits)
                                            ->with('fixed_deposit',$fixed_deposit)
                                            ->with('rev_fixed_deposit',$rev_fixed_deposit)
                                            ->with('investment',$investment)
                                            ->with('wht',$wht) 
                                            ->with('cash_balance',$cash_balance);

      }else{
       return view('reports.cash_flow');
      }
    }

    public function reference_search(){
      $gettranslip = SavingsTransaction::where('slip',request()->slipno)->orWhere('reference_no',request()->slipno)->get();
      if(empty($gettranslip)){
          return view('reports.reference_search')->with('error','slip or reference number not found');
       }else{
         return view('reports.reference_search')->with('data',$gettranslip);
       }
    }
    
    
    
    public function collection_project(){
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

      $monthly_collections = array();
        $start_date1 = date("Y-m-d");
      //   for ($i = 1; $i < 14; $i++) {
            //$d = explode('-', $start_date1);
            //get loans in that period
            
            $payments_due = 0;
            foreach (LoanSchedule::where('branch_id',$branch)->get() as $key) {
                $payments_due = $payments_due + $key->principal + $key->interest + $key->fees + $key->penalty;
            }
            $payments_due = round($payments_due, 2);
            //$ext = ' ' . $d[0];
            $date = date("M Y");
            array_push($monthly_collections, array(
                'month' => $date,
                'due' => $payments_due
            ));
            //add 1 month to start date
            $start_date1 = date_format(date_add(date_create($start_date1),
                date_interval_create_from_date_string('1 months')),
                'Y-m-d');
        //}
     $collections = json_encode(array( 'month' => $date,'due' => $payments_due));
   
        return view('reports.collection_projection')->with('collections',$collections);
    }

    public function collection_report(){
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

      $payments = 0;
      $payments_due = 0;
      foreach (Loan::where('branch_id', $branch)->where('status', 'disbursed')->get() as $key) {
          $payments = $payments + $this->loan_paid_item($key->id, 'interest',$key->due_date) + $this->loan_paid_item($key->id, 'fees',
                  $key->due_date) + $this->loan_paid_item($key->id, 'penalty',
                  $key->due_date) + $this->loan_paid_item($key->id, 'principal', $key->due_date);
          $payments_due = $payments_due + $this->loan_total_principal($key->id) + $this->loan_total_fees($key->id) + $this->loan_total_penalty($key->id) + $this->loan_total_interest($key->id);
      }
      $payments = round($payments, 2);
      $payments_due = round($payments_due, 2);
      $date = date("M Y");
      // array_push($monthly_collections, array(
      //     'month' => date_format(date_create($start_date1),
      //         'M' . $ext),
      //     'payments' => $payments,
      //     'due' => $payments_due
      // ));
      //add 1 month to start date
      ;

          $collections = json_encode(array( 'month' => $date,'paid' => $payments,'due' => $payments_due));

          return view('reports.collection_report')->with('collections',$collections);
    }
    
    public function posting_approval(){
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
      if(request()->filter == true){
         if (request()->status == 'all') {
            $alldatastatus = SavingsTransaction::whereIn('status',['approved','declined'])
                                                ->whereBetween('created_at', [date("Y-m-d",strtotime(request()->datefrom)),date("Y-m-d",strtotime(request()->dateto))])
                                                  ->where('is_approve','1')
                                                   ->get();
           
            return view('reports.posting_approval')->with('data', $alldatastatus);
         } else {
            $statusdata = SavingsTransaction::whereBetween('created_at', [date("Y-m-d",strtotime(request()->datefrom)),date("Y-m-d",strtotime(request()->dateto))])
                                             ->where('status',request()->status)
                                             ->where('is_approve','1')
                                             ->get();
                return view('reports.posting_approval')->with('data',$statusdata);
        }
      }else{
         return view('reports.posting_approval');
      }
    }
    
    public function customer_statement(){
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

         if(request()->filter == true){
            $balance = 0;
            //where('branch_id', $branch)
            $custmer = Customer::where('acctno',request()->acctno)->first();
      
           $data = SavingsTransaction::whereBetween('created_at', [date("Y-m-d",strtotime(request()->datefrom)),date("Y-m-d",strtotime(request()->dateto))])
                                     ->where('customer_id',$custmer->id)->orderBy('created_at',"ASC")->get();
             
       $savtrns = SavingsTransaction::where('customer_id',$custmer->id)->whereDate('created_at','<',request()->datefrom)->orderBy('created_at','ASC')->get();
        
        foreach($savtrns as $key){
            if($key['type']=="deposit" || $key['type']=="investment"  || $key['type']=="dividend" || $key['type']=="interest" ||
                                  $key['type']=="credit" || $key['type']=="fixed_deposit" || $key['type']=="loan" || $key['type']=="fd_interest" 
                                  || $key['type']=="inv_int" || $key['type']=="rev_withdrawal" || $key['type'] == 'guarantee_restored'){
                                  
                                 if($key['status'] == 'approved'){
                                     $balance += $key->amount;
                                   }else{
                                     $balance;
                                 }
                                 
                                }else{
                                     if($key->status == 'pending' || $key->status == 'declined'){
                                     $balance += 0;
                                      }else{
                                         $balance -= $key->amount;
                                        
                                    }
                                }
                                
                                $balance;
        }
      
                  return view('reports.customer_statement')->with('data',$data)
                                                        ->with('custid',$balance);
         }else{
            return view('reports.customer_statement');
         }
   }
   
    public function customer_balance(){
      if(request()->filter == true){
         if(request()->fetchby == "byname"){
            $bynme = Customer::where('first_name',request()->name)->orWhere('last_name',request()->name)->get();
            return view('reports.customer_balance')->with('data',$bynme);
         }elseif(request()->fetchby == "byaccount"){
            $byac = Customer::where('acctno',request()->acctno)->get();
            return view('reports.customer_balance')->with('data',$byac);
         }elseif(empty(request()->acctno) || empty(request()->name)){
            return view('reports.customer_balance')->with('data', Customer::all());
         }else{
            return view('reports.customer_balance')->with('data', Customer::all());
         }
     
      }else{
         return view('reports.customer_balance');
      }
   }
   
      public function customer_view(){
         $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

      if(request()->filter == true){

         if (request()->officer == 'all') {

            $data = Customer::select('first_name','last_name','phone','status','gender','accountofficer_id')
                              ->where('id',request()->cusmername)->get();

            return view('reports.customer_view')->with('data',$data)
                                                   ->with('customers', Customer::select('id','first_name','last_name')->get())
                                                   ->with('officers',Accountofficer::select('id','full_name')->get());
         } else {

        $data = Customer::select('first_name','last_name','phone','status','gender','accountofficer_id')
                           ->where('id',request()->cusmername)
                         ->where('accountofficer_id',request()->officer)->get();

        return view('reports.customer_view')->with('data',$data)
                                             ->with('customers', Customer::select('id','first_name','last_name')->get())
                                          ->with('officers',Accountofficer::select('id','full_name')->get()); 
      }

      }else{

         return view('reports.customer_view')->with('customers', Customer::select('id','first_name','last_name')->get())
                                             ->with('officers',Accountofficer::select('id','full_name')->get());
      }
   }
   
  public function profit_loss(){
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

if(request()->prfltype == '1'){
     if(request()->filter == true){

      $expenses = $this->total_expenses(request()->datefrom, request()->dateto);
      $other_expenses = $this->total_savings_interest(request()->datefrom, request()->dateto);
      $fd_interest_expense = $this->total_FD_interest_expense(request()->datefrom, request()->dateto);
      $inv_interest_expense = $this->total_inv_int_expense(request()->datefrom, request()->dateto);
      $payroll = $this->total_payroll(request()->datefrom, request()->dateto);
      $other_income = $this->total_other_income(request()->datefrom, request()->dateto);
      $interest_paid = $this->loans_total_paid_item('interest',request()->datefrom, request()->dateto);
      $fees_paid = $this->loans_total_paid_item('fees', request()->datefrom, request()->dateto);
      $bank_fees = $this->total_bank_fees(request()->datefrom, request()->dateto);
      $wht = $this->total_wht(request()->datefrom, request()->dateto);
      $form_fees = $this->total_form_fees(request()->datefrom, request()->dateto);
      $process_fees = $this->total_process_fees(request()->datefrom, request()->dateto);
      $esusu = $this->total_esusu(request()->datefrom, request()->dateto);
      $monthly_charge = $this->total_monthly_charge(request()->datefrom, request()->dateto);
       $transfer_charge = $this->total_transfer_charge(request()->datefrom, request()->dateto);
      $penalty_paid = $this->loans_total_paid_item('penalty',request()->datefrom, request()->dateto);
      $loan_default = $this->loans_total_default(request()->datefrom, request()->dateto);

      $operating_expenses = $expenses + $payroll + $other_expenses + $fd_interest_expense + $inv_interest_expense;

      $operating_profit = $fees_paid + $interest_paid + $penalty_paid + $bank_fees + $form_fees + $process_fees + $esusu + $monthly_charge + $transfer_charge + $other_income;

      $gross_profit = $operating_profit - $operating_expenses;

      $net_profit = $gross_profit - $loan_default;

      $fromdate = date("Y-m",strtotime(request()->datefrom)); 
      $todate = date("Y-m",strtotime(request()->dateto));
     

      //monthly_net_income_data
       //get loans in that period
   $o_profit = 0;
   foreach (Loan::where('branch_id',$branch)->whereBetween('created_at',[$fromdate,$todate])->where('status',
       'disbursed')->get() as $key) {
       $o_profit = $o_profit + $this->loan_paid_item($key->id, 'interest',
               $key->due_date) + $this->loan_paid_item($key->id, 'fees',
               $key->due_date) + $this->loan_paid_item($key->id, 'penalty', $key->due_date);
   }
   $o_profit = round($o_profit + OtherIncome::whereBetween('created_at',[$fromdate,$todate])->sum('amount'), 2);

   $o_expense = Expenses::where('branch_id',$branch)->whereBetween('created_at',[$fromdate,$todate])->sum('amount');

   foreach (Payroll::whereBetween('created_at',[$fromdate,$todate])->get() as $key) {
       $o_expense = $o_expense + $this->single_payroll_total_pay($key->id);
   }

   $o_expense = round($o_expense, 2);
   $ot_expense = 0;

   foreach (Loan::where('branch_id',$branch)->whereBetween('created_at',[$fromdate,$todate])->where('status',
       'disbursed')->get() as $key) {
       $ot_expense = $ot_expense + ($key->principal - $this->loan_total_paid($key->id));
   }

   $ot_expense = round($ot_expense, 2);
   
   $totnet_income = round(($o_profit - $o_expense - $ot_expense), 2);
   
    //$monthly_operating_profit_expenses_data
    $o_profit = 0;
   foreach (Loan::where('branch_id',$branch)->whereBetween('created_at',[$fromdate,$todate])->where('status',
       'disbursed')->get() as $key) {
       $o_profit = $o_profit + $this->loan_paid_item($key->id, 'interest',
               $key->due_date) + $this->loan_paid_item($key->id, 'fees',
               $key->due_date) + $this->loan_paid_item($key->id, 'penalty', $key->due_date);
   }

   $o_profit = round($o_profit + OtherIncome::where('branch_id', $branch)->whereBetween('created_at',[$fromdate,$todate])->sum('amount'), 2);

   $o_expense = Expenses::where('branch_id', $branch)->whereBetween('created_at',[$fromdate,$todate])->sum('amount');

   foreach (Payroll::where('branch_id', $branch)->whereBetween('created_at',[$fromdate,$todate])->get() as $key) {
       $o_expense = $o_expense + $this->single_payroll_total_pay($key->id);
   }

   $o_expense = round($o_expense, 2);
   $ot_expense = 0;

   foreach (Loan::where('branch_id',$branch)->whereBetween('created_at',[$fromdate,$todate])->where('status',
       'disbursed')->get() as $key) {
       $ot_expense = $ot_expense + ($key->principal - $this->loan_total_paid($key->id));
   }

   $ot_expense = round($ot_expense, 2);
   
   $n_income = round(($o_profit - $o_expense - $ot_expense), 2);        

     //get loans in that period
     $o_profit = 0;
     foreach (Loan::where('branch_id',$branch)->whereBetween('created_at',[$fromdate,$todate])->where('status',
         'disbursed')->get() as $key) {
         $o_profit = $o_profit + $this->loan_paid_item($key->id, 'interest',
                 $key->due_date) + $this->loan_paid_item($key->id, 'fees',
                 $key->due_date) + $this->loan_paid_item($key->id, 'penalty', $key->due_date);
     }
     $o_profit = round($o_profit + OtherIncome::where('branch_id',$branch)->whereBetween('created_at',[$fromdate,$todate])->sum('amount'), 2);

     $o_expense = Expenses::where('branch_id',$branch)->whereBetween('created_at',[$fromdate,$todate])->sum('amount');

     foreach (Payroll::where('branch_id',$branch)->whereBetween('created_at',[$fromdate,$todate])->get() as $key) {
         $o_expense = $o_expense + $this->single_payroll_total_pay($key->id);
     }
     $o_expense = round($o_expense, 2);
     $ot_expense = 0;

     foreach (Loan::where('branch_id',$branch)->whereBetween('created_at',[$fromdate,$todate])->where('status',
         'disbursed')->get() as $key) {
         $ot_expense = $ot_expense + ($key->principal - $this->loan_total_paid($key->id));
     }

     foreach (SavingsTransaction::where('branch_id',$branch)->whereBetween('created_at',[$fromdate,$todate])->where('type','interest')->get() as $key) {
         $ot_expense = $ot_expense + $key->amount;
     }
     // FD INTEREST
   foreach (SavingsTransaction::where('branch_id',$branch)->whereBetween('created_at',[$fromdate,$todate])->where('type','fd_interest_expense')->get() as $key) {
         $ot_expense = $ot_expense + $key->amount;
     }
  
     $ot_expense = round($ot_expense, 2);
     
     $n_income = round(($o_profit - $o_expense - $ot_expense), 2);
    
           $date = date("M Y");
  
            $monthly_net_income_data = json_encode(array('month' => date("M Y",strtotime(request()->dateto)),'amount' => $net_profit));
            $monthly_operating_profit_expenses_data = json_encode(array('month' => date("M Y",strtotime(request()->dateto)),'profit' => $operating_profit,'expenses' => $operating_expenses));
            $monthly_other_expenses_data = json_encode(array('month' => date("M Y",strtotime(request()->dateto)), 'expenses' => $loan_default));
            
            return view('reports.profit_loss')->with('incomedata',$monthly_net_income_data)
                                             ->with('operating_profit_data',$monthly_operating_profit_expenses_data)
                                             ->with('other_expenses_data',$monthly_other_expenses_data)
                                             ->with('expenses',$expenses)
                                             ->with('payroll', $payroll)
                                             ->with('operating_expenses',$operating_expenses)
                                             ->with('other_income',$other_income)
                                             ->with('bank_fees',$bank_fees)
                                             ->with('form_fees',$form_fees)
                                             ->with('process_fees',$process_fees)
                                             ->with('interest_paid',$interest_paid)
                                             ->with('fees_paid',$fees_paid)
                                             ->with('penalty_paid',$penalty_paid)
                                             ->with('esusu',$esusu)
                                             ->with('monthly_charge',$monthly_charge)
                                             ->with('transfer_charge',$transfer_charge)
                                             ->with('interest_paid',$interest_paid)
                                             ->with('operating_profit',$operating_profit)
                                             ->with('gross_profit',$gross_profit)
                                             ->with('net_profit',$net_profit)
                                             ->with('other_expenses',$other_expenses)
                                             ->with('inv_interest_expense',$inv_interest_expense)
                                             ->with('loan_default',$loan_default)
                                             ->with('fd_interest_expense',$fd_interest_expense);
     }else{
      return view('reports.profit_loss');
     }
     
}elseif(request()->prfltype == '2'){

      return view('reports.profit_loss2')->with('branch', $branch);
}
    
   }
   
    public function loan_balance()
    {
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        if (request()->filter == true) {

       $data = Loan::select('id','release_date','principal','customer_id','interest_period','interest_rate','balance','override')
                     ->whereBetween('created_at', [request()->datefrom, request()->dateto])
                     ->where('status','disbursed')->get();
      
       return view('reports.loan_balance')->with('data',$data);

      } else {
         return view('reports.loan_balance');
        }
    }

    public function loan_classification()
    {
            $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

      if (request()->filter == true) {

                  $data = Loan::select('id','maturity_date','principal','customer_id','balance','override')
                              ->where('status', 'disbursed')
                              ->whereBetween('disbursed_date', [request()->datefrom, request()->dateto])
                              ->get();

        return view('reports.loan_classification')->with('data',$data);

      } else {
         return view('reports.loan_classification');
        }
    }
    
    
    public function loan_list()
    {
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        if (request()->filter == true) {
            if (request()->status == 'all') {
                $alldata = Loan::where('branch_id',$branch)
                               ->whereBetween('release_date',[request()->datefrom, request()->dateto])->get();
              
                return view('reports.loan_list')->with('data',$alldata);
              
               } else {
                $data = Loan::where('branch_id',$branch)
                             ->whereBetween('release_date',[request()->datefrom, request()->dateto])
                             ->where('status',request()->status)->get();

               return view('reports.loan_list')->with('data',$data);
            }

        } else {
         return view('reports.loan_list');
        }
        
    }
    
      public function repayment_report()
    {
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        if (request()->filter == true) {
         
            $data  = LoanSchedule::where('branch_id',$branch)
                                 ->whereBetween('due_date', [request()->datefrom, request()->dateto])
                                 ->orderBy('due_date', 'asc')->get();

            return view('reports.repayment')->with('data', $data);
        } else {
         return view('reports.repayment');
        }
      }
      
       public function loan_transaction()
      {
         $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

          if (request()->filter == true) {
            
              $data = LoanRepayment::select('loan_id','customer_id','user_id','collection_date','amount','repayment_method')
                                    ->whereBetween('collection_date', [request()->datefrom, request()->dateto])
                                    ->get();

            return view('reports.loan_repayment_trx')->with('data',$data);

          } else {
              return view('reports.loan_repayment_trx');
          }
         
      }
      
       public function chart_of_accounts(){
         if (request()->filter == true) {

            $asset = GeneralLedger::with(['savingstrangl'])
                                   ->where('gl_type','asset')
                                   ->where('account_balance','!=',0)
                                   ->where('status','1')
                                   ->get();
            $libility = GeneralLedger::with(['savingstrangl'])
                                   ->where('gl_type','liability')
                                   ->where('account_balance','!=',0)
                                   ->where('status','1')
                                   ->get();
            $capital = GeneralLedger::with(['savingstrangl'])
                                   ->where('gl_type','capital')
                                   ->where('account_balance','!=',0)
                                   ->where('status','1')
                                   ->get();
            $income = GeneralLedger::with(['savingstrangl'])
                                   ->where('gl_type','income')
                                   ->where('account_balance','!=',0)
                                   ->where('status','1')
                                   ->get();
            $expense = GeneralLedger::with(['savingstrangl'])
                                   ->where('gl_type','expense')
                                   ->where('account_balance','!=',0)
                                   ->where('status','1')
                                   ->get();
         
           
            return view('reports.chart_of_accounts')->with('data',$asset)
                                                    ->with('datalib',$libility)
                                                    ->with('datacap',$capital)
                                                    ->with('dataincom',$income)
                                                    ->with('dataexp',$expense);
        } else {
         return view('reports.chart_of_accounts');
      }
   }
      
   public function accounts_mgmt_report(){
         $balance = 0;
         if (request()->filter == true) {
            if(empty(request()->fetchby)){
                return redirect()->back()->with('error','search option is required');
            }else{
                if(request()->fetchby == "bygl"){
               $gl = GeneralLedger::select('id')->where('gl_code',request()->glcode)->first();
              // dd($gl);
               if(empty($gl->id)){
                
                return view('reports.accounts_mgmt_report')->with('data',[]);

               }else{

                    $data = SavingsTransactionGL::where('general_ledger_id', $gl->id)
                         ->whereBetween('created_at', [request()->datefrom, request()->dateto])
                         ->get();

                    $datasavrts = SavingsTransactionGL::where('general_ledger_id', $gl->id)
                                 ->whereDate('created_at','<=',request()->datefrom)
                                 ->orderBy('created_at','ASC')
                                 ->get();

                         //return $data;
                  foreach($datasavrts as $key){
                     if ($key->generalledger->gl_type == "asset"){

                     if($key->type == "credit"){
                        if($key->status == 'approved'){
                           $balance -= $key->amount;
                         }else{
                            $balance;
                         }
                  }else{
                   if($key->status == 'pending' || $key->status == 'declined'){
                     $balance += 0;
                   }else{
                     $balance += $key->amount;
                    }
                  }
               }elseif($key->generalledger->gl_type == "liability"){

                 if($key->type == "credit"){
                   if($key->status == 'approved'){
                     $balance += $key->amount;
                   }else{
                     $balance += 0;
                    }
                  }else{

                     if($key->status == 'pending' || $key->status == 'declined'){
                        $balance -= 0;
                     }else{
                        $balance -= $key->amount;
                     }
                  }
               }elseif($key->generalledger->gl_type == "capital"){

                     if($key->type == "credit"){
                        if($key->status == 'approved'){
                           $balance += $key->amount;
                        }else{
                           $balance += 0;
                        }
                  }else{
                     if($key->status == 'pending' || $key->status == 'declined'){
                        $balance -= 0;
                        }else{
                        $balance -= $key->amount;
                        }
                     }
               }elseif($key->generalledger->gl_type == "income"){

                     if($key->type == "credit"){
                        if($key->status == 'approved'){
                           $balance += $key->amount;
                        }else{
                           $balance += 0;
                         }
                   }else{
                     if($key->status == 'pending' || $key->status == 'declined'){
                        $balance -= 0;
                     }else{
                           $balance -= $key->amount;
                     }
                  }
         }elseif($key->generalledger->gl_type == "expense"){
             
             if($key->type == "credit"){
                 if($key->status == 'approved'){
                  $balance -= $key->amount;
                 }else{
                     $balance -= 0;
                 }
               }else{
             if($key->status == 'pending' || $key->status == 'declined'){
                  $balance += 0;
             }else{
                  $balance += $key->amount;
              }
            }
         }
              $balance;
            }

      return view('reports.accounts_mgmt_report')->with('data',$data)
                                                   ->with('opbal',$balance)
                                                ->with('gl',GeneralLedger::select('gl_name')->where('gl_code',request()->glcode)->first());
      }
               
       }elseif(request()->fetchby == "byref"){

               $data = SavingsTransactionGL::where('reference_no',request()->reference)
                         ->whereBetween('created_at', [request()->datefrom, request()->dateto])
                         ->get();

            $datasavrts = SavingsTransactionGL::where('reference_no',request()->reference)
                                             ->whereDate('created_at','<=',request()->datefrom)
                                             ->orderBy('created_at','ASC')
                                             ->get();

                 //return $data;
                   foreach($datasavrts as $key){
                        if ($key->generalledger->gl_type == "asset"){

                        if($key->type == "credit"){
                           if($key->status == 'approved'){
                              $balance -= $key->amount;
                           }else{
                           $balance;
                           }
                     }else{
                     if($key->status == 'pending' || $key->status == 'declined'){
                        $balance += 0;
                     }else{
                        $balance += $key->amount;
                        }
                     }
                  }elseif($key->generalledger->gl_type == "liability"){

                     if($key->type == "credit"){
                        if($key->status == 'approved'){
                           $balance += $key->amount;
                        }else{
                           $balance += 0;
                        }
                     }else{
                        if($key->status == 'pending' || $key->status == 'declined'){
                           $balance -= 0;
                        }else{
                           $balance -= $key->amount;
                        }
                     }
                   }elseif($key->generalledger->gl_type == "capital"){

                        if($key->type == "credit"){
                           if($key->status == 'approved'){
                              $balance += $key->amount;
                           }else{
                              $balance += 0;
                           }
                     }else{
                        if($key->status == 'pending' || $key->status == 'declined'){
                           $balance -= 0;
                           }else{
                           $balance -= $key->amount;
                           }
                        }
                  }elseif($key->generalledger->gl_type == "income"){

                        if($key->type == "credit"){
                           if($key->status == 'approved'){
                              $balance += $key->amount;
                           }else{
                              $balance += 0;
                           }
                     }else{
                        if($key->status == 'pending' || $key->status == 'declined'){
                           $balance -= 0;
                        }else{
                              $balance -= $key->amount;
                        }
                     }
            }elseif($key->generalledger->gl_type == "expense"){
               
               if($key->type == "credit"){
                     if($key->status == 'approved'){
                     $balance -= $key->amount;
                     }else{
                        $balance -= 0;
                     }
                  }else{
                     if($key->status == 'pending' || $key->status == 'declined'){
                           $balance += 0;
                     }else{
                        $balance += $key->amount;
                     }
               }
            }
                  $balance;
               }

                  return view('reports.accounts_mgmt_report')->with('data',$data)
                                                            ->with('opbal',$balance);

            }else{
               return view('reports.accounts_mgmt_report');
            }
      }
   } else {
   return view('reports.accounts_mgmt_report');
   }
         
}
      
      public function fund_transfer_report(){
      if (request()->filter == true) {
      $fundTrnx = SavingsTransaction::where('trnx_type','trnsfer')
                                    ->whereIn('status',['approved','failed','pending','decline'])
                                    ->whereBetween('created_at',[request()->datefrom, request()->dateto])
                                    ->get();
      return view('reports.fund_transfer')->with('data',$fundTrnx);
      
      }else{
          
         $allfndTrnx = SavingsTransaction::where('trnx_type','trnsfer')
                                       ->whereIn('status',['approved','failed','pending','decline'])
                                       ->get();

         return view('reports.fund_transfer')->with('data',$allfndTrnx);
      }
   }
   
   public function vendors_data_report(){
      if (request()->filter == true) {
      $succesTrnx = SavingsTransaction::where('trnx_type','utility')
                                       ->whereIn('status',['approved','failed','pending','decline'])
                                       ->whereBetween('created_at',[request()->datefrom, request()->dateto])
                                       ->get();

      return view('reports.utility_data')->with('data',$succesTrnx);

      }else{
         $allTrnx = SavingsTransaction::where('trnx_type','utility')
                                     ->whereIn('status',['approved','failed','pending','decline'])
                                     ->get();

         return view('reports.utility_data')->with('data',$allTrnx);
      }
   }
   
  public function ledger_details(){ 
      $tabdata = "";    
    
    if(!empty(request()->ref)){                                     
         $savingtrx = SavingsTransaction::select('amount','type','notes','customer_id')
                                       ->where('reference_no',request()->ref)
                                       ->orWhere('slip',request()->ref)->get();
                                       
          $deatails = SavingsTransactionGL::select('type','amount','notes','general_ledger_id')
                                          ->where('slip',request()->ref)
                                          ->orWhere('reference_no',request()->ref)->get();
       
     
   //  return $deatails;
     foreach($savingtrx as $deatai){
          $tabdata .= "<tr>
               <td>".$deatai->customer->last_name." ".$deatai->customer->first_name."</td>
             <td>".$deatai->customer->acctno."</td>";
             if($deatai->type == 'debit'){ 
                $tabdata .= "<td>".number_format($deatai->amount,2)."</td>";
             }else{
               $tabdata .= "<td></td>";
             }
             if($deatai->type == 'credit'){
                  $tabdata .= "<td>".number_format($deatai->amount,2)."</td>";
             }else{
               $tabdata .= "<td></td>";
             }
             $tabdata .="<td>".$deatai->notes."</td>
             </tr>";
     }
     
     foreach($deatails as $deatai){
          $tabdata .= "<tr>
               <td>".$deatai->generalledger->gl_name."</td>
             <td>".$deatai->generalledger->gl_code."</td>";
               if($deatai->type == 'debit'){
                  $tabdata .= "<td>".number_format($deatai->amount,2)."</td>";
               }else{
                  $tabdata .= "<td></td>";
               }
             if($deatai->type == 'credit'){
               $tabdata .= "<td>".number_format($deatai->amount,2)."</td>";
               }else{
                  $tabdata .= "<td></td>";
               }
               $tabdata .= "<td>".$deatai->notes."</td>
             </tr>";
     }
        //return $tabdata;
        return ["status" => "success", "msg" => "data fetched", "data" => $tabdata];
    }else{
        return ["status" => "false", "msg" => "No Data Found", "data" => $tabdata];
    }
   }
   
   public function tranx_details(){      

         $tabdata = "";    

      if(!empty(request()->ref)){                                     
            $savingtrx = SavingsTransaction::select('amount','type','notes','customer_id')
                                          ->where('reference_no',request()->ref)
                                          ->orWhere('slip',request()->ref)->get();

            $deatails = SavingsTransactionGL::select('type','amount','notes','general_ledger_id')
                                             ->where('slip',request()->ref)
                                             ->orWhere('reference_no',request()->ref)->get();


      //  return $deatails;
      foreach($savingtrx as $deatai){
            $tabdata .= "<tr>
                  <td>".$deatai->customer->last_name." ".$deatai->customer->first_name."</td>
               <td>".$deatai->customer->acctno."</td>";
               if($deatai->type == 'debit'){ 
                  $tabdata .= "<td>".number_format($deatai->amount,2)."</td>";
               }else{
                  $tabdata .= "<td></td>";
               }
               if($deatai->type == 'credit'){
                     $tabdata .= "<td>".number_format($deatai->amount,2)."</td>";
               }else{
                  $tabdata .= "<td></td>";
               }
               $tabdata .="<td>".$deatai->notes."</td>
               </tr>";
      }

      foreach($deatails as $deatai){
            $tabdata .= "<tr>
                  <td>".$deatai->generalledger->gl_name."</td>
               <td>".$deatai->generalledger->gl_code."</td>";
                  if($deatai->type == 'debit'){
                     $tabdata .= "<td>".number_format($deatai->amount,2)."</td>";
                  }else{
                     $tabdata .= "<td></td>";
                  }
               if($deatai->type == 'credit'){
                  $tabdata .= "<td>".number_format($deatai->amount,2)."</td>";
                  }else{
                     $tabdata .= "<td></td>";
                  }
                  $tabdata .= "<td>".$deatai->notes."</td>
               </tr>";
      }
         //return $tabdata;
         return response()->json(["status" => "success", "msg" => "data fetched", "data" => $tabdata]);
      }else{
         return response()->json(["status" => "false", "msg" => "No Data Found", "data" => $tabdata]);
      }

 }

   public function tsq_report(){      
   
      return view('reports.tsq');
   }

   public function queryTransactionStatus(Request $r){  
      $getsetvalue = new Setting();
     
      if($getsetvalue->getsettingskey('payoption') == '1'){

         $response = Http::withHeaders([
            "PublicKey" => env('PUBLIC_KEY'),
         "EncryptKey" => env('ENCRYPT_KEY'),
         "Content-Type" => "application/json"
         ])->get(env('ASSETMATRIX_BASE_URL')."query-transaction/settlement",[
            'ref' => $r->reference
         ])->json();

         if($response['status'] == true){
            return response()->json(["status" => 'success','ptype' => "1","msg" => "Transaction Found Successfully", "data" => $response['data']]);
         }else {
               return response()->json(["status" => false, "msg" => "Transaction not Found"]);
         }

      }elseif($getsetvalue->getsettingskey('payoption') == '2'){

         $authbasic = base64_encode(env('MONNIFY_LIVE_API_KEY').":".env('MONNIFY_LIVE_SECRET_KEY'));
         $response = Http::withHeaders([
             "Authorization" => "Basic ".$authbasic,
            "Accept" => "application/json",
            "Content-Type" => "application/json"
         ])->get(env('MONNIFY_LIVE_URL')."v1/transactions/search",[
            "paymentReference" => $r->reference
         ])->json();

         
         if($response["responseCode"] == "0" || $response["requestSuccessful"] == true){
            return response()->json(["status" => 'success','ptype' => "2","msg" => "Transaction Found Successfully", "data" => $response["responseBody"]["content"]]);
        }else {
            return response()->json(["status" => false, "msg" => "Transaction not Found"]);
        }

      }elseif($getsetvalue->getsettingskey('payoption') == '4'){

         $response = Http::withHeaders([
             "Api-Key" => env('WIRELESS_API_KEY'),
          "Accept" => "application/json"
          ])->post("https://backup.wirelessbeta.com/api/v1/tsq-reference-number",[
            "transaction_reference" => $r->reference
         ])->json();

        //return $response;
         if($response['status'] == "success"){
               return response()->json(["status" => 'success', 'ptype' => "4", "msg" => "Transaction Found Successfully", "data" => ['status' => $response['status'],'message' => $response['message']]]);
         }else {
               return response()->json(["status" => false, "msg" => "Transaction not Found"]);
         }
      }

   }

   public function fxmgmt_report(){

       if(request()->filter == true){

         $fxnmg = Fxmgmt::where('fxtype',request()->fxtyp)
                        ->whereBetween('created_at',[request()->datefrom, request()->dateto])
                        ->get();

          return view('reports.fxmgmt_report')->with('sales',$fxnmg);

       }else{

          return view('reports.fxmgmt_report');

       }

   }

   public function GetcurrencyExchge($currencyid){
       $exghcg = Exchangerate::where('id',$currencyid)->orderBy('created_at','DESC')->first();
       return $exghcg ? $exghcg->currency_symbol : '';
   }

   public function fixedsepo_report(){

          if (empty(request()->status)) {
                if(!empty(request()->fx_filter)){ 
                     $filter = request()->fx_filter == "Null" ? null : request()->fx_filter;
                      $fxcust = Customer::select('id')->where('exchangerate_id',$filter)->get();
                    foreach($fxcust as $fxc){
                        $fx[] = $fxc->id;
                    }
                  
                    $data = FixedDeposit::with([
                        'customer:id,first_name,last_name,acctno,phone,state', 
                        'accountofficer:id,full_name',
                        'fixed_deposit_product:id,name'
                    ])->select('id','fixed_deposit_code','principal','interest_method','release_date','maturity_date','status','customer_id','accountofficer_id','fixed_deposit_product_id')
                                       ->when(request()->filter == true, function ($query) {
                                          $query->whereDate('created_at',request()->dateto);
                                       })->whereIn('customer_id',$fx)
                                        ->orderBy('id','DESC')->paginate(100);
                
                }else{
                    $data = FixedDeposit::with([
                        'customer:id,first_name,last_name,acctno,phone,state', 
                        'accountofficer:id,full_name',
                        'fixed_deposit_product:id,name'
                    ])->select('id','fixed_deposit_code','principal','interest_method','release_date','maturity_date','status','customer_id','accountofficer_id','fixed_deposit_product_id')
                                         ->when(request()->filter == true, function ($query) {
                                             $query->whereDate('created_at',request()->dateto);
                                          })->orderBy('id','DESC')->paginate(100);
                }
                
             } else{

                if(!empty(request()->fx_filter)){
                     $filter = request()->fx_filter == "Null" ? null : request()->fx_filter;
                      $fxcust = Customer::select('id')->where('exchangerate_id',$filter)->get();
                    foreach($fxcust as $fxc){
                        $fx[] = $fxc->id;
                    }
                        
                    $data = FixedDeposit::with([
                        'customer:id,first_name,last_name,acctno,phone,state', 
                        'accountofficer:id,full_name',
                        'fixed_deposit_product:id,name'
                    ])->select('id','fixed_deposit_code','principal','interest_method','release_date','maturity_date','status','customer_id','accountofficer_id','fixed_deposit_product_id')
                                       ->when(request()->filter == true, function ($query) {
                                          $query->whereDate('created_at',request()->dateto);
                                       })->whereIn('customer_id',$fx)
                                        ->where('status', request()->status)
                                        ->orderBy('id','DESC')->paginate(100);

                }else{

                    $data = FixedDeposit::with([
                        'customer:id,first_name,last_name,acctno,phone,state', 
                        'accountofficer:id,full_name',
                        'fixed_deposit_product:id,name'
                    ])->select('id','fixed_deposit_code','principal','interest_method','release_date','maturity_date','status','customer_id','accountofficer_id','fixed_deposit_product_id')
                               ->when(request()->filter == true, function ($query) {
                                 $query->whereDate('created_at',request()->dateto);
                               })->where('status', request()->status)
                              ->orderBy('id','DESC')->paginate(100);
                }
                 
             }

        return view('reports.fixed_deposit_report')->with('fixds',$data)
                                             ->with('exrate',Exchangerate::all());
   }

   public function savingbalance_report(){
        

        if(request()->filter == true){

               $filter = request()->fx_filter == "Null" ? null : request()->fx_filter;

               $cust = Customer::select('id','first_name','last_name','acctno','accountofficer_id','phone','exchangerate_id')
                              ->where('exchangerate_id',$filter)
                              ->orderBy('id','DESC')->paginate(100);
        }else{
                 $cust = Customer::select('id','first_name','last_name','acctno','accountofficer_id','phone','exchangerate_id')
                              ->where('exchangerate_id',null)
                              ->orderBy('id','DESC')->paginate(100);
        }


        return view('reports.saving_balance_report')->with('customersbal',$cust)
                                                  ->with('exrate',Exchangerate::all());
   }

   public function savingbalances_export(){

        $filter = request()->filter == true ? true : false;
        $searchval = !empty(request()->dateto) ? request()->dateto : null;
      $fxfilter = request()->fx_filter == "Null" ? null : request()->fx_filter;

        return Excel::download(new SavingsBalanceExport($searchval,$filter,$fxfilter), 'Customer_balance.xlsx');
   }
   
   public function cbn_returns_report(){      
   
      return view('reports.cbn_reports');
   }

   public function generate_cbn_report(Request $r){
      $filename = $_SERVER['DOCUMENT_ROOT']."/csv/cbnreturn.xlsx";

      $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filename);
      //$reader = IOFactory::createReader('Xls');
      $spreadsheet = $reader->load($_SERVER['DOCUMENT_ROOT']."/csv/cbnreturn.xlsx");
      $sheet = new Spreadsheet();
      //return $spreadsheet->getSheetByName("1000");
      
      $floan = 0;
      $mloan = 0;
      $floansum = 0;
      $mloansum = 0;
      $fcummuloan=0;
      $mcummuloan =0;
      global $totalcurrentac, $totalsavingac;
     

      $startMonth = Carbon::parse(date("Y-m", strtotime($r->reporting_date)))->startOfMonth();
      $startYear = Carbon::parse(date("Y", strtotime($r->reporting_date)))->startOfYear();

    
      $glacctvault = GeneralLedger::select('id')->where('gl_code',"10373391")->where('status','1')->first();//vault account GL
      $glaccttrebill = GeneralLedger::select('id')->where('gl_code',"10525945")->where('status','1')->first();//Treasury Bills account GL
      $glacctmicro = GeneralLedger::select('id')->where('gl_code',"10739869")->where('status','1')->first();//micro loans
      $glacctsme = GeneralLedger::select('id')->where('gl_code',"10156223")->where('status','1')->first();//business/sme
      $glacctstaff = GeneralLedger::select('id')->where('gl_code',"10156223")->where('status','1')->first();//staff loan
      $glacctbpur = GeneralLedger::select('id')->where('gl_code',"10502088")->where('status','1')->first();//Improvement To Building(purchase)
      $glacctblease = GeneralLedger::select('id')->where('gl_code',"10465194")->where('status','1')->first();//Improvement To Building(lease)
      $glacctplant = GeneralLedger::select('id')->where('gl_code',"10280383")->where('status','1')->first();//plant
      $glacctfurni = GeneralLedger::select('id')->where('gl_code',"10968173")->where('status','1')->first();//furniture
      $glacctmotor = GeneralLedger::select('id')->where('gl_code',"10815485")->where('status','1')->first();//motor
      $glacctoffice = GeneralLedger::select('id')->where('gl_code',"10661678")->where('status','1')->first();//office
      $glacctcapshare = GeneralLedger::select('id')->where('gl_code',"30488648")->where('status','1')->first();//capital shares
      $glacctvoluncurrnt = GeneralLedger::select('id')->where('gl_code',"20639526")->where('status','1')->first();//Voluntary Savings Deposits
      $glacctvolunsaving = GeneralLedger::select('id')->where('gl_code',"20993097")->where('status','1')->first();//Voluntary Savings Deposits
      $glacctfixeddeposit = GeneralLedger::select('id')->where('gl_code',"20944548")->where('status','1')->first();//fixed deposit Deposits 
      $glacctloanintrincm = GeneralLedger::select('id')->where('gl_code',"40248362")->where('status','1')->first();//loan interest income 
      $glacctfixdepositintrexp = GeneralLedger::select('id')->where('gl_code',"50249457")->where('status','1')->first();//fixed deposit Deposits interest expenses

      $glacctaccumd = GeneralLedger::select('id')->where('account_category_id','7')->where('status','1')->get()->toArray();//accumulated depreciation
      $glacctfeesnchrg = GeneralLedger::select('id')->where('account_category_id','36')->where('status','1')->get()->toArray();//fees and charges 
      $glacctcommisn = GeneralLedger::select('id')->where('account_category_id','20')->where('status','1')->get()->toArray();//commissions
      $glacctsfcost = GeneralLedger::select('id')->where('account_category_id','106')->where('status','1')->get()->toArray();//staff costs
      $glacctovheds = GeneralLedger::select('id')->where('account_category_id','81')->where('status','1')->get()->toArray();//overheads
      $glacctDueFromBanks = GeneralLedger::select('id','gl_name','account_balance')->where('account_category_id','32')->where('status','1')->get();//Due From Banks

      $totloan = Loan::where('status', 'disbursed')->whereMonth('created_at', '=', Carbon::parse($r->reporting_date)->format('m'))->get()->count();//total loan disbured
      
      $totloandisbur = Loan::where('status', 'disbursed')->get()->count();//total active loan
     
      $totloanamt = Loan::where('status', 'disbursed')->whereMonth('created_at', '=', Carbon::parse($r->reporting_date)->format('m'))->sum('principal');//total loan disbured amount
     
      $cumtotloan = Loan::where('status', 'disbursed')
                     ->whereDate('created_at','>=', $startYear)
                     ->whereDate('created_at','<=',$r->reporting_date)
                     ->get()->count();//total cummulative loan disbured

      $cumtotloanamt = Loan::where('status', 'disbursed')
                        ->whereDate('created_at','>=', $startYear)
                        ->whereDate('created_at','<=',$r->reporting_date)
                        ->sum('principal');//total cummulative loan disbured amount


      $loans = Loan::where('status', 'disbursed')->whereMonth('created_at', '=', Carbon::parse($r->reporting_date)->format('m'))->get();//get loan for male or female
      //get loan female and male numbers
      foreach($loans as $loan){
         $floan = Customer::where('id',$loan->customer_id)->where('gender','female')->whereMonth('created_at', '=', Carbon::parse($r->reporting_date)->format('m'))->count();
         $mloan = Customer::where('id',$loan->customer_id)->where('gender','male')->whereMonth('created_at', '=', Carbon::parse($r->reporting_date)->format('m'))->count();

         $fcummuloan = Customer::where('id',$loan->customer_id)
                           ->where('gender','female')
                           ->whereDate('created_at','>=', $startYear)
                           ->whereDate('created_at','<=',$r->reporting_date)
                           ->count();
         $mcummuloan = Customer::where('id',$loan->customer_id)
                           ->where('gender','male')
                           ->whereDate('created_at','>=', $startYear)
                           ->whereDate('created_at','<=',$r->reporting_date)
                           ->count();
      }

      //get  female and male values
      $females = Customer::select('id')->where('gender','female')->get()->toArray();
      $males = Customer::select('id')->where('gender','male')->get()->toArray();
      //dd($females);

      $totloanfemaleamt = Loan::whereIn('customer_id', $females)->where('status', 'disbursed')->whereMonth('created_at', '=', Carbon::parse($r->reporting_date)->format('m'))->sum('principal');//total loan disbured amount female
      $totloanmaleamt = Loan::whereIn('customer_id', $males)->where('status', 'disbursed')->whereMonth('created_at', '=', Carbon::parse($r->reporting_date)->format('m'))->sum('principal');//total loan disbured amount male
      
      $totloanfemalecummuamt = Loan::whereIn('customer_id', $females)
                              ->where('status', 'disbursed')
                              ->whereDate('created_at','>=', $startYear)
                              ->whereDate('created_at','<=',$r->reporting_date)
                              ->sum('principal');//total cummulative loan disbured amount female

      $totloanmalecummuamt = Loan::whereIn('customer_id', $males)
                           ->where('status', 'disbursed')
                           ->whereDate('created_at','>=', $startYear)
                           ->whereDate('created_at','<=',$r->reporting_date)
                           ->sum('principal');//total cummulative loan disbured amount male

      $femaledropout = Customer::where('gender','female')->where('status','2')->whereMonth('created_at', '=', Carbon::parse($r->reporting_date)->format('m'))->get()->count();
      $maledropout = Customer::where('gender','male')->where('status','2')->whereMonth('created_at', '=', Carbon::parse($r->reporting_date)->format('m'))->get()->count();
      
      $femaledropoutcumm = Customer::where('gender','female')
                                 ->where('status','2')
                                 ->whereDate('created_at','>=', $startYear)
                               ->whereDate('created_at','<=',$r->reporting_date)
                                 ->get()->count();//cummulative female

      $maledropoutcumm = Customer::where('gender','male')
                                 ->where('status','2')
                                 ->whereDate('created_at','>=', $startYear)
                                  ->whereDate('created_at','<=',$r->reporting_date)
                                 ->get()->count();//cummulative male
      
      $femaledropoutsavin = Customer::select('id')->where('gender','female')->where('status','2')->whereMonth('created_at', '=', Carbon::parse($r->reporting_date)->format('m'))->get()->toArray();
      $maledropoutsavin = Customer::select('id')->where('gender','male')->where('status','2')->whereMonth('created_at', '=', Carbon::parse($r->reporting_date)->format('m'))->get()->toArray();

      $fmaledutsaving = Saving::whereIn('customer_id',$femaledropoutsavin)->sum('account_balance');
      $maledutsaving = Saving::whereIn('customer_id',$maledropoutsavin)->sum('account_balance');
      
      $femaledepositors = Customer::whereIn('status',['1','4','7'])->where('gender','female')->whereMonth('created_at', '=', Carbon::parse($r->reporting_date)->format('m'))->get()->count();
      $maledepositors = Customer::whereIn('status',['1','4','7'])->where('gender','male')->whereMonth('created_at', '=', Carbon::parse($r->reporting_date)->format('m'))->get()->count();
     
      $femaledepositorsavin = Customer::select('id')->whereIn('status',['1','4','7'])->where('gender','female')->whereMonth('created_at', '=', Carbon::parse($r->reporting_date)->format('m'))->get()->toArray();
      $maledepositorsavin = Customer::select('id')->whereIn('status',['1','4','7'])->where('gender','male')->whereMonth('created_at', '=', Carbon::parse($r->reporting_date)->format('m'))->get()->toArray();
      
      $fmaledpositsaving = Saving::whereIn('customer_id',$femaledepositorsavin)->sum('account_balance');
      $maledpositsaving = Saving::whereIn('customer_id',$maledepositorsavin)->sum('account_balance');

      $arraykey= ["deposit","credit","dividend","interest","fixed_deposit","loan","fd_interest","rev_withdrawal","guarantee_restored"];
     $arraykey2 = ["rev_fixed_deposit","withdrawal","monthly_charge","debit","repayment","transfer_charge"];

     $dropoutcummfemales = Customer::select('id')->where('gender','female')->where('status','2')->get()->toArray();
     $dropoutcummumales = Customer::select('id')->where('gender','male')->where('status','2')->get()->toArray();
//     client drop-out cummulative female
      $femaldroutcrt = SavingsTransaction::whereIn('customer_id',$dropoutcummfemales)
                                       ->whereIn('type',$arraykey)
                                       ->where('status','approved')
                                       ->whereDate('created_at','>=',$startMonth)
                                       ->whereDate('created_at','<=',$r->reporting_date)
                                       ->sum('amount');

      $femaldroutdbt = SavingsTransaction::whereIn('customer_id',$dropoutcummfemales)
                                       ->whereIn('type',$arraykey2)
                                       ->where('status','approved')
                                       ->whereDate('created_at','>=',$startMonth)
                                       ->whereDate('created_at','<=',$r->reporting_date)
                                       ->sum('amount');
  
//client drop-out cummulative male
$maldroutcrt = SavingsTransaction::whereIn('customer_id', $dropoutcummumales)
                                    ->whereIn('type',$arraykey)
                                    ->where('status','approved')
                                 ->whereDate('created_at','>=',$startMonth)
                                 ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');

$maldroutdbt = SavingsTransaction::whereIn('customer_id', $dropoutcummumales)
                                 ->whereIn('type',$arraykey2)
                                 ->where('status','approved')
                                 ->whereDate('created_at','>=',$startMonth)
                                 ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');

$depositorscummfemales = Customer::select('id')->whereIn('status',['1','4','7'])->where('gender','female')->get()->toarray();
$depositorscummumales = Customer::select('id')->whereIn('status',['1','4','7'])->where('gender','male')->get()->toarray();
//cummulative depositors female
$femalecummdepositor = SavingsTransaction::whereIn('customer_id',$depositorscummfemales)
                                       ->whereIn('type',$arraykey)
                                       ->where('status','approved')
                                       ->whereDate('created_at','>=',$startMonth)
                                       ->whereDate('created_at','<=',$r->reporting_date)
                                       ->sum('amount');
//cummulative depositors male
$malecummdepositor = SavingsTransaction::whereIn('customer_id', $depositorscummumales)
                                    ->whereIn('type',$arraykey)
                                    ->where('status','approved')
                                 ->whereDate('created_at','>=',$startMonth)
                                 ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');



$ascrtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctvault->id)
                                       ->where('type','credit')
                                       ->where('status','approved')
                                       ->whereDate('created_at','>=',$startMonth)
                                       ->whereDate('created_at','<=',$r->reporting_date)
                                       ->sum('amount');
  
$asdbtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctvault->id)
                                 ->where('type','debit')
                                 ->where('status','approved')
                                 ->whereDate('created_at','>=',$startMonth)
                                 ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');
//Treasury Bills
      $trecrtrnx = SavingsTransactionGL::where('general_ledger_id',$glaccttrebill->id)
                                       ->where('type','credit')
                                       ->where('status','approved')
                                       ->whereDate('created_at','>=',$startMonth)
                                       ->whereDate('created_at','<=',$r->reporting_date)
                                       ->sum('amount');
  
$tredbtrnx = SavingsTransactionGL::where('general_ledger_id',$glaccttrebill->id)
                                 ->where('type','debit')
                                 ->where('status','approved')
                                 ->whereDate('created_at','>=',$startMonth)
                                 ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');
//micro loans
      $miccrtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctmicro->id)
                                       ->where('type','credit')
                                       ->where('status','approved')
                                       ->whereDate('created_at','>=',$startMonth)
                                       ->whereDate('created_at','<=',$r->reporting_date)
                                       ->sum('amount');
  
$micdbtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctmicro->id)
                                 ->where('type','debit')
                                 ->where('status','approved')
                                 ->whereDate('created_at','>=',$startMonth)
                                 ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');
//business/sme loans
      $smecrtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctsme->id)
                                       ->where('type','credit')
                                       ->where('status','approved')
                                       ->whereDate('created_at','>=',$startMonth)
                                       ->whereDate('created_at','<=',$r->reporting_date)
                                       ->sum('amount');
  
$smedbtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctsme->id)
                                 ->where('type','debit')
                                 ->where('status','approved')
                                 ->whereDate('created_at','>=',$startMonth)
                                 ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');
//staff loans
      $staffcrtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctstaff->id)
                                       ->where('type','credit')
                                       ->where('status','approved')
                                       ->whereDate('created_at','>=',$startMonth)
                                       ->whereDate('created_at','<=',$r->reporting_date)
                                       ->sum('amount');
  
$staffdbtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctstaff->id)
                                 ->where('type','debit')
                                 ->where('status','approved')
                                 ->whereDate('created_at','>=',$startMonth)
                                 ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');

//Improvement To Building(purchase)
      $bpurcrtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctbpur->id)
                                       ->where('type','credit')
                                       ->where('status','approved')
                                       ->whereDate('created_at','>=',$startMonth)
                                       ->whereDate('created_at','<=',$r->reporting_date)
                                       ->sum('amount');
  
$bpurdbtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctbpur->id)
                                 ->where('type','debit')
                                 ->where('status','approved')
                                 ->whereDate('created_at','>=',$startMonth)
                                 ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');

//Improvement To Building(lease)
      $bleasecrtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctblease->id)
                                       ->where('type','credit')
                                       ->where('status','approved')
                                       ->whereDate('created_at','>=',$startMonth)
                                       ->whereDate('created_at','<=',$r->reporting_date)
                                       ->sum('amount');
  
$bleasedbtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctblease->id)
                                 ->where('type','debit')
                                 ->where('status','approved')
                                 ->whereDate('created_at','>=',$startMonth)
                                 ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');
//plant and mechinery
      $plantcrtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctplant->id)
                                       ->where('type','credit')
                                       ->where('status','approved')
                                       ->whereDate('created_at','>=',$startMonth)
                                       ->whereDate('created_at','<=',$r->reporting_date)
                                       ->sum('amount');
  
$plantdbtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctplant->id)
                                 ->where('type','debit')
                                 ->where('status','approved')
                                 ->whereDate('created_at','>=',$startMonth)
                                 ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');
//furniture and fittings
      $furnicrtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctfurni->id)
                                       ->where('type','credit')
                                       ->where('status','approved')
                                       ->whereDate('created_at','>=',$startMonth)
                                       ->whereDate('created_at','<=',$r->reporting_date)
                                       ->sum('amount');
  
$furnidbtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctfurni->id)
                                 ->where('type','debit')
                                 ->where('status','approved')
                                 ->whereDate('created_at','>=',$startMonth)
                                 ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');
//motor and vehicles
      $motocrtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctmotor->id)
                                       ->where('type','credit')
                                       ->where('status','approved')
                                       ->whereDate('created_at','>=',$startMonth)
                                       ->whereDate('created_at','<=',$r->reporting_date)
                                       ->sum('amount');
  
$motodbtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctmotor->id)
                                 ->where('type','debit')
                                 ->where('status','approved')
                                 ->whereDate('created_at','>=',$startMonth)
                                 ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');
//office equipement
      $offccrtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctoffice->id)
                                       ->where('type','credit')
                                       ->where('status','approved')
                                       ->whereDate('created_at','>=',$startMonth)
                                       ->whereDate('created_at','<=',$r->reporting_date)
                                       ->sum('amount');
  
$offcdbtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctoffice->id)
                                 ->where('type','debit')
                                 ->where('status','approved')
                                 ->whereDate('created_at','>=',$startMonth)
                                 ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');
//less accumulated depreciation
      $accumcrtrnx = SavingsTransactionGL::whereIn('general_ledger_id',$glacctaccumd)
                                       ->where('type','credit')
                                       ->where('status','approved')
                                       ->whereDate('created_at','>=', $startYear)
                                       ->whereDate('created_at','<=',$r->reporting_date)
                                       ->sum('amount');
  
$accumdbtrnx = SavingsTransactionGL::whereIn('general_ledger_id',$glacctaccumd)
                                 ->where('type','debit')
                                 ->where('status','approved')
                                 ->whereDate('created_at','>=', $startYear)
                                 ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');

//Voluntary Savings Deposits
      $voluncrtrnx = SavingsTransactionGL::whereIn('general_ledger_id',[$glacctvoluncurrnt->id, $glacctvolunsaving->id])
                                       ->where('type','credit')
                                       ->where('status','approved')
                                       ->whereDate('created_at','>=', $startMonth)
                                       ->whereDate('created_at','<=',$r->reporting_date)
                                       ->sum('amount');
  
$volundbtrnx = SavingsTransactionGL::whereIn('general_ledger_id',[$glacctvoluncurrnt->id, $glacctvolunsaving->id])
                                 ->where('type','debit')
                                 ->where('status','approved')
                                 ->whereDate('created_at','>=', $startMonth)
                                 ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');
  
//capital shares
      $sharescrtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctcapshare->id)
                                       ->where('type','credit')
                                       ->where('status','approved')
                                       ->whereDate('created_at','>=', $startMonth)
                                       ->whereDate('created_at','<=',$r->reporting_date)
                                       ->sum('amount');
  
$sharesdbtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctcapshare->id)
                                 ->where('type','debit')
                                 ->where('status','approved')
                                 ->whereDate('created_at','>=', $startMonth)
                                 ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');
//fixed deposit
$fixeddpocrtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctfixeddeposit->id)
                                    ->where('type','credit')
                                    ->where('status','approved')
                                    ->whereDate('created_at','>=', $startMonth)
                                    ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');

$fixeddpodbtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctfixeddeposit->id)
                                    ->where('type','debit')
                                    ->where('status','approved')
                                    ->whereDate('created_at','>=', $startMonth)
                                    ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');

//loan interest income
$loanintrincmcrtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctloanintrincm->id)
                                    ->where('type','credit')
                                    ->where('status','approved')
                                    ->whereDate('created_at','>=', $startMonth)
                                    ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');

$loanintrincmdbtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctloanintrincm->id)
                                    ->where('type','debit')
                                    ->where('status','approved')
                                    ->whereDate('created_at','>=', $startMonth)
                                    ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');

//fixed deposit interest expenses
$fixeddpointrexpcrtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctfixdepositintrexp->id)
                                    ->where('type','credit')
                                    ->where('status','approved')
                                    ->whereDate('created_at','>=', $startMonth)
                                    ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');

$fixeddpointrexpdbtrnx = SavingsTransactionGL::where('general_ledger_id',$glacctfixdepositintrexp->id)
                                    ->where('type','debit')
                                    ->where('status','approved')
                                    ->whereDate('created_at','>=', $startMonth)
                                    ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');

//fees and charges
$feesncgrcrtrnx = SavingsTransactionGL::whereIn('general_ledger_id',$glacctfeesnchrg)
                                    ->where('type','credit')
                                    ->where('status','approved')
                                    ->whereDate('created_at','>=', $startYear)
                                    ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');

$feesncgrdbtrnx = SavingsTransactionGL::whereIn('general_ledger_id',$glacctfeesnchrg)
                                    ->where('type','debit')
                                    ->where('status','approved')
                                    ->whereDate('created_at','>=', $startYear)
                                    ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');

//commission
$commisncrtrnx = SavingsTransactionGL::whereIn('general_ledger_id',$glacctcommisn)
                                    ->where('type','credit')
                                    ->where('status','approved')
                                    ->whereDate('created_at','>=', $startYear)
                                    ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');

$commisndbtrnx = SavingsTransactionGL::whereIn('general_ledger_id',$glacctcommisn)
                                    ->where('type','debit')
                                    ->where('status','approved')
                                    ->whereDate('created_at','>=', $startYear)
                                    ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');
//staff cost
$sfcostscrtrnx = SavingsTransactionGL::whereIn('general_ledger_id',$glacctsfcost)
                                    ->where('type','credit')
                                    ->where('status','approved')
                                    ->whereDate('created_at','>=', $startYear)
                                    ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');

$sfcostsdbtrnx = SavingsTransactionGL::whereIn('general_ledger_id',$glacctsfcost)
                                    ->where('type','debit')
                                    ->where('status','approved')
                                    ->whereDate('created_at','>=', $startYear)
                                    ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');
//overheads
$ovrheadscrtrnx = SavingsTransactionGL::whereIn('general_ledger_id',$glacctovheds)
                                    ->where('type','credit')
                                    ->where('status','approved')
                                    ->whereDate('created_at','>=', $startYear)
                                    ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');

$ovrheadsdbtrnx = SavingsTransactionGL::whereIn('general_ledger_id',$glacctovheds)
                                    ->where('type','debit')
                                    ->where('status','approved')
                                    ->whereDate('created_at','>=', $startYear)
                                    ->whereDate('created_at','<=',$r->reporting_date)
                                    ->sum('amount');

$prformin = Loan::where("status", "disbursed")
                  ->where("provision_type","performing")
                  ->whereDate('created_at','>=', $startYear)
                  ->whereDate('created_at','<=',$r->reporting_date)
                  ->sum("provision_amount");//performing loan

 $loansproves = Loan::where("status", "disbursed")
                  ->whereDate('created_at','>=', $startYear)
                  ->whereDate('created_at','<=',$r->reporting_date)
                  ->get();//for loan provision rating

  $sectors = Sector::all();

//   $totsavingacct = Customer::where('account_type','1')
//                               ->whereDate('created_at','>=',$startMonth)
//                               ->whereDate('created_at','<=',$r->reporting_date)->get()->count();

//   $totcurrentacct = Customer::where('account_type','2')
//                               ->whereDate('created_at','>=',$startMonth)
//                               ->whereDate('created_at','<=',$r->reporting_date)->get()->count();

   $totnumsaving100 = Saving::where('savings_product_id','1')
                              ->where('account_balance','>=','1')
                              ->where('account_balance','<=','100000')
                              ->whereDate('created_at','>=',$startMonth)
                              ->whereDate('created_at','<=',$r->reporting_date)
                              ->count();//total number of saving btw 1-100,000

   $totnumsavingsabve100 = Saving::where('savings_product_id','1')
                                 ->where('account_balance','>=','101000')
                                 ->where('account_balance','<=','900000000000000')
                                 ->whereDate('created_at','>=',$startMonth)
                              ->whereDate('created_at','<=',$r->reporting_date)
                                 ->count();//total number of saving btw 100,001 above

   $totnumcurrent100 = Saving::where('savings_product_id','2')
                              ->where('account_balance','>=','1')
                              ->where('account_balance','<=','100000')
                              ->whereDate('created_at','>=',$startMonth)
                              ->whereDate('created_at','<=',$r->reporting_date)
                             ->count();//total number of current btw 1-100,000

   $totnumcurrentabve100 = Saving::where('savings_product_id','2')
                                 ->where('account_balance','>=','101000')
                                 ->where('account_balance','<=','900000000000000')
                                 ->whereDate('created_at','>=',$startMonth)
                                 ->whereDate('created_at','<=',$r->reporting_date)
                                 ->count();//total number of current btw 100,001 above

   $totamountsaving100 = Saving::where('savings_product_id','1')
                              ->where('account_balance','>=','1')
                              ->where('account_balance','<=','100000')
                              ->whereDate('created_at','>=',$startMonth)
                              ->whereDate('created_at','<=',$r->reporting_date)
                              ->sum('account_balance');//total amount of saving btw 1-100,000

   $totamountsavingabve100 = Saving::where('savings_product_id','1')
                              ->where('account_balance','>=','1')
                              ->where('account_balance','<=','900000000000000')
                              ->whereDate('created_at','>=',$startMonth)
                              ->whereDate('created_at','<=',$r->reporting_date)
                              ->sum('account_balance');//total amount of saving btw 100,001 above

   $totamountcurrent100 = Saving::where('savings_product_id','2')
                              ->where('account_balance','>=','1')
                              ->where('account_balance','<=','100000')
                              ->whereDate('created_at','>=',$startMonth)
                              ->whereDate('created_at','<=',$r->reporting_date)
                              ->sum('account_balance');//total amount of current btw 1-100,000

   $totamountcurrentabove100 = Saving::where('savings_product_id','2')
                              ->where('account_balance','>=','101000')
                              ->where('account_balance','<=','900000000000000')
                              ->whereDate('created_at','>=',$startMonth)
                              ->whereDate('created_at','<=',$r->reporting_date)
                              ->sum('account_balance');//total amount of current btw 100,001 above

  $numfixeddeposits100 = FixedDeposit::where('principal','>=','1')
                                    ->where('principal','<=','100000')
                                    ->whereDate('created_at','>=',$startMonth)
                                     ->whereDate('created_at','<=',$r->reporting_date)
                                     ->get()->count();//total number of fixed deposit btw 1 - 100,000

  $numfixeddepositsabove100 = FixedDeposit::where('principal','>=','101000')
                                          ->where('principal','<=','900000000000000')
                                          ->whereDate('created_at','>=',$startMonth)
                                          ->whereDate('created_at','<=',$r->reporting_date)
                                          ->get()->count();//total number of fixed deposit btw 100,001 above

  $amtfixeddeposits100 = FixedDeposit::where('principal','>=','1')
                                       ->where('principal','<=','100000')
                                       ->whereDate('created_at','>=',$startMonth)
                                       ->whereDate('created_at','<=',$r->reporting_date)
                                       ->sum("principal");//total amount of fixed deposit btw 1 - 100,000

  $amtfixeddepositsabove100 = FixedDeposit::where('principal','>=','101000')
                                          ->where('principal','<=','900000000000000')
                                          ->whereDate('created_at','>=',$startMonth)
                                          ->whereDate('created_at','<=',$r->reporting_date)
                                          ->sum("principal");//total amount of fixed deposit btw 100,001 above

      $terbills = $tredbtrnx - $trecrtrnx;
      $cashasst = $asdbtrnx - $ascrtrnx;
      $microasst = $micdbtrnx - $miccrtrnx;
      $smeasst = $smedbtrnx - $smecrtrnx;
      $staffasst = $staffdbtrnx - $staffcrtrnx;
      $freehold = $bpurdbtrnx - $bpurdbtrnx;
      $lease = $bleasedbtrnx - $bleasecrtrnx;
      $plant = $plantdbtrnx - $plantcrtrnx;
      $furni = $furnidbtrnx - $furnicrtrnx;
      $moto = $motodbtrnx - $motocrtrnx;
      $office = $offcdbtrnx - $offccrtrnx;
      $accumdepre = $accumdbtrnx - $accumcrtrnx;
      $voluntarysav = $voluncrtrnx - $volundbtrnx;
      $ordinshares = $sharescrtrnx - $sharesdbtrnx;
      $fixeddepo = $fixeddpocrtrnx - $fixeddpodbtrnx;
      $feesandchargs = $feesncgrcrtrnx - $feesncgrdbtrnx;
      $intrincome = $loanintrincmcrtrnx - $loanintrincmdbtrnx;
      $intrexpens = $fixeddpointrexpdbtrnx - $fixeddpointrexpcrtrnx;
      $commission = $commisncrtrnx - $commisndbtrnx;
      $staffcost = $sfcostsdbtrnx - $sfcostscrtrnx;
      $ovheads = $ovrheadsdbtrnx - $ovrheadscrtrnx;
      $dropoutcummufemale = $femaldroutcrt -  $femaldroutdbt;
      $dropoutcummumale = $maldroutcrt - $maldroutdbt;
      $totmloan = $microasst + $smeasst;
      
      $currentcontentrow = 13;
      $loancontentrow = 20;
      $sn =0;
      $seccontentrow = 12;
      //sheet300     
      $spreadsheet->getSheet(0)->getCell('B114')->setValue($r->md_name);
      $spreadsheet->getSheet(0)->getCell('B115')->setValue($r->md_phone);
      $spreadsheet->getSheet(0)->getCell('B117')->setValue($r->bank_email);
      $spreadsheet->getSheet(0)->getCell('C1')->setValue($r->bank_code);
      $spreadsheet->getSheet(0)->getCell('C2')->setValue($r->bank_name);
      $spreadsheet->getSheet(0)->getCell('C5')->setValue(date('d/m/Y',strtotime($r->reporting_date)));
      $spreadsheet->getSheet(0)->getCell('C6')->setValue($r->state);
      $spreadsheet->getSheet(0)->getCell('C7')->setValue($r->state_code);
      $spreadsheet->getSheet(0)->getCell('C8')->setValue($r->lga);
      $spreadsheet->getSheet(0)->getCell('C9')->setValue($r->lga_code);
      $spreadsheet->getSheet(0)->getCell('D15')->setValue(substr($cashasst,0,-3));
      $spreadsheet->getSheet(0)->getCell('D27')->setValue(substr($terbills,0,-3));
      $spreadsheet->getSheet(0)->getCell('D36')->setValue(substr($smeasst,0,-3));
      $spreadsheet->getSheet(0)->getCell('D41')->setValue(substr($staffasst,0,-3));
      $spreadsheet->getSheet(0)->getCell('D52')->setValue(substr($freehold,0,-3));
      $spreadsheet->getSheet(0)->getCell('D53')->setValue(substr($lease,0,-3));
      $spreadsheet->getSheet(0)->getCell('D54')->setValue(substr($plant,0,-3));
      $spreadsheet->getSheet(0)->getCell('D55')->setValue(substr($furni,0,-3));
      $spreadsheet->getSheet(0)->getCell('D56')->setValue(substr($moto,0,-3));
      $spreadsheet->getSheet(0)->getCell('D57')->setValue(substr($office,0,-3));
      $spreadsheet->getSheet(0)->getCell('D59')->setValue(substr($accumdepre,0,-3));
      $spreadsheet->getSheet(0)->getCell('D67')->setValue(substr($voluntarysav,0,-3));
      $spreadsheet->getSheet(0)->getCell('D68')->setValue(substr($fixeddepo,0,-3));
      $spreadsheet->getSheet(0)->getCell('D89')->setValue(substr($ordinshares,0,-3));
      $spreadsheet->getSheet(0)->getCell('D91')->setValue(substr($ordinshares,0,-3));
      $spreadsheet->getSheet(0)->getCell('E114')->setValue($r->co_name);
      $spreadsheet->getSheet(0)->getCell('E115')->setValue($r->co_phone);

      //sheet 1000
      $spreadsheet->getSheet(1)->getCell('D14')->setValue(substr($intrincome,0,-3));
      $spreadsheet->getSheet(1)->getCell('D15')->setValue(substr($intrexpens,0,-3));
      $spreadsheet->getSheet(1)->getCell('D18')->setValue(substr($commission,0,-3));
      $spreadsheet->getSheet(1)->getCell('D19')->setValue(substr($feesandchargs,0,-3));
      $spreadsheet->getSheet(1)->getCell('D25')->setValue(substr($staffcost,0,-3));
      $spreadsheet->getSheet(1)->getCell('D27')->setValue(substr(0,0,-3));
      $spreadsheet->getSheet(1)->getCell('D31')->setValue(substr($ovheads,0,-3));

      //sheet 001
      $spreadsheet->getSheet(2)->getCell('C13')->setValue($totloan);
      $spreadsheet->getSheet(2)->getCell('C15')->setValue($floan);
      $spreadsheet->getSheet(2)->getCell('C16')->setValue($mloan);
      $spreadsheet->getSheet(2)->getCell('C18')->setValue($femaledropout);
      $spreadsheet->getSheet(2)->getCell('C19')->setValue($maledropout);
      $spreadsheet->getSheet(2)->getCell('C21')->setValue($femaledepositors);
      $spreadsheet->getSheet(2)->getCell('C22')->setValue($maledepositors);
      $spreadsheet->getSheet(2)->getCell('C24')->setValue($r->male_senior);
      $spreadsheet->getSheet(2)->getCell('C25')->setValue($r->male_junior);
      $spreadsheet->getSheet(2)->getCell('C27')->setValue($r->loan_officer);
      $spreadsheet->getSheet(2)->getCell('C28')->setValue($r->male_resign);
      $spreadsheet->getSheet(2)->getCell('C29')->setValue($r->male_recruit);
      $spreadsheet->getSheet(2)->getCell('C30')->setValue($r->cbn_ndic);
      $spreadsheet->getSheet(2)->getCell('C31')->setValue($r->recommended_provision);
      $spreadsheet->getSheet(2)->getCell('C32')->setValue($r->financial_year_end);
      $spreadsheet->getSheet(2)->getCell('C34')->setValue($r->list_branch);
      $spreadsheet->getSheet(2)->getCell('C35')->setValue($r->new_branch);
      $spreadsheet->getSheet(2)->getCell('C36')->setValue($r->closed_branch);
      $spreadsheet->getSheet(2)->getCell('C37')->setValue($r->cash_center);
      $spreadsheet->getSheet(2)->getCell('C38')->setValue($r->meet_point);
      $spreadsheet->getSheet(2)->getCell('D13')->setValue(substr($totloanamt,0,-3));
      $spreadsheet->getSheet(2)->getCell('D15')->setValue(substr($totloanfemaleamt,0,-3));
      $spreadsheet->getSheet(2)->getCell('D16')->setValue(substr($totloanmaleamt,0,-3));
      $spreadsheet->getSheet(2)->getCell('D18')->setValue(substr($fmaledutsaving,0,-3));
      $spreadsheet->getSheet(2)->getCell('D19')->setValue(substr($maledutsaving,0,-3));
      $spreadsheet->getSheet(2)->getCell('D21')->setValue(substr($fmaledpositsaving,0,-3));
      $spreadsheet->getSheet(2)->getCell('D22')->setValue(substr($maledpositsaving,0,-3));
      $spreadsheet->getSheet(2)->getCell('D24')->setValue($r->female_senior);
      $spreadsheet->getSheet(2)->getCell('D25')->setValue($r->female_junior);
      $spreadsheet->getSheet(2)->getCell('D28')->setValue($r->female_resign);
      $spreadsheet->getSheet(2)->getCell('D29')->setValue($r->female_recruit);
      $spreadsheet->getSheet(2)->getCell('E13')->setValue($cumtotloan);
      $spreadsheet->getSheet(2)->getCell('E15')->setValue($fcummuloan);
      $spreadsheet->getSheet(2)->getCell('E16')->setValue($mcummuloan);
      $spreadsheet->getSheet(2)->getCell('E18')->setValue($femaledropoutcumm);
      $spreadsheet->getSheet(2)->getCell('E19')->setValue($maledropoutcumm);
      $spreadsheet->getSheet(2)->getCell('E24')->setValue($r->cum_male_senior);
      $spreadsheet->getSheet(2)->getCell('E25')->setValue($r->cum_male_junior);
      $spreadsheet->getSheet(2)->getCell('E28')->setValue($r->cum_male_resign);
      $spreadsheet->getSheet(2)->getCell('E29')->setValue($r->cum_male_recruit);
      $spreadsheet->getSheet(2)->getCell('F13')->setValue(substr($cumtotloanamt,0,-3));
      $spreadsheet->getSheet(2)->getCell('F15')->setValue(substr($totloanfemalecummuamt,0,-3));
      $spreadsheet->getSheet(2)->getCell('F16')->setValue(substr($totloanmalecummuamt,0,-3));
      $spreadsheet->getSheet(2)->getCell('F18')->setValue(substr($dropoutcummufemale,0,-3));
      $spreadsheet->getSheet(2)->getCell('F19')->setValue(substr($dropoutcummumale,0,-3));
      $spreadsheet->getSheet(2)->getCell('F21')->setValue(substr($femalecummdepositor,0,-3));
      $spreadsheet->getSheet(2)->getCell('F22')->setValue(substr($malecummdepositor,0,-3));
      $spreadsheet->getSheet(2)->getCell('F28')->setValue($r->cum_female_resign);
      $spreadsheet->getSheet(2)->getCell('F24')->setValue($r->cum_female_senior);
      $spreadsheet->getSheet(2)->getCell('F25')->setValue($r->cum_female_junior);
      $spreadsheet->getSheet(2)->getCell('F29')->setValue($r->cum_female_recruit);

      //sheet 221
      foreach($glacctDueFromBanks as $duefromBank){
          $spreadsheet->getSheet(3)->getCell('A'.$currentcontentrow)->setValue("");
          $spreadsheet->getSheet(3)->getCell('B'.$currentcontentrow)->setValue(ucwords($duefromBank->gl_name));
          $spreadsheet->getSheet(3)->getCell('D'.$currentcontentrow)->setValue(substr($duefromBank->account_balance,0,-3));
         $currentcontentrow++;
      }
     
      //sheet 711
      $spreadsheet->getSheet(7)->getCell('C12')->setValue($totloandisbur);
      $spreadsheet->getSheet(7)->getCell('D12')->setValue(substr($totmloan,0,-3));
  
      //sheet 761
      $spreadsheet->getSheet(9)->getCell('D12')->setValue(substr($prformin,0,-3));

      //sheet 771
      foreach($loansproves as $loansprov){
         $spreadsheet->getSheet(10)->getCell('A'.$loancontentrow)->setValue($sn+1);
         $spreadsheet->getSheet(10)->getCell('B'.$loancontentrow)->setValue($loansprov->loan_code);
         $spreadsheet->getSheet(10)->getCell('C'.$loancontentrow)->setValue(ucwords($loansprov->customer->last_name." ".$loansprov->customer->first_name));
         $spreadsheet->getSheet(10)->getCell('D'.$loancontentrow)->setValue(date("m/d/Y",strtotime($loansprov->maturity_date)));
         $spreadsheet->getSheet(10)->getCell('E'.$loancontentrow)->setValue(date("m/d/Y",strtotime($loansprov->maturity_date)));
         $spreadsheet->getSheet(10)->getCell('F'.$loancontentrow)->setValue(substr($loansprov->principal,0,-3));
         $spreadsheet->getSheet(10)->getCell('G'.$loancontentrow)->setValue(substr($this->loan_paid_item($loansprov->id),0,-3));
         $spreadsheet->getSheet(10)->getCell('H'.$loancontentrow)->setValue(substr($this->loan_interest_paid_item($loansprov->id),0,-3));
         $spreadsheet->getSheet(10)->getCell('J'.$loancontentrow)->setValue($loansprov->provision_type == "pass & watch" ? substr($loansprov->provision_amount,0,-3) : "");
         $spreadsheet->getSheet(10)->getCell('K'.$loancontentrow)->setValue($loansprov->provision_type == "substandard" ? substr($loansprov->provision_amount,0,-3) : "");
         $spreadsheet->getSheet(10)->getCell('L'.$loancontentrow)->setValue($loansprov->provision_type == "doubtful" ? substr($loansprov->provision_amount,0,-3) : "");
         $spreadsheet->getSheet(10)->getCell('M'.$loancontentrow)->setValue($loansprov->provision_type == "lost" ? substr($loansprov->provision_amount,0,-3) : "");

         $sn++;
         $loancontentrow++;
      }

      //sheet 762
      foreach($sectors as $secto){
         $sumloans =  Loan::where("sector_id",$secto->id)->sum("principal");

         $spreadsheet->getActiveSheet(11)->getCell('A'.$seccontentrow)->setValue($secto->sector);
         $spreadsheet->getActiveSheet(11)->getCell('C'.$seccontentrow)->setValue($secto->Loans->count());
         $spreadsheet->getActiveSheet(11)->getCell('D'.$seccontentrow)->setValue(substr($sumloans,0,-3));
        
         $seccontentrow++;

      }

    //sheet 202
    $spreadsheet->getSheet(17)->getCell('D14')->setValue($totnumcurrent100);
    $spreadsheet->getSheet(17)->getCell('D15')->setValue(substr($totamountcurrent100,0,-3));
    $spreadsheet->getSheet(17)->getCell('D17')->setValue($totnumsaving100);
    $spreadsheet->getSheet(17)->getCell('D18')->setValue(substr($totamountsaving100,0,-3));
    $spreadsheet->getSheet(17)->getCell('D20')->setValue($numfixeddeposits100);
    $spreadsheet->getSheet(17)->getCell('D21')->setValue(substr($amtfixeddeposits100,0,-3));

    $spreadsheet->getSheet(17)->getCell('E14')->setValue($totnumcurrentabve100);
    $spreadsheet->getSheet(17)->getCell('E15')->setValue(substr($totamountcurrentabove100,0,-3));
    $spreadsheet->getSheet(17)->getCell('E17')->setValue($totnumsavingsabve100);
    $spreadsheet->getSheet(17)->getCell('E18')->setValue(substr($totamountsavingabve100,0,-3));
    $spreadsheet->getSheet(17)->getCell('E20')->setValue($numfixeddepositsabove100);
    $spreadsheet->getSheet(17)->getCell('E21')->setValue(substr($amtfixeddepositsabove100,0,-3));

      $nfilename = "cbn_reports_".date("Y_m_d");

     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
     header('Content-Disposition: attachment;filename="'.$nfilename.'.xlsx"');
     header('Cache-Control: max-age=0');

     $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
   //   $writer->save($nfilename.'.xlsx');
     $writer->save('php://output');
      exit();
}
}//endclass

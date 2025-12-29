<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Loan;
use App\Models\Email;
use App\Models\Saving;
use App\Models\Setting;
use App\Models\Customer;
use App\Models\LoanProduct;
use Illuminate\Support\Str;
use App\Models\LoanSchedule;
use Illuminate\Http\Request;
use App\Models\GeneralLedger;
use App\Models\LoanRepayment;
use App\Models\Accountofficer;
use App\Models\OutstandingLoan;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Traites\LoanTraite;
use App\Http\Traites\UserTraite;
use App\Http\Traites\AuditTraite;
use App\Http\Traites\SavingTraite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class RepaymentController extends Controller
{
    use LoanTraite;
    use AuditTraite;
    use UserTraite;
    use SavingTraite;
    public function __construct()
    {
       $this->middleware('auth'); 
    }

    public function index()
    {
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

    if(Auth::user()->roles()->first()->name == 'account officer'){
         $acofficer = Accountofficer::select('id','branch_id')->where('user_id',Auth::user()->id)->first();
         
          $data = LoanRepayment::where('accountofficer_id',$acofficer->id)->get();

        return view('loan.repayment.index')->with('allpayments',$data);

    }else{
         $data = LoanRepayment::all();

        return view('loan.repayment.index')->with('allpayments',$data);
    }
       
    }

    public function create(){
         $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;
         
         $data = Loan::where('status','disbursed')->where('loan_status','open')->get();

           if(Auth::user()->roles()->first()->name == 'account officer'){
                 $acofficer = Accountofficer::select('id','branch_id')->where('user_id',Auth::user()->id)->first();
                 // ->where('branch_id',$acofficer->branch_id)
                 if(!empty(request()->lcode)){
                    $loandata = Loan::where('accountofficer_id',$acofficer->id)
                                    ->where('status','disbursed')
                                    ->where('loan_status','open')
                                    ->where('id',request()->lcode)->first();

                 $schedules = LoanSchedule::where('loan_id',request()->lcode)->get();

                    return view('loan.repayment.create')->with('loans', $data)
                                                         ->with('lcd', $loandata)
                                                        ->with('schedules',  $schedules);
                 }else{
                    return view('loan.repayment.create')->with('loans',$data);
                 }
                
           }else{
                if(!empty(request()->lcode)){
                    $loandata = Loan::where('status','disbursed')
                                    ->where('loan_status','open')
                                    ->where('id',request()->lcode)->first();

                $schedules = LoanSchedule::where('loan_id',request()->lcode)->get();

                    return view('loan.repayment.create')->with('loans', $data)
                                                        ->with('lcd', $loandata)
                                                        ->with('schedules',  $schedules);
                }else{
                    return view('loan.repayment.create')->with('loans',$data);
                }
           }
           
    }

public function store(Request $request)
{
    $lock = Cache::lock('repymnt-'.mt_rand('1111','9999'),2);
        
        if($lock->get()){
            
            DB::beginTransaction();
            
    $this->loginfo("loan repayment request log",$request->all());

    $getsetvalue = new Setting();
    
     $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

     $usern = Auth::user()->last_name." ".Auth::user()->first_name;
     
     $acofficer = Accountofficer::select('id')->where('user_id',Auth::user()->id)->first();

     $trxref = $this->generatetrnxref("R");

     $loan = Loan::findorfail($request->loanid);

     $customeracct = Saving::lockForUpdate()->where('customer_id',$request->customerid)->first();
     $customer = Customer::where('id',$request->customerid)->first();

         $loanprod = LoanProduct::select('gl_code','interest_gl','incomefee_gl')->where('id',$loan->loan_product_id)->first();

    if(empty($loanprod->gl_code) || empty($loanprod->interest_gl) || empty($loanprod->incomefee_gl)){
        return ['status' => false, 'msg' => 'Loan product GLs is required'];
    }

    $glacctmloan = GeneralLedger::select('id','gl_name','status','account_balance')
                                ->where('gl_code',$loanprod->gl_code)
                                 ->lockForUpdate()->first();

     $outloan = OutstandingLoan::where('loan_id',$request->loanid)
                                ->where('customer_id',$request->customerid)->first();

    //  $glacctmicro = GeneralLedger::select('id','status','account_balance')->where("gl_code","10739869")->first();
    //  $glacctsme = GeneralLedger::select('id','status','account_balance')->where("gl_code","10156223")->first();
     $glacctloansuspense = GeneralLedger::select('id','status','account_balance')->where("gl_code","10596204")->lockForUpdate()->first();

      //loan fee income/suspense
     $glacctloanfeeincm = GeneralLedger::select('id','status','account_balance')->where("gl_code","40953331")->lockForUpdate()->first();
     $glacctfeeinsusp = GeneralLedger::select('id','status','account_balance')->where("gl_code","20986758")->lockForUpdate()->first();
    
     //loan interest/suspense
     $glacctloaninterest = GeneralLedger::select('id','status','account_balance')->where("gl_code",$loanprod->interest_gl)->lockForUpdate()->first();
     $glacctinterestsusp = GeneralLedger::select('id','status','account_balance')->where("gl_code","20258512")->lockForUpdate()->first();
     $glacctincmsusp = GeneralLedger::select('id','status','account_balance')->where("gl_code","20117581")->lockForUpdate()->first();

    $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->lockForUpdate()->first();
$glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->lockForUpdate()->first();

    $totalamount = $request->principal + $request->interest + $request->outstanding_loan;

        // if($request->principal > $request->payable){
        //     return ['status' => false, 'msg' => 'Sorry principal amount cannot be greater amount payable'];
        // }
        
    if($customeracct->account_balance >= $totalamount){

        if($customeracct->account_balance >= $request->outstanding_loan){ //substract outstanding loan

            // $subprincinrt = $request->repayment_amount[$key] - $schedule->interest;
        if(!empty($outloan)){
              if($outloan->amount >= $request->outstanding_loan){
           
            $oysntt = $outloan->amount - $request->outstanding_loan;
            $outloan->amount = $oysntt < 0 ? 0 : $oysntt;
            $outloan->save();

            $cusoutsnd = $customeracct->account_balance - $request->outstanding_loan;
            $customeracct->account_balance = $cusoutsnd;
            $customeracct->save();
                
                $this->create_saving_transaction(Auth::user()->id,$request->customerid,$branch,$request->outstanding_loan,
                                        'debit','core','0',null,null,null,null,$trxref,'loan outstanding payment-m --'.$loan->loan_code,'approved','2','trnsfer',$usern);
                
         if(!is_null($customer->exchangerate_id)){
                $this->checkforeigncurrncy($customer->exchangerate_id,$request->outstanding_loan,$trxref,'debit');
            }else{
                //if($customer->account_type == '1'){//saving acct GL
                    if($glsavingdacct->status == '1'){
                        $this->gltransaction('deposit',$glsavingdacct,$request->outstanding_loan,null);
                        $this->create_saving_transaction_gl(null,$glsavingdacct->id,null,$request->outstanding_loan,'debit','core',$trxref,$this->generatetrnxref('svgl'),'customer debited for outstanding loan-m --'.$loan->loan_code,'approved',$usern);
                         }
                        // }elseif($customer->account_type == '2'){//current acct GL
                        //     if($glcurrentacct->status == '1'){
                        //     $this->gltransaction('deposit',$glcurrentacct,$request->outstanding_loan,null);
                        // $this->create_saving_transaction_gl(null,$glcurrentacct->id,null,$request->outstanding_loan,'debit','core',$trxref,$this->generatetrnxref('crgl'),'customer debited for outstanding loan-m --'.$loan->loan_code,'approved',$usern);
                        //     } 
                        // }
                    }
        }else{

            $oysnt = $outloan->amount - $customeracct->account_balance;
            $outloan->amount = $oysnt;
            $outloan->save();
        
            $cusoutsnd = $customeracct->account_balance - $request->outstanding_loan;
            $customeracct->account_balance = $cusoutsnd;
            $customeracct->save();
                
                $this->create_saving_transaction(Auth::user()->id,$request->customerid,$branch,$request->outstanding_loan,
                                        'debit','core','0',null,null,null,null,$trxref,'loan outstanding payment-m --'.$loan->loan_code,'approved','2','trnsfer',$usern);
                
        if(!is_null($customer->exchangerate_id)){
                $this->checkforeigncurrncy($customer->exchangerate_id,$request->outstanding_loan,$trxref,'debit');
            }else{
                //if($customer->account_type == '1'){//saving acct GL
                    if($glsavingdacct->status == '1'){
                        $this->gltransaction('deposit',$glsavingdacct,$request->outstanding_loan,null);
                        $this->create_saving_transaction_gl(null,$glsavingdacct->id,null,$request->outstanding_loan,'debit','core',$trxref,$this->generatetrnxref('svgl'),'customer debited for outstanding loan-m --'.$loan->loan_code,'approved',$usern);
                         }
                        // }elseif($customer->account_type == '2'){//current acct GL
                        //     if($glcurrentacct->status == '1'){
                        //     $this->gltransaction('deposit',$glcurrentacct,$request->outstanding_loan,null);
                        // $this->create_saving_transaction_gl(null,$glcurrentacct->id,null,$request->outstanding_loan,'debit','core',$trxref,$this->generatetrnxref('crgl'),'customer debited for outstanding loan-m --'.$loan->loan_code,'approved',$usern);
                        //     } 
                        // }
                    }

        } 
        }
         
            //loan interest
            // $this->create_saving_transaction_gl(null,$glacctloaninterest->id,null, $request->interest,'credit','core',$trxref,$this->generatetrnxref('L'),'loan interest','approved',$usern);
            // $this->gltransaction('withdrawal',$glacctloaninterest,$request->interest,null); 
    
        }

    if($customeracct->account_balance >= $request->interest){ //substract interest

        // $subprincinrt = $request->repayment_amount[$key] - $schedule->interest;
 if($request->interest > 0){

        $interest = $customeracct->account_balance - $request->interest;
        $customeracct->account_balance = $interest;
        $customeracct->save();
        
        $this->create_saving_transaction(Auth::user()->id,$request->customerid,$branch,$request->interest,
                                'debit','core','0',null,null,null,null,$trxref,'loan interest repayment-m --'.$loan->loan_code,'approved','2','trnsfer',$usern);
      
   if(!is_null($customer->exchangerate_id)){
        $this->checkforeigncurrncy($customer->exchangerate_id,$request->interest,$trxref,'debit');
    }else{
       // if($customer->account_type == '1'){//saving acct GL
            if($glsavingdacct->status == '1'){
                $this->gltransaction('deposit',$glsavingdacct,$request->interest,null);
                $this->create_saving_transaction_gl(null,$glsavingdacct->id,null,$request->interest,'debit','core',$trxref,$this->generatetrnxref('svgl'),'customer debited for loan insterest-m --'.$loan->loan_code,'approved',$usern);
            }
                // }elseif($customer->account_type == '2'){//current acct GL
                //     if($glcurrentacct->status == '1'){
                //     $this->gltransaction('deposit',$glcurrentacct,$request->interest,null);
                // $this->create_saving_transaction_gl(null,$glcurrentacct->id,null,$request->interest,'debit','core',$trxref,$this->generatetrnxref('crgl'),'customer debited for loan insterest-m --'.$loan->loan_code,'approved',$usern);
                //     }
                // }
            }
                
        //loan interest
        if($glacctloaninterest->status == '1'){
            
         $this->create_saving_transaction_gl(null,$glacctloaninterest->id,null, $request->interest,'credit','core',$trxref,$this->generatetrnxref('L'),'loan interest-m --'.$loan->loan_code,'approved',$usern);
        $this->gltransaction('withdrawal',$glacctloaninterest,$request->interest,null); 
        
        }
     
     
      }
    }

    //for pricipal
    if($customeracct->account_balance >= $request->principal){
        
        $pricipal = $customeracct->account_balance - $request->principal;
        $customeracct->account_balance = $pricipal;
        $customeracct->save();
        
        $this->create_saving_transaction(Auth::user()->id,$request->customerid,$branch,$request->principal,
                                'debit','core','0',null,null,null,null,$trxref,'loan repayment-m --'.$loan->loan_code,'approved','2','trnsfer',$usern);
        
        if($glacctmloan->status == "1"){

                  $this->gltransaction('deposit', $glacctmloan, $loan->principal,null);
            $this->create_saving_transaction_gl(null,$glacctmloan->id,null, $request->principal,'credit','core',$trxref,$this->generatetrnxref('rpm'),'loan repay--'.$loan->loan_code,'approved',$usern);
          
        }
        // if($loan->principal >= '500' && $loan->principal <= '99000'){
        //       if($glacctmicro->status == '1'){
        //         $this->gltransaction('deposit',$glacctmicro,$request->principal,null); 
        //     $this->create_saving_transaction_gl(null,$glacctmicro->id,null, $request->principal,'credit','core',$trxref,$this->generatetrnxref('micro'),'micro loans-m --'.$loan->loan_code,'approved',$usern);
        //       }
        //     }elseif($loan->principal >= '99000'){
        //         if($glacctsme->status == '1'){
        //             $this->gltransaction('deposit',$glacctsme,$request->principal,null); 
        //          $this->create_saving_transaction_gl(null,$glacctsme->id,null, $request->principal,'credit','core',$trxref,$this->generatetrnxref('sme'),'business and sme loans-m --'.$loan->loan_code,'approved',$usern);
        //      }
        //     }
        
            if(!is_null($customer->exchangerate_id)){
                $this->checkforeigncurrncy($customer->exchangerate_id,$request->principal,$trxref,'debit');
            }else{
            if($customer->account_type == '1'){//saving acct GL
                if($glsavingdacct->status == '1'){
                $this->gltransaction('deposit',$glsavingdacct,$request->principal,null);
                $this->create_saving_transaction_gl(null,$glsavingdacct->id,null,$request->principal,'debit','core',$trxref,$this->generatetrnxref('svgl'),'customer debited-m --'.$loan->loan_code,'approved',$usern.'(c)');
                }
                }elseif($customer->account_type == '2'){//current acct GL
                    if($glcurrentacct->status == '1'){
                    $this->gltransaction('deposit',$glcurrentacct,$request->principal,null);
                $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $request->principal,'debit','core',$trxref,$this->generatetrnxref('crgl'),'customer debited-m --'.$loan->loan_code,'approved',$usern.'(c)');
                    }
                }
            }
    }else{

        $actualprincpal = $customeracct->account_balance;
        $pendingdue = $request->principal - $customeracct->account_balance;

        $pricipal = $customeracct->account_balance - $request->principal;
        $customeracct->account_balance = $pricipal;
        $customeracct->save();
        
        $this->create_saving_transaction(Auth::user()->id,$request->customerid,$branch,$request->principal,
                                'debit','core','0',null,null,null,null,$trxref,'loan repayment','approved','2','trnsfer',$usern);
        
       if($glacctmloan->status == "1"){

                  $this->gltransaction('deposit', $glacctmloan, $loan->principal,null);
            $this->create_saving_transaction_gl(null,$glacctmloan->id,null, $request->principal,'credit','core',$trxref,$this->generatetrnxref('rpm'),'loan repay--'.$loan->loan_code,'approved',$usern);
          
        }
            // if($loan->principal >= '500' && $loan->principal <= '99000'){
            //     if($glacctmicro->status == '1'){
            //         $this->gltransaction('deposit',$glacctmicro,$request->principal,null); 
            //     $this->create_saving_transaction_gl(null,$glacctmicro->id,null, $request->principal,'credit','core',$trxref,$this->generatetrnxref('micro'),'micro loans-m --'.$loan->loan_code,'approved',$usern);
            //     }
            //     }elseif($loan->principal >= '99000'){
            //         if($glacctsme->status == '1'){
            //             $this->gltransaction('deposit',$glacctsme,$request->principal,null); 
            //         $this->create_saving_transaction_gl(null,$glacctsme->id,null, $request->principal,'credit','core',$trxref,$this->generatetrnxref('sme'),'business and sme loans-m --'.$loan->loan_code,'approved',$usern);
            //     }
            //     }

            if(!is_null($customer->exchangerate_id)){
                $this->checkforeigncurrncy($customer->exchangerate_id,$request->principal,$trxref,'debit');
            }else{
                if($customer->account_type == '1'){//saving acct GL
                    if($glsavingdacct->status == '1'){
                        $this->gltransaction('deposit',$glsavingdacct,$request->principal,null);
                     $this->create_saving_transaction_gl(null,$glsavingdacct->id,null,$request->principal,'debit','core',$trxref,$this->generatetrnxref('svgl'),'customer debited-m --'.$loan->loan_code,'approved',$usern.'(c)');
                    
                    }
                }elseif($customer->account_type == '2'){//current acct GL
                    if($glcurrentacct->status == '1'){
                    $this->gltransaction('deposit',$glcurrentacct,$request->principal,null);
                $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $request->principal,'debit','core',$trxref,$this->generatetrnxref('crgl'),'customer debited-m --'.$loan->loan_code,'approved',$usern.'(c)');
                    }
                }
            }
            //loan suspense
            $this->create_saving_transaction_gl(null,$glacctloansuspense->id,null, $request->principal,'debit','core',$trxref,$this->generatetrnxref('lsusp'),'loan suspense-m --'.$loan->loan_code,'approved',$usern);
                $this->gltransaction('withdrawal',$glacctloansuspense,$request->principal,null); 
                
               if(!empty($outloan)){
                    $oysnt = $outloan->amount + $pendingdue;
                    $outloan->amount = $oysnt;
                    $outloan->save();
                }else{
                    OutstandingLoan::create([
                        'loan_id' => $loan->id,
                        'customer_id' => $request->customerid,
                        'amount' => $pendingdue
                    ]); 
                }

    }

    
    $schedule = LoanSchedule::where('id',$request->schduleid)->first();
  

    LoanRepayment::create([
        "user_id" => Auth::user()->id,
        "accountofficer_id" => !empty($acofficer) ? $acofficer->id : null,
        "amount" => $totalamount,
        "loan_id" => $request->loanid ,
        "customer_id" => $request->customerid,
        "branch_id" => $branch,
        "repayment_method" => 'flat',
        "collection_date" => Carbon::now(),
        "notes" => 'loan repayment --'.$loan->loan_code,
        "type" => 'credit',
         "reference" => Str::random(10),
        "due_date" => date("Y-m-d",strtotime($schedule->due_date)),
        "status" => '1'
    ]);
    
      $schedule->closed = '1';
    $schedule->save();
    
    if (round($this->loan_total_balance($request->loanid), 2) == 0) {
        $l = Loan::findorfail($request->loanid);
        $l->status = "closed";
        $l->save();
    }

    $msg =  "An outstanding loan has been debited <br>Avail Bal: N". number_format($customeracct->account_balance,2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trxref;
     if($customer->enable_email_alert == "1"){
        Email::create([
                'user_id' => '1',
                'branch_id' => $branch,
                'subject' => 'loan repayment',
                'message' => $msg,
                'recipient' => $customer->email,
            ]);

            Mail::send(['html' => 'mails.sendmail'],[
                'msg' => $msg,
                'type' => 'loan Repayment'
            ],function($mail)use($customer,$getsetvalue){
                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                $mail->to($customer->email);
                $mail->subject('Loan Repayment');
            });
     }

    $this->tracktrails(Auth::user()->id,$branch,$usern,'loan repayment','added loan repayment');

    DB::commit();

     return ['status' => 'success', 'msg' => 'Repayment successfully saved'];

    }else{
        return ['status' => false, 'msg' => 'Insuffient Balance'];
    }
    
    $lock->release();
    
    DB::rollback();
    
    }//lock
    
}



    public function edit($id){
        return view('loan.repayment.edit')->with('repayment',LoanRepayment::findorfail($id));
    }

    public function update(Request $r, $id){
        $this->loginfo("loan repayment update request log",$r->all());

                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        $loan = Loan::findorfail($r->loanid);

        $repayment = LoanRepayment::findorfail($id);
        $repayment->amount = $r->amount;
        $repayment->loan_id = $loan->id;
        $repayment->collection_date = $r->collection_date;
        $repayment->repayment_method_id = $r->repayment_method_id;
        $repayment->notes = $r->notes;

        //determine which schedule due date the payment applies too
        $schedule = LoanSchedule::where('due_date', '>=', $r->collection_date)
                               ->where('loan_id',$loan->id)
                               ->orderBy('due_date','asc')->first();

        if (!empty($schedule)) {
            $repayment->due_date = $schedule->due_date;
        } else {
            $schedule = LoanSchedule::where('loan_id',
                $loan->id)->orderBy('due_date',
                'desc')->first();
            if ($r->collection_date > $schedule->due_date) {

                $repayment->due_date = $schedule->due_date;

            } else {
                $schedule = LoanSchedule::where('due_date', '>', $r->collection_date)
                                       ->where('loan_id',$loan->id)
                                       ->orderBy('due_date','asc')->first();

                $repayment->due_date = $schedule->due_date;
            }

        }
        $repayment->save();

         //update loan status if need be
         if (round($this->loan_total_balance($loan->id), 2) == 0) {
            $l = Loan::findorfail($loan->id);
            $l->status = "closed";
            $l->save();

        } else {
            $l = Loan::findorfail($loan->id);
            $l->status = "disbursed";
            $l->save();
        }
   
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'loan repayment','updated repayment for loan with id:'.$loan->loan_code);

        return redirect()->back()->with('success','Repayment deleted');
    }

    public function delete($id){
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        $lreapy = LoanRepayment::findorfail($id);
        $loan = Loan::findorfail($lreapy->loan_id);

        if ($this->loan_total_balance($loan->id) > 0 && $loan->status == "closed") {
            $l = Loan::findorfail($loan->id);
            $l->status = "disbursed";
            $l->save();     
        }

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'loan repayment','deleted repayment for loan with id:'.$loan->loan_code);

        return redirect()->back()->with('success','Repayment deleted');
    }

    //    print repayment
    public function pdf($id)
    {
        
        $loans = Loan::findorfail(request()->loanid);
        $repayment = LoanRepayment::findorfail($id);
        $getsetvalue = new Setting();
            $data = [
                'title' => $getsetvalue->getsettingskey('company_name')." Loan Repayment Receipt",
                'date' => date('m/d/Y'),
                'loan' => $loans,
                'repayment' => $repayment
            ];
            
            $pdf = PDF::loadView("loan.repayment.pdf", $data);
            return $pdf->download(ucfirst($loans->customer->title)." ".$loans->customer->first_name." ".$loans->customer->last_name." - Loan Repayment Receipt.pdf");
    }

    public function print($id)
    {
        $loan = Loan::findorfail(request()->loanid);
        $repayment = LoanRepayment::findorfail($id);
        return view('loan.repayment.print')->with('loan',$loan)
                                         ->with('repayment',$repayment);
    }

    public function getuserloandetails(){
        $loan = Loan::findorfail(request()->loanid);
        $schedules = LoanSchedule::where('loan_id',request()->loanid)->get();
       if(count($schedules) > 0){
        $csudtls = Customer::where('id',$loan->customer_id)->first();
        if(empty($csudtls)){
            return array(
                'status' => '0'
            );
        }else{
            $getbal = Saving::where('customer_id',$loan->customer_id)->first();
            return array(
                'status' => '1',
               'acnum' => $csudtls->acctno,
               'bal' => number_format($getbal->account_balance),
               'custmerid' => $loan->customer_id
            ); 
        }
       }else{
        return array(
            'status' => '0',
            'msg' => 'Awaiting disbursement'
        );
        
       }
    }


}

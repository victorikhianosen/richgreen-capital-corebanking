<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Email;
use App\Models\Charge;
use App\Models\Saving;
use App\Models\Setting;
use App\Models\Customer;
use App\Models\SavingFee;
use App\Models\Exchangerate;
use Illuminate\Http\Request;
use App\Models\GeneralLedger;
use App\Models\SavingsProduct;
use App\Models\OutstandingLoan;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Traites\UserTraite;
use App\Http\Traites\AuditTraite;
use App\Http\Traites\SavingTraite;
use App\Models\SavingsTransaction;
use Illuminate\Support\Facades\DB;
use App\Models\SavingsTransactionGL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use App\Exports\CustomersBalanceExport;
use App\Models\Upload_transaction_status;

class DepositmgmtController extends Controller
{
    use AuditTraite;
    use SavingTraite;
    use UserTraite;
    public function __construct()
    {
       $this->middleware('auth'); 
    }
    
    //savings product
    public function manage_saving_product(){
        return view('deposit.manage_saving_product')->with('sprods',SavingsProduct::all());
    }

    public function saving_product_create(){
        return view('deposit.create_saving_product');
    }

    public function saving_customer_balance(){

      $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

     $filter = request()->fx_filter == "Null" ? null : request()->fx_filter;
     
             $cust = Customer::select('id','first_name','last_name','acctno','accountofficer_id','phone','exchangerate_id')
                            ->when(request()->filter == true, function ($query) {
                                $query->where(function ($q) {
                                    $q->where('first_name', 'like', '%' . request()->search . '%')
                                    ->orWhere('last_name', 'like', '%' . request()->search . '%')
                                    ->orWhere('acctno', 'like', '%' . request()->search . '%');
                                });
                            })
                             ->when(!is_null($filter), function ($query) use ($filter) {
                                    $query->where('exchangerate_id', $filter);
                                })
                            ->orderBy('id','DESC')
                            ->paginate(100);

        return view('deposit.customer_balance')->with('customersbal',$cust)
                                            ->with('exrate',Exchangerate::all());
    }

    public function saving_tran_details($id){
        return view('deposit.customer_balance_details')->with('customer',Customer::findorfail($id))
                                                    ->with('transactions',SavingsTransaction::where('customer_id',$id)->orderBy('created_at','ASC')->get())
                                                    ->with('custid',SavingsTransaction::where('customer_id',$id)->orderBy('id',"ASC")->first());
    }

    public function transactions_approve_data(){
        if (request()->filter == true) {
            $Trnx = SavingsTransaction::where('is_approve','0')
                                        ->where('status','pending')
                                        ->whereBetween('created_at',[request()->datefrom, request()->dateto])
                                        ->get();
      
            return view('deposit.transaction_approval')->with('transactions',$Trnx);
      
     }else{

        $trans = SavingsTransaction::where('is_approve','0')
                                    ->where('status','pending')
                                    ->orderBy('created_at','ASC')->get();
                                    
        return view('deposit.transaction_approval')->with('transactions',$trans);

     }
    }

    public function transactions_approve_GL_data(){
        if (request()->filter == true) {
            $Trnx = SavingsTransactionGL::where('slip','=',null)
                                        ->where('status','pending')
                                        ->where('reference_no','like','%Gltogl%')
                                        ->whereBetween('created_at',[request()->datefrom, request()->dateto])
                                        ->get();
      
            return view('deposit.GLtransaction_approval')->with('transactions',$Trnx);
      
     }else{

        $trans = SavingsTransactionGL::where('slip','=',null)
                                    ->where('status','pending')
                                    ->where('reference_no','like','%Gltogl%')
                                    ->orderBy('created_at','ASC')->get();
                                    
        return view('deposit.GLtransaction_approval')->with('transactions',$trans);

     }
    }
    
    public function saving_product_store(Request $r){
        $this->logInfo("creating saving product",$r->all());

        $this->validate($r,[
            'name' => ['required','String'],
            'product_number' => ['required','String','min:3'],
            'interest_rate' => ['required','String'],
            'allow_overdraw' => ['required','String'],
            'interest_posting' => ['required','String'],
            'interest_adding' => ['required','String'],
            'minimum_balance' => ['required','String'],
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        SavingsProduct::create([
                'user_id' => Auth::user()->id,
                'name' => $r->name,
                'product_number' => $r->product_number,
                'interest_rate' => $r->interest_rate,
                'allow_overdraw' => $r->allow_overdraw,
                'interest_posting' => $r->interest_posting,
                'interest_adding' => $r->interest_adding,
                'minimum_balance' => $r->minimum_balance,
            ]);
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'deposit','created a saving product');

        return redirect()->route('savings.product')->with('success','Product Created');
    }

    public function saving_product_edit($id){
        return view('deposit.edit_saving_product')->with('ed',SavingsProduct::findorfail($id));
    }

    public function saving_product_update(Request $r,$id){
        $this->logInfo("updating saving product",$r->all());
        $this->validate($r,[
            'name' => ['required','String'],
            'product_number' => ['required','String'],
            'interest_rate' => ['required','String'],
            'allow_overdraw' => ['required','String'],
            'interest_posting' => ['required','String'],
            'interest_adding' => ['required','String'],
            'minimum_balance' => ['required','String'],
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $upsavings =  SavingsProduct::findorfail($id);

        $upsavings->update([
            'name' => $r->name,
            'product_number' => $r->product_number,
            'interest_rate' => $r->interest_rate,
            'allow_overdraw' => $r->allow_overdraw,
            'interest_posting' => $r->interest_posting,
            'interest_adding' => $r->interest_adding,
            'minimum_balance' => $r->minimum_balance,
        ]);

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'deposit','updated a saving product');

        return redirect()->route('savings.product')->with('success','Product Created');
    }

    public function saving_product_delete($id){
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

       SavingsProduct::findorfail($id)->delete();

           $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'deposit','deleted a saving product');

        return redirect()->back()->with('success','Product Deleted');
    }
    //end saving product


    //saving fees
    public function manage_saving_fee(){
        return view('deposit.manage_saving_fees')->with('sprods',SavingFee::all());
    }

    public function saving_fee_create(){
        return view('deposit.create_saving_fees')->with('savingsprods',SavingsProduct::all());
    }

    public function saving_fee_store(Request $r){
        $this->logInfo("creating saving fees",$r->all());
        $this->validate($r,[
            'name' => ['required','String'],
            'amount' => ['required','String','numeric'],
            'fees_posting' => ['required','String'],
            'fees_adding' => ['required','String'],
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        SavingFee::create([
                'user_id' => Auth::user()->id,
                'name' => $r->name,
                'savings_products' => $r->savings_products,
                'amount' => $r->amount,
                'fees_posting' => $r->fees_posting,
                'fees_adding' => $r->fees_adding,
            ]);
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'deposit','created a saving fee');

        return redirect()->route('savings.fee')->with('success','Saving Fee Created');
    }

    public function saving_fee_edit($id){
        return view('deposit.edit_saving_fees')->with('savingsprods',SavingsProduct::all())
                                                ->with('ed',SavingFee::findorfail($id));
    }

    public function saving_fee_update(Request $r,$id){
        $this->logInfo("updating saving product",$r->all());
        $this->validate($r,[
            'name' => ['required','String'],
            'amount' => ['required','String','numeric'],
            'fees_posting' => ['required','String'],
            'fees_adding' => ['required','String'],
        ]);
        $upsavings =  SavingFee::findorfail($id);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $upsavings->update([
            'name' => $r->name,
            'savings_products' => $r->savings_products,
            'amount' => $r->amount,
            'fees_posting' => $r->fees_posting,
            'fees_adding' => $r->fees_adding,
        ]);

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'deposit','updated a saving fee');

        return redirect()->route('savings.fee')->with('success','Saving Fee Updated');
    }

    public function saving_fee_delete($id){
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        SavingFee::findorfail($id)->delete();

           $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'deposit','deleted a saving fee');

        return redirect()->back()->with('success','Saving Fee Deleted');
    }
    //end saving fee

    //saving transaction
    public function manage_saving_tran(){
        //  $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        if(request()->filter == true){
            $savtrans = SavingsTransaction::with('customer')->select('customer_id','amount','type','slip','reference_no','created_at')
                                        ->where('status_type','!=','31')
                                        ->whereBetween('created_at',[request()->datefrom,request()->dateto])
                                    ->orderBy('created_at','DESC')->take(100)->get();
        }else{

            $savtrans = SavingsTransaction::with('customer')->select('customer_id','amount','type','slip','reference_no','created_at')
                                        ->where('status_type','!=','31')
                                        ->orderBy('created_at','DESC')->take(100)->get();
        }
       
        return view('deposit.manage_saving_transaction')->with('strans',$savtrans);
    }

    // public function saving_tran_edit($id){
    //     return view()->with('ed',SavingsTransaction::findorfail($id));
    // }
    
    public function saving_tran_update(Request $r){
        $this->logInfo("updating saving transaction",$r->all());

        SavingsTransaction::where('id',$r->trnid)->update([
            'type' => $r->type
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'deposit/savings transaction','updated a savings transaction type');

        return redirect()->back()->with('success','Saving Transaction Update');
    }

    public function saving_tran_delete($id){
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        SavingFee::findorfail($id)->delete();

           $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'deposit','deleted a saving fee');

        return redirect()->back()->with('success','Saving Fee Deleted');
    }
    //end saving fee

    //manage accounts
    public function manage_all_accounts(){
         if(request()->ac_type == "domicilary account"){
        return view('deposit.manage_accounts')->with('domiaccounts',Customer::whereNotNull('exchangerate_id')->where('status','1')->get());
        }elseif(request()->ac_type == "Savings Account" || request()->ac_type == "Current Account"){
             $accts = SavingsProduct::where('name',request()->ac_type)->first();
        return view('deposit.manage_accounts')->with('accounts',Customer::where('account_type',$accts->id)->whereNull('exchangerate_id')->where('status','1')->get());
        }
    }

    //make_transactions: deposit,withdrawal
    public function create_transactions(){
        return view('deposit.make_transactions');
    }
    
    public function charges_posting(){
        return view('deposit.make_transactions')->with('charges',Charge::all());
    }

    //transfers
    public function accounts_transfer_funds(){
        return view('deposit.account_transfer');
    }

    public function accounts_transfer(Request $r){

         $lock = Cache::lock('bnkacctrnf-'.mt_rand('1111','9999'),5);
       
       if($lock->get()){ 

        DB::beginTransaction();
           
        $this->logInfo("account to account transfer",$r->all());

        $this->validate($r,[
            'acno_one' => ['required','string','numeric'],
            'amount' => ['required','string','numeric','gt:0'],
        ]);
        
        if (preg_match('/[\'^£$%&*}{@#~?><>()"|=_+¬]/', $r->description)) {
                return ['status' => '0', 'msg' => "No special character allowed in narration"];
            }

     $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;
     
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;

         $cust = Customer::where('id',$r->customerid)->first();

         $chkcres = $this->checkCustomerRestriction($r->customerid);
            if($chkcres == true){
                $this->tracktrails(Auth::user()->id,$branch,$usern,'customer','Account Restricted');
            return array('status' => '0','msg' => 'Customer Account Has Been Restricted');
            }
            
         $chklien = $this->checkCustomerLienStatus($r->customerid);
            if($chklien['status'] == true && $chklien['lien'] == 2){
                $this->tracktrails(Auth::user()->id,$branch,$usern,'customer','Account lien');
            return array('status' => '0','msg' => 'Customer Account has been lien('.$chklien['message'].')...please contact support');
            }
            
        $customeracct = Saving::lockForUpdate()->where('customer_id',$r->customerid)->first();

        $custt2 = Customer::where('id',$r->customerid2)->first();

      if($custt2->exchangerate_id != $cust->exchangerate_id){

           return ['status' => '0', 'msg' => 'Currency mis-match'];

        }

        $getsetvalue = new Setting();
        
        $convrtamt = 0;
        $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('till_account'))->lockForUpdate()->first();

        $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->lockForUpdate()->first();
        $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->lockForUpdate()->first();   
      
        $validateuserbalance = $this->validatecustomerbalance($r->customerid,$r->amount);
        if($validateuserbalance["status"] == false){
            $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
            return ['status' => '0', 'msg' => $validateuserbalance["message"]];
        }  
        
        if($r->dbit == 'debit'){
            $trxref = $this->generatetrnxref($r->tran_initial);
            
            if($getsetvalue->getsettingskey('withdrawal_limit') == 0 || $r->amount < $getsetvalue->getsettingskey('withdrawal_limit')){
              
                $dedamount = $customeracct->account_balance - $r->amount;
                $customeracct->account_balance = $dedamount;
            $customeracct->save();

            if(!is_null($cust->exchangerate_id)){
                $this->checkforeigncurrncy($cust->exchangerate_id,$r->amount,$trxref,'debit');
            }else{
               // if($cust->account_type == '1'){//saving acct GL
                    if($glsavingdacct->status == '1'){
                         $this->gltransaction('deposit',$glsavingdacct,$r->amount,null);
               $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $r->amount,'debit','core',$trxref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                   
                    }
                  
            //    }elseif($cust->account_type == '2'){//current acct GL
               
            //        if($glcurrentacct->status == '1'){
            //        $this->gltransaction('deposit',$glcurrentacct,$r->amount,null);
            //    $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $r->amount,'debit','core',$trxref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
            //        }
                   
            //    }
            }

            $this->create_saving_transaction(Auth::user()->id,$r->customerid,$branch,$r->amount,
            $r->tran_type,'core','0',$r->slipno,null,null,null,$trxref,str_replace("'", "",$r->description),'approved','2','trnsfer',$usern);

            
        $this->tracktrails(Auth::user()->id,$branch,$usern,'account transfer','withdraw from an account');
    
    if($glacct->status == '1'){
        $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$r->amount,'credit','core',$trxref,$this->generatetrnxref('ctp'),str_replace("'", "",$r->description),'approved',$usern);

        $this->gltransaction('deposit',$glacct,$r->amount,$branch);
    }
    
    $smsmsg = "Debit Amt: N".number_format($r->amount,2)."\n Desc: ".str_replace("'", "",$r->description)." \n Avail Bal: ".number_format($customeracct->account_balance,2)."\n Date: ".date("Y-m-d")."\n Ref: ".$trxref;
                         
    if($cust->enable_sms_alert){
    $this->sendSms($cust->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
    }

    if($cust->enable_email_alert){
         $msg =  "Debit Amt: N".number_format($r->amount,2)."<br> Desc: ".str_replace("'", "",$r->description)." <br>Avail Bal: N". number_format($customeracct->account_balance,2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trxref;
        Email::create([
               'user_id' => $cust->id,
               'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert',
               'message' => $msg,
               'recipient' => $cust->email,
           ]);

           Mail::send(['html' => 'mails.sendmail'],[
               'msg' => $msg,
               'type' => 'Debit Transaction'
           ],function($mail)use($getsetvalue,$cust){
               $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                $mail->to($cust->email);
               $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
           });
    }

    $chkcres = $this->checkCustomerRestriction($r->customerid2);
             $chklien = $this->checkCustomerLienStatus($r->customerid2);
            
            if($chkcres == true){
              
            $amnt = $customeracct->account_balance + $r->amount;
            $customeracct->account_balance = $amnt;
            $customeracct->save();

            $this->create_saving_transaction(Auth::user()->id,$r->customerid,$branch,$r->amount,
            $r->tran_type2,'core','0',$r->slipno,null,null,null,$trxref,'transaction reversed','approved','4','trnsfer',$usern);

            $this->tracktrails(Auth::user()->id,$branch,$usern,'account transfer','transaction reversed');
            
            if($glacct->status == '1'){
            $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$r->amount,'debit','core',$trxref,$this->generatetrnxref('ctp'),'transaction reversed','approved',$usern);
    
            $this->gltransaction('withdrawal',$glacct,$r->amount,$branch);
            }
            
            return array('status' => '0','msg' => 'Destination Account Restricted');
            
            }elseif($chklien['status'] == true && $chklien['lien'] == 1){
                
               $ramunt = $customeracct->account_balance + $r->amount;
               $customeracct->account_balance = $ramunt;
            $customeracct->save();

            $this->create_saving_transaction(Auth::user()->id,$r->customerid,$branch,$r->amount,
            $r->tran_type2,'core','0',$r->slipno,null,null,null,$trxref,'transaction reversed','approved','4','trnsfer',$usern);

            $this->tracktrails(Auth::user()->id,$branch,$usern,'account transfer','transaction reversed');
    
         if($glacct->status == '1'){
            $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$r->amount,'debit','core',$trxref,$this->generatetrnxref('ctp'),'transaction reversed','approved',$usern);
    
            $this->gltransaction('withdrawal',$glacct,$r->amount,$branch);
         }   
                return array('status' => '0','msg' => 'Destination account has been lien('.$chklien['message'].')...please contact support');
                
            }else{
                
                 $this->credit_account_transfer($r,$r->customerid2,$trxref,$glacct);
            }

            return array(
                'status' => 'success',
                'msg' => 'Transfer Successful'
             );
       
            }else{
                
                $dacct = $custt2->last_name." ".$custt2->first_name.','.$custt2->acctno;

                 $this->create_saving_transaction(Auth::user()->id,$r->customerid,$branch,$r->amount,$r->tran_type,'core','0',$r->slipno,'0','actoac',$dacct,$trxref,str_replace("'", "",$r->description),'pending','2','trnsfer',$usern);
               
                if($glacct->status == '1'){
                    $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$r->amount,'credit','core',$trxref,$this->generatetrnxref('ctp'),str_replace("'", "",$r->description),'pending',$usern);
                }

                return array(
                    'status' => 'success',
                    'msg' => 'Transfer Successful...Awaiting Approval'
                );
            }
             
        }
        
        DB::commit();

        $lock->release();
         }//lock
         
         DB::rollBack();
    }

   public function credit_account_transfer($r,$cid,$trx,$glacct){

        $customeracct2 = Saving::lockForUpdate()->where('customer_id',$cid)->first();
        $cust = Customer::where('id',$cid)->first();
        
            $usern = Auth::user()->last_name." ".Auth::user()->first_name;

        $getsetvalue = new Setting();

        $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->lockForUpdate()->first();
        $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->lockForUpdate()->first();   
         
            $convrtamt =0;
        $tref = $this->generatetrnxref($r->tran_initial2);

        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

     if($getsetvalue->getsettingskey('deposit_limit') == 0 || $r->amount < $getsetvalue->getsettingskey('deposit_limit')){
        
        
             $runt = $customeracct2->account_balance + $r->amount;
             $customeracct2->account_balance = $runt;
           $customeracct2->save();
        
             $this->checkOutstandingCustomerLoan($cid,$r->amount);//check if customer has an outstanding loan
             
            $this->create_saving_transaction(Auth::user()->id,$cid,$branch,$r->amount,$r->tran_type2,'core','0',$r->slipno,null,null,
                                            null,$tref,str_replace("'", "",$r->description),'approved','1','trnsfer',$usern);

    if(!is_null($cust->exchangerate_id)){
                 $this->checkforeigncurrncy($cust->exchangerate_id,$r->amount,$tref,'credit');
            }else{
                 //if($cust->account_type == '1'){//saving acct GL
        
               if($glsavingdacct->status == '1'){         
                        $this->gltransaction('withdrawal',$glsavingdacct,$r->amount,null);
                    $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $r->amount,'credit','core',$tref,$this->generatetrnxref('D'),'customer credited','approved',$usern);
               } 
               
        // }elseif($cust->account_type == '2'){//current acct GL
        
        //     if($glcurrentacct->status == '1'){
        //     $this->gltransaction('withdrawal',$glcurrentacct,$r->amount,null);
        // $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $r->amount,'credit','core',$tref,$this->generatetrnxref('D'),'customer credited','approved',$usern);
            
        //     }
        // }

     }
        
       
       
        $this->tracktrails(Auth::user()->id,$branch,$usern,'account transfer','deposited to an account');

    if($glacct->status == '1'){
        $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$r->amount,'debit','core',$tref,$this->generatetrnxref('tcp'),str_replace("'", "",$r->description),'approved',$usern);

         $this->gltransaction('withdrawal',$glacct,$r->amount,$branch);
    }
    
    if($cust->enable_email_alert == '1'){
         $msg =  "Credit Amt: N".number_format($r->amount,2)."<br> Desc: ".str_replace("'", "",$r->description)." <br>Avail Bal: N". number_format($customeracct2->account_balance,2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$tref;
         Email::create([
                'user_id' => $cust->id,
                'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
                'message' => $msg,
                'recipient' => $cust->email,
            ]);

            Mail::send(['html' => 'mails.sendmail'],[
                'msg' => $msg,
                'type' => 'Credit Transaction'
            ],function($mail)use($getsetvalue,$cust){
                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                 $mail->to($cust->email);
                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
            });
        }
   }else{
            $this->create_saving_transaction(Auth::user()->id,$cid,$branch,$r->amount,
        $r->tran_type2,'core','0',$trx,'0','deposit',null, $tref,str_replace("'", "",$r->description),'pending','1','trnsfer',$usern);
        
        $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$r->amount,'debit','core',$tref,$this->generatetrnxref('tcp'),str_replace("'", "",$r->description),'pending',$usern);

            }
    }


    //deposit, withdrawal,charge-posting
    public function store_transactions(Request $r){

           $trxref = $this->generatetrnxref($r->tran_initial);

 $lock= Cache::lock('strtrnx-'.$trxref,2);
     
     if($lock->get()){
         try {
        DB::beginTransaction();
        
            $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;
   
        if (preg_match('/[\'^£$%&*}{@#~?><>()"|=_+¬]/', $r->description)) {
                return ['status' => '0', 'msg' => "No special character allowed in narration"];
            }
            
        //$checkslipno = $this->check_transaction_slip($r->slipno);
         
            
            $usern = Auth::user()->last_name." ".Auth::user()->first_name;

            $getsetvalue = new Setting();
            
        $convrtamt = 0;
        
        $customeracct = Saving::lockForUpdate()->where('customer_id',$r->customerid)->first();
          $cust = Customer::where('id',$r->customerid)->first();
          
        $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('till_account'))->first();
        
        $glchrgacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','40476030')->lockForUpdate()->first();

        $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->lockForUpdate()->first();
         $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->lockForUpdate()->first();
            
         if($r->tran_type == 'deposit'){
            
            $this->logInfo("deposit via core banking",$r->all());
           
            $this->validate($r,[
                'amount' => ['required','string','numeric','gt:0'],
                
            ]);

  $chkcres = $this->checkCustomerRestriction($r->customerid);
            if($chkcres == true){
                 $this->tracktrails(Auth::user()->id,$branch,$usern,'customer','Account Restricted');
                 
                return array('status' => '0','msg' => 'Customer account restricted');
            }
            
             $chklien = $this->checkCustomerLienStatus($r->customerid);
             if($chklien['status'] == true && $chklien['lien'] == 1){
                 $this->tracktrails(Auth::user()->id,$branch,$usern,'customer','Account Lien');
                 
                return array('status' => '0','msg' => 'Customer account has been lien('.$chklien['message'].')...please contact support');
            }
            
            
                
            if($getsetvalue->getsettingskey('deposit_limit') == 0 || $r->amount < $getsetvalue->getsettingskey('deposit_limit')){
                
                    $ramount = $customeracct->account_balance + $r->amount;
                     $customeracct->account_balance = $ramount;
                      $customeracct->save();
                
              
            $this->create_saving_transaction(Auth::user()->id,$r->customerid,$branch,$r->amount,
                                             $r->tran_type,'core','0',$r->slipno,null,null,null,$trxref,str_replace("'", "",$r->description),'approved','1','trnsfer',$usern);
            if($glacct->status == '1'){
                
                $this->gltransaction('withdrawal',$glacct,$r->amount,$branch);
            $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$r->amount,'debit','core',$trxref,$this->generatetrnxref('tcp'),str_replace("'", "",$r->description),'approved',$usern);
            
                
            }
         
         
              if(!is_null($cust->exchangerate_id)){
                 $this->checkforeigncurrncy($cust->exchangerate_id,$r->amount,$trxref,'credit');
            }else{
                  //if($cust->account_type == '1'){//saving acct GL
         
                    if($glsavingdacct->status == '1'){
                        $this->gltransaction('withdrawal',$glsavingdacct,$r->amount,null);
                    $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $r->amount,'credit','core',$trxref,$this->generatetrnxref('Cr'),'customer credited','approved',$usern);
                    }
              
            // }elseif($cust->account_type == '2'){//current acct GL
            
            // if($glcurrentacct->status == '1'){
            //     $this->gltransaction('withdrawal',$glcurrentacct,$r->amount,null);
            // $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $r->amount,'credit','core',$trxref,$this->generatetrnxref('Cr'),'customer credited','approved',$usern);
            //      }
                 
            // }
                 }
     
                 $this->checkOutstandingCustomerLoan($r->customerid,$r->amount);//check if customer has an outstanding loan

            DB::commit();

         if($cust->enable_email_alert == '1'){
        $msg =  "Credit Amt: N".number_format($r->amount,2)."<br> Desc: ".str_replace("'", "",$r->description)." <br>Avail Bal: N". number_format($customeracct->account_balance,2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trxref;
         Email::create([
                'user_id' => $cust->id,
                'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
                'message' => $msg,
                'recipient' => $cust->email,
            ]);

            Mail::send(['html' => 'mails.sendmail'],[
                'msg' => $msg,
                'type' => 'Credit Transaction'
            ],function($mail)use($getsetvalue,$cust){
                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                 $mail->to($cust->email);
                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
            });
         }
          
        
        $this->tracktrails(Auth::user()->id,$branch,$usern,'deposit','deposited to an account');
        
     

        return array(
            'status' => 'success',
            'msg' => 'Account Credited Successfully'
        );
        
        }else{
            $this->create_saving_transaction(Auth::user()->id,$r->customerid,$branch,$r->amount,
                                             $r->tran_type,'core','0',$r->slipno,'0','deposit',null,$trxref,str_replace("'", "",$r->description),'pending','1','trnsfer',$usern);
        

        $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$r->amount,'debit','core',$trxref,$this->generatetrnxref('tcp'),str_replace("'", "",$r->description),'pending',$usern);

          return array(
                    'status' => 'success',
                    'msg' => 'Deposit Posted...Awaiting Approval'
                );
            }
        
       
         }elseif($r->tran_type == 'withdrawal'){

            $this->logInfo("withdrawal via core banking",$r->all());
              
            $this->validate($r,[
                'amount' => ['required','string','numeric','gt:0'],
            ]);

  $chkcres = $this->checkCustomerRestriction($r->customerid);
            if($chkcres == true){
                 $this->tracktrails(Auth::user()->id,$branch,$usern,'customer','Account Restricted');
                 
                return array('status' => '0','msg' => 'Customer account restricted');
            }
            
             $chklien = $this->checkCustomerLienStatus($r->customerid);
             if($chklien['status'] == true && $chklien['lien'] == 2){
                 $this->tracktrails(Auth::user()->id,$branch,$usern,'customer','Account Lien');
                 
                return array('status' => '0','msg' => 'Customer account has been lien('.$chklien['message'].')...please contact support');
            }
            
            $validateuserbalance = $this->validatecustomerbalance($r->customerid,$r->amount);
                if($validateuserbalance["status"] == false){
                    $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
                    return ['status' => '0', 'msg' => $validateuserbalance["message"]];
                }
            
                    
            if($getsetvalue->getsettingskey('withdrawal_limit') == 0 || $r->amount < $getsetvalue->getsettingskey('withdrawal_limit')){
                
                $rwamount = $customeracct->account_balance - $r->amount;
                $customeracct->account_balance = $rwamount;
                $customeracct->save();
                
                $this->create_saving_transaction(Auth::user()->id,$r->customerid,$branch,$r->amount,
                                                 $r->tran_type,'core','0',$r->slipno,null,null,null,$trxref,str_replace("'", "",$r->description),'approved','2','trnsfer',$usern);
                 if($glacct->status == '1'){
                     
                     $this->gltransaction('deposit',$glacct,$r->amount,$branch);
          $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$r->amount,'credit','core',$trxref,$this->generatetrnxref('ctp'),str_replace("'", "",$r->description),'approved',$usern);
            
             }
         
          if(!is_null($cust->exchangerate_id)){
                 $this->checkforeigncurrncy($cust->exchangerate_id,$r->amount,$trxref,'debit');
            }else{
                   // if($cust->account_type == '1'){//saving acct GL
          
             if($glsavingdacct->status == '1'){
                $this->gltransaction('deposit',$glsavingdacct,$r->amount,null);
                 $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $r->amount,'debit','core',$trxref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                }
                
            // }elseif($cust->account_type == '2'){//current acct GL
            
            //     if($glcurrentacct->status == '1'){
            //     $this->gltransaction('deposit',$glcurrentacct,$r->amount,null);
            // $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $r->amount,'debit','core',$trxref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
            //     }
                
            // } 
                 }
         
        
         DB::commit();

         if($cust->enable_email_alert == '1'){
                 $msg =  "Debit Amt: N".number_format($r->amount,2)."<br> Desc: ".str_replace("'", "",$r->description)." <br>Avail Bal: N". number_format($customeracct->account_balance,2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trxref;
            Email::create([
                'user_id' => $cust->id,
                'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert',
                'message' => $msg,
                'recipient' => $cust->email,
            ]);

            Mail::send(['html' => 'mails.sendmail'],[
                'msg' => $msg,
                'type' => 'Debit Transaction'
            ],function($mail)use($getsetvalue,$cust){
                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                 $mail->to($cust->email);
                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
            });
         }
            $this->tracktrails(Auth::user()->id,$branch,$usern,'withdrawal','withdraw from an account');
            
            return array(
                'status' => 'success',
                'msg' => 'Account Withdrawn Successfully'
            );
            
            }else{
                  $this->create_saving_transaction(Auth::user()->id,$r->customerid,$branch,$r->amount,
                                                 $r->tran_type,'core','0',$r->slipno,'0','withdrawal',null,$trxref,str_replace("'", "",$r->description),'pending','2','trnsfer',$usern);
                                                 
            $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$r->amount,'credit','core',$trxref,$this->generatetrnxref('ctp'),str_replace("'", "",$r->description),'pending',$usern);

                    return array(
                        'status' => 'success',
                        'msg' => 'Withdrawal Posted...Awaiting Approval'
                    );
            }
          
        
        }elseif($r->tran_type == 'reversal'){
            $this->logInfo("reversal via core banking",$r->all());
             
            $this->validate($r,[
                'amount' => ['required','string','numeric','gt:0'],
            ]);
            
            global $getslip;
            $gettranslip = SavingsTransaction::where('type',$r->type)->where('slip',$r->slipno)->first();
            $gettranref = SavingsTransaction::where('type',$r->type)->where('reference_no',$r->slipno)->first();
                                            //   ->where('slip',$r->slipno)
                                            //   ->orWhere('reference_no',$r->slipno)->get();
                                            
            if($gettranslip){
                $getslip = $gettranslip;
            }elseif($gettranref){
                $getslip = $gettranref;
            }
                                               
              if($getslip){
                 return array(
                 'status' => '2',
                 'msg' => 'These transaction has already been reversed'
                );  
              }else{
                  if($r->revtyp == 'deposit' || $r->revtyp == 'credit'){
                         $chkcres = $this->checkCustomerRestriction($r->customerid);
                            if($chkcres == true){
                                 $this->tracktrails(Auth::user()->id,$branch,$usern,'customer','Account Restricted');
                                 
                                return array('status' => '0','msg' => 'Customer account restricted');
                            }
                            
                             $chklien = $this->checkCustomerLienStatus($r->customerid);
                             if($chklien['status'] == true && $chklien['lien'] == 1){
                                 $this->tracktrails(Auth::user()->id,$branch,$usern,'customer','Account Lien');
                                 
                                return array('status' => '0','msg' => 'Customer account has been lien('.$chklien['message'].')...please contact support');
                            }
                            
               if($r->type == 'rev_deposit' && !empty($r->type)){
                   
                   $validateuserbalance = $this->validatecustomerbalance($r->customerid,$r->amount);
                if($validateuserbalance["status"] == false){
                    $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
                    return ['status' => '0', 'msg' => $validateuserbalance["message"]];
                }
                
                $rvdamount = $customeracct->account_balance - $r->amount;
                $customeracct->account_balance = $rvdamount;
                $customeracct->save();
                
                $this->create_saving_transaction(Auth::user()->id,$r->customerid,$branch,$r->amount,
                                                 $r->type,'core','0',$r->slipno,null,null,null,$trxref,str_replace("'", "",$r->description),'approved','3','trnsfer',$usern);
                 
            
            if(!is_null($cust->exchangerate_id)){
                 $this->checkforeigncurrncy($cust->exchangerate_id,$r->amount,$trxref,'debit');
            }else{
                  // if($cust->account_type == '1'){//saving acct GL
            
             if($glsavingdacct->status == '1'){           
                $this->gltransaction('deposit',$glsavingdacct,$r->amount,null);
                  $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $r->amount,'debit','core',$trxref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
            }
            
            // }elseif($cust->account_type == '2'){//current acct GL
            
            //     if($glcurrentacct->status == '1'){
            //     $this->gltransaction('deposit',$glcurrentacct,$r->amount,null);
            // $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $r->amount,'debit','core',$trxref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
            //     }
                
            // } 
                 }
            
                 if($glacct->status == '1'){
                
                    $this->gltransaction('deposit',$glacct,$r->amount,$branch);
                    
                $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$r->amount,'credit','core',$trxref,$this->generatetrnxref('ctp'),str_replace("'", "",$r->description),'approved',$usern);
                   }
                   
                 $this->tracktrails(Auth::user()->id,$branch,$usern,'reversal','deposit reversal was carried out from an account');

            DB::commit();

            if($cust->enable_email_alert == '1'){
              $msg =  "Debit Amt: N".number_format($r->amount,2)."<br> Desc: ".str_replace("'", "",$r->description)." <br>Avail Bal: N". number_format($customeracct->account_balance,2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trxref;
            Email::create([
                'user_id' => $cust->id,
                'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert',
                'message' => $msg,
                'recipient' => $cust->email,
            ]);
   
            Mail::send(['html' => 'mails.sendmail'],[
                'msg' => $msg,
                'type' => 'Debit Transaction'
            ],function($mail)use($getsetvalue,$cust){
                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                 $mail->to($cust->email);
                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
            });
            }
            
            return array(
                'status' => 'success',
                'msg' => 'Deposit Reversed Successfully'
            );
               }else{
                return array(
                    'status' => '0',
                    'msg' => 'Sorry these is a deposit transaction'
                );
               }
               
            }elseif($r->revtyp == 'withdrawal' || $r->revtyp == 'debit'){
                
                   $chkcres = $this->checkCustomerRestriction($r->customerid);
                    if($chkcres == true){
                         $this->tracktrails(Auth::user()->id,$branch,$usern,'customer','Account Restricted');
                         
                        return array('status' => '0','msg' => 'Customer account restricted');
                    }
                    
                     $chklien = $this->checkCustomerLienStatus($r->customerid);
                     if($chklien['status'] == true && $chklien['lien'] == 2){
                         $this->tracktrails(Auth::user()->id,$branch,$usern,'customer','Account Lien');
                         
                        return array('status' => '0','msg' => 'Customer account has been lien('.$chklien['message'].')...please contact support');
                    }
            
               if($r->type == 'rev_withdrawal' && !empty($r->type)){
                
                $rvwamount = $customeracct->account_balance + $r->amount;
                $customeracct->account_balance = $rvwamount;
                $customeracct->save();

                $this->create_saving_transaction(Auth::user()->id,$r->customerid,$branch,$r->amount,
                                                 $r->type,'core','0',$r->slipno,null,null,null,$trxref,str_replace("'", "",$r->description),'approved','4','trnsfer',$usern);
          
            
           if(!is_null($cust->exchangerate_id)){
                 $this->checkforeigncurrncy($cust->exchangerate_id,$r->amount,$trxref,'credit');
            }else{
                  // if($cust->account_type == '1'){//saving acct GL
            
                        if($glsavingdacct->status == '1'){
                            $this->gltransaction('withdrawal',$glsavingdacct,$r->amount,null);
                             $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $r->amount,'credit','core',$trxref,$this->generatetrnxref('D'),'customer credited','approved',$usern);
                        }
                        
            // }elseif($cust->account_type == '2'){//current acct GL
            
            //     if($glcurrentacct->status == '1'){
            //     $this->gltransaction('withdrawal',$glcurrentacct,$r->amount,null);
            // $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $r->amount,'credit','core',$trxref,$this->generatetrnxref('D'),'customer credited','approved',$usern);
            //     }
            // }
                 }

                 if($glacct->status == '1'){
                    $this->gltransaction('withdrawal',$glacct,$r->amount,$branch);
                
                    $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$r->amount,'debit','core',$trxref,$this->generatetrnxref('ctp'),str_replace("'", "",$r->description),'approved',$usern);
                }
                 
                 $this->tracktrails(Auth::user()->id,$branch,$usern,'reversal','withdrawal reversal was carried out from an account');

            DB::commit();

            if($cust->enable_email_alert == '1'){
            $msg =  "Credit Amt: N".number_format($r->amount,2)."<br> Desc: ".str_replace("'", "",$r->description)." <br>Avail Bal: N". number_format($customeracct->account_balance,2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trxref;
            Email::create([
                'user_id' => $cust->id,
                'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
                'message' => $msg,
                'recipient' => $cust->email,
            ]);
   
            Mail::send(['html' => 'mails.sendmail'],[
                'msg' => $msg,
                'type' => 'Credit Transaction'
            ],function($mail)use($getsetvalue,$cust){
                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                 $mail->to($cust->email);
                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
            });
            }
            return array(
                'status' => 'success',
                'msg' => 'Withdrawal Reversed Successfully'
            );
               }else{
                return array(
                    'status' => '0',
                    'msg' => 'Sorry these is a withdrawal transaction'
                );
               }
            }
              }
            

         }elseif($r->tran_type == 'charge posting'){
            $this->logInfo("charge via core banking",$r->all());
              
        $custcg = Customer::where('id',$r->crgcustomerid)->first();             

              $chkcres = $this->checkCustomerRestriction($r->crgcustomerid);
            if($chkcres == true){
                
                 $this->tracktrails(Auth::user()->id,$branch,$usern,'customer','Account Restricted');
                 
                return array('status' => '0','msg' => 'Customer account restricted');
            }
            
             $chklien = $this->checkCustomerLienStatus($r->crgcustomerid);
             if($chklien['status'] == true && $chklien['lien'] == 2){
                 $this->tracktrails(Auth::user()->id,$branch,$usern,'customer','Account Lien');
                 
                return array('status' => '0','msg' => 'Customer account has been lien('.$chklien["message"].')...please contact support');
            
             }
             
             $validateuserbalance = $this->validatecustomerbalance($r->crgcustomerid,$r->amount);
                if($validateuserbalance["status"] == false){
                    $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
                    return ['status' => '0', 'msg' => $validateuserbalance["message"]];
                }
                
            $customeracctcrg = Saving::lockForUpdate()->where('customer_id',$r->crgcustomerid)->first();
                
                $chramount = $customeracctcrg->account_balance - $r->amount;
                $customeracctcrg->account_balance = $chramount;
                $customeracctcrg->save();
    
                    
                $this->create_saving_transaction(Auth::user()->id,$r->crgcustomerid,$branch,$r->amount,
                                                 $r->charge_type,'core','0',null,null,null,null,$trxref,str_replace("'", "",$r->description),'approved','6','trnsfer',$usern);
                 
           
            $this->tracktrails(Auth::user()->id,$branch,$usern,'charges','charges withdrawn from an account');
            
            if($glchrgacct->status == '1'){
            $this->gltransaction('withdrawal',$glchrgacct,$r->amount,$branch);
            
            $this->create_saving_transaction_gl(Auth::user()->id,$glchrgacct->id,$branch,$r->amount,'credit','core',$trxref,$this->generatetrnxref('chrg'),'charges from an account','approved',$usern);
                }
                
                
                if(!is_null($cust->exchangerate_id)){
                 $this->checkforeigncurrncy($cust->exchangerate_id,$r->amount,$trxref,'debit');
            }else{
                //if($custcg->account_type == '1'){//saving acct GL
                
                        if($glsavingdacct->status == '1'){
                            $this->gltransaction('deposit',$glsavingdacct,$r->amount,null);
                            $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $r->amount,'debit','core',$trxref,$this->generatetrnxref('chrg'),'customer charge debited','approved',$usern);
                        }
                        
            // }elseif($custcg->account_type == '2'){//current acct GL
                
            //     if($glcurrentacct->status == '1'){
            //     $this->gltransaction('deposit',$glcurrentacct,$r->amount,null);
            // $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $r->amount,'debit','core',$trxref,$this->generatetrnxref('chrg'),'customer charge debited','approved',$usern);
            //     }
                
            // }
            }

            DB::commit();

            if($custcg->enable_email_alert == '1'){
             $msg =  "Debit Amt: N".number_format($r->amount,2)."<br> Desc: ".str_replace("'", "",$r->description)." <br>Avail Bal: N". number_format($customeracctcrg->account_balance,2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trxref;
            Email::create([
                'user_id' => $custcg->id,
                'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert',
                'message' => $msg,
                'recipient' => $custcg->email,
            ]);
   
            Mail::send(['html' => 'mails.sendmail'],[
                'msg' => $msg,
                'type' => 'Debit Transaction'
            ],function($mail)use($getsetvalue,$custcg){
                $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                 $mail->to($custcg->email);
                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
            });
            }
            return array(
                'status' => 'success',
                'msg' => 'Account Charged Successfully'
            );
           
         
         }
        
        // $lock->release();

              
    } catch (\Throwable $e) {
        DB::rollBack();
       return array('status' => '0','msg' => 'Error processing transaction');
    } finally {
        $lock->release();
    }
        }//lock
    }


    //upload transaction
    public function upload_transactions(){
        return view('deposit.upload_transaction');
    }

    public function store_upload_transactions(Request $r){
        $this->validate($r,[
            'file_upload' => ['required','mimes:csv','max:10240']
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $csvfile = $r->file('file_upload');
        $newcsvfile = time()."_".$csvfile->getClientOriginalName();
        $csvfile->move('uploads',$newcsvfile);

        $csvfilepath = $_SERVER["DOCUMENT_ROOT"]."/uploads/".$newcsvfile;
       $this->upload_transactions_via_excel($csvfilepath);
       
        unlink($csvfilepath); //remove uploaded file

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'transaction upload','uploaded transactions');
        
        $urllink = route('viewuploadstatus')."?uploadstatus=current";
        return redirect($urllink)->with('success','Transactions Uploaded');  

    }

    public function upload_transactions_via_excel($filepath){
        $errorup = array();
        $successup = array();
             $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $handlefile = fopen($filepath, "r");
        fgetcsv($handlefile);//skip first line
        while(($data = fgetcsv($handlefile, '1000000',',')) != FALSE){
            $acctno = $data[0];
            $amount = $data[1];
            $trxtype = $data[2];
            $slipno = $data[3]; 
        
            $trxref = $this->generatetrnxref("up");
            
            $usern = Auth::user()->last_name." ".Auth::user()->first_name;

            $getsetvalue = new Setting();

        $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('till_account'))->lockForUpdate()->first();
        
         $glchrgacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$getsetvalue->getsettingskey('glcharges'))->lockForUpdate()->first();
         
          $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->lockForUpdate()->first();
         $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->lockForUpdate()->first();
              
            $customerac = Customer::select('id','first_name','last_name','acctno','email','account_type','enable_email_alert','enable_sms_alert')->where('acctno',$acctno)->first();
         
                 $convrtamt = 0;
                 
            if(!empty($customerac)){
                $checkslipno = $this->check_transaction_slip($slipno);
               if(!$checkslipno){

                $customeracct = Saving::lockForUpdate()->where('customer_id',$customerac->id)->first();//get customer account balance via customer id
                
                   $chkcres = $this->checkCustomerRestriction($customerac->id);
                    if($chkcres == false){
                        
                       $chklien = $this->checkCustomerLienStatus($customerac->id);
                         if($chklien['status'] == false){
            
                  if(strtolower(str_replace(" ","_",$trxtype)) == "deposit"){
                      
                      if($getsetvalue->getsettingskey('deposit_limit') == 0 || $amount < $getsetvalue->getsettingskey('deposit_limit')){

                     
                           $damount = $customeracct->account_balance + $amount;
                            $customeracct->account_balance = $damount;
                             $customeracct->save();
                             
                     $this->checkOutstandingCustomerLoan($customerac->id,$amount);//check if customer has an outstanding loan
                    
                    $this->create_saving_transaction(Auth::user()->id,$customerac->id,$branch,$amount,
                                                     $trxtype,'core','0',$slipno,null,null,null,$trxref,null,'approved','1','trnsfer',$usern);

                $this->upload_trx_status($branch,$customerac->id,null,$customeracct->account_balance,$amount,$trxtype,null,Carbon::now(),null,'1','1');

                    
                    $this->tracktrails(Auth::user()->id,$branch,$usern,'deposit','deposited to an account');
                    
                    if($glacct->status == '1'){
                    $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$amount,'debit','core',$trxref,$this->generatetrnxref('tcp'),null,'approved',$usern);

                    $this->gltransaction('withdrawal',$glacct,$amount,$branch);
                    }
                    
                    if(!is_null($customerac->exchangerate_id)){
                        $this->checkforeigncurrncy($customerac->exchangerate_id,$amount,$trxref,'credit');
                     }else{
                           //if($customerac->account_type == '1'){//saving acct GL
                       
                        if($glsavingdacct->status == '1'){
                                    $this->gltransaction('withdrawal',$glsavingdacct,$amount,null);
                                $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $amount,'credit','core',$trxref,$this->generatetrnxref('D'),'customer credited','approved',$usern);
                        }
                        
            // }elseif($customerac->account_type == '2'){//current acct GL
            
            //     if($glcurrentacct->status == '1'){
            //     $this->gltransaction('withdrawal',$glcurrentacct,$amount,null);
            // $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $amount,'credit','core',$trxref,$this->generatetrnxref('D'),'customer credited','approved',$usern);
            //     }
                
            // }
                 }
                 
               
                    
                if($customerac->enable_email_alert == '1'){
                    $msg =  "Credit Amt: N".number_format($amount,2)."<br> Desc: Transfer <br>Avail Bal: N". number_format($customeracct->account_balance,2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trxref;
                    Email::create([
                        'user_id' => $customerac->id,
                        'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
                        'message' => $msg,
                        'recipient' => $customerac->email,
                    ]);
           
                    Mail::send(['html' => 'mails.sendmail'],[
                        'msg' => $msg,
                        'type' => 'Credit Transaction'
                    ],function($mail)use($getsetvalue,$customerac){
                        $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                         $mail->to($customerac->email);
                        $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
                    });
                    }
                  }else{
                      $this->create_saving_transaction(Auth::user()->id,$customerac->id,$branch,$amount,
                                                     $trxtype,'core','0',$slipno,'0',null,null,$trxref,null,'pending','1','trnsfer',$usern);
                                                     
                         $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$amount,'debit','core',$trxref,$this->generatetrnxref('tcp'),null,'pending',$usern);

                  }
                  
                  }elseif(strtolower(str_replace(" ","_",$trxtype)) == "withdrawal"){
                      
                    if($customeracct->account_balance >= $amount){
                        
                        if($getsetvalue->getsettingskey('withdrawal_limit') == 0 || $amount < $getsetvalue->getsettingskey('withdrawal_limit')){
                            
                        $wamount = $customeracct->account_balance - $amount;
                        $customeracct->account_balance = $wamount;
                    $customeracct->save();
                    
                    $this->create_saving_transaction(Auth::user()->id,$customerac->id,$branch,$amount,
                                                     $trxtype,'core','0',$slipno,null,null,null,$trxref,null,'approved','2','trnsfer',$usern);

                    
                    $this->tracktrails(Auth::user()->id,$branch,$usern,'withdrawal','withdraw from an account');
                    
                     $this->upload_trx_status($branch,$customerac->id,null,$customeracct->account_balance,$amount,$trxtype,null,Carbon::now(),null,'1','1');

                if($glacct->status == '1'){
                     $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref('ctp'),null,'approved',$usern);

                     $this->gltransaction('deposit',$glacct,$amount,$branch);
                    }
                    
                   if(!is_null($customerac->exchangerate_id)){
                        $this->checkforeigncurrncy($customerac->exchangerate_id,$amount,$trxref,'debit');
                     }else{
                  // if($customerac->account_type == '1'){//saving acct GL
                        
                        if($glsavingdacct->status == '1'){
                            $this->gltransaction('deposit',$glsavingdacct,$amount,null);
                        $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $amount,'debit','core',$trxref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                        }
                        
                        // }elseif($customerac->account_type == '2'){//current acct GL
                        
                        //     if($glcurrentacct->status == '1'){
                        //     $this->gltransaction('deposit',$glcurrentacct,$amount,null);
                        // $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $amount,'debit','core',$trxref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                        //     }
                            
                        // }
                 }
                    
                        
            
            if($customerac->enable_email_alert == '1'){
                     $msg =  "Debit Amt: N".number_format($amount,2)."<br> Desc: withdrawal trasnsaction <br>Avail Bal: N". number_format($customeracct->account_balance,2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trxref;
                     Email::create([
                         'user_id' => $customerac->id,
                         'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert',
                         'message' => $msg,
                         'recipient' => $customerac->email,
                     ]);
            
                     Mail::send(['html' => 'mails.sendmail'],[
                         'msg' => $msg,
                         'type' => 'Debit Transaction'
                     ],function($mail)use($getsetvalue,$customerac){
                         $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                          $mail->to($customerac->email);
                         $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
                     });
            }
                        }else{
                            $this->create_saving_transaction(Auth::user()->id,$customerac->id,$branch,$amount,
                                                     $trxtype,'core','0',$slipno,'0',null,null,$trxref,null,'pending','2','trnsfer',$usern);
                                                     
                          $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref('ctp'),null,'pending',$usern);

                        }
                    }else{
                       $this->upload_trx_status($branch,$customerac->id,null,$customeracct->account_balance,$amount,$trxtype,null,Carbon::now(),'insufficent fund','0','1');
                    }
                    
                    
                  }elseif(strtolower(str_replace(" ","_",$trxtype)) == 'esusu' || strtolower(str_replace(" ","_",$trxtype)) == 'monthly_charge' || 
                  strtolower(str_replace(" ","_",$trxtype)) == 'transfer_charge' || strtolower(str_replace(" ","_",$trxtype)) == 'form_fees' || strtolower(str_replace(" ","_",$trxtype)) == 'process_fees'){
                  
                    if($customeracct->account_balance >= $amount){
                        
                        $cghamount = $customeracct->account_balance - $amount;
                        $customeracct->account_balance = $cghamount;
                        $customeracct->save();
                        
                        $this->create_saving_transaction(Auth::user()->id,$customerac->id,$branch,$amount,
                                        strtolower(str_replace(" ","_",$trxtype)),'core','0',null,null,null,null,$trxref,null,'approved','6','trnsfer',$usern);
    
                       
                        $this->tracktrails(Auth::user()->id,$branch,$usern,'charges','charges withdrawn from an account');
                        if($glacct->status == '1'){
                        $this->create_saving_transaction_gl(Auth::user()->id,$glchrgacct->id,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref('chrg'),'charges from an account','approved',$usern);

                        $this->gltransaction('withdrawal',$glchrgacct,$amount,$branch);
                        }
                        
                         if(!is_null($customerac->exchangerate_id)){
                        $this->checkforeigncurrncy($customerac->exchangerate_id,$amount,$trxref,'debit');
                     }else{
                    //if($customerac->account_type == '1'){//saving acct GL
                    
                        if($glsavingdacct->status == '1'){
                                $this->gltransaction('deposit',$glsavingdacct,$amount,null);
                            $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $amount,'debit','core',$trxref,$this->generatetrnxref('chrg'),'customer charges','approved',$usern);
                        }
                        
            // }elseif($customerac->account_type == '2'){//current acct GL
            
            //     if($glcurrentacct->status == '1'){
            //     $this->gltransaction('deposit',$glcurrentacct,$amount,null);
            // $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $amount,'debit','core',$trxref,$this->generatetrnxref('chrg'),'customer charges','approved',$usern);
            //     }
                
            // }
                    }
            
            if($customerac->enable_email_alert == '1'){
                    $msg =  "Debit Amt: N".number_format($amount,2)."<br> Desc: debit transaction for charges <br>Avail Bal: N". number_format($customeracct->account_balance,2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trxref;
                     Email::create([
                         'user_id' => $customerac->id,
                         'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert',
                         'message' => $msg,
                         'recipient' => $customerac->email,
                     ]);
            
                     Mail::send(['html' => 'mails.sendmail'],[
                         'msg' => $msg,
                         'type' => 'Debit Transaction'
                     ],function($mail)use($getsetvalue,$customerac){
                         $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
                          $mail->to($customerac->email);
                         $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
                     });
            }
                       $this->upload_trx_status($branch,$customerac->id,null,$customeracct->account_balance,$amount,$trxtype,null,Carbon::now(),null,'1','1');
                    }else{
                       $this->upload_trx_status($branch,$customerac->id,null,$customeracct->account_balance,$amount,$trxtype,null,Carbon::now(),'insufficent fund','0','1');
                    }
                     
        
                  }
                  
                     }else{
                   $this->upload_trx_status($branch,$customerac->id,null,$customeracct->account_balance,$amount,$trxtype,null,Carbon::now(),'account has been lien','0','1');
                     }
                  }else{
                   $this->upload_trx_status($branch,$customerac->id,null,$customeracct->account_balance,$amount,$trxtype,null,Carbon::now(),'Customer Account Restricted','0','1');
                }
                  
               }else{
                return false;
               }
            }else{
                return false;
               }
               
        }
        
        //Close opened CSV file
        fclose($handlefile);
        
                     
    }

    public function print_statement($id)
    {
        $balance = 0;
        $getsave = Saving::select('account_balance','savings_product_id')
                          ->where('customer_id',$id)->first();
       
       $getproname = SavingsProduct::where('id',$getsave->savings_product_id)->first();

        $transac = SavingsTransaction::where('customer_id',$id)
                                     ->whereBetween('created_at',[request()->fromdate,request()->todate])
                                     ->orderBy('created_at','ASC')->get();
        
        $savtrns = SavingsTransaction::where('customer_id',$id)->whereDate('created_at','<',request()->fromdate)->orderBy('created_at','ASC')->get();
        
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
      
        return view('deposit.print')->with('customer',Customer::findorfail($id))
                                    ->with('getsave',$getsave)
                                    ->with('getproname',$getproname)
                                     ->with('transactions',$transac)
                                     ->with('custid',$balance);
    }

    public function pdf_statement($id)
    {
        $getsetvalue = new Setting();
        $getsave = Saving::select('account_balance','savings_product_id')
                                  ->where('customer_id',$id)->first();

 $customer = Customer::findorfail($id);

$getproname = SavingsProduct::where('id',$getsave->savings_product_id)->first();

$transac = SavingsTransaction::where('customer_id',$id)
                            ->whereBetween('created_at',[request()->fromdate,request()->todate])
                            ->orderBy('created_at','ASC')->get();
  
   $balance = 0;                          
 $savtrns = SavingsTransaction::where('customer_id',$id)->whereDate('created_at','<',request()->fromdate)->orderBy('created_at','ASC')->get();
        
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
        
        $data = [
            'title' => $getsetvalue->getsettingskey('company_name')." Savings Statement",
            'date' => date('m/d/Y'),
            'customer' => $customer,
            'getsave' => $getsave,
            'getproname' => $getproname,
            'transactions' => $transac,
            'custid' => $balance
        ];
        
        $pdf = PDF::loadView("deposit.pdf_statement", $data);
        return $pdf->download(ucfirst($customer->title)." ".$customer->first_name." ".$customer->last_name." - Statement.pdf");
    }
    

public function uploadtrx_status(){
    if(request()->uploadstatus == "current"){
        return view('deposit.uploadtrxstatus')->with('uploadstatus',Upload_transaction_status::where('upload_status',1)->get());
    }elseif(request()->uploadfilter == true){
        return view('deposit.uploadtrxstatus')->with('uploadstatus',Upload_transaction_status::whereBetween('created_at',[request()->datefrom,request()->dateto])->orderBy('created_at','DESC')->get());
    }elseif(request()->type == "cgl"){
        Upload_transaction_status::where('gl_type',request()->type)->update([
            'upload_status' => '0'
        ]);
        return view('deposit.uploadtrxstatus');   
    }elseif(request()->type == "glc"){
        Upload_transaction_status::where('gl_type',request()->type)->update([
            'upload_status' => '0'
        ]);
        return view('deposit.uploadtrxstatus'); 
    }elseif(request()->type == "gltogl"){
        Upload_transaction_status::where('gl_type',request()->type)->update([
            'upload_status' => '0'
        ]);
        return view('deposit.uploadtrxstatus'); 
    }else{
        return view('deposit.uploadtrxstatus')->with('uploadstatus',Upload_transaction_status::orderBy('created_at','DESC')->get());   
    }
    }
    
    public function changeuploadstaus(Request $r){
        foreach($r->uplid as $id){
            Upload_transaction_status::where('id',$id)->update([
                'upload_status' => '0'
            ]);
        }
        return redirect()->back();
    }
    
      public function overdraft(){
        return view('deposit.overdraft');
    }
    
    public function overdraft_transactions(Request $r){

       $lock= Cache::lock('ovrrtrnx-'.mt_rand('1111','9999'),2);
       
       try{
         $lock->block(1);
   
     //  if($lock->get()){
        
        DB::beginTransaction();

        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $this->logInfo("customer overdraft posting",$r->all());
             $trxref2 = $this->generatetrnxref('cgl');

            $this->validate($r,[
                    'account_number' => ['required','string','numeric'],
                    'gl_code2' => ['required','string','numeric'],
                    'amount' => ['required','string','numeric','gt:0'],
                ]);

        if (preg_match('/[\'^£$%&*}{@#~?><>()"|=_+¬]/', $r->description)) {
                return ['status' => '0', 'msg' => "No special character allowed in narration"];
            }
            
                $getsetvalue = new Setting();

         //$glcurrentacct = GeneralLedgerFI::select('id','status','account_balance')->where('gl_code','20639526')->where('branch_id',$branch)->first();
           
                $usern = Auth::user()->last_name." ".Auth::user()->first_name;

                $cust = Customer::where('id',$r->customerid)->first();
                
                $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->lockForUpdate()->first();
         $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->lockForUpdate()->first();
         $glcurryacct = GeneralLedger::select('id','status','account_balance','currency_id')->where('gl_code',$r->gl_code2)->lockForUpdate()->first();
           

            if($glcurryacct->currency_id != $cust->exchangerate_id){

                return ['status' => '0', 'msg' => 'Currency mis-match'];

                }

                $chkcres = $this->checkCustomerRestriction($r->customerid,$branch);
                    if($chkcres == true){
                        $this->tracktrails('1','1',$usern,'customer','Account Restricted',null);
                    return array('status' => '0','msg' => 'Customer Account Has Been Restricted');
                    }
                    
                 $chklien = $this->checkCustomerLienStatus($r->customerid,$branch);
                    if($chklien['status'] == true && $chklien['lien'] == 2){
                        $this->tracktrails('1','1',$usern,'customer','Account has been lien',null);
                     return  array('status' => '0','msg' => 'Customer Account Has Been Lien('.$chklien['message'].')...please contact support');
                    }

                    
                $customeracct = Saving::lockForUpdate()->where('customer_id',$r->customerid)->first();
       
                  if($r->dbit == 'debit'){
                      $trxref = date("ymd")."".mt_rand(111,999);
                      
                      if($getsetvalue->getsettingskey('withdrawal_limit') == 0 || $r->amount < $getsetvalue->getsettingskey('withdrawal_limit')){

                         $bal = $customeracct->account_balance;
                        $overdftbal = $r->amount > $customeracct->account_balance ? $r->amount - $customeracct->account_balance : $r->amount;

                        $dedamount = $customeracct->account_balance - $r->amount;
                      $customeracct->account_balance = $dedamount;
                      $customeracct->save();
          
                      $this->create_saving_transaction(Auth::user()->id,$r->customerid,$branch,$r->amount,
                      $r->dbit,'core','0',null,null,'ovd',null,$trxref2,str_replace("'","",$r->description),'approved','2','trnsfer',$usern);
                        
                 if($bal > 0){

                      if(!is_null($cust->exchangerate_id)){
                        $this->checkforeigncurrncy($cust->exchangerate_id,$bal,$trxref2,'debit',$branch);
                      }else{
                        
                            //if($cust->account_type == '1'){//saving acct GL
                            
                                if($glsavingdacct->status == '1'){
                                $this->gltransaction('deposit',$glsavingdacct,$r->amount,null);
                            $this->create_saving_transaction_gl(Auth::user()->id,$glsavingdacct->id,$branch,$bal,'debit','core',$trxref2,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                            }
                            
                            // }elseif($cust->account_type == '2'){//current acct GL
                            
                            //     if($glcurrentacct->status == '1'){
                            //         $this->gltransaction('deposit',$glcurrentacct,$r->amount,null);
                            // $this->create_saving_transaction_gl(Auth::user()->id,$glcurrentacct->id,$branch, $r->amount,'debit','core',$trxref2,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                                
                            //     }
                            
                            // }
                      }

                    }

                     
                  $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debit from an account');
          
                     $this->credit_gl_account_transfer($r->options,$r->gl_code2,$overdftbal,$branch,$r->gldger_id,$r->dbit2,'ovd',$trxref2,str_replace("'","",$r->description),'');
                  
           
                     DB::commit();

                     return array(
                        'status' => 'success',
                        'msg' => 'Overdraft Posted Successful'
                    );
                    
                }else{

                    $this->create_saving_transaction(Auth::user()->id,$r->customerid,$branch,$r->amount,
                      $r->dbit,'core','0',null,'0','ovd',null,$trxref2,str_replace("'", "",$r->description),'pending','2','trnsfer',$usern);

                      $this->credit_gl_account_transfer($r->options,$r->gl_code2,$r->amount,$branch,$r->gldger_id,$r->dbit2,'ovd',$trxref2,str_replace("'","",$r->description),'pending');
                    
                      DB::commit();
                     return array(
                           'status' => 'success',
                           'msg' => "Overdraft Posted...Awaiting Approval"
                            );
                }

                    }
                
                    

        //         $lock->release();    
        //  }//lock
         }catch (\Exception $e){

           DB::rollBack();

            return ['status' => '0', 'msg' => 'Error processing overdraft'];
       }
        
    }
    
     //credit general ledger
     public function credit_gl_account_transfer($opt,$glcode,$amount,$branch,$glid,$dbit2,$inita,$trxref,$desc,$status){
            
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;

        $subcod = substr($glcode,0,2);// get first two digits of gl code
        //$checkacctyp = AccountType::where('code',$subcod)->first();
        $glacct2 = GeneralLedger::where('id',$glid)->lockForUpdate()->first();
        
        if($opt == "cgl"){
            
         if($glacct2->gl_type == "asset"){
            
            if($status == "pending"){
                $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'pending',$usern);
            }else{
                
                 $astamount = $glacct2->account_balance - $amount;
                 $glacct2->account_balance = $astamount;
               $glacct2->save();
               
            $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
    
            
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an asset account',null);
            
            }
            
        }elseif($glacct2->gl_type == "liability"){
            if($status == "pending"){
                $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'pending',$usern);
            }else{
            
                 $liamount = $glacct2->account_balance + $amount;
                 $glacct2->account_balance = $liamount;
               $glacct2->save();
            

            $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
    
           
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to a liability account',null);
            
        }
         }elseif($glacct2->gl_type == "capital"){
            if($status == "pending"){
                $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'pending',$usern);
            }else{
            
                 $cpamount = $glacct2->account_balance + $amount;
                 $glacct2->account_balance = $cpamount;
               $glacct2->save();
            

            $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
    
          
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to a capital account',null);
        }
         }elseif($glacct2->gl_type == "income"){
            if($status == "pending"){
                $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'pending',$usern);
            }else{
            
                 $inamount = $glacct2->account_balance + $amount;
                 $glacct2->account_balance = $inamount;
               $glacct2->save();
            

            $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
    
           
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an income account',null);
             }
         }elseif($glacct2->gl_type == "expense"){
        
            if($status == "pending"){
                $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'pending',$usern);
            }else{
             $eamount = $glacct2->account_balance - $amount;
             $glacct2->account_balance = $eamount;
               $glacct2->save();
               
            $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
    
           
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an expense account',null);
            
         }
        }
        }
       
    }

  
    
}//end class
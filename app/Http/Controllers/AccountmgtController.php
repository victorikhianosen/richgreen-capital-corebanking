<?php

namespace App\Http\Controllers;

use session;
use Carbon\Carbon;
use App\Models\Email;
use App\Models\Saving;
use App\Models\Capital;
use App\Models\Setting;
use App\Models\Customer;
use App\Models\AccountType;
use App\Models\Exchangerate;
use Illuminate\Http\Request;
use App\Models\GeneralLedger;
use App\Models\AccountCategory;
use App\Models\OutstandingLoan;
use App\Http\Traites\UserTraite;
use App\Http\Traites\AuditTraite;
use App\Http\Traites\SavingTraite;
use App\Models\SavingsTransaction;
use Illuminate\Support\Facades\DB;
use App\Models\SavingsTransactionGL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class AccountmgtController extends Controller
{
    use SavingTraite;
    use AuditTraite;
    use UserTraite;
    public function __construct()
    {
       $this->middleware('auth'); 
    }

    //account type
    public function account_type()
    {
        return view('accountmgt.manage_accounttype')->with('actyps',AccountType::all());
    }

 public function manage_gl_trans()
    {
        if(request()->filter == true){
            $savtransgl = SavingsTransactionGL::whereBetween('created_at',[request()->datefrom,request()->dateto])
                                    ->orderBy('id','DESC')->take(100)->get();
        }else{

            $savtransgl = SavingsTransactionGL::orderBy('id','DESC')->take(100)->get();
        }

        return view('accountmgt.manage_gl_transaction')->with('data',$savtransgl);
    }
    
    public function update_accountcode(Request $r, $id)
    {
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        $upacd = AccountType::findorfail($id);
        $upacd->code = $r->ac_code;
        $upacd->save();

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'account type','updated account type code');

        return redirect()->route('actype')->with('success','account code updated');
    }

    //account_category
    public function account_category_index()
    {
        return view('accountmgt.category.index')->with('accates',AccountCategory::orderBy('id','DESC')->get());
    }

    public function batch_upload()
    {
        return view('accountmgt.batch_upload');
    }

    public function account_category_create()
    {
        return view('accountmgt.category.create')->with('actyps',AccountType::all());
    }

    public function account_category_store(Request $r)
    {  
         $this->logInfo("store category",$r->all());
        $this->validate($r,[
            'name' => ['required','string'],
            'account_type' => ['required','string'],
            'description' => ['required','string'],
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        AccountCategory::create([
            'name' => strtolower($r->name),
            'type' => strtolower($r->account_type),
            'description' => str_replace("'", "",$r->description)
        ]);
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'account category','created account category');

        return redirect()->route('ac.category.index')->with('success','Account Category Created');
    }

    public function account_category_edit($id)
    {
        return view('accountmgt.category.edit')->with('ed',AccountCategory::findorfail($id))
                                                ->with('actyps',AccountType::all());
    }

    public function account_category_update(Request $r, $id)
    {
         $this->logInfo("category update",$r->all());
        $this->validate($r,[
            'name' => ['required','string'],
            'account_type' => ['required','string'],
            'description' => ['required','string'],
        ]);

        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
        $upacate = AccountCategory::findorfail($id);

        $upacate->update([
            'name' => strtolower($r->name),
            'type' => strtolower($r->account_type),
            'description' => str_replace("'", "",$r->description)
        ]);

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'account category','updated account category');

        return redirect()->route('ac.category.index')->with('success','Account Category Updated');
    }

   public function account_category_delete($id)
    {
        AccountCategory::findorfail($id)->delete();
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'account category','deleted an account category');

        return [ "status" => "success", "msg" => "Account Category Deleted"];
    }

    public function multiple_account_category_delete(Request $r)
    {    
        foreach($r->accateid as $accateid){
             AccountCategory::where('id',$accateid)->delete();   
        }
        
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'account category','multiple deleted an account category');
        
        return [ "status" => "success", "msg" => "Account Category Deleted"];
    }


     //capitals
     public function capital_index()
     {
         return view('accountmgt.capital.index')->with('capitals',Capital::orderBy('id','DESC')->get());
     }
 
     public function capital_create()
     {
         return view('accountmgt.capital.create');
     }
 
     public function capital_store(Request $r)
     {
          $this->logInfo("store capital",$r->all());
         $this->validate($r,[
             'share_holder_name' => ['required','string'],
             'percentage' => ['required','string'],
         ]);
         $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
 
        Capital::create([
             'share_holder_name' => strtolower($r->share_holder_name),
             'user_id' => Auth::user()->id,
             'branch_id' => $branch,
             'percentage' => $r->percentage,
             'notes' => str_replace("'", "",$r->description)
         ]);
         $usern = Auth::user()->last_name." ".Auth::user()->first_name;
         $this->tracktrails(Auth::user()->id,$branch,$usern,'account category','created capital');
 
         return redirect()->route('capital.index')->with('success','Capital Created');
     }
 
     public function capital_edit($id)
     {
         return view('accountmgt.capital.edit')->with('ed',Capital::findorfail($id));
     }
 
     public function capital_update(Request $r, $id)
     {
          $this->logInfo("upate capital",$r->all());
          
         $this->validate($r,[
            'share_holder_name' => ['required','string'],
            'percentage' => ['required','string'],
         ]);
 
         $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
         $upacate = Capital::findorfail($id);
 
         $upacate->update([
            'share_holder_name' => strtolower($r->share_holder_name),
            'user_id' => Auth::user()->id,
            'branch_id' => $branch,
            'percentage' => $r->percentage,
            'notes' => str_replace("'", "",$r->description)
         ]);
 
         $usern = Auth::user()->last_name." ".Auth::user()->first_name;
         $this->tracktrails(Auth::user()->id,$branch,$usern,'capital','updated capital');
 
         return redirect()->route('capital.index')->with('success','Capital Updated');
     }
 
     public function capital_delete($id)
     {
         Capital::findorfail($id)->delete();
         $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
 
         $usern = Auth::user()->last_name." ".Auth::user()->first_name;
         $this->tracktrails(Auth::user()->id,$branch,$usern,'capital','deleted capital');
         
         return redirect()->route('capital.index')->with('success','Capital Deleted');
     }

     public function fund_gl_accounts(){
        return view('accountmgt.fund_gl_transaction')->with('actyps',AccountType::all());
    }

     //general ledger transactions
     public function gl_customer_posting(){
        return view('accountmgt.gl_customer_posting');
    }
    
     //general ledger
     public function gl_index()
     {
         return view('accountmgt.gl.index')->with('gls',GeneralLedger::orderBy('gl_name','ASC')->get());
     }
 
     public function gl_create()
     {
         return view('accountmgt.gl.create')->with('actyps',AccountType::all())
                                            ->with('exrate',Exchangerate::all())
                                             ->with('accates',AccountCategory::orderBy('id','ASC')->get());
     }
 

     public function gl_store(Request $r)
     {
         $lock = Cache::lock('glaccst-'.mt_rand('1111','9999'),3);
              
    if($lock->get()){
          $this->logInfo("storing general ledger",$r->all());
          
         $this->validate($r,[
             'name' => ['required','string'],
             'account_type' => ['required','string'],
         ]);

         $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
 
         $cod = $r->code."".mt_rand('111111','999999');
        GeneralLedger::create([
            'account_category_id' => $r->account_category,
             'user_id' => Auth::user()->id,
             'branch_id' => $branch,
             'gl_name' => strtolower($r->name),
             'gl_code' => $cod,
             'gl_type' => $r->account_type,
             'currency_id' => $r->currency_type,
             'status' => '1',
             'branch_id' => $branch
         ]);
         $usern = Auth::user()->last_name." ".Auth::user()->first_name;
         $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','created general ledger');
 
         return redirect()->route('gl.index')->with('success','General Ledger Created');
         
         $lock->release();
     }//lock
     
     }
 

     public function gl_edit($id)
     {
         return view('accountmgt.gl.edit')->with('ed',GeneralLedger::findorfail($id))
                                            ->with('exrate',Exchangerate::all())
                                        ->with('accates',AccountCategory::orderBy('id','ASC')->get());
     }
 

     public function gl_update(Request $r, $id)
     {
       $lock = Cache::lock('glaccupt-'.mt_rand('1111','9999'),3);
         if($lock->get()){
             
        $this->logInfo("update general ledger",$r->all());

         $this->validate($r,[
            'name' => ['required','string'],
         ]);
 
         $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
         $upacate = GeneralLedger::findorfail($id);
 
         $upacate->update([
            'gl_name' => strtolower($r->name),
             'account_category_id' => $r->account_category,
             'currency_id' => $r->currency_type,
             'branch_id' => $branch
         ]);
 
         $usern = Auth::user()->last_name." ".Auth::user()->first_name;
         $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','updated general ledger');
 
         return redirect()->route('gl.index')->with('success','General Ledger Updated');
         
         $lock->release();
         }
     }
 

     public function gl_delete($id)
     {
        GeneralLedger::findorfail($id)->delete();
         $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
 
         $usern = Auth::user()->last_name." ".Auth::user()->first_name;
         $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deleted general ledger');
         
         return redirect()->route('gl.index')->with('success','General Ledger Deleted');
     }


        public function change_gl_status($glid, $status){
             $lock = Cache::lock('chgglacccstus-'.mt_rand('1111','9999'),3);
             if($lock->get()){
                 
            $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

            $upstatu = GeneralLedger::findorfail($glid);
            $upstatu->status = $status;
            $upstatu->save();

            $usern = Auth::user()->last_name." ".Auth::user()->first_name;
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deleted general ledger');

            return redirect()->back()->with('success','General Ledger Status Changed');
            
            $lock->release();
          }
        }

   //activate general ledger account
     public function activate_deactive_glaccount(Request $r){
         
         $lock= Cache::lock('glaccchesta-'.mt_rand('1111','9999'),3);
          if($lock->get()){
              
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        if(!empty($r->glid)){
          if($r->cmdupdatestatus == "Activate Account(s)"){
              foreach($r->glid as $gid){
                GeneralLedger::where('id',$gid)->update([
                      'status' => "1"
                  ]);
              }
          $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
  
             
              $this->tracktrails(Auth::user()->id,$branch,$usern,'customer','activated General ledger account');
         
              return redirect()->back()->with('success','Account Activated');
  
          }elseif($r->cmdupdatestatus == "Deactivate Account(s)"){
              foreach($r->glid as $gid){
                GeneralLedger::where('id',$gid)->update([
                      'status' => "0"
                  ]);
              }
            $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
  
          
          $this->tracktrails(Auth::user()->id,$branch,$usern,'customer','deactivated General ledger account');
  
          return redirect()->back()->with('success','Account Deactivated');
  
          }
        }else{
              return redirect()->back();
          }
          
          $lock->release();
          }//lock
      }
      
        //vault till customer posting/transactions
        public function vault_till_posting(){
            return view('accountmgt.vault_till_customer_posting')->with('actyps',AccountType::all());
        }

    public function make_vault_transactions(Request $r){
           
           $lock = Cache::lock('mkvulttrxn-'.mt_rand('1111','9999'),2);
  
   //  try{

    if($lock->get()){


           // DB::beginTransaction();

            $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;
            
             $usern = Auth::user()->last_name." ".Auth::user()->first_name;
             
             
             if (preg_match('/[\'^£$%&*}{@#~?><>()"|=_+¬]/', $r->description)) {
                return ['status' => '0', 'msg' => "No special character allowed in narration"];
            }
            
            $getsetvalue = new Setting();

            
        $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->lockForUpdate()->first();
        $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->lockForUpdate()->first();
          
            if($r->options == "vtp"){
                $this->logInfo("vault to till posting",$r->all());
                
                $this->validate($r,[
                    'amount' => ['required','string','numeric','gt:0'],
                    'glcode' => ['required','string','numeric'],
                    'glcode2' => ['required','string','numeric'],
                ]);
                $subcod = substr($r->glcode,0,2);// get first two digits of gl code
                $checkacctyp = AccountType::where('code',$subcod)->first();
                
                $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$r->glcode)->lockForUpdate()->first();


                $trxref = $this->generatetrnxref('vtp');
                
                    if($r->account_type == "asset" && $r->account_type2 == "asset" && $r->gltype == "Vault Account"){
                        
                        if($glacct->status == '1'){

                        if($glacct->account_balance >= $r->amount){
                            $dedamount = $glacct->account_balance - $r->amount;

                        if($r->dbit == 'debit'){
                            $glacct->account_balance = $dedamount;
                            $glacct->save();

                            $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$r->amount,'credit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
          
                           
                            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited an vault account');

                            $this->credit_other_gl_accounts($r->options,$r->glcode2,$r->amount,$branch,$trxref,str_replace("'", "",$r->description),'');
                        }

                    //    DB::commit();

                        return array(
                            'status' => 'success',
                            'msg' => 'Transfer Successful'
                        );

                    }else{
                        return array(
                            'status' => '0',
                            'msg' => 'Insufficent Fund'
                        );
                      }
                      
                        }else{
                        return array(
                            'status' => '0',
                            'msg' => 'Inactive GL account'
                        );
                    }
                    
                    }else{
                        return array(
                            'status' => '0',
                            'msg' => 'Transaction must be of account type asset with GL of Vault Account  or Teller till'
                        );
                    }
                  
               

            }elseif($r->options == "tvp"){
                $this->logInfo("till to vault posting",$r->all());
                
                $this->validate($r,[
                    'amount' => ['required','string','numeric','gt:0'],
                    'glcode3' => ['required','string','numeric'],
                    'glcode4' => ['required','string','numeric'],
                ]);
                
                $trxref = $this->generatetrnxref('tvp');
                
                    $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$r->glcode3)->lockForUpdate()->first();

                if($r->account_type3 == "asset" && $r->account_type4 == "asset" && $r->gltype == "Vault Account"){
                    if($glacct->status == '1'){


                    if($glacct->account_balance >= $r->amount){
                        $dedamount = $glacct->account_balance - $r->amount;
                        
                    if($r->dbit == 'debit'){
                        $glacct->account_balance = $dedamount;
                            $glacct->save();
                       
                        $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$r->amount,'credit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
      
                       
                        $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited an account');

                        $this->credit_other_gl_accounts($r->options,$r->glcode4,$r->amount,$branch,$trxref,str_replace("'", "",$r->description),'');
                    }

                   // DB::commit();

                    return array(
                        'status' => 'success',
                        'msg' => 'Transfer Successful'
                    );

                }else{
                    return array(
                        'status' => '0',
                        'msg' => 'Insufficent Fund'
                    );
                  }
                }else{
                    return array(
                        'status' => '0',
                        'msg' => 'Inactive GL account'
                    );
                  }
                }else{
                    return array(
                        'status' => '0',
                        'msg' => 'Transaction must be of account type asset with GL of Vault Account or Teller till'
                    );
                }

            }elseif($r->options == "tcp"){
                $this->logInfo("till to customer posting",$r->all());
                
                $this->validate($r,[
                    'account_number' => ['required','string','numeric'],
                    'amount' => ['required','string','numeric','gt:0'],
                    'glcode5' => ['required','string','numeric'],
                ]);
                $trxref = $this->generatetrnxref('tcp');
                
                    $glacct = GeneralLedger::select('id','status','account_balance','currency_id')->where('gl_code',$r->glcode5)->lockForUpdate()->first();

                 $cust = Customer::where('id',$r->customerid)->first();

                if($glacct->currency_id != $cust->exchangerate_id){

                    return array('status' => '0','msg' => 'Currency mis-match');
                }

                if($r->account_type5 == "asset"){
                    if($glacct->status == '1'){

                    if($glacct->account_balance > $r->amount || $glacct->account_balance == $r->amount){
                       

                    if($r->dbit == 'debit'){
                        if($getsetvalue->getsettingskey('deposit_limit') == 0 || $r->amount < $getsetvalue->getsettingskey('deposit_limit')){
                        
                            $addamount = $glacct->account_balance + $r->amount;
                        $glacct->account_balance = $addamount;
                            $glacct->save();
                        
                        $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
                    }else{
                    
                        $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$r->amount,'credit','core',null,$trxref,str_replace("'", "",$r->description),'pending',$usern);
                       
                      }
                        $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited an till account');

                         
                            $creditcut = $this->credit_account_transfer($r->amount,$r->customerid,$r->options,'credit',$trxref,str_replace("'", "",$r->description),$branch);
                       

                        if($creditcut['status'] == false){
                             $glacct->account_balance -= $r->amount;
                            $glacct->save();
                        
                        $this->create_saving_transaction_gl(Auth::user()->id,$glacct->id,$branch,$r->amount,'credit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
      
                        $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited till account reversed');
                           
                       // DB::commit();

                            return array(
                             'status' => '0',
                             'msg' => $creditcut['msg']
                            );
                        }else{
                             return array(
                                'status' => 'success',
                                'msg' => $creditcut['msg']
                            );
                        }
                      
                    }
                    

                }else{
                    return array(
                        'status' => '0',
                        'msg' => 'Insufficent Fund'
                    );
                  }
                }else{
                    return array(
                        'status' => '0',
                        'msg' => 'Inactive GL account'
                    );
                  }
                }else{
                    return array(
                        'status' => '0',
                        'msg' => 'Transaction must be of account type asset'
                    );
                }

            }elseif($r->options == "ctp"){

                $this->logInfo("customer to till posting",$r->all());
                
                $this->validate($r,[
                    'account_number' => ['required','string','numeric'],
                    'amount' => ['required','string','numeric','gt:0'],
                    'glcode6' => ['required','string','numeric'],
                ]);
                
                $trxrefc = $this->generatetrnxref('ctp');

                    $cust = Customer::where('id',$r->customerid)->first();
                $glacct = GeneralLedger::select('id','status','account_balance','currency_id')->where('gl_code',$r->glcode6)->lockForUpdate()->first();

                if($glacct->currency_id != $cust->exchangerate_id){

                       return array('status' => '0','msg' => 'Currency mis-match');
                }

            $chkcres = $this->checkCustomerRestriction($r->customerid);
                    if($chkcres == true){
                
                        $this->tracktrails('1','1',$usern,'customer','Account Restricted');
                        
                    return array('status' => '0','msg' => 'Customer Account Has Been Restricted');
                    }
                    
                    $chklien = $this->checkCustomerLienStatus($r->customerid);
                    if($chklien['status'] == true && $chklien['lien'] == 2){
                        $this->tracktrails('1','1',$usern,'customer','Account has been lien');
                     return  array('status' => '0','msg' => 'Your Account Has Been Lien('.$chklien['message'].')...please contact support');
                    }
                    
                if($r->account_type6 == "asset"){

                    $customeracct2 = Saving::lockForUpdate()->where('customer_id',$r->customerid)->first();

                  if($customeracct2->account_balance > $r->amount || $customeracct2->account_balance == $r->amount){

                    if($r->dbit == "debit"){
                        if($getsetvalue->getsettingskey('withdrawal_limit') == 0 || $r->amount < $getsetvalue->getsettingskey('withdrawal_limit')){
                           
                            $dedamount = $customeracct2->account_balance - $r->amount;
                        $customeracct2->account_balance = $dedamount;
                        $customeracct2->save();
                    
                        $this->create_saving_transaction(Auth::user()->id,$r->customerid,$branch,$r->amount,
                            'debit','core','0',null,null,null,null,$trxrefc,str_replace("'", "",$r->description),'approved','2','trnsfer',$usern);
                            
                            if(!is_null($cust->exchangerate_id)){
                                $this->checkforeigncurrncy($cust->exchangerate_id,$r->amount,$trxrefc,'debit');
                             }else{

                                //if($cust->account_type == '1'){//saving acct GL
                            
                                    if($glsavingdacct->status == '1'){
                                    $this->gltransaction('deposit',$glsavingdacct,$r->amount,null);
                                $this->create_saving_transaction_gl(null,$glsavingdacct->id,null,$r->amount,'debit','core',$trxrefc,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                                }
                                
                                // }elseif($cust->account_type == '2'){//current acct GL
                                
                                //      if($glsavingdacct->status == '1'){
                                //     $this->gltransaction('deposit',$glcurrentacct,$r->amount,null);
                                // $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $r->amount,'debit','core',$trxrefc,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                                //      } 
                                     
                                // }
                             }

                            
                        $this->tracktrails(Auth::user()->id,Auth::user()->branch_id,$usern,'general ledger','deposited to a Tiil account from customer');
                    
                      $this->credit_other_gl_accounts($r->options,$r->glcode6,$r->amount,$branch,$trxrefc,str_replace("'", "",$r->description),'');
                          
                    //   DB::commit();

                      $smsmsg = "Debit Amt: N".number_format($r->amount,2)."\n Desc: ".str_replace("'", "",$r->description)." \n Avail Bal: N".number_format($customeracct2->account_balance,2)."\n Date:" . date('Y-m-d') . "\n Ref: " . $trxrefc;
    
                      if($cust->enable_sms_alert){
                          $this->sendSms(Auth::user()->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                          }

                     if($cust->enable_email_alert){
                    $msg =  "Debit Amt: N".number_format($r->amount,2)."<br> Desc: ".str_replace("'", "",$r->description)." <br>Avail Bal: N". number_format($customeracct2->account_balance,2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trxrefc;
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
                        'msg' => 'Withdrawal Posted Successfully'
                        );
                        
                    }else{

                        $this->create_saving_transaction(Auth::user()->id,$r->customerid,$branch,$r->amount,
                            'debit','core','0',null,'0',$r->options,null,$trxrefc,str_replace("'", "",$r->description),'pending','2','trnsfer',$usern);

                            $this->credit_other_gl_accounts($r->options,$r->glcode6,$r->amount,$branch,$trxrefc,str_replace("'", "",$r->description),'pending');
                        
                           // DB::commit();

                     return array(
                        'status' => 'success',
                        'msg' => 'Withdrawal Posted...Awaiting Approval'
                        );
                     }
                    }
                    
                   }else{
                        return array(
                            'status' => '0',
                            'msg' => 'Insufficent Fund'
                        );
                      }
               }else{
                return array(
                    'status' => '0',
                    'msg' => 'Transaction must be of account type asset'
                   );
               }
          }

          //DB::commit();
          
          $lock->release();

        //   DB::rollBack();

         }//lock
  
    }
        
        
        public function credit_other_gl_accounts($opt,$glcode,$amount,$branch,$trx,$desc,$status){

       $glacct2 = GeneralLedger::select('id','status','account_balance')->where('gl_code',$glcode)->lockForUpdate()->first();
       
            $usern = Auth::user()->last_name." ".Auth::user()->first_name;
     if($glacct2->status == '1'){
         
            if($opt == "vtp"){
            $addamount = $glacct2->account_balance + $amount;

                $glacct2->account_balance = $addamount;
                $glacct2->save();
            
            $this->create_saving_transaction_gl(Auth::user()->id,$glacct2->id,$branch,$amount,'debit','core',$trx,$this->generatetrnxref('vtp'),$desc,'approved',$usern);

           
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','credited an Till account');
           
            }elseif($opt == "tvp"){
                $addamount = $glacct2->account_balance + $amount;
    
                    $glacct2->account_balance = $addamount;
                    $glacct2->save();
                
                $this->create_saving_transaction_gl(Auth::user()->id,$glacct2->id,$branch,$amount,'debit','core',$trx,$this->generatetrnxref('tvp'),$desc,'approved',$usern);
    
                $usern = Auth::user()->last_name." ".Auth::user()->first_name;
                $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','credited an vault account');
            }elseif($opt == "tcp"){

            }elseif($opt == "ctp"){
                $dedamount = $glacct2->account_balance - $amount;
    
                // if($glacct2->account_balance <= 0){
                //     $glacct2->account_balance = $amount;
                //   $glacct2->save();
                // }else{
                // }
                if($status == "pending"){

                    $this->create_saving_transaction_gl(Auth::user()->id,$glacct2->id,$branch,$amount,'credit','core',$trx,$this->generatetrnxref('ctp'),$desc,'pending',$usern);

                }else{
                    $glacct2->account_balance = $dedamount;
                    $glacct2->save();
                
                $this->create_saving_transaction_gl(Auth::user()->id,$glacct2->id,$branch,$amount,'credit','core',$trx,$this->generatetrnxref('ctp'),$desc,'approved',$usern);
    
                
                $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','credited a till account');
                }
                  
            }
           }
        }

        //credit gl from bank funds or capital
        public function credit_gl_accounts(Request $r){
            
           $lock= Cache::lock('glcrdit-'.mt_rand('1111','9999'),3);
           
           if($lock->get()){  
               
            $this->logInfo("credit gl account",$r->all());
            
            $this->validate($r,[
                'amount' => ['required','string','numeric'],
                'glcode' => ['required','string','numeric'], 
            ]);
            $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;
            
           $usern = Auth::user()->last_name." ".Auth::user()->first_name;
           
            if($r->account >= $r->amount){
                $subcod = substr($r->glcode,0,2);// get first two digits of gl code
                $checkacctyp = AccountType::where('code',$subcod)->first();

                $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$r->glcode)->lockForUpdate()->first();


                $trxref = date("Ymd")."".mt_rand(111,999);
                
            $dedamount = $glacct->account_balance - $r->amount;
                    $addamount = $glacct->account_balance + $r->amount;
                    
                      if($checkacctyp->name == "asset"){
                        
                            $glacct->account_balance = $addamount;
                            $glacct->save();
                        
                         $subal = $r->account - $r->amount;
                        if($r->funds == "bank funds"){
                        
                            Setting::where('setting_key', 'bank_fund')->update(['setting_value' => $subal]);

                        }elseif($r->funds == "capital"){

                         Setting::where('setting_key', 'company_capital')->update(['setting_value' => $subal]);
                        
                        }
    
                          $this->create_saving_transaction_gl(Auth::user()->id, $glacct->id,$branch,$r->amount,'debit','core',$trxref,$this->generatetrnxref('bnkgl'),str_replace("'", "",$r->description),'approved',$usern);
        
                          
                          $this->tracktrails(Auth::user()->id,$branch,$usern,'from '.$r->funds,'deposited to a '.$glacct->gl_name.' GL account from '.$r->funds);
  
                      }elseif($checkacctyp->name == "liability"){
                        
                            $glacct->account_balance = $addamount;
                            $glacct->save();
                        
                         $subal = $r->account - $r->amount;
                        if($r->funds == "bank funds"){
                        
                            Setting::where('setting_key', 'bank_fund')->update(['setting_value' => $subal]);

                        }elseif($r->funds == "capital"){

                         Setting::where('setting_key', 'company_capital')->update(['setting_value' => $subal]);
                        
                        }
    
                          $this->create_saving_transaction_gl(Auth::user()->id, $glacct->id,$branch,$r->amount,'credit','core',$trxref,$this->generatetrnxref('bnkgl'),str_replace("'", "",$r->description),'approved',$usern);
        
                          $usern = Auth::user()->last_name." ".Auth::user()->first_name;
                          $this->tracktrails(Auth::user()->id,$branch,$usern,'from '.$r->funds,'deposited to a '.$glacct->gl_name.' GL account from '.$r->funds);

                      }elseif($checkacctyp->name == "capital"){
                       
                            $glacct->account_balance = $addamount;
                            $glacct->save();
                        
                         $subal = $r->account - $r->amount;
                        if($r->funds == "bank funds"){
                        
                            Setting::where('setting_key', 'bank_fund')->update(['setting_value' => $subal]);

                        }elseif($r->funds == "capital"){

                         Setting::where('setting_key', 'company_capital')->update(['setting_value' => $subal]);
                        
                        }
    
                          $this->create_saving_transaction_gl(Auth::user()->id, $glacct->id,$branch,$r->amount,'credit','core',$trxref,$this->generatetrnxref('bnkgl'),str_replace("'", "",$r->description),'approved',$usern);
        
                         
                          $this->tracktrails(Auth::user()->id,$branch,$usern,'from '.$r->funds,'deposited to a '.$glacct->gl_name.' GL account from '.$r->funds);
                     
                      }elseif($checkacctyp->name == "income"){
                         
                            $glacct->account_balance = $addamount;
                            $glacct->save();
                        
                         $subal = $r->account - $r->amount;
                        if($r->funds == "bank funds"){
                        
                            Setting::where('setting_key', 'bank_fund')->update(['setting_value' => $subal]);

                        }elseif($r->funds == "capital"){

                         Setting::where('setting_key', 'company_capital')->update(['setting_value' => $subal]);
                        
                        }
    
                          $this->create_saving_transaction_gl(Auth::user()->id, $glacct->id,$branch,$r->amount,'credit','core',$trxref,$this->generatetrnxref('bnkgl'),str_replace("'", "",$r->description),'approved',$usern);
        
                          $this->tracktrails(Auth::user()->id,$branch,$usern,'from '.$r->funds,'deposited to a '.$glacct->gl_name.' GL account from '.$r->funds);
  
                      }elseif($checkacctyp->name == "expense"){
                        
                            $glacct->account_balance = $addamount;
                            $glacct->save();
                        
                         $subal = $r->account - $r->amount;
                        if($r->funds == "bank funds"){
                        
                            Setting::where('setting_key', 'bank_fund')->update(['setting_value' => $subal]);

                        }elseif($r->funds == "capital"){

                         Setting::where('setting_key', 'company_capital')->update(['setting_value' => $subal]);
                        
                        }
    
                          $this->create_saving_transaction_gl(Auth::user()->id, $glacct->id,$branch,$r->amount,'debit','core',$trxref,$this->generatetrnxref('bnkgl'),str_replace("'", "",$r->description),'approved',$usern);
        
                          
                          $this->tracktrails(Auth::user()->id,$branch,$usern,'from '.$r->funds,'deposited to a '.$glacct->gl_name.' GL account from '.$r->funds);
                      }
                      
                      return array(
                        'status' => 'success',
                        'msg' => 'Transfer Successful'
                    );
                  }else{
                    return array(
                        'status' => '0',
                        'msg' => 'Insufficent Fund'
                    );
                  }
             
             $lock->release();     
            }
        }

public function gl_make_transaction(Request $r){
            
            $lock = Cache::lock('glmktranxn-'.mt_rand('1111','9999'),2);

    if($lock->get()){
         //try{

        //DB::beginTransaction();
       
            $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;
            
         $usern = Auth::user()->last_name." ".Auth::user()->first_name;
         
         if (preg_match('/[\'^£$%&*}{@#~?><>"()|=_+¬]/', $r->description)) {
                return ['status' => '0', 'msg' => "No special character allowed in narration"];
          }
            
         $getsetvalue = new Setting();
        
         $convrtamt = 0;

         $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->lockForUpdate()->first();
         $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->lockForUpdate()->first();
           
         $convrtamt = 0;

            if($r->options == "glc"){ //GL account to customer

            $this->logInfo("general ledger to customer",$r->all());
            
                $this->validate($r,[
                    'account_number' => ['required','string','numeric'],
                    'gl_code' => ['required','string','numeric'],
                    'amount' => ['required','string','numeric','gt:0'],
                ]);
                $subcod = substr($r->gl_code,0,2);// get first two digits of gl code
               
                $glacct = GeneralLedger::where('id',$r->gldger_id)->first();

                 $cust = Customer::where('id',$r->customerid)->first();
                 
                if($glacct->currency_id != $cust->exchangerate_id){

                    return array('status' => '0','msg' => 'Currency mis-match');
                }

                // $checkacctyp = AccountType::where('name',)->first();

                $trxref = $this->generatetrnxref('glc');

                  if($r->dbit == 'debit'){
                    if($glacct->gl_type == "asset"){
                        
                            // $glacct->account_balance = $dedamount;
                            // $glacct->save();
                     if($getsetvalue->getsettingskey('deposit_limit') == 0 || $r->amount < $getsetvalue->getsettingskey('deposit_limit')){

                        $addamount = $glacct->account_balance + $r->amount;
                            $glacct->account_balance = $addamount;
                            $glacct->save();
                        
                        
                        $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
    
                        $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an account');

                    }else{
                        $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'pending',$usern);
                    }

                    }elseif($glacct->gl_type == "liability"){
                        
                        if($glacct->account_balance >= $r->amount){
                            if($getsetvalue->getsettingskey('deposit_limit') == 0 || $r->amount < $getsetvalue->getsettingskey('deposit_limit')){
                                    
                                    $dedamount = $glacct->account_balance - $r->amount;
                                $glacct->account_balance = $dedamount;
                                $glacct->save();
        
                                $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
            
                                $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited liability account');
                                }else{
                                    $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'pending',$usern);
                                }
                        }else{
                            return array(
                                'status' => '0',
                                'msg' => 'Insufficent Fund'
                            );
                          }
                       
                        
                    }elseif($glacct->gl_type == "capital"){

                        if($glacct->account_balance >= $r->amount){
                            if($getsetvalue->getsettingskey('deposit_limit') == 0 || $r->amount < $getsetvalue->getsettingskey('deposit_limit')){
                                
                                    $dedamount = $glacct->account_balance - $r->amount;
                                $glacct->account_balance = $dedamount;
                                $glacct->save();
        
                                $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
            
                                $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited capital account');
                                }else{
                                    $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'pending',$usern);
                                }
                        }else{
                            return array(
                                'status' => '0',
                                'msg' => 'Insufficent Fund'
                            );
                          }
                       
                    }elseif($glacct->gl_type == "income"){
                        
                        if($glacct->account_balance >= $r->amount){
                            if($getsetvalue->getsettingskey('deposit_limit') == 0 || $r->amount < $getsetvalue->getsettingskey('deposit_limit')){
                                
                                    $dedamount = $glacct->account_balance - $r->amount;
                                $glacct->account_balance = $dedamount;
                                $glacct->save();
        
                                $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
            
                                
                                $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debit income account');
                            }else{
                                $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'pending',$usern);
                            }
                        }else{
                            return array(
                                'status' => '0',
                                'msg' => 'Insufficent Fund'
                            );
                          }
                     
                        
                    }elseif($glacct->gl_type == "expense"){
                        
                     if($getsetvalue->getsettingskey('deposit_limit') == 0 || $r->amount < $getsetvalue->getsettingskey('deposit_limit')){
                        $addamount = $glacct->account_balance + $r->amount;
                            $glacct->account_balance = $addamount;
                            $glacct->save();
                        
  
                        $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
      
                        $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited expense account');
                    }else{
                        $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'pending',$usern);

                    }
                    }
                             
                        $creditcut = $this->credit_account_transfer($r->amount,$r->customerid,'glc',$r->dbit2,$trxref,str_replace("'", "",$r->description),$branch);
                    
                         if($creditcut['status'] == false){

                            if($glacct->gl_type == "asset"){

                                $dedamount = $glacct->account_balance - $r->amount;
                            $glacct->account_balance = $dedamount;
                            $glacct->save();
                         
                        
                        $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'credit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
      
                       
                        $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited asset account reversed');

                    }elseif($glacct->gl_type == "liability"){

                        $addamount = $glacct->account_balance + $r->amount;
                        $glacct->account_balance = $addamount;
                        $glacct->save();
  
                        $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'credit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
      
                        $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited liability account reversed');

                    }elseif($glacct->gl_type == "capital"){

                        $addamount = $glacct->account_balance + $r->amount;
                        $glacct->account_balance = $addamount;
                        $glacct->save();
  
                        $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'credit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
      
                        $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited capital account reversed');
                   
                    }elseif($glacct->gl_type == "income"){

                        $addamount = $glacct->account_balance + $r->amount;
                        $glacct->account_balance = $addamount;
                        $glacct->save();
  
                        $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'credit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
      
                        
                        $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debit income account reversed');

                    }elseif($glacct->gl_type == "expense"){

                         $dedamount = $glacct->account_balance - $r->amount;
                        $glacct->account_balance = $dedamount;
                        $glacct->save();
  
                        $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'credit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
      
                        $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited expense account reveresed');
                    }
                            
                    // DB::commit();

                            return array(
                             'status' => '0',
                             'msg' => $creditcut['msg']
                            );
                            
                        }else{
                             return array(
                           'status' => 'success',
                           'msg' => $creditcut['msg']
                            );
                        }
                  }
               
             

            }elseif($r->options == "cgl"){ //customer to GL account

             $this->logInfo("customer to general legder posting",$r->all());
             
             $trxref2 = $this->generatetrnxref('cgl');

            $this->validate($r,[
                    'account_number' => ['required','string','numeric'],
                    'gl_code2' => ['required','string','numeric'],
                    'amount' => ['required','string','numeric','gt:0'],
                ]);

                $cust = Customer::where('id',$r->customerid)->first();

                $glacct = GeneralLedger::select('id','status','account_balance','currency_id')->where('gl_code',$r->gl_code2)->lockForUpdate()->first();

                 
                if($glacct->currency_id != $cust->exchangerate_id){

                    return array('status' => '0','msg' => 'Currency mis-match');
                }

                $chkcres = $this->checkCustomerRestriction($r->customerid);
                    if($chkcres == true){
                        $this->tracktrails('1','1',$usern,'customer','Account Restricted');
                    return array('status' => '0','msg' => 'Customer Account Has Been Restricted');
                    }
                    
                 $chklien = $this->checkCustomerLienStatus($r->customerid);
                    if($chklien['status'] == true && $chklien['lien'] == 2){
                        $this->tracktrails('1','1',$usern,'customer','Account has been lien');
                     return  array('status' => '0','msg' => 'Customer Account Has Been Lien('.$chklien['message'].')...please contact support');
                    }
                    
                     $validateuserbalance = $this->validatecustomerbalance($r->customerid,$r->amount);
                    if($validateuserbalance["status"] == false){
                        $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
                        $this->logInfo("customer balance",$validateuserbalance);
                        return ['status' => '0', 'msg' => $validateuserbalance["message"]];
                    }
                    
                $customeracct = Saving::lockForUpdate()->where('customer_id',$r->customerid)->first();
    
                  
                  if($r->dbit == 'debit'){
                      
                      //$trxref = date("ymd")."".mt_rand(111,999);
                      
                      if($getsetvalue->getsettingskey('withdrawal_limit') == 0 || $r->amount < $getsetvalue->getsettingskey('withdrawal_limit')){

                        $dedamount = $customeracct->account_balance - $r->amount;
                      $customeracct->account_balance = $dedamount;
                      $customeracct->save();
          
                      $this->create_saving_transaction(Auth::user()->id,$r->customerid,Auth::user()->branch_id,$r->amount,
                      $r->dbit,'core','0',null,null,'cgl',null,$trxref2,str_replace("'", "",$r->description),'approved','2','trnsfer',$usern);
                        
                      if(!is_null($cust->exchangerate_id)){
                        $this->checkforeigncurrncy($cust->exchangerate_id,$r->amount,$trxref2,'debit');
                      }else{
                        
                           // if($cust->account_type == '1'){//saving acct GL
                            
                            if($glsavingdacct->status == '1'){
                                $this->gltransaction('deposit',$glsavingdacct,$r->amount,null);
                            $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $r->amount,'debit','core',$trxref2,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                            }
                            
                            // }elseif($cust->account_type == '2'){//current acct GL
                            
                            //     if($glcurrentacct->status == '1'){
                            //         $this->gltransaction('deposit',$glcurrentacct,$r->amount,null);
                            // $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $r->amount,'debit','core',$trxref2,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                                
                            //     }
                            
                            // }
                      }

                     
                  $this->tracktrails(Auth::user()->id,Auth::user()->branch_id,$usern,'general ledger','debit from an account');
          
                     $this->credit_gl_account_transfer($r->options,$r->gl_code2,$r->amount,$branch,$r->gldger_id,$r->dbit2,'cgl',$trxref2,str_replace("'", "",$r->description),'');
                  
                     DB::commit();

                     $smsmsg = "Debit Amt: N".number_format($r->amount,2)."\n Desc: ".str_replace("'", "",$r->description)." \n Avail Bal: N".number_format($customeracct->account_balance,2)."\n Date:" . date('Y-m-d') . "\n Ref: " . $trxref2;
    
            if($cust->enable_sms_alert){
                $this->sendSms(Auth::user()->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                }

                   if($cust->enable_email_alert){
                    $msg =  "Debit Amt: N".number_format($r->amount,2)."<br> Desc: ".str_replace("'", "",$r->description)." <br>Avail Bal: N". number_format($customeracct->account_balance,2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trxref2;
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
                        'msg' => 'Withdrawal Posted Successful'
                    );
                    
                }else{

                    $this->create_saving_transaction(Auth::user()->id,$r->customerid,Auth::user()->branch_id,$r->amount,
                      $r->dbit,'core','0',null,'0','cgl',null,$trxref2,str_replace("'", "",$r->description),'pending','2','trnsfer',$usern);

                      $this->credit_gl_account_transfer($r->options,$r->gl_code2,$r->amount,$branch,$r->gldger_id,$r->dbit2,'cgl',$trxref2,str_replace("'", "",$r->description),'pending');
                    
                     // DB::commit();

                     return array(
                           'status' => 'success',
                           'msg' => "Withdrawal Posted...Awaiting Approval"
                            );
                }

                    }
                  
                
                
            }elseif($r->options == "gltogl"){
                
                $this->logInfo("gl to gl Posting",$r->all());

                $this->validate($r,[
                    'gl_code' => ['required','string','numeric'],
                    'gl_code2' => ['required','string','numeric'],
                    'amount' => ['required','string','numeric','gt:0'],
                ]);
                
                $subcod = substr($r->gl_code,0,2);// get first two digits of gl code
                $checkacctyp = AccountType::where('code',$subcod)->first();

                $trxref = $this->generatetrnxref('gltogl');
                
                $glacct = GeneralLedger::where('id',$r->gldger_id)->first();

                $glacctcuuty = GeneralLedger::select('id','status','account_balance','currency_id')->where('gl_code',$r->gl_code2)->lockForUpdate()->first();
                 
                if($glacct->currency_id != $glacctcuuty->currency_id){

                    return array('status' => '0','msg' => 'Currency mis-match');
                }
    
                //if($glacct->account_balance >= $r->amount || $glacct->account_balance >= "0"){
                if($glacct->account_balance <= "0" || $glacct->account_balance >= "0"){
                    $dedamount = $glacct->account_balance - $r->amount;
                    $addamount = $glacct->account_balance + $r->amount;
                    
                    if($r->dbit == 'debit'){
                       if($getsetvalue->getsettingskey('withdrawal_limit') == 0 || $r->amount < $getsetvalue->getsettingskey('withdrawal_limit')){

                      if($glacct->gl_type == "asset"){
                       
                            $glacct->account_balance = $addamount;
                            $glacct->save();
                        
                        // $glacct->account_balance = $dedamount;
                        // $glacct->save();
    
                          $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
        
                          
                          $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an account');
  
                      }elseif($glacct->gl_type == "liability"){
                          $glacct->account_balance = $dedamount;
                          $glacct->save();
    
                          $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
        
                          
                          $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited liability account');
  
                      }elseif($glacct->gl_type == "capital"){
                          $glacct->account_balance = $dedamount;
                          $glacct->save();
    
                          $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
        
                          
                          $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited capital account');
                     
                      }elseif($glacct->gl_type == "income"){
                          
                        $glacct->account_balance = $dedamount;
                        $glacct->save();
    
                          $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
        
                        
                          $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debit income account');
  
                      }elseif($glacct->gl_type == "expense"){
                        
                            $glacct->account_balance = $addamount;
                            $glacct->save();
                       
    
                          $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
        
                          $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited expense account');
                      }

                     $this->credit_gl_account_transfer($r->options,$r->gl_code2,$r->amount,$branch,$r->gldger_id2,$r->dbit2,'gltogl',$trxref,str_replace("'", "",$r->description),'');

                     //DB::commit();

                return array(
                    'status' => 'success',
                    'msg' => 'GL Transfer Successful'
                );

                    }else{

                        $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'pending',$usern);

                        $this->credit_gl_account_transfer($r->options,$r->gl_code2,$r->amount,$branch,$r->gldger_id2,$r->dbit2,'gltogl',$trxref,str_replace("'", "",$r->description),'pending');
                        
                        //DB::commit();

                        return array(
                            'status' => 'success',
                            'msg' => "GL transaction Posted...Awaiting Approval"
                                );
                    }
              }else{
                return array(
                    'status' => '0',
                    'msg' => 'Insufficent Fund'
                );
              }
            }else{
                
            }
           
            }
            
           // DB::commit();

           $lock->release();
      
          // DB::rollBack();
    }//lock
}
    
    
        public function credit_account_transfer($amount,$cid,$inita,$tran_type2,$trxref,$desc){
            $usern = Auth::user()->last_name." ".Auth::user()->first_name;

            $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->lockForUpdate()->first();
            $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->lockForUpdate()->first();   
            
             $chkcres = $this->checkCustomerRestriction($cid);
             $chklien = $this->checkCustomerLienStatus($cid);

             $getsetvalue = new Setting();
             $convrtamt = 0;

             $cust = Customer::where('id',$cid)->first();
            if($chkcres == true){
        
                $this->tracktrails('1','1',$usern,'customer','Account Restricted');
                
            return array('status' => false,'msg' => 'Customer Account Has Been Restricted');
                
            }elseif($chklien['status'] == true && $chklien['lien'] == 1){
               
                $this->tracktrails('1','1',$usern,'customer','Account Restricted');
                
               return array('status' => false,'msg' => 'Customer Account Has Been Lien('.$chklien['message'].')...please contact support');
            
            } else{
                 if($amount > $getsetvalue->getsettingskey('deposit_limit')){
                    
                        $this->create_saving_transaction(Auth::user()->id,$cid,Auth::user()->branch_id,$amount,
                                                $tran_type2,'core','0',$trxref,'0',$inita,null,$this->generatetrnxref('Cr'),$desc,'pending','1','trnsfer',$usern);
        
                    return array('status' => true,'msg' => 'Deposit Posted...Awaiting Approval');
                    
                }else{
                
            $customeracct2 = Saving::lockForUpdate()->where('customer_id',$cid)->first();
            
            $cramount = $customeracct2->account_balance + $amount;
                 $customeracct2->account_balance = $cramount;
               $customeracct2->save();
            

             $this->create_saving_transaction(Auth::user()->id,$cid,Auth::user()->branch_id,$amount,
                                        $tran_type2,'core','0',$trxref,null,$inita,null,$this->generatetrnxref('Cr'),$desc,'approved','1','trnsfer',$usern);
                   
            if(!is_null($cust->exchangerate_id)){
                $this->checkforeigncurrncy($cust->exchangerate_id,$amount,$trxref,'credit');
             }else{
               //if($cust->account_type == '1'){//saving acct GL

                    if($glsavingdacct->status == '1'){
                        $this->gltransaction('withdrawal',$glsavingdacct,$amount,null);
                        $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $amount,'credit','core',$trxref,$this->generatetrnxref('Cr'),'customer credited','approved',$usern);
                
                    }
                
            // }elseif($cust->account_type == '2'){//current acct GL
            //      if($glcurrentacct->status == '1'){
            //     $this->gltransaction('withdrawal',$glcurrentacct,$amount,null);
            // $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $amount,'credit','core',$trxref,$this->generatetrnxref('Cr'),'customer credited','approved',$usern);
            //      }
            // }

             }

             $this->checkOutstandingCustomerLoan($cid,$amount);//check if customer has an outstanding loan

            $this->tracktrails(Auth::user()->id,Auth::user()->branch_id,$usern,'general ledger','deposited to an account');

            $smsmsg = "Debit Amt: N".number_format($amount,2)."\n Desc: ".$desc." \n Avail Bal: N".number_format($customeracct2->account_balance,2)."\n Date:" . date('Y-m-d') . "\n Ref: " . $trxref;
    
            if($cust->enable_sms_alert){
                $this->sendSms(Auth::user()->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
                }

         if($cust->enable_email_alert){
        $msg =  "Credit Amt: N".number_format($amount,2)."<br> Desc: ".$desc." <br>Avail Bal: N". number_format($customeracct2->account_balance,2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trxref;
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
         
                 return array('status' => true,'msg' => 'Deposit Posted Successfully');

        }
            }
        }

        //credit general ledger
        public function credit_gl_account_transfer($opt,$glcode,$amount,$branch,$glid,$dbit2,$inita,$trxref,$desc,$status){
            
            $usern = Auth::user()->last_name." ".Auth::user()->first_name;

            $subcod = substr($glcode,0,2);// get first two digits of gl code
            $checkacctyp = AccountType::where('code',$subcod)->first();
            $glacct2 = GeneralLedger::where('id',$glid)->first();
            
            if($opt == "cgl"){
                
             if($glacct2->gl_type == "asset"){
                
                if($status == "pending"){
                    $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'pending',$usern);
                }else{
                    
                     $astamount = $glacct2->account_balance - $amount;
                     $glacct2->account_balance = $astamount;
                   $glacct2->save();
                   
                $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
        
                
                $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an asset account');
                
                }
                
            }elseif($glacct2->gl_type == "liability"){
                if($status == "pending"){
                    $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'pending',$usern);
                }else{
                
                     $liamount = $glacct2->account_balance + $amount;
                     $glacct2->account_balance = $liamount;
                   $glacct2->save();
                
    
                $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
        
               
                $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to a liability account');
                
            }
             }elseif($glacct2->gl_type == "capital"){
                if($status == "pending"){
                    $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'pending',$usern);
                }else{
                
                     $cpamount = $glacct2->account_balance + $amount;
                     $glacct2->account_balance = $cpamount;
                   $glacct2->save();
                
    
                $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
        
              
                $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to a capital account');
            }
             }elseif($glacct2->gl_type == "income"){
                if($status == "pending"){
                    $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'pending',$usern);
                }else{
                
                     $inamount = $glacct2->account_balance + $amount;
                     $glacct2->account_balance = $inamount;
                   $glacct2->save();
                
    
                $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
        
               
                $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an income account');
                 }
             }elseif($glacct2->gl_type == "expense"){
            
                if($status == "pending"){
                    $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'pending',$usern);
                }else{
                 $eamount = $glacct2->account_balance - $amount;
                 $glacct2->account_balance = $eamount;
                   $glacct2->save();
                   
                $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
        
               
                $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an expense account');
                
             }
            }
            }elseif($opt == "gltogl"){
                
                
             if($glacct2->gl_type == "asset"){
                if($status == "pending"){
                    $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'pending',$usern);
                }else{
                 $asglamount = $glacct2->account_balance - $amount;
                 $glacct2->account_balance = $asglamount;
                  $glacct2->save();
                   
                $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
        
                
                $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an asset account');
             }
            }elseif($glacct2->gl_type == "liability"){

                if($status == "pending"){
                    $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'pending',$usern);
                }else{
                    $liglamount = $glacct2->account_balance + $amount;
                     $glacct2->account_balance = $liglamount;
                   $glacct2->save();
                
    
                $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
        
                $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to a liability account');
                
                 }

             }elseif($glacct2->gl_type == "capital"){
                
                if($status == "pending"){
                    $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'pending',$usern);
                }else{
                     $cpglamount = $glacct2->account_balance + $amount;
                     $glacct2->account_balance = $cpglamount;
                   $glacct2->save();
                
    
                $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
        
                $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to a capital account');
                }
             }elseif($glacct2->gl_type == "income"){
                
                if($status == "pending"){
                    $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'pending',$usern);
                }else{
                     $inglamount = $glacct2->account_balance + $amount;
                     $glacct2->account_balance = $inglamount;
                   $glacct2->save();
                
    
                $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
        
                
                $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an income account');
                }
             }elseif($glacct2->gl_type == "expense"){
                
                if($status == "pending"){
                    $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'pending',$usern);
                }else{
                $eglamount = $glacct2->account_balance - $amount;
                $glacct2->account_balance = $eglamount;
                  $glacct2->save();
                  
                $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
        
              
                $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an expense account');
                }
             }
             
            }
           
        }

        //batch upload account mgmt records
 public function batch_upload_store(Request $r)
    {
        $this->validate($r,[
            'file_upload' => ['required','mimes:csv','max:10240']
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $csvfile = $r->file('file_upload');
        $newcsvfile = time()."_".$csvfile->getClientOriginalName();
        $csvfile->move('uploads',$newcsvfile);

        
        if($r->uploads == "ac"){
            $csvfilepath = $_SERVER["DOCUMENT_ROOT"]."/uploads/".$newcsvfile;
            $this->account_category_upload_file($csvfilepath);
            unlink($csvfilepath); //remove uploaded file

            $usern = Auth::user()->last_name." ".Auth::user()->first_name;
            $this->tracktrails(Auth::user()->id,$branch,$usern,'account category','uploaded account category');
           
            return redirect()->route('ac.category.index')->with('success','Account Category Uploaded');

        }elseif($r->uploads == "gl"){//general ledger
            $glcsvfilepath = $_SERVER["DOCUMENT_ROOT"]."/uploads/".$newcsvfile;
            $this->general_legder_upload_file($glcsvfilepath);
            unlink($glcsvfilepath); //remove uploaded file

            $usern = Auth::user()->last_name." ".Auth::user()->first_name;
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','uploaded general ledger');
           
            return redirect()->route('gl.index')->with('success','General Ledger Uploaded');

        }elseif($r->uploads == "cgl"){//customer to GL

            $cglcsvfilepath = $_SERVER["DOCUMENT_ROOT"]."/uploads/".$newcsvfile;
            $response = $this->customer_general_legder_upload_file($cglcsvfilepath);

            if($response['status'] == false){
                unlink($cglcsvfilepath);//remove uploaded file
    
                  return redirect()->back()->with('error',$response['msg']);
           }else{
                 
            unlink($cglcsvfilepath); //remove uploaded file

            $usern = Auth::user()->last_name." ".Auth::user()->first_name;
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','uploaded customer to general ledger');
           
            $urllink = route('viewuploadstatus')."?type=cgl&uploadstatus=current";
            return redirect($urllink)->with('success','General Ledger Uploaded');
           }

        }elseif($r->uploads == "glc"){//GL to customer

            $glccsvfilepath = $_SERVER["DOCUMENT_ROOT"]."/uploads/".$newcsvfile;
            $response = $this->general_legder_customer_upload_file($glccsvfilepath);

            if($response['status'] == false){
                unlink($glccsvfilepath);//remove uploaded file
    
                  return redirect()->back()->with('error',$response['msg']);
           }else{
            unlink($glccsvfilepath); //remove uploaded file

            $usern = Auth::user()->last_name." ".Auth::user()->first_name;
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','uploaded general ledger to cutomer');
           
            $urllink = route('viewuploadstatus')."?type=glc&uploadstatus=current";
            return redirect($urllink)->with('success','General Ledger Uploaded');
           }

        }elseif($r->uploads == "gltogl"){//GL to GL

            $glglcsvfilepath = $_SERVER["DOCUMENT_ROOT"]."/uploads/".$newcsvfile;
            $response = $this->general_legder_gl_upload_file($glglcsvfilepath);

            if($response['status'] == false){
                unlink($glglcsvfilepath);//remove uploaded file
    
                  return redirect()->back()->with('error',$response['msg']);
           }else{  
            unlink($glglcsvfilepath); //remove uploaded file

            $usern = Auth::user()->last_name." ".Auth::user()->first_name;
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','uploaded general ledger');
            
            $urllink = route('viewuploadstatus')."?type=gltogl&uploadstatus=current";
            return redirect($urllink)->with('success','General Ledger Uploaded');
        }

        }else{
            return redirect()->back()->with('error','Please Select a file');
        }
    }

    public function account_category_upload_file($filepath)
    {
        $handlefile = fopen($filepath, "r");
        fgetcsv($handlefile);//skip first line
        while(($data = fgetcsv($handlefile, '1000000',',')) != FALSE){
            $name = $data[0];
            $account_type = $data[1];
            $description = $data[2]; 

            AccountCategory::firstOrCreate([
                'name' => strtolower($name),
                'type' => ucwords($account_type),
                'description' => $description
            ]);
        }

        //Close opened CSV file
        fclose($handlefile);
    }

    public function general_legder_upload_file($filepath)
    {
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $handlefile = fopen($filepath, "r");
        fgetcsv($handlefile);//skip first line
        while(($data = fgetcsv($handlefile, '1000000',',')) != FALSE){
            $name = $data[0];
            $cod = $data[1];
            $status = $data[2]; 
 
            $type = AccountType::where('code',$cod)->first();
            $gcode = $cod."".mt_rand('111111','999999');
            
            GeneralLedger::firstOrCreate([
                'user_id' => Auth::user()->id,
                'branch_id' => $branch,
                'gl_name' => strtolower($name),
                'gl_code' => $gcode,
                'gl_type' => $type->name,
                'status' => $status
            ]);
        }

        //Close opened CSV file
        fclose($handlefile);
    }

    //customer to gl upload
    public function customer_general_legder_upload_file($filepath){
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $handlefile = fopen($filepath, "r");
        fgetcsv($handlefile);//skip first line
        while(($data = fgetcsv($handlefile, '1000000',',')) != FALSE){

            $num = count($data);
            if($num > 20){

                 return ['status' => false, 'msg' => 'uploaded files exceeds length of 20'];

            }else{

            $custacc = $data[0];
            $glcode = $data[1];
            $amount = $data[2]; 
            $desc = $data[3]; 
            
            $trx = 'debit';
            $trx2 = 'credit'; 

            $subcod = substr($glcode,0,2);// get first two digits of gl code
            $checkacctyp = AccountType::where('code',$subcod)->first();
            
            $usern = Auth::user()->last_name." ".Auth::user()->first_name;
            
            $trxref = $this->generatetrnxref('cgl');
            
            $getsetvalue = new Setting();

            $convrtamt = 0;
            
            $customerac = Customer::select('id','first_name','last_name','acctno','account_type','enable_email_alert','enable_sms_alert','exchangerate_id')->where('acctno',$custacc)->first();
            $customeracct = Saving::lockForUpdate()->where('customer_id',$customerac->id)->first();
            $gl = GeneralLedger::select('id','status','account_balance','gl_type','currency_id')->where('gl_code',$glcode)->lockForUpdate()->first();
            
            $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->lockForUpdate()->first();
            $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->lockForUpdate()->first();   

            if($customerac->exchangerate_id != $gl->currency_id){

                $this->upload_trx_status($branch,$customerac->id,$gl->id,$customeracct->account_balance,$amount,$trx,'cgl',Carbon::now(),'Currency mis-match','0','1');

            }
    
      if(!empty($customerac)){

            $chkcres = $this->checkCustomerRestriction($customerac->id);
              if($chkcres == false){
                  $chklien = $this->checkCustomerLienStatus($customerac->id);
                     if($chklien['status'] == false){
                     
            if($customeracct->account_balance >= $amount){
                
                    
           if($getsetvalue->getsettingskey('withdrawal_limit') == 0 || $amount < $getsetvalue->getsettingskey('withdrawal_limit')){

              $dedamount = $customeracct->account_balance - $amount;
                  $customeracct->account_balance = $dedamount;
                  $customeracct->save();
                  
                  
                    
                  if(!is_null($customerac->exchangerate_id)){
                    $this->checkforeigncurrncy($customerac->exchangerate_id,$amount,$trxref,'debit');
                 }else{
                   // if($customerac->account_type == '1'){//saving acct GL
                        if($glsavingdacct->status == '1'){
                               $this->gltransaction('deposit',$glsavingdacct,$amount,null);
                             $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $amount,'debit','core',$trxref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                       
                        }
                    
                //    }elseif($customerac->account_type == '2'){//current acct GL
                //        if($glcurrentacct->status == '1'){
                //        $this->gltransaction('deposit',$glcurrentacct,$amount,null);
                //    $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $amount,'debit','core',$trxref,$this->generatetrnxref('W'),'customer debited','approved',$usern);
                //        }
                //    }
                 }
                    
                  $this->create_saving_transaction(Auth::user()->id,$customerac->id,Auth::user()->branch_id,$amount,
                  $trx,'core','0',null,null,null,null,$trxref,$desc,'approved','2','trnsfer',$usern);
      
                 
              $this->tracktrails(Auth::user()->id,$branch,$usern,'upload general ledger','debit from an customer account');
      
            //   $this->upload_trx_status($branch,$customerac->id,$gl->id,$customeracct->account_balance,$amount,$trx,'cgl',Carbon::now(),null,'1','1');
           
              if($gl->status == 1){
                $this->credit_gl_account_transfer('cgl',$glcode,$amount,$branch,$gl->id,$trx2,'cgl',$trxref, $desc,'');

                $this->upload_trx_status($branch,$customerac->id,$gl->id,$customeracct->account_balance,$amount,$trx,'cgl',Carbon::now(),null,'1','1');

                // $usern = Auth::user()->last_name." ".Auth::user()->first_name;
                // $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','credited to a ledger account');

               }else{

                  if($gl->gl_type == "asset"){
                    $glsupens = GeneralLedger::where('gl_code',$getsetvalue->getsettingskey('asset_suspense'))->first();

                         $aspamount = $glsupens->account_balance + $amount;
                         $glsupens->account_balance = $aspamount;
                       $glsupens->save();
                    
                    
                    $this->create_saving_transaction_gl(Auth::user()->id,$glsupens->id,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref('susp'),'credited suspense account(asset)','approved',$usern);

                    $this->upload_trx_status($branch,$customerac->id,$glsupens->id,$customeracct->account_balance,$amount,$trx,'cgl',Carbon::now(),'inactive GL account, credited suspense account(asset)','0','1');

                    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','credited suspense account(asset)');
                  
                   }elseif($gl->gl_type == "liability"){
                       
                    $glsupens = GeneralLedger::where('gl_code',$getsetvalue->getsettingskey('liability_suspense'))->first();

                         $lispamount = $glsupens->account_balance + $amount;
                         $glsupens->account_balance = $lispamount;
                       $glsupens->save();
                    
                    $this->create_saving_transaction_gl(Auth::user()->id,$glsupens->id,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref('susp'),'credited suspense account(liability)','approved',$usern);

                    $this->upload_trx_status($branch,$customerac->id,$glsupens->id,$customeracct->account_balance,$amount,$trx,'cgl',Carbon::now(),'inactive GL account, credited suspense account(liability)','0','1');

                    
                    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','credited suspense account(liability)');

                   }elseif($gl->gl_type == "capital"){

                    $glsupens = GeneralLedger::where('gl_code',$getsetvalue->getsettingskey('capital_suspense'))->first();

                    
                         $cpspamount = $glsupens->account_balance + $amount;
                         $glsupens->account_balance = $cpspamount;
                       $glsupens->save();
                     
                    $this->create_saving_transaction_gl(Auth::user()->id,$glsupens->id,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref('susp'),'credited suspense account(capital)','approved',$usern);

                    $this->upload_trx_status($branch,$customerac->id,$glsupens->id,$customeracct->account_balance,$amount,$trx,'cgl',Carbon::now(),'inactive GL account, credited suspense account(capital)','0','1');

                    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','credited suspense account(capital)');

                   }elseif($gl->gl_type == "income"){

                    $glsupens = GeneralLedger::where('gl_code',$getsetvalue->getsettingskey('income_suspense'))->first();

                    
                         $inspamount = $glsupens->account_balance + $amount;
                         $glsupens->account_balance = $inspamount;
                       $glsupens->save();
                    
                    $this->create_saving_transaction_gl(Auth::user()->id,$glsupens->id,$branch,$amount,'credit','core',$trxref,$this->generatetrnxref('susp'),'credited suspense account(income)','approved',$usern);

                    $this->upload_trx_status($branch,$customerac->id,$glsupens->id,$customeracct->account_balance,$amount,$trx,'cgl',Carbon::now(),'inactive GL account, credited suspense account(income)','0','1');

                    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','credited suspense account(income)');

                   }elseif($gl->gl_type == "expense"){
                       
                    $glsupens = GeneralLedger::where('gl_code',$getsetvalue->getsettingskey('exps_suspense'))->first();

                    
                         $espamount = $glsupens->account_balance + $amount;
                         $glsupens->account_balance = $espamount;
                       $glsupens->save();
                    
                    $this->create_saving_transaction_gl(Auth::user()->id,$glsupens->id,$branch,$amount,'debit','core',$trxref,$this->generatetrnxref('susp'),'credited suspense account(expense)','approved',$usern);

                    $this->upload_trx_status($branch,$customerac->id,$glsupens->id,$customeracct->account_balance,$amount,$trx,'cgl',Carbon::now(),'inactive GL account, credited suspense account(expense)','0','1');

                    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','credited suspense account(expense)');
                   }           
                }
            }else{
                
                $this->create_saving_transaction(Auth::user()->id,$customerac->id,Auth::user()->branch_id,$amount,
                      $trx,'core','0',null,'0','cgl',null,$trxref,$desc,'pending','2','trnsfer',$usern.'(c)');
          
                      $this->credit_gl_account_transfer('cgl',$glcode,$amount,$branch,$gl->id,$trx2,'cgl',$trxref, $desc,'pending');

                      $this->upload_trx_status($branch,$customerac->id,$gl->id,$customeracct->account_balance,$amount,$trx,'cgl',Carbon::now(),'awaiting approval','1','1');

               }
                
            }else{
                $this->upload_trx_status($branch,$customerac->id,$gl->id,$customeracct->account_balance,$amount,$trx,'cgl',Carbon::now(),'insufficent fund','0','1');
            }
            
            }else{
                $this->upload_trx_status($branch,$customerac->id,$gl->id,$customeracct->account_balance,$amount,$trx,'cgl',Carbon::now(),'customer account lien('.$chklien['message'].')','0','1');
            }
            
            }else{
                $this->upload_trx_status($branch,$customerac->id,$gl->id,$customeracct->account_balance,$amount,$trx,'cgl',Carbon::now(),'customer account restricted','0','1');
            }
         
        //     $smsmsg = "Debit Amt: N".number_format($amount,2)."\n Desc: ".$desc." \n Avail Bal: N".number_format($customeracct->account_balance,2)."\n Date:" . date('Y-m-d') . "\n Ref: " . $trxref;
    
        //     if($customerac->enable_sms_alert){
        //         $this->sendSms(Auth::user()->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
        //         }

        //   if($customerac->enable_email_alert){
        // $msg =  "Debit Amt: N".number_format($amount,2)."<br> Desc: ".$desc." <br>Avail Bal: N". number_format($customeracct->account_balance,2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trxref;
        //  Email::create([
        //         'user_id' => $customerac->id,
        //         'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert',
        //         'message' => $msg,
        //         'recipient' => $customerac->email,
        //     ]);

        //     Mail::send(['html' => 'mails.sendmail'],[
        //         'msg' => $msg,
        //         'type' => 'Debit Transaction'
        //     ],function($mail)use($getsetvalue,$customerac){
        //         $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
        //          $mail->to($customerac->email);
        //         $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert');
        //     });
        //  }

            }else{
                $this->upload_trx_status($branch,$customerac->id,$gl->id,$customeracct->account_balance,$amount,$trx,'cgl',Carbon::now(),'invalid account','0','1');
            }
        }
    }

    return ["status" => "success", "message" => "Transaction Posted"]; 

        //Close opened CSV file
        fclose($handlefile);
    }

     //gl to customer upload
    public function general_legder_customer_upload_file($filepath){

        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        $handlefile = fopen($filepath, "r");
        fgetcsv($handlefile);//skip first line
        while(($data = fgetcsv($handlefile, '1000000',',')) != FALSE){

            
            $num = count($data);
            if($num > 30){

                 return ['status' => false, 'msg' => 'uploaded files exceeds length of 30'];

            }else{
            $glcode = $data[0];
            $custacc = $data[1];
            $amount = $data[2]; 
            $desc = $data[3]; 
            
            $trx = 'debit';
            $trx2 = 'credit';   

            $customerac = Customer::select('id','first_name','last_name','acctno','exchangerate_id')->where('acctno',$custacc)->first();
            
            $gl = GeneralLedger::select('id','status','account_balance','gl_type','currency_id')->where('gl_code',$glcode)->lockForUpdate()->first();
            
                if($customerac->exchangerate_id != $gl->currency_id){

                 $this->upload_trx_status($branch,$customerac->id,$gl->id,$customerac->account_balance,$amount,$trx,'glc',Carbon::now(),'Currency mis-match','0','1');

                }

            if(!empty($customerac)){
                
            $customeracct = Saving::lockForUpdate()->where('customer_id',$customerac->id)->first();
            

            $subcod = substr($glcode,0,2);// get first two digits of gl code
            $checkacctyp = AccountType::where('code',$subcod)->first();

            $trxref =$this->generatetrnxref('glc');
            
            $usern = Auth::user()->last_name." ".Auth::user()->first_name;
 
            $getsetvalue = new Setting();

        if(!empty($gl)){
            
                if($gl->account_balance >= $amount || $gl->account_balance >= 0){
                    if($gl->status == 1){
                        if($gl->gl_type == "asset"){
                            
                            if($getsetvalue->getsettingskey('deposit_limit') == 0 || $amount < $getsetvalue->getsettingskey('deposit_limit')){
                               
                                $addamount = $gl->account_balance + $amount;
                                $gl->account_balance = $addamount;
                                $gl->save();
                            
                            // $gl->account_balance = $dedamount;
                            // $gl->save(); $trxref

                            $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'debit','core',null,$trxref,$desc,'approved',$usern);
          
                           
                            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an account');
                        }else{
                            $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'debit','core',null,$trxref,$desc,'pending',$usern);
                            $this->upload_trx_status($branch,$customerac->id,$gl->id,$gl->account_balance,$amount,$trx,'glc',Carbon::now(),'awaiting approval','1','1');

                        }
                        }elseif($gl->gl_type == "liability"){
                            
                            if($getsetvalue->getsettingskey('deposit_limit') == 0 || $amount < $getsetvalue->getsettingskey('deposit_limit')){
                                $dedamount = $gl->account_balance - $amount;
                            $gl->account_balance = $dedamount;
                            $gl->save();
      
                            $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'debit','core',null,$trxref,$desc,'approved',$usern);
          
                            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited liability account');
                            
                        }else{

                                $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'debit','core',null,$trxref,$desc,'pending',$usern);
                                $this->upload_trx_status($branch,$customerac->id,$gl->id,$gl->account_balance,$amount,$trx,'glc',Carbon::now(),'awaiting approval','1','1');

                            }
                        }elseif($gl->gl_type == "capital"){
                            
                            if($getsetvalue->getsettingskey('deposit_limit') == 0 || $amount < $getsetvalue->getsettingskey('deposit_limit')){
                              
                                $dedamount = $gl->account_balance - $amount;
                            $gl->account_balance = $dedamount;
                            $gl->save();
      
                            $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'debit','core',null,$trxref,$desc,'approved',$usern);
          
                            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited capital account');
                            }else{

                                $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'debit','core',null,$trxref,$desc,'pending',$usern);
                                $this->upload_trx_status($branch,$customerac->id,$gl->id,$gl->account_balance,$amount,$trx,'glc',Carbon::now(),'awaiting approval','1','1');
                            }

                        }elseif($gl->gl_type == "income"){
                           
                            if($getsetvalue->getsettingskey('deposit_limit') == 0 || $amount < $getsetvalue->getsettingskey('deposit_limit')){
                              
                                $dedamount = $gl->account_balance - $amount;
                            $gl->account_balance = $dedamount;
                            $gl->save();
      
                            $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'debit','core',null,$trxref,$desc,'approved',$usern);
          
                            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debit income account');
                            }else{
                                $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'debit','core',null,$trxref,$desc,'pending',$usern);
                                $this->upload_trx_status($branch,$customerac->id,$gl->id,$gl->account_balance,$amount,$trx,'glc',Carbon::now(),'awaiting approval','1','1');
                            }
                        }elseif($gl->gl_type == "expense"){
                            
                            if($getsetvalue->getsettingskey('deposit_limit') == 0 || $amount < $getsetvalue->getsettingskey('deposit_limit')){

                                $addamount = $gl->account_balance + $amount;
                                $gl->account_balance = $addamount;
                                $gl->save();
                            
                            // $gl->account_balance = $dedamount;
                            // $gl->save();
      
                            $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'debit','core',null,$trxref,$desc,'approved',$usern);
          
                            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited expense account');
                        }else{
                            $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'debit','core',null,$trxref,$desc,'pending',$usern);
                            $this->upload_trx_status($branch,$customerac->id,$gl->id,$gl->account_balance,$amount,$trx,'glc',Carbon::now(),'awaiting approval','1','1');
                        }
                      }

                            $credittnx = $this->credit_account_transfer($amount,$customerac->id,'glc',$trx2,$trxref,$desc,$branch);
                      

                        if($credittnx['status'] == true){
                            //$this->upload_trx_status($branch,$customerac->id,$gl->id,$gl->account_balance,$amount,$trx,'glc',Carbon::now(),$credittnx['msg'],'1','1');
                        }else{
                            if($gl->gl_type == "asset"){

                                $dedamount = $gl->account_balance - $amount;
                                $gl->account_balance = $dedamount;
                                $gl->save();
    
                                $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'credit','core',null,$trxref,$desc,'approved',$usern);
              
                               
                                $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an account reversed');
        
                            }elseif($gl->gl_type == "liability"){

                                $addamount = $gl->account_balance + $amount;
                                $gl->account_balance = $addamount;
                                $gl->save();
          
                                $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'credit','core',null,$trxref,$desc,'approved',$usern);
              
                                $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited liability account reversed');
        
                            }elseif($gl->gl_type == "capital"){

                                $addamount = $gl->account_balance + $amount;
                                $gl->account_balance = $addamount;
                                $gl->save();
          
                                $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'credit','core',null,$trxref,$desc,'approved',$usern);
              
                                $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited capital account reversed');
                           
                            }elseif($gl->gl_type == "income"){
                                
                                $addamount = $gl->account_balance + $amount;
                                $gl->account_balance = $addamount;
                                $gl->save();
          
                                $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'credit','core',null,$trxref,$desc,'approved',$usern);
              
                                $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debit income account reversed');
        
                            }elseif($gl->gl_type == "expense"){
                    
                                $dedamount = $gl->account_balance - $amount;
                                    $gl->account_balance = $dedamount;
                                    $gl->save();
                                
          
                                $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'credit','core',null,$trxref,$desc,'approved',$usern);
              
                                $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited expense account reversed');
                            }
                        }

                    }else{
                        $this->upload_trx_status($branch,$customerac->id,$gl->id,$gl->account_balance,$amount,$trx,'glc',Carbon::now(),'inactive GL account','0','1');
                    }
                    
                    }else{
                        $this->upload_trx_status($branch,$customerac->id,$gl->id,$gl->account_balance,$amount,$trx,'glc',Carbon::now(),'insufficent fund','0','1');
                    }
                    
             }else{

                $this->upload_trx_status($branch,$customerac->id,$gl->id,$gl->account_balance,$amount,$trx,'glc',Carbon::now(),'invalid GL Code','0','1');
              
            }
           
            }else{
            $this->upload_trx_status($branch,$customerac->id,$gl->id,$gl->account_balance,$amount,$trx,'glc',Carbon::now(),'incorrect account number('.$custacc.')','0','1'); 
        } 
  }
}

        return ["status" => "success", "message" => "Transaction Posted"]; 

        //Close opened CSV file
        fclose($handlefile);
    }


   //GL to GL uploads
   public function general_legder_gl_upload_file($filepath){

    $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

    $handlefile = fopen($filepath, "r");
    fgetcsv($handlefile);//skip first line
    while(($data = fgetcsv($handlefile, '1000000',',')) != FALSE){

        
        $num = count($data);
        if($num > 20){

             return ['status' => false, 'msg' => 'uploaded files exceeds length of 20'];

        }else{

        $glcode = $data[0];
        $amount = $data[1]; 
        $glcodee2 = $data[2];
        $desc = $data[3]; 
       
        $subcod = substr($glcode,0,2);// get first two digits of gl code
        $checkacctyp = AccountType::where('code',$subcod)->first();
        
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;

        $gl = GeneralLedger::select('id','status','account_balance','gl_type','currency_id')->where('gl_code',$glcode)->lockForUpdate()->first();
        $glllcd2 = GeneralLedger::select('id','status','account_balance','gl_type','currency_id')->where('gl_code',$glcodee2)->lockForUpdate()->first();

         $getsetvalue = new Setting();
         
        $trxref = $this->generatetrnxref('gltogl');

        //if($gl->status == 1){
       // if($trxtype == "debit"){ 

            if($glllcd2->currency_id != $gl->currency_id){

                   $this->upload_trx_status($branch,$glllcd2->id,$gl->id,$gl->account_balance,$amount,$trxref,'gltogl',Carbon::now(),'Currency mis-match','0','1');

            }
            
            $dedamount = $gl->account_balance - $amount;
            $addamount = $gl->account_balance + $amount;

         if($gl->status == 1){

            if($getsetvalue->getsettingskey('withdrawal_limit') == 0 || $amount < $getsetvalue->getsettingskey('withdrawal_limit')){

                if($gl->gl_type == "asset"){
                 
                      $gl->account_balance = $addamount;
                      $gl->save();
                  

                    $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'debit','core',null,$trxref,$desc,'approved',$usern);
  
                    
                    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','deposited to an account');

                }elseif($gl->gl_type == "liability"){
                    $gl->account_balance = $dedamount;
                    $gl->save();

                    $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'debit','core',null,$trxref,$desc,'approved',$usern);
  
                    
                    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited liability account');

                }elseif($gl->gl_type == "capital"){
                    $gl->account_balance = $dedamount;
                    $gl->save();

                    $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'debit','core',null,$trxref,$desc,'approved',$usern);
  
                    
                    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited capital account');
               
                }elseif($gl->gl_type == "income"){
                    
                  $gl->account_balance = $dedamount;
                  $gl->save();

                    $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'debit','core',null,$trxref,$desc,'approved',$usern);
  
                  
                    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debit income account');

                }elseif($gl->gl_type == "expense"){
                  
                      $gl->account_balance = $addamount;
                      $gl->save();
                 

                    $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'debit','core',null,$trxref,$desc,'approved',$usern);
  
                    $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited expense account');
                }

               $this->credit_gl_account_transfer("gltogl",$glcodee2,$amount,$branch,$glllcd2->id,'debit','gltogl',$trxref,$desc,'');


              }else{

                  $this->create_saving_transaction_gl(Auth::user()->id,$gl->id,$branch,$amount,'debit','core',null,$trxref,$desc,'pending',$usern);

                  $this->credit_gl_account_transfer("gltogl",$glcodee2,$amount,$branch,$glllcd2->id,'debit','gltogl',$trxref,$desc,'pending');
                  
                    $this->upload_trx_status($branch,$glllcd2->id,$gl->id,$gl->account_balance,$amount,$trxref,'gltogl',Carbon::now(),'awaiting approval','1','1');
              }
           
            }else{
                $this->upload_trx_status($branch,$glllcd2->id,$gl->id,$gl->account_balance,$amount,$trxref,'gltogl',Carbon::now(),'inactive GL account','0','1');
            }

             
            
            }
    }

    return ["status" => "success", "message" => "Transaction Posted"]; 
    
    //Close opened CSV file
    fclose($handlefile);
}
    
     public function gl_reversal(){
        return view('accountmgt.gl_reversal');
    }

    public function gl_check_transref(){
    
        if(request()->txntype == "glc"){
           
            $getgltrnx = SavingsTransactionGL::where('reference_no',request()->reference)->first();
      
//|| $gettranslip->transfer_type != "glc"
            if(empty($getgltrnx)){
                return array(
                    'status' => '0',
                    'msg' => 'reference number not found'
                );
            }else{
                $gettranslip = SavingsTransaction::where('slip',$getgltrnx->reference_no)->first();
                if(empty($gettranslip)){
                    return array(
                        'status' => '0',
                        'msg' => 'reference number not found'
                    );
                }else{
                    return array(
                        'status' => '1',
                    'txrtype' =>   $gettranslip->type,
                    'amount' => $gettranslip->amount,
                    'txrdate' => date("d-m-y",strtotime($gettranslip->created_at))." at ".date("h:ia",strtotime($gettranslip->created_at)),
                    'custmerid' => $gettranslip->customer_id,
                    'glid' => $getgltrnx->general_ledger_id,
                    'glid2' => null,
                    'ttpe' => $gettranslip->transfer_type,
                    'msg' => 'verified'
                    ); 
                }
                
            }
        }elseif(request()->txntype == "cgl"){
            $gettranslip = SavingsTransaction::where('reference_no',request()->reference)->first();
            

            if(empty($gettranslip)){
                return array(
                    'status' => '0',
                    'msg' => 'reference number not found'
                );
            }else{
                $getgltrnx = SavingsTransactionGL::where('slip',$gettranslip->reference_no)->get();

                return array(
                    'status' => '1',
                   'txrtype' =>   $gettranslip->type,
                   'amount' => $gettranslip->amount,
                   'txrdate' => date("d-m-y",strtotime($gettranslip->created_at))." at ".date("h:ia",strtotime($gettranslip->created_at)),
                   'custmerid' => $gettranslip->customer_id,
                   'glid' => $getgltrnx[0]->general_ledger_id,
                   'glid2' => $getgltrnx[1]->general_ledger_id,
                   'ttpe' => $gettranslip->transfer_type,
                   'msg' => 'verified'
                ); 
            }  
        }elseif(request()->txntype == "gltogl"){
            $getgltrnxone = SavingsTransactionGL::select('general_ledger_id','amount','created_at')->where('reference_no',request()->reference)->first();
       
            if(empty($getgltrnxone)){
                return array(
                    'status' => '0',
                    'msg' => 'reference number not found'
                );
            }else{
                $getgltrnxtwo = SavingsTransactionGL::select('general_ledger_id','amount')->where('slip',request()->reference)->first();

                // $glcd = GeneralLedger::where('id', $getgltrnxone->general_ledger_id)->where('status','1')->first();
                // $glcd2 = GeneralLedger::where('id',)->where('status','1')->first();

                return array(
                'status' => '1',
                'amount' => $getgltrnxone->amount,
                'txrdate' => date("d-m-y",strtotime($getgltrnxone->created_at))." at ".date("h:ia",strtotime($getgltrnxone->created_at)),
                'glid' => $getgltrnxone->general_ledger_id,
                'glid2' => $getgltrnxtwo->general_ledger_id,
                'ttpe' => "gltogl",
                ); 
            }
        }
        
        
    }

    public function gl_reversal_posting(Request $r){
        
       $lock = Cache::lock('glaccrvpst-'.mt_rand('1111','9999'),2);
          
       try{

          //if($lock->get()){  

              $lock->block(1);

            DB::beginTransaction();

        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;
            
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        
        $getsetvalue = new Setting();
       
        $convrtamt = 0;

        $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->lockForUpdate()->first();
        $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->lockForUpdate()->first();
          
        $convrtamt = 0;
        
        if (preg_match('/[\'^£$%&*}{@#~?><>()"|=_+¬]/', $r->description)) {
                return ['status' => '0', 'msg' => "No special character allowed in narration"];
            }

        if($r->options == "glc"){ //GL account to customer
           $this->logInfo("general ledger to customer",$r->all());
           
               $this->validate($r,[
                   'amount' => ['required','string','numeric','gt:0'],
               ]);
               
               $glacct = GeneralLedger::where('id',$r->gldger_id)->first();

               // $checkacctyp = AccountType::where('name',)->first();

               $trxref = $this->generatetrnxref('glc');

                $cust = Customer::where('id',$r->customerid)->first();  

                if($cust->exchangerate_id != $glacct->currency_id){
                     return array('status' => '0', 'msg' => "Currency mis-match");
                }

                   if($glacct->gl_type == "asset"){
                    
                       $addamount = $glacct->account_balance - $r->amount;
                           $glacct->account_balance = $addamount;
                           $glacct->save();
                       
                       
                       $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'credit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
   
                       $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','asset account reversal');

                   }elseif($glacct->gl_type == "liability"){
                              
                                   $dedamount = $glacct->account_balance + $r->amount;
                               $glacct->account_balance = $dedamount;
                               $glacct->save();
       
                               $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'credit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
           
                               $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','liability account reversal');
                       
                   }elseif($glacct->gl_type == "capital"){
                               
                                   $dedamount = $glacct->account_balance + $r->amount;
                               $glacct->account_balance = $dedamount;
                               $glacct->save();
       
                               $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'credit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
           
                               $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','capital account reversal');
                            
                   }elseif($glacct->gl_type == "income"){
                                                      
                                   $dedamount = $glacct->account_balance + $r->amount;
                               $glacct->account_balance = $dedamount;
                               $glacct->save();
       
                               $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'credit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
           
                               
                               $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','income account reversal');
                        
                   }elseif($glacct->gl_type == "expense"){
                       
                       $addamount = $glacct->account_balance - $r->amount;
                           $glacct->account_balance = $addamount;
                           $glacct->save();
                       
 
                       $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'credit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
     
                       $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','expense account reversal');
        
                   }
                         
                    $creditcut = $this->reverse_account_transfer($r->amount,$r->customerid,'glc',$r->dbit2,$trxref,str_replace("'", "",$r->description));
                    
                        if($creditcut['status'] == false){

                                if($glacct->gl_type == "asset"){
    
                                    $dedamount = $glacct->account_balance + $r->amount;
                                $glacct->account_balance = $dedamount;
                                $glacct->save();
                             
                            
                            $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
          
                           
                            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited asset account reversed');
    
                        }elseif($glacct->gl_type == "liability"){
    
                            $addamount = $glacct->account_balance - $r->amount;
                            $glacct->account_balance = $addamount;
                            $glacct->save();
      
                            $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
          
                            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited liability account reversed');
    
                        }elseif($glacct->gl_type == "capital"){
    
                            $addamount = $glacct->account_balance - $r->amount;
                            $glacct->account_balance = $addamount;
                            $glacct->save();
      
                            $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
          
                            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited capital account reversed');
                       
                        }elseif($glacct->gl_type == "income"){
    
                            $addamount = $glacct->account_balance - $r->amount;
                            $glacct->account_balance = $addamount;
                            $glacct->save();
      
                            $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
          
                            
                            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debit income account reversed');
    
                        }elseif($glacct->gl_type == "expense"){
    
                             $dedamount = $glacct->account_balance + $r->amount;
                            $glacct->account_balance = $dedamount;
                            $glacct->save();
      
                            $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'debit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
          
                            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','debited expense account reveresed');
                        }

                         DB::commit();
                           
                           return array(
                            'status' => '0',
                            'msg' => $creditcut['msg']
                           );
                           
                       }else{
                            return array(
                          'status' => 'success',
                          'msg' => $creditcut['msg']
                           );
                       }
                 
           }elseif($r->options == "cgl"){ //customer to GL account

            $this->logInfo("customer to general legder posting",$r->all());
            $trxref2 = $this->generatetrnxref('cgl');

           $this->validate($r,[
                   'amount' => ['required','string','numeric','gt:0'],
               ]);

               $cust = Customer::where('id',$r->customerid)->first();

               $glacct = GeneralLedger::where('gl_code',$r->gl_code2)->first();

                if($cust->exchangerate_id != $glacct->currency_id){
                     return array('status' => '0', 'msg' => "Currency mis-match");
                }

               $chkcres = $this->checkCustomerRestriction($r->customerid);
                   if($chkcres == true){
                       $this->tracktrails('1','1',$usern,'customer','Account Restricted');
                   return array('status' => '0','msg' => 'Customer Account Has Been Restricted');
                   }
                   
                $chklien = $this->checkCustomerLienStatus($r->customerid);
                   if($chklien['status'] == true && $chklien['lien'] == 2){
                       $this->tracktrails('1','1',$usern,'customer','Account has been lien');
                    return  array('status' => '0','msg' => 'Customer Account Has Been Lien('.$chklien['message'].')...please contact support');
                   }

                  
               $customeracct = Saving::lockForUpdate()->where('customer_id',$r->customerid)->first();
      
                       $dedamount = $customeracct->account_balance + $r->amount;
                     $customeracct->account_balance = $dedamount;
                     $customeracct->save();
         
                     $this->create_saving_transaction(Auth::user()->id,$r->customerid,Auth::user()->branch_id,$r->amount,
                     'credit','core','0',null,null,'cgl',null,$trxref2,str_replace("'", "",$r->description),'approved','2','trnsfer',$usern);
                       
                     if(!is_null($cust->exchangerate_id)){
                       $this->checkforeigncurrncy($cust->exchangerate_id,$r->amount,$trxref2,'credit');
                     }else{
                       
                          // if($cust->account_type == '1'){//saving acct GL
                           
                            if($glsavingdacct->status == '1'){
                               $this->gltransaction('withdrawal',$glsavingdacct,$r->amount,null);
                                 $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $r->amount,'credit','core',$trxref2,$this->generatetrnxref('cr'),'customer credited','approved',$usern);
                           }
                           
                        //    }elseif($cust->account_type == '2'){//current acct GL
                           
                        //        if($glcurrentacct->status == '1'){
                        //            $this->gltransaction('withdrawal',$glcurrentacct,$r->amount,null);
                        //    $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $r->amount,'credit','core',$trxref2,$this->generatetrnxref('cr'),'customer credited','approved',$usern);
                               
                        //        }
                        //    }
                     }

                    
                 $this->tracktrails(Auth::user()->id,Auth::user()->branch_id,$usern,'general ledger','account reversal');
         
                    $this->reverse_gl_account_transfer($r->options,$r->gl_code2,$r->amount,$branch,$r->gldger_id2,$r->dbit2,'cgl',$trxref2,str_replace("'", "",$r->description),'');
                 
                    DB::commit();

                    $smsmsg = "Credit Amt: N".number_format($r->amount,2)."\n Desc: ".str_replace("'", "",$r->description)." \n Avail Bal: N".number_format($customeracct->account_balance,2)."\n Date:" . date('Y-m-d') . "\n Ref: " . $trxref2;
   
           if($cust->enable_sms_alert){
               $this->sendSms(Auth::user()->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
               }

                  if($cust->enable_email_alert){
                   $msg =  "Credit Amt: N".number_format($r->amount,2)."<br> Desc: ".str_replace("'", "",$r->description)." <br>Avail Bal: N". number_format($customeracct->account_balance,2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trxref2;
                    Email::create([
                           'user_id' => $cust->id,
                           'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Debit Alert',
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
                       'msg' => 'Reversal Posted Successful'
                   );
                       
           }elseif($r->options == "gltogl"){
               
               $this->logInfo("gl to gl Posting",$r->all());

               $this->validate($r,[
                   'amount' => ['required','string','numeric','gt:0'],
               ]);
               
               $subcod = substr($r->gl_code,0,2);// get first two digits of gl code
               $checkacctyp = AccountType::where('code',$subcod)->first();

               $trxref = $this->generatetrnxref('gltogl');
               $glacct = GeneralLedger::where('id',$r->gldger_id)->first();
   
                 $glacccuurtyt = GeneralLedger::where('gl_code',$r->gl_code2)->first();

                if($glacccuurtyt->currency_id != $glacct->currency_id){
                     return array('status' => '0', 'msg' => "Currency mis-match");
                }
               
                   $dedamount = $glacct->account_balance + $r->amount;
                   $addamount = $glacct->account_balance - $r->amount;
                   
                   
                     if($glacct->gl_type == "asset"){
                      
                           $glacct->account_balance = $addamount;
                           $glacct->save();
                     
                         $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'credit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
          
                         $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','asset account reversal');
 
                     }elseif($glacct->gl_type == "liability"){
                         $glacct->account_balance = $dedamount;
                         $glacct->save();
   
                         $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'credit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
       
                         
                         $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','liability account reversal');
 
                     }elseif($glacct->gl_type == "capital"){
                         $glacct->account_balance = $dedamount;
                         $glacct->save();
   
                         $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'credit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
       
                         
                         $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','capital account reversal');
                    
                     }elseif($glacct->gl_type == "income"){
                         
                       $glacct->account_balance = $dedamount;
                       $glacct->save();
   
                         $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'credit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
       
                       
                         $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','income account reversal');
 
                     }elseif($glacct->gl_type == "expense"){
                       
                           $glacct->account_balance = $addamount;
                           $glacct->save();
                      
   
                         $this->create_saving_transaction_gl(Auth::user()->id,$r->gldger_id,$branch,$r->amount,'credit','core',null,$trxref,str_replace("'", "",$r->description),'approved',$usern);
       
                         $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','expense account reversal');
                     }

                    $this->reverse_gl_account_transfer($r->options,$r->gl_code2,$r->amount,$branch,$r->gldger_id2,$r->dbit2,'gltogl',$trxref,str_replace("'", "",$r->description),'');

                    DB::commit();

               return array(
                   'status' => 'success',
                   'msg' => 'Reversal Posted Successful'
               );
            
           }
           
        //    $lock->release();
        // }//lock

        }catch(\Exception $e){

            DB::rollBack();

            $this->loginfo("error gl reversal", "Error processing GL reversal");

             return array('status' => '0', 'msg' => "Error processing GL reversal");

       }
    }

    //reversal
    public function reverse_account_transfer($amount,$cid,$inita,$tran_type2,$trxref,$desc){
      
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;

        $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->lockForUpdate()->first();
        $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->lockForUpdate()->first();   
        
         $chkcres = $this->checkCustomerRestriction($cid);
         $chklien = $this->checkCustomerLienStatus($cid);

         $getsetvalue = new Setting();
         $convrtamt = 0;

         $cust = Customer::where('id',$cid)->first();
        if($chkcres == true){
    
            $this->tracktrails('1','1',$usern,'customer','Account Restricted');
            
        return array('status' => 'false','msg' => 'Customer Account Has Been Restricted');
            
        }elseif($chklien['status'] == true && $chklien['lien'] == 1){
           
            $this->tracktrails('1','1',$usern,'customer','Account Restricted');
            
           return array('status' => 'false','msg' => 'Customer Account Has Been Lien('.$chklien['message'].')...please contact support');
        
        } else{

            $validateuserbalance = $this->validatecustomerbalance($cid,$amount);
            
            if($validateuserbalance["status"] == false){
                $this->tracktrails('1','1',$usern,'customer',$validateuserbalance["message"]);
                return ['status' => 'false', 'msg' => $validateuserbalance["message"]];
            }  

        $customeracct2 = Saving::lockForUpdate()->where('customer_id',$cid)->first();
        
        $cramount = $customeracct2->account_balance - $amount;
             $customeracct2->account_balance = $cramount;
           $customeracct2->save();
  
        $this->create_saving_transaction(Auth::user()->id,$cid,Auth::user()->branch_id,$amount,
        'debit','core','0',$trxref,null,$inita,null,$this->generatetrnxref('Dr'),$desc,'approved','1','trnsfer',$usern);
                
        if(!is_null($cust->exchangerate_id)){
            $this->checkforeigncurrncy($cust->exchangerate_id,$amount,$trxref,'debit');
         }else{
            //if($cust->account_type == '1'){//saving acct GL null,$trxref
                if($glsavingdacct->status == '1'){
                    $this->gltransaction('deposit',$glsavingdacct,$amount,null);
        $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $amount,'debit','core',$trxref,$this->generatetrnxref('Dr'),'customer debited','approved',$usern);
            
                }
            
        // }elseif($cust->account_type == '2'){//current acct GL
        //      if($glcurrentacct->status == '1'){
        //     $this->gltransaction('deposit',$glcurrentacct,$amount,null);
        // $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $amount,'debit','core',$trxref,$this->generatetrnxref('Dr'),'customer debited','approved',$usern);
        //      }
        // }
        }

        
        $this->tracktrails(Auth::user()->id,Auth::user()->branch_id,$usern,'general ledger','account reversal');

        $smsmsg = "Debit Amt: N".number_format($amount,2)."\n Desc: ".$desc." \n Avail Bal: N".number_format($customeracct2->account_balance,2)."\n Date:" . date('Y-m-d') . "\n Ref: " . $trxref;

        if($cust->enable_sms_alert){
            $this->sendSms(Auth::user()->phone,$smsmsg,$getsetvalue->getsettingskey('active_sms'));//send sms
            }

     if($cust->enable_email_alert){
    $msg =  "Debit Amt: N".number_format($amount,2)."<br> Desc: ".$desc." <br>Avail Bal: N". number_format($customeracct2->account_balance,2)."<br> Date: ".date("Y-m-d")."<br>Ref: ".$trxref;
     Email::create([
            'user_id' => $cust->id,
            'subject' => ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert',
            'message' => $msg,
            'recipient' => $cust->email,
        ]);

        Mail::send(['html' => 'mails.sendmail'],[
            'msg' => $msg,
            'type' => 'Debit Transaction'
        ],function($mail)use($getsetvalue,$cust){
            $mail->from($getsetvalue->getsettingskey('company_email'),ucwords($getsetvalue->getsettingskey('company_name')));
             $mail->to($cust->email);
            $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')).' Credit Alert');
        });
     }
     
             return array('status' => 'success','msg' => 'Reversal Posted Successful');
             
        }
    }

    //reverse general ledger
    public function reverse_gl_account_transfer($opt,$glcode,$amount,$branch,$glid,$dbit2,$inita,$trxref,$desc,$status){
        
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;

        $subcod = substr($glcode,0,2);// get first two digits of gl code
        $checkacctyp = AccountType::where('code',$subcod)->first();
        $glacct2 = GeneralLedger::where('id',$glid)->first();
        
        if($opt == "cgl"){
            
         if($glacct2->gl_type == "asset"){
            
                 $astamount = $glacct2->account_balance + $amount;
                 $glacct2->account_balance = $astamount;
               $glacct2->save();
               
            $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'debit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
    
            
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','asset account reversal');
            
            
        }elseif($glacct2->gl_type == "liability"){
           
                 $liamount = $glacct2->account_balance - $amount;
                 $glacct2->account_balance = $liamount;
               $glacct2->save();
            

            $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'debit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
    
           
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','liability account reversal');
            
         }elseif($glacct2->gl_type == "capital"){

                 $cpamount = $glacct2->account_balance - $amount;
                 $glacct2->account_balance = $cpamount;
               $glacct2->save();
            

            $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'debit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
    
          
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','capital account reversal');

         }elseif($glacct2->gl_type == "income"){
           
                 $inamount = $glacct2->account_balance - $amount;
                 $glacct2->account_balance = $inamount;
               $glacct2->save();
            

            $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'debit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
    
           
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','income account reversal');
             
         }elseif($glacct2->gl_type == "expense"){
        
             $eamount = $glacct2->account_balance + $amount;
             $glacct2->account_balance = $eamount;
               $glacct2->save();
               
            $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'debit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);

            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','expense account reversal');
     
        }
        }elseif($opt == "gltogl"){
            
            
         if($glacct2->gl_type == "asset"){
            
             $asglamount = $glacct2->account_balance + $amount;
             $glacct2->account_balance = $asglamount;
              $glacct2->save();
               
            $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'debit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
    
            
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','asset account reversal');
            
        }elseif($glacct2->gl_type == "liability"){
            
                $liglamount = $glacct2->account_balance - $amount;
                 $glacct2->account_balance = $liglamount;
               $glacct2->save();
            

            $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'debit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
    
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','liability account reversal');
            

         }elseif($glacct2->gl_type == "capital"){
            
                 $cpglamount = $glacct2->account_balance - $amount;
                 $glacct2->account_balance = $cpglamount;
               $glacct2->save();
            

            $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'debit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
    
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','capital account reversal');
            
         }elseif($glacct2->gl_type == "income"){
            
                 $inglamount = $glacct2->account_balance - $amount;
                 $glacct2->account_balance = $inglamount;
               $glacct2->save();
            

            $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'debit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
    
            
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','income account reversal');
            
         }elseif($glacct2->gl_type == "expense"){
            
            $eglamount = $glacct2->account_balance + $amount;
            $glacct2->account_balance = $eglamount;
              $glacct2->save();
              
            $this->create_saving_transaction_gl(Auth::user()->id,$glid,$branch,$amount,'debit','core',$trxref,$this->generatetrnxref($inita),$desc,'approved',$usern);
    
          
            $this->tracktrails(Auth::user()->id,$branch,$usern,'general ledger','expense account reversal');
            
         }
         
        }
       
    }
}//endclass

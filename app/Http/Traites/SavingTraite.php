<?php 
namespace App\Http\Traites;

use App\Models\Beneficiary;
use App\Models\Customer;
use App\Models\GeneralLedger;
use App\Models\Saving;
use App\Models\Exchangerate;
use App\Models\SavingsTransaction;
use App\Models\SavingsTransactionGL;
use App\Models\Setting;
use App\Models\Loan;
use App\Models\OutstandingLoan;
use App\Models\LoanRepayment;
use Carbon\Carbon;
use App\Models\Upload_transaction_status;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

trait SavingTraite{
     
  public function create_account($u,$cus,$acty){
    $checksav = Saving::where('customer_id',$cus)->first();
    if(empty($checksav)){
      Saving::Create([
        'user_id' => $u,
        'customer_id' => $cus,
        'savings_product_id' => $acty,
        'account_balance' => '0'
    ]);
    }else{
     
       Saving::where('customer_id',$cus)->update([
         'user_id' => $u,
          'savings_product_id' => $acty
       ]);
    
    }
    
  }

  public function check_transaction_slip($slip){
      $slipno = SavingsTransaction::where('slip',$slip)->first();
      if($slipno){
        return true;
      }else{
        return false;
      }
  }

  public function check_transaction_reference($ref){
    $refno = SavingsTransaction::where('reference_no',$ref)->first();
    if($refno){
      return true;
    }else{
      return false;
    }
}

public function check_account_status($acctno){
  $acctstat = Customer::where('acctno',$acctno)->first();

  if($acctstat->status == "1"){
    return true;
  }else{
    $this->checkaccountstatustype($acctstat->status);
  }
}

  public function create_saving_transaction($uid,$cid,$bid,$amt,$typ,$dv,$sysintst,$slip,$fddy,$mtudte,$dacct,$ref,$nte,$status,$sta,$txntyp,$nme){
    $trns = SavingsTransaction::create([
      'user_id' => $uid,
      'customer_id' => $cid,
      'branch_id' => $bid,
      'amount' => $amt,
      'type' => $typ,
      'device' => $dv,
      'system_interest' => $sysintst,
      'slip' => $slip,
      'is_approve' => $fddy,
      'transfer_type' => $mtudte,
      'destination_account' => $dacct,
      'reference_no' => $ref,
      'notes' => $nte,
      'status' => $status,
      'status_type' => $sta,
      'trnx_type' => $txntyp,
      'initiated_by' => $nme,
      'approve_by' => $nme
    ]);

  }
  
   public function updateTransactionAndAddTrnxcharges($uid,$cid,$bid,$amt,$typ,$dv,$sysintst,$slip,$fddy,$mtudte,$ref,$nwnte,$nte,$status,$sta,$nme,$dacct){
    $trnxupdate = SavingsTransaction::where('reference_no',$ref)->first();
    $trnxupdate->status = $status;
    $trnxupdate->notes = $nwnte;
    !empty($nme) ?: $trnxupdate->initiated_by = $nme;
    $trnxupdate->destination_account = $dacct;
    $trnxupdate->save();

   if($amt > 0){
        $trns = SavingsTransaction::create([
      'user_id' => $uid,
      'customer_id' => $cid,
      'branch_id' => $bid,
      'amount' => $amt,
      'type' => $typ,
      'device' => $dv,
      'system_interest' => $sysintst,
      'slip' => $slip,
      'is_approve' => $fddy,
      'transfer_type' => $mtudte,
      'reference_no' => $ref,
      'notes' => $nte,
      'status' => $status,
      'status_type' => $sta,
      'initiated_by' => $nme
    ]);
   }
  }


public function create_saving_transaction_gl($uid,$cid,$bid,$amt,$typ,$dv,$slip,$ref,$nte,$status,$nme){
    SavingsTransactionGL::create([
      'user_id' => $uid,
      'general_ledger_id' => $cid,
      'branch_id' => $bid,
      'amount' => $amt,
      'type' => $typ,
      'device' => $dv,
      'slip' => $slip,
      'reference_no' => $ref,
      'notes' => $nte,
      'status' => $status,
      'initiated_by' => $nme
    ]);
  }

  public function checkOutstandingCustomerLoan($customerid,$amount){

    $trxref = $this->generatetrnxref('OL');

    $customer = Customer::where('id',$customerid)->first();
    $loans = Loan::where('customer_id',$customerid)->get();
     $customeracct = Saving::lockForUpdate()->where('customer_id',$customerid)->first();

     $glsavingdacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20993097')->lockForUpdate()->first();
     $glcurrentacct = GeneralLedger::select('id','status','account_balance')->where('gl_code','20639526')->lockForUpdate()->first();
     $glacctloansuspense = GeneralLedger::select('id','status','account_balance')->where("gl_code","10816440")->lockForUpdate()->first();
     
     $usern = Auth::check() ? Auth::user()->last_name." ".Auth::user()->first_name : '';

    $repayment = new LoanRepayment();
       // foreach($loans as $loan){
       if($loans->count() > 0){

         $loan = Loan::where(function ($query) use ($customerid) {
                    $query->where('customer_id', $customerid)
                        ->where('status', 'disbursed');
                })
                ->orWhere(function ($query) use ($customerid) {
                    $query->where('customer_id', $customerid)
                        ->whereDate('maturity_date', '<', date("Y-m-d"));
                })
                ->orderBy('id', 'ASC')
                ->first();

      $outloan = OutstandingLoan::where('loan_id',$loan->id)->where('customer_id',$customerid)->first();

      if(!empty($outloan) && $outloan->amount > 0){

        if($amount > $outloan->amount || $amount == $outloan->amount){

         $oysntt = $amount - $outloan->amount;
          $outloan->amount = $oysntt >= 0 ? 0 : $oysntt;
          $outloan->save();


          $repayment->accountofficer_id = $loan->accountofficer_id;
          $repayment->amount = $amount;
          $repayment->loan_id = $loan->id;
          $repayment->customer_id = $loan->customer_id;
          $repayment->branch_id = $loan->branch_id;
          $repayment->repayment_method = 'flat';
          $repayment->collection_date = Carbon::now();
          $repayment->due_date = Carbon::now();
          $repayment->notes = 'loan outstanding repayment--'.$loan->loan_code;
          $repayment->type = 'credit';
          $repayment->status = '1';
          $repayment->save();

            if($glacctloansuspense->status == '1'){

            $this->gltransaction('withdrawal',$glacctloansuspense,$amount,null);
            $this->create_saving_transaction_gl(null,$glacctloansuspense->id,$loan->branch_id,$amount,'debit','core',$trxref,$this->generatetrnxref('lsusp'),'loan suspense--'.$loan->loan_code,'approved',$usern);
                             
            }

          //$cusoutsnd = $customeracct->account_balance - $mainstnd;
          // $customeracct->account_balance = $oysntt;
          // $customeracct->save();
              
    //           $this->create_saving_transaction(Auth::user()->id,$loan->customer_id,$loan->branch_id,$mainstnd,
    //                                   'debit','core','0',null,null,null,null,$trxref,'loan outstanding payment--'.$loan->loan_code,'approved','2','trnsfer',$usern);
              
    //       if(!is_null($customer->exchangerate_id)){
    //         $this->checkforeigncurrncy($customer->exchangerate_id,$mainstnd,$trxref,'debit');
    //   }else{
    //       if($customer->account_type == '1'){//saving acct GL
    
    //           if($glsavingdacct->status == '1'){
    //               $this->gltransaction('withdrawal',$glsavingdacct,$mainstnd,null);
    //           $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $mainstnd,'debit','core',$trxref,$this->generatetrnxref('D'),'customer credited','approved',$usern);
    //             }
                
    //           }elseif($customer->account_type == '2'){//current acct GL
              
    //           if($glcurrentacct->status == '1'){
    //               $this->gltransaction('withdrawal',$glcurrentacct,$mainstnd,null);
    //           $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $mainstnd,'debit','core',$trxref,$this->generatetrnxref('D'),'customer credited','approved',$usern);
    //               }
                   
    //           }
    //   }

        }else{

           $oysntp = $amount - $outloan->amount;
          $outloan->amount = str_replace("-","",$oysntp);
          $outloan->save();

          $repayment->accountofficer_id = $loan->accountofficer_id;
          $repayment->amount = $amount;
          $repayment->loan_id = $loan->id;
          $repayment->customer_id = $loan->customer_id;
          $repayment->branch_id = $loan->branch_id;
          $repayment->repayment_method = 'flat';
          $repayment->collection_date = Carbon::now();
          $repayment->due_date = Carbon::now();
          $repayment->notes = 'loan outstanding repayment--'.$loan->loan_code;
          $repayment->type = 'credit';
          $repayment->status = '1';
          $repayment->save();

            if($glacctloansuspense->status == '1'){

             $this->gltransaction('withdrawal',$glacctloansuspense,$amount,null);
            $this->create_saving_transaction_gl(null,$glacctloansuspense->id,$loan->branch_id,$amount,'debit','core',$trxref,$this->generatetrnxref('lsusp'),'loan suspense--'.$loan->loan_code,'approved',$usern);
            }

         // $cusoutsnd = $customeracct->account_balance - $amount;
        //  $customeracct->account_balance = $oysntp;
        //   $customeracct->save();
              
            //   $this->create_saving_transaction(Auth::user()->id,$loan->customer_id,$loan->branch_id,$amount,
            //                           'debit','core','0',null,null,null,null,$trxref,'loan outstanding payment--'.$loan->loan_code,'approved','2','trnsfer',$usern);
              
            //     if(!is_null($customer->exchangerate_id)){
            //       $this->checkforeigncurrncy($customer->exchangerate_id,$amount,$trxref,'debit');
            // }else{
            //     if($customer->account_type == '1'){//saving acct GL
          
            //         if($glsavingdacct->status == '1'){
            //             $this->gltransaction('withdrawal',$glsavingdacct,$amount,null);
            //         $this->create_saving_transaction_gl(null,$glsavingdacct->id,null, $amount,'debit','core',$trxref,$this->generatetrnxref('D'),'customer credited','approved',$usern);
            //           }
                      
            //         }elseif($customer->account_type == '2'){//current acct GL
                    
            //         if($glcurrentacct->status == '1'){
            //             $this->gltransaction('withdrawal',$glcurrentacct,$amount,null);
            //         $this->create_saving_transaction_gl(null,$glcurrentacct->id,null, $amount,'debit','core',$trxref,$this->generatetrnxref('D'),'customer credited','approved',$usern);
            //             }
                        
            //         }
            // }
        
        }
    } 
    }
    
  }

  public function debitcreditCompanyBalance($amount,$btyp,$typ){
    $getsetvalue = Setting::first();
   
    if($btyp == "debit"){
        if($typ == "combal"){

          $bal = $getsetvalue->getsettingskey('company_balance') - $amount;
          Setting::where('setting_key', 'company_balance')->update(['setting_value' => $bal]);

        }elseif($typ == "vas"){

          $bal = $getsetvalue->getsettingskey('vas_wallet') - $amount;
          Setting::where('setting_key', 'vas_wallet')->update(['setting_value' => $bal]);

        }elseif($typ == "whapp"){

          $bal = $getsetvalue->getsettingskey('whatsapp_balance') - $amount;
          Setting::where('setting_key', 'whatsapp_balance')->update(['setting_value' => $bal]);

        }elseif($typ == "ussd"){

          $bal = $getsetvalue->getsettingskey('ussd_balance') - $amount;
          
          Setting::where('setting_key', 'ussd_balance')->update(['setting_value' => $bal]);

        }elseif($typ == "vme"){ 

          $bal = $getsetvalue->getsettingskey('verifyme_balance') - $amount;
          Setting::where('setting_key', 'verifyme_balance')->update(['setting_value' => $bal]);

        }
        
    }elseif($btyp == "credit"){
      
      if($typ == "combal"){
        
        $bal = $getsetvalue->getsettingskey('company_balance') + $amount;
        Setting::where('setting_key', 'company_balance')->update(['setting_value' => $bal]);

      }elseif($typ == "vas"){

        $bal = $getsetvalue->getsettingskey('vas_wallet') + $amount;
        Setting::where('setting_key', 'vas_wallet')->update(['setting_value' => $bal]);

      }elseif($typ == "whapp"){
        
        $bal = $getsetvalue->getsettingskey('whatsapp_balance') + $amount;
        Setting::where('setting_key', 'whatsapp_balance')->update(['setting_value' => $bal]);

      }elseif($typ == "ussd"){
        
        $bal = $getsetvalue->getsettingskey('ussd_balance') + $amount;
        Setting::where('setting_key', 'ussd_balance')->update(['setting_value' => $bal]);

      }elseif($typ == "vme"){

        $bal = $getsetvalue->getsettingskey('verifyme_balance') + $amount;
        Setting::where('setting_key', 'verifyme_balance')->update(['setting_value' => $bal]);
      
      }
    }
    
  }

 public function validateTransfer($amount,$maxlimit,$userid){
    
    if ($amount > $maxlimit) {
      return ["status" => false, 'message' => "Maximum transaction amount exceeded ".$maxlimit];
  }

  $limit = Customer::select('transfer_limit')->where('id',$userid)->first();

  $sum = SavingsTransaction::where('type','debit')
                            ->where('status','approved')
                            ->where('customer_id',$userid)
                          ->whereDate('created_at', Carbon::today())
                          ->sum('amount');

  //$wbAccount->accountType->daily_withdraw_limit_count
  if (($sum + $amount) > $limit->transfer_limit) {
      return ["status" => false, 'message' => "Daily Transaction Limit Exceeded"];
  }

  return ["status" => true, 'message' => "Transaction Validated"];
  }
  
   //general ledger transaction
   public function gltransaction($opt,$gl,$amt,$brch){
    if($opt == "deposit"){
        $dedamount = (float)$gl->account_balance - (float)$amt;
        $gl->account_balance = $dedamount;
        $gl->save();

    }elseif($opt == "withdrawal"){
        $addamount = (float)$gl->account_balance + (float)$amt;

            $gl->account_balance = $addamount;
            $gl->save();
    }
}

  
  public static function savings_account_balance($id)
    {
       $balance = Saving::select('account_balance')->where('customer_id',$id)->first();
       
   
        return $balance->account_balance;
    }
    
    public function upload_trx_status($bid,$cid,$glid,$bal,$amt,$txtyp,$gltyp,$txdate,$reas,$txstat,$upstat){
        Upload_transaction_status::create([
          'branch_id' => $bid,
          'customer_id' => $cid,
          'general_ledger_id' => $glid,
          'balance' => $bal,
          'amount' => $amt,
          'trx_type' => $txtyp,
          'gl_type' => $gltyp,
          'trx_date' => $txdate,
          'reason' => $reas,
          'trx_status' => $txstat,
          'upload_status' => $upstat
      ]);
    }

    //generate otp
    public function generateOTP(){
        $otp = mt_rand('1111','9999');
        return $otp;
    }

    //validatetrnxpin 
    public function validatetrnxpin($pin,$userid){
      $user = Customer::where('id', $userid)->first();
      if (Hash::check($pin, $user->pin)) {
        return ['status' => true, 'message' => 'Valid pin'];
      }else{
        if($user->failed_pin < 4){
          $user->failed_pin += 1;
          $user->save();
          return ['status' => false, 'message' => 'Invalid pin'];
        }else{
          $user->status = 4;
          $user->save();
         return ['status' => false, 'message' => 'Your account has been restricted due to multiple pin trials'];
        }
      }
    }

    //generating transaction reference
    public function generatetrnxref($inita){
      return ucwords($inita)."".date("Ymdhis")."".mt_rand(1111,9999);    
    }

    //validate customer balance
    public function validatecustomerbalance($userid,$amount){
      $customeracctbal = Saving::where('customer_id',$userid)->first();
      
      $user = Customer::where('id', $userid)->first();
   
      if($customeracctbal->account_balance >= $amount){
        return ['status' => true, 'message' => 'valid amount'];
      }else{
        if($user->failed_balance < 4){
          $user->failed_balance += 1;
          $user->save();
          return ['status' => false, 'message' => 'Insufficent Fund'];
        }else{
          $user->status = 4;
          $user->save();
         return ['status' => false, 'message' => 'Your account has been restricted due to multiple balance trials'];
        }
        
      }
    }

   
  //validate company balance
  public function validatecompanybalance($amount,$typ){
    $getsetvalue = new Setting();

    if($typ == "combal"){
      if($getsetvalue->getsettingskey('company_balance') > $amount){
        return ['status' => true, 'message' => 'valid amount'];
      }else{
        return ['status' => false, 'message' => 'Issuer or switcher inoperative... Please contact support for assistance'];
      }
    }elseif($typ == "vas"){
      if($getsetvalue->getsettingskey('vas_wallet') > $amount){
        return ['status' => true, 'message' => 'valid amount'];
      }else{
        return ['status' => false, 'message' => 'vas error... Please contact support for assistance'];
      }
    }elseif($typ == "whapp"){
      if($getsetvalue->getsettingskey('whatsapp_balance') > $amount){
        return ['status' => true, 'message' => 'valid amount'];
      }else{
        return ['status' => false, 'message' => 'wasp error... Please contact support for assistance'];
      }
    }elseif($typ == "ussd"){
      if($getsetvalue->getsettingskey('ussd_balance') > $amount){
        return ['status' => true, 'message' => 'valid amount'];
      }else{
        return ['status' => false, 'message' => 'ussd switcher error... Please contact support for assistance'];
      }
    }elseif($typ == "vme"){
      if($getsetvalue->getsettingskey('verifyme_balance') > $amount){
        return ['status' => true, 'message' => 'valid amount'];
      }else{
        return ['status' => false, 'message' => 'vme error... Please contact support for assistance'];
      }
    }

  }

 //credit customer account
    public function CreditCustomerAccount($userid,$amount,$glcode,$trxref,$n,$desc,$brnch){
      
      $customeracctbal = Saving::lockForUpdate()->where('customer_id',$userid)->first();
    $crbal = (float)$customeracctbal->account_balance + (float)$amount;
      
        $customeracctbal->account_balance = $crbal;
        $customeracctbal->save();

      
      $this->create_saving_transaction(null, $userid,$brnch,$amount,
                     'credit',null,'0',null,null,null,null,$trxref,$desc,'approved','1',"trnsfer",null);

      $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$glcode)->lockForUpdate()->first();

      if($glacct->status == '1'){
    $this->gltransaction('withdrawal',$glacct,$amount,null);
      $this->create_saving_transaction_gl(null,$glacct->id,$brnch,$amount,'debit',null,$trxref,$this->generatetrnxref('D'),'inward Tranx','approved',null); 
      }

      return ['status' => true, 'balance' => $customeracctbal->account_balance];
    }
    
    public function ReverseDebitTrnxandcompanyGlAcct($userid,$amount,$glamt=null,$trxref,$glcode,$ref,$decs,$plft,$txntyp,$nme,$dacct){
        $user = Customer::where('id', $userid)->first();
        
      $customeracctbal = Saving::lockForUpdate()->where('customer_id',$userid)->first();
       $totl = (float)$customeracctbal->account_balance + (float)$amount;

       $customeracctbal->account_balance = $totl;
      $customeracctbal->save();
      
        $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$glcode)->lockForUpdate()->first();
        
$this->create_saving_transaction(null, Auth::user()->id,$user->branch_id,$amount,
                   'credit',$plft,'0',null,null,null,$dacct,$trxref,"Transaction Reversed",'approved','4',$txntyp,$nme);
                   
if($glacct->status == '1'){
      $this->create_saving_transaction_gl(null,$glacct->id,$user->branch_id,$glamt,'debit',$plft,null,$this->generatetrnxref($ref),$decs,'approved',$nme);

        $this->gltransaction('withdrawal',$glacct,$glamt,null);
    }
    
    $getsetvalue = new Setting();
        $bal = $getsetvalue->getsettingskey('company_balance') + $amount;
        Setting::where('setting_key', 'company_balance')->update(['setting_value' => $bal]);
        
    return ['status' => true, 'balance' => $customeracctbal->account_balance];
 }

   public function DebitCustomerandcompanyGlAcct($userid,$amount,$glamt,$glcode,$ref,$decs,$plft,$nme){ //for transfers,giftbils, vtpass
     
    $customeracctbal = Saving::lockForUpdate()->where('customer_id',$userid)->first();

      $user = Customer::where('id', $userid)->first();

      $glacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',$glcode)->lockForUpdate()->first();
     $cbalan = (float)$customeracctbal->account_balance - (float)$amount;
     
// if($glacct->status == '1'){
      if($customeracctbal->account_balance >= $amount){
          $customeracctbal->account_balance = $cbalan;
        $customeracctbal->save();
        
    // if($glacct->account_balance >= $glamt){
    //       $this->gltransaction('deposit',$glacct,$glamt,null); 
    //     $this->create_saving_transaction_gl(null,$glacct->id,$user->branch_id,$glamt,'credit',$plft,null,$this->generatetrnxref($ref),$decs,'approved',$nme);
        
    //      return ['status' => true, 'message' => 'valid amount', 'balance' => $customeracctbal->account_balance];
    //     }else{
    //       return ['status' => false, 'glstatus' => '1', 'message' => 'Switcher Error...insufficient Gl Balance', 'balance' => $glacct->account_balance];
    //     }

    if($glacct->status == '1'){
    $this->gltransaction('deposit',$glacct,$glamt,null); 
    $this->create_saving_transaction_gl(null,$glacct->id,$user->branch_id,$glamt,'credit',$plft,null,$this->generatetrnxref($ref),$decs,'approved',$nme);
    }

     return ['status' => true, 'message' => 'valid amount', 'balance' => $customeracctbal->account_balance];

      }else{
        if($user->failed_balance < 4){
          $user->failed_balance += 1;
          $user->save();
          return ['status' => false, 'message' => 'Insufficent Fund'];
        }else{
          $user->status = 4;
          $user->save();
         return ['status' => false, 'message' => 'Your account has been restricted due to multiple balance trials'];
        }
        
      } 
      
    // }else{
    //   return ['status' => false, 'message' => 'Switcher Error...Inactive GL'];
    // }
 }

    public function checkCustomerRestriction($userid){
      $user = Customer::findorfail($userid);
      
      if($user->status == "4" || $user->status == "8" || $user->status == "2" || $user->status == "5" || $user->status == "6" || $user->status == "9"){
        return true;
      }else{
        return false;
      }
    }
    
   public function checkCustomerLienStatus($userid){
      $userlien = Customer::where('id',$userid)->first();
      
      if($userlien->lien == "1"){
        return ['status' => true, 'lien' => $userlien->lien, 'message' => 'deposit cannot be posted'];
      }elseif($userlien->lien == "2"){
        return ['status' => true, 'lien' => $userlien->lien, 'message' => 'withdrawal cannot be posted'];
      } else{
        return ['status' => false, 'message' => 'valid'];
      }
    }

     public function checkaccountstatustype($sid){
      if ($sid == "6"){
        return ['status' => false, 'message' => 'Your Account Has Been Blocked. Please contact support'];
      }elseif($sid == "5"){
          return ['status' => false, 'message' => 'Your Account Has Blocked Due to Fraudulent Attack. Please contact support or visit any of our branches'];
      }elseif($sid == "4"){
          return ['status' => false, 'message' => 'Your Account Has Been Restricted. Please contact support'];
      }elseif($sid == "2"){
          return ['status' => false, 'message' => 'Your Account Has Been Disabled. Please contact support'];
      }elseif($sid == "8"){
          return ['status' => false, 'message' => 'Your Account is Dormant or Inactive. Please contact support or visit any of our branches'];
      }elseif($sid == "7"){
          return ['status' => false, 'message' => 'Your Account Is Currently Being Reviewed And Will Be Approved Soon'];
      }
    }
    
    public function saveBeneficiary($beneficiary,$userid,$account_name,$account_number,$bank_name,$bank_code,$typ){
        if($beneficiary){
            $chckbene = Beneficiary::where('account_number',$account_number)->where('customer_id',$userid)->first();
            if(empty($chckbene)){
             Beneficiary::create([
                 'customer_id' => $userid,
                 'account_name' => $account_name,
                 'account_number' => $account_number,
                 'bank_name' => $bank_name,
                 'bank_code' => $bank_code,
                 'type' => $typ
             ]);
             
            }
         }
    }
    
public function checkforeigncurrncy($uid,$amount,$ref,$type){
    $exrate = Exchangerate::findorfail($uid);

    $gldollaracct = GeneralLedger::select('id','status','account_balance')->where('gl_code',"20242722")->lockForUpdate()->first();
    $gleuroacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',"20409988")->lockForUpdate()->first();
    $glpoundsacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',"20776967")->lockForUpdate()->first();
    $glrandacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',"20276747")->lockForUpdate()->first();

    if($type == "credit"){
       if($uid == "1"){
        if($gldollaracct->status == '1'){         
           $this->gltransaction('withdrawal',$gldollaracct,$amount,null);
            $this->create_saving_transaction_gl(null,$gldollaracct->id,null, $amount,'credit','core',$ref,$this->generatetrnxref('Cr'),'customer credited','approved','');
             } 
       }elseif($uid == "2"){
        if($glpoundsacct->status == '1'){         
          $this->gltransaction('withdrawal',$glpoundsacct,$amount,null);
           $this->create_saving_transaction_gl(null,$glpoundsacct->id,null, $amount,'credit','core',$ref,$this->generatetrnxref('Cr'),'customer credited','approved','');
            } 
       }elseif($uid == "3"){
        if($gleuroacct->status == '1'){         
          $this->gltransaction('withdrawal',$gleuroacct,$amount,null);
           $this->create_saving_transaction_gl(null,$gleuroacct->id,null, $amount,'credit','core',$ref,$this->generatetrnxref('Cr'),'customer credited','approved','');
            } 
       }elseif($uid == "4"){
        if($glrandacct->status == '1'){         
          $this->gltransaction('withdrawal',$glrandacct,$amount,null);
           $this->create_saving_transaction_gl(null,$glrandacct->id,null, $amount,'credit','core',$ref,$this->generatetrnxref('Cr'),'customer credited','approved','');
            } 
       }

    }elseif($type == "debit"){
      if($uid == "1"){
        if($gldollaracct->status == '1'){         
           $this->gltransaction('deposit',$gldollaracct,$amount,null);
            $this->create_saving_transaction_gl(null,$gldollaracct->id,null, $amount,'debit','core',$ref,$this->generatetrnxref('D'),'customer debited','approved','');
             } 
       }elseif($uid == "2"){
        if($glpoundsacct->status == '1'){         
          $this->gltransaction('deposit',$glpoundsacct,$amount,null);
           $this->create_saving_transaction_gl(null,$glpoundsacct->id,null, $amount,'debit','core',$ref,$this->generatetrnxref('D'),'customer debited','approved','');
            } 
       }elseif($uid == "3"){
        if($gleuroacct->status == '1'){         
          $this->gltransaction('deposit',$gleuroacct,$amount,null);
           $this->create_saving_transaction_gl(null,$gleuroacct->id,null, $amount,'debit','core',$ref,$this->generatetrnxref('D'),'customer debited','approved','');
            } 
       }elseif($uid == "4"){
        if($glrandacct->status == '1'){         
          $this->gltransaction('deposit',$glrandacct,$amount,null);
           $this->create_saving_transaction_gl(null,$glrandacct->id,null, $amount,'debit','core',$ref,$this->generatetrnxref('D'),'customer debited','approved','');
            } 
       } 
    }
    
}

public function foreigncurrncywtholdingTax($uid,$amount,$ref){
  $gldollarwthacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',"20685315")->lockForUpdate()->first();
    $gleurowthacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',"20590802")->lockForUpdate()->first();
    $glpoundswthacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',"20696956")->lockForUpdate()->first();
    $glrandwthacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',"20481975")->lockForUpdate()->first();


    if($uid == "1"){
      if($gldollarwthacct->status == '1'){         
        $this->gltransaction('withdrawal',$gldollarwthacct,$amount,null);
        $this->create_saving_transaction_gl(null,$gldollarwthacct->id,null, $amount,'credit','core',$ref,$this->generatetrnxref('withtx'),'withholding tax','approved','');
        
           } 
     }elseif($uid == "2"){
      if($glpoundswthacct->status == '1'){         
        $this->gltransaction('withdrawal',$glpoundswthacct,$amount,null);
        $this->create_saving_transaction_gl(null,$glpoundswthacct->id,null, $amount,'credit','core',$ref,$this->generatetrnxref('withtx'),'withholding tax','approved','');
        
          } 
     }elseif($uid == "3"){
      if($gleurowthacct->status == '1'){         
        $this->gltransaction('withdrawal',$gleurowthacct,$amount,null);
            $this->create_saving_transaction_gl(null,$gleurowthacct->id,null, $amount,'credit','core',$ref,$this->generatetrnxref('withtx'),'withholding tax','approved','');
            
          } 
     }elseif($uid == "4"){
      if($glrandwthacct->status == '1'){         
        $this->gltransaction('withdrawal',$glrandwthacct,$amount,null);
            $this->create_saving_transaction_gl(null,$glrandwthacct->id,null, $amount,'credit','core',$ref,$this->generatetrnxref('withtx'),'withholding tax','approved','');
            
          } 
     }
}

public function foreigncurrncyLiquidationCharge($uid,$amount,$ref){
  $gldollarliqacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',"40305095")->lockForUpdate()->first();
  $gleuroliqacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',"40420294")->lockForUpdate()->first();
  $glpoundsliqacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',"40775710")->lockForUpdate()->first();
  $glrandliqacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',"40942289")->lockForUpdate()->first();

  if($uid == "1"){
    if($gldollarliqacct->status == '1'){         
       
      $this->gltransaction('withdrawal',$gldollarliqacct,$amount,null);
      $this->create_saving_transaction_gl(null,$gldollarliqacct->id,null, $amount,'credit','core',$ref,$this->generatetrnxref('fdchrg'),'fixed deposit charge','approved','');
      
         } 
   }elseif($uid == "2"){
    if($glpoundsliqacct->status == '1'){         
  
      $this->gltransaction('withdrawal',$glpoundsliqacct,$amount,null);
      $this->create_saving_transaction_gl(null,$glpoundsliqacct->id,null, $amount,'credit','core',$ref,$this->generatetrnxref('fdchrg'),'fixed deposit charge','approved','');
      
        } 
   }elseif($uid == "3"){
    if($gleuroliqacct->status == '1'){         
     
      $this->gltransaction('withdrawal',$gleuroliqacct,$amount,null);
      $this->create_saving_transaction_gl(null,$gleuroliqacct->id,null, $amount,'credit','core',$ref,$this->generatetrnxref('fdchrg'),'fixed deposit charge','approved','');
      
        } 
   }elseif($uid == "4"){
    if($glrandliqacct->status == '1'){         
     
      $this->gltransaction('withdrawal',$glrandliqacct,$amount,null);
      $this->create_saving_transaction_gl(null,$glrandliqacct->id,null, $amount,'credit','core',$ref,$this->generatetrnxref('fdchrg'),'fixed deposit charge','approved','');
      
        } 
   }
}

public function foreigncurrncyinterestExpense($uid,$amount,$ref,$fxd){
  $gldollarintexpacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',"50500086")->lockForUpdate()->first();
  $gleurointexpacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',"50921377")->lockForUpdate()->first();
  $glpoundsintexpacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',"50359501")->lockForUpdate()->first();
  $glrandintexpacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',"50962524")->lockForUpdate()->first();

  if($uid == "1"){
    if($gldollarintexpacct->status == '1'){         
       
     
      $this->gltransaction('withdrawal',$gldollarintexpacct,$amount,null);
      $this->create_saving_transaction_gl(null,$gldollarintexpacct->id,null, $amount,'debit','core',$ref,$this->generatetrnxref('intrexp'),'fixed deposit investment interest - '.$fxd,'approved','');

         } 
   }elseif($uid == "2"){
    if($glpoundsintexpacct->status == '1'){         
  
     
      $this->gltransaction('withdrawal',$glpoundsintexpacct,$amount,null);
      $this->create_saving_transaction_gl(null,$glpoundsintexpacct->id,null, $amount,'debit','core',$ref,$this->generatetrnxref('intrexp'),'fixed deposit investment interest - '.$fxd,'approved','');

        } 
   }elseif($uid == "3"){
    if($gleurointexpacct->status == '1'){         
     
      $this->gltransaction('withdrawal',$gleurointexpacct,$amount,null);
      $this->create_saving_transaction_gl(null,$gleurointexpacct->id,null, $amount,'debit','core',$ref,$this->generatetrnxref('intrexp'),'fixed deposit investment interest - '.$fxd,'approved','');

        } 
   }elseif($uid == "4"){
    if($glrandintexpacct->status == '1'){         
      
      $this->gltransaction('withdrawal',$glrandintexpacct,$amount,null);
      $this->create_saving_transaction_gl(null,$glrandintexpacct->id,null, $amount,'debit','core',$ref,$this->generatetrnxref('intrexp'),'fixed deposit investment interest - '.$fxd,'approved','');

        } 
   }
}

public function foreigncurrncyinvestment($uid,$amount,$ref,$type,$unr){
  $gldollarinvacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',"20398069")->lockForUpdate()->first();
  $gleuroinvacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',"20719646")->lockForUpdate()->first();
  $glpoundsinvacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',"20794594")->lockForUpdate()->first();
  $glrandinvacct = GeneralLedger::select('id','status','account_balance')->where('gl_code',"20128265")->lockForUpdate()->first();

    if($type == "debit"){
      if($uid == "1"){
        if($gldollarinvacct->status == '1'){         
           
          $this->gltransaction('deposit',$gldollarinvacct,$amount,null);
          $this->create_saving_transaction_gl(null,$gldollarinvacct->id,null, $amount,'debit','core',$ref,$this->generatetrnxref('inv'),'debit investment','approved',$unr);
          
             } 
       }elseif($uid == "2"){
        if($glpoundsinvacct->status == '1'){         
      
          $this->gltransaction('deposit',$glpoundsinvacct,$amount,null);
          $this->create_saving_transaction_gl(null,$glpoundsinvacct->id,null, $amount,'debit','core',$ref,$this->generatetrnxref('inv'),'debit investment','approved',$unr);
          
            } 
       }elseif($uid == "3"){
        if($gleuroinvacct->status == '1'){         
         
          $this->gltransaction('deposit',$gleuroinvacct,$amount,null);
          $this->create_saving_transaction_gl(null,$gleuroinvacct->id,null, $amount,'debit','core',$ref,$this->generatetrnxref('inv'),'debit investment','approved',$unr);
          
            } 
       }elseif($uid == "4"){
        if($glrandinvacct->status == '1'){         
         
          $this->gltransaction('deposit',$glrandinvacct,$amount,null);
          $this->create_saving_transaction_gl(null,$glrandinvacct->id,null, $amount,'debit','core',$ref,$this->generatetrnxref('inv'),'debit investment','approved',$unr);
          
            } 
       }

    }elseif($type == "credit"){

      if($uid == "1"){
        if($gldollarinvacct->status == '1'){         
           
          $this->gltransaction('withdrawal',$gldollarinvacct,$amount,null);
          $this->create_saving_transaction_gl(null,$gldollarinvacct->id,null, $amount,'credit','core',$ref,$this->generatetrnxref('inv'),'credit investment','approved',$unr);
          
             } 
       }elseif($uid == "2"){
        if($glpoundsinvacct->status == '1'){         
      
          $this->gltransaction('withdrawal',$glpoundsinvacct,$amount,null);
          $this->create_saving_transaction_gl(null,$glpoundsinvacct->id,null, $amount,'credit','core',$ref,$this->generatetrnxref('inv'),'credit investment','approved',$unr);
          
            } 
       }elseif($uid == "3"){
        if($gleuroinvacct->status == '1'){         
         
          $this->gltransaction('withdrawal',$gleuroinvacct,$amount,null);
          $this->create_saving_transaction_gl(null,$gleuroinvacct->id,null, $amount,'credit','core',$ref,$this->generatetrnxref('inv'),'credit investment','approved',$unr);
          
            } 
       }elseif($uid == "4"){
        if($glrandinvacct->status == '1'){         
         
          $this->gltransaction('withdrawal',$glrandinvacct,$amount,null);
          $this->create_saving_transaction_gl(null,$glrandinvacct->id,null, $amount,'credit','core',$ref,$this->generatetrnxref('inv'),'credit investment','approved',$unr);
          
            } 
       }
    }
}

     public function getUtilityPercentage(){
        $jsons = [
            [
            "service"  => "enugu-electric",
            "value" => "1.40"
           ],
            [
            "service"  => "eko-electric",
            "value" => "1.0"
           ],
            [
            "service"  => "ikeja-electric",
            "value" => "1.50"
           ],
            [
            "service"  => "ibadan-electric",
            "value" => "0.70"
           ],
            [
            "service"  => "kano-electric",
            "value" => "1.00"
           ],
            [
            "service"  => "kaduna-electric",
            "value" => "1.50"
           ],
            [
            "service"  => "portharcourt-electric",
            "value" => "2.00"
           ],

            [
            "service"  => "abuja-electric",
            "value" => "1.2"
           ],
            [
            "service"  => "jos-electric",
            "value" => "0.90"
           ],

            [
            "service"  => "benin-electric",
            "value" => "1.50"
           ],
            [
            "service"  => "etisalat",
            "value" => "4.00"
           ],
            [
            "service"  => "airtel",
            "value" => "3.40"
           ],
            [
            "service"  => "mtn",
            "value" => "3.00"
           ],
            [
            "service"  => "glo",
            "value" => "4.00"
           ],
            [
            "service"  => "mtn-data",
            "value" => "3.00"
           ],
             [
            "service"  => "airtel-data",
            "value" => "4.00"
           ],
            [
            "service"  => "etisalat-data",
            "value" => "4.00"
           ],
            [
            "service"  => "glo-data",
            "value" => "4.00"
           ],
            [
            "service"  => "smile-direct",
            "value" => "5.00"
           ],
            [
            "service"  => "spectranet",
            "value" => "3.00"
           ],
            [
            "service"  => "dstv",
            "value" => "1.50"
           ],
            [
            "service"  => "gotv",
            "value" => "1.50"
           ],
            [
            "service"  => "startimes",
            "value" => "2.50"
           ],
            [
            "service"  => "showmax",
            "value" => "1.50"
           ],
            [
            "service"  => "BET9JA",
            "value" => "0.10"
           ],
            [
            "service"  => "BANGBET",
            "value" => "0.50"
           ],
            [
            "service"  => "SPORTYBET",
            "value" => "0.00"
           ],
            [
            "service"  => "BETKING",
            "value" => "0.40"
           ],
            [
            "service"  => "ONE_XBET",
            "value" => "0.50"
           ],
            [
            "service"  => "BETWAY",
            "value" => "0.50"
           ],
            [
            "service"  => "MERRYBET",
            "value" => "0.25"
           ],
            [
            "service"  => "MELBET",
            "value" => "0.50"
           ],
            [
            "service"  => "BETLION",
            "value" => "0.00"
           ],
            [
            "service"  => "BET9JA_AGENT",
            "value" => "0.10"
           ],
            [
            "service"  => "NAIJABET",
            "value" => "0.50"
           ],
            [
            "service"  => "MYLOTTOHUB",
            "value" => "0.50"
           ],
            [
            "service"  => "CLOUDBET",
            "value" => "0.00"
           ],
            [
            "service"  => "PARIPESA",
            "value" => "0.25"
           ],
            [
            "service"  => "NAIRAMILLION",
            "value" => "0.00"
           ],
            [
            "service"  => "NAIRABET",
            "value" => "0.25"
           ],
            [
            "service"  => "PARIMATCH",
            "value" => "0.4"
           ],
            [
            "service"  => "LIVESCOREBET",
            "value" => "0.4"
           ],
            [
            "service"  => "BETBONANZA",
            "value" => "0.5"
           ],
            [
            "service"  => "ILOT",
            "value" => "0.5"
           ],
        ];

        return $jsons;
     }
}//endTraite
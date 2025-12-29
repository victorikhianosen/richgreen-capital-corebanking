<?php

namespace App\Http\Controllers;

use App\Http\Traites\AuditTraite;
use App\Http\Traites\SavingTraite;
use App\Models\Charge;
use App\Models\Setting;
use App\Models\GeneralLedger;
use App\Models\ProvidusKey;
use App\Models\User;
use App\Models\SavingsTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class SettingsController extends Controller
{
    use AuditTraite;
      use SavingTraite;
    public function __construct()
    {
       $this->middleware('auth'); 
    }


    public function settings(){
        return view('settings')->with('data',GeneralLedger::orderBy('gl_name','ASC')->get())
                                ->with('chargedata',Charge::get());
    }

    public function change_password(){
        return view('change_password');
    }
    
    //update password
    public function update_password(Request $r){
        $this->validate($r,[
            'password' => ['required','string','min:8','confirmed']
        ]);

        User::where('id',$r->id)->update([
            'password' => Hash::make($r->password)
        ]);
        return redirect()->back()->with('success','Password Changed Successfully');
    }
    
     public function wallet(){
        if (request()->filter == true) {
            $succesTrnx = SavingsTransaction::where('status_type','31')
                                             ->where('status','approved')
                                             ->whereBetween('created_at',[request()->datefrom, request()->dateto])
                                             ->get();

            return view('fundwallet')->with('data',$succesTrnx);
        }else{
            $succesTrnx = SavingsTransaction::where('status_type','31')
                                            ->where('status','approved')->get();

            return view('fundwallet')->with('data',$succesTrnx);
        }
    }

    //update profile
    public function update_profile(Request $r){
        $this->validate($r,[
            'firstname' => ['required','string'],
            'lastname' => ['required','string'],
            'email' => ['required','string'],
            'gender' => ['required','string'],
            'address' => ['required','string'],
            'phone' => ['required','string'],
        ]);

        User::where('id',$r->id)->update([
            'first_name' => $r->firstname,
            'last_name' => $r->lastname,
            'username' => $r->username,
            'email' => $r->email,
            'gender' => $r->gender,
            'address' => $r->address,
            'phone' => $r->phone
        ]);
        return redirect()->back()->with('success','Profile Updated Successfully');
    }

    public function update_settings(Request $request){

        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        Setting::where('setting_key', 'company_name')->update(['setting_value' => $request->company_name]);
        
        Setting::where('setting_key', 'company_code')->update(['setting_value' => $request->company_code]);
        
         Setting::where('setting_key', 'company_share')->update(['setting_value' => $request->company_share]);
         
         Setting::where('setting_key', 'company_capital')->update(['setting_value' => $request->company_capital]);
         
         Setting::where('setting_key', 'bank_fund')->update(['setting_value' => $request->bank_fund]);
         
           Setting::where('setting_key', 'till_account')->update(['setting_value' => $request->glcode]);

           Setting::where('setting_key', 'vault_account')->update(['setting_value' => $request->vglcode]);
           
            Setting::where('setting_key', 'assetmtx')->update(['setting_value' => $request->assetmtx]);

           Setting::where('setting_key', 'giftbill_account')->update(['setting_value' => $request->gblglcode]);

           Setting::where('setting_key', 'vtpass_account')->update(['setting_value' => $request->vtglcode]);
           
           Setting::where('setting_key', 'fdliquid_interest')->update(['setting_value' => $request->liquidation_interest]);

           Setting::where('setting_key', 'enable_virtual_ac')->update(['setting_value' => $request->enable_virtual_account]);
           
           Setting::where('setting_key', 'inwardoption')->update(['setting_value' => $request->inwardpayoptn]);
           
           Setting::where('setting_key', 'income_suspense')->update(['setting_value' => $request->inmsusp]);

        Setting::where('setting_key', 'asset_suspense')->update(['setting_value' => $request->asstsusp]);
        
        Setting::where('setting_key', 'liability_suspense')->update(['setting_value' => $request->libsusp]);
        
        Setting::where('setting_key', 'exps_suspense')->update(['setting_value' => $request->expsusp]);
        
        Setting::where('setting_key', 'capital_suspense')->update(['setting_value' => $request->capsusp]);
           
            Setting::where('setting_key', 'giftbill_income')->update(['setting_value' => $request->giftincmglcode]);

           Setting::where('setting_key', 'vtpass_income')->update(['setting_value' => $request->vtincmglcode]);
           
           Setting::where('setting_key', 'pos_charges')->update(['setting_value' => $request->poschrglcode]);
           
           Setting::where('setting_key', 'glcharges')->update(['setting_value' => $request->chrglcode]);
           
           Setting::where('setting_key', 'transfer_charge')->update(['setting_value' => $request->chrgtrn]);

           Setting::where('setting_key', 'esusucharges')->update(['setting_value' => $request->chrgesusu]);

           Setting::where('setting_key', 'frmfeecharges')->update(['setting_value' => $request->chrgformfee]);

           Setting::where('setting_key', 'processcharges')->update(['setting_value' => $request->chrgprcessfee]);

           Setting::where('setting_key', 'monthlycharges')->update(['setting_value' => $request->chrgmonthly]);
           
           Setting::where('setting_key', 'othercharges')->update(['setting_value' => $request->chrgsother]);
           
           Setting::where('setting_key', 'othrchargesgl')->update(['setting_value' => $request->chrgother]);
           
           Setting::where('setting_key', 'bankcharge')->update(['setting_value' => $request->bank_charge]);
           
            Setting::where('setting_key', 'bvnroute')->update(['setting_value' => $request->bvnroute]);
           
           Setting::where('setting_key', 'withholdingtax')->update(['setting_value' => $request->withholding_tax]);

           Setting::where('setting_key', 'fdcharge')->update(['setting_value' => $request->fd_charge]);
           
           Setting::where('setting_key', 'monnifycharge')->update(['setting_value' => $request->monnify_charge]);
           
           Setting::where('setting_key', 'moniepointgl')->update(['setting_value' => $request->moniepglcode]);

           Setting::where('setting_key', 'company_account')->update(['setting_value' => $request->cmglcode]);

           Setting::where('setting_key', 'online_transfer')->update(['setting_value' => $request->online_transfer]);
        
        Setting::where('setting_key', 'company_phone')->update(['setting_value' => $request->company_phone]);
        
        Setting::where('setting_key', 'company_email')->update(['setting_value' => $request->company_email]);
        
        Setting::where('setting_key', 'company_website')->update(['setting_value' => $request->company_website]);
        
        Setting::where('setting_key', 'company_address')->update(['setting_value' => $request->company_address]);

        Setting::where('setting_key', 'portal_address')->update(['setting_value' => $request->portal_address]);
        
        Setting::where('setting_key', 'currency_symbol')->update(['setting_value' => $request->currency_symbol]);
        
        Setting::where('setting_key', 'currency_position')->update(['setting_value' => $request->currency_position]);
        
        Setting::where('setting_key', 'company_currency')->update(['setting_value' => $request->company_currency]);
        
        Setting::where('setting_key', 'company_country')->update(['setting_value' => $request->company_country]);
        
        Setting::where('setting_key', 'withdrawal_limit')->update(['setting_value' => $request->withdrawal_limit]);
        
        Setting::where('setting_key', 'deposit_limit')->update(['setting_value' => $request->deposit_limit]);
        
        Setting::where('setting_key', 'sms_enabled')->update(['setting_value' => $request->sms_enabled]);
        
        Setting::where('setting_key', 'sms_sender')->update(['setting_value' => $request->sms_sender]);
        
        Setting::where('setting_key', 'active_sms')->update(['setting_value' => $request->active_sms]);
        
        Setting::where('setting_key', 'sms_public_key')->update(['setting_value' => $request->sms_public_key]);
        
        Setting::where('setting_key', 'sms_secret_key')->update(['setting_value' => $request->sms_secret_key]);
                
        Setting::where('setting_key','twilio_phone_number')->update(['setting_value' => $request->twilio_phone_number]);
        
        Setting::where('setting_key','sms_baseurl')->update(['setting_value' => $request->sms_baseurl]);
        
        Setting::where('setting_key', 'infobip_username')->update(['setting_value' => $request->infobip_username]);
        
        Setting::where('setting_key', 'infobip_password')->update(['setting_value' => $request->infobip_password]);

        Setting::where('setting_key', 'infobip_api_key')->update(['setting_value' => $request->infobip_api_key]);

        Setting::where('setting_key', 'infobip_baseurl')->update(['setting_value' => $request->infobip_baseurl]);
        
        
        Setting::where('setting_key', 'clickatell_api_id')->update(['setting_value' => $request->clickatell_api_id]);
        
        Setting::where('setting_key', 'clickatell_username')->update(['setting_value' => $request->clickatell_api_id]);

        Setting::where('setting_key', 'clickatell_password')->update(['setting_value' => $request->clickatell_password]);
        
        Setting::where('setting_key', 'clickatell_baseurl')->update(['setting_value' => $request->clickatell_baseurl]);
        
        Setting::where('setting_key', 'payment_received_sms_template')->update(['setting_value' => $request->payment_received_sms_template]);
        
        Setting::where('setting_key','payment_received_email_template')->update(['setting_value' => $request->payment_received_email_template]);
        
        Setting::where('setting_key','payment_received_email_subject')->update(['setting_value' => $request->payment_received_email_subject]);
        
        Setting::where('setting_key','birthday_msg')->update(['setting_value' => $request->birthday_msg]);
        
        Setting::where('setting_key','payment_email_subject')->update(['setting_value' => $request->payment_email_subject]);
        
        Setting::where('setting_key','payment_email_template')->update(['setting_value' => $request->payment_email_template]);
        
        Setting::where('setting_key','borrower_statement_email_subject')->update(['setting_value' => $request->borrower_statement_email_subject]);
        
        Setting::where('setting_key', 'borrower_statement_email_template')->update(['setting_value' => $request->borrower_statement_email_template]);
        
        Setting::where('setting_key','loan_statement_email_subject')->update(['setting_value' => $request->loan_statement_email_subject]);
        
        Setting::where('setting_key','loan_statement_email_template')->update(['setting_value' => $request->loan_statement_email_template]);
        
        Setting::where('setting_key','loan_schedule_email_subject')->update(['setting_value' => $request->loan_schedule_email_subject]);
        
        Setting::where('setting_key','loan_schedule_email_template')->update(['setting_value' => $request->loan_schedule_email_template]);
        
        Setting::where('setting_key', 'auto_apply_penalty')->update(['setting_value' => $request->auto_apply_penalty]);
        
        Setting::where('setting_key','auto_payment_receipt_email')->update(['setting_value' => $request->auto_payment_receipt_email]);
        
        Setting::where('setting_key', 'auto_payment_receipt_sms')->update(['setting_value' => $request->auto_payment_receipt_sms]);
        
        Setting::where('setting_key','auto_repayment_sms_reminder')->update(['setting_value' => $request->auto_repayment_sms_reminder]);
        
        Setting::where('setting_key','auto_repayment_email_reminder')->update(['setting_value' => $request->auto_repayment_email_reminder]);
        
        Setting::where('setting_key','auto_repayment_days')->update(['setting_value' => $request->auto_repayment_days]);
        
        Setting::where('setting_key','auto_overdue_repayment_sms_reminder')->update(['setting_value' => $request->auto_overdue_repayment_sms_reminder]);
        
        Setting::where('setting_key', 'auto_overdue_repayment_email_reminder')->update(['setting_value' => $request->auto_overdue_repayment_email_reminder]);
        
        Setting::where('setting_key','auto_overdue_repayment_days')->update(['setting_value' => $request->auto_overdue_repayment_days]);
        
        Setting::where('setting_key','auto_overdue_loan_sms_reminder')->update(['setting_value' => $request->auto_overdue_loan_sms_reminder]);
        
        Setting::where('setting_key', 'auto_overdue_loan_email_reminder')->update(['setting_value' => $request->auto_overdue_loan_email_reminder]);
        
        Setting::where('setting_key','auto_overdue_loan_days')->update(['setting_value' => $request->auto_overdue_loan_days]);
        
        Setting::where('setting_key','loan_overdue_email_subject')->update(['setting_value' => $request->loan_overdue_email_subject]);
        
        Setting::where('setting_key','loan_overdue_email_template')->update(['setting_value' => $request->loan_overdue_email_template]);
        
        Setting::where('setting_key','loan_overdue_sms_template')->update(['setting_value' => $request->loan_overdue_sms_template]);
        
        Setting::where('setting_key','loan_payment_reminder_subject')->update(['setting_value' => $request->loan_payment_reminder_subject]);
        
        Setting::where('setting_key','loan_payment_reminder_email_template')->update(['setting_value' => $request->loan_payment_reminder_email_template]);
        
        Setting::where('setting_key','loan_payment_reminder_sms_template')->update(['setting_value' => $request->loan_payment_reminder_sms_template]);
        
        Setting::where('setting_key','missed_payment_email_subject')->update(['setting_value' => $request->missed_payment_email_subject]);
        
        Setting::where('setting_key','missed_payment_email_template')->update(['setting_value' => $request->missed_payment_email_template]);
        
         Setting::where('setting_key','missed_payment_sms_template')->update(['setting_value' => $request->missed_payment_sms_template]);
       
         Setting::where('setting_key', 'enable_2FA')->update(['setting_value' => $request->enable2fa]);
         
         Setting::where('setting_key', 'payoption')->update(['setting_value' => $request->payoptn]);
         
         Setting::where('setting_key', 'enable_cron')->update(['setting_value' => $request->enable_cron]);

        Setting::where('setting_key', 'welcome_note')->update(['setting_value' => $request->welcome_note]);
        
        Setting::where('setting_key','allow_self_registration')->update(['setting_value' => $request->allow_self_registration]);
        
        Setting::where('setting_key', 'allow_client_login')->update(['setting_value' => $request->allow_client_login]);
        
        Setting::where('setting_key', 'allow_client_apply')->update(['setting_value' => $request->allow_client_apply]);

        Setting::where('setting_key','enable_online_payment')->update(['setting_value' => $request->enable_online_payment]);

        Setting::where('setting_key', 'payment_gateway')->update(['setting_value' => $request->payment_gateway]);
       
        Setting::where('setting_key', 'gateway_pub_key')->update(['setting_value' => $request->gateway_pub_key]);
       
        Setting::where('setting_key', 'gateway_secret_key')->update(['setting_value' => $request->gateway_secret_key]);
       
        Setting::where('setting_key','client_request_guarantor')->update(['setting_value' => $request->client_request_guarantor]);
       
        Setting::where('setting_key','auto_post_savings_interest')->update(['setting_value' => $request->auto_post_savings_interest]);
       
        Setting::where('setting_key','client_auto_activate_account')->update(['setting_value' => $request->client_auto_activate_account]);

        if($request->hasFile('company_logo')){
            $lgfilename = $request->file('company_logo');
            $newlgfilename = time()."_".$lgfilename->getClientOriginalName();
            $lgfilename->move('uploads',$newlgfilename);

            Setting::where('setting_key', 'company_logo')->update([
                'setting_value' => 'uploads/'.$newlgfilename
            ]);
        }
        if($request->hasFile('background_image')){
            $bgfilename = $request->file('background_image');
            $newbgfilename = time()."_".$bgfilename->getClientOriginalName();
            $bgfilename->move('uploads',$newbgfilename);

            Setting::where('setting_key','login_background')->update([
                'setting_value' => 'uploads/'.$newbgfilename
            ]);
        }
        $usern = Auth::user()->last_name." ".Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id,$branch,$usern,'settings','Settings Updated');
        
         return redirect()->route('setting')->with('success','Settings Saved');
        
    }

    //checking bvn verification
    public function check_bvn(Request $r){
        if($r->ajax()){
      $response = Http::post('https://api-demo.paysorta.com/services/verification-service/api/v1/bvn/verifySingleBVN',[
                'bvn' => $r->bvn
            ]);

            return $response;
        }
    }
    
    
 public function fund_wallet(Request $r){
        $this->validate($r,[
            'amount' => ['required','numeric','gt:0'] 
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $usern = Auth::user()->last_name." ".Auth::user()->first_name;

        $trxref = $this->generatetrnxref('wallet');

        $description = "Wallet Topup";

        $getsetvalue = new Setting();
        $bal = $getsetvalue->getsettingskey('company_balance') + $r->amount;
        Setting::where('setting_key', 'company_balance')->update(['setting_value' => $bal]);
        
        $this->create_saving_transaction(Auth::user()->id,null,$branch,$r->amount,
                                                 'credit','core','0',null,null,null,null,$trxref,$description,'approved','31','trnsfer',$usern);

            return ['status' => 'success', 'msg' => 'Wallet Top Successful'];
    }
    
}//endclass

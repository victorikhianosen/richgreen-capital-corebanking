<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Loan;
use App\Models\Email;
use App\Models\Saving;
use App\Models\Status;
use App\Models\Setting;
use App\Models\Customer;
use Illuminate\Support\Str;
use App\Models\Exchangerate;
use Illuminate\Http\Request;
use App\Models\Accountofficer;
use App\Models\SavingsProduct;
use App\Http\Traites\LoanTraite;
use App\Http\Traites\UserTraite;
use App\Http\Traites\AuditTraite;
use App\Http\Traites\SavingTraite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomersBalanceExport;

class CustomersController extends Controller
{
    use AuditTraite;
    use SavingTraite;
    use LoanTraite;
    use UserTraite;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function manage_customers()
    {
        if (Auth::user()->roles()->first()->name == 'account officer') {
            $acofficer = Accountofficer::select('id')->where('user_id', Auth::user()->id)->first();
            $customers = Customer::select('id', 'last_name', 'first_name', 'account_type', 'acctno', 'section', 'username', 'phone', 'gender', 'email', 'reg_date', 'status')
                ->where('accountofficer_id', $acofficer->id)
                ->where('status', '1')
                ->orderBy('id', 'DESC')->get();
            // dd($customers);
            return view('customers.manage_customer')->with('customers', $customers);
        } else {

            $cust = Customer::select('id', 'last_name', 'first_name', 'account_type', 'acctno', 'section', 'username', 'phone', 'gender', 'email', 'reg_date', 'status')
                ->where('status', '1')->orderBy('id', 'DESC')->get();

            return view('customers.manage_customer')->with('customers', $cust);
        }
    }

    public function manage_pending_customers()
    {
        if (Auth::user()->roles()->first()->name == 'account officer') {
            $acofficer = Accountofficer::where('user_id', Auth::user()->id)->first();
            $customers = Customer::select('id', 'last_name', 'first_name', 'account_type', 'section', 'acctno', 'username', 'phone', 'gender', 'email', 'reg_date', 'status')
                ->where('accountofficer_id', $acofficer->id)
                ->where('status', '7')
                ->orderBy('id', 'DESC')->get();

            return view('customers.manage_pending_customer')->with('pdcustomers', $customers);
        } else {
            return view('customers.manage_pending_customer')->with('pdcustomers', Customer::select('id', 'last_name', 'first_name', 'acctno', 'account_type', 'section', 'username', 'phone', 'gender', 'email', 'reg_date', 'status')->where('status', '7')->orderBy('created_at', 'DESC')->get());
        }
    }

    public function manage_closed_customers()
    {
        if (Auth::user()->roles()->first()->name == 'account officer') {
            $acofficer = Accountofficer::where('user_id', Auth::user()->id)->first();
            $customers = Customer::select('id', 'last_name', 'first_name', 'account_type', 'section', 'acctno', 'username', 'phone', 'gender', 'email', 'reg_date', 'status')->where('accountofficer_id', $acofficer->id)
                ->where('status', '2')
                ->orderBy('id', 'DESC')->get();

            return view('customers.manage_closed_customer')->with('clcustomers', $customers);
        } else {
            return view('customers.manage_closed_customer')->with('clcustomers', Customer::select('id', 'last_name', 'first_name', 'acctno', 'account_type', 'section', 'username', 'phone', 'gender', 'email', 'reg_date', 'status')->where('status', '2')->orderBy('created_at', 'DESC')->get());
        }
    }
    public function manage_restricted_customers()
    {
        if (Auth::user()->roles()->first()->name == 'account officer') {
            $acofficer = Accountofficer::where('user_id', Auth::user()->id)->first();
            $customers = Customer::select('id', 'last_name', 'first_name', 'account_type', 'section', 'username', 'acctno', 'phone', 'gender', 'email', 'reg_date', 'status')->where('accountofficer_id', $acofficer->id)
                ->whereBetween('status', ['4', '6'])
                ->orderBy('id', 'DESC')->get();

            return view('customers.manage_restricted_customer')->with('clcustomers', $customers);
        } else {
            return view('customers.manage_restricted_customer')->with('clcustomers', Customer::select('id', 'last_name', 'first_name', 'acctno', 'account_type', 'section', 'username', 'phone', 'gender', 'email', 'reg_date', 'status')->whereBetween('status', ['4', '6'])->orderBy('created_at', 'DESC')->get());
        }
    }
    public function manage_dom_accounts()
    {
        if (Auth::user()->roles()->first()->name == 'account officer') {
            $acofficer = Accountofficer::where('user_id', Auth::user()->id)->first();
            $customers = Customer::select('id', 'last_name', 'first_name', 'account_type', 'acctno', 'section', 'username', 'phone', 'gender', 'email', 'reg_date', 'status')
                ->where('accountofficer_id', $acofficer->id)
                ->where('status', '8')
                ->orderBy('id', 'DESC')->get();


            return view('customers.manage_dom_account')->with('clcustomers', $customers);
        } else {
            return view('customers.manage_dom_account')->with('clcustomers', Customer::select('id', 'last_name', 'first_name', 'acctno', 'account_type', 'section', 'username', 'phone', 'gender', 'email', 'reg_date', 'status')->where('status', '8')->orderBy('created_at', 'DESC')->get());
        }
    }

    public function view_customer()
    {
        if (request()->filter == true) {

            $customer = Customer::select('id', 'last_name', 'first_name', 'acctno', 'account_type', 'section', 'username', 'phone', 'gender', 'email', 'reg_date', 'status')
                ->where('acctno', request()->csdetails)
                ->orWhere('first_name', 'like', '%' . request()->csdetails . '%')
                ->orWhere('last_name', 'like', '%' . request()->csdetails . '%')
                ->get();

            return view('customers.view_customer')->with('customers', $customer);
        } else {

            return view('customers.view_customer');
        }
    }

    public function customer_create()
    {
        return view('customers.create_customer')->with('savingsprods', SavingsProduct::all())
            ->with('exrate', Exchangerate::all())
            ->with('officers', Accountofficer::all());
    }

    public function customer_show($id)
    {
        $balance = Saving::select('account_balance')->where('customer_id', $id)->first();
        return view('customers.show_customer')->with('cutoms', Customer::findorfail($id))
            ->with('balance', $balance);
    }

    public function customer_store(Request $r)
    {
        $this->logInfo("creating customer via core banking", $r->all());

        $this->validate($r, [
            'last_name' => ['required', 'string'],
            'first_name' => ['required', 'string'],
            'gender' => ['required', 'string'],
            'account_type' => ['required', 'string'],
            'account_number' => ['required', 'string', 'digits:10', 'min:10'],
        ]);

        $getsetvalue = new Setting();
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        //     if (Customer::where('email',$r->email)->exists()) {

        //       return redirect()->back()->with('error','Account with these email already exist');

        //   }elseif(Customer::where('phone',$r->phone)->exists()){

        //       return redirect()->back()->with('error','Account with these phone number already exist');

        //   }else
        if (Customer::where('acctno', $r->account_number)->exists()) {

            return redirect()->back()->with('error', 'Account number already exist');
        }

        $refe = Str::random(6);
        $trnxpin = Hash::make(mt_rand('1111', '9999'));

        $passw = mt_rand('11111111', '99999999');

        $cusid = [
            'user_id' => Auth::user()->id,
            'branch_id' => $r->branchid,
            'accountofficer_id' => $r->account_officer,
            'title' => $r->title,
            'first_name' => $r->first_name,
            'last_name' => $r->last_name,
            'email' => $r->email,
            'phone' => $r->phone,
            'gender' => strtolower($r->gender),
            'religion' => $r->religion,
            'marital_status' => $r->marital_status,
            'residential_address' => $r->address,
            'dob' => $r->dob,
            'country' => $r->country,
            'state' => $r->state,
            'state_lga' => $r->lga,
            'account_type' => $r->account_type,
            'section' => $r->account_section,
            'exchangerate_id' => !empty($r->domicilary) ? $r->domicilary : null,
            'acctno' => $r->account_number,
            'refacct' => $r->refacct,
            'bvn' => $r->bvn,
            'next_kin' => $r->kin,
            'kin_address' => $r->kin_address,
            'kin_phone' => $r->kin_phone,
            'kin_relate' => $r->kin_relate,
            'occupation' => $r->occupation,
            'business_name' => $r->business_name,
            'working_status' => $r->working_status,
            'means_of_id' => $r->means_of_id,
            'transfer_limit' => '500000',
            'phone_verify' => '0',
            'pin' => $trnxpin,
            'referral_code' => strtolower($refe),
            'reg_date' => Carbon::now(),
            'source' => 'admin',
            'status' => '7'
        ];
        $cuid = Customer::create($cusid);

        if ($r->hasFile('upload_id')) {
            $photoid = $r->file('upload_id');
            $newphotoid = time() . "_" . $photoid->getClientOriginalName();
            $photoid->move('uploads', $newphotoid);

            Customer::where('id', $cuid->id)->update([
                'photo' => 'uploads/' . $newphotoid,
            ]);
        }

        if ($r->hasFile('photo')) {
            $photp = $r->file('photo');
            $newphoto = time() . "_" . $photp->getClientOriginalName();
            $photp->move('uploads', $newphoto);

            Customer::where('id', $cuid->id)->update([
                'photo' => 'uploads/' . $newphoto,
            ]);
        }

        if ($r->hasFile('signature')) {
            $signature = $r->file('signature');
            $newsignature = time() . "_" . $signature->getClientOriginalName();
            $signature->move('uploads', $newsignature);

            Customer::where('id', $cuid->id)->update([
                'signature' => 'uploads/' . $newsignature,
            ]);
        }

        $this->logInfo("customer created successfully", $cusid);

        //adding to savings
        $this->create_account(Auth::user()->id, $cuid->id, $r->account_type);

        $msg = "Dear Valued Customer,<br><br>

We are pleased to inform you that your account has been successfully created. Please find your account details below:<br><br>

<strong>Account Name:</strong> " . $r->last_name . " " . $r->first_name . "<br>
<strong>Account Number:</strong> " . $r->account_number . "<br>

Thank you for choosing " . ucwords($getsetvalue->getsettingskey('company_name')) . ".";

        //    $msg = "Dear Customer, <br> below is your account details <br>Account Name: ".$r->last_name." ".$r->first_name."<br>Account Number: ".$r->account_number."<br> Bank: ".ucwords($getsetvalue->getsettingskey('company_name'));

        if (isset($r->email)) {
            Email::create([
                'user_id' =>  $cuid->id,
                'subject' => 'Welcome to ' . ucwords($getsetvalue->getsettingskey('company_name')),
                'message' => $msg,
                'recipient' => $r->email,
            ]);

            Mail::send(['html' => 'mails.sendmail'], [
                'msg' => $msg,
                'type' => 'Account Opening'
            ], function ($mail) use ($getsetvalue, $r) {
                $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                $mail->to($r->email);
                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')) . " Account Opening");
            });
        }

        $usern = Auth::user()->last_name . " " . Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id, $branch, $usern, 'customer', 'created a new customer account');

        if ($getsetvalue->getsettingskey('enable_virtual_ac') == '1') {
            $response = Http::withHeaders([
                "PublicKey" => env('PUBLIC_KEY'),
                "EncryptKey" => env('ENCRYPT_KEY'),
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->post(env('VIRTUAL_ACCOUNT_URL'), [
                "settlement_accountno" => env('SETTLEMENT_ACCOUNT'),
                "account_name" =>  $r->last_name . " " . $r->first_name,
                "accountno" =>  $r->account_number
            ]);
        }


        return redirect()->route('customer.index')->with('success', 'Record Created');
    }

    public function customer_edit($id)
    {
        return view('customers.edit_customer')->with('savingsprods', SavingsProduct::all())
            ->with('ced', Customer::findorfail($id))
            ->with('statuses', Status::all())
            ->with('exrate', Exchangerate::all())
            ->with('officers', Accountofficer::all());
    }

    public function customer_update(Request $r, $id)
    {
        $this->logInfo("customer updated by " . Auth::user()->first_name, $r->all());

        $this->validate($r, [
            'last_name' => ['required', 'string'],
            'first_name' => ['required', 'string'],
            'gender' => ['required', 'string'],
            'account_type' => ['required', 'string'],
        ]);


        // $subnum = substr($r->account_number,3);
        // $newacctnumba = $r->pcode."".$subnum;
        $getsetvalue = new Setting();

        $cusid = Customer::findorfail($id);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        if ($r->hasFile('upload_id')) {
            if (file_exists($cusid->upload_id)) {
                unlink($cusid->upload_id);
            }
            $photoid = $r->file('upload_id');
            $newphotoid = time() . "_" . $photoid->getClientOriginalName();
            $photoid->move('uploads', $newphotoid);
            $cusid->upload_id = 'uploads/' . $newphotoid;
        }

        if ($r->hasFile('photo')) {
            if (file_exists($cusid->photo)) {
                unlink($cusid->photo);
            }
            $photp = $r->file('photo');
            $newphoto = time() . "_" . $photp->getClientOriginalName();
            $photp->move('uploads', $newphoto);
            $cusid->photo = 'uploads/' . $newphoto;
        }

        if ($r->hasFile('signature')) {
            if (file_exists($cusid->signature)) {
                unlink($cusid->signature);
            }
            $signature = $r->file('signature');
            $newsignature = time() . "_" . $signature->getClientOriginalName();
            $signature->move('uploads', $newsignature);
            $cusid->signature = 'uploads/' . $newsignature;
        }

        $cusid->accountofficer_id = $r->account_officer;
        $cusid->title = $r->title;
        $cusid->first_name = $r->first_name;
        $cusid->last_name = $r->last_name;
        $cusid->email = $r->email;
        $cusid->phone = $r->phone;
        $cusid->gender = $r->gender;
        $cusid->religion = $r->religion;
        $cusid->marital_status = $r->marital_status;
        $cusid->residential_address = $r->address;
        $cusid->username = $r->username;
        $cusid->dob = $r->dob;
        $cusid->country = $r->country;
        $cusid->state = $r->state;
        $cusid->state_lga = $r->lga;
        $cusid->account_type = $r->account_type;
        $cusid->section = $r->account_section;
        $cusid->exchangerate_id = !empty($r->domicilary) ? $r->domicilary : null;
        $cusid->refacct = $r->refacct;
        $cusid->bvn = $r->bvn;
        $cusid->next_kin = $r->kin;
        $cusid->kin_address = $r->kin_address;
        $cusid->kin_phone = $r->kin_phone;
        $cusid->kin_relate = $r->kin_relate;
        $cusid->transfer_limit = $r->transfer_limit;
        $cusid->lien = $r->lien_account;
        $cusid->occupation = $r->occupation;
        $cusid->business_name = $r->business_name;
        $cusid->working_status = $r->working_status;
        $cusid->means_of_id = $r->means_of_id;
        $cusid->phone_verify = $r->phone_status;
        $cusid->status = $r->account_status;
        $cusid->enable_email_alert = $r->enable_email_alert;
        $cusid->enable_sms_alert = $r->enable_sms_alert;

        if ($r->account_status == "1") {
            $cusid->failed_balance = null;
            $cusid->failed_logins = null;
            $cusid->failed_pin = null;
        }

        $cusid->save();

        //adding to savings
        $this->create_account(Auth::user()->id, $cusid->id, $r->account_type);

        $usern = Auth::user()->last_name . " " . Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id, $branch, $usern, 'customer', 'updated a customer account');

        if ($getsetvalue->getsettingskey('enable_virtual_ac') == '1') {
            $response = Http::withHeaders([
                "PublicKey" => env('PUBLIC_KEY'),
                "EncryptKey" => env('ENCRYPT_KEY'),
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->post(env('VIRTUAL_ACCOUNT_URL'), [
                "settlement_accountno" => env('SETTLEMENT_ACCOUNT'),
                "account_name" =>  $r->last_name . " " . $r->first_name,
                "accountno" => $cusid->acctno
            ]);
        }

        return redirect()->route('customer.edit', ['id' => $id])->with('success', 'Record Updated');
    }

    public function customer_delete($id)
    {
        $cust = Customer::findorfail($id);
        if (file_exists($cust->photo)) {
            unlink($cust->photo);
        }
        if (file_exists($cust->signature)) {
            unlink($cust->signature);
        }
        $cust->delete();
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $usern = Auth::user()->last_name . " " . Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id, $branch, $usern, 'customer', 'deleted a customer account');

        return redirect()->back()->with('success', 'Record Deleted');
    }

    //pin / password reset
    public function customer_reset_pin_password(Request $r)
    {

        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $getsetvalue = new Setting();
        $customerAccunt = Customer::where('id', $r->userid)->first();

        if ($r->type == "Send Password Reset") {
            $this->logInfo("Password reset by " . Auth::user()->first_name, "");

            $passwrd = strtolower(Str::random(8));
            $customerAccunt->password = Hash::make($passwrd);
            $customerAccunt->failed_logins = NUll;
            $customerAccunt->save();


            $usern = Auth::user()->last_name . " " . Auth::user()->first_name;
            $this->tracktrails(Auth::user()->id, $branch, $usern, 'customer', 'sent password reset to customer account');

            $msg = "Hi " . $customerAccunt->last_name . " " . $customerAccunt->first_name . "<br><br> Below is your Password <br> " . $passwrd . " <br><br>Kindly upload your Valid ID and link BVN.";
            Email::create([
                'user_id' =>  Auth::user()->id,
                'subject' => 'Password Reset Request from ' . ucwords($getsetvalue->getsettingskey('company_name')),
                'message' => $msg,
                'recipient' => $customerAccunt->email,
            ]);

            Mail::send(['html' => 'mails.sendmail'], [
                'msg' => $msg,
                'type' => 'Password Reset Request'
            ], function ($mail) use ($customerAccunt, $getsetvalue) {
                $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                $mail->to($customerAccunt->email);
                $mail->subject('Password Reset Request from ' . ucwords($getsetvalue->getsettingskey('company_name')));
            });

            //'customer.edit',['id' => $r->userid]
        } elseif ($r->type == "Send Pin Reset") {
            $this->logInfo("Pin reset by " . Auth::user()->first_name, "");

            $pin = mt_rand('1111', '9999');
            $customerAccunt->pin = Hash::make($pin);
            $customerAccunt->failed_pin = null;
            $customerAccunt->save();


            $usern = Auth::user()->last_name . " " . Auth::user()->first_name;
            $this->tracktrails(Auth::user()->id, $branch, $usern, 'customer', 'sent pin reset customer account');

            $msg = "Hi " . $customerAccunt->last_name . " " . $customerAccunt->first_name . "<br><br> Below is your Pin <br> " . $pin . " <br><br>Kindly upload your Valid ID and link BVN.";
            Email::create([
                'user_id' =>  Auth::user()->id,
                'subject' => 'Pin Reset Request from ' . ucwords($getsetvalue->getsettingskey('company_name')),
                'message' => $msg,
                'recipient' => $customerAccunt->email,
            ]);

            Mail::send(['html' => 'mails.sendmail'], [
                'msg' => $msg,
                'type' => 'Pin Reset Request'
            ], function ($mail) use ($customerAccunt, $getsetvalue) {
                $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                $mail->to($customerAccunt->email);
                $mail->subject('Pin Reset Request from ' . ucwords($getsetvalue->getsettingskey('company_name')));
            });

            //return redirect()->back()->with('success','Pin Reset Sent successfully');
        }

        return redirect()->back()->with('success', 'Reset Request Sent successfully');
    }
    //activate customers account
    public function activate_close_customer(Request $r)
    {
        if (!empty($r->customerid)) {
            if ($r->cmdupdatestatus == "Activate Account(s)") {
                foreach ($r->customerid as $customerid) {
                    Customer::where('id', $customerid)->update([
                        'status' => "1",
                        'failed_balance' => null,
                        'failed_logins' => null,
                        'failed_pin' => null
                    ]);
                }
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

                $usern = Auth::user()->last_name . " " . Auth::user()->first_name;
                $this->tracktrails(Auth::user()->id, $branch, $usern, 'customer', 'activated a customer account');

                return redirect()->back()->with('success', 'Account Activated');
            } elseif ($r->cmdupdatestatus == "Close Account(s)") {
                foreach ($r->customerid as $customerid) {
                    Customer::where('id', $customerid)->update([
                        'status' => "2"
                    ]);
                }
                $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

                $usern = Auth::user()->last_name . " " . Auth::user()->first_name;
                $this->tracktrails(Auth::user()->id, $branch, $usern, 'customer', 'deactivated/closed a customer account');

                return redirect()->back()->with('success', 'Account Closed');
            }
        } else {
            return redirect()->back();
        }
    }

    public function customer_activate($id)
    {
        $this->logInfo("customer activated by" . Auth::user()->first_name, "");

        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        Customer::where('id', $id)->update([
            'status' => request()->status,
            'failed_balance' => null,
            'failed_logins' => null,
            'failed_pin' => null
        ]);

        $usern = Auth::user()->last_name . " " . Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id, $branch, $usern, 'customer', 'activated a customer account');

        return redirect()->back()->with('success', 'Account Activated');
    }

    //close customers account
    public function customer_closed($id)
    {
        $this->logInfo("customer account closed by" . Auth::user()->first_name, "");

        Customer::where('id', $id)->update([
            'status' => request()->status
        ]);
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $usern = Auth::user()->last_name . " " . Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id, $branch, $usern, 'customer', 'deactivated/closed a customer account');

        return redirect()->back()->with('success', 'Account Closed');
    }

    public function create_mail($id = null)
    {
        $getemail = "";
        if (!is_null($id) && request()->sendmail == true) {
            $getemail = Customer::select('email')->where('id', $id)->first();
        }
        return view('communicate.create_emails')->with('remail', $getemail->email)
            ->with('cusem', Customer::select('email')->where('status', '1')->get());
    }

    public function create_sms($id = null)
    {
        $getemail = "";
        if (!is_null($id) && request()->sendsms == true) {
            $cusromers = Customer::select('first_name', 'last_name', 'phone')->where('id', $id)->first();

            return view('communicate.create_sms')->with('cusms', $cusromers);
        } else {
            return view('communicate.create_sms')->with('cusms', Customer::select('first_name', 'last_name', 'phone')->where('status', '1')->get());
        }
    }

    public function getpendingcust()
    {
        if (Auth::user()->roles()->first()->name == 'account officer') {
            $acofficer = Accountofficer::where('user_id', Auth::user()->id)->first();
            $pending = Customer::where('accountofficer_id', $acofficer->id)
                ->where('status', '7')
                ->count();
            return $pending;
        } else {
            $pending =  Customer::where('status', '7')->count();
            return $pending;
        }
    }

    public function getclosecust()
    {
        if (Auth::user()->roles()->first()->name == 'account officer') {
            $acofficer = Accountofficer::where('user_id', Auth::user()->id)->first();
            $closeing = Customer::where('accountofficer_id', $acofficer->id)
                ->where('status', '2')
                ->count();
            return $closeing;
        } else {
            $closeing =  Customer::where('status', '2')->count();
            return $closeing;
        }
    }

    public function getrestricust()
    {
        if (Auth::user()->roles()->first()->name == 'account officer') {
            $acofficer = Accountofficer::where('user_id', Auth::user()->id)->first();
            $closeing = Customer::where('accountofficer_id', $acofficer->id)
                ->whereBetween('status', ['4', '6'])
                ->count();
            return $closeing;
        } else {
            $closeing =  Customer::whereBetween('status', ['4', '6'])->count();
            return $closeing;
        }
    }
    public function getdomcust()
    {
        if (Auth::user()->roles()->first()->name == 'account officer') {
            $acofficer = Accountofficer::where('user_id', Auth::user()->id)->first();
            $closeing = Customer::where('accountofficer_id', $acofficer->id)
                ->where('status', '8')
                ->count();
            return $closeing;
        } else {
            $closeing =  Customer::where('status', '8')->count();
            return $closeing;
        }
    }

    public function getactivecust()
    {
        if (Auth::user()->roles()->first()->name == 'account officer') {
            $acofficer = Accountofficer::where('user_id', Auth::user()->id)->first();
            $activeg = Customer::where('accountofficer_id', $acofficer->id)
                ->where('status', '1')
                ->get()->count();
            return $activeg;
        } else {
            $activeg =  Customer::where('status', '1')->count();
            return $activeg;
        }
    }

    public function store_upload_customer(Request $r)
    {
        $this->validate($r, [
            'customer_file' => ['required', 'mimes:csv', 'max:10240']
        ]);

        $csvfile = $r->file('customer_file');
        $newcsvfile = time() . "_" . $csvfile->getClientOriginalName();
        $csvfile->move('uploads', $newcsvfile);

        // return $_SERVER['DOCUMENT_ROOT']."/uploads/";
        $csvfilepath = $_SERVER['DOCUMENT_ROOT'] . "/uploads/" . $newcsvfile;
        $this->upload_customers_via_excel($csvfilepath);

        unlink($csvfilepath); //remove uploaded file

        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;

        $usern = Auth::user()->last_name . " " . Auth::user()->first_name;
        $this->tracktrails(Auth::user()->id, $branch, $usern, 'customer upload', 'uploaded customers via csv');

        return redirect()->back()->with('success', 'File Uploaded Successfully');
    }

    public function upload_customers_via_excel($filepath)
    {
        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : null;
        $getsetvalue = new Setting();

        $handlefile = fopen($filepath, "r");
        fgetcsv($handlefile); //skip first line
        while (($data = fgetcsv($handlefile, '1000000', ',')) != FALSE) {
            $title = $data[0];
            $fname = $data[1];
            $lname = $data[2];
            $email = $data[3];
            $phone = $data[4];
            $gender = $data[5];
            $religion = $data[6];
            $marital_status = $data[7];
            $address = $data[8];
            $dob = $data[9];
            $state = $data[10];
            $lga = $data[11];
            $account_type = $data[12];
            $bvn = $data[13];

            $actype = SavingsProduct::select('id', 'product_number')->where('name', strtolower($account_type))->first();
            $accno = $actype->product_number . "" . mt_rand('1111111', '9999999');

            $trnxpin = Hash::make(mt_rand('1111', '9999'));

            $cusid = Customer::firstOrCreate([
                'user_id' => Auth::user()->id,
                'branch_id' => $branch,
                'title' => $title,
                'first_name' => $fname,
                'last_name' => $lname,
                'email' => $email,
                'phone' => $phone,
                'gender' => strtolower($gender),
                'religion' => $religion,
                'marital_status' => $marital_status,
                'residential_address' => $address,
                'dob' => $dob,
                'country' => 'nigeria',
                'state' => $state,
                'state_lga' => $lga,
                'account_type' => $actype->id,
                'acctno' => $accno,
                'bvn' => $bvn,
                'phone_verify' => '0',
                'pin' => $trnxpin,
                'reg_date' => Carbon::now(),
                'source' => 'admin',
                'status' => '7'
            ]);

            //creating savings account
            $this->create_account(Auth::user()->id, $cusid->id, $actype->id);

            $msg = "welcome " . $lname . " " . $fname . " below is account details <br> Your Account Number: " . $accno . "<br> Bank: " . ucwords($getsetvalue->getsettingskey('company_name'));


            if (!empty($email) && $email != "") {
                Email::create([
                    'user_id' =>  Auth::user()->id,
                    'subject' => 'Welcome to ' . ucwords($getsetvalue->getsettingskey('company_name')),
                    'message' => $msg,
                    'recipient' => $email,
                ]);


                Mail::send(['html' => 'mails.sendmail'], [
                    'msg' => $msg,
                    'type' => 'Account Registration'
                ], function ($mail) use ($getsetvalue, $email) {
                    $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                    $mail->to($email);
                    $mail->subject('Welcome to ' . ucwords($getsetvalue->getsettingskey('company_name')));
                });
            }
        }

        //Close opened CSV file
        fclose($handlefile);
    }

    public function export_customerbalance_data()
    {

        $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        $filter = request()->filter == true ? true : false;
        $searchval = !empty(request()->searchval) ? request()->searchval : null;
        $fxfilter = request()->fx_filter == "Null" ? null : request()->fx_filter;

        return Excel::download(new CustomersBalanceExport($branch, $searchval, $filter, $fxfilter), 'Customer_balance.xlsx');
    }
}//endclass

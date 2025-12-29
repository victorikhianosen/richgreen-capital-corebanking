<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Loan;
use App\Models\Email;
use App\Models\Saving;
use App\Models\Setting;
use App\Models\Customer;
use App\Models\LoanProduct;
use Illuminate\Support\Str;
use App\Models\FixedDeposit;
use App\Models\LoanSchedule;
use Illuminate\Http\Request;
use App\Models\GeneralLedger;
use App\Models\LoanRepayment;
use App\Models\ProvisionRate;
use App\Models\SubcriptionLog;
use App\Models\OutstandingLoan;
use App\Http\Traites\LoanTraite;
use App\Http\Traites\UserTraite;
use App\Http\Traites\SavingTraite;
use App\Models\InvestmentSchedule;
use App\Models\InvestmetRepayment;
use App\Models\SavingsTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Traites\TransferTraite;
use App\Models\SavingsTransactionGL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Traites\InvestmentTraite;

class CronjobController extends Controller
{
    use LoanTraite;
    use UserTraite;
    use SavingTraite;
    use InvestmentTraite;
    use TransferTraite;

    public function __construct()
    {
        $this->account_dormancy();
    }

    public function birthday_cron()
    {

        $this->logInfo("birthday cron", "");

        $getsetvalue = new Setting();
        // $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;


        $return = "";
        if ($getsetvalue->getsettingskey('enable_cron') == '0') {
            Mail::send(['html' => 'mails.sendmail'], [
                'msg' => 'Cron job is disabled, please enable it in settings',
                'type' => 'Cron Job Disabled'
            ], function ($mail) use ($getsetvalue) {
                $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                $mail->to($getsetvalue->getsettingskey('company_email'));
                $mail->subject('Cron Job Failed ' . ucwords($getsetvalue->getsettingskey('company_name')));
            });

            return 'cron job disabled';
        } else {
            $customers = Customer::whereMonth('dob', '=', Carbon::now()->format('m'))->whereDay('dob', '=', Carbon::now()->format('d'))->get();
            if ($customers) {
                $body = $getsetvalue->getsettingskey('birthday_msg');
                foreach ($customers as $customer) {

                    $msg = str_replace('{name}', ucwords(strtolower($customer->first_name . ' ' . $customer->last_name)), $body);

                    if ($customer->enable_sms_alert) {
                        $this->sendSms($customer->phone, $msg, $getsetvalue->getsettingskey('active_sms')); //send sms
                    }

                    if ($customer->enable_email_alert) {
                        Email::create([
                            'user_id' => '1',
                            'branch_id' => null,
                            'subject' => 'Happy birthday from us at ' . ucwords($getsetvalue->getsettingskey('company_name')),
                            'message' => $msg,
                            'recipient' => $customer->email,
                        ]);

                        Mail::send(['html' => 'mails.sendmail'], [
                            'msg' => $msg,
                            'type' => 'Happy Birthday <span style="font-size:50px;">&#127874; &#127881;</span>'
                        ], function ($mail) use ($customer, $getsetvalue) {
                            $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                            $mail->to($customer->email);
                            $mail->subject('Happy birthday from us at ' . ucwords($getsetvalue->getsettingskey('company_name')));
                        });
                    }
                }
            }
        }
    }

    public function account_dormancy()
    {
        $this->logInfo("dormancy cron", "");


        $domduration = "180";
        $getsetvalue = new Setting();

        $customers = Customer::where('status', '!=', '8')->get();

        $todays = date('Y-m-d');

        foreach ($customers as $customer) {
            $custm = Customer::findorfail($customer->id);

            $domdta = $this->diffbtwdate($custm->created_at, $todays);

            if ($domdta >= $domduration) {
                $trnxs = SavingsTransaction::select('created_at')->where('customer_id', $customer->id)
                    ->orderBy('id', 'DESC')->first();

                if ($trnxs) {
                    $domdta2 = $this->diffbtwdate($trnxs->created_at, $todays);
                    if ($domdta2 >= $domduration) {
                        $custm->status = '8';
                        $custm->save();
                        $msg = "We've noticed that your account $custm->acctno has been inactive for over $domdta2 consecutive days. According to our terms of service, your account is now marked as dormant $todays. From the moment your account has been marked as dormant, you'll have $domduration days to stop the process.";

                        if ($customer->enable_sms_alert) {
                            $this->sendSms($customer->phone, $msg, $getsetvalue->getsettingskey('active_sms')); //send sms
                        }

                        if ($customer->enable_email_alert) {
                            Email::create([
                                'user_id' => '1',
                                'branch_id' => null,
                                'subject' => 'Dom Account',
                                'message' => $msg,
                                'recipient' => $customer->email,
                            ]);

                            Mail::send(['html' => 'mails.sendmail'], [
                                'msg' => $msg,
                                'type' => 'Dom Account'
                            ], function ($mail) use ($customer, $getsetvalue) {
                                $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                                $mail->to($customer->email);
                                $mail->subject('Dom Account');
                            });
                        }
                    }
                }
            }
        }
    }

    public function loan_reminder_cron()
    {
        $this->logInfo("loan reminder cron", "");
        $getsetvalue = new Setting();
        // $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
        $todays = date('Y-m-d');

        if ($getsetvalue->getsettingskey('enable_cron') == '0') {
            Mail::send(['html' => 'mails.sendmail'], [
                'msg' => 'Cron job has it is disabled, please enable it in settings',
                'type' => 'Cron Job Disabled'
            ], function ($mail) use ($getsetvalue) {
                $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                $mail->to($getsetvalue->getsettingskey('company_email'));
                $mail->subject('Cron Job Failed ' . ucwords($getsetvalue->getsettingskey('company_name')));
            });

            return 'cron job disabled';
        } else {
            // send auto_repayment_email_reminder
            if ($getsetvalue->getsettingskey('auto_repayment_email_reminder') == 1) {

                $days = $getsetvalue->getsettingskey('auto_repayment_days');

                //Carbon::now()->subDays($days);
                // date_format(date_add(date_create(date('Y-m-d')),
                //     date_interval_create_from_date_string($days . ' days')),
                //     'Y-m-d');



                $schedules = LoanSchedule::where('closed', '0')->orderBy('due_date', 'ASC')->get();

                foreach ($schedules as $schedule) {
                    $due_date = $this->diffbtwdate($schedule->due_date, $todays);

                    if ($due_date == $days) {

                        //check if borrower has email
                        if (!empty($schedule->customer->email)) {
                            $borrower = $schedule->customer;
                            $loan = $schedule->loan;
                            $body = $getsetvalue->getsettingskey('loan_payment_reminder_email_template');

                            $tms = $schedule->principal + $schedule->interest + $schedule->fees + $schedule->penalty;
                            $tslm = number_format($tms, 2);

                            $body = str_replace('{borrowerFirstName}', $borrower->last_name . " " . $borrower->first_name, $body);
                            $body = str_replace('{loanNumber}', $loan->loan_code, $body);
                            $body = str_replace('{paymentAmount}', "N" . $tslm, $body);
                            $body = str_replace('{paymentDate}', date("d-m-Y", strtotime($schedule->due_date)), $body);

                            $lonbal = $this->loan_total_due_amount($loan->id) - $this->loan_total_paid($loan->id);

                            $msg = $body . "<br> Loan Payment: " . number_format($this->loan_total_paid($loan->id), 2) . "<br> Loan Due: " . number_format($this->loan_total_due_amount($loan->id), 2) . "<br>Loan Balance: " . number_format($lonbal, 2);

                            if ($borrower->enable_sms_alert) {
                                $this->sendSms($borrower->phone, $body, $getsetvalue->getsettingskey('active_sms')); //send sms
                            }

                            if ($borrower->enable_email_alert) {
                                Email::create([
                                    'user_id' => '1',
                                    'branch_id' => null,
                                    'subject' => $getsetvalue->getsettingskey('loan_payment_reminder_subject'),
                                    'message' => $msg,
                                    'recipient' => $borrower->email,
                                ]);

                                Mail::send(['html' => 'mails.sendmail'], [
                                    'msg' => $msg,
                                    'type' => $getsetvalue->getsettingskey('loan_payment_reminder_subject')
                                ], function ($mail) use ($borrower, $getsetvalue) {
                                    $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                                    $mail->to($borrower->email);
                                    $mail->subject($getsetvalue->getsettingskey('loan_payment_reminder_subject'));
                                });
                            }
                        }
                    }
                }
            }

            //send auto_overdue_repayment_days
            if ($getsetvalue->getsettingskey('auto_overdue_repayment_email_reminder') == 1) {

                $days = $getsetvalue->getsettingskey('auto_overdue_repayment_days');

                // $due_date = Carbon::now()->subDays($days);
                // date_format(date_sub(date_create(date('Y-m-d')),
                //     date_interval_create_from_date_string($days . ' days')),
                //     'Y-m-d');

                $schedules = LoanSchedule::where('closed', '0')->orderBy('due_date', 'ASC')->get();

                foreach ($schedules as $schedule) {

                    $due_date = $this->diffbtwdate($schedule->due_date, $todays);

                    if ($due_date == $days) {
                        //check if borrower has email
                        if (!empty($schedule->customer->email)) {
                            $borrower = $schedule->customer;
                            $loan = $schedule->loan;

                            $payments = LoanRepayment::where('loan_id', $loan->id)->where('due_date', $schedule->due_date)->sum('amount');

                            if ($payments == 0) {
                                $body = $getsetvalue->getsettingskey('missed_payment_email_template');

                                $sm = $schedule->principal + $schedule->interest + $schedule->fees + $schedule->penalty;
                                $tsum = number_format($sm, 2);

                                $body = str_replace('{borrowerFirstName}', $borrower->last_name . " " . $borrower->first_name, $body);
                                $body = str_replace('{borrowerAddress}', $borrower->address, $body);
                                $body = str_replace('{loanNumber}', $loan->loan_code, $body);
                                $body = str_replace('{paymentAmount}', "N" . $tsum, $body);
                                $body = str_replace('{paymentDate}', date("d-m-Y", strtotime($schedule->due_date)), $body);


                                $lonbal = $this->loan_total_due_amount($loan->id) - $this->loan_total_paid($loan->id);

                                $msg = $body . "<br> Loan Payment: " . number_format($this->loan_total_paid($loan->id), 2) . "<br> Loan Due: " . number_format($this->loan_total_due_amount($loan->id), 2) . "<br>Loan Balance: " . number_format($lonbal, 2);

                                if ($borrower->enable_sms_alert) {
                                    $this->sendSms($borrower->phone, $body, $getsetvalue->getsettingskey('active_sms')); //send sms
                                }

                                if ($borrower->enable_email_alert) {
                                    Email::create([
                                        'user_id' => '1',
                                        'branch_id' => null,
                                        'subject' => $getsetvalue->getsettingskey('missed_payment_email_subject'),
                                        'message' => $msg,
                                        'recipient' => $borrower->email,
                                    ]);

                                    Mail::send(['html' => 'mails.sendmail'], [
                                        'msg' => $msg,
                                        'type' => $getsetvalue->getsettingskey('missed_payment_email_subject')
                                    ], function ($mail) use ($borrower, $getsetvalue) {
                                        $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                                        $mail->to($borrower->email);
                                        $mail->subject($getsetvalue->getsettingskey('missed_payment_email_subject'));
                                    });
                                }
                            } else {
                                //user has paid something
                            }
                        }
                    }
                }
            }

            if ($getsetvalue->getsettingskey('auto_overdue_loan_email_reminder') == 1) {

                // $days = $getsetvalue->getsettingskey('auto_overdue_loan_days');

                //$due_date = Carbon::now()->subDays($days);
                // date_format(date_sub(date_create(date('Y-m-d')),
                //     date_interval_create_from_date_string($days . ' days')),
                //     'Y-m-d');whereDate('maturity_date','>', $due_date)->

                $loans = Loan::where('loan_status', 'open')->get();

                foreach ($loans as $loan) {
                    $due_date = $this->diffbtwdate($loan->maturity_date, $todays);

                    if ($due_date == $days) {
                        //check if borrower has email
                        if (!empty($loan->customer->email)) {
                            //$borrower = $loan->borrower;

                            $body = $getsetvalue->getsettingskey('loan_overdue_email_template');

                            $body = str_replace('{borrowerFirstName}', $loan->customer->last_name . " " . $loan->customer->first_name, $body);
                            $body = str_replace('{loanNumber}', $loan->loan_code, $body);

                            $lonbal = $this->loan_total_due_amount($loan->id) - $this->loan_total_paid($loan->id);

                            $msg = $body . "<br> Loan Payment: N" . number_format($this->loan_total_paid($loan->id), 2) . "<br> Loan Due: " . number_format($this->loan_total_due_amount($loan->id), 2) . "<br>Loan Balance: " . number_format($lonbal, 2);
                            $sms = $body . "\n Loan Payment: N" . number_format($this->loan_total_paid($loan->id), 2) . "\n Loan Due: " . number_format($this->loan_total_due_amount($loan->id), 2) . "\n Loan Balance: " . number_format($lonbal, 2);

                            if ($borrower->enable_sms_alert) {
                                $this->sendSms($borrower->phone, $sms, $getsetvalue->getsettingskey('active_sms')); //send sms
                            }

                            if ($borrower->enable_email_alert) {
                                Email::create([
                                    'user_id' => $loan->customer->id,
                                    'branch_id' => null,
                                    'subject' => $getsetvalue->getsettingskey('loan_overdue_email_subject'),
                                    'message' => $msg,
                                    'recipient' => $borrower->email,
                                ]);

                                Mail::send(['html' => 'mails.sendmail'], [
                                    'msg' => $msg,
                                    'type' => $getsetvalue->getsettingskey('loan_overdue_email_subject')
                                ], function ($mail) use ($borrower, $getsetvalue) {
                                    $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                                    $mail->to($borrower->email);
                                    $mail->subject($getsetvalue->getsettingskey('loan_overdue_email_subject'));
                                });
                            }
                        }
                    }
                }
            }
        }
    }

    public function loan_cron()
    {  //debit loan from customer account

        try {

            DB::beginTransaction();

            $this->logInfo("loan cron", "");

            $getsetvalue = new Setting();

            //$branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
            $amountpaid = 0;

            if ($getsetvalue->getsettingskey('enable_cron') == '0') {
                Mail::send(['html' => 'mails.sendmail'], [
                    'msg' => 'Cron job has it is disabled, please enable it in settings',
                    'type' => 'Cron Job Disabled'
                ], function ($mail) use ($getsetvalue) {
                    $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                    $mail->to($getsetvalue->getsettingskey('company_email'));
                    $mail->subject('Cron Job Failed ' . ucwords($getsetvalue->getsettingskey('company_name')));
                });

                return 'cron job disabled';
            } else {



                $this->loginfo("loan repayment cron request log", "");

                //$usern = Auth::user()->last_name." ".Auth::user()->first_name;


                $loans = Loan::where('status', 'disbursed')->where('loan_status', 'open')->get();

                foreach ($loans as $loan) {

                    $trxref = $this->generatetrnxref("LR");

                    if (!empty($loan->loan_product)) {

                        $customeracct = Saving::lockForUpdate()->where('customer_id', $loan->customer_id)->first();
                        $customer = Customer::where('id', $loan->customer_id)->first();

                        $usern = $customer->last_name . " " . $customer->first_name;

                        $date = Carbon::now();
                        //Carbon::now()->toDateString();

                        $schedules = LoanSchedule::where('loan_id', $loan->id)->where('closed', 0)->whereDate('due_date', '=', Carbon::today())->first();

                        //$this->loginfo("loan schedule ". $date, $schedules);

                        $loanprod = LoanProduct::select('gl_code', 'interest_gl', 'incomefee_gl')->where('id', $loan->loan_product_id)->first();

                        if (!empty($schedules)) {


                            if ($loanprod) {

                                $getrepamt = LoanRepayment::where('loan_id', $loan->id)->sum('amount');

                                $glacctmloan = GeneralLedger::select('id', 'gl_name', 'status', 'account_balance')
                                    ->where('gl_code', $loanprod->gl_code)
                                    ->first();

                                // $glacctmicro = GeneralLedger::select('id','status','account_balance')->where("gl_code","10739869")->first();
                                // $glacctsme = GeneralLedger::select('id','status','account_balance')->where("gl_code","10156223")->first();10596204
                                $glacctloansuspense = GeneralLedger::select('id', 'status', 'account_balance')->where("gl_code", "10816440")->lockForUpdate()->first();

                                // //loan fee income/suspense
                                // $glacctloanfeeincm = GeneralLedger::select('id','status','account_balance')->where("gl_code","40953331")->first();
                                // $glacctfeeincmsusp = GeneralLedger::select('id','status','account_balance')->where("gl_code","20986758")->first();


                                //loan interest/suspense  "40248362"
                                $glacctloaninterest = GeneralLedger::select('id', 'status', 'account_balance')->where("gl_code", $loanprod->interest_gl)->lockForUpdate()->first();
                                $glacctinterestsusp = GeneralLedger::select('id', 'status', 'account_balance')->where("gl_code", "20258512")->lockForUpdate()->first();
                                $glacctincmsusp = GeneralLedger::select('id', 'status', 'account_balance')->where("gl_code", "20117581")->lockForUpdate()->first();

                                $glsavingdacct = GeneralLedger::select('id', 'status', 'account_balance')->where('gl_code', '20993097')->first();
                                $glcurrentacct = GeneralLedger::select('id', 'status', 'account_balance')->where('gl_code', '20639526')->lockForUpdate()->first();


                                $totschedule = $schedules->principal + $schedules->interest;


                                if ($customeracct->account_balance >= $totschedule) {

                                    $pintamountpaid = 0;
                                    if ($customeracct->account_balance >= $schedules->interest) { //substract interest

                                        // $subprincinrt = $request->repayment_amount[$key] - $schedule->interest;

                                        $scinte = $customeracct->account_balance - $schedules->interest;
                                        $customeracct->account_balance = $scinte;
                                        $customeracct->save();

                                        $this->create_saving_transaction(
                                            null,
                                            $loan->customer_id,
                                            $loan->branch_id,
                                            $schedules->interest,
                                            'debit',
                                            'core',
                                            '0',
                                            null,
                                            null,
                                            null,
                                            null,
                                            $trxref,
                                            'loan interest repayment--' . $loan->loan_code,
                                            'approved',
                                            '18',
                                            'trnsfer',
                                            'system'
                                        );

                                        if (!is_null($customer->exchangerate_id)) {
                                            $this->checkforeigncurrncy($customer->exchangerate_id, $schedules->interest, $trxref, 'debit');
                                        } else {
                                            //if($customer->account_type == '1'){//saving acct GL

                                            if ($glsavingdacct->status == '1') {
                                                $this->gltransaction('deposit', $glsavingdacct, $schedules->interest, null);
                                                $this->create_saving_transaction_gl(null, $glsavingdacct->id, $loan->branch_id, $schedules->interest, 'debit', 'core', $trxref, $this->generatetrnxref('svgl'), 'customer debited for loan interest--' . $loan->loan_code, 'approved', 'system');
                                            }

                                            // }elseif($customer->account_type == '2'){//current acct GL

                                            //     if($glcurrentacct->status == '1'){
                                            //     $this->gltransaction('deposit',$glcurrentacct,$schedules->interest,null);
                                            // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$loan->branch_id,$schedules->interest,'debit','core',$trxref,$this->generatetrnxref('crgl'),'customer debited for loan interest--'.$loan->loan_code,'approved','system');
                                            //     }

                                            // }
                                        }

                                        //loan interest
                                        $this->gltransaction('withdrawal', $glacctloaninterest, $schedules->interest, null);
                                        $this->create_saving_transaction_gl(null, $glacctloaninterest->id, $loan->branch_id, $schedules->interest, 'credit', 'core', $trxref, $this->generatetrnxref('L'), 'loan interest--' . $loan->loan_code, 'approved', 'system');

                                        $pintamountpaid += $schedules->interest;
                                    }


                                    if ($customeracct->account_balance >= $schedules->principal) {

                                        $schprinc = $customeracct->account_balance - $schedules->principal;
                                        $customeracct->account_balance = $schprinc;
                                        $customeracct->save();

                                        $this->create_saving_transaction(
                                            null,
                                            $loan->customer_id,
                                            $loan->branch_id,
                                            $schedules->principal,
                                            'debit',
                                            'core',
                                            '0',
                                            null,
                                            null,
                                            null,
                                            null,
                                            $trxref,
                                            'loan principal repayment--' . $loan->loan_code,
                                            'approved',
                                            '19',
                                            'trnsfer',
                                            'system'
                                        );

                                        if (!is_null($customer->exchangerate_id)) {
                                            $this->checkforeigncurrncy($customer->exchangerate_id, $schedules->principal, $trxref, 'debit');
                                        } else {
                                            // if($customer->account_type == '1'){//saving acct GL
                                            if ($glsavingdacct->status == '1') {
                                                $this->gltransaction('deposit', $glsavingdacct, $schedules->principal, null);
                                                $this->create_saving_transaction_gl(null, $glsavingdacct->id, $loan->branch_id, $schedules->principal, 'debit', 'core', $trxref, $this->generatetrnxref('svgl'), 'customer debited for loan principal--' . $loan->loan_code, 'approved', 'system');
                                            }
                                            // }elseif($customer->account_type == '2'){//current acct GL
                                            //     if($glcurrentacct->status == '1'){
                                            //     $this->gltransaction('deposit',$glcurrentacct,$schedules->principal,null);
                                            // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$loan->branch_id,$schedules->principal,'debit','core',$trxref,$this->generatetrnxref('crgl'),'customer debited for loan principal--'.$loan->loan_code,'approved','system');
                                            //     }
                                            // }
                                        }

                                        if ($glacctmloan->status == "1") {
                                            $this->gltransaction('withdrawal', $glacctmloan, $loan->principal, null);
                                            $this->create_saving_transaction_gl(null, $glacctmloan->id, null, $schedules->principal, 'credit', 'core', $trxref, $this->generatetrnxref('lp'), 'loan repayment--' . $loan->loan_code, 'approved', 'system');
                                        }
                                        // if($loan->principal >= '500' && $loan->principal <= '99000'){

                                        //     $this->gltransaction('deposit',$glacctmicro,$schedules->principal,null);
                                        //     $this->create_saving_transaction_gl(null,$glacctmicro->id,$loan->branch_id, $schedules->principal,'credit','core',$trxref,$this->generatetrnxref('micro'),'micro loans--'.$loan->loan_code,'approved','system');

                                        //     }elseif($loan->principal >= '99000'){

                                        //     $this->gltransaction('deposit',$glacctsme,$schedules->principal,null);
                                        //     $this->create_saving_transaction_gl(null,$glacctsme->id,$loan->branch_id, $schedules->principal,'credit','core',$trxref,$this->generatetrnxref('sme'),'business and sme loans--'.$loan->loan_code,'approved','system');

                                        //     }

                                        $pintamountpaid += $schedules->principal;
                                    }

                                    LoanRepayment::create([
                                        "user_id" => null,
                                        "accountofficer_id" => $loan->accountofficer_id,
                                        "amount" => $pintamountpaid,
                                        "loan_id" => $loan->id,
                                        "customer_id" => $loan->customer_id,
                                        "branch_id" => $loan->branch_id,
                                        "repayment_method" => 'flat',
                                        "due_date" => $schedules->due_date,
                                        "collection_date" => $date,
                                        "type" => 'credit',
                                        "reference" => Str::random(10),
                                        "notes" => 'loan repayment--' . $loan->loan_code,
                                        "status" => '1'
                                    ]);

                                    $sched = LoanSchedule::where('id', $schedules->id)->update([
                                        'closed' => '1'
                                    ]);


                                    DB::commit();
                                } else {


                                    $mainbal = $customeracct->account_balance;

                                    $scinterest = $customeracct->account_balance - $schedules->interest;
                                    $customeracct->account_balance = $scinterest;
                                    $customeracct->save();

                                    $this->create_saving_transaction(
                                        null,
                                        $loan->customer_id,
                                        $loan->branch_id,
                                        $schedules->interest,
                                        'debit',
                                        'core',
                                        '0',
                                        null,
                                        null,
                                        null,
                                        null,
                                        $trxref,
                                        'loan interest repayment--' . $loan->loan_code,
                                        'approved',
                                        '18',
                                        'trnsfer',
                                        'system'
                                    );

                                    if ($mainbal > 0) {
                                        if (!is_null($customer->exchangerate_id)) {
                                            $this->checkforeigncurrncy($customer->exchangerate_id, $schedules->interest, $trxref, 'debit');
                                        } else {
                                            // if($customer->account_type == '1'){//saving acct GL

                                            if ($glsavingdacct->status == '1') {
                                                $this->gltransaction('deposit', $glsavingdacct, $schedules->interest, null);
                                                $this->create_saving_transaction_gl(null, $glsavingdacct->id, $loan->branch_id, $schedules->interest, 'debit', 'core', $trxref, $this->generatetrnxref('svgl'), 'customer debited for loan interest--' . $loan->loan_code, 'approved', 'system');
                                            }

                                            // }elseif($customer->account_type == '2'){//current acct GL

                                            //     if($glcurrentacct->status == '1'){
                                            //     $this->gltransaction('deposit',$glcurrentacct,$schedules->interest,null);
                                            // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$loan->branch_id,$schedules->interest,'debit','core',$trxref,$this->generatetrnxref('crgl'),'customer debited for loan interest--'.$loan->loan_code,'approved','system');
                                            //     }

                                            // }
                                        }
                                    }

                                    //loan interest
                                    $this->gltransaction('withdrawal', $glacctloaninterest, $schedules->interest, null);
                                    $this->create_saving_transaction_gl(null, $glacctloaninterest->id, $loan->branch_id, $schedules->interest, 'credit', 'core', $trxref, $this->generatetrnxref('L'), 'loan interest payment--' . $loan->loan_code, 'approved', 'system');


                                    //loan principal
                                    $schbalpay = $customeracct->account_balance - $schedules->principal;
                                    $customeracct->account_balance = $schbalpay;
                                    $customeracct->save();

                                    $this->create_saving_transaction(
                                        null,
                                        $loan->customer_id,
                                        $loan->branch_id,
                                        $schedules->principal,
                                        'debit',
                                        'core',
                                        '0',
                                        null,
                                        null,
                                        null,
                                        null,
                                        $trxref,
                                        'loan princpal repayment --' . $loan->loan_code,
                                        'approved',
                                        '19',
                                        'trnsfer',
                                        'system'
                                    );

                                    if ($mainbal > 0) {

                                        if (!is_null($customer->exchangerate_id)) {
                                            $this->checkforeigncurrncy($customer->exchangerate_id, $schedules->principal, $trxref, 'debit');
                                        } else {
                                            // if($customer->account_type == '1'){//saving acct GL
                                            if ($glsavingdacct->status == '1') {
                                                $this->gltransaction('deposit', $glsavingdacct, $schedules->principal, null);
                                                $this->create_saving_transaction_gl(null, $glsavingdacct->id, $loan->branch_id, $schedules->principal, 'debit', 'core', $trxref, $this->generatetrnxref('svgl'), 'customer debited for loan principal--' . $loan->loan_code, 'approved', 'system');
                                            }
                                            // }elseif($customer->account_type == '2'){//current acct GL
                                            //     if($glcurrentacct->status == '1'){
                                            //     $this->gltransaction('deposit',$glcurrentacct,$schedules->principal,null);
                                            // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$loan->branch_id,$schedules->principal,'debit','core',$trxref,$this->generatetrnxref('crgl'),'customer debited for loan principal--'.$loan->loan_code,'approved','system');
                                            //     }
                                            // }
                                        }
                                    }

                                    if ($glacctmloan->status == "1") {
                                        $this->gltransaction('withdrawal', $glacctmloan, $loan->principal, null);
                                        $this->create_saving_transaction_gl(null, $glacctmloan->id, null, $schedules->principal, 'credit', 'core', $trxref, $this->generatetrnxref('lp'), 'loan repayment--' . $loan->loan_code, 'approved', 'system');
                                    }

                                    // if($loan->principal >= '500' && $loan->principal <= '99000'){

                                    // $this->gltransaction('deposit',$glacctmicro,$schedules->principal,null);
                                    // $this->create_saving_transaction_gl(null,$glacctmicro->id,$loan->branch_id, $schedules->principal,'credit','core',$trxref,$this->generatetrnxref('micro'),'micro loans--'.$loan->loan_code,'approved','system');

                                    // }elseif($loan->principal >= '99000'){

                                    // $this->gltransaction('deposit',$glacctsme,$schedules->principal,null);
                                    // $this->create_saving_transaction_gl(null,$glacctsme->id,$loan->branch_id, $schedules->principal,'credit','core',$trxref,$this->generatetrnxref('sme'),'business and sme loans--'.$loan->loan_code,'approved','system');

                                    // }

                                    $ttola = $totschedule - $mainbal;
                                    $pendingbal = $mainbal > 0 ? $ttola : $totschedule;

                                    if (!empty($outloan)) {
                                        $oysnt = $outloan->amount + $pendingbal;
                                        $outloan->amount = $oysnt;
                                        $outloan->save();
                                    } else {
                                        OutstandingLoan::create([
                                            'loan_id' => $loan->id,
                                            'customer_id' => $loan->customer_id,
                                            'amount' => $pendingbal
                                        ]);
                                    }

                                    if ($mainbal > 0) {

                                        LoanRepayment::create([
                                            "user_id" => null,
                                            "accountofficer_id" => $loan->accountofficer_id,
                                            "amount" => $mainbal,
                                            "loan_id" => $loan->id,
                                            "customer_id" => $loan->customer_id,
                                            "branch_id" => $loan->branch_id,
                                            "repayment_method" => 'flat',
                                            "due_date" => $schedules->due_date,
                                            "reference" => Str::random(10),
                                            "collection_date" => $date,
                                            "type" => 'credit',
                                            "notes" => 'loan repayment--' . $loan->loan_code,
                                            "status" => '1'
                                        ]);
                                    }

                                    $sched = LoanSchedule::where('id', $schedules->id)->update([
                                        'closed' => '1'
                                    ]);

                                    $suspmamt = $mainbal > 0 ? $ttola : $totschedule;
                                    if ($glacctloansuspense->status == '1') {

                                        $this->gltransaction('deposit', $glacctloansuspense, $suspmamt, null);
                                        $this->create_saving_transaction_gl(null, $glacctloansuspense->id, $loan->branch_id, $suspmamt, 'credit', 'core', $trxref, $this->generatetrnxref('lsusp'), 'loan suspense--' . $loan->loan_code, 'approved', 'system');
                                    }


                                    //loan suspense
                                    // $this->gltransaction('withdrawal',$glacctsme,$totschedule,null);
                                    // $this->create_saving_transaction_gl(null,$glacctsme->id,$loan->branch_id, $totschedule,'debit','core',$trxref,$this->generatetrnxref('lsusp'),'loan suspense--'.$loan->loan_code,'approved','system');

                                    DB::commit();

                                    $this->loginfo("loan repayment added to outstanding--" . $loan->loan_code, "");


                                    $msg = $usern . ", your loan of " . number_format($totschedule, 2) . "is not paid due to insuffient balance, an outstanding payment will be deducted from your account <br>Regards<br>" . ucwords($getsetvalue->getsettingskey('company_name'));
                                    if ($customer->enable_email_alert == "1") {
                                        Email::create([
                                            'user_id' => '1',
                                            'branch_id' => null,
                                            'subject' => 'loan repayment',
                                            'message' => $msg,
                                            'recipient' => $customer->email,
                                        ]);

                                        Mail::send(['html' => 'mails.sendmail'], [
                                            'msg' => $msg,
                                            'type' => 'loan Repayment'
                                        ], function ($mail) use ($customer, $getsetvalue) {
                                            $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                                            $mail->to($customer->email);
                                            $mail->subject('Loan Repayment');
                                        });
                                    }
                                }
                            } else {
                                $this->loginfo("loan product gl", "invalid Loan product --" . $loan->loan_code . "--" . $loan->loan_product_id);
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {

            DB::rollBack();

            $this->loginfo("loan processing error", $e->getMessage() . " -- " . $e->getLine());
        }
    }

    public function missed_loan_payment_cron()
    { //missed payment penalty
        $this->logInfo("missed loan payment", "");

        $getsetvalue = new Setting();
        //$branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;
        global $date;
        if ($getsetvalue->getsettingskey('enable_cron') == '0') {
            Mail::send(['html' => 'mails.sendmail'], [
                'msg' => 'Cron job has it is disabled, please enable it in settings',
                'type' => 'Cron Job Disabled'
            ], function ($mail) use ($getsetvalue) {
                $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                $mail->to($getsetvalue->getsettingskey('company_email'));
                $mail->subject('Cron Job Failed ' . ucwords($getsetvalue->getsettingskey('company_name')));
            });

            return 'cron job disabled';
        } else {

            $msg = "";
            $body = $getsetvalue->getsettingskey('missed_payment_email_template');

            $loans = Loan::where('status', 'disbursed')->where('loan_status', 'open')->get();

            foreach ($loans as $loan) {
                if (!empty($loan->loan_product)) {

                    $customer = Customer::where('id', $loan->customer_id)->first();

                    if ($loan->loan_product->enable_late_repayment_penalty == 1) {

                        $schedules = LoanSchedule::where('loan_id', $loan->id)
                            ->where('missed', '1')
                            ->where('closed', '0')
                            ->orderBy('due_date', 'ASC')->get();

                        foreach ($schedules as $schedule) {
                            if ($loan->loan_product->late_repayment_penalty_grace_period > 0) {
                                $date = date_format(
                                    date_add(
                                        date_create($schedule->due_date),
                                        date_interval_create_from_date_string($loan->loan_product->late_repayment_penalty_grace_period . ' days')
                                    ),
                                    'Y-m-d'
                                );
                            } else {
                                $date = $this->diffbtwdate($schedule->due_date, date('Y-m-d'));
                            }
                            if ($date < $getsetvalue->getsettingskey('auto_overdue_loan_days')) {
                                if ($this->loan_total_due_period($loan->id, $schedule->due_date) > $this->loan_total_paid_period($loan->id, $schedule->due_date)) {

                                    $sch = LoanSchedule::findorfail($schedule->id);
                                    $sch->missed_penalty_applied = 1;
                                    //determine which amount to use
                                    if ($loan->loan_product->late_repayment_penalty_type == "fixed") {
                                        $sch->penalty = $sch->penalty + $loan->loan_product->late_repayment_penalty_amount;
                                    } else {
                                        if ($loan->loan_product->late_repayment_penalty_calculate == 'overdue_principal') {

                                            $principal = ($this->loan_total_principal($loan->id, $schedule->due_date) - $this->loan_paid_item($loan->id, 'principal', $schedule->due_date));

                                            $sch->penalty += (($loan->loan_product->late_repayment_penalty_amount / 100) * $principal);
                                        }
                                        if ($loan->loan_product->late_repayment_penalty_calculate == 'overdue_principal_interest') {
                                            $principal = ($this->loan_total_principal(
                                                $loan->id,
                                                $schedule->due_date
                                            ) + $this->loan_total_interest(
                                                $loan->id,
                                                $schedule->due_date
                                            ) - $this->loan_paid_item(
                                                $loan->id,
                                                'principal',
                                                $schedule->due_date
                                            ) - $this->loan_paid_item($loan->id, 'interest', $schedule->due_date));

                                            $sch->penalty += (($loan->loan_product->late_repayment_penalty_amount / 100) * $principal);
                                        }
                                        if ($loan->loan_product->late_repayment_penalty_calculate == 'overdue_principal_interest_fees') {
                                            $principal = ($this->loan_total_principal(
                                                $loan->id,
                                                $schedule->due_date
                                            ) + $this->loan_total_interest(
                                                $loan->id,
                                                $schedule->due_date
                                            ) + $this->loan_total_fees(
                                                $loan->id,
                                                $schedule->due_date
                                            ) - $this->loan_paid_item(
                                                $loan->id,
                                                'principal',
                                                $schedule->due_date
                                            ) - $this->loan_paid_item(
                                                $loan->id,
                                                'interest',
                                                $schedule->due_date
                                            ) - $this->loan_paid_item(
                                                $loan->id,
                                                'fees',
                                                $schedule->due_date
                                            ));

                                            $sch->penalty += (($loan->loan_product->late_repayment_penalty_amount / 100) * $principal);
                                        }
                                        if ($loan->loan_product->late_repayment_penalty_calculate == 'total_overdue') {
                                            $principal = ($this->loan_total_due_amount(
                                                $loan->id,
                                                $schedule->due_date
                                            ) - $this->loan_total_paid(
                                                $loan->id,
                                                $schedule->due_date
                                            ));
                                            $sch->penalty += (($loan->loan_product->late_repayment_penalty_amount / 100) * $principal);
                                        }
                                    }
                                    $sch->save();
                                }

                                $payamt = $schedule->principal +  $schedule->interest + $schedule->fees;
                                $py = number_format($payamt, 2);

                                $body = str_replace('{borrowerFirstName}', ucwords(strtolower($customer->first_name . ' ' . $customer->last_name)), $body);
                                $body = str_replace('{paymentAmount}', "N" . $py, $body);
                                $body = str_replace('{paymentDate}', date("d-M-Y", strtotime($schedule->due_date)), $body);
                                $body = str_replace('{loanNumber}', $loan->loan_code, $body);

                                //  $smsmsg = str_replace('{borrowerFirstName}',ucwords(strtolower($customer->first_name.' '.$customer->last_name)), $body)."
                                // ".str_replace('{paymentAmount}', number_format($payamt,2), $body)."
                                // ".str_replace('{paymentDate}', date("d-M-Y",strtotime($schedule->due_date)), $body)."
                                // ".str_replace('{loanNumber}', $loan->loan_code, $body);

                                if ($customer->enable_sms_alert) {
                                    $this->sendSms($customer->phone, $body, $getsetvalue->getsettingskey('active_sms')); //send sms
                                }

                                if ($customer->enable_email_alert == "1") {
                                    Email::create([
                                        'user_id' => '1',
                                        'branch_id' => null,
                                        'subject' => 'missed loan repayment',
                                        'message' => $body,
                                        'recipient' => $customer->email,
                                    ]);

                                    Mail::send(['html' => 'mails.sendmail'], [
                                        'msg' => $body,
                                        'type' => 'missed loan Repayment'
                                    ], function ($mail) use ($customer, $getsetvalue) {
                                        $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                                        $mail->to($customer->email);
                                        $mail->subject($getsetvalue->getsettingskey('missed_payment_email_subject'));
                                    });
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function after_loan_maturity_cron()
    { //after maturity date payment
        $getsetvalue = new Setting();
        // $branch = session()->has('branchid') ? session()->get('branchid')['bid'] : Auth::user()->branch_id;

        if ($getsetvalue->getsettingskey('enable_cron') == '0') {
            Mail::send(['html' => 'mails.sendmail'], [
                'msg' => 'Cron job has it is disabled, please enable it in settings',
                'type' => 'Cron Job Disabled'
            ], function ($mail) use ($getsetvalue) {
                $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                $mail->to($getsetvalue->getsettingskey('company_email'));
                $mail->subject('Cron Job Failed ' . ucwords($getsetvalue->getsettingskey('company_name')));
            });

            return 'cron job disabled';
        } else {

            $loans = Loan::where('status', 'disbursed')->get();
            foreach ($loans as $loan) {
                $product = $loan->loan_product;
                if (!empty($product)) {

                    $date = Carbon::now()->subDays($product->late_repayment_penalty_grace_period);

                    //   date_format(date_sub(date_create(date('Y-m-d')),
                    //       date_interval_create_from_date_string($product->late_repayment_penalty_grace_period . ' days')),
                    //       'Y-m-d');

                    $schedule = LoanSchedule::where('loan_id', $loan->id)
                        ->whereDate('due_date', $date)->first();

                    if (!empty($schedule)) {
                        $due_items = $this->loan_due_items($loan->id, $loan->release_date, $date);
                        $paid_items = $this->loan_paid_items(
                            $loan->id,
                            $loan->release_date,
                            date('Y-m-d')
                        );
                    }


                    $due_items = $this->loan_due_items($loan->id, $loan->release_date, Carbon::now()->toDateString());
                    $paid_items = $this->loan_paid_items($loan->id, $loan->release_date, Carbon::now()->toDateString());

                    if ($loan->maturity_date < date('Y-m-d')) {

                        $loa = Loan::where('id', $loan->id)->first();
                        $checkmaturity = $this->diffbtwdate($loan->maturity_date, date('Y-m-d'));
                        foreach (ProvisionRate::all() as $prvisn) {
                            if ($checkmaturity ==  $prvisn->days) {
                                $prvamtv = $this->loan_paid_item($loan->id) / 100 * $prvisn->rate;
                                $loa->provision_date = Carbon::now();
                                $loa->provision_amount = $prvamtv;
                                $loa->provision_type = $prvisn->name;
                                $loa->save();
                            }
                        }
                    }



                    if ($loan->maturity_date < date('Y-m-d') && ($due_items["interest"] + $due_items["principal"] + $due_items["fees"] + $due_items["penalty"] - $paid_items["interest"] - $paid_items["principal"] - $paid_items["fees"] - $paid_items["penalty"]) > 0) {


                        $schedule = LoanSchedule::where('loan_id', $loan->id)
                            ->orderBy('due_date', 'desc')->first();

                        if (!empty($schedule)) {
                            //update schedule
                            $schedule->penalty = $schedule->penalty;
                            $schedule->missed_penalty_applied = 1;
                            $schedule->save();
                        }
                    }
                }
            }
        }
    }

    public function subcription_cron()
    {
        $checkSub = SubcriptionLog::whereDate('expiration_date', Carbon::now()->toDateString())->where('is_active', '1')->first();
        if ($checkSub) {
            $checkSub->is_active = '0';
            $checkSub->save();
        }
    }

    public function subcription_warning_cron()
    {
        $getsetvalue = new Setting();

        $subwarn = SubcriptionLog::whereDate('warning_date', Carbon::now()->toDateString())->where('is_active', '1')->first();

        if ($subwarn) {
            $msg =  "hello " . ucwords($getsetvalue->getsettingskey('company_name')) . "<br> your current subcription for " . $subwarn->subcription . " will expire on " . date("d-M-Y", strtotime($subwarn->expiration_date));
            Email::create([
                'user_id' => null,
                'branch_id' => null,
                'subject' => 'subcription reminder',
                'message' => $msg,
                'recipient' => $getsetvalue->getsettingskey('company_email'),
            ]);

            Mail::send(['html' => 'mails.sendmail'], [
                'msg' => $msg,
                'type' => 'subcription reminder'
            ], function ($mail) use ($getsetvalue) {
                $mail->from("no-reply@mybanqpro.com", ucwords('banqpro'));
                $mail->to($getsetvalue->getsettingskey('company_email'));
                $mail->subject('Subcription Reminder');
            });
        }
    }

    public function investment_cron()
    {

        try {

            $this->logInfo("investment cron started", "");

            DB::beginTransaction();

            $getsetvalue = new Setting();

            $fixeddosits = FixedDeposit::where('status', 'approved')->where('fd_status', 'open')->get();

            $glfixeddacct = GeneralLedger::select('id', 'status', 'account_balance')->where('gl_code', '20944548')->lockForUpdate()->first(); //fixed deposit gl
            $glinterestexpacct = GeneralLedger::select('id', 'status', 'account_balance')->where('gl_code', '50249457')->lockForUpdate()->first(); //interest expenses
            $glwithhdtaxacct = GeneralLedger::select('id', 'status', 'account_balance')->where('gl_code', '20391084')->lockForUpdate()->first(); //withholding tax
            // $glfdchrgacct = GeneralLedger::select('id','status','account_balance')->where('gl_code', $getsetvalue->getsettingskey('fdliquid_interest'))->first();//for liquidation charge

            $glsavingdacct = GeneralLedger::select('id', 'status', 'account_balance')->where('gl_code', '20993097')->lockForUpdate()->first(); //saving account gl
            $glcurrentacct = GeneralLedger::select('id', 'status', 'account_balance')->where('gl_code', '20639526')->lockForUpdate()->first(); //current account gl

            foreach ($fixeddosits as $fxd) {

                $tref =  $this->generatetrnxref("fd");

                $fdcd = $fxd->fixed_deposit_code;

                $schedules = InvestmentSchedule::where('fixed_deposit_id', $fxd->id)
                    ->where('closed', '0')
                    ->whereDate('due_date', '=', Carbon::now())->first();

                $rolloverinterst = $this->investment_total_interest($fxd->id, '');

                $smrolover = InvestmentSchedule::where('fixed_deposit_id', $fxd->id)
                    ->where('customer_id', $fxd->customer_id)->sum('interest');

                $sches = InvestmentSchedule::where('fixed_deposit_id', $fxd->id)
                    ->where('customer_id', $fxd->customer_id)->get();

                $customeracct = Saving::lockForUpdate()->where('customer_id', $fxd->customer_id)->first();
                $customer = Customer::where('id', $fxd->customer_id)->first();

                //if($schedules){
                if ($fxd->interest_method == "upfront") {

                    if ($fxd->maturity_date == Carbon::today()) {

                        $fxdprl = $customeracct->account_balance + $fxd->principal;
                        $customeracct->account_balance = $fxdprl;
                        $customeracct->save();



                        $this->create_saving_transaction(
                            null,
                            $fxd->customer_id,
                            $fxd->branch_id,
                            $fxd->principal,
                            'credit',
                            'core',
                            '0',
                            null,
                            null,
                            null,
                            null,
                            $tref,
                            'fixed deposit investment liquidation - ' . $fdcd,
                            'approved',
                            '12',
                            'trnsfer',
                            'system'
                        );

                        if (!is_null($customer->exchangerate_id)) {
                            $this->checkforeigncurrncy($customer->exchangerate_id, $fxd->principal, $tref, 'credit');
                            $this->foreigncurrncyinvestment($customer->exchangerate_id, $fxd->principal, $tref, 'debit', 'system');
                        } else {
                            //deposit into saving acct and current acct Gl
                            //if($customer->account_type == '1'){//saving acct GL

                            if ($glcurrentacct->status == '1') {
                                $this->gltransaction('withdrawal', $glsavingdacct, $fxd->principal, null);
                                $this->create_saving_transaction_gl(null, $glsavingdacct->id, $fxd->branch_id, $fxd->principal, 'credit', 'core', $tref, $this->generatetrnxref('svgl'), 'customer credited', 'approved', 'system');
                            }

                            // }elseif($customer->account_type == '2'){//current acct GL

                            // $this->gltransaction('withdrawal',$glcurrentacct,$fxd->principal,null);
                            // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$fxd->branch_id, $fxd->principal,'credit','core',$tref,$this->generatetrnxref('crgl'),'customer credited','approved','system');

                            // }
                            //debit fd investment gl
                            if ($glfixeddacct->status == '1') {

                                $this->gltransaction('deposit', $glfixeddacct, $fxd->principal, null);
                                $this->create_saving_transaction_gl(null, $glfixeddacct->id, $fxd->branch_id, $fxd->principal, 'debit', 'core', $tref, $this->generatetrnxref('inv'), 'debit investment- ' . $fxd->fixed_deposit_code, 'approved', 'system');
                            }
                        }


                        InvestmetRepayment::create([
                            'fixed_deposit_id' => $fxd->id,
                            'accountofficer_id' => $fxd->accountofficer_id,
                            'customer_id' => $fxd->customer_id,
                            'branch_id' => $fxd->branch_id,
                            'amount' => $fxd->principal,
                            'collection_date' => Carbon::now(),
                            'notes' => 'principal paid- ' . $fxd->fixed_deposit_code,
                            'payment_method' => 'flat',
                            'due_date' => Carbon::now()
                        ]);

                        $fxds = FixedDeposit::findorfail($fxd->id);
                        $fxds->closed_notes  = 'fixed deposit liquidated';
                        $fxds->closed_date = Carbon::now();
                        $fxds->fd_status = 'fully_paid';
                        $fxds->status = 'closed';
                        $fxds->save();

                        //close schedules
                        foreach ($sches as $itemclose) {
                            $sched = InvestmentSchedule::where('id', $itemclose->id)->first();
                            $sched->payment_date = Carbon::now();
                            $sched->payment_method = "auto";
                            $sched->posted_by = "system";
                            $sched->closed = '1';
                            $sched->save();
                        }

                        $smsmsg = "Credit Amt: N" . number_format($fxd->principal, 2) . "\n Desc: fixed deposit investment liquidation \n Avail Bal: " . number_format($customeracct->account_balance, 2) . "\n Date: " . date("Y-m-d") . "\n Ref: " . $tref;

                        if ($customer->enable_sms_alert) {
                            $this->sendSms($customer->phone, $smsmsg, $getsetvalue->getsettingskey('active_sms')); //send sms
                        }

                        if ($customer->enable_email_alert) {
                            $msg =  "Credit Amt: N" . number_format($fxd->principal, 2) . "<br> Desc: fixed deposit investment liquidation <br>Avail Bal: N" . number_format($customeracct->account_balance, 2) . "<br> Date: " . date("Y-m-d") . "<br>Ref: " . $tref;
                            Email::create([
                                'user_id' => $customer->id,
                                'subject' => ucwords($getsetvalue->getsettingskey('company_name')) . ' Credit Alert',
                                'message' => $msg,
                                'recipient' => $customer->email,
                            ]);

                            Mail::send(['html' => 'mails.sendmail'], [
                                'msg' => $msg,
                                'type' => 'Credit Transaction'
                            ], function ($mail) use ($getsetvalue, $customer) {
                                $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                                $mail->to($customer->email);
                                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')) . ' Credit Alert');
                            });
                        }

                        if ($fxd->auto_book_investment == '1') {
                            $autoinvest = new InvestmentController();
                            $autoinvest->autoBookFixed_deposit($fxd->id, $fxd->principal);
                        }
                    }
                }

                //simple rollover
                if ($fxd->interest_method == "simple_rollover") {
                    if ($fxd->maturity_date == date("Y-m-d")) {

                        $prcipal = $fxd->principal + $smrolover;

                        $tprcipal = $customeracct->account_balance + $prcipal;
                        $customeracct->account_balance = $tprcipal;
                        $customeracct->save();




                        $this->create_saving_transaction(
                            null,
                            $fxd->customer_id,
                            $fxd->branch_id,
                            $prcipal,
                            'credit',
                            'core',
                            '0',
                            null,
                            null,
                            null,
                            null,
                            $tref,
                            'fixed deposit investment liquidation- ' . $fdcd,
                            'approved',
                            '12',
                            'trnsfer',
                            'system'
                        );

                        if (!is_null($customer->exchangerate_id)) {
                            $this->checkforeigncurrncy($customer->exchangerate_id, $prcipal, $tref, 'credit');
                            $this->foreigncurrncyinterestExpense($customer->exchangerate_id, $smrolover, $tref, $fxd->fixed_deposit_code);
                        } else {
                            //deposit into saving acct and current acct Gl
                            //if($customer->account_type == '1'){//saving acct GL
                            if ($glsavingdacct->status == '1') { //saving acct GL

                                $this->gltransaction('withdrawal', $glsavingdacct, $prcipal, null);
                                $this->create_saving_transaction_gl(null, $glsavingdacct->id, $fxd->branch_id, $prcipal, 'credit', 'core', $tref, $this->generatetrnxref('svgl'), 'customer credited', 'approved', 'system');
                            }
                            // }elseif($customer->account_type == '2'){//current acct GL

                            // $this->gltransaction('withdrawal',$glcurrentacct,$prcipal,null);
                            // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$fxd->branch_id, $prcipal,'credit','core',$tref,$this->generatetrnxref('crgl'),'customer credited','approved','system');

                            // }
                            //debit fd investment gl
                            if ($glfixeddacct->status == '1') {

                                $this->gltransaction('deposit', $glfixeddacct, $fxd->principal, null);
                                $this->create_saving_transaction_gl(null, $glfixeddacct->id, $fxd->branch_id, $fxd->principal, 'debit', 'core', $tref, $this->generatetrnxref('inv'), 'debit investment', 'approved', 'system');
                            }

                            //debit interest expenses(add)
                            if ($glinterestexpacct->status == '1') {

                                $this->gltransaction('withdrawal', $glinterestexpacct, $smrolover, null);
                                $this->create_saving_transaction_gl(null, $glinterestexpacct->id, $fxd->branch_id, $smrolover, 'debit', 'core', $tref, $this->generatetrnxref('intrexp'), 'fixed deposit investment interest -' . $fxd->fixed_deposit_code, 'approved', 'system');
                            }
                        }


                        InvestmetRepayment::create([
                            'fixed_deposit_id' => $fxd->id,
                            'accountofficer_id' => $fxd->accountofficer_id,
                            'customer_id' => $fxd->customer_id,
                            'branch_id' => $fxd->branch_id,
                            'amount' => $prcipal,
                            'collection_date' => Carbon::now(),
                            'notes' => 'principal and interest paid- ' . $fxd->fixed_deposit_code,
                            'payment_method' => 'flat',
                            'due_date' => Carbon::now()
                        ]);

                        $fxds = FixedDeposit::findorfail($fxd->id);
                        $fxds->closed_notes  = 'fixed deposit liquidated';
                        $fxds->closed_date = Carbon::now();
                        $fxds->fd_status = 'fully_paid';
                        $fxds->status = 'closed';
                        $fxds->save();

                        //close schedules
                        foreach ($sches as $itemclose) {
                            $sched = InvestmentSchedule::where('id', $itemclose->id)->first();
                            $sched->payment_date = Carbon::now();
                            $sched->payment_method = "auto";
                            $sched->posted_by = "system";
                            $sched->closed = '1';
                            $sched->save();
                        }

                        $smsmsg = "Credit Amt: N" . number_format($prcipal, 2) . "\n Desc: fixed deposit investment liquidation \n Avail Bal: " . number_format($customeracct->account_balance, 2) . "\n Date: " . date("Y-m-d") . "\n Ref: " . $tref;

                        if ($customer->enable_sms_alert) {
                            $this->sendSms($customer->phone, $smsmsg, $getsetvalue->getsettingskey('active_sms')); //send sms
                        }

                        if ($customer->enable_email_alert) {
                            $msg =  "Credit Amt: N" . number_format($prcipal, 2) . "<br> Desc: fixed deposit investment liquidation <br>Avail Bal: N" . number_format($customeracct->account_balance, 2) . "<br> Date: " . date("Y-m-d") . "<br>Ref: " . $tref;
                            Email::create([
                                'user_id' => $customer->id,
                                'subject' => ucwords($getsetvalue->getsettingskey('company_name')) . ' Credit Alert',
                                'message' => $msg,
                                'recipient' => $customer->email,
                            ]);

                            Mail::send(['html' => 'mails.sendmail'], [
                                'msg' => $msg,
                                'type' => 'Credit Transaction'
                            ], function ($mail) use ($getsetvalue, $customer) {
                                $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                                $mail->to($customer->email);
                                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')) . ' Credit Alert');
                            });
                        }

                        if ($fxd->enable_withholding_tax == '1') {

                            $wtref = $this->generatetrnxref('whtx');

                            $withhdtax = $fxd->withholding_tax / 100 * $smrolover;

                            $witax = $customeracct->account_balance - $withhdtax;
                            $customeracct->account_balance = $witax;
                            $customeracct->save();

                            $this->create_saving_transaction(
                                null,
                                $customer->id,
                                $fxd->branch_id,
                                $withhdtax,
                                'debit',
                                'core',
                                '0',
                                null,
                                null,
                                null,
                                null,
                                $wtref,
                                'withholding tax- ' . $fdcd,
                                'approved',
                                '11',
                                'trnsfer',
                                'system'
                            );


                            if (!is_null($customer->exchangerate_id)) {
                                $this->checkforeigncurrncy($customer->exchangerate_id, $withhdtax, $wtref, 'debit');
                                $this->foreigncurrncywtholdingTax($customer->exchangerate_id, $withhdtax, $wtref);
                            } else {
                                //deposit into saving acct and current acct Gl
                                if ($glsavingdacct->status == '1') { //saving acct GL

                                    $this->gltransaction('deposit', $glsavingdacct, $withhdtax, null);
                                    $this->create_saving_transaction_gl(null, $glsavingdacct->id, $fxd->branch_id, $withhdtax, 'debit', 'core', $wtref, $this->generatetrnxref('svgl'), 'customer debited', 'approved', 'system');
                                }
                                // }elseif($customer->account_type == '2'){//current acct GL

                                //     $this->gltransaction('deposit',$glcurrentacct,$withhdtax,null);
                                // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$fxd->branch_id,$withhdtax,'debit','core',$wtref,$this->generatetrnxref('crgl'),'customer debited','approved','system');

                                // }
                                //add withholding tax
                                if ($glwithhdtaxacct->status == '1') {

                                    $this->gltransaction('withdrawal', $glwithhdtaxacct, $withhdtax, null);
                                    $this->create_saving_transaction_gl(null, $glwithhdtaxacct->id, $fxd->branch_id, $withhdtax, 'credit', 'core', $wtref, $this->generatetrnxref('withtx'), 'withholding tax', 'approved', 'system');
                                }
                            }


                            $smsmsg = "Debit Amt: N" . number_format($withhdtax, 2) . "\n Desc: Fixed deposit withholding tax \n Avail Bal: " . number_format($customeracct->account_balance, 2) . "\n Date: " . date("Y-m-d") . "\n Ref: " . $wtref;

                            if ($customer->enable_sms_alert) {
                                $this->sendSms($customer->phone, $smsmsg, $getsetvalue->getsettingskey('active_sms')); //send sms
                            }

                            if ($customer->enable_email_alert) {
                                $msg =  "Debit Amt: N" . number_format($withhdtax) . "<br> Desc: Fixed deposit withholding tax <br>Avail Bal: N" . number_format($customeracct->account_balance, 2) . "<br> Date: " . date("Y-m-d") . "<br>Ref: " . $wtref;
                                Email::create([
                                    'user_id' => $customer->id,
                                    'subject' => ucwords($getsetvalue->getsettingskey('company_name')) . ' Debit Alert',
                                    'message' => $msg,
                                    'recipient' => $customer->email,
                                ]);

                                Mail::send(['html' => 'mails.sendmail'], [
                                    'msg' => $msg,
                                    'type' => 'Debit Transaction'
                                ], function ($mail) use ($getsetvalue, $customer) {
                                    $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                                    $mail->to($customer->email);
                                    $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')) . ' Debit Alert');
                                });
                            }
                        }

                        if ($fxd->auto_book_investment == '1') {
                            $autoinvest = new InvestmentController();
                            $autoinvest->autoBookFixed_deposit($fxd->id, $fxd->principal);
                        }
                    }
                }

                if ($fxd->interest_method == "monthly") {
                    if (!empty($schedules)) {
                        if ($fxd->maturity_date == date("Y-m-d")) {

                            $prpale = $fxd->principal;

                            $pr = $fxd->principal + $schedules->interest;

                            $tpr = $customeracct->account_balance + $pr;
                            $customeracct->account_balance = $tpr;
                            $customeracct->save();



                            $this->create_saving_transaction(
                                null,
                                $fxd->customer_id,
                                $fxd->branch_id,
                                $pr,
                                'credit',
                                'core',
                                '0',
                                null,
                                null,
                                null,
                                null,
                                $tref,
                                'fixed deposit investment liquidation- ' . $fdcd,
                                'approved',
                                '12',
                                'trnsfer',
                                'system'
                            );


                            if (!is_null($customer->exchangerate_id)) {
                                $this->checkforeigncurrncy($customer->exchangerate_id, $pr, $tref, 'credit');
                                $this->foreigncurrncyinvestment($customer->exchangerate_id, $fxd->principal, $tref, 'debit', 'system');
                                $this->foreigncurrncyinterestExpense($customer->exchangerate_id, $schedules->interest, $tref, $fxd->fixed_deposit_code);
                            } else {
                                //deposit into saving acct and current acct Gl
                                if ($glsavingdacct->status == '1') { //saving acct GL

                                    $this->gltransaction('withdrawal', $glsavingdacct, $pr, null);
                                    $this->create_saving_transaction_gl(null, $glsavingdacct->id, $fxd->branch_id, $pr, 'credit', 'core', $tref, $this->generatetrnxref('svgl'), 'customer credited', 'approved', 'system');
                                }
                                // }elseif($customer->account_type == '2'){//current acct GL

                                // $this->gltransaction('withdrawal',$glcurrentacct,$pr,null);
                                // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$fxd->branch_id, $pr,'credit','core',$tref,$this->generatetrnxref('crgl'),'customer credited','approved','system');

                                // }

                                //debit fd investment gl
                                if ($glfixeddacct->status == '1') {

                                    $this->gltransaction('deposit', $glfixeddacct, $fxd->principal, null);
                                    $this->create_saving_transaction_gl(null, $glfixeddacct->id, $fxd->branch_id, $fxd->principal, 'debit', 'core', $tref, $this->generatetrnxref('inv'), 'debit investment', 'approved', 'system');
                                }

                                //debit interest expenses(add)
                                if ($glinterestexpacct->status == '1') {

                                    $this->gltransaction('withdrawal', $glinterestexpacct, $schedules->interest, null);
                                    $this->create_saving_transaction_gl(null, $glinterestexpacct->id, $fxd->branch_id, $schedules->interest, 'debit', 'core', $tref, $this->generatetrnxref('intrexp'), 'fixed deposit investment interest -' . $fxd->fixed_deposit_code, 'approved', 'system');
                                }
                            }


                            $fxds = FixedDeposit::where('id', $fxd->id)->first();
                            $fxds->closed_notes  = 'fixed deposit liquidated';
                            $fxds->closed_date = Carbon::now();
                            $fxds->fd_status = 'fully_paid';
                            $fxds->status = 'closed';
                            $fxds->save();

                            $schedules->payment_date = Carbon::now();
                            $schedules->payment_method = "auto";
                            $schedules->posted_by = "system";
                            $schedules->closed = '1';
                            $schedules->save();

                            InvestmetRepayment::create([
                                'fixed_deposit_id' => $fxd->id,
                                'accountofficer_id' => $fxd->accountofficer_id,
                                'customer_id' => $fxd->customer_id,
                                'branch_id' => $fxd->branch_id,
                                'amount' => $schedules->interest,
                                'collection_date' => Carbon::now(),
                                'notes' => 'principal paid - ' . $fxd->fixed_deposit_code,
                                'payment_method' => 'flat',
                                'due_date' => Carbon::now()
                            ]);

                            $smsmsg = "Credit Amt: N" . number_format($pr, 2) . "\n Desc: fixed deposit investment  \n Avail Bal: " . number_format($customeracct->account_balance, 2) . "\n Date: " . date("Y-m-d") . "\n Ref: " . $tref;

                            if ($customer->enable_sms_alert) {
                                $this->sendSms($customer->phone, $smsmsg, $getsetvalue->getsettingskey('active_sms')); //send sms
                            }

                            if ($customer->enable_email_alert) {
                                $msg =  "Credit Amt: N" . number_format($pr, 2) . "<br> Desc: fixed deposit investment <br>Avail Bal: N" . number_format($customeracct->account_balance, 2) . "<br> Date: " . date("Y-m-d") . "<br>Ref: " . $tref;
                                Email::create([
                                    'user_id' => $customer->id,
                                    'subject' => ucwords($getsetvalue->getsettingskey('company_name')) . ' Credit Alert',
                                    'message' => $msg,
                                    'recipient' => $customer->email,
                                ]);

                                Mail::send(['html' => 'mails.sendmail'], [
                                    'msg' => $msg,
                                    'type' => 'Credit Transaction'
                                ], function ($mail) use ($getsetvalue, $customer) {
                                    $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                                    $mail->to($customer->email);
                                    $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')) . ' Credit Alert');
                                });
                            }

                            if ($fxd->enable_withholding_tax == '1') {

                                $wtref = $this->generatetrnxref('whtx');

                                $withhdtax = $fxd->withholding_tax / 100 * $schedules->interest;

                                $whhdtax = $customeracct->account_balance - $withhdtax;
                                $customeracct->account_balance = $whhdtax;
                                $customeracct->save();

                                $this->create_saving_transaction(
                                    null,
                                    $customer->id,
                                    $fxd->branch_id,
                                    $withhdtax,
                                    'debit',
                                    'core',
                                    '0',
                                    null,
                                    null,
                                    null,
                                    null,
                                    $wtref,
                                    'withholding tax-' . $fdcd,
                                    'approved',
                                    '11',
                                    'trnsfer',
                                    'system'
                                );

                                if (!is_null($customer->exchangerate_id)) {
                                    $this->checkforeigncurrncy($customer->exchangerate_id, $withhdtax, $wtref, 'debit');
                                    $this->foreigncurrncywtholdingTax($customer->exchangerate_id, $withhdtax, $wtref);
                                } else {
                                    //deposit into saving acct and current acct Gl
                                    if ($glsavingdacct->status == '1') { //saving acct GL

                                        $this->gltransaction('deposit', $glsavingdacct, $withhdtax, null);
                                        $this->create_saving_transaction_gl(null, $glsavingdacct->id, $fxd->branch_id, $withhdtax, 'debit', 'core', $wtref, $this->generatetrnxref('svgl'), 'customer debited', 'approved', 'system');
                                    }
                                    // }elseif($customer->account_type == '2'){//current acct GL

                                    //     $this->gltransaction('deposit',$glcurrentacct,$withhdtax,null);
                                    // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$fxd->branch_id,$withhdtax,'debit','core',$wtref,$this->generatetrnxref('crgl'),'customer debited','approved','system');

                                    // }
                                    //add withholding tax
                                    if ($glwithhdtaxacct->status == '1') {

                                        $this->gltransaction('withdrawal', $glwithhdtaxacct, $withhdtax, null);
                                        $this->create_saving_transaction_gl(null, $glwithhdtaxacct->id, $fxd->branch_id, $withhdtax, 'credit', 'core', $wtref, $this->generatetrnxref('withtx'), 'withholding tax', 'approved', 'system');
                                    }
                                }


                                $smsmsg = "Debit Amt: N" . number_format($withhdtax, 2) . "\n Desc: Fixed deposit withholding tax \n Avail Bal: " . number_format($customeracct->account_balance, 2) . "\n Date: " . date("Y-m-d") . "\n Ref: " . $wtref;

                                if ($customer->enable_sms_alert) {
                                    $this->sendSms($customer->phone, $smsmsg, $getsetvalue->getsettingskey('active_sms')); //send sms
                                }

                                if ($customer->enable_email_alert) {
                                    $msg =  "Debit Amt: N" . number_format($withhdtax, 2) . "<br> Desc: Fixed deposit withholding tax <br>Avail Bal: N" . number_format($customeracct->account_balance, 2) . "<br> Date: " . date("Y-m-d") . "<br>Ref: " . $wtref;
                                    Email::create([
                                        'user_id' => $customer->id,
                                        'subject' => ucwords($getsetvalue->getsettingskey('company_name')) . ' Debit Alert',
                                        'message' => $msg,
                                        'recipient' => $customer->email,
                                    ]);

                                    Mail::send(['html' => 'mails.sendmail'], [
                                        'msg' => $msg,
                                        'type' => 'Debit Transaction'
                                    ], function ($mail) use ($getsetvalue, $customer) {
                                        $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                                        $mail->to($customer->email);
                                        $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')) . ' Debit Alert');
                                    });
                                }
                            }

                            if ($fxd->auto_book_investment == '1') {
                                $autoinvest = new InvestmentController();
                                $autoinvest->autoBookFixed_deposit($fxd->id, $prpale);
                            }
                        } else {

                            $schst = $customeracct->account_balance + $schedules->interest;
                            $customeracct->account_balance = $schst;
                            $customeracct->save();



                            $this->create_saving_transaction(
                                null,
                                $fxd->customer_id,
                                $fxd->branch_id,
                                $schedules->interest,
                                'credit',
                                'core',
                                '0',
                                null,
                                null,
                                null,
                                null,
                                $tref,
                                'fixed deposit investment interest - ' . $fdcd,
                                'approved',
                                '8',
                                'trnsfer',
                                'system'
                            );

                            if (!is_null($customer->exchangerate_id)) {
                                $this->checkforeigncurrncy($customer->exchangerate_id, $schedules->interest, $tref, 'credit');
                                $this->foreigncurrncyinterestExpense($customer->exchangerate_id, $schedules->interest, $tref, $fxd->fixed_deposit_code);
                            } else {
                                //deposit into saving acct and current acct Gl
                                if ($glsavingdacct->status == '1') { //saving acct GL

                                    $this->gltransaction('withdrawal', $glsavingdacct, $schedules->interest, null);
                                    $this->create_saving_transaction_gl(null, $glsavingdacct->id, $fxd->branch_id, $schedules->interest, 'credit', 'core', $tref, $this->generatetrnxref('crgl'), 'customer credited', 'approved', 'system');
                                }
                                // }elseif($customer->account_type == '2'){//current acct GL

                                // $this->gltransaction('withdrawal',$glcurrentacct,$schedules->interest,null);
                                // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$fxd->branch_id, $schedules->interest,'credit','core',$tref,$this->generatetrnxref('crgl'),'customer credited','approved','system');

                                // }
                                //debit interest expenses(add)
                                if ($glinterestexpacct->status == '1') {

                                    $this->gltransaction('withdrawal', $glinterestexpacct, $schedules->interest, null);
                                    $this->create_saving_transaction_gl(null, $glinterestexpacct->id, $fxd->branch_id, $schedules->interest, 'debit', 'core', $tref, $this->generatetrnxref('intrexp'), 'fixed deposit investment interest - ' . $fxd->fixed_deposit_code, 'approved', 'system');
                                }
                            }


                            $schedules->payment_date = Carbon::now();
                            $schedules->payment_method = "auto";
                            $schedules->posted_by = "system";
                            $schedules->closed = '1';
                            $schedules->save();

                            InvestmetRepayment::create([
                                'fixed_deposit_id' => $fxd->id,
                                'accountofficer_id' => $fxd->accountofficer_id,
                                'customer_id' => $fxd->customer_id,
                                'branch_id' => $fxd->branch_id,
                                'amount' => $schedules->interest,
                                'collection_date' => Carbon::now(),
                                'notes' => 'interest paid - ' . $fxd->fixed_deposit_code,
                                'payment_method' => 'flat',
                                'due_date' => Carbon::now()
                            ]);

                            $smsmsg = "Credit Amt: N" . number_format($schedules->interest, 2) . "\n Desc: fixed deposit investment interest \n Avail Bal: " . number_format($customeracct->account_balance, 2) . "\n Date: " . date("Y-m-d") . "\n Ref: " . $tref;

                            if ($customer->enable_sms_alert) {
                                $this->sendSms($customer->phone, $smsmsg, $getsetvalue->getsettingskey('active_sms')); //send sms
                            }

                            if ($customer->enable_email_alert) {
                                $msg =  "Credit Amt: N" . number_format($schedules->interest, 2) . "<br> Desc: fixed deposit investment interest<br>Avail Bal: N" . number_format($customeracct->account_balance, 2) . "<br> Date: " . date("Y-m-d") . "<br>Ref: " . $tref;
                                Email::create([
                                    'user_id' => $customer->id,
                                    'subject' => ucwords($getsetvalue->getsettingskey('company_name')) . ' Credit Alert',
                                    'message' => $msg,
                                    'recipient' => $customer->email,
                                ]);

                                Mail::send(['html' => 'mails.sendmail'], [
                                    'msg' => $msg,
                                    'type' => 'Credit Transaction'
                                ], function ($mail) use ($getsetvalue, $customer) {
                                    $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                                    $mail->to($customer->email);
                                    $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')) . ' Credit Alert');
                                });
                            }


                            if ($fxd->enable_withholding_tax == '1') {

                                $wtref = $this->generatetrnxref('whtx');

                                $withhdtax = $fxd->withholding_tax / 100 * $schedules->interest;

                                $witax = $customeracct->account_balance - $withhdtax;
                                $customeracct->account_balance = $witax;
                                $customeracct->save();

                                $this->create_saving_transaction(
                                    null,
                                    $customer->id,
                                    $fxd->branch_id,
                                    $withhdtax,
                                    'debit',
                                    'core',
                                    '0',
                                    null,
                                    null,
                                    null,
                                    null,
                                    $wtref,
                                    'withholding tax-' . $fdcd,
                                    'approved',
                                    '11',
                                    'trnsfer',
                                    'system'
                                );

                                if (!is_null($customer->exchangerate_id)) {
                                    $this->checkforeigncurrncy($customer->exchangerate_id, $withhdtax, $wtref, 'debit');
                                    $this->foreigncurrncywtholdingTax($customer->exchangerate_id, $withhdtax, $wtref);
                                } else {
                                    //deposit into saving acct and current acct Gl
                                    if ($glsavingdacct->status == '1') { //saving acct GL

                                        $this->gltransaction('deposit', $glsavingdacct, $withhdtax, null);
                                        $this->create_saving_transaction_gl(null, $glsavingdacct->id, $fxd->branch_id, $withhdtax, 'debit', 'core', $wtref, $this->generatetrnxref('drgl'), 'customer debited', 'approved', 'system');
                                    }
                                    // }elseif($customer->account_type == '2'){//current acct GL

                                    //     $this->gltransaction('deposit',$glcurrentacct,$withhdtax,null);
                                    // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$fxd->branch_id,$withhdtax,'debit','core',$wtref,$this->generatetrnxref('drgl'),'customer debited','approved','system');

                                    // }

                                    //add withholding tax
                                    if ($glwithhdtaxacct->status == '1') {

                                        $this->gltransaction('withdrawal', $glwithhdtaxacct, $withhdtax, null);
                                        $this->create_saving_transaction_gl(null, $glwithhdtaxacct->id, $fxd->branch_id, $withhdtax, 'credit', 'core', $wtref, $this->generatetrnxref('withtx'), 'withholding tax', 'approved', 'system');
                                    }
                                }


                                $smsmsg = "Debit Amt: N" . number_format($withhdtax, 2) . "\n Desc: Fixed deposit withholding tax \n Avail Bal: " . number_format($customeracct->account_balance, 2) . "\n Date: " . date("Y-m-d") . "\n Ref: " . $tref;

                                if ($customer->enable_sms_alert) {
                                    $this->sendSms($customer->phone, $smsmsg, $getsetvalue->getsettingskey('active_sms')); //send sms
                                }

                                if ($customer->enable_email_alert) {
                                    $msg =  "Debit Amt: N" . number_format($withhdtax, 2) . "<br> Desc: Fixed deposit withholding tax <br>Avail Bal: N" . number_format($customeracct->account_balance, 2) . "<br> Date: " . date("Y-m-d") . "<br>Ref: " . $tref;
                                    Email::create([
                                        'user_id' => $customer->id,
                                        'subject' => ucwords($getsetvalue->getsettingskey('company_name')) . ' Debit Alert',
                                        'message' => $msg,
                                        'recipient' => $customer->email,
                                    ]);

                                    Mail::send(['html' => 'mails.sendmail'], [
                                        'msg' => $msg,
                                        'type' => 'Debit Transaction'
                                    ], function ($mail) use ($getsetvalue, $customer) {
                                        $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                                        $mail->to($customer->email);
                                        $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')) . ' Debit Alert');
                                    });
                                }
                            }
                        }
                    }
                }

                if ($fxd->interest_method == "rollover") {

                    if ($fxd->maturity_date == date("Y-m-d")) {

                        $pr = $fxd->principal + $rolloverinterst;

                        $ropr = $customeracct->account_balance + $pr;
                        $customeracct->account_balance = $ropr;
                        $customeracct->save();



                        $this->create_saving_transaction(
                            null,
                            $fxd->customer_id,
                            $fxd->branch_id,
                            $pr,
                            'credit',
                            'core',
                            '0',
                            null,
                            null,
                            null,
                            null,
                            $tref,
                            'fixed deposit investment liquidation-' . $fdcd,
                            'approved',
                            '12',
                            'trnsfer',
                            'system'
                        );

                        if (!is_null($customer->exchangerate_id)) {
                            $this->checkforeigncurrncy($customer->exchangerate_id, $pr, $tref, 'credit');
                            $this->foreigncurrncyinvestment($customer->exchangerate_id, $fxd->principal, $tref, 'debit', 'system');
                            $this->foreigncurrncyinterestExpense($customer->exchangerate_id, $rolloverinterst, $tref, $fxd->fixed_deposit_code);
                        } else {
                            //deposit into saving acct and current acct Gl
                            if ($glsavingdacct->status == '1') { //saving acct GL

                                $this->gltransaction('withdrawal', $glsavingdacct, $pr, null);
                                $this->create_saving_transaction_gl(null, $glsavingdacct->id, $fxd->branch_id, $pr, 'credit', 'core', $tref, $this->generatetrnxref('svgl'), 'customer credited', 'approved', 'system');
                            }
                            // }elseif($customer->account_type == '2'){//current acct GL

                            // $this->gltransaction('withdrawal',$glcurrentacct,$pr,null);
                            // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$fxd->branch_id, $pr,'credit','core',$tref,$this->generatetrnxref('crgl'),'customer credited','approved','system');

                            // }
                            //debit fd investment gl
                            if ($glfixeddacct->status == '1') {

                                $this->gltransaction('deposit', $glfixeddacct, $fxd->principal, null);
                                $this->create_saving_transaction_gl(null, $glfixeddacct->id, $fxd->branch_id, $fxd->principal, 'debit', 'core', $tref, $this->generatetrnxref('inv'), 'debit investment', 'approved', 'system');
                            }

                            //debit interest expenses(add)
                            if ($glinterestexpacct->status == '1') {

                                $this->gltransaction('withdrawal', $glinterestexpacct, $rolloverinterst, null);
                                $this->create_saving_transaction_gl(null, $glinterestexpacct->id, $fxd->branch_id, $rolloverinterst, 'debit', 'core', $tref, $this->generatetrnxref('intrexp'), 'investment interest - ' . $fxd->fixed_deposit_code, 'approved', 'system');
                            }
                        }

                        InvestmetRepayment::create([
                            'fixed_deposit_id' => $fxd->id,
                            'accountofficer_id' => $fxd->accountofficer_id,
                            'customer_id' => $fxd->customer_id,
                            'branch_id' => $fxd->branch_id,
                            'amount' => $pr,
                            'collection_date' => Carbon::now(),
                            'notes' => 'principal paid - ' . $fxd->fixed_deposit_code,
                            'payment_method' => 'flat',
                            'due_date' => Carbon::now()
                        ]);

                        $smsmsg = "Credit Amt: N" . number_format($pr, 2) . "\n Desc: fixed deposit investment \n Avail Bal: " . number_format($customeracct->account_balance, 2) . "\n Date: " . date("Y-m-d") . "\n Ref: " . $tref;

                        if ($customer->enable_sms_alert) {
                            $this->sendSms($customer->phone, $smsmsg, $getsetvalue->getsettingskey('active_sms')); //send sms
                        }

                        if ($customer->enable_email_alert) {

                            $msg =  "Credit Amt: N" . number_format($pr, 2) . "<br> Desc: fixed deposit investment <br>Avail Bal: N" . number_format($customeracct->account_balance, 2) . "<br> Date: " . date("Y-m-d") . "<br>Ref: " . $tref;
                            Email::create([
                                'user_id' => $customer->id,
                                'subject' => ucwords($getsetvalue->getsettingskey('company_name')) . ' Credit Alert',
                                'message' => $msg,
                                'recipient' => $customer->email,
                            ]);

                            Mail::send(['html' => 'mails.sendmail'], [
                                'msg' => $msg,
                                'type' => 'Credit Transaction'
                            ], function ($mail) use ($getsetvalue, $customer) {
                                $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                                $mail->to($customer->email);
                                $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')) . ' Credit Alert');
                            });
                        }

                        $withhdtax = 0;
                        if ($fxd->enable_withholding_tax == '1') {

                            $wtref = $this->generatetrnxref('whtx');

                            $withhdtax = $fxd->withholding_tax / 100 * $rolloverinterst;

                            $wdtax = $customeracct->account_balance - $withhdtax;
                            $customeracct->account_balance = $wdtax;
                            $customeracct->save();

                            $this->create_saving_transaction(
                                null,
                                $customer->id,
                                $fxd->branch_id,
                                $withhdtax,
                                'debit',
                                'core',
                                '0',
                                null,
                                null,
                                null,
                                null,
                                $wtref,
                                'withholding tax-' . $fdcd,
                                'approved',
                                '11',
                                'trnsfer',
                                'system'
                            );

                            if (!is_null($customer->exchangerate_id)) {
                                $this->checkforeigncurrncy($customer->exchangerate_id, $withhdtax, $wtref, 'debit');
                                $this->foreigncurrncywtholdingTax($customer->exchangerate_id, $withhdtax, $wtref);
                            } else {
                                //deposit into saving acct and current acct Gl
                                if ($glsavingdacct->status == '1') { //saving acct GL

                                    $this->gltransaction('deposit', $glsavingdacct, $withhdtax, null);
                                    $this->create_saving_transaction_gl(null, $glsavingdacct->id, $fxd->branch_id, $withhdtax, 'debit', 'core', $wtref, $this->generatetrnxref('svgl'), 'customer debited', 'approved', 'system');
                                }
                                // }elseif($customer->account_type == '2'){//current acct GL

                                //     $this->gltransaction('deposit',$glcurrentacct,$withhdtax,null);
                                // $this->create_saving_transaction_gl(null,$glcurrentacct->id,$fxd->branch_id,$withhdtax,'debit','core',$wtref,$this->generatetrnxref('crgl'),'customer debited','approved','system');

                                // }
                                //add withholding tax
                                if ($glwithhdtaxacct->status == '1') {

                                    $this->gltransaction('withdrawal', $glwithhdtaxacct, $withhdtax, null);
                                    $this->create_saving_transaction_gl(null, $glwithhdtaxacct->id, $fxd->branch_id, $withhdtax, 'credit', 'core', $wtref, $this->generatetrnxref('withtx'), 'withholding tax', 'approved', 'system');
                                }
                            }

                            $smsmsg = "Debit Amt: N" . number_format($withhdtax, 2) . "\n Desc: Fixed deposit withholding tax \n Avail Bal: " . number_format($customeracct->account_balance, 2) . "\n Date: " . date("Y-m-d") . "\n Ref: " . $wtref;

                            if ($customer->enable_sms_alert) {
                                $this->sendSms($customer->phone, $smsmsg, $getsetvalue->getsettingskey('active_sms')); //send sms
                            }

                            if ($customer->enable_email_alert) {
                                $msg =  "Debit Amt: N" . number_format($withhdtax, 2) . "<br> Desc: Fixed deposit withholding tax <br>Avail Bal: N" . number_format($customeracct->account_balance, 2) . "<br> Date: " . date("Y-m-d") . "<br>Ref: " . $wtref;
                                Email::create([
                                    'user_id' => $customer->id,
                                    'subject' => ucwords($getsetvalue->getsettingskey('company_name')) . ' Debit Alert',
                                    'message' => $msg,
                                    'recipient' => $customer->email,
                                ]);

                                Mail::send(['html' => 'mails.sendmail'], [
                                    'msg' => $msg,
                                    'type' => 'Debit Transaction'
                                ], function ($mail) use ($getsetvalue, $customer) {
                                    $mail->from($getsetvalue->getsettingskey('company_email'), ucwords($getsetvalue->getsettingskey('company_name')));
                                    $mail->to($customer->email);
                                    $mail->subject(ucwords($getsetvalue->getsettingskey('company_name')) . ' Debit Alert');
                                });
                            }
                        }

                        //close schedules
                        foreach ($sches as $itemclose) {
                            $sched = InvestmentSchedule::where('id', $itemclose->id)->first();
                            $sched->payment_date = Carbon::now();
                            $sched->payment_method = "auto";
                            $sched->posted_by = "system";
                            $sched->closed = '1';
                            $sched->save();
                        }

                        $fxds = FixedDeposit::where('id', $fxd->id)->first();
                        $fxds->closed_notes  = 'fixed deposit liquidated';
                        $fxds->closed_date = Carbon::now();
                        $fxds->fd_status = 'fully_paid';
                        $fxds->status = 'closed';
                        $fxds->save();

                        $bkfdbal = $pr - $withhdtax;

                        if ($fxd->auto_book_investment == '1') {
                            $autoinvest = new InvestmentController();
                            $autoinvest->autoBookFixed_deposit($fxd->id, $bkfdbal);
                        }
                    } //end method

                }

                //}
            }

            DB::commit();
        } catch (Exception $e) {

            DB::rollBack();

            $this->loginfo("Error processing investment cron", $e->getMessage() . "--" . $e->getLine());
        }
    }

    public function checkPendingTransaction_cron()
    {

        $this->logInfo("checking pending approve after EOD", "");

        $trnx = SavingsTransaction::where(['status' => 'pending', 'device' => 'core'])->whereDate('created_at', '<', date('Y-m-d'))->get();
        if (count($trnx) > 0) {
            foreach ($trnx as $item) {
                $trnxxx = SavingsTransaction::where('id', $item->id)->first();
                //for saving transaction
                $trnxxx->status = "declined";
                $trnxxx->is_approve = "1";
                $trnxxx->approve_by = "system";
                $trnxxx->approve_date = Carbon::now();
                $trnxxx->save();
            }
        }

        $trnxGL = SavingsTransactionGL::where(['status' => 'pending', 'device' => 'core'])->whereDate('created_at', '<', date('Y-m-d'))->get();
        if (count($trnxGL) > 0) {
            foreach ($trnxGL as $item) {
                $trnxGLgl = SavingsTransactionGL::where('id', $item->id)->first();
                //for saving transactionGL
                $trnxGLgl->status = "declined";
                $trnxGLgl->approved_by = "system";
                $trnxGLgl->approve_date = Carbon::now();
                $trnxGLgl->save();
            }
        }
    }
}//endclass

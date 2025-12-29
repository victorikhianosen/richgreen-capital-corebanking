<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([
            [
                'setting_key' => 'company_name',
                'setting_value' => 'GGTconnect',
            ],
            [
                'setting_key' => 'company_code',
                'setting_value' => '34524',
            ],
            [
                'setting_key' => 'company_share',
                'setting_value' => '1200000',
            ],
            [
                'setting_key' => 'company_capital',
                'setting_value' => '2100000',
            ],
            [
                'setting_key' => 'bank_fund',
                'setting_value' => '1700000',
            ],
            [
                'setting_key' => 'till_account',
                'setting_value' => '10923392',
            ],
            [
                'setting_key' => 'fdliquid_interest',
                'setting_value' => '10926738',
            ],
            [
                'setting_key' => 'enable_virtual_ac',
                'setting_value' => '0',
            ],
             [
                'setting_key' => 'assetmtx',
                'setting_value' => '10839',
            ],
            [
                'setting_key' => 'vault_account',
                'setting_value' => '10373391',
            ],
            [
                'setting_key' => 'giftbill_account',
                'setting_value' => '10839235',
            ],
            [
                'setting_key' => 'vtpass_account',
                'setting_value' => '10221490',
            ],
            [
                'setting_key' => 'vtpass_income',
                'setting_value' => '40469341',
            ],
            [
                'setting_key' => 'giftbill_income',
                'setting_value' => '40920905',
            ],
            [
                'setting_key' => 'pos_charges',
                'setting_value' => '409204785',
            ],
             [
                'setting_key' => 'birthday_msg',
                'setting_value' => 'usdifhsf',
            ],
            [
                'setting_key' => 'glcharges',
                'setting_value' => '40920978',
            ],
            [
                'setting_key' => 'othercharges',
                'setting_value' => '409209534',
            ],
            [
                'setting_key' => 'payoption',
                'setting_value' => '4',
            ],
            [
                'setting_key' => 'othrchargesgl',
                'setting_value' => '44',
            ],
            [
                'setting_key' => 'transfer_charge',
                'setting_value' => '4',
            ],
            [
                'setting_key' => 'withdrawal_limit',
                'setting_value' => '0',
            ],
            [
                'setting_key' => 'deposit_limit',
                'setting_value' => '0',
            ],
            

            [
                'setting_key' => 'esusucharges',
                'setting_value' => '43',
            ],

            [
                'setting_key' => 'frmfeecharges',
                'setting_value' => '8',
            ],
            [
                'setting_key' => 'processcharges',
                'setting_value' => '9',
            ],
            [
                'setting_key' => 'enable_2FA',
                'setting_value' => '1',
            ],
             [
                'setting_key' => 'moniepointgl',
                'setting_value' => '28494393',
            ],
             [
                'setting_key' => 'monnifycharge',
                'setting_value' => '2',
            ],
             [
                'setting_key' => 'bankcharge',
                'setting_value' => '24',
            ],
             [
                'setting_key' => 'withholdingtax',
                'setting_value' => '24',
            ],
             [
                'setting_key' => 'fdcharge',
                'setting_value' => '24',
            ],
            [
                'setting_key' => 'monthlycharges',
                'setting_value' => '2',
            ],
            [
                'setting_key' => 'income_suspense',
                'setting_value' => '1099677918',
            ],
            [
                'setting_key' => 'asset_suspense',
                'setting_value' => '1099677918',
            ],
            [
                'setting_key' => 'capital_suspense',
                'setting_value' => '1099677918',
            ],
            [
                'setting_key' => 'liability_suspense',
                'setting_value' => '1099677918',
            ],
            [
                'setting_key' => 'exps_suspense',
                'setting_value' => '1099677918',
            ],

            [
                'setting_key' => 'company_account',
                'setting_value' => '10997918',
            ],
            [
                'setting_key' => 'online_transfer',
                'setting_value' => '500000',
            ],
            [
                'setting_key' => 'company_address',
                'setting_value' => '33A1 fani kayode street ikeja GRA',
            ],
            [
                'setting_key' => 'company_currency',
                'setting_value' => 'NGN',
            ],
            [
                'setting_key' => 'company_website',
                'setting_value' => 'https://www.assetmatrixmfb.com',
            ],
            [
                'setting_key' => 'company_country',
                'setting_value' => 'Nigeria',
            ],
            [
                'setting_key' => 'system_version',
                'setting_value' => '1.0',
            ],
            [
                'setting_key' => 'sms_enabled',
                'setting_value' => '1',
            ],
            [
                'setting_key' => 'active_sms',
                'setting_value' => 'clickatell',
            ],
            [
                'setting_key' => 'portal_address',
                'setting_value' => 'http://www.',
            ],
            [
                'setting_key' => 'company_email',
                'setting_value' => 'info@assetmatrixmfb.com',
            ],
            [
                'setting_key' => 'company_phone',
                'setting_value' => '0819076000',
            ],
            [
                'setting_key' => 'currency_symbol',
                'setting_value' => '₦',
            ],
            [
                'setting_key' => 'currency_position',
                'setting_value' => 'left',
            ],
            [
                'setting_key' => 'company_logo',
                'setting_value' => 'uploads/1671474299_asset_matrix_logo.png',
            ],
            [
                'setting_key' => 'login_background',
                'setting_value' => 'uploads/1669749834_Am_Bkgrd3.jpg',
            ],
            [
                'setting_key' => 'twilio_sid',
                'setting_value' => Null,
            ],
            [
                'setting_key' => 'twilio_token',
                'setting_value' => Null,
            ],
            [
                'setting_key' => 'twilio_phone_number',
                'setting_value' => Null,
            ],
            [
                'setting_key' => 'twilio_baseurl',
                'setting_value' => Null,
            ],
            [
                'setting_key' => 'routesms_host',
                'setting_value' => Null,
            ],
            [
                'setting_key' => 'routesms_username',
                'setting_value' => Null,
            ],
            [
                'setting_key' => 'routesms_password',
                'setting_value' => Null,
            ],
            [
                'setting_key' => 'routesms_port',
                'setting_value' => Null,
            ],
            [
                'setting_key' => 'sms_sender',
                'setting_value' => 'AssetMatrix',
            ],
            [
                'setting_key' => 'clickatell_username',
                'setting_value' => '684unfuh6837d8o93bn9d3893',
            ],
            [
                'setting_key' => 'clickatell_password',
                'setting_value' => 'clickatell',
            ],
            [
                'setting_key' => 'clickatell_api_id',
                'setting_value' => '684unfuh6837d8o93bn9d3893',
            ],
            [
                'setting_key' => 'clickatell_baseurl',
                'setting_value' => 'https://api.clickatell.com',
            ],
            [
                'setting_key' => 'paypal_email',
                'setting_value' => Null,
            ],
            [
                'setting_key' => 'currency',
                'setting_value' => 'USD',
            ],
            [
                'setting_key' => 'password_reset_subject',
                'setting_value' => 'Password reset instructions',
            ],
            [
                'setting_key' => 'password_reset_template',
                'setting_value' => 'Password reset instructions',
            ],
            [
                'setting_key' => 'payment_received_sms_template',
                'setting_value' => 'Dear {borrowerFirstName}, we have received your payment of ${paymentAmount} for loan {loanNumber}. New loan balance:${loanBalance}. Thank you',
            ],
            [
                'setting_key' => 'payment_received_email_template',
                'setting_value' => 'Dear {borrowerFirstName}, we have received your payment of ${paymentAmount} for loan {loanNumber}. New loan balance:${loanBalance}. Thank you',
            ],
            [
                'setting_key' => 'payment_received_email_subject',
                'setting_value' => 'Payment Received',
            ],
            [
                'setting_key' => 'payment_email_subject',
                'setting_value' => 'Payment Receipt',
            ],
            [
                'setting_key' => 'payment_email_template',
                'setting_value' => 'Dear {borrowerFirstName}, find attached receipt of your payment of ${paymentAmount} for loan {loanNumber} on {paymentDate}. New loan balance:${loanBalance}. Thank you',
            ],
            [
                'setting_key' => 'borrower_statement_email_subject',
                'setting_value' => 'Client Statement',
            ],
            [
                'setting_key' => 'borrower_statement_email_template',
                'setting_value' => 'Dear {borrowerFirstName}, find attached statement of your loans with us. Thank you',
            ],
            [
                'setting_key' => 'loan_statement_email_subject',
                'setting_value' => 'Loan Statement',
            ],
            [
                'setting_key' => 'loan_statement_email_template',
                'setting_value' => 'Dear {borrowerFirstName}, find attached loan statement for loan {loanNumber}. Thank you',
            ],
            [
                'setting_key' => 'loan_schedule_email_subject',
                'setting_value' => 'Loan Schedule',
            ],
            [
                'setting_key' => 'loan_schedule_email_template',
                'setting_value' => 'Dear {borrowerFirstName}, find attached loan schedule for loan {loanNumber}. Thank you',
            ],
            [
                'setting_key' => 'cron_last_run',
                'setting_value' => Null,
            ],
            [
                'setting_key' => 'auto_apply_penalty',
                'setting_value' => '1',
            ],
            [
                'setting_key' => 'auto_payment_receipt_sms',
                'setting_value' => '1',
            ],
            [
                'setting_key' => 'auto_payment_receipt_email',
                'setting_value' => '1',
            ],
            [
                'setting_key' => 'auto_repayment_sms_reminder',
                'setting_value' => '1',
            ],
            [
                'setting_key' => 'auto_repayment_email_reminder',
                'setting_value' => '1',
            ],
            [
                'setting_key' => 'auto_repayment_days',
                'setting_value' => '4',
            ],
            [
                'setting_key' => 'auto_overdue_repayment_sms_reminder',
                'setting_value' => '0',
            ],
            [
                'setting_key' => 'auto_overdue_repayment_email_reminder',
                'setting_value' => '1',
            ],
            [
                'setting_key' => 'auto_overdue_repayment_days',
                'setting_value' => '2',
            ],
            [
                'setting_key' => 'auto_overdue_loan_sms_reminder',
                'setting_value' => '0',
            ],
            [
                'setting_key' => 'auto_overdue_loan_email_reminder',
                'setting_value' => '1',
            ],
            [
                'setting_key' => 'auto_overdue_loan_days',
                'setting_value' => '2',
            ],
            [
                'setting_key' => 'loan_overdue_email_subject',
                'setting_value' => 'Loan Overdue',
            ],
            [
                'setting_key' => 'loan_overdue_email_template',
                'setting_value' => 'Dear {borrowerFirstName}, Your loan {loanNumber} is overdue. Please make your payment. Thank you',
            ],
            [
                'setting_key' => 'loan_overdue_sms_template',
                'setting_value' => 'Dear {borrowerFirstName}, Your loan {loanNumber} is overdue. Please make your payment. Thank you',
            ],
            [
                'setting_key' => 'loan_payment_reminder_subject',
                'setting_value' => 'Upcoming Payment Reminder',
            ],
            [
                'setting_key' => 'loan_payment_reminder_email_template',
                'setting_value' => 'Dear {borrowerFirstName},You have an upcoming payment of {paymentAmount} due on {paymentDate} for loan {loanNumber}. Please make your payment. Thank you',
            ],
            [
                'setting_key' => 'loan_payment_reminder_sms_template',
                'setting_value' => 'Dear {borrowerFirstName},You have an upcoming payment of {paymentAmount} due on {paymentDate} for loan {loanNumber}. Please make your payment. Thank you',
            ],
            [
                'setting_key' => 'missed_payment_email_subject',
                'setting_value' => 'Missed Payment',
            ],
            [
                'setting_key' => 'missed_payment_email_template',
                'setting_value' => 'Dear {borrowerFirstName},You missed  payment of {paymentAmount} which was due on {paymentDate} for loan {loanNumber}. Please make your payment. Thank you',
            ],
            [
                'setting_key' => 'missed_payment_sms_template',
                'setting_value' => 'Dear {borrowerFirstName},You missed  payment of {paymentAmount} which was due on {paymentDate} for loan {loanNumber}. Please make your payment. Thank you',
            ],
            [
                'setting_key' => 'enable_cron',
                'setting_value' => '1',
            ],
            [
                'setting_key' => 'infobip_username',
                'setting_value' => Null,
            ],
            [
                'setting_key' => 'infobip_password',
                'setting_value' => Null,
            ],
            [
                'setting_key' => 'infobip_api_key',
                'setting_value' => Null,
            ],
            [
                'setting_key' => 'infobip_baseurl',
                'setting_value' => Null,
            ],
            [
                'setting_key' => 'welcome_note',
                'setting_value' => 'Welcome to AssetMatrix MFB Ltd',
            ],
            [
                'setting_key' => 'allow_self_registration',
                'setting_value' => '1',
            ],
            [
                'setting_key' => 'client_auto_activate_account',
                'setting_value' => '1',
            ],
            [
                'setting_key' => 'allow_client_login',
                'setting_value' => '1',
            ],
            [
                'setting_key' => 'client_request_guarantor',
                'setting_value' => '1',
            ],
            [
                'setting_key' => 'allow_client_apply',
                'setting_value' => '1',
            ],
            [
                'setting_key' => 'auto_post_savings_interest',
                'setting_value' => '1',
            ],
            [
                'setting_key' => 'enable_online_payment',
                'setting_value' => '1',
            ],
            [
                'setting_key' => 'payment_gateway',
                'setting_value' => 'flutterwave',
            ],
            [
                'setting_key' => 'gateway_pub_key',
                'setting_value' => 'j9866ghv8uio',
            ],
            [
                'setting_key' => 'gateway_secret_key',
                'setting_value' => '4fcjvjhdfsuiklkpl',
            ],

        ]);
    }
}

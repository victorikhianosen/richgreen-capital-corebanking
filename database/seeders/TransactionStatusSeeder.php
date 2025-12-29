<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('transaction_status')->insert([
            ['status_type' => 'deposit'],
            ['status_type' => 'withdrawal'],
            ['status_type' => 'reverse deposit'],
            ['status_type' => 'reverse withdrawal'],
            ['status_type' => 'fixed deposit charge'],
            ['status_type' => 'monthly charge'],
            ['status_type' => 'loan fees'],
            ['status_type' => 'fixed deposit interest'],
            ['status_type' => 'sms'],
            ['status_type' => 'transfer charge'],
            ['status_type' => 'withholding tax'],
            ['status_type' => 'fixed deposit'],
            ['status_type' => 'betting'],
            ['status_type' => 'airtime topup'],
            ['status_type' => 'data subscription'],
            ['status_type' => 'cable subcription'],
            ['status_type' => 'electricity payment'],
            ['status_type' => 'loan interest'],
            ['status_type' => 'loan'],
            ['status_type' => 'POS Cashout'],
            ['status_type' => 'POS Bills Payment'],
            ['status_type' => 'POS Funds Transfer'],
            ['status_type' => 'POS Airtime Purchase'],
            ['status_type' => 'POS Data Purchase'],
            ['status_type' => 'POS Cable Subscription'],
            ['status_type' => 'POS Electricity Purchase'],
            ['status_type' => 'USSD Session Charge'],
            ['status_type' => 'Whatsapp Session Charge'],
        ]);
       
    }
}

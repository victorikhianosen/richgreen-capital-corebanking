<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('savings_transaction_g_l_s', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('branch_id')->nullable();
            $table->foreignId('general_ledger_id');
            $table->string('gl_code');
            $table->string('amount');
            $table->enum('type',['deposit','rev_deposit','rev_withdrawal','withdrawal','esusu','monthly_charge','form_fees',
            'bank_fees','interest','sms','dividend','guarantee','guarantee_restored','repayment','fd_interest','investment','process_fees',
            'fixed_deposit','rev_fixed_deposit','loan','wht','rev_interest','transfer_charge','debit','credit','part_liquidate'])->nullable();
            $table->enum('device',['web','mobile'])->nullable();
            $table->string('slip')->nullable();
            $table->string('reference_no');
            $table->text('notes')->nullable();
            $table->text('status')->nullable();
             $table->string('initiated_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->dateTime('approve_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('savings_transaction_g_l_s');
    }
};

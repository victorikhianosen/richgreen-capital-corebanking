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
        Schema::create('fxmgmts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('accountofficer_id')->nullable();
            $table->foreignId('exchangerate_id')->nullable();
            $table->string('customer')->nullable();
            $table->string('purchase_exchange_rate')->nullable();
            $table->string('sales_exchange_rate')->nullable();
            $table->string('naria_amount');
            $table->string('foreign_amount');
            $table->string('fee_amount')->default('0');
            $table->string('fx_reference')->nullable();
            $table->string('purchase_naria_from')->nullable();
            $table->string('purchase_recieve_currency')->nullable();
            $table->string('payment_mode')->nullable();
            $table->string('sales_from')->nullable();
            $table->string('sales_paid_to')->nullable();
            $table->string('sales_margin')->nullable();
            $table->text('beneficiary')->nullable();
            $table->string('beneficiary_bank')->nullable();
            $table->string('depositor')->nullable();
            $table->string('actual_payment')->nullable();
            $table->string('swift_bank_charges')->nullable();
            $table->text('description')->nullable();
            $table->string('fxtype')->nullable();
            $table->string('initiated_by')->nullable();
            $table->string('branch_id')->nullable();
            $table->date('tranx_date');
            $table->boolean('rev_status')->default(0);
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
        Schema::dropIfExists('fxmgmts');
    }
};

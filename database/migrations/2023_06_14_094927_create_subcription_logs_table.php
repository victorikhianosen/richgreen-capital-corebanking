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
        Schema::create('subcription_logs', function (Blueprint $table) {
            $table->id();
            $table->string('subcription');
            $table->string('amount_paid');
            $table->string('paymentref');
            $table->string('vat')->nullable();
            $table->string('total_paid');
            $table->string('expense_account');
            $table->string('credit_account');
            $table->date('warning_date');
            $table->date('expiration_date');
            $table->dateTime('payment_date');
            $table->string('note');
            $table->boolean('is_active')->default(0);
            $table->string('branch_id')->nullable();
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
        Schema::dropIfExists('subcription_logs');
    }
};

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
        Schema::create('savings_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('customer_id')->nullable();
            $table->foreignId('branch_id')->nullable();
            $table->string('amount');
            $table->string('type')->nullable();
            $table->string('device')->nullable();
            $table->tinyInteger('system_interest')->default(0);
            $table->string('slip')->nullable();
            $table->string('is_approve')->nullable();
            $table->string('cust_int')->nullable();
            $table->string('transfer_type')->nullable();
            $table->string('reference_no');
            $table->text('notes')->nullable();
            $table->string('status')->nullable();
            $table->string('trnx_type')->nullable();
            $table->string('initiated_by')->nullable();
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
        Schema::dropIfExists('savings_transactions');
    }
};

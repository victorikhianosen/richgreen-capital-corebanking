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
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id');
            $table->foreignId('accountofficer_id')->nullable();
            $table->foreignId('customer_id');
            $table->foreignId('branch_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->string('amount');
            $table->string('repayment_method');
            $table->string('type')->nullable();
            $table->string('reference')->nullable();
            $table->date('collection_date');
            $table->text('notes')->nullable();
            $table->date('due_date');
            $table->boolean('status')->default('0');
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
        Schema::dropIfExists('loan_repayments');
    }
};

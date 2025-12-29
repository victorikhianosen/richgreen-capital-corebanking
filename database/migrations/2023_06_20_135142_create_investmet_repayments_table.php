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
        Schema::create('investmet_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_deposit_id');
            $table->foreignId('accountofficer_id')->nullable();
            $table->foreignId('customer_id');
            $table->foreignId('branch_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->string('amount');
            $table->string('payment_method');
            $table->date('collection_date');
            $table->text('notes')->nullable();
            $table->date('due_date');
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
        Schema::dropIfExists('investmet_repayments');
    }
};

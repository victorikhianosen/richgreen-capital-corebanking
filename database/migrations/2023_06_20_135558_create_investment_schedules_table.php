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
        Schema::create('investment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_deposit_id');
            $table->foreignId('customer_id');
            $table->foreignId('branch_id');
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->string('principal')->nullable();
            $table->string('interest')->nullable();
            $table->string('rollover')->nullable();
            $table->string('total_interest')->nullable();
            $table->string('total_due')->nullable();
            $table->tinyInteger('closed')->default('0');
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('posted_by')->nullable();
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
        Schema::dropIfExists('investment_schedules');
    }
};

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
        Schema::create('payment_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id');
            $table->foreignId('branch_id');
            $table->string('basic');
            $table->string('other_allowance');
            $table->string('gross_pay');
            $table->string('paye_percent');
            $table->string('paye');
            $table->string('other_deduction');
            $table->string('deduction');
            $table->string('net_pay');
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
        Schema::dropIfExists('payment_structures');
    }
};

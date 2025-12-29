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
        Schema::create('fixed_deposit_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('name')->nullable();
            $table->string('minimum_principal')->nullable();
            $table->string('default_principal')->nullable();
            $table->string('maximum_principal')->nullable();
            $table->string('interest_method')->nullable();
            $table->string('interest_rate')->nullable();
            $table->string('interest_period')->default('year');
            $table->string('minimum_interest_rate')->nullable();
            $table->string('default_interest_rate')->nullable();
            $table->string('maximum_interest_rate')->nullable();
            $table->string('interest_payment')->default('monthly');
            $table->string('default_duration')->nullable();
            $table->string('default_loan_duration_type')->default('monthly');
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
        Schema::dropIfExists('fixed_deposit_products');
    }
};

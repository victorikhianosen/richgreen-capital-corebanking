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
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('name')->nullable();
            $table->string('gl_code')->nullable();
            $table->string('interest_gl')->nullable();
            $table->string('incomefee_gl')->nullable();
            $table->string('loan_disbursed_by')->nullable();
            $table->string('minimum_principal')->nullable();
            $table->string('default_principal')->nullable();
            $table->string('maximum_principal')->nullable();
            $table->string('interest_method')->nullable();
            $table->string('interest_rate')->nullable();
            $table->string('interest_period')->default('year');
            $table->string('minimum_interest_rate')->nullable();
            $table->string('default_interest_rate')->nullable();
            $table->string('maximum_interest_rate')->nullable();
            $table->boolean('override_interest')->default(0);
            $table->string('override_interest_amount')->nullable();
            $table->integer('default_loan_duration')->nullable();
            $table->string('default_loan_duration_type')->default('year');
            $table->string('repayment_cycle')->default('monthly');
            $table->string('repayment_order')->nullable();
            $table->string('loan_fees_schedule')->default('distribute_fees_evenly');
            $table->string('branch_access')->nullable();
            $table->string('grace_on_interest_charged')->nullable();
            $table->boolean('advanced_enabled')->default('0');
            $table->boolean('enable_late_repayment_penalty')->default('0');
            $table->boolean('enable_after_maturity_date_penalty')->default('0');
            $table->string('after_maturity_date_penalty_type')->default('percentage');
            $table->string('late_repayment_penalty_type')->default('percentage');
            $table->string('late_repayment_penalty_calculate')->default('overdue_principal');
            $table->string('after_maturity_date_penalty_calculate')->default('overdue_principal');
            $table->string('late_repayment_penalty_amount')->nullable();
            $table->string('after_maturity_date_penalty_amount')->nullable();
            $table->string('late_repayment_penalty_grace_period')->nullable();
            $table->string('after_maturity_date_penalty_grace_period')->nullable();
            $table->string('late_repayment_penalty_recurring')->nullable();
            $table->string('after_maturity_date_penalty_recurring')->nullable();
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
        Schema::dropIfExists('loan_products');
    }
};

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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('customer_id');
            $table->foreignId('loan_product_id');
            $table->foreignId('branch_id');
            $table->foreignId('accountofficer_id');
            $table->foreignId('approved_by_id')->nullable();
            $table->foreignId('disbursed_by_id')->nullable();
            $table->foreignId('withdrawn_by_id')->nullable();
            $table->foreignId('declined_by_id')->nullable();
            $table->foreignId('written_off_by_id')->nullable();
            $table->foreignId('rescheduled_by_id')->nullable();
            $table->foreignId('closed_by_id')->nullable();
            $table->string('loan_code');
            $table->string('reference')->nullable();
            $table->string('equity');
            $table->string('purpose');
            $table->string('old_disbursedate')->nullable();
            $table->string('old_maturedate')->nullable();
            $table->date('release_date')->nullable();
            $table->date('maturity_date')->nullable();
            $table->date('interest_start_date')->nullable();
            $table->date('first_payment_date')->nullable();
            $table->string('principal')->nullable();
            $table->string('interest_method')->nullable();
            $table->string('interest_rate')->nullable();
            $table->string('interest_period')->default('day');
            $table->boolean('override_interest')->default(0);
            $table->string('override_interest_amount')->nullable();
            $table->string('loan_duration')->nullable();
            $table->string('loan_duration_type')->default('year');
            $table->string('repayment_cycle')->default('monthly');
            $table->string('repayment_order')->nullable();
            $table->string('loan_fees_schedule')->default('distribute_fees_evenly');
            $table->string('grace_on_interest_charged')->nullable();
            $table->integer('loan_status_id')->nullable();
            $table->string('files')->nullable();
            $table->text('description')->nullable();
            $table->enum('loan_status',['open','fully_paid','defaulted','restructured','processing'])->default('open');
            $table->string('balance');
            $table->boolean('override')->default(0);
            $table->string('applied_amount')->nullable();
            $table->string('approved_amount')->nullable();
            $table->text('approved_notes')->nullable();
            $table->text('disbursed_notes')->nullable();
            $table->text('withdrawn_notes')->nullable();
            $table->text('closed_notes')->nullable();
            $table->text('rescheduled_notes')->nullable();
            $table->text('declined_notes')->nullable();
            $table->text('written_off_notes')->nullable();
            $table->date('approved_date')->nullable();
            $table->date('disbursed_date')->nullable();
            $table->date('disbursed_by')->nullable();
            $table->date('withdrawn_date')->nullable();
            $table->date('closed_date')->nullable();
            $table->date('rescheduled_date')->nullable();
            $table->date('declined_date')->nullable();
            $table->date('written_off_date')->nullable();
            $table->string('processing_fee')->nullable();
            $table->enum('status',['pending','approved','disbursed','declined','withdrawn','written_off','closed','pending_reschedule','rescheduled','trashed'])->default('pending');
            $table->date('provision_date')->nullable();
            $table->string('provision_amount')->nullable();
            $table->string('provision_type')->nullable();
            $table->foreignId('sector_id')->nullable();
            $table->timestamp('deleted_at')->nullable();
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
        Schema::dropIfExists('loans');
    }
};

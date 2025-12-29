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
        Schema::create('fixed_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('customer_id');
            $table->foreignId('fixed_deposit_product_id');
            $table->foreignId('accountofficer_id');
            $table->foreignId('approved_by_id')->nullable();
            $table->foreignId('closed_by_id')->nullable();
            $table->string('fixed_deposit_code');
            $table->string('reference')->nullable();
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
            $table->string('duration')->nullable();
            $table->string('duration_type')->default('year');
            $table->string('payment_cycle')->default('monthly');
            $table->integer('status_id')->nullable();
            $table->enum('fd_status',['open','fully_paid','defaulted','restructured','processing'])->default('open');
            $table->string('balance');
            $table->string('applied_amount')->nullable();
            $table->string('approved_amount')->nullable();
            $table->text('approved_notes')->nullable();
            $table->text('closed_notes')->nullable();
            $table->boolean('enable_withholding_tax')->default(0);
            $table->string('withholding_tax')->nullable();
            $table->boolean('auto_book_investment')->default(0);
            $table->date('approved_date')->nullable();
            $table->date('closed_date')->nullable();
            $table->date('declined_date')->nullable();
            $table->enum('status',['pending','approved','disbursed','declined','withdrawn','written_off','closed','pending_reschedule','rescheduled','trashed'])->default('pending');
           $table->boolean('system_approve')->default(0);
            $table->timestamp('deleted_at')->nullable();
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
        Schema::dropIfExists('fixed_deposits');
    }
};

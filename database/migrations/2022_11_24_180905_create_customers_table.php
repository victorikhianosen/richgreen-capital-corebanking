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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('branch_id')->nullable();
            $table->foreignId('accountofficer_id')->nullable();
            $table->string('title')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->string('gender');
            $table->string('religion');
            $table->string('gender');
            $table->string('section');
            $table->string('marital_status');
            $table->string('residential_address');
            $table->date('dob');
            $table->string('country')->nullable();
            $table->string('state');
            $table->string('state_lga');
            $table->string('account_type')->nullable();
            $table->foreignId('exchangerate_id')->nullable();
            $table->string('acctno');
            $table->text('refacct')->nullable();
            $table->integer('bvn')->nullable();
            $table->integer('nin')->nullable();
            $table->string('next_kin');
            $table->string('kin_address')->nullable();
            $table->string('kin_phone')->nullable();
            $table->string('kin_relate')->nullable();
            $table->string('maiden');
            $table->string('occupation');
            $table->string('business_name')->nullable();
            $table->string('working_status')->nullable();
            $table->text('question')->nullable();
            $table->text('answer')->nullable();
            $table->string('means_of_id');
            $table->string('upload_id');
            $table->string('photo')->nullable();
            $table->string('signature')->nullable();
            $table->string('username')->nullable();
            $table->string('pin')->nullable();
            $table->string('otp')->nullable();
            $table->dateTime('otp_expiration_date')->nullable();
            $table->boolean('phone_verify')->default('0');
            $table->boolean('whatsapp')->default('0');
            $table->boolean('ussd')->default('0');
            $table->string('password')->nullable();
            $table->string('reg_date')->nullable();
            $table->enum('source',['online','admin'])->nullable();
            $table->string('failed_logins')->nullable();
            $table->string('transfer_limit')->nullable();
            $table->string('failed_balance')->nullable();
            $table->string('failed_pin')->nullable();
            $table->string('status')->nullable();
            $table->string('referral_code')->nullable();
            $table->string('referral')->nullable();
            $table->tinyInteger('status');
            $table->boolean('enable_sms_alert')->default(0);
            $table->boolean('enable_sms_alert')->default(0);
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
};

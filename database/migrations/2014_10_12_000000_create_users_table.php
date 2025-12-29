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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('gender')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->foreignId('role_id')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->timestamp('is_2fa_enable')->nullable();
            $table->string('two_factor_code')->nullable();
            $table->dateTime('two_factor_expire_at')->nullable();
            $table->boolean('status')->default('0');
            $table->text('notes')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('account_type');
            $table->string('signature')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};

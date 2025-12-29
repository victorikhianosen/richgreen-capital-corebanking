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
        Schema::create('loan_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id');
            $table->foreignId('customer_id');
            $table->foreignId('branch_id');
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->string('principal')->nullable();
            $table->string('principal_balance')->nullable();
            $table->string('interest')->nullable();
            $table->string('fees')->nullable();
            $table->string('penalty')->nullable();
            $table->string('due')->nullable();
            $table->tinyInteger('system_generated')->default('0');
            $table->tinyInteger('closed')->default('0');
            $table->tinyInteger('missed')->default('0');
            $table->tinyInteger('missed_penalty_applied')->default('0');
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
        Schema::dropIfExists('loan_schedules');
    }
};

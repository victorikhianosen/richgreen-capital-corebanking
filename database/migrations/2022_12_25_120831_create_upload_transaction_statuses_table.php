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
        Schema::create('upload_transaction_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id');
            $table->foreignId('customer_id')->nullable();
            $table->foreignId('general_ledger_id')->nullable();
            $table->string('balance');
            $table->string('amount');
            $table->string('trx_type');
            $table->string('gl_type')->nullable();
            $table->date('trx_date');
            $table->text('reason')->nullable();
            $table->boolean('trx_status')->default(0);
            $table->boolean('upload_status')->default(0);
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
        Schema::dropIfExists('upload_transaction_statuses');
    }
};

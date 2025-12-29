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
        Schema::create('collaterals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->nullable();
            $table->foreignId('customer_id')->nullable();
            $table->foreignId('collateral_type_id')->nullable();
            $table->string('name')->nullable();
            $table->string('value')->default(0);
            $table->date('date')->nullable();
            $table->enum('status',['returned_to_customer','repossessed','repossession_initiated','sold','lost','collateral_with_customer','deposited_into_branch'])->nullable();
            $table->text('notes')->nullable();
            $table->string('photo')->nullable();
            $table->string('files')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('model_name')->nullable();
            $table->string('model_number')->nullable();
            $table->date('manufacture_date')->nullable();
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
        Schema::dropIfExists('collaterals');
    }
};

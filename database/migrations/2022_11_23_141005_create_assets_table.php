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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('asset_type_id')->nullable();
            $table->foreignId('branch_id')->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('purchase_price')->nullable();
            $table->string('replacement_value')->nullable();
            $table->string('initial')->nullable();
            $table->text('serial_number')->nullable();
            $table->text('bought_from')->nullable();
            $table->text('note')->nullable();
            $table->text('file')->nullable();
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
        Schema::dropIfExists('assets');
    }
};

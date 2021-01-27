<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('polic_id')->nullable();
            $table->integer('tarrif_id');
            $table->float('Amount');
            $table->string('OrderId')->nullable();
            $table->string('Status')->nullable();
            $table->integer('PaymentId')->nullable();
            $table->integer('CardId')->nullable();
            $table->string('Pan')->nullable();
            $table->string('link')->nullable();
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
        Schema::dropIfExists('payments');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePolisiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('polisies', function (Blueprint $table) {
            $table->id();
            $table->integer('active')->default(0);
            $table->integer('subscribed')->default(1);
            $table->integer('changed_tarrif')->default(0);
            $table->integer('user_id');
            $table->integer('number')->nullable();
            $table->string('address');
            $table->string('appartment')->nullable();
            $table->date('start')->nullable();
            $table->date('finish')->nullable();
            $table->integer('tarrif_id');
            $table->text('vote')->nullable();
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
        Schema::dropIfExists('polisies');
    }
}

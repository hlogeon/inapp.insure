<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_surname')->nullable();
            $table->string('address')->nullable();
            $table->integer('cashback_id')->nullable();
            $table->string('RebillId')->nullable();
            $table->float('cashback', 0, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
            $table->dropColumn('user_name');
            $table->dropColumn('user_email');
            $table->dropColumn('user_surname');
            $table->dropColumn('address');
            $table->dropColumn('cashback_id');
            $table->dropColumn('RebillId');
            $table->dropColumn('cashback');
        });
    }
}

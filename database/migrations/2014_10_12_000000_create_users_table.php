<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
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
            $table->string('openid')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('phone', 11)->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('api_token', 64)->nullable();
            $table->string("session_key", 100)->nullable();
            $table->string('refresh_token', 64)->nullable();
            $table->softDeletes();
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
}

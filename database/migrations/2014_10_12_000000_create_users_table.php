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
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('fullname')->nullable();
            $table->string('company')->nullable();
            $table->string('phone1', 20)->unique()->nullable();
            $table->string('phone2', 20)->nullable();
            $table->string('address')->nullable();
            $table->float('balance', 8, 2)->default(0);
            $table->smallInteger('rank')->default(1);
            $table->smallInteger('trust')->default(1);
            $table->text('comment')->nullable();
            $table->boolean('active')->default(1);
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('api_token', 80)->unique()->nullable()->default(null);
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
}

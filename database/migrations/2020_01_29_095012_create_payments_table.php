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
            $table->bigIncrements('id');
            $table->string('name')->default('payment');
            $table->float('amount', 10, 2)->default(0);
            $table->float('balance_before', 10, 2)->nullable();
            $table->float('balance_after', 10, 2)->nullable();
            $table->string('comment')->default('');

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('manager_id');
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

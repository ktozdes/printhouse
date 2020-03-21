<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('c')->nullable()->default(false);
            $table->boolean('m')->nullable()->default(false);
            $table->boolean('y')->nullable()->default(false);
            $table->boolean('k')->nullable()->default(false);
            $table->boolean('pantone')->nullable()->default(false);
            $table->boolean('urgent')->default(false);
            $table->boolean('deliver')->default(false);
            $table->string('address')->nullable();
            $table->text('comment')->nullable();

            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('storage_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->unsignedBigInteger('status_id')->nullable();
            
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
        Schema::dropIfExists('orders');
    }
}

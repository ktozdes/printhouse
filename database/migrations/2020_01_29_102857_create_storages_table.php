<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoragesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('storages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->default('order');
            $table->integer('quantity')->default(0);
            $table->integer('quantity_before')->nullable();
            $table->integer('quantity_after')->nullable();
            $table->float('price', 10, 2)->nullable();
            
            $table->bigInteger('plate_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('manager_id')->nullable();
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
        Schema::dropIfExists('storages');
    }
}

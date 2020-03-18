<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('specification')->nullable();
            $table->string('producer')->nullable();
            $table->float('price', 10, 2)->default(0);
            $table->integer('quantity')->default(0);
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('thickness')->nullable();
            $table->string('measurement_unit')->default('mm');
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
        Schema::dropIfExists('plates');
    }
}

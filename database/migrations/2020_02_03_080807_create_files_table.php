<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('url');
            $table->string('name')->nullable();
            $table->string('old_name')->nullable();
            $table->string('pages')->nullable();
            $table->string('size')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->unsignedInteger('filable_id')->unsigned();
            $table->string('filable_type');

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
        Schema::dropIfExists('files');
    }
}

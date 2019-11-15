<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommondataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commondata', function (Blueprint $table) {
            $table->bigIncrements('id');            
            $table->nestedSet();
            
            $table->string('key', 64);
            $table->text('value')->nullable();
            $table->integer('position')->nullable()->default(0);
            $table->smallInteger('readonly')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('commondata');
    }
}

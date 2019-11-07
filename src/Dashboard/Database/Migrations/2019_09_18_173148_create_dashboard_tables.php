<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDashboardTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	$this->down();
    	
    	Schema::create('dashboards', function (Blueprint $table) {
    		$table->increments('id');
    		$table->unsignedInteger('user_id');
            $table->string('name', 64);
            $table->smallInteger('position')->default(0);
        });

    	Schema::create('dashboard_applets', function (Blueprint $table) {
    		$table->increments('id');
    		$table->unsignedInteger('dashboard_id');
            $table->string('class', 512);            
            $table->smallInteger('column')->default(1);
            $table->smallInteger('row')->default(0);
            $table->text('options');
            
            $table->foreign('dashboard_id')->references('id')->on('dashboards');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    	Schema::dropIfExists('dashboard_applets');
    	Schema::dropIfExists('dashboards');    	
    }
}

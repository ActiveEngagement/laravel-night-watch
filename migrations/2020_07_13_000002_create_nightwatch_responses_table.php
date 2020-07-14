<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNightwatchResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nightwatch_responses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('watcher_id')->unsigned();
            $table->foreign('watcher_id')->references('id')->on('nightwatch_watchers')->onDelete('cascade')->onUpdate('cascade');
            $table->boolean('success');
            $table->integer('status_code');
            $table->json('response');
            $table->text('message')->nullable();
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
        Schema::dropIfExists('nightwatch_responses');
    }
}

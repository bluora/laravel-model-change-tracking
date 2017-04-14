<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLogModelStateChangeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_model_state_change', function ($table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('model')->default('');
            $table->bigInteger('model_id');
            $table->text('state')->nullable();
            $table->timestamp('log_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->bigInteger('log_by')->unsigned()->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->foreign('log_by')->references('id')->on('user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_model_state', function (Blueprint $table) {
            $table->dropForeign('log_model_state_log_by_foreign');
        });
        Schema::drop('log_model_state');
    }
}

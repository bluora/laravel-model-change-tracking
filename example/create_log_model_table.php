<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogModelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_model', function ($table) {
            $table->bigIncrements('id');
            $table->string('model');
            $table->integer('model_id');
            $table->text('old_value');
            $table->text('new_value');
            $table->timestamp('log_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->integer('log_by');
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
        Schema::table('log_model', function (Blueprint $table) {
            $table->dropForeign('log_model_log_by_foreign');
        });
        Schema::drop('log_model');
    }
}

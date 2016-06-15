<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLogModelChangeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_model_change', function ($table) {
            $table->bigIncrements('id');
            $table->string('model');
            $table->integer('model_id');
            $table->text('column_name');
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
        Schema::table('log_model_change', function (Blueprint $table) {
            $table->dropForeign('log_model_change_log_by_foreign');
        });
        Schema::drop('log_model_change');
    }
}

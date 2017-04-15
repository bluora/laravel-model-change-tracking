<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLogModelChangeTable extends Migration
{
    protected $table_name = 'log_model_change';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table_name, function ($table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('model')->default('');
            $table->bigInteger('model_id');
            $table->string('table_name')->default('');
            $table->text('column_name')->nullable();
            $table->text('old_text')->nullable();
            $table->json('old_value')->nullable();
            $table->text('new_text')->nullable();
            $table->json('new_value')->nullable();
            $table->json('add_value')->nullable();
            $table->json('remove_value')->nullable();
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
        Schema::table($this->table_name, function (Blueprint $table) {
            $table->dropForeign($this->table_name.'_log_by_foreign');
        });
        Schema::drop($this->table_name);
    }
}

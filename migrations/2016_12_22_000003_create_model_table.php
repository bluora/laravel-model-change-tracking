<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelTable extends Migration
{
    protected $table_name = 'model';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table_name, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('name', 255)->default('');
            $table->string('title', 255)->default('');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('log_model_change', function ($table) {
            $table->foreign('model_id')->references('id')->on($this->table_name);
        });

        Schema::table('log_model_state_change', function ($table) {
            $table->foreign('model_id')->references('id')->on($this->table_name);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_model_change', function ($table) {
            $table->dropForeign('log_model_change_model_id_foreign');
        });

        Schema::table('log_model_state_change', function ($table) {
            $table->dropForeign('log_model_state_change_model_id_foreign');
        });

        Schema::drop($this->table_name);
    }
}

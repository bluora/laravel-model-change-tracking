<?php
namespace ModelChangeTracking;

use Auth;

trait LogChangesTrait
{

    /**
     * Get the namespaced class reference to the logging model
     * 
     * @return  string
     */
    public function getModelsPath()
    {
        return 'App\\Models\\';
    }

    /**
     * Get the namespaced class reference to the logging model
     * 
     * @return  string
     */
    public function getLoggingModel()
    {
        return 'App\\Models\\LogModel';
    }

    /**
     * Boot the log changes trait for a model.
     *
     * @return void
     */
    public static function bootLogChangesTrait()
    {
        static::saving(function ($model) {
            $casts = $model->getCasts();
            $log_model_name = $model->getLoggingModel();
            $models_path = $model->getModelsPath();
            foreach($model->getDirty(true) as $column_name => $value) {
                if (empty($model->do_not_log[$column_name])
                    && (empty($casts[$column_name]) || $casts[$column_name] != 'json')) {
                    $log = new $log_model_name;
                    $log->model = str_replace($models_path, '', static::class);
                    $log->model_id = $model->id;
                    $log->column_name = $column_name;
                    $old_value = $model->getOriginal($column_name);
                    $log->old_value = json_encode((is_null($old_value)) ? '' : $old_value);
                    $log->new_value = json_encode($value);
                    if (Auth::check()) {
                        $log->log_by = Auth::user()->id;
                    }
                    $log->save();
                }
            }
        });
    }
}

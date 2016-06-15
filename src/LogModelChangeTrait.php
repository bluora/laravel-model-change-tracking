<?php

namespace ModelChangeTracking;

use Auth;
use Diff\Differ\MapDiffer;

trait LogModelChangeTrait
{

    /**
     * Boot the log changes trait for a model.
     *
     * @return void
     */
    public static function bootLogChangesTrait()
    {
        static::saving(function ($model) {
            $casts = $model->getCasts();
            foreach ($model->getDirty(true) as $column_name => $value) {
                if ((empty($model->do_not_log) || !in_array($column_name, $model->do_not_log))
                    && (empty($casts[$column_name]) || $casts[$column_name] != 'json')) {
                    $old_value = $model->getOriginal($column_name);
                    $log_change = [];
                    static::getModelChangeDiff($column_name, $log_change, $old_value, $value);
                    foreach ($log_change as $change) {
                        self::addModelChange($model, $change['column_name'], $change['old_value'], $change['new_value']);
                    }
                }
            }
        });
    }

    /**
     * Calculate the difference.
     *
     * @param string $column_name
     * @param array  &$log_change
     * @param mixed  $old_value
     * @param mixed  $new_value
     *
     * @return void
     */
    private static function getModelChangeDiff($column_name, &$log_change, $old_value, $new_value)
    {
        if (is_array($old_value) && is_array($new_value)) {
            $difference = (new MapDiffer())->doDiff($old_value, $new_value);
            foreach ($difference as $key => $value) {
                $value = $value->toArray();
                if (!array_key_exists('oldvalue', $value)) {
                    $value['oldvalue'] = '';
                }
                if (!array_key_exists('newvalue', $value)) {
                    $value['newvalue'] = '';
                }
                if (is_array($value['oldvalue']) && is_array($value['newvalue'])) {
                    static::getModelChangeDiff($column_name.'.'.$key, $log_change, $value['oldvalue'], $value['newvalue']);
                } else {
                    $log_change[] = [
                        'column_name' => $column_name.'.'.$key,
                        'old_value'   => $value['oldvalue'],
                        'new_value'   => $value['newvalue'],
                    ];
                }
            }
        } else {
            $log_change[] = [
                'column_name' => $column_name,
                'old_value'   => $old_value,
                'new_value'   => $new_value,
            ];
        }
    }

    /**
     * Log the change.
     *
     * @param string $model
     * @param string $column_name
     * @param mixed  $old_value
     * @param mixed  $new_value
     *
     * @return void
     */
    private static function addModelChange($model, $column_name, $old_value, $new_value)
    {
        $models_path = Config::get('model_change_tracking.ModelsPath');
        $log_model_name = Config::get('model_change_tracking.LogModelChange');

        $log = new $log_model_name();
        $log->model = str_replace($models_path, '', static::class);
        $log->model_id = $model->id;
        $log->column_name = $column_name;
        $log->old_value = json_encode((is_null($old_value)) ? '' : $old_value);
        $log->new_value = json_encode($new_value);
        if (Auth::check()) {
            $log->log_by = Auth::user()->id;
        }
        $log->ip_address = request()->ip();
        $log->save();
    }
}

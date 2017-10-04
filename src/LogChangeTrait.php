<?php

namespace Bluora\LaravelModelChangeTracking;

use App\Models\LogModelChange;
use Auth;
use Config;
use Diff\Differ\MapDiffer;

trait LogChangeTrait
{
    /**
     * Disable trimming of column values.
     *
     * @param var
     */
    protected static $disable_trim_values = false;

    /**
     * No logging of the standard date columns.
     *
     * @param array
     */
    protected static $do_not_log_fields = ['created_at', 'updated_at', 'archived_at', 'deleted_at'];

    /**
     * Run a dirty check before returning what is dirty.
     *
     * @return array
     */
    public function preDirtyCheck()
    {
        // Ensure empty string values for dates are converted to null
        $casts = $this->getDates();
        foreach ($this->getDirty() as $column_name => $value) {
            if (in_array($column_name, $casts) && $value === '') {
                $this->$column_name = null;
            }
        }

        // Trim all values
        if (!static::$disable_trim_values) {
            foreach ($this->getDirty() as $column_name => $value) {
                if (!is_null($value)) {
                    $this->$column_name = trim($value);
                }
            }
        }

        return $this->getDirty();
    }

    /**
     * Calculate the difference.
     *
     * @param string $column_name
     * @param array  &$log_change
     * @param mixed  $old_text
     * @param mixed  $new_text
     *
     * @return void
     */
    private static function getModelChangeDiff($column_name, &$log_change, $old_text, $new_text)
    {
        if (is_array($old_text) && is_array($new_text)) {
            $difference = (new MapDiffer())->doDiff($old_text, $new_text);

            foreach ($difference as $key => $value) {
                $value = $value->toArray();

                if (!array_key_exists('oldText', $value)) {
                    $value['oldText'] = '';
                }

                if (!array_key_exists('newValue', $value)) {
                    $value['newValue'] = '';
                }

                if (is_array($value['oldText']) && is_array($value['newValue'])) {
                    static::getModelChangeDiff($column_name.'.'.$key, $log_change, $value['oldText'], $value['newValue']);

                    return;
                }

                $log_change[] = [
                    'column_name' => $column_name.'.'.$key,
                    'old_text'    => $value['oldText'],
                    'new_text'    => $value['newValue'],
                ];
            }

            return;
        }

        $log_change[] = [
            'column_name' => $column_name,
            'old_text'    => $old_text,
            'new_text'    => $new_text,
        ];
    }

    /**
     * Log the change.
     *
     * @param string $model
     * @param string $table_name
     * @param string $column_name
     * @param string $old_text
     * @param string $new_text
     * @param array  $add_value
     * @param array  $remove_value
     *
     * @return void
     */
    public static function addModelChange(
        $model_id,
        $table_name,
        $column_name,
        $old_text,
        $new_text,
        $add_value,
        $remove_value
    ) {
        $models_path = Config::get('model_change_tracking.ModelsPath');
        $log_model_name = Config::get('model_change_tracking.LogModelChange');
        $model_id_name = config('model_change_tracking.log-model-change.model-id', 'model_id');

        $add_value = (is_null($add_value)) ? '' : $add_value;
        $remove_value = (is_null($remove_value)) ? '' : $remove_value;

        if ($old_text !== $new_text) {
            $log = new $log_model_name();
            $log->model = str_replace($models_path, '', static::class);
            $log->$model_id_name = $model_id;
            $log->table_name = $table_name;
            $log->column_name = $column_name;
            $log->old_text = $old_text;
            $log->new_text = $new_text;
            $log->add_value = json_encode($add_value);
            $log->remove_value = json_encode($remove_value);
            if (Auth::check()) {
                $log->log_by = Auth::user()->getKey();
            }
            $log->ip_address = request()->ip();
            $log->save();
        }
    }

    /**
     * Process relationship changes and log.
     *
     * @param string $relation_method_name
     * @param array  $new_value
     *
     * @return void
     */
    public function processManyToManyChange($relation_method_name, $new_value, $override_relationship_method = null)
    {
        // The join between the model and the other model
        $relation = $this->$relation_method_name();

        // This model
        $model_id = $this->getKey();
        $model_key_name = $relation->getQualifiedForeignKeyName();
        $model_qualified_id_column = $this->getQualifiedKeyName();

        // The other model
        $related_model = $relation->getRelated();
        $related_model_key_name = $relation->getQualifiedRelatedKeyName();
        $related_model_id_column = $related_model->getKeyName();
        $related_model_class = get_class($related_model);

        // Check singular and plural
        if (!is_null($override_relationship_method)) {
            $relationship_method = $override_relationship_method;
        } elseif (is_null($override_relationship_method)) {
            $relationship_method = camel_case($this->getTable());
            if (!method_exists($related_model_class, $relationship_method)) {
                $relationship_method = str_plural($relationship_method);
            }
        }

        // Old items before we make change
        $old_data = $related_model_class::whereHas($relationship_method, function ($sub_query) use ($model_qualified_id_column, $model_id) {
            $sub_query->where($model_qualified_id_column, $model_id);
        })->get();

        // Run sync
        $relation->sync($new_value);

        // New items after we made the change
        $new_data = $related_model_class::whereHas($relationship_method, function ($sub_query) use ($model_qualified_id_column, $model_id) {
            $sub_query->where($model_qualified_id_column, $model_id);
        })->get();

        // Filter this
        $old_data_list = $old_data;
        $new_data_list = $new_data;
        $old_data_id_list = array_unique($old_data_list->pluck($related_model_id_column)->all());
        $new_data_id_list = array_unique($new_data_list->pluck($related_model_id_column)->all());

        // Calculate the change
        $add_value = array_diff($new_data_id_list, $old_data_id_list);
        $remove_value = array_diff($old_data_id_list, $new_data_id_list);
        $change_values = $add_value + $remove_value;

        // Old text and values
        $old_text = $old_data->whereIn($related_model_id_column, $change_values)->implode('log_name', ', ');

        // New text and values
        $new_text = $new_data->whereIn($related_model_id_column, $change_values)->implode('log_name', ', ');

        // Log against the model
        self::addModelChange(
            $this->getKey(),
            $relation->getTable(),
            $related_model_key_name,
            $old_text,
            $new_text,
            $add_value,
            $remove_value
        );

        // Log against each added related model
        foreach ($add_value as $relation_id) {
            self::addModelChange(
                $relation_id,
                $relation->getTable(),
                $model_key_name,
                '',
                $this->log_name,
                [$this->id],
                []
            );
        }

        // Log against each removed related model
        foreach ($remove_value as $relation_id) {
            self::addModelChange(
                $relation_id,
                $relation->getTable(),
                $model_key_name,
                $this->log_name,
                '',
                [],
                [$this->id]
            );
        }
    }

    /**
     * Get change log for this model.
     *
     * @return
     */
    public function changeLog()
    {
        $model_id_name = config('model_change_tracking.log-model-change.model-id', 'model_id');
        $model_other_id_name = config('model_change_tracking.log-model-change.model-other-id', 'id');

        return $this->hasMany(LogModelChange::class, $model_id_name, $model_other_id_name)
            ->where('table_name', $this->getTable());
    }

    /**
     * Boot the log changes trait for a model.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public static function bootLogChangeTrait()
    {
        // Saving model event
        static::saving(function ($model) {
            $model->preDirtyCheck();
        });

        // Saved model event
        static::saved(function ($model) {

            // Track all changes to every column
            $casts = $model->getCasts();
            foreach ($model->getDirty(true) as $column_name => $value) {

                // Ignore columns found in the do_not_log variable, and ignore non-json columns
                if ((empty($model->do_not_log) || !in_array($column_name, $model->do_not_log))
                    && !in_array($column_name, static::$do_not_log_fields)
                    && (empty($casts[$column_name]) || $casts[$column_name] != 'json')) {
                    $old_text = $model->getOriginal($column_name);
                    $log_change = [];
                    static::getModelChangeDiff($column_name, $log_change, $old_text, $value);
                    foreach ($log_change as $change) {
                        if (is_array($change['old_text'])) {
                            $change['old_text'] = json_encode($change['old_text']);
                        }

                        if (is_array($change['new_text'])) {
                            $change['new_text'] = json_encode($change['new_text']);
                        }

                        self::addModelChange(
                            $model->id,
                            $model->getTable(),
                            $change['column_name'],
                            (string) $change['old_text'],
                            (string) $change['new_text'],
                            [],
                            []
                        );
                    }
                }
            }
        });
    }
}

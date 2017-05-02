<?php

namespace Bluora\LaravelModelChangeTracking;

use App;
use Auth;
use Config;

trait LogStateChangeTrait
{
    /**
     * Log the state.
     *
     * @param string $model
     * @param mixed  $state
     *
     * @return void
     */
    private static function addModelStateChange($model, $state)
    {
        $models_path = Config::get('model_change_tracking.ModelsPath');
        $log_model_name = Config::get('model_change_tracking.LogModelStateChange');

        $log = new $log_model_name();
        $log->model = str_replace($models_path, '', static::class);
        $log->model_id = $model->id;
        $log->state = $state;
        $log->log_by = null;
        if (!App::runningInConsole() && Auth::check()) {
            $log->log_by = Auth::user()->id;
        }
        $log->ip_address = request()->ip();
        $log->save();
    }

    /**
     * Boot the events that apply which user is making the last event change.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.NpathComplexity)
     */
    public static function bootLogStateChangeTrait()
    {
        static::created(function ($model) {
            static::addModelStateChange($model, 'created');
        });

        static::updated(function ($model) {
            static::addModelStateChange($model, 'updated');
        });

        if (method_exists(get_class(), 'deleted')) {
            static::deleted(function ($model) {
                static::addModelStateChange($model, 'deleted');
            });
        }

        if (method_exists(get_class(), 'restored')) {
            static::restored(function ($model) {
                static::addModelStateChange($model, 'restored');
            });
        }
    }

    /**
     * Return the user that created this model.
     *
     * @return User
     */
    public function getCreatedByAttribute()
    {
        return $this->stateChange()
            ->where('state', 'created')
            ->first();
    }

    /**
     * Return the user that updated this model.
     *
     * @return User
     */
    public function getUpdatedByAttribute()
    {
        return $this->stateChange()
            ->where('state', 'updated')
            ->orderBy('log_at', 'DESC')
            ->first();
    }

    /**
     * Return the user that deleted this model.
     *
     * @return User
     */
    public function getDeletedByAttribute()
    {
        return $this->stateChange()
            ->where('state', 'deleted')
            ->orderBy('log_at', 'DESC')
            ->first();
    }

    /**
     * Return the user that restored this model.
     *
     * @return User
     */
    public function getRestoredByAttribute()
    {
        return $this->stateChange()
            ->where('state', 'restored')
            ->orderBy('log_at', 'DESC')
            ->first();
    }

    /**
     * Get the logs for this model.
     *
     * @return Collection
     */
    public function stateChange()
    {
        return $this->hasMany(config('model_change_tracking.LogModelStateChange'), 'model_id', 'id')
            ->where('model', studly_case($this->table))
            ->orderBy('log_at');
    }
}

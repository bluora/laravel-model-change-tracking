<?php

namespace Bluora\LaravelModelChangeTracking;

use App;
use Auth;

trait ChangeByUserTrait
{
    public function getCreatedByColumn()
    {
        return 'created_by';
    }

    public function getUpdatedByColumn()
    {
        return 'updated_by';
    }

    public function getArchivedByColumn()
    {
        return 'archived_by';
    }

    public function getDeletedByColumn()
    {
        return 'deleted_by';
    }

    /**
     * Boot the events that apply which user is making the last event change.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public static function bootChangeByUserTrait()
    {
        static::creating(function ($model) {
            if (!App::runningInConsole() && Auth::check()) {
                if ($model->getCreatedByColumn()) {
                    $model->{$model->getUpdatedByColumn()} = Auth::user()->id;
                }
                if ($model->getUpdatedByColumn()) {
                    $model->{$model->getUpdatedByColumn()} = Auth::user()->id;
                }
            }

            return true;
        });

        static::updating(function ($model) {
            if (!App::runningInConsole() && Auth::check() && $model->getUpdatedByColumn()) {
                $model->{$model->getUpdatedByColumn()} = Auth::user()->id;
            }

            return true;
        });

        static::deleting(function ($model) {
            if (!App::runningInConsole() && Auth::check() && $model->getDeletedByColumn()) {
                $model->{$model->getDeletedByColumn()} = Auth::user()->id;
            }

            return true;
        });
    }
}

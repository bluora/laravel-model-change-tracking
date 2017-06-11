<?php

namespace Bluora\LaravelModelChangeTracking;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // Add the command to artisan.
        if ($this->app->runningInConsole()) {
            $this->commands([
                ModelSyncCommand::class,
            ]);
        }

        // Publish the default config and models.
        $this->publishes([
            __DIR__.'/../config/model_change_tracking.php' => base_path('config/model_change_tracking.php'),
            __DIR__.'/../models/LogModelChange.php'        => base_path('app/Models/LogModelChange.php'),
            __DIR__.'/../models/LogModelStateChange.php'   => base_path('app/Models/LogModelStateChange.php'),
        ]);

        // Load migrations.
        $this->loadMigrationsFrom(__DIR__.'/../migrations');
    }
}

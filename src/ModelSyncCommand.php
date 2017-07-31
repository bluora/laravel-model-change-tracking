<?php

namespace Bluora\LaravelModelChangeTracking;

use App\Models\AppModel;
use Illuminate\Console\Command;
use Storage;

class ModelSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'models:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pre-deploy process. Run after migrations.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Read the app folder & save
        $models = Storage::disk('models')->allFiles();

        $models = array_filter($models, function ($model_file_name) {
            return stripos($model_file_name, '.php') !== false;
        });

        foreach ($models as $model_file_name) {
            $model_name = substr($model_file_name, 0, -4);
            $model_class = 'App/Models/'.$model_name;
            $model_title = ucwords(str_replace('_', ' ', snake_case($model_name)));
            if (class_exists($model_class)) {
                $model = (new $model_class());
                if (method_exists($model, 'getFilterModelName')) {
                    $model_title = $model->getFilterModelName();
                }
            }
            $app_model = AppModel::firstOrCreate(['name' => $model_name]);

            $app_model->name = $model_name;
            $app_model->title = $model_title;

            if ($app_model->getDirty()) {
                $app_model->save();
            }
        }
    }
}

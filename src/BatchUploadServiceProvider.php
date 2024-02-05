<?php

declare(strict_types=1);

namespace Dandysi\Laravel\BatchUpload;

use Dandysi\Laravel\BatchUpload\Console\ListCommand;
use Dandysi\Laravel\BatchUpload\Console\ProcessorGeneratorCommand;
use Dandysi\Laravel\BatchUpload\Console\ShowCommand;
use Dandysi\Laravel\BatchUpload\Console\ViewCommand;
use Dandysi\Laravel\BatchUpload\FileReaders\FileReaderRepository;
use Dandysi\Laravel\BatchUpload\Processors\ProcessorRepository;
use Illuminate\Support\ServiceProvider;
use Dandysi\Laravel\BatchUpload\Console\CreateCommand;
use Dandysi\Laravel\BatchUpload\Console\DispatchCommand;
use Dandysi\Laravel\BatchUpload\FileReaders\FileReaderInterface;
use Dandysi\Laravel\BatchUpload\FileReaders\CsvFileReader;
use Illuminate\Contracts\Foundation\Application;

class BatchUploadServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateCommand::class,
                DispatchCommand::class,
                ListCommand::class,
                ShowCommand::class,
                ProcessorGeneratorCommand::class
            ]);
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->publishes([__DIR__ . '/../config/config.php' => config_path('batch-upload.php')]);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->bind(FileReaderRepository::class, function () {
            $repo = new FileReaderRepository();
            foreach (config('batch-upload.file_readers', []) as $fileReader) {
                $repo->add($this->app->make($fileReader));
            }
            return $repo;
        });

        $this->app->singleton(ProcessorRepository::class, function (Application $app) {
            return new ProcessorRepository($app, config('batch-upload.processors', []));
        });

        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'batch-upload');
    }
}

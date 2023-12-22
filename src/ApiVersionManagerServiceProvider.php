<?php

namespace UttamRabadiya\ApiVersionManager;

use Illuminate\Routing\Matching\UriValidator as LaravelUriValidator;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
use UttamRabadiya\ApiVersionManager\Commands\MakeVersionedRequestCommand;
use UttamRabadiya\ApiVersionManager\Commands\MakeVersionedResourceCommand;
use UttamRabadiya\ApiVersionManager\Services\UriValidator;

class ApiVersionManagerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('api-version-manager.php'),
            ], 'api-version-manager');

            // Registering package commands.
            $this->commands([
                MakeVersionedRequestCommand::class,
                MakeVersionedResourceCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'api-version-manager');

        $this->loadRouteValidator();
    }

    private function loadRouteValidator(): void
    {
        $validators = Route::getValidators();

        // Append our custom UriValidator
        $validators[] = new UriValidator();
        Route::$validators = array_filter($validators, function ($validator) {
            // Remove the default Laravel UriValidator
            return get_class($validator) != LaravelUriValidator::class;
        });
    }
}

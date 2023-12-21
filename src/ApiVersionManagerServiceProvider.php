<?php

namespace Uttamrabadiya\ApiVersionManager;

use Illuminate\Routing\Matching\UriValidator as LaravelUriValidator;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
use Uttamrabadiya\ApiVersionManager\Commands\MakeVersionedRequestCommand;
use Uttamrabadiya\ApiVersionManager\Commands\MakeVersionedResourceCommand;
use Uttamrabadiya\ApiVersionManager\Services\UriValidator;

class ApiVersionManagerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('api-version-manager.php'),
            ], 'config');

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
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'api-version-manager');

        $this->loadRouteValidator();
    }

    private function loadRouteValidator() {
        $validators = Route::getValidators();

        // Append our custom UriValidator
        $validators[] = new UriValidator();
        Route::$validators = array_filter($validators, function ($validator) {
            // Remove the default Laravel UriValidator
            return get_class($validator) != LaravelUriValidator::class;
        });
    }
}

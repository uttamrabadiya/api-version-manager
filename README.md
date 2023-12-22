# Laravel API Version Manager

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)

The **Laravel API Version Manager** Package streamlines the management of API endpoint versions in Laravel applications. This package empowers you to effortlessly handle API versioning, eliminating the necessity to create individual controllers for each version. Its design presents a flexible and efficient solution, enabling you to define fallback versions and effortlessly generate version-specific Requests and Resources.


## Features
- **Fallback Versions**: Define fallback versions for your API routes, ensuring smooth transitions and maintaining backward compatibility.
- **Single Controller**: Eliminate the need to create separate controllers for each API version. Our package dynamically injects version-specific Request and Resource classes into your existing controller.
- **Effortless Versioning**: Easily manage API versions through route configuration. The package automatically handles the resolution of version-specific components, streamlining your development process.


## Installation
You can install the package via Composer:

```bash
composer require uttamrabadiya/api-version-manager
```

## Configuration

### Publish Config File
It is mandatory to publish the config file before using the package. You can publish the config file using the following command:
```bash
php artisan vendor:publish --tag=api-version-manager
```

### Mandatory Configurations:
- Define available `versions` array in config
- Define default version `version` in config

### Other possible configurations are:
- `app_http_namespace` (default: `App\Http`) - Generally we use `App\Http` namespace to store all our requests & resources classes, but if you are using different namespace then you can define it here.
- `api_prefix` (default: `api`) - API prefix for all versioned routes.
- `use_fallback_entity` (default: `true`) - If you want to use fallback entity for all request & resource class then set this to `true`, otherwise set it to `false`. For example, you define `SampleRequest` in **V1**, and now you want to use same request in **V2** then you can set this option to `true` and it will automatically use `SampleRequest` from **V1**.

## Available Commands

#### Create a new versioned request
```bash
php artisan make:versioned-request {name}
```
**Possible options:**
- `--force`: Overwrite the request if it already exists.

#### Create a new versioned resource
```bash
php artisan make:versioned-resource {name}
```
**Possible options:**
- `--collection`: Create a resource collection instead of a single resource.
- `--force`: Overwrite the resource if it already exists.

## Usage

Example of `api.php` file:
```php
Route::prefix('v1')->group(function () {
    Route::get('endpoint1', [SomeController::class, 'endpoint1']); // Available on v1 & v2 (Via default fallback)
    Route::get('endpoint2', [SomeController::class, 'endpoint2']); // Available on v1 & v2 (Via default fallback)
});
Route::prefix('v2')->group(function () {
    Route::get('new-endpoint', [SomeController::class, 'endpoint3']); // Available only on v2 
});
```
Example of `SomeController.php` file:
```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\Versioned\EndpointResource; // Mandatory to use versioned resource only. Don't use `App\Http\Resources\V1\EndpointResource` or `App\Http\Resources\V2\EndpointResource`
use App\Http\Requests\Versioned\NewEndpointRequest; // Mandatory to use versioned request only. Don't use `App\Http\Requests\V1\NewEndpointRequest` or `App\Http\Requests\V2\NewEndpointRequest`
use Illuminate\Http\Request;

class SomeController extends Controller
{
    public function endpoint1(Request $request)
    {
        return DashboardResource::item(['some' => 'data']); // Replacement of native `new DashboardResource(['some' => 'data'])` resource
    }
    
    public function endpoint1(Request $request)
    {
        return DashboardResource::collection(['some' => 'data']);
    }
    
    public function endpoint3(NewEndpointRequest $request)
    {
        return DashboardResource::item(['some' => 'data']);
    }
}

```

## Changelog
Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

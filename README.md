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
composer require uttamrabadiya/laravel-api-version-manager
```

## Usage
- Define Versions: Specify the supported API versions in your route configuration.
- Inject Versioned Components: In your existing controller methods, access version-specific Request and Resource classes seamlessly, without cluttering your codebase.
- Fallback Mechanism: Set up fallback versions to ensure graceful degradation when a requested version is not available.


### Available Commands

#### Create a new versioned request
```bash
php artisan make:versioned-request {name}
```
**Possible options:**
- `--version`: The version of the request to create. If not specified, the request will be created for the latest version.
- `--force`: Overwrite the request if it already exists.

#### Create a new versioned resource
```bash
php artisan make:versioned-resource {name}
```
**Possible options:**
- `--collection`: Create a resource collection instead of a single resource.
- `--force`: Overwrite the resource if it already exists.

#### Publish config file

``` php
php artisan vendor:publish --provider="UttamRabadiya\ApiVersionManager\ApiVersionManagerServiceProvider"
```

## Changelog
Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

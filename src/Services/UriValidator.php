<?php

namespace UttamRabadiya\ApiVersionManager\Services;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Matching\UriValidator as LaravelUriValidator;

class UriValidator extends LaravelUriValidator
{
    /**
     * @var array[]
     */
    private $versions;

    /**
     * @var string
     */
    private $apiPrefix;

    public function __construct()
    {
        $this->versions = config('api-version-manager.versions', []);
        $this->apiPrefix = config('api-version-manager.api_prefix');
    }

    /**
     * @param Route $route
     * @param Request $request
     * @return bool
     */
    public function matches(Route $route, Request $request): bool
    {
        $result = false;

        $path = rtrim($request->getPathInfo(), '/') ?: '/';

        /**
         * there is not more than one api version
         * or the route is not for api
         * strncmp work like Illuminate\Support\Str::startsWith in laravel
         */
        if (count($this->versions) < 1 || strncmp($path, $this->apiPrefix, strlen($this->apiPrefix)) !== 0) {
            return parent::matches($route, $request);
        }

        /**
         * basically it loop through API versions and change the path to support API fallback
         */
        for ($index = 0; $index < count($this->versions); $index++) {
            $result = preg_match($route->getCompiled()->getRegex(), rawurldecode($path));

            /**
             * exit if you find a proportionate route
             */
            if ($result) return (bool)$result;

            if (isset($this->versions[$index + 1])) {
                $path = str_replace($this->versions[$index], $this->versions[$index + 1], $path);
            }
        }
        return (bool)$result;
    }
}

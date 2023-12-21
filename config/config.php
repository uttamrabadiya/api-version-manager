<?php

return [
    /**
     * Laravel application namespace
     */
    'app_http_namespace' => 'App\\Http',

    /**
     * Latest stable version
     */
    'version' => 'v1',

    /**
     * this prefix help that the package does not process non-api routes
     */
    'api_prefix' => '/api/',

    /**
     * Write your API versions in descending order
     * Example: ['v2.1', 'v2', 'v1'] this way version v2 is a fallback for v2.1
     */
    'versions' => [],

    /**
     * Define to use fallback request/resource entity if not found in current version
     * Example: If you are using v2 version, and you have not created v2 version of SampleRequest then it will use SampleRequest from v1 version
     * NOTE: This will only control for request/resource classes and not for routes.
     */
    'use_fallback_entity' => false,
];

<?php

namespace UttamRabadiya\ApiVersionManager\Tests;

class ConfigTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // Reset config on each request
        config(['api-version-manager' => require __DIR__ . '/../config/config.php']);
    }

    /**
     * @test
     */
    public function itWillLoadDefaultConfig(): void
    {
        $config = config('api-version-manager');

        $this->assertIsArray($config);
        $this->assertSame([
            'app_http_namespace' => 'App\\Http',
            'version' => '',
            'api_prefix' => '/api/',
            'versions' => [],
            'use_fallback_entity' => false,
        ], $config);
    }
}
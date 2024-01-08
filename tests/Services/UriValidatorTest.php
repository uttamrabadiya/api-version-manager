<?php

namespace UttamRabadiya\ApiVersionManager\Tests\Services;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Config;
use UttamRabadiya\ApiVersionManager\Services\UriValidator;
use UttamRabadiya\ApiVersionManager\Tests\TestCase;

class UriValidatorTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Config::set('api-version-manager.version', 'v1');
        Config::set('api-version-manager.versions', ['v2', 'v1']);
    }

    /**
     * @return array<int, array<int, array<string, string>>>
     */
    public function dataProvider(): array
    {
        return [
            [
                [
                    'uri' => '/api/v2/example',
                    'method' => 'GET'
                ],
                [
                    'uri' => '/api/v2/example',
                    'method' => 'GET'
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<int, array<string, string>>>
     */
    public function invalidDataProvider(): array
    {
        return [
            [
                [
                    'uri' => '/api/v2/something-else',
                    'method' => 'GET'
                ],
                [
                    'uri' => '/api/v2/example',
                    'method' => 'GET'
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<int, array<string, string>>>
     */
    public function versionedDataProvider(): array
    {
        return [
            [
                [
                    'uri' => '/api/v1/example', // Fallback route
                    'method' => 'GET'
                ],
                [
                    'uri' => '/api/v2/example', // Requested
                    'method' => 'GET'
                ],
            ],
            [
                [
                    'uri' => '/api/v1/example',
                    'method' => 'GET'
                ],
                [
                    'uri' => '/api/v1/example',
                    'method' => 'GET'
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @param array<string, string> $routes
     * @param array<string, string> $request
     */
    public function itWillMatchRequestUriWithGivenRoute(array $routes, array $request): void
    {
        // Create a sample route with a specific URI pattern
        $route = new Route([$routes['method']], $routes['uri'], ['uses' => 'ExampleController@index']);
        $route->prepareForSerialization();

        // Create a request with the matching URI
        $request = Request::create($request['uri'], $request['method']);

        // Create an instance of UriValidator
        $uriValidator = new UriValidator();

        // Assert that the URI matches the route
        $this->assertTrue($uriValidator->matches($route, $request));
    }

    /**
     * @test
     * @dataProvider invalidDataProvider
     * @param array<string, string> $routes
     * @param array<string, string> $request
     */
    public function itWillNotMatchRequestUriWhenInvalidRoute(array $routes, array $request): void
    {
        // Create a sample route with a specific URI pattern
        $route = new Route([$routes['method']], $routes['uri'], ['uses' => 'ExampleController@index']);
        $route->prepareForSerialization();

        // Create a request with the matching URI
        $request = Request::create($request['uri'], $request['method']);

        // Create an instance of UriValidator
        $uriValidator = new UriValidator();

        // Assert that the URI matches the route
        $this->assertFalse($uriValidator->matches($route, $request));
    }

    /**
     * @test
     * @dataProvider versionedDataProvider
     * @param array<string, string> $routes
     * @param array<string, string> $request
     */
    public function itWillMatchRouteForFallbackVersion(array $routes, array $request): void
    {
        // Create a sample route with a specific URI pattern
        $route = new Route([$routes['method']], $routes['uri'], ['uses' => 'ExampleController@index']);
        $route->prepareForSerialization();

        // Create a request with the matching URI
        $request = Request::create($request['uri'], $request['method']);

        // Create an instance of UriValidator
        $uriValidator = new UriValidator();

        // Assert that the URI matches the route
        $this->assertTrue($uriValidator->matches($route, $request));
    }

}

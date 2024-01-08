<?php

namespace UttamRabadiya\ApiVersionManager\Tests\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Testing\PendingCommand;
use UttamRabadiya\ApiVersionManager\Exceptions\InvalidVersionException;
use UttamRabadiya\ApiVersionManager\Tests\TestCase;

class MakeVersionedRequestCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Config::set('api-version-manager.version', 'v1');
        Config::set('api-version-manager.versions', ['v2', 'v1']);
    }

    /**
     * @test
     */
    public function itWillGenerateVersionedRequestClass(): void
    {
        $words = ['Butter', 'Bun', 'Cheese', 'Tomato', 'Lettuce'];
        $randomWord = Arr::random($words);

        // Create a class name by combining the random word with "Request"
        $testRequestName = $randomWord . 'Request';

        /** @var PendingCommand $command */
        $command = $this->artisan('make:versioned-request', ['name' => $testRequestName, '--versions' => 'V1,V2']);
        $command->assertSuccessful();
        $command->execute();

        $this->assertFileExists(app_path(sprintf('Http/Requests/V1/%s.php', $testRequestName)));
        $this->assertFileExists(app_path(sprintf('Http/Requests/V2/%s.php', $testRequestName)));
        $this->assertFileExists(app_path(sprintf('Http/Requests/Versioned/%s.php', $testRequestName)));
        $this->assertStringContainsString('extends BaseRequest', (string)file_get_contents(app_path(sprintf('Http/Requests/Versioned/%s.php', $testRequestName))));
    }

    /**
     * @test
     */
    public function itWillGenerateVersionedRequestForSpecificClass(): void
    {
        $testRequestName = 'TestRequest';
        $this->expectException(InvalidVersionException::class);
        $this->expectExceptionMessage('Version INVALID is not supported by versions [V2,V1]');

        /** @var PendingCommand $command */
        $command = $this->artisan('make:versioned-request', ['name' => $testRequestName, '--versions' => 'invalid']);
        $command->assertFailed();
    }
}

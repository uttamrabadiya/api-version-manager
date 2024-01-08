<?php

namespace UttamRabadiya\ApiVersionManager\Tests\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Testing\PendingCommand;
use UttamRabadiya\ApiVersionManager\Exceptions\InvalidVersionException;
use UttamRabadiya\ApiVersionManager\Tests\TestCase;

class MakeVersionedResourceCommandTest extends TestCase
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
    public function itWillGenerateVersionedResourceClass(): void
    {
        $words = ['Butter', 'Bun', 'Cheese', 'Tomato', 'Lettuce'];
        $randomWord = Arr::random($words);

        // Create a class name by combining the random word with "Request"
        $testResourceName = $randomWord . 'Resource';

        /** @var PendingCommand $command */
        $command = $this->artisan('make:versioned-resource', ['name' => $testResourceName, '--versions' => 'V1,V2']);
        $command->assertSuccessful();
        $command->execute();

        $this->assertFileExists(app_path(sprintf('Http/Resources/V1/%s.php', $testResourceName)));
        $this->assertFileExists(app_path(sprintf('Http/Resources/V2/%s.php', $testResourceName)));
        $this->assertFileExists(app_path(sprintf('Http/Resources/Versioned/%s.php', $testResourceName)));
        $this->assertStringContainsString('extends BaseResource', (string)file_get_contents(app_path(sprintf('Http/Resources/Versioned/%s.php', $testResourceName))));
    }

    /**
     * @test
     */
    public function itWillGenerateVersionedResourceForSpecificClass(): void
    {
        $testResourceName = 'TestResource';
        $this->expectException(InvalidVersionException::class);
        $this->expectExceptionMessage('Version INVALID is not supported by versions [V2,V1]');

        /** @var PendingCommand $command */
        $command = $this->artisan('make:versioned-resource', ['name' => $testResourceName, '--versions' => 'invalid']);
        $command->assertFailed();
    }
}

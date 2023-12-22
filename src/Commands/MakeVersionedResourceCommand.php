<?php

namespace UttamRabadiya\ApiVersionManager\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Console\Input\InputOption;
use UttamRabadiya\ApiVersionManager\Exceptions\InvalidDefaultVersionException;
use UttamRabadiya\ApiVersionManager\Traits\VersionResolver;

class MakeVersionedResourceCommand extends GeneratorCommand
{
    use VersionResolver;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:versioned-resource';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new versioned resource';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Resource';

    /**
     * Execute the console command.
     * @throws InvalidDefaultVersionException|FileNotFoundException
     */
    public function handle()
    {
        parent::handle();

        $versions = $this->gatherVersionsInteractively();
        foreach ($versions as $version) {
            $this->createResource($version);
        }

        return true;
    }

    protected function getStub()
    {
        return __DIR__ . '/stubs/versioned_resource.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Resources\Versioned';
    }

    /**
     * Get the console command options.
     *
     * @return array<mixed>
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the resource already exists'],
            ['collection', 'c', InputOption::VALUE_NONE, 'Create a resource collection'],
        ];
    }

    private function createResource(string $version): void
    {
        /** @var string $resourceName */
        $resourceName = $this->argument('name');
        $resource = class_basename($resourceName);

        $this->call('make:resource', array_filter([
            'name' => "$version/$resource",
            '--force' => $this->option('force'),
            '--collection' => $this->option('collection'),
        ]));
    }

    /**
     * Gather the desired Sail services using an interactive prompt.
     *
     * @return array
     * @throws InvalidDefaultVersionException
     */
    private function gatherVersionsInteractively(): array
    {
        $versions = self::getAvailableVersions();
        $defaultVersion = self::getDefaultVersion();
        if (function_exists('\Laravel\Prompts\multiselect')) {
            return \Laravel\Prompts\multiselect(
                'Select required versions for the resource',
                $versions,
                [$defaultVersion],
            );
        }

        return $this->choice('Select required versions for the resource', $versions, array_search($defaultVersion, $versions), null, true);
    }
}

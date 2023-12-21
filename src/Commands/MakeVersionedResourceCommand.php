<?php

namespace Uttamrabadiya\ApiVersionManager\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Uttamrabadiya\ApiVersionManager\Traits\VersionResolver;

#[AsCommand(name: 'make:versioned:resource')]
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
     */
    public function handle()
    {
        parent::handle();

        $versions = self::getAvailableVersions();
        $latestVersion = self::getLatestVersion();

        $versions = explode(',', $this->option('versions'));

        foreach ($versions as $version) {
            $this->createResource($version);
        }
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
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the resource already exists'],
            ['collection', 'c', InputOption::VALUE_NONE, 'Create a resource collection'],
        ];
    }

    /**
     * Create a controller for the model.
     *
     * @return void
     */
    protected function createResource($version)
    {
        $resource = class_basename($this->argument('name'));

        $this->call('make:resource', array_filter([
            'name' => "$version/$resource",
            '--force' => $this->option('force'),
            '--collection' => $this->option('collection'),
        ]));
    }
}

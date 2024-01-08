<?php

namespace UttamRabadiya\ApiVersionManager\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Console\Input\InputOption;
use UttamRabadiya\ApiVersionManager\Exceptions\InvalidVersionException;
use UttamRabadiya\ApiVersionManager\Helpers\VersionHelper;

class MakeVersionedResourceCommand extends GeneratorCommand
{
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
     * The list of versioned classes.
     *
     * @var array<int, string>
     */
    protected $versionedClassList = [];

    /**
     * Execute the console command.
     * @throws InvalidVersionException|FileNotFoundException
     */
    public function handle()
    {
        if (is_null($this->option('versions'))) {
            $versions = $this->gatherVersionsInteractively();
        } else {
            /** @var string $versions */
            $versions = $this->option('versions');
            $versions = explode(',' , $versions);
            $versions = array_map('strtoupper', $versions);
            VersionHelper::validateVersions($versions);
        }

        foreach ($versions as $version) {
            $this->createResource($version);
        }

        parent::handle();

        return null;
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceVersionClasses($stub)->replaceNamespace($stub, $name)->replaceClass($stub, $name);
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
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $this->getResourceNamespace($rootNamespace).'\Versioned';
    }

    /**
     * Get the resource namespace for the class.
     */
    protected function getResourceNamespace(string $rootNamespace): string
    {
        return $rootNamespace . '\Http\Resources';
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
            ['versions', 'vr', InputOption::VALUE_OPTIONAL, 'Select the version of the resource'],
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

        $rootNamespace = trim($this->laravel->getNamespace(), '\\');
        $this->versionedClassList[] = $this->getResourceNamespace($rootNamespace) . '\\' . $version . '\\' . $resource;
    }

    /**
     * Gather the desired Sail services using an interactive prompt.
     *
     * @return array<int, string>
     * @throws InvalidVersionException
     */
    private function gatherVersionsInteractively(): array
    {
        $versions = VersionHelper::getAvailableVersions();
        $defaultVersion = VersionHelper::getDefaultVersion();
        if (function_exists('\Laravel\Prompts\multiselect')) {
            return \Laravel\Prompts\multiselect(
                'Select required versions for the resource',
                $versions,
                [$defaultVersion],
            );
        }
        /** @var array<int, string> $selectedVersions */
        $selectedVersions = $this->choice('Select required versions for the resource', $versions, (string) array_search($defaultVersion, $versions), null, true);
        return $selectedVersions;
    }

    /**
     * Replace the version class list for the given stub.
     *
     * @param string $stub
     * @return $this
     */
    protected function replaceVersionClasses(string &$stub): self
    {
        $classListText = '/**';
        foreach ($this->versionedClassList as $versionedClass) {
            if (!str_starts_with($versionedClass, '\\')) {
                $versionedClass = '\\' . $versionedClass;
            }
            $classListText .= "\n * @see {$versionedClass}";
        }
        $classListText .= "\n */";
        $stub = str_replace(
            '{{ versionClasses }}',
            $classListText,
            $stub
        );

        return $this;
    }
}

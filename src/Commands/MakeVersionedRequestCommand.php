<?php

namespace Uttamrabadiya\ApiVersionManager\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Uttamrabadiya\ApiVersionManager\Traits\VersionResolver;

#[AsCommand(name: 'make:versioned:request')]
class MakeVersionedRequestCommand extends GeneratorCommand
{
    use VersionResolver;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:versioned-request';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new versioned form request class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Request';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        parent::handle();

        $versions = explode(',', $this->option('versions'));

        foreach ($versions as $version) {
            $this->createRequest($version);
        }
    }

    protected function getStub()
    {
        return __DIR__ . '/stubs/versioned_request.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Http\Requests\Versioned';
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
            ['versions', 'vr', InputOption::VALUE_OPTIONAL, 'Select the version of the resource'],
        ];
    }

    /**
     * Create a controller for the model.
     *
     * @return void
     */
    protected function createRequest($version)
    {
        $request = class_basename($this->argument('name'));

        $this->call('make:request', array_filter([
            'name' => "$version/$request",
            '--force' => $this->option('force'),
        ]));
    }
}

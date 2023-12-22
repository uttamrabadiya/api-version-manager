<?php

namespace UttamRabadiya\ApiVersionManager\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Console\Input\InputOption;
use UttamRabadiya\ApiVersionManager\Exceptions\InvalidDefaultVersionException;
use UttamRabadiya\ApiVersionManager\Traits\VersionResolver;

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
     * @throws InvalidDefaultVersionException|FileNotFoundException
     */
    public function handle()
    {
        parent::handle();

        $versions = $this->gatherVersionsInteractively();
        foreach ($versions as $version) {
            $this->createRequest($version);
        }

        return true;
    }

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/versioned_request.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Http\Requests\Versioned';
    }

    /**
     * Get the console command options.
     * @return array<mixed>
     */
    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the resource already exists'],
            ['versions', 'vr', InputOption::VALUE_OPTIONAL, 'Select the version of the resource'],
        ];
    }

    protected function createRequest(string $version): void
    {
        /** @var string $inputName */
        $inputName = $this->argument('name');
        $request = class_basename($inputName);

        $this->call('make:request', array_filter([
            'name' => "$version/$request",
            '--force' => $this->option('force'),
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
                'Select required versions for the request',
                $versions,
                [$defaultVersion],
            );
        }

        return $this->choice('Select required versions for the request', $versions, array_search($defaultVersion, $versions), null, true);
    }
}

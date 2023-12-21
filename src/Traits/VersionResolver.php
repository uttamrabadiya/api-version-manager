<?php

namespace Uttamrabadiya\ApiVersionManager\Traits;

use Uttamrabadiya\LaravelApiVersionManager\Exceptions\InvalidEntityException;
use Uttamrabadiya\LaravelApiVersionManager\Exceptions\EntityClassNotFoundException;

trait VersionResolver
{
    private static function supportedEntities(): array
    {
        return [
            'Requests',
            'Resources',
        ];
    }

    /**
     * @throws InvalidEntityException
     * @throws EntityClassNotFoundException
     */
    private static function resolveClassName(): string
    {
        if (! defined('self::RESOLVER_ENTITY')) {
            throw new InvalidEntityException('Define resolver entity constant (RESOLVER_ENTITY) while using VersionResolver Trait');
        }

        $entity = self::RESOLVER_ENTITY;
        if (! in_array($entity, self::supportedEntities())) {
            throw new InvalidEntityException(sprintf('Unsupported resolver entity: %s', $entity));
        }

        $className = class_basename(get_called_class());

        $version = self::getVersion();
        $appNamespace = config('api-version-manager.app_namespace');

        if (! class_exists($entityClass = sprintf("%s\\%s\\%s\\%s", $appNamespace, $entity, $version, $className))) {
            throw new EntityClassNotFoundException(sprintf('Class %s not found', $appNamespace));
        }

        return $entityClass;
    }

    private static function resolveClass(): mixed
    {
        $className = self::resolveClassName();

        return app()->make($className);
    }

    private static function getVersion(): string
    {
        $defaultVersion = self::getLatestVersion();

        $requestPath = request()->path();
        preg_match('/api\/(?<version>.*?)\//', $requestPath, $requestVersion);
        $version = $requestVersion['version'] ?? null;

        $supportedVersion = config('api-version-manager.versions');
        if (is_null($version)) {
            //TODO: Might want to log when version not found.
            $version = $defaultVersion;
        } elseif (! in_array($version, $supportedVersion)) {
            //TODO: Might want to log when version is not supported.
            $version = $defaultVersion;
        }

        return strtoupper($version);
    }

    private static function getAvailableVersions(): array
    {

        $versions = config('api-version-manager.versions');

        return array_map('strtoupper', $versions);
    }

    private static function getLatestVersion(): string
    {

        return strtoupper(config('api-version-manager.version'));
    }
}

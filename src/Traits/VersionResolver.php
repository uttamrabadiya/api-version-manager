<?php

namespace UttamRabadiya\ApiVersionManager\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use UttamRabadiya\ApiVersionManager\Exceptions\InvalidDefaultVersionException;
use UttamRabadiya\ApiVersionManager\Exceptions\InvalidEntityException;
use UttamRabadiya\ApiVersionManager\Exceptions\EntityClassNotFoundException;

trait VersionResolver
{
    /**
     * @throws InvalidDefaultVersionException
     * @throws InvalidEntityException
     * @throws EntityClassNotFoundException
     */
    public static function resolveClassName(): string
    {
        if (! defined('self::RESOLVER_ENTITY')) {
            throw new InvalidEntityException('Define resolver entity constant (RESOLVER_ENTITY) while using VersionResolver Trait');
        }

        $entity = self::RESOLVER_ENTITY;
        if (! in_array($entity, self::supportedEntities())) {
            throw new InvalidEntityException(sprintf('Unsupported resolver entity: %s', $entity));
        }

        return self::resolveEntityClass($entity);
    }

    /**
     * @return mixed
     * @throws InvalidDefaultVersionException
     * @throws InvalidEntityException
     * @throws EntityClassNotFoundException
     * @throws BindingResolutionException
     */
    public static function resolveClass()
    {
        $className = self::resolveClassName();

        return app()->make($className);
    }

    private static function supportedEntities(): array
    {
        return [
            'Requests',
            'Resources',
        ];
    }

    /**
     * @throws InvalidDefaultVersionException
     */
    private static function getVersion(): string
    {
        $defaultVersion = self::getDefaultVersion();

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

    /**
     * @throws InvalidDefaultVersionException
     */
    private static function getDefaultVersion(): string
    {
        $defaultVersion = strtoupper(config('api-version-manager.version'));
        $versions = self::getAvailableVersions();
        if (!\in_array($defaultVersion, $versions)) {
            throw new InvalidDefaultVersionException(sprintf('Default version %s is not supported by versions [%s]', $defaultVersion, implode(',', $versions)));
        }
        return $defaultVersion;
    }

    /**
     * @throws InvalidDefaultVersionException
     * @throws EntityClassNotFoundException
     */
    private static function resolveEntityClass(string $entity): string
    {
        $version = self::getVersion();
        $appNamespace = config('api-version-manager.app_http_namespace');
        $useFallbackEntity = config('api-version-manager.use_fallback_entity');
        $className = class_basename(get_called_class());

        $entityClass = sprintf("%s\\%s\\%s\\%s", $appNamespace, $entity, $version, $className);

        if (! class_exists($entityClass) && $useFallbackEntity === true) {
            $availableVersions = self::getAvailableVersions();
            foreach($availableVersions as $availableVersion) {
                $entityClass = sprintf("%s\\%s\\%s\\%s", $appNamespace, $entity, $availableVersion, $className);
                if (class_exists($entityClass)) {
                    break;
                }
            }
        }

        if (! class_exists($entityClass)) {
            throw new EntityClassNotFoundException(sprintf('Class %s not found', $entityClass));
        }

        return $entityClass;
    }
}

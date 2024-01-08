<?php

namespace UttamRabadiya\ApiVersionManager\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use UttamRabadiya\ApiVersionManager\Exceptions\InvalidVersionException;
use UttamRabadiya\ApiVersionManager\Exceptions\InvalidEntityException;
use UttamRabadiya\ApiVersionManager\Exceptions\EntityClassNotFoundException;
use UttamRabadiya\ApiVersionManager\Helpers\VersionHelper;

trait VersionResolver
{
    /**
     * @throws InvalidVersionException
     * @throws InvalidEntityException
     * @throws EntityClassNotFoundException
     */
    public static function resolveClassName(): string
    {
        if (! defined('self::RESOLVER_ENTITY')) {
            throw new InvalidEntityException('Define resolver entity constant (RESOLVER_ENTITY) while using VersionResolver Trait');
        }

        $entity = self::RESOLVER_ENTITY;
        if (! in_array($entity, ['Requests', 'Resources'])) {
            throw new InvalidEntityException(sprintf('Unsupported resolver entity: %s', $entity));
        }

        return self::resolveEntityClass($entity);
    }

    /**
     * @return mixed
     * @throws InvalidVersionException
     * @throws InvalidEntityException
     * @throws EntityClassNotFoundException
     * @throws BindingResolutionException
     */
    public static function resolveClass()
    {
        $className = self::resolveClassName();

        return app()->make($className);
    }

    /**
     * @throws InvalidVersionException
     * @throws \Exception
     */
    private static function getVersion(): string
    {
        $defaultVersion = VersionHelper::getDefaultVersion();

        $request = request();
        if (!$request instanceof Request) {
            throw new \Exception('Request is not available to resolve version.');
        }
        $requestPath = $request->path();
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

    /**
     * @throws InvalidVersionException
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
            $availableVersions = VersionHelper::getAvailableVersions();
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

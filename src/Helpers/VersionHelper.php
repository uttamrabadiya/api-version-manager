<?php

namespace UttamRabadiya\ApiVersionManager\Helpers;

use UttamRabadiya\ApiVersionManager\Exceptions\InvalidVersionException;

class VersionHelper
{
    /**
     * @param array<int, string> $versions
     * @throws InvalidVersionException
     */
    public static function validateVersions(array $versions): void
    {
        $availableVersions = self::getAvailableVersions();
        foreach($versions as $version) {
            if (!in_array(strtoupper($version), $availableVersions)) {
                throw new InvalidVersionException(sprintf('Version %s is not supported by versions [%s]', $version, implode(',', $availableVersions)));
            }
        }
    }

    /**
     * @return array<int, string>
     */
    public static function getAvailableVersions(): array
    {
        $versions = config('api-version-manager.versions');
        return array_map('strtoupper', $versions);
    }

    /**
     * @throws InvalidVersionException
     */
    public static function getDefaultVersion(): string
    {
        $defaultVersion = strtoupper(config('api-version-manager.version'));
        $versions = self::getAvailableVersions();
        if (!\in_array($defaultVersion, $versions)) {
            throw new InvalidVersionException(sprintf('Default version %s is not supported by versions [%s]', $defaultVersion, implode(',', $versions)));
        }
        return $defaultVersion;
    }
}
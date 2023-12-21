<?php

namespace Uttamrabadiya\LaravelApiVersionManager\Http\Resources;

use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Uttamrabadiya\ApiVersionManager\Traits\VersionResolver;

abstract class BaseResource
{
    use VersionResolver;

    const RESOLVER_ENTITY = 'Resources';

    /**
     * @throws Exception
     */
    public static function item(mixed $data): JsonResource
    {
        $resource = self::resolveClassName();

        return new $resource($data);
    }

    /**
     * @throws Exception
     */
    public static function collection(mixed $data): AnonymousResourceCollection
    {
        $resource = self::resolveClassName();

        return $resource::collection($data);
    }
}

<?php

namespace UttamRabadiya\ApiVersionManager\Http\Resources;

use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use UttamRabadiya\ApiVersionManager\Traits\VersionResolver;

abstract class BaseResource
{
    use VersionResolver;

    const RESOLVER_ENTITY = 'Resources';

    /**
     * @param mixed $data
     * @throws Exception
     */
    public static function item($data): JsonResource
    {
        $resource = self::resolveClassName();

        return new $resource($data);
    }

    /**
     * @param mixed $data
     * @throws Exception
     */
    public static function collection($data): AnonymousResourceCollection
    {
        $resource = self::resolveClassName();

        return $resource::collection($data);
    }
}

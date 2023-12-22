<?php

namespace UttamRabadiya\ApiVersionManager\Http\Requests;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Http\FormRequest;
use UttamRabadiya\ApiVersionManager\Exceptions\InvalidDefaultVersionException;
use UttamRabadiya\ApiVersionManager\Traits\VersionResolver;
use UttamRabadiya\ApiVersionManager\Exceptions\EntityClassNotFoundException;
use UttamRabadiya\ApiVersionManager\Exceptions\InvalidEntityException;

abstract class BaseRequest extends FormRequest
{
    use VersionResolver;

    const RESOLVER_ENTITY = 'Requests';

    /**
     * Determine if the user is authorized to make this request.
     * @throws InvalidDefaultVersionException
     * @throws InvalidEntityException
     * @throws EntityClassNotFoundException
     * @throws BindingResolutionException
     */
    public function authorize(): bool
    {
        /* @var $resource self */
        $resource = self::resolveClass();

        return $resource->authorize();
    }

    /**
     * Get the validation rules that apply to the request.
     * @throws InvalidDefaultVersionException
     * @throws InvalidEntityException
     * @throws EntityClassNotFoundException
     * @throws BindingResolutionException
     */
    public function rules(): array
    {
        /* @var $resource self */
        $resource = self::resolveClass();

        return $resource->rules();
    }
}

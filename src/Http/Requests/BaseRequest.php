<?php

namespace Uttamrabadiya\LaravelApiVersionManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Uttamrabadiya\ApiVersionManager\Traits\VersionResolver;

abstract class BaseRequest extends FormRequest
{
    use VersionResolver;

    const RESOLVER_ENTITY = 'Requests';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /* @var $resource BaseRequest */
        $resource = self::resolveClass();

        return $resource->authorize();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        /* @var $resource BaseRequest */
        $resource = self::resolveClass();

        return $resource->rules();
    }
}

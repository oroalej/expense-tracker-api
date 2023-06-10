<?php

namespace App\Http\Requests\Store;

use App\Http\Requests\CustomRequest;

/**
 * @property string $name
 * @property string $description
 */
class StoreTagRequest extends CustomRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:191',
            'description' => 'max:191',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'description' => 'Description',
        ];
    }
}

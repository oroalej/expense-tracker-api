<?php

namespace App\Http\Requests;

use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

/**
 * @property string $name
 * @property string $description
 * @property-read Tag $tag;
 */
class UpdateTagRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Gate::allows('update', $this->tag);
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

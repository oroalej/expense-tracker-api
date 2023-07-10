<?php

namespace App\Http\Requests\Category;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

/**
 * @property-read Category $category
 */
class CategoryActionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Gate::allows('update', $this->category);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'is_visible'    => 'nullable|boolean',
            'is_budgetable' => 'nullable|boolean',
            'is_reportable' => 'nullable|boolean',
        ];
    }
}

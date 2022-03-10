<?php

namespace App\Http\Requests;

use App\Enums\CategoryTypeState;
use App\Models\Category;
use App\Rules\IsEntryExist;
use App\Rules\IsSameCategoryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * @property int    $parent_id
 * @property string $name
 * @property string $description
 * @property int    $category_type
 */
class StoreCategoryRequest extends FormRequest
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
			'name'          => 'required|max:191',
			'description'   => 'nullable|max:191',
			'category_type' => [
				'required',
				new Enum( CategoryTypeState::class )
			],
			'parent_id'     => [
				'nullable',
				new IsEntryExist( Category::class ),
				new IsSameCategoryType( $this->get( 'category_type' ) )
			],
		];
	}

	public function attributes(): array
	{
		return [
			'name'          => 'Name',
			'description'   => 'Description',
			'parent_id'     => 'Parent',
			'category_type' => 'Type',
		];
	}
}

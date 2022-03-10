<?php

namespace Database\Seeders;

use App\Enums\FeatureState;
use App\Models\Category;
use App\Models\CategoryScope;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run(): void
	{
		//
	}

	public function dataCategories( array $categories, int $type, $parent = null ): void
	{
		foreach ( $categories as $data ) {
			if ( is_array( $data ) ) {
				$attributes = array_intersect_key( $data, array_flip( [ 'name' ] ) );
			} else {
				$attributes['name'] = $data;
			}

			if ( $parent ) {
				$attributes['parent_id'] = $parent->id;
			}

			$attributes['term_taxonomy_id'] = $type;
			$lastInsertedCategory           = $this->insertData( $attributes );

			if ( ! empty( $data['children'] ) ) {
				$this->dataCategories( $data['children'], $type, $lastInsertedCategory );
			}
		}
	}

	public function insertData( array $attributes ): Category
	{
		$category = new Category( [
			'name' => $attributes['name'],
		] );

		$category->type()->associate( $attributes['term_taxonomy_id'] );

		if ( isset( $attributes['parent_id'] ) ) {
			$category->parent()->associate( $attributes['parent_id'] );
		}

		$category->save();

		$categoryScope = new CategoryScope();
		$categoryScope->category()->associate( $category );
		$categoryScope->type()->associate( FeatureState::Transaction->value );

		$categoryScope->save();

		return $category;
	}
}

<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Tag;
use App\Policies\CategoryPolicy;
use App\Policies\TagPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
	/**
	 * The policy mappings for the application.
	 *
	 * @var array<class-string, class-string>
	 */
	protected $policies = [
		Tag::class => TagPolicy::class,
		Category::class => CategoryPolicy::class,
	];

	/**
	 * Register any authentication / authorization services.
	 *
	 * @return void
	 */
	public function boot(): void
	{
		$this->registerPolicies();

		//
	}
}

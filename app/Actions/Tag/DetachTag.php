<?php

namespace App\Actions\Tag;

use App\Contracts\TaggableInterface;
use App\Models\Tag;

class DetachTag
{
	public function __construct(
		protected TaggableInterface $model,
		protected Tag $tag
	) {
	}

	public function execute(): void
	{
		$this->model->tags()->detach($this->tag);
	}
}

<?php

namespace App\Actions\Tag;

use App\DataObject\TagData;
use App\Models\Tag;

class UpdateTag
{
	public function __construct(
		protected Tag $tag,
		protected TagData $attributes
	) {
	}

	public function execute(): Tag
	{
		$this->tag->update([
			'name' => $this->attributes->name,
			'$this->description' => $this->attributes->description,
		]);

		return $this->tag->refresh();
	}
}

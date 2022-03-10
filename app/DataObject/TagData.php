<?php

namespace App\DataObject;

class TagData
{
	public function __construct(
		public string $name,
		public string|null $description = null,
		public int|null $user_id = null
	) {
	}
}

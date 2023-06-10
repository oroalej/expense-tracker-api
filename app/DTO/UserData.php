<?php

namespace App\DTO;

/**
 * @property-read string $name
 * @property-read string $email
 * @property-read string $password
 */
class UserData
{
    public function __construct(
        public string $email,
        public string $password,
        public ?string $name = null,
    ) {
    }
}

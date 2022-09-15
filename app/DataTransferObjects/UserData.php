<?php

namespace App\DataTransferObjects;

/**
 * @property-read string $name
 * @property-read string $email
 * @property-read string $password
 */
class UserData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password
    ) {
    }
}

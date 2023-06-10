<?php

namespace App\DTO\Auth;

class EmailAndPasswordDTO
{
    public function __construct(
        public string $email,
        public string $password
    ) {
    }
}

<?php
namespace App\DTOs;

class LoginDTO {
    public function __construct(
        public string $username,
        public string $password
    ) {}
}
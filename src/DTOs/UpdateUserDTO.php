<?php
namespace App\DTOs;

class UpdateUserDTO {
    public function __construct(
        public readonly int $id,
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?string $password = null
    ) {}
}
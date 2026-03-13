<?php
namespace App\Models;

class User {
    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $email = null,
        public ?string $password = null,
        public ?string $created_at = null
    ) {}
}
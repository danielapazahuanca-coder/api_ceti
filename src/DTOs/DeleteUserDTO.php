<?php
namespace App\DTOs;

class DeleteUserDTO {
    public function __construct(
        public readonly int $id
    ) {}
}
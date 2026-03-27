<?php
namespace App\Repositories\Interfaces;

use App\Models\User;

interface UserRepositoryInterface {
    public function save(User $user): User;         // F2
    public function findByEmail(string $email): ?User; 
    public function findById(int $id): ?User;       
    public function update(User $user): User;       
    public function delete(int $id): bool;         
}
<?php
namespace App\Services;

use App\DTOs\CreateUserDTO;
use App\DTOs\UpdateUserDTO;
use App\DTOs\DeleteUserDTO; 
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Exception;

class UserService {
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function register(CreateUserDTO $dto): User {
        
        if ($this->userRepository->findByEmail($dto->email)) {
            throw new Exception("El correo electrónico ya está registrado.");
        }

        // encriptar contras
        $hashedPassword = password_hash($dto->password, PASSWORD_DEFAULT);

        $user = new User(
            name: $dto->name,
            email: $dto->email,
            password: $hashedPassword
        );

        return $this->userRepository->save($user);
    }

    public function update(UpdateUserDTO $dto): User {
        // Verificar si el usuario existe
        $existingUser = $this->userRepository->findById($dto->id);
        if (!$existingUser) throw new Exception("Usuario no encontrado.");

        if ($dto->email !== null && $dto->email !== $existingUser->email) {
            if ($this->userRepository->findByEmail($dto->email)) {
                throw new Exception("El correo electrónico ya está en uso.");
            }
        }

        $user = new User(
            id: $dto->id,
            name: $dto->name ?? $existingUser->name,
            email: $dto->email ?? $existingUser->email,
            password: $dto->password ? password_hash($dto->password, PASSWORD_DEFAULT) : null
        );

        return $this->userRepository->update($user);
    }

    public function delete(DeleteUserDTO $dto): bool {
   
        if (!$this->userRepository->findById($dto->id)) {
            throw new Exception("Usuario no encontrado.");
        }
        return $this->userRepository->delete($dto->id);
    }

    public function getById(int $id): User {
        $user = $this->userRepository->findById($id);
        if (!$user) throw new Exception("Usuario no encontrado.");
        return $user;
    }
}
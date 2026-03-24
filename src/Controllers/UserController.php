<?php
namespace App\Controllers;

use App\DTOs\CreateUserDTO;
use App\Services\UserService;
use Exception;

class UserController {
    public function __construct(
        private UserService $userService
    ) {}

    public function store(array $requestData): array {
        try {
          
            if (empty($requestData['name']) || empty($requestData['email']) || empty($requestData['password'])) {
                throw new Exception("Todos los campos son requeridos.");
            }

          
            $dto = new CreateUserDTO(
                name: $requestData['name'],
                email: $requestData['email'],
                password: $requestData['password']
            );

           
            $user = $this->userService->register($dto);

            return [
                'status' => 'success',
                'message' => 'Usuario creado correctamente',
                'data' => ['id' => $user->id, 'email' => $user->email]
            ];

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function update(array $requestData, int $id): array {
        try {
            if (empty($requestData['name']) && empty($requestData['email'])) {
                throw new Exception("Al menos un campo es requerido para actualizar.");
            }

            $dto = new UpdateUserDTO(
                id: $id,
                name: $requestData['name'] ?? null,
                email: $requestData['email'] ?? null,
                password: $requestData['password'] ?? null
            );

            $user = $this->userService->update($dto);

            return [
                'status' => 'success',
                'message' => 'Usuario actualizado correctamente',
                'data' => ['id' => $user->id, 'email' => $user->email]
            ];

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    
    public function delete(int $id): array {
        try {
            $dto = new DeleteUserDTO(id: $id);
            $this->userService->delete($dto);

            return [
                'status' => 'success',
                'message' => 'Usuario eliminado correctamente'
            ];

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

   
    public function show(int $id): array {
        try {
            $user = $this->userService->getById($id);

            return [
                'status' => 'success',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at
                ]
            ];

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function index(): array {
        try {
            $users = $this->userService->getAll();

            return [
                'status' => 'success',
                'data' => array_map(fn($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at
                ], $users)
            ];

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
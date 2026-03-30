<?php
namespace App\Controllers;

use App\Services\UserService;
use App\DTOs\CreateUserDTO;
use App\DTOs\UpdateUserDTO;
use App\DTOs\DeleteUserDTO;
use Exception;

class UserController {
    // F3: Recibimos el Service por el constructor (Inyección de Dependencias)
    public function __construct(
        private UserService $userService
    ) {}

    public function store(array $requestData): array {
        try {
            // F3: Validación básica de entrada
            if (empty($requestData['name']) || empty($requestData['email']) || empty($requestData['password'])) {
                throw new Exception("Nombre, email y password son obligatorios.");
            }

            // F3: Empacamos los datos en el DTO
            $dto = new CreateUserDTO(
                name: $requestData['name'],
                email: $requestData['email'],
                password: $requestData['password']
            );

            // F3: Llamamos al motor (Fase 2)
            $user = $this->userService->register($dto);

            return [
                'status' => 'success',
                'message' => 'Usuario creado correctamente',
                'data' => ['id' => $user->id, 'email' => $user->email]
            ];
        } catch (Exception $e) {
            http_response_code(400);
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
            http_response_code(404);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function update(array $requestData, int $id): array {
        try {
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
                'data' => ['id' => $user->id]
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
}
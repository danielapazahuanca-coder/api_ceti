<?php
namespace App\Controllers;

use App\Services\AuthService;
use App\DTOs\LoginDTO;
use Exception;

class AuthController {
    public function __construct(private AuthService $authService) {}

    public function login(array $data): array {
        try {
            if (empty($data['username']) || empty($data['password'])) {
                throw new Exception("El usuario y la contraseña son requeridos.");
            }

            $dto = new LoginDTO(
                username: trim($data['username']),
                password: $data['password']
            );

            $result = $this->authService->login($dto);

            return [
                'status' => 'success',
                'message' => 'Autenticación exitosa',
                'user' => $result
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    public function indexUsers(): array {
        try {
            $usuarios = $this->authService->listarUsuarios();
            return ['status' => 'success', 'data' => $usuarios];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updateForm(array $data, int $id): array {
        try {
            if(empty($data['username']) || empty($data['name'])) {
                throw new Exception("El nombre de usuario y nombre real son obligatorios.");
            }
            $res = $this->authService->modificarUsuario($id, $data);
            return ['status' => 'success', 'message' => $res['message']];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deleteAccess(int $id): array {
        try {
            $this->authService->eliminarAccesoAcademico($id);
            return ['status' => 'success', 'message' => 'Acceso académico removido con éxito'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function createUser(array $data): array {
        try {
            if (empty($data['username']) || empty($data['password']) || empty($data['name'])) {
                throw new Exception("El usuario, contraseña y nombre real son obligatorios.");
            }
            $res = $this->authService->registrarUsuario($data);
            return ['status' => 'success', 'message' => $res['message']];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function destroyUser(int $id): array {
        try {
            $this->authService->eliminarUsuarioCompleto($id);
            return ['status' => 'success', 'message' => 'Usuario eliminado.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

}
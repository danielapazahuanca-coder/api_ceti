<?php
namespace App\Services;

use App\Repositories\UserRepository;
use App\DTOs\LoginDTO;
use Exception;

class AuthService {
    public function __construct(private UserRepository $userRepository) {}

    public function login(LoginDTO $dto): array {
        // Buscamos al usuario limpiando espacios en el username
        $user = $this->userRepository->findByUsername(trim($dto->username));

        if (!$user) {
            throw new Exception("Credenciales incorrectas o el usuario no existe.");
        }

        $authenticated = false;
        $needsRehash = false;

        // Limpieza estricta de strings para evitar fallos de entrada
        $inputPassword = $dto->password;
        $storedPassword = trim($user->password);

        // 1. Intentar validar con Bcrypt (Contraseñas nuevas seguras)
        if (password_verify($inputPassword, $storedPassword)) {
            $authenticated = true;
        } 
        // 2. Intentar validar con MD5 ignorando diferencias entre mayúsculas y minúsculas (Contraseñas antiguas)
        elseif (strcasecmp($storedPassword, md5($inputPassword)) === 0) {
            $authenticated = true;
            $needsRehash = true; 
        }

        if (!$authenticated) {
            throw new Exception("Usuario o Contraseña inválidos.");
        }

        // Si es correcto y era MD5, migramos automáticamente a Bcrypt de manera transparente
        if ($needsRehash) {
            $newSecurePassword = password_hash($inputPassword, PASSWORD_BCRYPT);
            $this->userRepository->updatePasswordAndLogin($user->id, $newSecurePassword);
        } else {
            $this->userRepository->updateLastLogin($user->id);
        }

        return [
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'emailid' => $user->emailid,
            'sucursal' => $user->sucursal_varchar,
            'role_id' => $user->role_id,
            'role_name' => $user->nombre_rol ?? 'Sin Rol Académico'
        ];
    }

    public function listarUsuarios(): array {
        $users = $this->userRepository->findAll();
        $result = [];
        foreach ($users as $u) {
            $result[] = [
                'id' => $u->id,
                'username' => $u->username,
                'name' => $u->name,
                'emailid' => $u->emailid,
                'lastlogin' => $u->lastlogin,
                'sucursal' => $u->sucursal_varchar,
                'role_id' => $u->role_id,
                'role_name' => $u->nombre_rol
            ];
        }
        return $result;
    }

    public function modificarUsuario(int $id, array $data): array {
        $user = $this->userRepository->findById($id);
        if (!$user) throw new \Exception("Usuario no encontrado.");

        $user->username = trim($data['username']);
        $user->name = trim($data['name']);
        $user->emailid = trim($data['emailid']);
        $user->sucursal_varchar = trim($data['sucursal']);
        $user->role_id = (!empty($data['role_id'])) ? (int)$data['role_id'] : null;

        $this->userRepository->update($user);
        return ['message' => 'Usuario actualizado correctamente'];
    }

    public function eliminarAccesoAcademico(int $id): void {
        $this->userRepository->removeRole($id);
    }

    public function registrarUsuario(array $data): array {
        // Verificar si el username ya está en uso
        $existe = $this->userRepository->findByUsername(trim($data['username']));
        if ($existe) {
            throw new \Exception("El nombre de usuario ya está registrado por otra persona.");
        }

        // Cifrar la contraseña con Bcrypt automáticamente
        $passwordSegura = password_hash($data['password'], PASSWORD_BCRYPT);
        $roleId = (!empty($data['role_id'])) ? (int)$data['role_id'] : null;

        $nuevoUsuario = new \App\Models\User(
            id: 0, // La base de datos asignará el ID por AUTO_INCREMENT
            username: trim($data['username']),
            password: $passwordSegura,
            name: trim($data['name']),
            emailid: trim($data['emailid']),
            lastlogin: '0000-00-00 00:00:00',
            sucursal_varchar: trim($data['sucursal']),
            role_id: $roleId
        );

        $this->userRepository->save($nuevoUsuario);
        return ['message' => 'Usuario creado exitosamente con hash seguro.'];
    }

    public function eliminarUsuarioCompleto(int $id): void {
        // Evitar eliminar al administrador principal
        if ($id === 1) {
            throw new \Exception("No es posible eliminar al Administrador principal del sistema.");
        }

        // Eliminación física directa sin filtros ni rodeos de contabilidad
        $this->userRepository->delete($id);
    }
}
<?php
namespace App\Repositories;

use App\Database\Database;
use App\Models\User;
use PDO;

class UserRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByUsername(string $username): ?User {
        $sql = "SELECT u.*, r.nombre_rol 
                FROM user u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE u.username = :username";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return new User(
            id: (int)$row['id'],
            username: $row['username'],
            password: $row['password'],
            name: $row['name'],
            emailid: $row['emailid'],
            lastlogin: $row['lastlogin'],
            sucursal_varchar: $row['sucursal_varchar'],
            role_id: $row['role_id'] ? (int)$row['role_id'] : null,
            nombre_rol: $row['nombre_rol'] ?? null
        );
    }

    public function updatePasswordAndLogin(int $userId, string $hashedPassword): void {
        $sql = "UPDATE user SET password = :password, lastlogin = :lastlogin WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':password' => $hashedPassword,
            ':lastlogin' => date('Y-m-d H:i:s'),
            ':id' => $userId
        ]);
    }

    public function updateLastLogin(int $userId): void {
        $sql = "UPDATE user SET lastlogin = :lastlogin WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':lastlogin' => date('Y-m-d H:i:s'),
            ':id' => $userId
        ]);
    }
    public function findAll(): array {
        $sql = "SELECT u.*, r.nombre_rol 
                FROM user u 
                LEFT JOIN roles r ON u.role_id = r.id 
                ORDER BY u.id DESC";
        $stmt = $this->db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $users = [];
        foreach ($data as $row) {
            $users[] = new User(
                id: (int)$row['id'],
                username: $row['username'],
                password: $row['password'],
                name: $row['name'],
                emailid: $row['emailid'],
                lastlogin: $row['lastlogin'],
                sucursal_varchar: $row['sucursal_varchar'],
                role_id: $row['role_id'] ? (int)$row['role_id'] : null,
                nombre_rol: $row['nombre_rol'] ?? 'Sin Rol Académico'
            );
        }
        return $users;
    }

    public function findById(int $id): ?User {
        $sql = "SELECT u.*, r.nombre_rol FROM user u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        return new User(
            id: (int)$row['id'],
            username: $row['username'],
            password: $row['password'],
            name: $row['name'],
            emailid: $row['emailid'],
            lastlogin: $row['lastlogin'],
            sucursal_varchar: $row['sucursal_varchar'],
            role_id: $row['role_id'] ? (int)$row['role_id'] : null,
            nombre_rol: $row['nombre_rol'] ?? null
        );
    }

    public function update(User $user): void {
        $sql = "UPDATE user SET 
                username = :username, 
                name = :name, 
                emailid = :emailid, 
                sucursal_varchar = :sucursal, 
                role_id = :role_id 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':username' => $user->username,
            ':name' => $user->name,
            ':emailid' => $user->emailid,
            ':sucursal' => $user->sucursal_varchar,
            ':role_id' => $user->role_id,
            ':id' => $user->id
        ]);
    }
    
    public function removeRole(int $id): void {
        $sql = "UPDATE user SET role_id = NULL WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    public function save(User $user): void {
        $sql = "INSERT INTO user (username, password, name, emailid, lastlogin, sucursal_varchar, role_id) 
                VALUES (:username, :password, :name, :emailid, '0000-00-00 00:00:00', :sucursal, :role_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':username' => $user->username,
            ':password' => $user->password, // Ya vendrá cifrada desde el servicio
            ':name' => $user->name,
            ':emailid' => $user->emailid,
            ':sucursal' => $user->sucursal_varchar,
            ':role_id' => $user->role_id
        ]);
    }

    public function delete(int $id): void {
        // Opción segura: Borrado físico si no tiene dependencias, de lo contrario puedes usar una desactivación.
        // Como tu tabla 'user' tiene restricciones cascade/set null, ejecutaremos un DELETE controlado.
        $sql = "DELETE FROM user WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }
}
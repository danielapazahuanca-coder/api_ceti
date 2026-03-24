<?php
namespace App\Repositories\Interfaces;

use App\Database\Database;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use PDO;

class UserRepository implements UserRepositoryInterface {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function save(User $user): User {
        $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
        $stmt = $this->db->prepare($sql);
        
        $stmt->execute([
            ':name' => $user->name,
            ':email' => $user->email,
            ':password' => $user->password
        ]);

        $user->id = (int) $this->db->lastInsertId();
        return $user;
    }

    public function findByEmail(string $email): ?User {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? new User(...$data) : null;
    }

       
    public function findById(int $id): ?User {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? new User(...$data) : null;
    }

    
    public function update(User $user): User {
        $sql = "UPDATE users SET 
                name = :name, 
                email = :email 
                WHERE id = :id";
        
        $params = [
            ':id' => $user->id,
            ':name' => $user->name,
            ':email' => $user->email
        ];

       
        if ($user->password !== null) {
            $sql = "UPDATE users SET 
                    name = :name, 
                    email = :email, 
                    password = :password 
                    WHERE id = :id";
            $params[':password'] = $user->password;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $user;
    }

   
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function findAll(): array {
        $stmt = $this->db->query("SELECT * FROM users");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => new User(...$row), $data);
    }
}
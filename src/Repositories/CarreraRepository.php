<?php
namespace App\Repositories;

use App\Database\Database;
use App\Models\Carrera;
use PDO;

class CarreraRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findAll(): array {
        $sql = "SELECT * FROM acad_carrera ORDER BY id_carrera DESC";
        $stmt = $this->db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $carreras = [];
        foreach ($data as $row) {
            $carreras[] = new Carrera(
                id_carrera: (int)$row['id_carrera'],
                nombre: $row['nombre'],
                estado: (int)$row['estado'],
                sucursal_varchar: $row['sucursal_varchar']
            );
        }
        return $carreras;
    }

    public function findById(int $id): ?Carrera {
        $sql = "SELECT * FROM acad_carrera WHERE id_carrera = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return new Carrera(
            id_carrera: (int)$row['id_carrera'],
            nombre: $row['nombre'],
            estado: (int)$row['estado'],
            sucursal_varchar: $row['sucursal_varchar']
        );
    }

    public function save(Carrera $carrera): void {
        $sql = "INSERT INTO acad_carrera (nombre, estado, sucursal_varchar) 
                VALUES (:nombre, :estado, :sucursal)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $carrera->nombre,
            ':estado' => $carrera->estado,
            ':sucursal' => $carrera->sucursal_varchar
        ]);
    }

    public function update(Carrera $carrera): void {
        $sql = "UPDATE acad_carrera SET 
                nombre = :nombre, 
                estado = :estado, 
                sucursal_varchar = :sucursal 
                WHERE id_carrera = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $carrera->nombre,
            ':estado' => $carrera->estado,
            ':sucursal' => $carrera->sucursal_varchar,
            ':id' => $carrera->id_carrera
        ]);
    }

    public function changeState(int $id, int $estado): void {
        $sql = "UPDATE acad_carrera SET estado = :estado WHERE id_carrera = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':estado' => $estado, ':id' => $id]);
    }
}
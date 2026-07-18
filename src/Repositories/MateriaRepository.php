<?php
namespace App\Repositories;

use App\Database\Database;
use App\Models\Materia;
use PDO;

class MateriaRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findAll(): array {
        $sql = "SELECT m.*, c.nombre as carrera_nombre, n.nombre as nivel_nombre 
                FROM acad_materia m
                INNER JOIN acad_carrera c ON m.id_carrera = c.id_carrera
                INNER JOIN acad_nivel n ON m.id_nivel = n.id_nivel
                ORDER BY m.id_materia DESC";
        $stmt = $this->db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $materias = [];
        foreach ($data as $row) {
            $materias[] = new Materia(
                id_materia: (int)$row['id_materia'],
                id_carrera: (int)$row['id_carrera'],
                id_nivel: (int)$row['id_nivel'],
                sigla: $row['sigla'],
                nombre: $row['nombre'],
                estado: (int)$row['estado'],
                carrera_nombre: $row['carrera_nombre'] ?? '',
                nivel_nombre: $row['nivel_nombre'] ?? ''
            );
        }
        return $materias;
    }

    public function save(Materia $materia): void {
        $sql = "INSERT INTO acad_materia (id_carrera, id_nivel, sigla, nombre, estado) 
                VALUES (:id_carrera, :id_nivel, :sigla, :nombre, :estado)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_carrera' => $materia->id_carrera,
            ':id_nivel' => $materia->id_nivel,
            ':sigla' => strtoupper($materia->sigla),
            ':nombre' => $materia->nombre,
            ':estado' => $materia->estado
        ]);
    }
    public function updateEstado(int $id_materia, int $nuevoEstado): void {
        $sql = "UPDATE acad_materia SET estado = :estado WHERE id_materia = :id_materia";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':estado' => $nuevoEstado,
            ':id_materia' => $id_materia
        ]);
    }

    public function update(int $id_materia, array $data): void {
        $sql = "UPDATE acad_materia 
                SET id_carrera = :id_carrera, id_nivel = :id_nivel, sigla = :sigla, nombre = :nombre, estado = :estado 
                WHERE id_materia = :id_materia";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_carrera' => (int)$data['id_carrera'],
            ':id_nivel' => (int)$data['id_nivel'],
            ':sigla' => strtoupper($data['sigla']),
            ':nombre' => $data['nombre'],
            ':estado' => (int)$data['estado'],
            ':id_materia' => $id_materia
        ]);
    }
}
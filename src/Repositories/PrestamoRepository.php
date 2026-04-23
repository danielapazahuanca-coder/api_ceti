<?php
namespace App\Repositories;

use App\Database\Database;
use App\Models\Prestamo;
use PDO;

class PrestamoRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function save(Prestamo $prestamo): Prestamo {
        $sql = "INSERT INTO prestamos (activo_id, cantidad, solicitante, documento_identidad, fecha_prestamo, estado) 
                VALUES (:activo_id, :cantidad, :solicitante, :documento, :fecha, :estado)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':activo_id' => $prestamo->activo_id,
            ':cantidad'  => $prestamo->cantidad,
            ':solicitante' => $prestamo->solicitante,
            ':documento' => $prestamo->documento_identidad,
            ':fecha'     => $prestamo->fecha_prestamo ?? date('Y-m-d H:i:s'),
            ':estado'    => 'Prestado'
        ]);
        $prestamo->id = (int) $this->db->lastInsertId();
        return $prestamo;
    }

    public function findAll(): array {
        $sql = "SELECT p.*, a.nombre as nombre_activo 
                FROM prestamos p 
                JOIN activos a ON p.activo_id = a.id 
                ORDER BY p.fecha_prestamo DESC";
        $stmt = $this->db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $prestamos = [];
        foreach ($data as $row) {
            $prestamos[] = new Prestamo(
                id: (int)$row['id'],
                activo_id: (int)$row['activo_id'],
                cantidad: (int)$row['cantidad'],
                solicitante: $row['solicitante'],
                documento_identidad: $row['documento_identidad'],
                fecha_prestamo: $row['fecha_prestamo'],
                fecha_entrega_real: $row['fecha_entrega_real'],
                fecha_devolucion: $row['fecha_devolucion'],
                estado: $row['estado'],
                nombre_activo: $row['nombre_activo']
            );
        }
        return $prestamos;
    }

    public function findById(int $id): ?Prestamo {
        $sql = "SELECT p.*, a.nombre as nombre_activo 
                FROM prestamos p 
                JOIN activos a ON p.activo_id = a.id 
                WHERE p.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        return new Prestamo(
            id: (int)$row['id'],
            activo_id: (int)$row['activo_id'],
            cantidad: (int)$row['cantidad'],
            solicitante: $row['solicitante'],
            documento_identidad: $row['documento_identidad'],
            fecha_prestamo: $row['fecha_prestamo'],
            fecha_entrega_real: $row['fecha_entrega_real'],
            fecha_devolucion: $row['fecha_devolucion'],
            estado: $row['estado'],
            nombre_activo: $row['nombre_activo']
        );
    }

    public function updateReturn(int $id): bool {
        $sql = "UPDATE prestamos SET 
                fecha_devolucion = :fecha, 
                estado = 'Devuelto' 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':fecha' => date('Y-m-d H:i:s'),
            ':id' => $id
        ]);
    }
}
<?php
namespace App\Repositories;

use App\Database\Database;
use App\Models\Prestamo;
use PDO;
use Exception;

class PrestamoRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function save(Prestamo $prestamo, array $items): int {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO prestamos (solicitante, documento_identidad, fecha_prestamo, estado) 
                    VALUES (:solicitante, :documento, :fecha, :estado)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':solicitante' => $prestamo->solicitante,
                ':documento' => $prestamo->documento_identidad,
                ':fecha'     => $prestamo->fecha_prestamo ?? date('Y-m-d H:i:s'),
                ':estado'    => 'Prestado'
            ]);
            
            $prestamoId = (int) $this->db->lastInsertId();

            $sqlDetalle = "INSERT INTO prestamos_detalles (prestamo_id, activo_id, cantidad) 
                           VALUES (:p_id, :a_id, :cant)";
            $stmtDetalle = $this->db->prepare($sqlDetalle);

            foreach ($items as $item) {
                $stmtDetalle->execute([
                    ':p_id' => $prestamoId,
                    ':a_id' => $item['activo_id'],
                    ':cant' => $item['cantidad']
                ]);
            }

            $this->db->commit();
            return $prestamoId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function findAll(): array {
        $sql = "SELECT p.*, 
                GROUP_CONCAT(CONCAT(pd.cantidad, 'x ', a.nombre) SEPARATOR ', ') as nombre_activo
                FROM prestamos p 
                JOIN prestamos_detalles pd ON p.id = pd.prestamo_id
                JOIN activos a ON pd.activo_id = a.id 
                GROUP BY p.id
                ORDER BY p.fecha_prestamo DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array {
        $sql = "SELECT p.*, 
                GROUP_CONCAT(CONCAT(pd.cantidad, 'x ', a.nombre) SEPARATOR ', ') as nombre_activo
                FROM prestamos p 
                JOIN prestamos_detalles pd ON p.id = pd.prestamo_id
                JOIN activos a ON pd.activo_id = a.id 
                WHERE p.id = :id
                GROUP BY p.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
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
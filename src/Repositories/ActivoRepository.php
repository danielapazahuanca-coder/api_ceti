<?php
namespace App\Repositories\Interfaces;

use App\Database\Database;
use App\Models\Activo;
use App\Repositories\Interfaces\ActivoRepositoryInterface; // <-- REVISA QUE DIGA INTERFACE AQUÍ
use PDO;

class ActivoRepository implements ActivoRepositoryInterface { // <-- AQUÍ TAMBIÉN
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function save(Activo $activo): Activo {
        $sql = "INSERT INTO activos (nombre, codigo_activo, estado_id, ubicacion, precio_compra, fecha_ingreso, responsable, foto_path, observaciones) 
                VALUES (:nombre, :codigo, :estado, :ubicacion, :precio, :fecha, :responsable, :foto, :obs)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre'      => $activo->nombre,
            ':codigo'      => $activo->codigo_activo,
            ':estado'      => $activo->estado_id,
            ':ubicacion'   => $activo->ubicacion,
            ':precio'      => $activo->precio_compra,
            ':fecha'       => $activo->fecha_ingreso,
            ':responsable' => $activo->responsable,
            ':foto'        => $activo->foto_path,
            ':obs'         => $activo->observaciones
        ]);

        $activo->id = (int) $this->db->lastInsertId();
        return $activo;
    }

    // Para que no te dé error por falta de métodos de la interface, añade estos vacíos por ahora:
    public function findById(int $id): ?Activo { 
        $stmt = $this->db->prepare("SELECT * FROM activos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? new Activo(...$data) : null;
    }
    public function findAll(): array { 
        $stmt = $this->db->query("SELECT * FROM activos");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => new Activo(...$row), $data);
    }
    public function update(Activo $activo): Activo { 
        $sql = "UPDATE activos SET 
                nombre = :nombre, 
                codigo_activo = :codigo, 
                estado_id = :estado, 
                ubicacion = :ubicacion, 
                precio_compra = :precio, 
                fecha_ingreso = :fecha, 
                responsable = :responsable, 
                foto_path = :foto, 
                observaciones = :obs 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $activo->id,
            ':nombre' => $activo->nombre,
            ':codigo' => $activo->codigo_activo,
            ':estado' => $activo->estado_id,
            ':ubicacion' => $activo->ubicacion,
            ':precio' => $activo->precio_compra,
            ':fecha' => $activo->fecha_ingreso,
            ':responsable' => $activo->responsable,
            ':foto' => $activo->foto_path,
            ':obs' => $activo->observaciones
        ]);
        return $activo;
    }
    public function delete(int $id): bool { 
        $stmt = $this->db->prepare("DELETE FROM activos WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
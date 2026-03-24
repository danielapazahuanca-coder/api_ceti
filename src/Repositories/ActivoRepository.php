<?php
namespace App\Repositories;

use App\Database\Database;
use App\Models\Activo;
use App\Repositories\Interfaces\ActivoRepositoryInterface;
use PDO;

class ActivoRepository implements ActivoRepositoryInterface {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function save(Activo $activo): Activo {
        $sql = "INSERT INTO activos (
                    nombre, 
                    codigo_activo, 
                    estado_id, 
                    ubicacion, 
                    precio_compra, 
                    responsable, 
                    foto_path, 
                    observaciones, 
                    fecha_registro
                ) VALUES (
                    :nombre, 
                    :codigo, 
                    :estado, 
                    :ubicacion, 
                    :precio, 
                    :resp, 
                    :foto, 
                    :obs, 
                    :fecha_reg
                )";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->execute([
            ':nombre'    => $activo->nombre,
            ':codigo'    => $activo->codigo_activo,
            ':estado'    => $activo->estado_id,
            ':ubicacion' => $activo->ubicacion,
            ':precio'    => $activo->precio_compra,
            ':resp'      => $activo->responsable,
            ':foto'      => $activo->foto_path,
            ':obs'       => $activo->observaciones,
            ':fecha_reg' => $activo->fecha_registro // Captura el valor manual del modelo
        ]);

        $activo->id = (int) $this->db->lastInsertId();
        return $activo;
    }

    public function findById(int $id): ?Activo {
        $stmt = $this->db->prepare("SELECT * FROM activos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // El operador (...) reparte los datos del array en el constructor del Modelo
        return $data ? new Activo(...$data) : null;
    }

    public function findAll(): array {
        $stmt = $this->db->query("SELECT * FROM activos");
        return $stmt->fetchAll(PDO::FETCH_CLASS, Activo::class);
    }

    public function update(Activo $activo): Activo {
        // Se implementará en la Fase 3 según cronograma
        return $activo;
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM activos WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
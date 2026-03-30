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
            ':fecha_reg' => $activo->fecha_registro 
        ]);

        $activo->id = (int) $this->db->lastInsertId();
        return $activo;
    }

    public function findById(int $id): ?Activo {
        $stmt = $this->db->prepare("SELECT * FROM activos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$row) return null;

        return new Activo(
            id:             (int)$row['id'],
            nombre:         $row['nombre'],
            codigo_activo:  $row['codigo_activo'],
            estado_id:      (int)$row['estado_id'],
            ubicacion:      $row['ubicacion'],
            precio_compra:  (float)$row['precio_compra'],
            responsable:    $row['responsable'],
            foto_path:      $row['foto_path'],
            observaciones:  $row['observaciones'],
            fecha_registro: $row['fecha_registro']
        );
    }

    public function findAll(): array {
        $stmt = $this->db->query("SELECT * FROM activos");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC); // Traemos como array simple
        $activos = [];
        foreach ($data as $row) {
            $activos[] = new Activo(
                id:             (int)$row['id'],
                nombre:         $row['nombre'],
                codigo_activo:  $row['codigo_activo'],
                estado_id:      (int)$row['estado_id'],
                ubicacion:      $row['ubicacion'],
                precio_compra:  (float)$row['precio_compra'],
                responsable:    $row['responsable'],
                foto_path:      $row['foto_path'],
                observaciones:  $row['observaciones'],
                fecha_registro: $row['fecha_registro']
            );
        }
        return $activos;
    }

    public function update(Activo $activo): Activo {
        $sql = "UPDATE activos SET
        nombre = :nombre,
        codigo_activo = :codigo,
        estado_id = :estado,
        ubicacion = :ubicacion,
        precio_compra = :precio,
        responsable = :resp,
        observaciones = :obs
        WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $activo->nombre,
            ':codigo' => $activo->codigo_activo,
            ':estado' => $activo->estado_id,
            ':ubicacion' => $activo->ubicacion,
            ':precio' => $activo->precio_compra,
            ':resp' => $activo->responsable,
            ':obs' => $activo->observaciones,
            ':id' => $activo->id
        ]);
        return $activo;
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM activos WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
    public function findByCodigo(string $codigo): ?Activo {
        $stmt = $this->db->prepare("SELECT * FROM activos WHERE codigo_activo = :codigo");
        $stmt->execute([':codigo' => $codigo]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $data ? new Activo(...$data) : null;
    }
}
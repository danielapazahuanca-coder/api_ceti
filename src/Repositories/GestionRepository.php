<?php
namespace App\Repositories;

use App\Models\Gestion;
use PDO;

class GestionRepository {
    public function __construct(private PDO $db) {}

    public function findAll(): array {
        $sql = "SELECT * FROM gestion ORDER BY id_gestion DESC";
        $stmt = $this->db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $gestiones = [];
        foreach ($data as $row) {
            $gestiones[] = new Gestion(
                (int)$row['id_gestion'],
                $row['gestion_varchar'],
                (int)$row['estado_bt'],
                $row['sucursal_varchar']
            );
        }
        return $gestiones;
    }

    public function save(Gestion $gestion): void {
        // Si la nueva gestión se crea como Activa (1), primero desactivamos todas las demás de esa sucursal
        if ($gestion->estado_bt === 1) {
            $this->desactivarTodas($gestion->sucursal_varchar);
        }

        $sql = "INSERT INTO gestion (gestion_varchar, estado_bt, sucursal_varchar) 
                VALUES (:gestion, :estado, :sucursal)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':gestion' => $gestion->gestion_varchar,
            ':estado' => $gestion->estado_bt,
            ':sucursal' => $gestion->sucursal_varchar
        ]);
    }

    public function activar(int $id, string $sucursal): void {
        $this->desactivarTodas($sucursal);
        $sql = "UPDATE gestion SET estado_bt = 1 WHERE id_gestion = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    private function desactivarTodas(string $sucursal): void {
        $sql = "UPDATE gestion SET estado_bt = 0 WHERE sucursal_varchar = :sucursal";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':sucursal' => $sucursal]);
    }
}
<?php
namespace App\Services;

use App\Repositories\GestionRepository;
use App\Models\Gestion;
use Exception;

class GestionService {
    public function __construct(private GestionRepository $gestionRepository) {}

    public function obtenerGestiones(): array {
        $list = $this->gestionRepository->findAll();
        $res = [];
        foreach ($list as $g) {
            $res[] = [
                'id_gestion' => $g->id_gestion,
                'gestion_varchar' => $g->gestion_varchar,
                'estado_bt' => $g->estado_bt,
                'sucursal_varchar' => $g->sucursal_varchar
            ];
        }
        return $res;
    }

    public function registrarGestion(array $data): void {
        $gestionNom = trim($data['gestion_varchar']);
        $sucursal = trim($data['sucursal_varchar']);

        if (empty($gestionNom) || empty($sucursal)) {
            throw new Exception("El nombre de la gestión y la sucursal son obligatorios.");
        }

        // Toda nueva gestión entra desactivada (0) por defecto para que no altere nada por error
        $nueva = new Gestion(0, $gestionNom, 0, $sucursal);
        $this->gestionRepository->save($nueva);
    }

    public function establecerActiva(int $id, string $sucursal): void {
        $this->gestionRepository->activar($id, $sucursal);
    }
}
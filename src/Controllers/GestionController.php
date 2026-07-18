<?php
namespace App\Controllers;

use App\Services\GestionService;
use Exception;

class GestionController {
    public function __construct(private GestionService $gestionService) {}

    public function index(): array {
        return ['status' => 'success', 'data' => $this->gestionService->obtenerGestiones()];
    }

    public function create(array $data): array {
        try {
            $this->gestionService->registrarGestion($data);
            return ['status' => 'success', 'message' => 'Gestión académica creada correctamente.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function activate(int $id, array $data): array {
        try {
            if (empty($data['sucursal_varchar'])) {
                throw new Exception("Se requiere especificar la sucursal.");
            }
            $this->gestionService->establecerActiva($id, $data['sucursal_varchar']);
            return ['status' => 'success', 'message' => 'Gestión activada como el periodo actual de trabajo.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
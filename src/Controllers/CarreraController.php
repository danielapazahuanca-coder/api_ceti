<?php
namespace App\Controllers;

use App\Services\CarreraService;
use Exception;

class CarreraController {
    public function __construct(private CarreraService $carreraService) {}

    public function index(): array {
        try {
            $data = $this->carreraService->listarCarreras();
            return ['status' => 'success', 'data' => $data];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function store(array $data): array {
        try {
            $res = $this->carreraService->registrarCarrera($data);
            return ['status' => 'success', 'message' => $res['message']];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function update(array $data, int $id): array {
        try {
            $res = $this->carreraService->modificarCarrera($id, $data);
            return ['status' => 'success', 'message' => $res['message']];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function toggle(int $id, array $data): array {
        try {
            $estadoActual = isset($data['estado_actual']) ? (int)$data['estado_actual'] : 1;
            $res = $this->carreraService->toggleEstadoCarrera($id, $estadoActual);
            return ['status' => 'success', 'message' => $res['message']];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
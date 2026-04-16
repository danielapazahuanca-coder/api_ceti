<?php
namespace App\Controllers;
use App\Services\ActivoService;
use App\DTOs\CreateActivoDTO;
use Exception;

class ActivoController {
    public function __construct(private ActivoService $activoService) {}

    public function index(): array {
        try {
            $search = $_GET['buscar'] ?? null;
            $ubicacion = $_GET['ubicacion'] ?? null;
            $ver_sistema = (isset($_GET['papelera']) && $_GET['papelera'] == '1') ? 0 : 1;

            $activos = $this->activoService->listarTodo($search, $ubicacion, $ver_sistema);
            return ['status' => 'success', 'data' => $activos];
        } catch (Exception $e) { return ['status' => 'error', 'message' => $e->getMessage()]; }
    }

    public function store(array $data): array { /* Sin cambios */ }

    public function update(array $data, int $id): array {
        try {
            $activoActualizado = $this->activoService->actualizar($id, $data);
            return ['status' => 'success', 'message' => 'Activo actualizado', 'data' => $activoActualizado];
        } catch (Exception $e) { return ['status' => 'error', 'message' => $e->getMessage()]; }
    }

    public function destroy(int $id): array {
        try {
            $this->activoService->eliminar($id);
            return ['status' => 'success', 'message' => "Activo ocultado correctamente"];
        } catch (Exception $e) { return ['status' => 'error', 'message' => $e->getMessage()]; }
    }
}
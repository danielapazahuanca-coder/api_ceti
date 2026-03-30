<?php
namespace App\Controllers;

use App\Services\ActivoService;
use App\DTOs\CreateActivoDTO;
use Exception;

class ActivoController {
    public function __construct(
        private ActivoService $activoService
    ) {}

    /**
     * guardar un nuevo activo POST
     */
    public function store(array $data): array {
        try {
            $dto = new CreateActivoDTO(
                nombre:         $data['nombre'] ?? '',
                codigo_activo:  $data['codigo_activo'] ?? '',
                estado_id:      (int)($data['estado_id'] ?? 0),
                ubicacion:      $data['ubicacion'] ?? '',
                precio_compra:  (float)($data['precio_compra'] ?? 0),
                responsable:    $data['responsable'] ?? '',
                fecha_registro: $data['fecha_registro'] ?? date('Y-m-d'),
                foto_path:      $data['foto_path'] ?? null,
                observaciones:  $data['observaciones'] ?? null
            );

            $activo = $this->activoService->registrar($dto);

            return [
                'status'  => 'success',
                'message' => 'Activo registrado correctamente',
                'data'    => $activo
            ];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * listar todos GET
     */
    public function index(): array {
        try {
            $activos = $this->activoService->listarTodo();
            return [
                'status' => 'success',
                'data'   => $activos
            ];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * actualizar PUT
     */
    public function update(array $data, int $id): array {
        try {
            // Buscamos si existe
            $activoExistente = $this->activoService->obtenerPorId($id);
            
            if (!$activoExistente) {
                // Aquí te sugiero poner el ID en el mensaje para que sepas qué está buscando PHP
                return ['status' => 'error', 'message' => "Activo con ID $id no encontrado"];
            }

            $activoActualizado = $this->activoService->actualizar($id, $data);

            return [
                'status' => 'success',
                'message' => 'Activo actualizado correctamente',
                'data' => $activoActualizado
            ];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * eliminar DELETE
     */
    public function destroy(int $id): array {
        try {
            $this->activoService->eliminar($id);
            return [
                'status'  => 'success',
                'message' => "Activo con ID $id eliminado"
            ];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
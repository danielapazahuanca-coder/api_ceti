<?php
namespace App\Controllers;

use App\Services\PrestamoService;
use Exception;

class PrestamoController {
    public function __construct(private PrestamoService $prestamoService) {}

    public function index(): array {
        try {
            $data = $this->prestamoService->listarHistorial();
            return ['status' => 'success', 'data' => $data];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function store(array $data): array {
        try {
            // $data ahora incluirá 'cantidad' enviado desde el frontend
            $prestamo = $this->prestamoService->registrarPrestamo($data);
            return [
                'status' => 'success', 
                'message' => 'Préstamo registrado correctamente', 
                'data' => $prestamo
            ];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function return(int $id): array {
        try {
            $this->prestamoService->finalizarPrestamo($id);
            return ['status' => 'success', 'message' => 'Devolución registrada correctamente'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function show(int $id): array {
        try {
            $prestamo = $this->prestamoService->obtenerDetalle($id);
            return ['status' => 'success', 'data' => $prestamo];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
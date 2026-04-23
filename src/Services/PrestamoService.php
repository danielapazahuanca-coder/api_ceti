<?php
namespace App\Services;

use App\Repositories\PrestamoRepository;
use App\Repositories\ActivoRepository;
use App\Models\Prestamo;
use Exception;

class PrestamoService {
    public function __construct(
        private PrestamoRepository $prestamoRepository,
        private ActivoRepository $activoRepository
    ) {}

    public function listarHistorial(): array {
        return $this->prestamoRepository->findAll();
    }

    public function registrarPrestamo(array $data): Prestamo {
        // Validar que el activo existe
        $activo = $this->activoRepository->findById((int)$data['activo_id']);
        if (!$activo) {
            throw new Exception("El activo seleccionado no existe.");
        }

        // Se agrega la cantidad proveniente del array $data (por defecto 1 si no se envía)
        $prestamo = new Prestamo(
            activo_id: (int)$data['activo_id'],
            cantidad: (int)($data['cantidad'] ?? 1), 
            solicitante: $data['solicitante'],
            documento_identidad: $data['documento_identidad'],
            fecha_prestamo: date('Y-m-d H:i:s')
        );

        return $this->prestamoRepository->save($prestamo);
    }

    public function finalizarPrestamo(int $id): bool {
        $prestamo = $this->prestamoRepository->findById($id);
        if (!$prestamo) {
            throw new Exception("El registro de préstamo no existe.");
        }
        
        if ($prestamo->estado === 'Devuelto') {
            throw new Exception("Este préstamo ya fue marcado como devuelto.");
        }

        return $this->prestamoRepository->updateReturn($id);
    }

    public function obtenerDetalle(int $id): Prestamo {
        $prestamo = $this->prestamoRepository->findById($id);
        if (!$prestamo) throw new Exception("Préstamo no encontrado.");
        return $prestamo;
    }
}
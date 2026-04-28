<?php
namespace App\Services;

use App\Repositories\PrestamoRepository;
use App\Models\Prestamo;
use Exception;

class PrestamoService {
    public function __construct(
        private PrestamoRepository $prestamoRepository
    ) {}

    public function listarHistorial(): array {
        return $this->prestamoRepository->findAll();
    }

    public function registrarPrestamo(array $data): int {
        if (empty($data['items'])) {
            throw new Exception("Debe seleccionar al menos un activo.");
        }

        $prestamo = new Prestamo(
            solicitante: $data['solicitante'],
            documento_identidad: $data['documento_identidad'],
            fecha_prestamo: date('Y-m-d H:i:s')
        );

        return $this->prestamoRepository->save($prestamo, $data['items']);
    }

    public function finalizarPrestamo(int $id): bool {
        $prestamo = $this->prestamoRepository->findById($id);
        if (!$prestamo) throw new Exception("El registro de préstamo no existe.");
        if ($prestamo['estado'] === 'Devuelto') throw new Exception("Este préstamo ya fue devuelto.");

        return $this->prestamoRepository->updateReturn($id);
    }

    public function obtenerDetalle(int $id): array {
        $prestamo = $this->prestamoRepository->findById($id);
        if (!$prestamo) throw new Exception("Préstamo no encontrado.");
        return $prestamo;
    }
}
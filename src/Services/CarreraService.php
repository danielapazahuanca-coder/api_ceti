<?php
namespace App\Services;

use App\Repositories\CarreraRepository;
use App\Models\Carrera;
use Exception;

class CarreraService {
    public function __construct(private CarreraRepository $carreraRepository) {}

    public function listarCarreras(): array {
        $carreras = $this->carreraRepository->findAll();
        $result = [];
        foreach ($carreras as $c) {
            $result[] = [
                'id_carrera' => $c->id_carrera,
                'nombre' => $c->nombre,
                'estado' => $c->estado,
                'sucursal' => $c->sucursal_varchar
            ];
        }
        return $result;
    }

    public function registrarCarrera(array $data): array {
        if (empty($data['nombre']) || empty($data['sucursal_varchar'])) {
            throw new Exception("El nombre de la carrera y la sucursal son obligatorios.");
        }

        $carrera = new Carrera(
            nombre: trim($data['nombre']),
            estado: 1,
            sucursal_varchar: trim($data['sucursal_varchar'])
        );

        $this->carreraRepository->save($carrera);
        return ['message' => 'Carrera registrada con éxito.'];
    }

    public function modificarCarrera(int $id, array $data): array {
        $carrera = $this->carreraRepository->findById($id);
        if (!$carrera) throw new Exception("Carrera no encontrada.");

        if (empty($data['nombre']) || empty($data['sucursal_varchar'])) {
            throw new Exception("El nombre de la carrera y la sucursal son obligatorios.");
        }

        $carrera->nombre = trim($data['nombre']);
        $carrera->sucursal_varchar = trim($data['sucursal_varchar']);
        if (isset($data['estado'])) {
            $carrera->estado = (int)$data['estado'];
        }

        $this->carreraRepository->update($carrera);
        return ['message' => 'Carrera actualizada correctamente.'];
    }

    public function toggleEstadoCarrera(int $id, int $estadoActual): array {
        $nuevoEstado = ($estadoActual === 1) ? 0 : 1;
        $this->carreraRepository->changeState($id, $nuevoEstado);
        return ['message' => 'Estado de la carrera modificado con éxito.'];
    }
}
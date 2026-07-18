<?php
namespace App\Services;

use App\Repositories\MateriaRepository;
use App\Models\Materia;

class MateriaService {
    private MateriaRepository $repository;

    public function __construct(MateriaRepository $repository) {
        $this->repository = $repository;
    }

    public function listarMaterias(): array {
        $lista = $this->repository->findAll();
        $res = [];
        foreach ($lista as $m) {
            $res[] = [
                'id_materia' => $m->id_materia,
                'id_carrera' => $m->id_carrera,
                'id_nivel' => $m->id_nivel,
                'sigla' => $m->sigla,
                'nombre' => $m->nombre,
                'estado' => $m->estado,
                'carrera_nombre' => $m->carrera_nombre,
                'nivel_nombre' => $m->nivel_nombre
            ];
        }
        return $res;
    }

    public function registrarMateria(array $data): array {
        $materia = new Materia(
            id_carrera: (int)$data['id_carrera'],
            id_nivel: (int)$data['id_nivel'],
            sigla: trim($data['sigla']),
            nombre: trim($data['nombre']),
            estado: 1
        );
        $this->repository->save($materia);
        return ['message' => 'Materia registrada globalmente con éxito.'];
    }
    public function cambiarEstadoMateria(int $id_materia, array $data): array {
        // Si el estado actual es 1 pasa a 0, si es 0 pasa a 1
        $estadoActual = (int)($data['estado_actual'] ?? 1);
        $nuevoEstado = ($estadoActual === 1) ? 0 : 1;
        
        $this->repository->updateEstado($id_materia, $nuevoEstado);
        return ['message' => 'Estado de la materia actualizado con éxito.'];
    }

    public function actualizarMateria(int $id_materia, array $data): array {
        $this->repository->update($id_materia, $data);
        return ['message' => 'Materia actualizada de forma global con éxito.'];
    }
}
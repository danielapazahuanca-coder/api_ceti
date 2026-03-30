<?php
namespace App\Services;

use App\Repositories\Interfaces\ActivoRepositoryInterface;
use App\DTOs\CreateActivoDTO;
use App\DTOs\UpdateActivoDTO;
use App\Models\Activo;
use Exception;

class ActivoService {
    private $repository;

    public function __construct(ActivoRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    public function obtenerPorId(int $id): ?Activo {
        return $this->repository->findById($id);
    }

    public function registrar(CreateActivoDTO $dto): Activo {
        $existe = $this->repository->findByCodigo($dto->codigo_activo);
        if ($existe) {
            throw new Exception("El Codigo de activo '{$dto->codigo_activo}' ya esta registrado.");
        }
        if (empty($dto->codigo_activo)) {
            throw new Exception("El código del activo es obligatorio.");
        }
        if (empty($dto->nombre)){
            throw new Exception("Debe ingresar el nombre del activo.");
        }

        $nuevoActivo = new Activo(
            nombre: $dto->nombre,
            codigo_activo: $dto->codigo_activo,
            estado_id: $dto->estado_id,
            ubicacion: $dto->ubicacion,
            precio_compra: $dto->precio_compra,
            responsable: $dto->responsable,
            fecha_registro: $dto->fecha_registro,
            foto_path: $dto->foto_path,
            observaciones: $dto->observaciones
        );

        return $this->repository->save($nuevoActivo);
    }

    public function listarTodo(): array {
        return $this->repository->findAll();
    }
    
    public function actualizar(int $id, array $data): Activo {
        $activoExistente = $this->repository->findById($id);

        if(!$activoExistente) {
            throw new Exception("ERROR: No se encontro el activo con ID {$id}");
        }

        $activoExistente->nombre = $data['nombre'] ?? $activoExistente->nombre;
        $activoExistente->codigo_activo = $data['codigo_activo'] ?? $activoExistente->codigo_activo;
        $activoExistente->estado_id = $data['estado_id'] ?? $activoExistente->estado_id;
        $activoExistente->ubicacion = $data['ubicacion'] ?? $activoExistente->ubicacion;
        $activoExistente->precio_compra = $data['precio_compra'] ?? $activoExistente->precio_compra;
        $activoExistente->responsable = $data['responsable'] ?? $activoExistente->responsable;
        $activoExistente->observaciones = $data['observaciones'] ?? $activoExistente->observaciones;

        return $this->repository->update($activoExistente);
    }

    public function eliminar(int $id): bool {
        $existe = $this->repository->findById($id);
        if(!$existe){
            throw new Exception("ERROR: El activo que se intenta borrar no existe.");
        }
        return $this->repository->delete($id);
    }
}
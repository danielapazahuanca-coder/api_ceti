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

    public function registrar(CreateActivoDTO $dto): Activo {
        //Verificar si el cod ya existe
        $existe = $this->repository->findByCodigo($dto->codigo_activo);
        if ($existe) {
            throw new Exception("El Codigo de activo'{$dto->codigo_activo}' ya esta registrado.");
        }
        // Codigo no este vacio
        if (empty($dto->codigo_activo)) {
            throw new Exception("El código del activo es obligatorio.");
        }

        // No permitir nombres vacios
        if (empty($dto->nombre)){
            throw new Exception("Debe ingresar el nombre del activo.");
        }

        // Pasar los datos del dto al modelo para guardar
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
    
    public function actualizar(UpdateActivoDTO $dto): Activo{
        //Verificar si el activo existe en la BDD
        $activoExistente = $this->repository->findById($dto->id);

        if(!$activoExistente) {
            throw new Exception("ERROR: No se encontro el activo con ID {$dto->id}");
        }
        $activoExistente->nombre = $dto->nombre;
        $activoExistente->codigo_activo = $dto->codigo_activo;
        $activoExistente->estado_id = $dto->estado_id;
        $activoExistente->ubicacion = $dto->ubicacion;
        $activoExistente->precio_compra = $dto->precio_compra;
        $activoExistente->responsable = $dto->responsable;
        $activoExistente->observaciones = $dto->observaciones;
        //guardar los cambios
        return $this->repository->update($activoExistente);
    }
    public function eliminar(int $id): bool {
        //verificar q existe antes de borrar
        $existe = $this->repository->findById($id);
        if(!$existe){
            throw new Exception("ERROR: El activo que se intenta borar no existe.");
        }
        return $this->repository->delete($id);
    }
    
    
}
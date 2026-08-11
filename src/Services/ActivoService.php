<?php
namespace App\Services;
use App\Repositories\Interfaces\ActivoRepositoryInterface;
use App\DTOs\CreateActivoDTO;
use App\Models\Activo;
use Exception;

class ActivoService {
    private $repository;
    public function __construct(ActivoRepositoryInterface $repository) { $this->repository = $repository; }

    public function obtenerPorId(int $id): ?Activo { return $this->repository->findById($id); }

    public function registrar(CreateActivoDTO $dto): Activo {
        if ($this->repository->findByCodigo($dto->codigo_activo)) throw new Exception("El Codigo '{$dto->codigo_activo}' ya existe.");
        
        // CORRECCIÓN: Se añade fecha_compra al crear el objeto Activo
        $nuevoActivo = new Activo(
            nombre: $dto->nombre, 
            codigo_activo: $dto->codigo_activo, 
            estado_id: $dto->estado_id, 
            ubicacion: $dto->ubicacion, 
            precio_compra: $dto->precio_compra, 
            responsable: $dto->responsable, 
            fecha_registro: $dto->fecha_registro, 
            fecha_compra: $dto->fecha_compra, // <--- ESTO FALTABA
            foto_path: $dto->foto_path, 
            observaciones: $dto->observaciones, 
            activo_sistema: 1
        );
        
        return $this->repository->save($nuevoActivo);
    }

    public function listarTodo(?string $search = null, ?string $ubicacion = null, int $ver_sistema = 1): array {
        return $this->repository->findAll($search, $ubicacion, $ver_sistema);
    }
    
    public function actualizar(int $id, array $data): Activo {
        $activoExistente = $this->repository->findById($id);
        if(!$activoExistente) throw new Exception("Activo no encontrado.");

        $activoExistente->nombre = $data['nombre'] ?? $activoExistente->nombre;
        $activoExistente->codigo_activo = $data['codigo_activo'] ?? $activoExistente->codigo_activo;
        $activoExistente->estado_id = $data['estado_id'] ?? $activoExistente->estado_id;
        $activoExistente->ubicacion = $data['ubicacion'] ?? $activoExistente->ubicacion;
        $activoExistente->precio_compra = $data['precio_compra'] ?? $activoExistente->precio_compra;
        $activoExistente->responsable = $data['responsable'] ?? $activoExistente->responsable;
        // CORRECCIÓN: También permitimos actualizar la fecha de compra
        $activoExistente->fecha_compra = $data['fecha_compra'] ?? $activoExistente->fecha_compra; 
        $activoExistente->observaciones = $data['observaciones'] ?? $activoExistente->observaciones;
        $activoExistente->activo_sistema = $data['activo_sistema'] ?? $activoExistente->activo_sistema;

        return $this->repository->update($activoExistente);
    }

    public function eliminar(int $id): bool {
        if(!$this->repository->findById($id)) throw new Exception("El activo no existe.");
        return $this->repository->delete($id);
    }
}
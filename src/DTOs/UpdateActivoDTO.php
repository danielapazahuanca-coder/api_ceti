<?php
namespace App\DTOs;

class UpdateActivoDTO {
    public function __construct(
        public int $id, 
        public string $nombre,
        public string $codigo_activo,
        public int $estado_id,
        public string $ubicacion,
        public float $precio_compra,
        public string $responsable,
        public string $fecha_registro,
        public ?string $foto_path = null,
        public ?string $observaciones = null
    ) {}
}
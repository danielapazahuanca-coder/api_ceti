<?php
namespace App\DTOs;

class UpdateActivoDTO {
    public function __construct(
        public readonly int $id,
        public readonly ?string $nombre = null,
        public readonly ?string $codigo_activo = null,
        public readonly ?int $estado_id = null,
        public readonly ?string $ubicacion = null,
        public readonly ?float $precio_compra = null,
        public readonly ?string $fecha_ingreso = null,
        public readonly ?string $responsable = null,
        public readonly ?string $foto_path = null,
        public readonly ?string $observaciones = null
    ) {}
}
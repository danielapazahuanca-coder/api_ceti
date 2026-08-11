<?php
namespace App\DTOs;

class CreateActivoDTO {
    public function __construct(
        public readonly string $nombre,
        public readonly string $codigo_activo,
        public readonly int $estado_id,
        public readonly string $ubicacion,
        public readonly float $precio_compra,
        public readonly string $responsable,
        public readonly string $fecha_registro,
        public readonly ?string $fecha_compra = null, // NUEVO CAMPO
        public readonly ?string $foto_path = null,
        public readonly ?string $observaciones = null
    ) {}
}
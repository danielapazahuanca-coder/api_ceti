<?php
namespace App\Models;

class Activo {
    public function __construct(
        public ?int $id = null,
        public ?string $nombre = null,
        public ?string $codigo_activo = null,
        public ?int $estado_id = null,
        public ?string $ubicacion = null,
        public ?float $precio_compra = null,
        public ?string $fecha_ingreso = null,
        public ?string $responsable = null,
        public ?string $foto_path = null,
        public ?string $observaciones = null,
        public ?string $fecha_registro = null
    ) {}
}
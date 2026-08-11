<?php
namespace App\Models;

class Prestamo {
    public function __construct(
        public ?int $id = null,
        public int $activo_id = 0,
        public int $cantidad = 1, // Nuevo campo
        public string $solicitante = '',
        public string $documento_identidad = '',
        public ?string $fecha_prestamo = null,
        public ?string $fecha_entrega_real = null, // Nuevo campo
        public ?string $fecha_devolucion = null,
        public string $estado = 'Prestado',
        public ?string $nombre_activo = null 
    ) {}
}
<?php
namespace App\Models;

class Carrera {
    public function __construct(
        public ?int $id_carrera = null,
        public string $nombre = '',
        public int $estado = 1,
        public string $sucursal_varchar = ''
    ) {}
}
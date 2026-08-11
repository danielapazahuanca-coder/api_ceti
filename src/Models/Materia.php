<?php
namespace App\Models;

class Materia {
    public function __construct(
        public ?int $id_materia = null,
        public int $id_carrera = 0,
        public int $id_nivel = 0,
        public string $sigla = '',
        public string $nombre = '',
        public int $estado = 1,
        public string $carrera_nombre = '',
        public string $nivel_nombre = ''
    ) {}
}
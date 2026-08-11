<?php
namespace App\Models;

class Curso {
    public function __construct(
        public ?int $id_curso = null,
        public int $id_carrera = 0,
        public int $id_nivel = 0,
        public int $id_gestion = 0,
        public string $paralelo = '',
        public int $estado = 1,
        public string $carrera_nombre = '',
        public string $nivel_nombre = '',
        public string $gestion_varchar = '',
        public string $sucursal_varchar = ''
    ) {}
}
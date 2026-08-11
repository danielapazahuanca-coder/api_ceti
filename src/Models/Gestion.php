<?php
namespace App\Models;

class Gestion {
    public function __construct(
        public int $id_gestion,
        public string $gestion_varchar,
        public int $estado_bt,
        public string $sucursal_varchar
    ) {}
}
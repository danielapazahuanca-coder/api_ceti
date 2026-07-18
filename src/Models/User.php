<?php
namespace App\Models;

class User {
    public function __construct(
        public ?int $id = null,
        public string $username = '',
        public string $password = '',
        public string $name = '',
        public string $emailid = '',
        public ?string $lastlogin = null,
        public string $sucursal_varchar = '',
        public ?int $role_id = null,
        public ?string $nombre_rol = null
    ) {}
}
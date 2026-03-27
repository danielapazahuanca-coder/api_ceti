<?php
namespace App\Repositories\Interfaces;

use App\Models\Activo;

interface ActivoRepositoryInterface {
    public function findByCodigo(string $codigo): ?Activo;
    public function save(Activo $activo): Activo;
    public function findById(int $id): ?Activo;
    public function findAll(): array;
    public function update(Activo $activo): Activo;
    public function delete(int $id): bool;
}
<?php
namespace App\Database;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    // ================================================================
    // CONFIGURACIÓN DE BASE DE DATOS
    // Dejá SOLO UN bloque activo (sin comentar) según el entorno.
    // Al subir al hosting: comentar el bloque LOCAL y descomentar HOSTING.
    // ================================================================

    // ---- LOCAL (XAMPP) ----
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'cetilp';
    private const DB_USER = 'root';
    private const DB_PASS = '';

    // ---- HOSTING (descomentar y completar con los datos reales del hosting) ----
    // private const DB_HOST = 'localhost';
    // private const DB_NAME = 'ceetiico_XXXXX';   // nombre real de la BD en el hosting
    // private const DB_USER = 'ceetiico_XXXXX';   // usuario real de la BD en el hosting
    // private const DB_PASS = 'XXXXXXXXXX';       // password real de la BD en el hosting

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO(
                    "mysql:host=" . self::DB_HOST . ";dbname=" . self::DB_NAME . ";charset=utf8",
                    self::DB_USER,
                    self::DB_PASS,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}
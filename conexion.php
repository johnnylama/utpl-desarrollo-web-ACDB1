<?php
// Conexión PDO a MySQL. Lee variables de entorno para que funcione tanto en
// Docker (docker-compose) como en XAMPP local con valores por defecto.

$DB_HOST = getenv('DB_HOST') ?: 'db';
$DB_NAME = getenv('DB_NAME') ?: 'sistema_perfil';
$DB_USER = getenv('DB_USER') ?: 'app';
$DB_PASS = getenv('DB_PASS') ?: 'app_password';
$DB_PORT = getenv('DB_PORT') ?: '3306';

try {
    $dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Error de conexión a la base de datos: ' . htmlspecialchars($e->getMessage()));
}

// Configuración de sesión más segura (debe ir antes de session_start()).
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function usuario_autenticado(): bool {
    return isset($_SESSION['usuario_cedula']);
}

function requerir_login(): void {
    if (!usuario_autenticado()) {
        header('Location: login.php');
        exit;
    }
}

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

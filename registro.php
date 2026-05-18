<?php
require_once __DIR__ . '/conexion.php';

if (usuario_autenticado()) {
    header('Location: perfil.php');
    exit;
}

$errores = [];
$datos   = ['cedula' => '', 'nombre' => '', 'correo' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['cedula'] = trim($_POST['cedula'] ?? '');
    $datos['nombre'] = trim($_POST['nombre'] ?? '');
    $datos['correo'] = trim($_POST['correo'] ?? '');
    $password        = $_POST['password'] ?? '';
    $confirmar       = $_POST['confirmar'] ?? '';

    if ($datos['cedula'] === '' || !preg_match('/^\d{6,20}$/', $datos['cedula'])) {
        $errores[] = 'La cédula es obligatoria y debe contener solo dígitos (6 a 20).';
    }
    if ($datos['nombre'] === '') {
        $errores[] = 'El nombre es obligatorio.';
    }
    if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo no tiene un formato válido.';
    }
    if (strlen($password) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    }
    if ($password !== $confirmar) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    if (!$errores) {
        $stmt = $pdo->prepare('SELECT 1 FROM usuarios WHERE correo = :c OR cedula = :ced');
        $stmt->execute([':c' => $datos['correo'], ':ced' => $datos['cedula']]);
        if ($stmt->fetch()) {
            $errores[] = 'Ya existe un usuario con esa cédula o correo.';
        }
    }

    if (!$errores) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO usuarios (cedula, nombre, correo, password)
             VALUES (:cedula, :nombre, :correo, :password)'
        );
        $stmt->execute([
            ':cedula'   => $datos['cedula'],
            ':nombre'   => $datos['nombre'],
            ':correo'   => $datos['correo'],
            ':password' => $hash,
        ]);
        $_SESSION['flash'] = 'Registro exitoso. Ahora puedes iniciar sesión.';
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Sistema de Perfil</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="card">
    <h1>Crear cuenta</h1>

    <?php if ($errores): ?>
        <div class="alert error">
            <ul><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" novalidate>
        <label>Cédula
            <input type="text" name="cedula" value="<?= e($datos['cedula']) ?>" required>
        </label>
        <label>Nombre completo
            <input type="text" name="nombre" value="<?= e($datos['nombre']) ?>" required>
        </label>
        <label>Correo
            <input type="email" name="correo" value="<?= e($datos['correo']) ?>" required>
        </label>
        <label>Contraseña (mínimo 8 caracteres)
            <input type="password" name="password" required minlength="8">
        </label>
        <label>Confirmar contraseña
            <input type="password" name="confirmar" required minlength="8">
        </label>
        <button type="submit">Registrarme</button>
    </form>

    <p class="muted">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
</main>
</body>
</html>

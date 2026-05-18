<?php
require_once __DIR__ . '/conexion.php';
requerir_login();

$errores = [];
$exito   = null;

$stmt = $pdo->prepare('SELECT cedula, nombre, correo, fecha_registro FROM usuarios WHERE cedula = :c');
$stmt->execute([':c' => $_SESSION['usuario_cedula']]);
$usuario = $stmt->fetch();

if (!$usuario) {
    session_destroy();
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');

    if ($nombre === '') {
        $errores[] = 'El nombre no puede estar vacío.';
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo no tiene un formato válido.';
    }

    if (!$errores && $correo !== $usuario['correo']) {
        $check = $pdo->prepare('SELECT 1 FROM usuarios WHERE correo = :c AND cedula <> :ced');
        $check->execute([':c' => $correo, ':ced' => $usuario['cedula']]);
        if ($check->fetch()) {
            $errores[] = 'Ese correo ya está registrado por otro usuario.';
        }
    }

    if (!$errores) {
        $upd = $pdo->prepare('UPDATE usuarios SET nombre = :n, correo = :c WHERE cedula = :ced');
        $upd->execute([
            ':n'   => $nombre,
            ':c'   => $correo,
            ':ced' => $usuario['cedula'],
        ]);

        $_SESSION['usuario_nombre'] = $nombre;
        $_SESSION['usuario_correo'] = $correo;
        $usuario['nombre'] = $nombre;
        $usuario['correo'] = $correo;
        $exito = 'Datos actualizados correctamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi perfil</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <span>Hola, <strong><?= e($usuario['nombre']) ?></strong></span>
    <nav>
        <a href="cambiar_password.php">Cambiar contraseña</a>
        <a href="logout.php">Cerrar sesión</a>
    </nav>
</header>

<main class="card">
    <h1>Mi perfil</h1>

    <p class="muted">
        Cédula: <strong><?= e($usuario['cedula']) ?></strong><br>
        Registrado: <?= e($usuario['fecha_registro']) ?>
    </p>

    <?php if ($exito): ?>
        <div class="alert success"><?= e($exito) ?></div>
    <?php endif; ?>

    <?php if ($errores): ?>
        <div class="alert error">
            <ul><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" novalidate>
        <label>Nombre
            <input type="text" name="nombre" value="<?= e($usuario['nombre']) ?>" required>
        </label>
        <label>Correo
            <input type="email" name="correo" value="<?= e($usuario['correo']) ?>" required>
        </label>
        <button type="submit">Guardar cambios</button>
    </form>
</main>
</body>
</html>

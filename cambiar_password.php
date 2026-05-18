<?php
require_once __DIR__ . '/conexion.php';
requerir_login();

$errores = [];
$exito   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual     = $_POST['actual'] ?? '';
    $nueva      = $_POST['nueva'] ?? '';
    $confirmar  = $_POST['confirmar'] ?? '';

    if ($actual === '' || $nueva === '' || $confirmar === '') {
        $errores[] = 'Todos los campos son obligatorios.';
    }
    if (strlen($nueva) < 8) {
        $errores[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
    }
    if ($nueva !== $confirmar) {
        $errores[] = 'La nueva contraseña y su confirmación no coinciden.';
    }
    if ($nueva !== '' && $nueva === $actual) {
        $errores[] = 'La nueva contraseña debe ser diferente a la actual.';
    }

    if (!$errores) {
        $stmt = $pdo->prepare('SELECT password FROM usuarios WHERE cedula = :c');
        $stmt->execute([':c' => $_SESSION['usuario_cedula']]);
        $fila = $stmt->fetch();

        if (!$fila || !password_verify($actual, $fila['password'])) {
            $errores[] = 'La contraseña actual no es correcta.';
        } else {
            $nuevoHash = password_hash($nueva, PASSWORD_DEFAULT);
            $upd = $pdo->prepare('UPDATE usuarios SET password = :p WHERE cedula = :c');
            $upd->execute([
                ':p' => $nuevoHash,
                ':c' => $_SESSION['usuario_cedula'],
            ]);
            session_regenerate_id(true);
            $exito = 'Contraseña actualizada correctamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar contraseña</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <span>Hola, <strong><?= e($_SESSION['usuario_nombre']) ?></strong></span>
    <nav>
        <a href="perfil.php">Mi perfil</a>
        <a href="logout.php">Cerrar sesión</a>
    </nav>
</header>

<main class="card">
    <h1>Cambiar contraseña</h1>

    <?php if ($exito): ?>
        <div class="alert success"><?= e($exito) ?></div>
    <?php endif; ?>

    <?php if ($errores): ?>
        <div class="alert error">
            <ul><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" novalidate>
        <label>Contraseña actual
            <input type="password" name="actual" required>
        </label>
        <label>Nueva contraseña (mínimo 8 caracteres)
            <input type="password" name="nueva" required minlength="8">
        </label>
        <label>Confirmar nueva contraseña
            <input type="password" name="confirmar" required minlength="8">
        </label>
        <button type="submit">Actualizar contraseña</button>
    </form>
</main>
</body>
</html>

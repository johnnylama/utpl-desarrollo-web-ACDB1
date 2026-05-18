<?php
require_once __DIR__ . '/conexion.php';

if (usuario_autenticado()) {
    header('Location: perfil.php');
    exit;
}

$errores = [];
$correo  = '';
$flash   = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo   = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresa un correo válido.';
    }
    if ($password === '') {
        $errores[] = 'La contraseña es obligatoria.';
    }

    if (!$errores) {
        $stmt = $pdo->prepare('SELECT cedula, nombre, correo, password FROM usuarios WHERE correo = :c');
        $stmt->execute([':c' => $correo]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_cedula'] = $usuario['cedula'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_correo'] = $usuario['correo'];
            header('Location: perfil.php');
            exit;
        }
        $errores[] = 'Credenciales incorrectas.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="card">
    <h1>Iniciar sesión</h1>

    <?php if ($flash): ?>
        <div class="alert success"><?= e($flash) ?></div>
    <?php endif; ?>

    <?php if ($errores): ?>
        <div class="alert error">
            <ul><?php foreach ($errores as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" novalidate>
        <label>Correo
            <input type="email" name="correo" value="<?= e($correo) ?>" required autofocus>
        </label>
        <label>Contraseña
            <input type="password" name="password" required>
        </label>
        <button type="submit">Entrar</button>
    </form>

    <p class="muted">¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
</main>
</body>
</html>

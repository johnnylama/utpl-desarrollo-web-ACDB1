<?php
require_once __DIR__ . '/conexion.php';

header('Location: ' . (usuario_autenticado() ? 'perfil.php' : 'login.php'));
exit;

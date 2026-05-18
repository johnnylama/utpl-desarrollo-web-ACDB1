# Sistema de Perfil de Usuario y Cambio de Contraseña (PHP + MySQL)

Pequeña aplicación web que implementa autenticación, zona privada de perfil,
actualización de datos y cambio de contraseña seguro con `password_hash` y
`password_verify`. Desarrollada para la actividad **ACDB1-35%** de la asignatura
*Desarrollo Web* (UTPL).

## Funcionalidades

- Registro de usuario con validación de correo único y hash de contraseña.
- Login con verificación de credenciales y creación de sesión PHP.
- Zona privada (`perfil.php`) accesible solo con sesión activa.
- Actualización de nombre y correo con validación en servidor.
- Cambio de contraseña: verifica la actual, exige confirmación y almacena el
  nuevo hash.
- Logout que destruye la sesión y elimina la cookie.

## Estructura

```
sistema-perfil-usuario/
├── conexion.php         # PDO + helpers de sesión
├── database.sql         # Esquema y creación de la base
├── index.php            # Redirección según sesión
├── registro.php         # Alta de usuario
├── login.php            # Inicio de sesión
├── perfil.php           # Zona privada + actualización de datos
├── cambiar_password.php # Cambio de contraseña
├── logout.php           # Cierre de sesión
├── style.css
├── Dockerfile
└── docker-compose.yml
```

## Requisitos

- Docker Desktop (recomendado), **o** PHP 8.1+ con extensión `pdo_mysql` y MySQL
  8.x si se prefiere ejecutar localmente.

## Instalación y prueba con Docker

```bash
git clone <url-del-repo>
cd sistema-perfil-usuario
docker compose up --build
```

Cuando ambos contenedores estén listos, abrir <http://localhost:8080>.

El servicio de MySQL queda expuesto en `localhost:3307` por si se quiere conectar
con un cliente externo (DBeaver, MySQL Workbench, etc.).

Para detener:

```bash
docker compose down
```

Para borrar también los datos:

```bash
docker compose down -v
```

## Instalación manual (XAMPP / WAMP / LAMP)

1. Copiar la carpeta del proyecto a `htdocs/` (XAMPP) o equivalente.
2. Iniciar Apache y MySQL.
3. Importar `database.sql` desde phpMyAdmin (crea la base y la tabla).
4. Editar las variables al inicio de [`conexion.php`](conexion.php) o definir
   las variables de entorno `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`. Para
   XAMPP típico:
   - `DB_HOST=localhost`
   - `DB_USER=root`
   - `DB_PASS=` (vacío)
5. Abrir <http://localhost/sistema-perfil-usuario/>.

## Flujo de prueba sugerido

1. Ir a **Registro** y crear un usuario con cédula, nombre, correo y contraseña.
2. Iniciar sesión en **Login**.
3. En **Perfil** modificar nombre o correo y guardar.
4. Entrar a **Cambiar contraseña**, ingresar la actual y una nueva.
5. **Cerrar sesión** y volver a iniciar con la nueva contraseña.

## Seguridad implementada

- **Hash de contraseñas** con `password_hash(..., PASSWORD_DEFAULT)` y
  verificación con `password_verify`.
- **Consultas preparadas** (PDO con `ATTR_EMULATE_PREPARES = false`) en todas las
  operaciones contra la base, evitando SQL Injection.
- **Validación en servidor** de todos los inputs (cédula numérica, correo con
  `FILTER_VALIDATE_EMAIL`, longitud mínima de contraseña, no vacíos).
- **Escape de salida** con `htmlspecialchars` (helper `e()`) para prevenir XSS.
- **Control de sesión**: `requerir_login()` protege `perfil.php` y
  `cambiar_password.php`; tras login y cambio de contraseña se ejecuta
  `session_regenerate_id(true)` para mitigar fijación de sesión.
- **Cookies de sesión** con `HttpOnly` y `SameSite=Lax`.
- **Logout completo**: vacía `$_SESSION`, elimina la cookie y llama a
  `session_destroy()`.

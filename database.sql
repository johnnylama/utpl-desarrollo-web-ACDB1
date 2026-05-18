-- Base de datos para el sistema de perfil de usuario
CREATE DATABASE IF NOT EXISTS sistema_perfil
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE sistema_perfil;

CREATE TABLE IF NOT EXISTS usuarios (
  cedula         VARCHAR(20)  NOT NULL PRIMARY KEY,
  nombre         VARCHAR(100) NOT NULL,
  correo         VARCHAR(150) NOT NULL UNIQUE,
  password       VARCHAR(255) NOT NULL,
  fecha_registro DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

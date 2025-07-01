-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS free_tours;
USE free_tours;

-- Tabla de Usuarios (Administrador, Guía, Cliente)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'guia', 'cliente') DEFAULT 'cliente'
);

-- Tabla de Rutas
CREATE TABLE rutas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    localidad VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    foto VARCHAR(255),
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    latitud DECIMAL(10, 6),
    longitud DECIMAL(10, 6)
);



-- Tabla de Asignaciones de Guías a Rutas
CREATE TABLE asignaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ruta_id INT NOT NULL,
    guia_id INT NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ruta_id) REFERENCES rutas(id) ON DELETE CASCADE,
    FOREIGN KEY (guia_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de Reservas de Clientes en Rutas
CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ruta_id INT NOT NULL,
    cliente_id INT NOT NULL,
    num_personas INT CHECK (num_personas BETWEEN 1 AND 8),
    fecha_reserva TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ruta_id) REFERENCES rutas(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de Valoraciones de Rutas
CREATE TABLE valoraciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ruta_id INT NOT NULL,
    cliente_id INT NOT NULL,
    puntuacion INT CHECK (puntuacion BETWEEN 1 AND 5),
    comentario TEXT,
    fecha_valoracion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ruta_id) REFERENCES rutas(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

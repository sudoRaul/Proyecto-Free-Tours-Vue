USE free_tours;

-- Insertar Usuarios (Administrador, Guías, Clientes)
INSERT INTO usuarios (nombre, email, contraseña, rol) VALUES
('Admin', 'admin@tours.com', 'admin123', 'admin'),
('Guía 1', 'guia1@tours.com', 'pass123', 'guia'),
('Guía 2', 'guia2@tours.com', 'pass123', 'guia'),
('Cliente 1', 'cliente1@tours.com', 'pass123', 'cliente'),
('Cliente 2', 'cliente2@tours.com', 'pass123', 'cliente');

-- Insertar Rutas
INSERT INTO rutas (titulo, localidad, descripcion, foto, fecha, hora, latitud, longitud) VALUES
('Ruta Histórica', 'Madrid', 'Visita por lugares emblemáticos.', 'historia.jpg', '2025-02-10', '10:00', 40.4168, -3.7038),
('Ruta Cultural', 'Sevilla', 'Recorrido por el casco antiguo.', 'cultura.jpg', '2025-02-12', '16:00', 37.3891, -5.9845);


-- Insertar Asignaciones de Guías a Rutas
INSERT INTO asignaciones (ruta_id, guia_id) VALUES
(1, 2), -- Guía 2 asignado a la Ruta 1
(2, 3); -- Guía 3 asignado a la Ruta 2

-- Insertar Reservas de Clientes
INSERT INTO reservas (ruta_id, cliente_id, num_personas) VALUES
(1, 4, 2), -- Cliente 1 reservó para 2 personas en la Ruta 1
(2, 5, 3); -- Cliente 2 reservó para 3 personas en la Ruta 2

-- Insertar Valoraciones de Rutas
INSERT INTO valoraciones (ruta_id, cliente_id, puntuacion, comentario) VALUES
(1, 4, 5, 'Excelente recorrido, muy informativo!'),
(2, 5, 4, 'Muy interesante, pero un poco largo.');

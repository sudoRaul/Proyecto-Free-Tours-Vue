<?php
include 'db.php';

// Obtener todas las reservas o reservas específicas de un usuario
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $query = "
        SELECT 
            r.id AS reserva_id, 
            r.num_personas, 
            r.cliente_id, 
            u.nombre AS usuario_nombre, 
            u.email AS usuario_email, 
            ru.id AS ruta_id, 
            ru.titulo AS ruta_titulo, 
            ru.localidad AS ruta_localidad, 
            ru.descripcion AS ruta_descripcion, 
            ru.foto AS ruta_foto, 
            ru.fecha AS ruta_fecha, 
            ru.hora AS ruta_hora, 
            ru.latitud AS ruta_latitud, 
            ru.longitud AS ruta_longitud,
            COALESCE(v.puntuacion, NULL) AS valoracion

        FROM reservas r
        JOIN usuarios u ON r.cliente_id = u.id
        JOIN rutas ru ON r.ruta_id = ru.id
        JOIN valoraciones v ON r.ruta_id = v.ruta_id AND r.cliente_id = v.cliente_id
    ";

    // Si se especifica un email, filtrar por usuario_email
    if (isset($_GET['email'])) {
        $query .= " WHERE u.email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $_GET['email']);
    } else {
        $stmt = $conn->prepare($query);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $reservas = [];
    while ($row = $result->fetch_assoc()) {
        $reservas[] = $row;
    }

    echo json_encode($reservas, JSON_UNESCAPED_UNICODE);
    $stmt->close();
    $conn->close();
}


// Crear una nueva reserva
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'];  // Recibir email en lugar de user_id
    $ruta_id = $data['ruta_id'];
    $num_personas = $data['num_personas'];

    // Buscar el ID del usuario por su email
    $query = "SELECT id FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $user_id = $row['id'];

        // Insertar la reserva con el user_id obtenido
        $query = "INSERT INTO reservas (cliente_id, ruta_id, num_personas) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $user_id, $ruta_id, $num_personas);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Reserva creada']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al crear reserva']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
    }

    $stmt->close();
    $conn->close();
}

// Actualizar una reserva existente
if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $id = $_GET['id'];
    $data = json_decode(file_get_contents('php://input'), true);
    $num_personas = $data['num_personas'];

    $query = "UPDATE reservas SET num_personas = $num_personas WHERE id = $id";
    if ($conn->query($query)) {
        echo json_encode(['status' => 'success', 'message' => 'Reserva actualizada']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar reserva']);
    }
}

// Eliminar una reserva
if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $id = $_GET['id'];
    $query = "DELETE FROM reservas WHERE id = $id";
    if ($conn->query($query)) {
        echo json_encode(['status' => 'success', 'message' => 'Reserva eliminada']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar reserva']);
    }
}
?>

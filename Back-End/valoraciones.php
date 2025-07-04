<?php
include 'db.php';

// Obtener valoraciones con el nombre del usuario y de la ruta
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $query = "
        SELECT 
            v.id AS valoracion_id,
            v.puntuacion,
            v.comentario,
            v.ruta_id,
            r.titulo AS ruta_titulo,
            v.cliente_id,
            u.nombre AS cliente_nombre
        FROM valoraciones v
        JOIN usuarios u ON v.cliente_id = u.id
        JOIN rutas r ON v.ruta_id = r.id
    ";

    // Filtrar por ruta o usuario si se especifica en la petición
    if (isset($_GET['ruta_id'])) {
        $query .= " WHERE v.ruta_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $_GET['ruta_id']);
    } elseif (isset($_GET['user_id'])) {
        $query .= " WHERE v.cliente_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $_GET['user_id']);
    } else {
        $stmt = $conn->prepare($query);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $valoraciones = [];
    while ($row = $result->fetch_assoc()) {
        $valoraciones[] = $row;
    }

    echo json_encode($valoraciones, JSON_UNESCAPED_UNICODE);
    
    $stmt->close();
    $conn->close();
}

// Crear una nueva valoración (con validación para evitar duplicados)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $data['user_id'];
    $ruta_id = $data['ruta_id'];
    $estrellas = $data['estrellas'];
    $comentario = isset($data['comentario']) ? $data['comentario'] : '';

    // Verificar si el cliente ya ha valorado esta ruta
    $checkQuery = "SELECT id FROM valoraciones WHERE cliente_id = ? AND ruta_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $user_id, $ruta_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Si ya existe una valoración, no se permite otra
        echo json_encode(['status' => 'error', 'message' => 'Ya has valorado esta ruta']);
    } else {
        // Si no existe valoración, se permite crear una nueva
        $query = "INSERT INTO valoraciones (cliente_id, ruta_id, puntuacion, comentario) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiis", $user_id, $ruta_id, $estrellas, $comentario);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Valoración creada']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al crear valoración']);
        }
    }

    $stmt->close();
}

// Actualizar una valoración existente
if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $id = $_GET['id'];
    $data = json_decode(file_get_contents('php://input'), true);
    $estrellas = $data['estrellas'];
    $comentario = $data['comentario'];

    $query = "UPDATE valoraciones SET puntuacion = ?, comentario = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isi", $estrellas, $comentario, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Valoración actualizada']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar valoración']);
    }

    $stmt->close();
}

// Eliminar una valoración
if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $id = $_GET['id'];
    $query = "DELETE FROM valoraciones WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Valoración eliminada']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar valoración']);
    }

    $stmt->close();
}

$conn->close();

?>

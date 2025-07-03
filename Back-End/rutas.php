<?php
include 'db.php';

// Obtener todas las rutas con el guía asignado (si lo hay) y el número de asistentes
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['fecha'])) {
        $fecha = $_GET['fecha'];
        $localidad = isset($_GET['localidad']) ? $_GET['localidad'] : '';

        // Construcción de la consulta SQL
        $query = "SELECT * FROM rutas WHERE fecha = ?";
        $params = [$fecha];
        $types = "s";

        if (!empty($localidad)) {
            $query .= " AND localidad LIKE ?";
            $params[] = "%$localidad%";
            $types .= "s";
        }

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $rutas = [];
        while ($row = $result->fetch_assoc()) {
            $rutas[] = $row;
        }

        echo json_encode($rutas, JSON_UNESCAPED_UNICODE);
    
    } else if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $query = "
            SELECT 
                r.*, 
                COALESCE(SUM(res.num_personas), 0) AS asistentes
            FROM rutas r
            LEFT JOIN reservas res ON r.id = res.ruta_id
            WHERE r.id = ?
            GROUP BY r.id
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode($result->fetch_assoc(), JSON_UNESCAPED_UNICODE);
    
    } else {
        // Consulta para obtener todas las rutas con el guía asignado y número de asistentes
        $query = "
        SELECT 
            r.id, 
            r.titulo, 
            r.localidad, 
            r.descripcion, 
            r.foto, 
            r.fecha, 
            r.hora, 
            r.latitud, 
            r.longitud,
            u.id AS guia_id,
            u.nombre AS guia_nombre,
            u.email AS guia_email,
            COALESCE(SUM(res.num_personas), 0) AS asistentes
        FROM rutas r
        LEFT JOIN asignaciones a ON r.id = a.ruta_id
        LEFT JOIN usuarios u ON a.guia_id = u.id
        LEFT JOIN reservas res ON r.id = res.ruta_id
        GROUP BY r.id, u.id, u.nombre, u.email
        ";

        $result = $conn->query($query);
        $rutas = [];

        while ($row = $result->fetch_assoc()) {
            $rutas[] = $row;
        }

        echo json_encode($rutas, JSON_UNESCAPED_UNICODE);
    }
}


// Crear una nueva ruta con asignación opcional de guía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $titulo = $data['titulo'];
    $localidad = $data['localidad'];
    $descripcion = $data['descripcion'];
    $foto = $data['foto'];
    $fecha = $data['fecha'];
    $hora = $data['hora'];
    $latitud = $data['latitud'];
    $longitud = $data['longitud'];
    $guia_id = isset($data['guia_id']) ? $data['guia_id'] : null; // Puede ser null

    // Insertar la ruta en la base de datos
    $query = "INSERT INTO rutas (titulo, localidad, descripcion, foto, fecha, hora, latitud, longitud) 
              VALUES ('$titulo', '$localidad', '$descripcion', '$foto', '$fecha', '$hora', '$latitud', '$longitud')";
    
    if ($conn->query($query)) {
        $ruta_id = $conn->insert_id; // Obtener el ID de la ruta recién creada
        
        // Si se proporcionó un guia_id, intentar asignarlo
        if (!empty($guia_id)) {
            $result = $conn->query("SELECT id FROM usuarios WHERE id = $guia_id AND rol = 'guia'");
            
            if ($result->num_rows > 0) {
                $query_asignacion = "INSERT INTO asignaciones (ruta_id, guia_id) VALUES ('$ruta_id', '$guia_id')";
                if ($conn->query($query_asignacion)) {
                    echo json_encode(['status' => 'success', 'message' => 'Ruta creada y guía asignado']);
                } else {
                    echo json_encode(['status' => 'warning', 'message' => 'Ruta creada, pero error al asignar guía']);
                }
            } else {
                echo json_encode(['status' => 'warning', 'message' => 'Ruta creada, pero el guía no existe']);
            }
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Ruta creada sin asignación de guía']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al crear ruta']);
    }
}

// Actualizar una ruta
if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $id = $_GET['id'];
    $data = json_decode(file_get_contents('php://input'), true);
    $titulo = $data['titulo'];
    $localidad = $data['localidad'];
    $descripcion = $data['descripcion'];
    $foto = $data['foto'];
    $fecha = $data['fecha'];
    $hora = $data['hora'];
    $latitud = $data['latitud'];
    $longitud = $data['longitud'];

    $query = "UPDATE rutas SET titulo = '$titulo', localidad = '$localidad', descripcion = '$descripcion', 
              foto = '$foto', fecha = '$fecha', hora = '$hora', latitud = '$latitud', longitud = '$longitud' WHERE id = $id";
    if ($conn->query($query)) {
        echo json_encode(['status' => 'success', 'message' => 'Ruta actualizada']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar ruta']);
    }
}

// Eliminar una ruta
if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $id = $_GET['id'];
    $query = "DELETE FROM rutas WHERE id = $id";
    if ($conn->query($query)) {
        echo json_encode(['status' => 'success', 'message' => 'Ruta eliminada']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar ruta']);
    }
}
?>

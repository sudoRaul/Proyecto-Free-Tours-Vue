<?php
include 'db.php';

// Obtener asignaciones por guía o por ruta o por fecha
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['guia_id'])) {
        $guia_id = $_GET['guia_id'];
        $stmt = $conn->prepare("
            SELECT
                r.id AS ruta_id,
                r.titulo AS ruta_titulo,
                r.localidad AS ruta_localidad,
                r.descripcion AS ruta_descripcion,
                r.foto AS ruta_foto,
                r.fecha AS ruta_fecha,
                r.hora AS ruta_hora,
                r.latitud AS ruta_latitud,
                r.longitud AS ruta_longitud,
                u.nombre AS guia_nombre,
                u.email AS guia_email,
                u.rol AS guia_rol,
                res.id AS reserva_id,
                res.num_personas AS reserva_num_personas,
                res.fecha_reserva AS reserva_fecha_reserva,
                cliente.nombre AS cliente_nombre,
                cliente.email AS cliente_email
            FROM
                rutas r
            JOIN
                asignaciones a ON r.id = a.ruta_id
            JOIN
                usuarios u ON a.guia_id = u.id
            LEFT JOIN
                reservas res ON r.id = res.ruta_id
            LEFT JOIN
                usuarios cliente ON res.cliente_id = cliente.id
            WHERE
                a.guia_id = ?
        ");
        $stmt->bind_param("i", $guia_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        // Inicializar un array para almacenar las asignaciones
        $asignaciones = [];
    
        // Procesar los resultados
        while ($row = $result->fetch_assoc()) {
            // Verificar si la ruta ya existe en el array
            if (!isset($asignaciones[$row['ruta_id']])) {
                // Si no existe, agregar la ruta y la información del guía
                $asignaciones[$row['ruta_id']] = [
                    'ruta_id' => $row['ruta_id'],
                    'ruta_titulo' => $row['ruta_titulo'],
                    'ruta_localidad' => $row['ruta_localidad'],
                    'ruta_descripcion' => $row['ruta_descripcion'],
                    'ruta_foto' => $row['ruta_foto'],
                    'ruta_fecha' => $row['ruta_fecha'],
                    'ruta_hora' => $row['ruta_hora'],
                    'ruta_latitud' => $row['ruta_latitud'],
                    'ruta_longitud' => $row['ruta_longitud'],
                    'guia' => [
                        'nombre' => $row['guia_nombre'],
                        'email' => $row['guia_email'],
                        'rol' => $row['guia_rol']
                    ],
                    'reservas' => []
                ];
            }
            // Si hay una reserva, agregarla al array de reservas de la ruta
            if ($row['reserva_id'] !== null) {
                $asignaciones[$row['ruta_id']]['reservas'][] = [
                    'reserva_id' => $row['reserva_id'],
                    'num_personas' => $row['reserva_num_personas'],
                    'fecha_reserva' => $row['reserva_fecha_reserva'],
                    'cliente' => [
                        'nombre' => $row['cliente_nombre'],
                        'email' => $row['cliente_email']
                    ]
                ];
            }
        }
    
        // Convertir el array de asignaciones a formato JSON
        echo json_encode(array_values($asignaciones));
        $stmt->close();
    } elseif (isset($_GET['ruta_id'])) {
        $ruta_id = $_GET['ruta_id'];
        $result = $conn->query("SELECT * FROM asignaciones WHERE ruta_id = $ruta_id");
        $asignaciones = [];
        while ($row = $result->fetch_assoc()) {
            $asignaciones[] = $row;
        }
        echo json_encode($asignaciones);
    } elseif  (isset($_GET['fecha'])) {
        $fecha = $_GET['fecha'];

        // Consulta para obtener guías disponibles en la fecha dada
        $query = "
            SELECT u.id, u.nombre, u.email
            FROM usuarios u
            WHERE u.rol = 'guia'
            AND u.id NOT IN (
                SELECT a.guia_id
                FROM asignaciones a
                JOIN rutas r ON a.ruta_id = r.id
                WHERE r.fecha = ?
            )
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $fecha);
        $stmt->execute();
        $result = $stmt->get_result();

        $guias = [];
        while ($row = $result->fetch_assoc()) {
            $guias[] = $row;
        }

        echo json_encode($guias, JSON_UNESCAPED_UNICODE);
        $conn->close();

    } 

    else {
        $result = $conn->query("SELECT * FROM asignaciones");
        $asignaciones = [];
        while ($row = $result->fetch_assoc()) {
            $asignaciones[] = $row;
        }
        echo json_encode($asignaciones);
    }
}

// Crear o actualizar una asignación
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $ruta_id = $data['ruta_id'];
    $guia_id = $data['guia_id'];

    // Iniciar una transacción para garantizar integridad
    $conn->begin_transaction();

    try {
        // 1. Eliminar cualquier asignación existente para la misma ruta
        $query_delete = "DELETE FROM asignaciones WHERE ruta_id = ?";
        $stmt_delete = $conn->prepare($query_delete);
        $stmt_delete->bind_param("i", $ruta_id);
        $stmt_delete->execute();
        $stmt_delete->close();

        // 2. Insertar la nueva asignación
        $query_insert = "INSERT INTO asignaciones (ruta_id, guia_id) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($query_insert);
        $stmt_insert->bind_param("ii", $ruta_id, $guia_id);
        $stmt_insert->execute();
        $stmt_insert->close();

        // Confirmar transacción
        $conn->commit();

        echo json_encode(['status' => 'success', 'message' => 'Asignación creada o actualizada correctamente']);
    } catch (Exception $e) {
        // Revertir cambios si hay error
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar asignación']);
    }
}





?>

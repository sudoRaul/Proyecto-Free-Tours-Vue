<?php
include 'db.php';

header('Access-Control-Allow-Origin: *'); // Permite solicitudes de cualquier origen
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE'); // Métodos permitidos
header('Access-Control-Allow-Headers: Content-Type'); // Cabeceras permitidas
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

$request_uri = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$entity = $request_uri[0]; // Por ejemplo, 'usuarios', 'rutas'
$method = $_SERVER['REQUEST_METHOD'];




switch ($entity) {
    case 'usuarios':
        include 'usuarios.php';
        break;
    case 'rutas':
        include 'rutas.php';
        break;
    case 'reservas':
        include 'reservas.php';
        break;
    case 'valoraciones':
        include 'valoraciones.php';
        break;
    case 'asignaciones':
        include 'asignaciones.php';
        break;
    default:
        echo json_encode(['error' => 'Entidad no encontrada']);
        break;
}
?>

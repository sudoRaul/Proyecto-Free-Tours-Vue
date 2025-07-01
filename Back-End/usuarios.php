<?php
include 'db.php';

// Obtener todos los usuarios
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $result = $conn->query("SELECT * FROM usuarios");
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode($users, JSON_UNESCAPED_UNICODE);
}

// Iniciar sesión (Login)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['login'])) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['email'], $data['contraseña'])) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos']);
        exit;
    }

    $email = trim($data['email']);
    $password = $data['contraseña'];

    $stmt = $conn->prepare("SELECT id, nombre, email, `contraseña`, rol FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
        exit;
    }

    $user = $result->fetch_assoc();

    if ( $password == $user['contraseña']) {
        unset($user['contraseña']); // Eliminar la contraseña del array antes de enviarlo
        echo json_encode(['status' => 'success', 'message' => 'Login exitoso', 'user' => $user], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Contraseña incorrecta'], JSON_UNESCAPED_UNICODE);
    }
}

// Crear un usuario (Registro)
if ($_SERVER['REQUEST_METHOD'] == 'POST'  && !isset($_GET['login'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    $nombre = $data['nombre'];
    $email = $data['email'];
    $password = $data['contraseña'];
    $rol = "cliente";

    $query = "INSERT INTO usuarios (nombre, email, contraseña, rol) VALUES ('$nombre', '$email', '$password', '$rol')";
    if ($conn->query($query)) {
        echo json_encode(['status' => 'success', 'message' => 'Usuario creado']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al crear usuario']);
    }
}

// Actualizar el rol de un usuario
if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $id = $_GET['id'];
    $data = json_decode(file_get_contents('php://input'), true);
    $rol = $data['rol'];

    $query = "UPDATE usuarios SET rol = '$rol' WHERE id = $id";
    if ($conn->query($query)) {
        echo json_encode(['status' => 'success', 'message' => 'Rol actualizado']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar rol']);
    }
}

// Eliminar un usuario
if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $id = $_GET['id'];
    $query = "DELETE FROM usuarios WHERE id = $id";
    if ($conn->query($query)) {
        echo json_encode(['status' => 'success', 'message' => 'Usuario eliminado']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar usuario']);
    }
}
?>

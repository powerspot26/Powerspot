<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Ingresa tu correo y contraseña."]);
    exit;
}

// Buscar el usuario por su correo
$stmt = $conexion->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Correo o contraseña incorrectos."]);
    $stmt->close();
    exit;
}

$stmt->bind_result($id, $nombre, $passwordHash);
$stmt->fetch();
$stmt->close();

// Verificar contraseña cifrada
if (password_verify($password, $passwordHash)) {
    echo json_encode([
        "success" => true, 
        "message" => "¡Bienvenido de nuevo, $nombre!",
        "user" => ["id" => $id, "nombre" => $nombre, "email" => $email]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Correo o contraseña incorrectos."]);
}

$conexion->close();
?>
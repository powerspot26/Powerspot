<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

$nombre = $data['nombre'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($nombre) || empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Por favor, completa todos los campos."]);
    exit;
}

// Verificar si el correo ya existe en la base de datos
$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Este correo ya está registrado."]);
    $stmt->close();
    exit;
}
$stmt->close();

// Cifrar contraseña de forma segura
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Insertar nuevo usuario
$stmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $nombre, $email, $passwordHash);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "¡Cuenta creada con éxito! Ya puedes iniciar sesión."]);
} else {
    echo json_encode(["success" => false, "message" => "Error al registrar el usuario."]);
}

$stmt->close();
$conexion->close();
?>
<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION["id_user"])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

// Conexión a la base de datos
$conexion = pg_connect("host=127.0.0.1 port=5432 dbname=proyecto user=proyecto password=proyecto");
if (!$conexion) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit();
}

// Verificar que se recibió el ID de la nota
if (!isset($_GET['id_notes']) || !is_numeric($_GET['id_notes'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de nota inválido']);
    exit();
}

$id_notes = intval($_GET['id_notes']);
$id_user = intval($_SESSION["id_user"]);

// Consultar la nota, verificando que pertenezca al usuario logueado
$consulta = "SELECT id_notes, contenido, titulo FROM nota WHERE id_notes = $1 AND id_user = $2";
$resultado = pg_query_params($conexion, $consulta, [$id_notes, $id_user]);

if (!$resultado) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la consulta']);
    exit();
}

$nota = pg_fetch_assoc($resultado);

if (!$nota) {
    http_response_code(404);
    echo json_encode(['error' => 'Nota no encontrada']);
    exit();
}

// Establecer el header para JSON
header('Content-Type: application/json');

// Devolver la nota en formato JSON
echo json_encode([
    'id_notes' => $nota['id_notes'],
    'contenido' => $nota['contenido'],
    'titulo' => $nota['titulo']
]);

pg_close($conexion);
?>
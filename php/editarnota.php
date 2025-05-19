<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION["id_user"])) {
    echo json_encode(["error" => "No autorizado"]);
    exit();
}

if (!isset($_GET['id_note'])) {
    echo json_encode(["error" => "ID de nota no proporcionado"]);
    exit();
}

$id_note = intval($_GET['id_note']);
$id_user = intval($_SESSION["id_user"]);

$conexion = pg_connect("host=127.0.0.1 port=5432 dbname=proyecto user=proyecto password=proyecto");
if (!$conexion) {
    echo json_encode(["error" => "Error de conexión a la base de datos"]);
    exit();
}

// Verificar que el usuario tiene acceso a esa nota (propietario o compartida)
$query = "
SELECT contenido FROM nota n
LEFT JOIN compartir c ON n.id_notes = c.id_notes
WHERE n.id_notes = $id_note AND (n.id_user = $id_user OR c.id_user = $id_user)
LIMIT 1
";

$result = pg_query($conexion, $query);

if (!$result || pg_num_rows($result) === 0) {
    echo json_encode(["error" => "Nota no encontrada o acceso denegado"]);
    exit();
}

$nota = pg_fetch_assoc($result);

echo json_encode([
    "id_note" => $id_note,
    "contenido" => $nota['contenido']
]);
?>

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

// Verificar si es propietario o tiene permisos (incluyendo notas compartidas)
$consulta = "
    SELECT n.id_notes, n.contenido, n.titulo, 'propietario' as tipo_acceso
    FROM nota n 
    WHERE n.id_notes = $1 AND n.id_user = $2
    UNION
    SELECT n.id_notes, n.contenido, n.titulo, c.permisos as tipo_acceso
    FROM nota n
    JOIN compartir c ON n.id_notes = c.id_notes
    WHERE n.id_notes = $1 AND c.id_user = $2
";

$resultado = pg_query_params($conexion, $consulta, [$id_notes, $id_user]);

if (!$resultado) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la consulta']);
    exit();
}

$nota = pg_fetch_assoc($resultado);

if (!$nota) {
    http_response_code(404);
    echo json_encode(['error' => 'Nota no encontrada o sin permisos']);
    exit();
}

// Obtener etiquetas
$res_etiquetas = pg_query_params($conexion, "SELECT nombre FROM etiqueta WHERE id_notes = $1", [$id_notes]);
$etiquetas = [];

if ($res_etiquetas) {
    while ($et = pg_fetch_assoc($res_etiquetas)) {
        $etiquetas[] = $et['nombre'];
    }
}

// Establecer el header para JSON
header('Content-Type: application/json');

// Devolver la nota en formato JSON incluyendo las etiquetas
echo json_encode([
    'id_notes' => $nota['id_notes'],
    'contenido' => $nota['contenido'],
    'titulo' => $nota['titulo'],
    'etiquetas' => $etiquetas,
    'tipo_acceso' => $nota['tipo_acceso']
]);

pg_close($conexion);
?>
<?php
session_start();
$conexion = pg_connect("host=127.0.0.1 port=5432 dbname=proyecto user=proyecto password=proyecto");

if (!$conexion) {
    http_response_code(500);
    echo "Error de conexión";
    exit;
}

if (!isset($_SESSION["id_user"])) {
    http_response_code(401);
    echo "Sesión no iniciada";
    exit;
}

$id_user = intval($_SESSION["id_user"]);
$id_note = intval($_POST["id_notes"]);
$nuevoContenido = pg_escape_string($conexion, $_POST["contenido"]);
$fecha = date("Y-m-d");

$update = pg_query($conexion, "
    UPDATE nota SET contenido = '$nuevoContenido', fecha_editado = '$fecha'
    WHERE id_notes = $id_notes AND (
        id_user = $id_user OR EXISTS (
            SELECT 1 FROM compartir 
            WHERE id_notes = nota.id_notes AND id_user = $id_user AND permisos = 'edicion'
        )
    )
");

if (!$update) {
    http_response_code(500);
    echo "Error al actualizar la nota: " . pg_last_error($conexion);
} else {
    echo "Nota actualizada correctamente.";
}
?>

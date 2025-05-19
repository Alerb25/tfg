<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION["id_user"])) {
    exit("Sesión no iniciada.");
}

$conexion = pg_connect("host=localhost dbname=proyecto user=proyecto password=proyecto");
if (!$conexion) {
    exit("Error de conexión a la base de datos.");
}

$id_user_origen = $_SESSION["id_user"];

$id_nota = $_POST["id_note"] ?? null;
$email = $_POST["email_usuario"] ?? null;
$permisos = $_POST["permisos"] ?? 'lectura';

if (!$id_nota || !$email) {
    exit("Faltan datos.");
}

// Verificar que la nota le pertenece
$verificar_nota = pg_query_params($conexion, "SELECT * FROM nota WHERE id_note = $1 AND id_user = $2", [$id_nota, $id_user_origen]);
if (pg_num_rows($verificar_nota) == 0) {
    exit("No tienes permiso para compartir esta nota.");
}

// Verificar que el correo pertenece a otro usuario
$res = pg_query_params($conexion, "SELECT id_user FROM usuario WHERE mail = $1", [$email]);
if (pg_num_rows($res) == 0) {
    exit("El correo no está registrado.");
}

$destino = pg_fetch_assoc($res);
$id_user_destino = $destino["id_user"];

if ($id_user_destino == $id_user_origen) {
    exit("No puedes compartir una nota contigo mismo.");
}

// Revisar si ya existe la relación
$verificar = pg_query_params($conexion, "SELECT * FROM compartir WHERE id_note = $1 AND id_user = $2", [$id_nota, $id_user_destino]);

if (pg_num_rows($verificar) > 0) {
    exit("Ya has compartido esta nota con ese usuario.");
}

// Insertar la relación
$insertar = pg_query_params($conexion, "INSERT INTO compartir (id_note, id_user, permisos) VALUES ($1, $2, $3)", [$id_nota, $id_user_destino, $permisos]);

if ($insertar) {
    echo "Nota compartida exitosamente.";
} else {
    echo "Error al compartir la nota.";
}
?>

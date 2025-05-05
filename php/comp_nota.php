<?php
session_start();

// Verifica si hay sesión activa
if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit();
}

// Incluir archivo de conexión
require_once 'config/db.php';
$conexion = pg_connect("host=localhost dbname=proyecto user=proyecto password=proyecto");

// Datos del usuario
$id_user = $_SESSION["id_user"];
$error = "";
$mensaje = "";
$nota = null;
$usuarios_compartidos = array();

// Verificar que se recibió un ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: panel.php");
    exit();
}

$id_nota = (int)$_GET['id'];

// Verificar que la nota pertenece al usuario
$consulta = "SELECT * FROM nota WHERE id_note = $1 AND id_user = $2";
$resultado = pg_query_params($conexion, $consulta, array($id_nota, $id_user));

if (pg_num_rows($resultado) == 0) {
    header("Location: panel.php?error=nota_no_encontrada");
    exit();
}

$nota = pg_fetch_assoc($resultado);

// Obtener usuarios con quienes ya se compartió la nota
$consulta_compartidos = "SELECT c.*, u.nombre, u.mail 
                         FROM compartir c 
                         JOIN usuario u ON c.id_user = u.id_user 
                         WHERE c.id_note = $1";
$resultado_compartidos = pg_query_params($conexion, $consulta_compartidos, array($id_nota));
while ($usuario = pg_fetch_assoc($resultado_compartidos)) {
    $usuarios_compartidos[] = $usuario;
}

// Procesar formulario para compartir
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["compartir"])) {
    $email_usuario = pg_escape_string($conexion, $_POST["email_usuario"]);
    $permisos = pg_escape_string($conexion, $_POST["permisos"]);
    
    // Verificar que el usuario existe
    $consulta_usuario = "SELECT id_user FROM usuario WHERE mail = $1";
    $resultado_usuario = pg_query_params($conexion, $consulta_usuario, array($email_usuario));
    
}
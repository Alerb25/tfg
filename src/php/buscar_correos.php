<?php
// buscar_correos.php
session_start();
header('Content-Type: application/json');

$conexion = pg_connect("host=db  port=5432 dbname=proyecto user=proyecto password=proyecto");

$correo = strtolower(trim($_GET['correo'] ?? ''));

if (strlen($correo) < 2) {
    echo json_encode([]);
    exit();
}

$query = "SELECT mail FROM usuario WHERE mail ILIKE $1 AND id_user != $2 LIMIT 5";
$result = pg_query_params($conexion, $query, ["$correo%", $_SESSION["id_user"]]);

$correos = [];

while ($fila = pg_fetch_assoc($result)) {
    $correos[] = $fila["mail"];
}

echo json_encode($correos);

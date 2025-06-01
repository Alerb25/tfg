<?php
// config/db.php

// Solo crea la conexión si no existe
if (!isset($conexion)) {
    $conexion = pg_connect("host=localhost dbname=proyecto user=proyecto password=proyecto");

    if (!$conexion) {
        die("Error de conexión a la base de datos.");
    }
}
?>

<?php
session_start();

// Conexión a la base de datos
if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit();
}

?>
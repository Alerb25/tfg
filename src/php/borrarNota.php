<?php
session_start();

$conexion = pg_connect("host=127.0.0.1 port=5433 dbname=proyecto user=proyecto password=proyecto");

if (!$conexion) {
    die("Error de conexión");
}

if (!isset($_SESSION["id_user"])) {
    die("Sesión no iniciada.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id_notes"])) {
    $idNota = intval($_POST["id_notes"]);
    $id_user = intval($_SESSION["id_user"]);

    // Seguridad: solo el dueño de la nota puede borrarla
    $verifica = pg_query($conexion, "SELECT * FROM nota WHERE id_notes = $idNota AND id_user = $id_user");

    if ($verifica && pg_num_rows($verifica) === 1) {
        $delete = pg_query($conexion, "DELETE FROM nota WHERE id_notes = $idNota");
        echo $delete ? "Nota eliminada." : "Error al borrar.";
    } else {
        echo "No tienes permisos para borrar esta nota.";
    }
}
?>

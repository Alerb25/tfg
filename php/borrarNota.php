<?php
session_start();
$conexion = pg_connect("host=127.0.0.1 port=5432 dbname=proyecto user=proyecto password=proyecto");

if (!$conexion) {
    die("Error de conexión");
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id_note"])) {
    $idNota = intval($_POST["id_note"]);
    $id_user = $_SESSION["Id_user"];

    // Seguridad: solo el dueño de la nota puede borrarla
    $verifica = pg_query($conexion, "SELECT * FROM nota WHERE id_notes = $idNota AND id_user = $id_user");

    if (pg_num_rows($verifica) === 1) {
        $delete = pg_query($conexion, "DELETE FROM nota WHERE id_notes = $idNota");
        if ($delete) {
            echo "Nota eliminada.";
        } else {
            echo "Error al borrar.";
        }
    } else {
        echo "No tienes permisos para borrar esta nota.";
    }
}
?>

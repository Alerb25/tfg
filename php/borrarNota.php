<?php
session_start();
$conexion = pg_connect("host=127.0.0.1 port=5432 dbname=proyecto user=proyecto password=proyecto");

if (!$conexion) {
    die("Error de conexión");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_note"])) {
    $id_note = intval($_POST["id_note"]);
    $id_user = intval($_SESSION["id_user"]);

    // Verificamos que la nota le pertenezca al usuario
    $verificar = pg_query($conexion, "SELECT * FROM nota WHERE id_notes = $id_note AND id_user = $id_user");

    if (pg_num_rows($verificar) > 0) {
        $borrar = pg_query($conexion, "DELETE FROM nota WHERE id_notes = $id_note");
        if ($borrar) {
            echo "Nota borrada correctamente.";
        } else {
            echo "Error al borrar la nota.";
        }
    } else {
        echo "No tienes permiso para borrar esta nota.";
    }
}
?>

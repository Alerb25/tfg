<?php
session_start();

if (!isset($_SESSION["id_user"])) {
    die("Acceso no autorizado.");
}

$id_usuario_origen = intval($_SESSION["id_user"]);

// Conectar a la base de datos
$conexion = pg_connect("host=db  port=5432 dbname=proyecto user=proyecto password=proyecto");
if (!$conexion) {
    die("Error de conexión con la base de datos.");
}

// Validar POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_notes = intval($_POST["id_notes"]);
    $correo_destino = trim($_POST["email_usuario"]);
    $permisos = $_POST["permisos"] === 'edicion' ? 'edicion' : 'lectura';

    // Verificar si la nota pertenece al usuario que comparte
    $verificarNota = pg_query_params($conexion, "SELECT id_notes FROM nota WHERE id_notes = $1 AND id_user = $2", [$id_notes, $id_usuario_origen]);
    if (pg_num_rows($verificarNota) === 0) {
        die("No tienes permiso para compartir esta nota.");
    }

    // Buscar usuario de destino
    $consultaUsuario = pg_query_params($conexion, "SELECT id_user FROM usuario WHERE mail = $1", [$correo_destino]);
    if (pg_num_rows($consultaUsuario) === 0) {
        die("El usuario con ese correo no existe.");
    }

    $filaUsuario = pg_fetch_assoc($consultaUsuario);
    $id_usuario_destino = intval($filaUsuario["id_user"]);

    // Evitar compartir contigo mismo
    if ($id_usuario_destino === $id_usuario_origen) {
        die("No puedes compartir una nota contigo mismo.");
    }

    // Verificar si ya está compartida
    $verificarExistente = pg_query_params($conexion,
        "SELECT * FROM compartir WHERE id_notes = $1 AND id_user = $2",
        [$id_notes, $id_usuario_destino]
    );
    if (pg_num_rows($verificarExistente) > 0) {
        die("Esta nota ya está compartida con ese usuario.");
    }

    // Compartir nota
    $compartir = pg_query_params($conexion,
        "INSERT INTO compartir (id_notes, id_user, permisos) VALUES ($1, $2, $3)",
        [$id_notes, $id_usuario_destino, $permisos]
    );

    if ($compartir) {
        echo "Nota compartida correctamente.";
    } else {
        echo "Error al compartir la nota.";
    }
} else {
    echo "Método no permitido.";
}
?>

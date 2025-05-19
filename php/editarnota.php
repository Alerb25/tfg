<?php
session_start();

$conexion = pg_connect("host=127.0.0.1 port=5432 dbname=proyecto user=proyecto password=proyecto");

if (!$conexion) {
    die("Error de conexión");
}

if (!isset($_SESSION["id_user"])) {
    die("Sesión no iniciada.");
}

$id_user = intval($_SESSION["id_user"]);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id_note"])) {
    $idNota = intval($_POST["id_note"]);

    // Verifica que la nota pertenezca al usuario
    $consulta = pg_query($conexion, "
     SELECT n.* FROM nota n
     LEFT JOIN compartir c ON n.id_notes = c.id_notes
     WHERE n.id_notes = $idNota AND (
         n.id_user = $id_user OR (c.id_user = $id_user AND c.permiso = 'edicion')
     )
 ");

    if (pg_num_rows($consulta) === 1) {
        $nota = pg_fetch_assoc($consulta);
    } else {
        die("No tienes permiso para editar esta nota.");
    }
}

// Si el usuario envía el formulario con el nuevo contenido
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["editar_nota"])) {
    $idNota = intval($_POST["id_note"]);
    $nuevoContenido = pg_escape_string($conexion, $_POST["contenido"]);
    $fecha = date("Y-m-d");

    $update = pg_query($conexion, "
    UPDATE nota SET contenido = '$nuevoContenido', fecha_editado = '$fecha'
    WHERE id_notes = $idNota AND (
        id_user = $id_user OR EXISTS (
            SELECT 1 FROM compartir 
            WHERE id_notes = nota.id_notes AND id_user = $id_user AND permiso = 'edicion'
        )
    )
");
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Editar Nota</title>
    <style>
        body {
            font-family: Arial;
            padding: 20px;
            background: #f0f0f0;
        }

        form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        textarea {
            width: 100%;
            height: 150px;
            padding: 10px;
        }

        button {
            padding: 10px 15px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <h2>Editar Nota</h2>
    <form method="POST">
        <input type="hidden" name="id_note" value="<?= htmlspecialchars($nota['id_notes']) ?>">
        <textarea name="contenido" required><?= htmlspecialchars($nota['contenido']) ?></textarea>
        <br>
        <button type="submit" name="editar_nota">Guardar Cambios</button>
    </form>
</body>

</html>
?>
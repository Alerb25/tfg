<?php
session_start();

// Verifica si hay sesión activa
if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit();
}

// Conexión PostgreSQL
$conexion = pg_connect("host=localhost dbname=proyecto user=proyecto password=proyecto");
if (!$conexion) {
    die("Error de conexión con la base de datos");
}

// Datos del usuario
$id_user = $_SESSION["id_user"];
$nombre = $_SESSION["nombre"];

// Guardar nueva nota
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["guardar"])) {
    $contenido = pg_escape_string($conexion, $_POST["nota"]);
    $fecha = date("Y-m-d");

    $insertar = "INSERT INTO nota (contenido, fecha_creado, fecha_editado, id_user) 
                 VALUES ('$contenido', '$fecha', '$fecha', '$id_user')";
    if (pg_query($conexion, $insertar)) {
        header("Location: panel.php"); // Evita reenvío del form
        exit();
    } else {
        $error = "Error al guardar la nota.";
    }
}

// Obtener las nota del usuario
$consulta = "
SELECT DISTINCT n.* 
FROM nota n
LEFT JOIN compartir c ON n.id_note = c.id_note
WHERE n.id_user = $id_user OR c.id_user = $id_user
ORDER BY n.fecha_creado DESC;
";
$resultado = pg_query($conexion, $consulta);

$notas = [];

while ($fila = pg_fetch_assoc($resultado_propias)) {
    $notas[] = $fila;
}

while ($fila = pg_fetch_assoc($resultado_compartidas)) {
    $notas[] = $fila;
}


// HTML
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Panel de nota - App de nota</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; display: flex; flex-direction: column; align-items: center; padding-top: 30px; }
        .box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 400px; margin-bottom: 20px; }
        input, textarea { width: 100%; padding: 10px; margin: 8px 0; }
        button { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 5px; }
        .nota { background: #eef; padding: 10px; border-radius: 5px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Bienvenido, $nombre</h2>

    <div class='box'>
        <h3>Crear Nueva Nota</h3>
        <form method='POST'>
            <textarea name='nota' placeholder='Escribe tu nota aquí...' required></textarea>
            <button type='submit' name='guardar'>Guardar</button>
        </form>
    </div>

    <div class='box'>
        <h3>Notas Guardadas</h3>";

        if (pg_num_rows($resultado) > 0) {
            while ($nota = pg_fetch_assoc($resultado)) {
                echo "<div class='nota'>
                        <p><strong>Nota #{$nota["id_notes"]}</strong></p>
                        <p>{$nota["contenido"]}</p>
                        <p><em>Creada: {$nota["fecha_creado"]}</em></p>
                    </div>";
            }
        } else {
            echo "<p>No hay notas creadas.</p>";
        }

echo "  </div>
    <a href='logout.php'>Cerrar sesión</a>
</body>
</html>";
?>

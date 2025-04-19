<?php
session_start();

// Conexión a la base de datos, si no hay sesion activa se redirige al login
if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit();
}

// Conexión a la base de datos igual que en login.php
$conexion = new mysqli("localhost", "usuario", "", "proyecto");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

//datos del usuario
$id_user = $_SESSION["id_user"];
$nombre = $_SESSION["nombre"];

//Consultar notas del usuario
$consulta = "SELECT * FROM nota WHERE Id_User='$id_user'";
$resultado = $conexion->query($consulta);

//HTML
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Panel de Notas - App de Notas</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; display: flex; justify-content: center; align-items: flex-start; gap: 40px; padding-top: 50px; }
        .box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 300px; }
        input { width: 100%; padding: 10px; margin: 8px 0; }
        button { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 5px; }
        .error { color: red; margin-top: 10px; }
        .exito { color: green; margin-top: 10px; }
    </style>
</head>
<body>
    <h2>Bienvenido $nombre</h2>
    <div class='box'>
        <h2>CCrear Nueva Nota</h2>
        <form method='POST'>
            <input type='text' name='nota' placeholder='Escribe tu nota aquí...' required>
            <button type='submit' name='guardar'>Guardar</button>
        </form>
    <h2>Notas</h2>";

    if ($resultado->num_rows > 0) {
        while ($nota = $resultado->fetch_assoc()){
            echo "<div class='nota'>
                <p><strong>Nota #{$nota["Id_Notes"]}</strong></p>
                <p>{$nota["contenido"]}</p>
                <p>Creada: {$nota["fecha_creado"]}</p>
                </div>";
        }
    } else {
        echo "<p>No hay notas creadas</p>";
    }

    echo "<a href='logout.php'>Cerrar Sesión</a>";

echo "  </div>
    </body>
    </html>";

?>
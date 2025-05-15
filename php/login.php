<?php
session_start();

// Conexión a la base de datos (PostgreSQL)
$conexion = pg_connect("host=127.0.0.1 port=5432 dbname=proyecto user=proyecto password=proyecto");


if (!$conexion) {
    die("Error de conexión con la base de datos");
}

// Login 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $mail = trim(pg_escape_string($conexion, $_POST["mail"]));
    $password = trim(pg_escape_string($conexion, $_POST["password"]));

    $consulta = "SELECT * FROM usuario WHERE mail = '$mail' AND password = '$password'";
    $resultado = pg_query($conexion, $consulta);

    if (!$resultado) {
        die("Error en la consulta SQL: " . pg_last_error($conexion));
    }

    if (pg_num_rows($resultado) == 1) {
        $usuario = pg_fetch_assoc($resultado);
        $_SESSION["id_user"] = $usuario["id_user"];
        $_SESSION["Nombre"] = $usuario["nombre"];

        header("Location: panel.php");
        exit();
    } else {
        $error = "Correo o contraseña incorrectos";
    }
}


// Registro
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $mail = $_POST["new_mail"];
    $password = $_POST["password"];
    $nombre = $_POST["nombre"];
    $p_apellido = $_POST["p_apellido"];
    $s_apellido = $_POST["s_apellido"];

    $verificar = "SELECT * FROM usuario WHERE mail='$mail'";
    $existe = pg_query($conexion, $verificar);

    $insertar = "INSERT INTO usuario (id_user, nombre, password, mail, p_apellido, s_apellido) 
    VALUES (DEFAULT, '$nombre', '$password', '$mail', '$p_apellido', '$s_apellido')";

    if (@pg_query($conexion, $insertar)) {
        $exito = "Registro exitoso. Ahora puedes iniciar sesión";
    } else {
        if (str_contains(pg_last_error($conexion), 'duplicate key')) {
            $error = "El correo ya está en uso. Prueba con otro.";
        } else {
            $error = "Error al crear la cuenta: " . pg_last_error($conexion);
        }
    }
}

// HTML pintado con echo
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Login / Registro - App de Notas</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; display: flex; justify-content: center; align-items: flex-start; gap: 40px; padding-top: 50px; }
        .box { background: white; padding: 20px 50px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 300px; }
        input { width: 100%; padding: 10px; margin: 8px 0; }
        button { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 5px; }
        .error { color: red; margin-top: 10px; }
        .exito { color: green; margin-top: 10px; }
    </style>
</head>
<body>
    <div class='box'>
        <h2>Iniciar Sesión</h2>
        <form method='POST'>
            <input type='mail' name='mail' placeholder='Correo' required>
            <input type='password' name='password' placeholder='Contraseña' required>
            <button type='submit' name='login'>Entrar</button>
        </form>";

if (!empty($error)) echo "<p class='error'>$error</p>";
if (!empty($exito)) echo "<p class='exito'>$exito</p>";

echo "  </div>

    <div class='box'>
        <h2>Crear Cuenta</h2>
        <form method='POST'>
            <input type='text' name='nombre' placeholder='Nombre' required>
            <input type='text' name='p_apellido' placeholder='Primer Apellido' required>
            <input type='text' name='s_apellido' placeholder='Segundo Apellido' required>
            <input type='mail' name='new_mail' placeholder='Correo' required>
            <input type='password' name='password' placeholder='Contraseña' required>
            <button type='submit' name='register'>Registrarse</button>
        </form>
    </div>
</body>
</html>";

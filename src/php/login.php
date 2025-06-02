<?php
session_start();

// Conexión a la base de datos
$conexion = pg_connect("host=127.0.0.1 port=5433 dbname=proyecto user=proyecto password=proyecto");
if (!$conexion) {
    die("Error de conexión con la base de datos");
}

// Función para sanear datos
function sanear($data) {
    return htmlspecialchars(trim($data));
}

// LOGIN
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["login"])) {
    $mail = filter_var($_POST["mail"], FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"];

    if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $error = "Correo inválido";
    } else {
        $query = "SELECT * FROM usuario WHERE mail = $1 AND password = $2";
        $result = pg_query_params($conexion, $query, [$mail, $password]);

        if ($result && pg_num_rows($result) === 1) {
            $usuario = pg_fetch_assoc($result);
            $_SESSION["id_user"] = $usuario["id_user"];
            $_SESSION["Nombre"] = $usuario["nombre"];
            header("Location: panel.php");
            exit();
        } else {
            $error = "Correo o contraseña incorrectos";
        }
    }
}

// REGISTRO
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["register"])) {
    $mail = filter_var($_POST["new_mail"], FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"];
    $nombre = sanear($_POST["nombre"]);
    $p_apellido = sanear($_POST["p_apellido"]);
    $s_apellido = sanear($_POST["s_apellido"]);

    if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $error = "Correo inválido";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } else {
        $query_check = "SELECT 1 FROM usuario WHERE mail = $1";
        $check = pg_query_params($conexion, $query_check, [$mail]);

        if (pg_num_rows($check) > 0) {
            $error = "El correo ya está en uso";
        } else {
            $query_insert = "INSERT INTO usuario (nombre, password, mail, p_apellido, s_apellido) 
                             VALUES ($1, $2, $3, $4, $5)";
            $params = [$nombre, $password, $mail, $p_apellido, $s_apellido];
            $insert = pg_query_params($conexion, $query_insert, $params);

            if ($insert) {
                $exito = "Registro exitoso. Ya puedes iniciar sesión.";
            } else {
                $error = "Error al registrar el usuario.";
            }
        }
    }
}

// HTML
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
        button { padding: 10px 15px; background-color: #3498db; color: white; border: none; border-radius: 5px; }
        .error { color: red; margin-top: 10px; }
        .exito { color: green; margin-top: 10px; }
    </style>
</head>
<body>
    <div class='box'>
        <h2>Iniciar Sesión</h2>
        <form method='POST'>
            <input type='email' name='mail' placeholder='Correo' required>
            <input type='password' name='password' placeholder='Contraseña' required>
            <button type='submit' name='login'>Entrar</button>
        </form>";

if (!empty($error)) echo "<p class='error'>$error</p>";
if (!empty($exito)) echo "<p class='exito'>$exito</p>";

echo "</div>
    <div class='box'>
        <h2>Crear Cuenta</h2>
        <form method='POST'>
            <input type='text' name='nombre' placeholder='Nombre' required>
            <input type='text' name='p_apellido' placeholder='Primer Apellido' required>
            <input type='text' name='s_apellido' placeholder='Segundo Apellido' required>
            <input type='email' name='new_mail' placeholder='Correo' required>
            <input type='password' name='password' placeholder='Contraseña (mín. 6 caracteres)' required>
            <button type='submit' name='register'>Registrarse</button>
        </form>
    </div>
</body>
</html>";
?>

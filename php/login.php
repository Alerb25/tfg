<?php
session_start();

// Conexión a la base de datos
$conexion = new mysqli("localhost", "usuario", "", "proyecto");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

//Login 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $mail = $_POST["mail"];
    $password = $_POST["password"];

    $consulta = "SELECT * FROM usuario WHERE mail='$mail' AND Password='$password'";
    $resultado = $conexion->query($consulta);

    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();
        $_SESSION["id_user"] = $usuario["Id_User"];
        $_SESSION["nombre"] = $usuario["Nombre"];

        header("Location: index.php"); // Página principal tras login
        exit();
    } else {
        $error = "Correo o contraseña incorrectos";
    }
}

//Registro
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $mail = $_POST["new_mail"];
    $password = $_POST["password"];
    $nombre = $_POST["nombre"];
    $p_apellido = $_POST["p_apellido"];
    $s_apellido = $_POST["s_apellido"];

    $verificar = "SELECT * FROM usuario WHERE mail='$mail'";
    $existe = $conexion->query($verificar);

    if ($existe->num_rows > 0) {
        $error = "El correo ya existe";
    } else {
        $insertar = "INSERT INTO usuario (Nombre, Password, mail, p_apellido, s_apellido) VALUES ('$nombre', '$password', '$mail', '$p_apellido', '$s_apellido')";

        if ($conexion->query($insertar)) {
            $exito = "Registro exitoso. Ahora puedes iniciar sesión";
        } else {
            $error = "Error al crear la cuenta";
        }
    }
}

// HTML pintado con echo
echo "<!DOCTYPE html>
<html>
<head>
    <title>Login / Registro - App de Notas</title>
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
    <div class='box'>
        <h2>Iniciar Sesión</h2>
        <form method='POST'>
            <input type='email' name='mail' placeholder='Correo' required>
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
            <input type='email' name='nuevo_mail' placeholder='Correo' required>
            <input type='password' name='nueva_password' placeholder='Contraseña' required>
            <button type='submit' name='registro'>Registrarse</button>
        </form>
    </div>
</body>
</html>";

?>
<?php
session_start();

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "proyecto"); 

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mail = $_POST["mail"];
    $password = $_POST["password"];

    $consulta = "SELECT * FROM usuario WHERE mail='$mail' AND Password='$password'";
    $resultado = $conexion->query($consulta);

    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();
        $_SESSION["id_user"] = $usuario["Id_User"];
        $_SESSION["nombre"] = $usuario["Nombre"];

        header("Location: panel.php"); // Página principal tras login
        exit();
    } else {
        $error = "Correo o contraseña incorrectos";
    }
}

// HTML pintado con echo
echo "<!DOCTYPE html>
<html>
<head>
    <title>Login - App de Notas</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input { display: block; margin-bottom: 10px; width: 100%; padding: 10px; }
        button { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 5px; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class='login-box'>
        <h2>Iniciar Sesión</h2>";

       

echo "  <form method='POST' action=''>
            <input type='email' name='mail' placeholder='Correo electrónico' required>
            <input type='password' name='password' placeholder='Contraseña' required>
            <button type='submit'>Entrar</button>
        </form>
    </div>
</body>
</html>";
?>

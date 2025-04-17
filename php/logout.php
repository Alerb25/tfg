<?php
// Iniciar sesión
session_start();

//Eliminar variables de sesión
session_unset();

//Destruir sesión
session_destroy();

header("Location: login.php");
exit();

?>
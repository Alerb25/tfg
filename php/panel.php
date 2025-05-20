<?php
session_start();

// Conexión a la base de datos
$conexion = pg_connect("host=127.0.0.1 port=5432 dbname=proyecto user=proyecto password=proyecto");
if (!$conexion) {
    die("Error de conexión con la base de datos");
}

// Verificar sesión
if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit();
}

$id_user = intval($_SESSION["id_user"]);
$nombre = $_SESSION["Nombre"];

// Guardar nueva nota
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["guardar"])) {
    $contenido = pg_escape_string($conexion, $_POST["nota"]);
    $fecha = date("Y-m-d");

    $insertar = "INSERT INTO nota (contenido, fecha_creado, fecha_editado, id_user) VALUES ('$contenido', '$fecha', '$fecha', $id_user)";
    if (pg_query($conexion, $insertar)) {
        header("Location: panel.php");
        exit();
    } else {
        $error = "Error al guardar la nota.";
    }
}

// Obtener notas propias y compartidas
$consulta = "
SELECT DISTINCT n.* 
FROM nota n
LEFT JOIN compartir c ON n.id_notes = c.id_notes
WHERE n.id_user = $id_user OR c.id_user = $id_user
ORDER BY n.fecha_creado DESC;
";
$resultado = pg_query($conexion, $consulta);

$notas = [];
if ($resultado) {
    while ($fila = pg_fetch_assoc($resultado)) {
        $notas[] = $fila;
    }
} else {
    $error = "Error al obtener las notas.";
}

// HTML
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Panel de notas - App de notas</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; display: flex; flex-direction: column; align-items: center; padding-top: 30px; }
        .box { background: white; padding: 20px 100px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 400px; margin-bottom: 20px; }
        input, textarea { width: 100%; padding: 10px; margin: 8px 0; }
        button { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
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

if (!empty($error)) {
    echo "<p style='color:red;'>$error</p>";
} elseif (count($notas) > 0) {
    foreach ($notas as $nota) {
        echo "<div class='nota'>
            <p>" . htmlspecialchars($nota['contenido']) . "</p>
            <p><em>Creada: {$nota['fecha_creado']}</em></p>
            <button onclick='abrirModal({$nota['id_notes']})'>Compartir</button>
            <br><br>
            <form id='formBorrar{$nota['id_notes']}' method='POST'>
                <input type='hidden' name='id_note' value='{$nota['id_notes']}'>
                <button type='button' onclick='borrarNota({$nota['id_notes']})'>Borrar</button>
            </form>
            <br>
            <button onclick='editarNota({$nota['id_notes']})'>Editar</button>
        </div>";
    }
} else {
    echo "<p>No hay notas creadas.</p>";
}

echo "
    </div>

    <!-- Modal Compartir -->
    <div id='modalCompartir' style='display:none; position:fixed; top:20%; left:50%; transform:translateX(-50%);
        background:#fff; padding:20px; border-radius:8px; box-shadow:0 0 10px #999; z-index:1000;'>
        <h3>Compartir nota</h3>
        <form id='formCompartir'>
            <input type='hidden' name='id_note' id='id_note_modal'>
            <input type='email' name='email_usuario' placeholder='Correo del usuario' required> 
            <select name='permisos'>
                <option value='lectura'>Lectura</option>
                <option value='edicion'>Edición</option>
            </select>
            <button type='submit'>Compartir</button>
            <button type='button' onclick='cerrarModal()'>Cancelar</button>
        </form>
        <div id='respuestaAjax' style='margin-top:10px;'></div>
    </div>

    <!-- Modal Editar -->
    <div id='modalEditar' style='display:none; position:fixed; top:20%; left:50%; transform:translateX(-50%);
        background:#fff; padding:20px 50px; border-radius:8px; box-shadow:0 0 10px #999; z-index:1000;'>
        <h3>Editar nota</h3>
        <form id='formEditar'>
            <input type='hidden' name='id_note' id='editar_id_note'>
            <textarea name='contenido' id='editar_contenido' rows='5' style='width:100%;' required></textarea>
            <br>
            <button type='submit'>Guardar cambios</button>
            <button type='button' onclick='cerrarModalEditar()'>Cancelar</button>
        </form>
        <div id='respuestaEditar' style='margin-top:10px;'></div>
    </div>

    <a href='logout.php'>Cerrar sesión</a>

    <script>
        function abrirModal(idNota) {
            document.getElementById('id_note_modal').value = idNota;
            document.getElementById('modalCompartir').style.display = 'block';
        }

        function cerrarModal() {
            document.getElementById('modalCompartir').style.display = 'none';
            document.getElementById('respuestaAjax').innerText = '';
        }

        function borrarNota(idNota) {
            if (confirm('¿Estás seguro de que quieres borrar esta nota?')) {
                const form = document.getElementById('formBorrar' + idNota);
                const formData = new FormData(form);

                fetch('borrarNota.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(() => location.reload())
                .catch(() => alert('Error al borrar la nota.'));
            }
        }

        function editarNota(idNota) {
            fetch('/php/obtenerNota.php?id_note=' + idNota)
                .then(res => res.json())
                .then(data => {
                    if (data && data.contenido !== undefined) {
                        document.getElementById('editar_id_note').value = idNota;
                        document.getElementById('editar_contenido').value = data.contenido;
                        document.getElementById('modalEditar').style.display = 'block';
                    } else {
                        alert('No se pudo cargar el contenido.');
                    }
                })
                .catch(() => alert('Error al cargar la nota.'));
        }

        function cerrarModalEditar() {
            document.getElementById('modalEditar').style.display = 'none';
            document.getElementById('respuestaEditar').innerText = '';
        }

        document.getElementById('formEditar').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('actualizarNota.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(data => {
                document.getElementById('respuestaEditar').innerText = data;
                setTimeout(() => location.reload(), 1000);
            })
            .catch(() => {
                document.getElementById('respuestaEditar').innerText = 'Error al editar.';
            });
        });

        document.getElementById('formCompartir').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('compartir.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(data => {
                document.getElementById('respuestaAjax').innerText = data;
            })
            .catch(() => {
                document.getElementById('respuestaAjax').innerText = 'Error al compartir.';
            });
        });
    </script>
</body>
</html>";
?>

<?php
session_start();

$conexion = pg_connect("host=127.0.0.1 port=5432 dbname=proyecto user=proyecto password=proyecto");
if (!$conexion) {
    die("Error al conectar con la base de datos.");
}

if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit();
}

$id_user = intval($_SESSION["id_user"]);
$nombre = $_SESSION["Nombre"];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["guardar"])) {
    $titulo = pg_escape_string($conexion, $_POST["titulo"]);
    $contenido = pg_escape_string($conexion, $_POST["nota"]);
    $etiquetas = explode(',', $_POST["etiquetas"]);
    $fecha = date("Y-m-d");

    $insertar = "INSERT INTO nota (contenido, titulo, fecha_creado, fecha_editado, id_user) 
                 VALUES ('$contenido', '$titulo', '$fecha', '$fecha', $id_user)";
    if (pg_query($conexion, $insertar)) {
        $res = pg_query($conexion, "SELECT currval(pg_get_serial_sequence('nota','id_notes')) AS id_notes");
        if ($res && ($row = pg_fetch_assoc($res))) {
            $id_nota_nueva = $row['id_notes'];
            foreach ($etiquetas as $etiqueta) {
                $nombre_etiqueta = trim($etiqueta);
                if ($nombre_etiqueta !== "") {
                    $nombre_etiqueta_esc = pg_escape_string($conexion, $nombre_etiqueta);
                    pg_query($conexion, "INSERT INTO etiqueta (nombre, id_note) VALUES ('$nombre_etiqueta_esc', $id_nota_nueva)");
                }
            }
            header("Location: panel.php");
            exit();
        }
    } else {
        $error = "Error al guardar la nota.";
    }
}

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Panel de Notas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .box { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; border-radius: 8px; }
        .nota { background: #f9f9f9; padding: 10px; border-radius: 6px; margin-bottom: 10px; }
        textarea { width: 100%; height: 100px; }
        button { margin-right: 5px; }
    </style>
</head>
<body>";

echo "<h2>¡Hola!, $nombre</h2>";

echo "<div class='box'>
<form method='POST'>
    <input type='text' name='titulo' placeholder='Título de la nota' required><br><br>
    <textarea name='nota' placeholder='Escribe tu nota aquí...' required></textarea><br><br>
    <input type='text' name='etiquetas' placeholder='Etiquetas separadas por comas (opcional)'><br><br>
    <button type='submit' name='guardar'>Guardar</button>
</form>
</div>";

$consultaPropias = "SELECT * FROM nota WHERE id_user = $id_user ORDER BY fecha_creado DESC";
$resPropias = pg_query($conexion, $consultaPropias);
$notasPropias = [];

while ($fila = pg_fetch_assoc($resPropias)) {
    $id_nota = $fila['id_notes'];
    $res_etiquetas = pg_query($conexion, "SELECT nombre FROM etiqueta WHERE id_note = $id_nota");
    $etiquetas = [];

    if ($res_etiquetas) {
        while ($et = pg_fetch_assoc($res_etiquetas)) {
            $etiquetas[] = $et['nombre'];
        }
    }

    $fila['etiquetas'] = $etiquetas;
    $notasPropias[] = $fila;
}

echo "<div class='box'>
<h3>Mis Notas</h3>";

foreach ($notasPropias as $nota) {
    echo "<div class='nota'>
        <p><strong>{$nota['titulo']}</strong></p>
        <p>{$nota['contenido']}</p>
        <p><strong>Etiquetas:</strong> " . implode(', ', $nota['etiquetas']) . "</p>
        <p><em>Creada: {$nota['fecha_creado']}</em></p>
        <button onclick='abrirModal({$nota['id_notes']})'>Compartir</button>
        <form id='formBorrar{$nota['id_notes']}' method='POST'>
            <input type='hidden' name='id_note' value='{$nota['id_notes']}'>
            <button type='button' onclick='borrarNota({$nota['id_notes']})'>Borrar</button>
        </form>
        <button onclick='editarNota({$nota['id_notes']})'>Editar</button>
    </div>";
}

echo "</div>";

$notasCompartidas = [];
$consultaCompartidas = "
SELECT n.*, u.nombre AS autor
FROM nota n
JOIN compartir c ON n.id_notes = c.id_note
JOIN usuario u ON u.id_user = n.id_user
WHERE c.id_user = $id_user AND n.id_user != $id_user
ORDER BY n.fecha_creado DESC;
";

$resCompartidas = pg_query($conexion, $consultaCompartidas);
if ($resCompartidas) {
    echo "<div class='box'>
    <h3>Notas Compartidas Conmigo</h3>";
    while ($fila = pg_fetch_assoc($resCompartidas)) {
        echo "<div class='nota'>
            <p>{$fila['contenido']}</p>
            <p><em>Autor: {$fila['autor']} | Creada: {$fila['fecha_creado']}</em></p>
        </div>";
    }
    echo "</div>";
}

echo "<div id='modalCompartir' style='display:none; position:fixed; top:20%; left:50%; transform:translateX(-50%);
background:#fff; padding:20px; border-radius:8px; box-shadow:0 0 10px #999; z-index:1000;'>
    <h3>Compartir nota</h3>
    <form id='formCompartir'>
        <input type='hidden' name='id_note' id='id_note_modal'>
        <input type='email' name='email_usuario' id='correo_destino' placeholder='Correo del usuario' required>
        <select name='permisos'>
            <option value='lectura'>Lectura</option>
            <option value='edicion'>Edición</option>
        </select>
        <button type='submit'>Compartir</button>
        <button type='button' onclick='cerrarModal()'>Cancelar</button>
    </form>
    <div id='respuestaAjax'></div>
</div>

<div id='modalEditar' style='display:none; position:fixed; top:20%; left:50%; transform:translateX(-50%);
background:#fff; padding:20px 50px; border-radius:8px; box-shadow:0 0 10px #999; z-index:1000;'>
    <h3>Editar nota</h3>
    <form id='formEditar'>
        <input type='hidden' name='id_note' id='editar_id_note'>
        <textarea name='contenido' id='editar_contenido' rows='5' style='width:100%;' required></textarea><br>
        <button type='submit'>Guardar cambios</button>
        <button type='button' onclick='cerrarModalEditar()'>Cancelar</button>
    </form>
    <div id='respuestaEditar'></div>
</div>";

echo "<a href='logout.php'>Cerrar sesión</a>";

echo "<script>
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
        }).then(() => location.reload())
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

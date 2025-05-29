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
                    pg_query($conexion, "INSERT INTO etiqueta (nombre, id_notes) VALUES ('$nombre_etiqueta_esc', $id_nota_nueva)");
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
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background-color: #fafafa;
        color: #2c3e50;
        line-height: 1.6;
        padding: 20px;
        font-size: 15px;
    }
    
    /* Contenedor principal */
    .container {
        max-width: 900px;
        margin: 0 auto;
    }
    
    /* Título principal */
    h2 {
        color: #2c3e50;
        font-size: 2rem;
        font-weight: 400;
        margin-bottom: 2rem;
        letter-spacing: -0.5px;
    }
    
    /* Contenedores principales */
    .box {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        border: 1px solid #e9ecef;
        transition: box-shadow 0.2s ease;
    }
    
    .box:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    
    /* Títulos de sección */
    h3 {
        color: #2c3e50;
        font-size: 1.3rem;
        font-weight: 500;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #f8f9fa;
    }
    
    /* Formularios */
    form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    input[type='text'], 
    input[type='email'], 
    textarea, 
    select {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 0.875rem 1rem;
        font-size: 14px;
        transition: all 0.2s ease;
        outline: none;
        font-family: inherit;
    }
    
    input[type='text']:focus, 
    input[type='email']:focus, 
    textarea:focus, 
    select:focus {
        background: white;
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }
    
    textarea {
        min-height: 100px;
        resize: vertical;
        line-height: 1.5;
    }
    
    /* Botones */
    button {
        background: #3498db;
        border: none;
        border-radius: 6px;
        padding: 0.7rem 1.2rem;
        color: white;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
        font-family: inherit;
    }
    
    button:hover {
        background: #2980b9;
        transform: translateY(-1px);
    }
    
    button:active {
        transform: translateY(0);
    }
    
    /* Botones específicos */
    button[onclick*='borrar'] {
        background: #e74c3c;
    }
    
    button[onclick*='borrar']:hover {
        background: #c0392b;
    }
    
    button[onclick*='editar'] {
        background: #f39c12;
    }
    
    button[onclick*='editar']:hover {
        background: #e67e22;
    }
    
    button[onclick*='compartir'] {
        background: #27ae60;
    }
    
    button[onclick*='compartir']:hover {
        background: #229954;
    }
    
    /* Botones secundarios */
    button[type='button'] {
        background: #95a5a6;
    }
    
    button[type='button']:hover {
        background: #7f8c8d;
    }
    
    /* Notas individuales */
    .nota {
        background: #fdfdfd;
        border: 1px solid #f1f3f4;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.2s ease;
        position: relative;
    }
    
    .nota:hover {
        border-color: #e9ecef;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }
    
    .nota p {
        margin-bottom: 0.75rem;
    }
    
    .nota p:first-child {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 1rem;
    }
    
    .nota p:last-child {
        margin-bottom: 1rem;
    }
    
    .nota em {
        color: #7f8c8d;
        font-size: 13px;
    }
    
    /* Etiquetas */
    .nota p:contains('Etiquetas') {
        font-size: 14px;
        color: #5d6d7e;
    }
    
    /* Modales */
    #modalCompartir, #modalEditar {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        border: 1px solid #e9ecef;
        min-width: 400px;
        z-index: 1000;
    }
    
    /* Formularios en modales */
    #modalCompartir form, #modalEditar form {
        gap: 1.2rem;
    }
    
    #modalCompartir h3, #modalEditar h3 {
        margin-bottom: 1.5rem;
        border-bottom: none;
        font-size: 1.2rem;
    }
    
    /* Botones en modales */
    #modalCompartir button, #modalEditar button {
        margin-right: 0.75rem;
    }
    
    /* Enlaces */
    a {
        display: inline-block;
        color: #3498db;
        text-decoration: none;
        font-weight: 500;
        padding: 0.7rem 1.2rem;
        background: #f8f9fa;
        border-radius: 6px;
        transition: all 0.2s ease;
        margin-top: 1rem;
        border: 1px solid #e9ecef;
    }
    
    a:hover {
        background: #e9ecef;
        color: #2980b9;
    }
    
    /* Mensajes de respuesta */
    #respuestaAjax, #respuestaEditar {
        margin-top: 1rem;
        padding: 0.75rem;
        border-radius: 6px;
        font-size: 14px;
    }
    
    /* Estados de carga */
    .loading {
        opacity: 0.6;
        pointer-events: none;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        body {
            padding: 15px;
            font-size: 14px;
        }
        
        .box {
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        h2 {
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
        }
        
        .nota {
            padding: 1.25rem;
        }
        
        #modalCompartir, #modalEditar {
            min-width: 90vw;
            left: 5vw;
            transform: translateX(0);
            padding: 1.5rem;
        }
        
        button {
            width: 100%;
            margin-right: 0;
            margin-bottom: 0.75rem;
        }
        
        .nota button {
            width: auto;
            display: inline-block;
        }
    }
    
    /* Mejoras para accesibilidad */
    button:focus {
        outline: 2px solid #3498db;
        outline-offset: 2px;
    }
    
    input:focus, textarea:focus, select:focus {
        outline: none;
    }
    
    /* Scrollbar sutil */
    ::-webkit-scrollbar {
        width: 6px;
    }
    
    ::-webkit-scrollbar-track {
        background: #f8f9fa;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #dee2e6;
        border-radius: 3px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: #ced4da;
    }
    
    /* Estados vacíos */
    .empty-state {
        text-align: center;
        color: #7f8c8d;
        font-style: italic;
        padding: 2rem;
    }
    
    /* Separadores sutiles */
    .divider {
        height: 1px;
        background: #f1f3f4;
        margin: 1.5rem 0;
    }
    
    /* Animación suave para elementos que aparecen */
    .fade-in {
        animation: fadeIn 0.3s ease-out;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
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
    $res_etiquetas = pg_query($conexion, "SELECT nombre FROM etiqueta WHERE id_notes = $id_nota");
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
            <input type='hidden' name='id_notes' value='{$nota['id_notes']}'>
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
JOIN compartir c ON n.id_notes = c.id_notes
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
        <input type='hidden' name='id_notes' id='id_notes_modal'>
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
        <input type='hidden' name='id_notes' id='editar_id_notes'>
        <textarea name='contenido' id='editar_contenido' rows='5' style='width:100%;' required></textarea><br>
        <button type='submit'>Guardar cambios</button>
        <button type='button' onclick='cerrarModalEditar()'>Cancelar</button>
    </form>
    <div id='respuestaEditar'></div>
</div>";

echo "<a href='logout.php'>Cerrar sesión</a>";

echo "<script>
function abrirModal(idNota) {
    document.getElementById('id_notes_modal').value = idNota;
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
    fetch('/php/obtenerNota.php?id_notes=' + idNota)
        .then(res => res.json())
        .then(data => {
            console.log('Respuesta del servidor:', data); // <-- Mira qué llega
            if (data && data.contenido !== undefined) {
                document.getElementById('editar_id_notes').value = idNota;
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

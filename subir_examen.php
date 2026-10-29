<?php
session_start();
// Solo los médicos pueden subir exámenes
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] == 'paciente') {
    header("Location: login.php");
    exit();
}

require 'db.php';
$mensaje = '';

// Definir y crear la carpeta donde se guardarán los archivos
$directorio = 'uploads/examenes/';
if (!is_dir($directorio)) {
    mkdir($directorio, 0777, true);
}

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $paciente_id = $_POST['paciente_id'];
    $titulo = $_POST['titulo_documento'];
    $tipo = $_POST['tipo_archivo'];
    $stmt_medico = $conn->prepare("SELECT id FROM medicos WHERE usuario_id = :uid");
    $stmt_medico->execute([':uid' => $_SESSION['user_id']]);
    $perfil_medico = $stmt_medico->fetch(PDO::FETCH_ASSOC);
    
    if (!$perfil_medico) {
        $mensaje = "<div class='notification is-danger'>Error: Tu cuenta no tiene un perfil de médico asociado.</div>";
    } else {
        $medico_id = $perfil_medico['id'];
    }

    // Verificar si se subió un archivo sin errores
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $nombre_original = $_FILES['archivo']['name'];
        $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
        $formatos_permitidos = ['pdf', 'jpg', 'jpeg', 'png'];

        if (in_array($extension, $formatos_permitidos)) {
            // Crear un nombre único (ej. 65a9f8b_examen_sangre.pdf)
            $nombre_unico = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9.-]/", "_", $nombre_original);
            $ruta_final = $directorio . $nombre_unico;

            // Mover el archivo temporal de PHP a nuestra carpeta uploads
            if (move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_final)) {
                try {
                    $sql = "INSERT INTO archivos_medicos (paciente_id, medico_id, titulo_documento, tipo_archivo, ruta_archivo) 
                            VALUES (:pid, :mid, :titulo, :tipo, :ruta)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ':pid' => $paciente_id,
                        ':mid' => $medico_id,
                        ':titulo' => $titulo,
                        ':tipo' => $tipo,
                        ':ruta' => $ruta_final
                    ]);
                    $mensaje = "<div class='notification is-success'>Archivo subido y guardado correctamente.</div>";
                } catch(PDOException $e) {
                    // Si falla la base de datos, borramos el archivo físico para no tener basura
                    unlink($ruta_final);
                    $mensaje = "<div class='notification is-danger'>Error de BD: " . $e->getMessage() . "</div>";
                }
            } else {
                $mensaje = "<div class='notification is-danger'>Error al guardar el archivo en el servidor.</div>";
            }
        } else {
            $mensaje = "<div class='notification is-warning'>Formato no permitido. Solo PDF, JPG y PNG.</div>";
        }
    } else {
        $mensaje = "<div class='notification is-danger'>Por favor selecciona un archivo válido.</div>";
    }
}

// Obtener lista de pacientes para el select
$stmt_pac = $conn->query("SELECT id, nombre, apellidos FROM pacientes");
$pacientes = $stmt_pac->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir Examen - Hospital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body class="has-background-light" style="min-height: 100vh;">

<nav class="navbar" role="navigation" aria-label="main navigation">
  <div class="navbar-brand">
     <h2 class="navbar-item"> Bienvenido, <?php echo htmlspecialchars($_SESSION['correo']); ?> </h2>
    </svg>

    </a>

    <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
    </a>
  </div>

  <div id="navbarBasicExample" class="navbar-menu">
    <div class="navbar-start">
      <a class="navbar-item" href="ver_citas.php">
        Inicio
      </a>

        <a class="navbar-item" href="ver_historial.php">
            Historial Clínico
        </a>

      <a class="navbar-item" href="nueva_cita.php">
        Nueva Cita
      </a>

      <a class="navbar-item" href="subir_examen.php">
        Subir Examen
      </a>

      <div class="navbar-item has-dropdown is-hoverable">
        <a class="navbar-link">
            Más opciones
        </a>

        <div class="navbar-dropdown">
          <a class="navbar-item is-selected">
            Jobs
          </a>
          <a class="navbar-item">
            Contact
          </a>
          <hr class="navbar-divider">
          <a class="navbar-item">
            Report an issue
          </a>
        </div>
      </div>
    </div>

    <div class="navbar-end">
      <div class="navbar-item">
        <div class="buttons">
          <a class="button is-danger" href="closed.php">
            <strong>Cerrar Sesión</strong>
          </a>
        </div>
      </div>
    </div>
  </div>
</nav>

    <section class="section is-flex is-justify-content-center">
        <div class="container" style="max-width: 600px;">
            <div class="box">
                <h2 class="title is-4 has-text-centered">Adjuntar Examen o Imagen</h2>
                
                <?= $mensaje ?>
                
                <!-- IMPORTANTE: enctype="multipart/form-data" es obligatorio para subir archivos -->
                <form method="POST" action="" enctype="multipart/form-data">
                    
                    <div class="field">
                        <label class="label">Paciente</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="paciente_id" required>
                                    <option value="">Seleccione el paciente...</option>
                                    <?php foreach ($pacientes as $pac): ?>
                                        <option value="<?= $pac['id'] ?>">
                                            <?= htmlspecialchars($pac['nombre'] . ' ' . $pac['apellidos']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Título del Documento</label>
                        <div class="control">
                            <input class="input" type="text" name="titulo_documento" placeholder="Ej. Radiografía de Tórax, Hemograma..." required>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Categoría</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="tipo_archivo" required>
                                    <option value="laboratorio">Laboratorio</option>
                                    <option value="imagen">Imagen (Rayos X, RM, etc.)</option>
                                    <option value="receta">Receta Médica</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Archivo (PDF, JPG, PNG)</label>
                        <div class="file has-name is-fullwidth is-info">
                            <label class="file-label">
                                <!-- Agregamos id="file-upload" -->
                                <input class="file-input" type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png" required id="file-upload">
                                <span class="file-cta">
                                    <span class="file-label">Elegir un archivo...</span>
                                </span>
                                <!-- Agregamos el contenedor para el nombre del archivo -->
                                <span class="file-name" id="file-name">
                                    Ningún archivo seleccionado
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="field mt-5">
                        <div class="control">
                            <button type="submit" class="button is-primary is-fullwidth">Subir Archivo</button>
                        </div>
                    </div>
                    
                    <div class="has-text-centered mt-3">
                        <a href="ver_historial.php" class="is-size-7">Ir al Historial Clínico</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Script para actualizar el nombre del archivo seleccionado -->
    <script>
        const fileInput = document.querySelector('#file-upload');
        const fileName = document.querySelector('#file-name');

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                fileName.textContent = fileInput.files[0].name;
            } else {
                fileName.textContent = 'Ningún archivo seleccionado';
            }
        });
    </script>

</body>
</html>
<?php
session_start();
// Validación de seguridad: si no hay sesión, regresa al login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';
$mensaje = '';

// 1. Obtener el ID real del paciente basado en su usuario logueado
$usuario_id = $_SESSION['user_id'];
$stmt_pac = $conn->prepare("SELECT id FROM pacientes WHERE usuario_id = :uid");
$stmt_pac->bindParam(':uid', $usuario_id);
$stmt_pac->execute();
$paciente = $stmt_pac->fetch(PDO::FETCH_ASSOC);
$paciente_id = $paciente ? $paciente['id'] : null;

// 2. Obtener la lista de médicos para el menú desplegable
$stmt_medicos = $conn->prepare("SELECT id, nombre, apellidos, especialidad FROM medicos");
$stmt_medicos->execute();
$medicos = $stmt_medicos->fetchAll(PDO::FETCH_ASSOC);

// 3. Procesar el formulario cuando el usuario hace clic en "Agendar"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $medico_id = $_POST['medico_id'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $motivo = $_POST['motivo'];

    if ($paciente_id) {
        try {
            $sql = "INSERT INTO citas (paciente_id, medico_id, fecha, hora, motivo, estado) 
                    VALUES (:pac_id, :med_id, :fecha, :hora, :motivo, 'programada')";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':pac_id', $paciente_id);
            $stmt->bindParam(':med_id', $medico_id);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->bindParam(':hora', $hora);
            $stmt->bindParam(':motivo', $motivo);
            
            if ($stmt->execute()) {
                $mensaje = "Cita programada con éxito. <a href='ver_citas.php'>Ver mis citas</a>";
            }
        } catch(PDOException $e) {
            $mensaje = "Error al programar la cita: " . $e->getMessage();
        }
    } else {
        $mensaje = "Error: Tu perfil aún no tiene datos de paciente.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Cita - Hospital</title>
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
          <a class="navbar-item">
            About
          </a>
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

    <!-- Aquí puedes colocar tu menú de navegación (<nav>) -->

    <section class="section is-flex is-justify-content-center">
        <!-- Limitamos el ancho a 600px para que el formulario no se vea gigante -->
        <div class="container" style="max-width: 600px;">
            <div class="box">
                <h2 class="title is-4 has-text-centered">Programar Nueva Cita</h2>
                
                <?php if(!empty($mensaje)) echo "<div class='notification is-info is-light'>$mensaje</div>"; ?>
                
                <form method="POST" action="">
                    
                    <!-- Selección dinámica del Médico -->
                    <div class="field">
                        <label class="label">Médico Especialista</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="medico_id" required>
                                    <option value="">Seleccione un médico...</option>
                                    <?php foreach ($medicos as $medico): ?>
                                        <option value="<?= $medico['id'] ?>">
                                            Dr/Dra. <?= htmlspecialchars($medico['nombre'] . ' ' . $medico['apellidos']) ?> 
                                            (<?= htmlspecialchars($medico['especialidad']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Columnas de Bulma para poner Fecha y Hora en la misma línea -->
                    <div class="columns is-mobile">
                        <div class="column">
                            <div class="field">
                                <label class="label">Fecha</label>
                                <div class="control">
                                    <input class="input" type="date" name="fecha" required>
                                </div>
                            </div>
                        </div>
                        <div class="column">
                            <div class="field">
                                <label class="label">Hora</label>
                                <div class="control">
                                    <input class="input" type="time" name="hora" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Motivo de la consulta</label>
                        <div class="control">
                            <textarea class="textarea" name="motivo" placeholder="Ej. Dolor de cabeza frecuente, chequeo anual..." required></textarea>
                        </div>
                    </div>

                    <div class="field mt-5">
                        <div class="control">
                            <button type="submit" class="button is-primary is-fullwidth">Agendar Cita</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </section>

</body>
</html>
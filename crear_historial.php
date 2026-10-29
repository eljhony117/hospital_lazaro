<?php
session_start();
// Validar que el usuario sea médico (o administrador)
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] == 'paciente') {
    header("Location: login.php");
    exit();
}

require 'db.php';
$mensaje = '';

// Obtener lista de pacientes para el menú desplegable
$stmt_pacientes = $conn->prepare("SELECT id, nombre, apellidos FROM pacientes");
$stmt_pacientes->execute();
$pacientes = $stmt_pacientes->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $paciente_id = $_POST['paciente_id'];
    
    // Datos del Historial Personal
    $alergias = $_POST['alergias'];
    $enfermedades = $_POST['enfermedades_previas'];
    $cirugias = $_POST['cirugias'];
    $vacunas = $_POST['vacunas'];
    $medicamentos = $_POST['medicamentos_actuales'];
    $habitos = $_POST['habitos_salud'];

    // Datos de Antecedentes Familiares
    $parentesco = $_POST['parentesco'];
    $enf_familiares = $_POST['enfermedades_familiares'];
    $estado_vital = $_POST['estado_vital'];

    try {
        // Iniciar transacción: Si falla un INSERT, no se guarda ninguno de los dos
        $conn->beginTransaction();

        // 1. Guardar Historial Médico Personal
        // Usamos ON DUPLICATE KEY UPDATE por si el paciente ya tenía un historial creado, para que solo se actualice
        $sql_historial = "INSERT INTO historial_medico 
            (paciente_id, alergias, enfermedades_previas, cirugias, vacunas, medicamentos_actuales, habitos_salud) 
            VALUES (:pid, :alergias, :enf, :cirugias, :vacunas, :med, :habitos)
            ON DUPLICATE KEY UPDATE 
            alergias = :alergias, enfermedades_previas = :enf, cirugias = :cirugias, 
            vacunas = :vacunas, medicamentos_actuales = :med, habitos_salud = :habitos";
        
        $stmt1 = $conn->prepare($sql_historial);
        $stmt1->execute([
            ':pid' => $paciente_id, ':alergias' => $alergias, ':enf' => $enfermedades, 
            ':cirugias' => $cirugias, ':vacunas' => $vacunas, ':med' => $medicamentos, ':habitos' => $habitos
        ]);

        // 2. Guardar Antecedente Familiar
        if (!empty($parentesco) && !empty($enf_familiares)) {
            $sql_familiar = "INSERT INTO antecedentes_familiares (paciente_id, parentesco, enfermedades_presentes_pasadas, estado_vital) 
                             VALUES (:pid, :parentesco, :enf_fam, :estado)";
            $stmt2 = $conn->prepare($sql_familiar);
            $stmt2->execute([
                ':pid' => $paciente_id, ':parentesco' => $parentesco, 
                ':enf_fam' => $enf_familiares, ':estado' => $estado_vital
            ]);
        }

        // Confirmar la transacción
        $conn->commit();
        $mensaje = "<div class='notification is-success'>Historial y antecedentes guardados correctamente.</div>";

    } catch(PDOException $e) {
        // Si hay error, revertimos todos los cambios
        $conn->rollBack();
        $mensaje = "<div class='notification is-danger'>Error al guardar: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Historial Médico</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body class="has-background-light">

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


    <section class="section">
        <div class="container" style="max-width: 800px;">
            <div class="box">
                <h2 class="title is-4 has-text-centered has-text-info">Gestión de Historial Médico y Antecedentes</h2>
                
                <?= $mensaje ?>
                
                <form method="POST" action="">
                    <!-- Selección del Paciente -->
                    <div class="field">
                        <label class="label">Seleccionar Paciente</label>
                        <div class="control">
                            <div class="select is-fullwidth is-info">
                                <select name="paciente_id" required>
                                    <option value="">Seleccione un paciente...</option>
                                    <?php foreach ($pacientes as $pac): ?>
                                        <option value="<?= $pac['id'] ?>">
                                            <?= htmlspecialchars($pac['nombre'] . ' ' . $pac['apellidos']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h3 class="subtitle is-5 has-text-weight-bold">1. Historial Personal</h3>
                    
                    <div class="columns is-multiline">
                        <div class="column is-half">
                            <div class="field">
                                <label class="label">Alergias</label>
                                <div class="control">
                                    <textarea class="textarea" name="alergias" placeholder="Ej. Penicilina, polen..." rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="column is-half">
                            <div class="field">
                                <label class="label">Enfermedades Previas</label>
                                <div class="control">
                                    <textarea class="textarea" name="enfermedades_previas" placeholder="Ej. Hipertensión, asma..." rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="column is-half">
                            <div class="field">
                                <label class="label">Cirugías</label>
                                <div class="control">
                                    <textarea class="textarea" name="cirugias" placeholder="Ej. Apendicectomía (2015)..." rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="column is-half">
                            <div class="field">
                                <label class="label">Vacunas Recientes</label>
                                <div class="control">
                                    <textarea class="textarea" name="vacunas" placeholder="Ej. COVID-19, Influenza..." rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="column is-half">
                            <div class="field">
                                <label class="label">Medicamentos Actuales</label>
                                <div class="control">
                                    <textarea class="textarea" name="medicamentos_actuales" placeholder="Ej. Losartán 50mg..." rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="column is-half">
                            <div class="field">
                                <label class="label">Hábitos de Salud</label>
                                <div class="control">
                                    <textarea class="textarea" name="habitos_salud" placeholder="Ej. Fuma, bebe ocasionalmente..." rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h3 class="subtitle is-5 has-text-weight-bold">2. Agregar Antecedente Familiar</h3>
                    
                    <div class="columns">
                        <div class="column is-one-third">
                            <div class="field">
                                <label class="label">Parentesco</label>
                                <div class="control">
                                    <input class="input" type="text" name="parentesco" placeholder="Ej. Madre, Abuelo paterno...">
                                </div>
                            </div>
                        </div>
                        <div class="column is-one-third">
                            <div class="field">
                                <label class="label">Estado Vital</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select name="estado_vital">
                                            <option value="vivo">Vivo</option>
                                            <option value="fallecido">Fallecido</option>
                                            <option value="desconocido">Desconocido</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="column is-one-third">
                            <div class="field">
                                <label class="label">Enfermedad Hereditaria</label>
                                <div class="control">
                                    <input class="input" type="text" name="enfermedades_familiares" placeholder="Ej. Diabetes Tipo 2">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="field mt-5">
                        <div class="control">
                            <button type="submit" class="button is-info is-fullwidth is-medium">Guardar Historial Clínico</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</body>
</html> 
<?php
    session_start();
    // Si no existe la sesión del usuario, lo devuelve al login
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // A partir de aquí mantienes tu código: require 'db.php'; y el SQL...
?>



<?php
    // 1. Incluir el archivo de conexión (el db.php que creamos antes)
    require 'db.php';

    $citas = []; // Arreglo para guardar los resultados

    try {
        // 2. Preparar la consulta SQL
        // 1. Obtienes el ID del usuario activo desde la sesión
        $usuario_id = $_SESSION['user_id'];

        // 2. Modificas la consulta agregando "AND p.usuario_id = :user_id"
        $sql = "SELECT 
                    c.fecha,
                    c.hora,
                    CONCAT(p.nombre, ' ', p.apellidos) AS paciente,
                    CONCAT('Dr/Dra. ', m.nombre, ' ', m.apellidos) AS medico,
                    m.especialidad,
                    c.motivo
                FROM citas c
                INNER JOIN pacientes p ON c.paciente_id = p.id
                INNER JOIN medicos m ON c.medico_id = m.id
                WHERE c.estado = 'programada' AND p.usuario_id = :user_id
                ORDER BY c.fecha ASC, c.hora ASC";
                
        $stmt = $conn->prepare($sql);
        
        // 3. Pasas el ID del usuario a la consulta para mayor seguridad
        $stmt->bindParam(':user_id', $usuario_id);
        $stmt->execute();
        
        $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch(PDOException $e) {
        die("Error al obtener las citas: " . $e->getMessage());
    }
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Citas Programadas - Hospital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body>

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

<?php if ($_SESSION['rol'] == 'medico' || $_SESSION['rol'] == 'admin'): ?>
    <div class="container mt-4">
        <a href="crear_historial.php" class="button is-info is-light">
            <strong>+ Registrar Historial Clínico</strong>
        </a>
    </div>
<?php endif; ?>

<!-- Contenedor principal para separar el contenido del navbar -->
<section class="section">
    <div class="container">
        <!-- Tarjeta blanca con sombra para resaltar sobre el fondo gris -->
        <div class="box">
            <h1 class="title is-4 has-text-centered">Resumen de Próximas Citas</h1>
            
            <!-- Contenedor responsivo para que la tabla no se desborde en móviles -->
            <div class="table-container mt-4">
                <!-- Clases de Bulma para una tabla de ancho completo, con filas rayadas y efecto hover -->
                <table class="table is-fullwidth is-striped is-hoverable is-bordered">
                    <thead>
                        <tr class="has-background-info">
                            <th class="has-text-white">Fecha</th>
                            <th class="has-text-white">Hora</th>
                            <th class="has-text-white">Paciente</th>
                            <th class="has-text-white">Médico</th>
                            <th class="has-text-white">Especialidad</th>
                            <th class="has-text-white">Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($citas) && count($citas) > 0): ?>
                            <?php foreach ($citas as $cita): ?>
                                <tr>
                                    <td><?= htmlspecialchars($cita['fecha']) ?></td>
                                    <td><?= htmlspecialchars($cita['hora']) ?></td>
                                    <td><?= htmlspecialchars($cita['paciente']) ?></td>
                                    <td><?= htmlspecialchars($cita['medico']) ?></td>
                                    <td><?= htmlspecialchars($cita['especialidad']) ?></td>
                                    <td><?= htmlspecialchars($cita['motivo']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="has-text-centered">No hay citas programadas en este momento.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>


<script src="javascript/main.js"></script>
</body>
</html>
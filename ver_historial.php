<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';

$rol = $_SESSION['rol'];
$paciente_id = null;
$paciente_info = null;
$historial = null;
$familiares = [];

// 1. Determinar de qué paciente vamos a buscar la información
if ($rol == 'paciente') {
    // Si es paciente, buscamos SU propio ID
    $stmt = $conn->prepare("SELECT id FROM pacientes WHERE usuario_id = :uid");
    $stmt->execute([':uid' => $_SESSION['user_id']]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($res) {
        $paciente_id = $res['id'];
    }
} elseif (isset($_GET['paciente_id']) && !empty($_GET['paciente_id'])) {
    // Si es médico y seleccionó un paciente en el menú
    $paciente_id = $_GET['paciente_id'];
}

// 2. Si ya tenemos un ID de paciente, extraemos toda su información
if ($paciente_id) {
    // Datos personales
    $stmt_info = $conn->prepare("SELECT nombre, apellidos, fecha_nacimiento FROM pacientes WHERE id = :pid");
    $stmt_info->execute([':pid' => $paciente_id]);
    $paciente_info = $stmt_info->fetch(PDO::FETCH_ASSOC);

    // Historial Médico Personal
    $stmt_historial = $conn->prepare("SELECT * FROM historial_medico WHERE paciente_id = :pid");
    $stmt_historial->execute([':pid' => $paciente_id]);
    $historial = $stmt_historial->fetch(PDO::FETCH_ASSOC);

    // Antecedentes Familiares
    $stmt_fam = $conn->prepare("SELECT * FROM antecedentes_familiares WHERE paciente_id = :pid");
    $stmt_fam->execute([':pid' => $paciente_id]);
    $familiares = $stmt_fam->fetchAll(PDO::FETCH_ASSOC);

    // NUEVO: Consultar los archivos adjuntos
    $stmt_archivos = $conn->prepare("SELECT titulo_documento, tipo_archivo, ruta_archivo, creado_en FROM archivos_medicos WHERE paciente_id = :pid ORDER BY creado_en DESC");
    $stmt_archivos->execute([':pid' => $paciente_id]);
    $archivos = $stmt_archivos->fetchAll(PDO::FETCH_ASSOC);
}

// 3. (Solo para médicos) Obtener lista de pacientes para el menú de búsqueda
$lista_pacientes = [];
if ($rol == 'medico' || $rol == 'admin') {
    $stmt_lista = $conn->query("SELECT id, nombre, apellidos FROM pacientes");
    $lista_pacientes = $stmt_lista->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Historial Clínico</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body class="has-background-light" style="min-height: 100vh; padding: 20px;">

    <div class="container" style="max-width: 900px;">
        
        <!-- Controles para el Médico -->
        <?php if ($rol == 'medico' || $rol == 'admin'): ?>
            <div class="box has-background-white-ter">
                <form method="GET" action="">
                    <div class="field is-grouped">
                        <div class="control is-expanded">
                            <div class="select is-fullwidth is-info">
                                <select name="paciente_id" required>
                                    <option value="">Seleccione un paciente para ver su expediente...</option>
                                    <?php foreach ($lista_pacientes as $pac): ?>
                                        <option value="<?= $pac['id'] ?>" <?= ($paciente_id == $pac['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($pac['nombre'] . ' ' . $pac['apellidos']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="control">
                            <button type="submit" class="button is-info">Buscar Historial</button>
                        </div>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Despliegue del Expediente -->
        <?php if ($paciente_id && $paciente_info): ?>
            
            <h1 class="title is-3 has-text-centered mb-5">Expediente Clínico</h1>
            
            <!-- Tarjeta de Datos del Paciente -->
            <div class="card mb-5">
                <header class="card-header has-background-primary">
                    <p class="card-header-title has-text-white">Datos del Paciente</p>
                </header>
                <div class="card-content">
                    <p><strong>Nombre completo:</strong> <?= htmlspecialchars($paciente_info['nombre'] . ' ' . $paciente_info['apellidos']) ?></p>
                    
                    <?php
                        // Cálculo dinámico de la edad
                        $edad = 'No registrada';
                        if (!empty($paciente_info['fecha_nacimiento'])) {
                            $fecha_nac = new DateTime($paciente_info['fecha_nacimiento']);
                            $hoy = new DateTime();
                            $edad = $hoy->diff($fecha_nac)->y . ' años';
                        }
                    ?>
                    
                    <p><strong>Fecha de nacimiento:</strong> <?= htmlspecialchars($paciente_info['fecha_nacimiento'] ?? 'No registrada') ?> <em>(Edad: <?= $edad ?>)</em></p>
                </div>
            </div>

            <!-- Tarjeta de Historial Personal -->
            <div class="card mb-5">
                <header class="card-header has-background-info">
                    <p class="card-header-title has-text-white">Historial Personal</p>
                </header>
                <div class="card-content">
                    <?php if ($historial): ?>
                        <div class="columns is-multiline">
                            <div class="column is-half"><p><strong>Alergias:</strong><br><?= nl2br(htmlspecialchars($historial['alergias'])) ?></p></div>
                            <div class="column is-half"><p><strong>Enfermedades previas:</strong><br><?= nl2br(htmlspecialchars($historial['enfermedades_previas'])) ?></p></div>
                            <div class="column is-half"><p><strong>Cirugías:</strong><br><?= nl2br(htmlspecialchars($historial['cirugias'])) ?></p></div>
                            <div class="column is-half"><p><strong>Vacunas:</strong><br><?= nl2br(htmlspecialchars($historial['vacunas'])) ?></p></div>
                            <div class="column is-half"><p><strong>Medicamentos actuales:</strong><br><?= nl2br(htmlspecialchars($historial['medicamentos_actuales'])) ?></p></div>
                            <div class="column is-half"><p><strong>Hábitos de salud:</strong><br><?= nl2br(htmlspecialchars($historial['habitos_salud'])) ?></p></div>
                        </div>
                        <p class="is-size-7 has-text-grey mt-4"><em>Última actualización: <?= $historial['ultima_actualizacion'] ?></em></p>
                    <?php else: ?>
                        <p class="has-text-grey">No hay registros personales para este paciente.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tarjeta de Antecedentes Familiares -->
            <div class="card mb-5">
                <header class="card-header has-background-warning">
                    <p class="card-header-title">Antecedentes Familiares</p>
                </header>
                <div class="card-content">
                    <?php if (count($familiares) > 0): ?>
                        <table class="table is-fullwidth is-striped">
                            <thead>
                                <tr>
                                    <th>Parentesco</th>
                                    <th>Enfermedad Hereditaria</th>
                                    <th>Estado Vital</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($familiares as $fam): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($fam['parentesco']) ?></td>
                                        <td><?= htmlspecialchars($fam['enfermedades_presentes_pasadas']) ?></td>
                                        <td><span class="tag is-light"><?= ucfirst(htmlspecialchars($fam['estado_vital'])) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="has-text-grey">No hay antecedentes familiares registrados.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tarjeta de Archivos y Exámenes -->
            <div class="card mb-5">
                <header class="card-header has-background-success">
                    <p class="card-header-title has-text-white">Exámenes y Archivos Adjuntos</p>
                </header>
                <div class="card-content">
                    <?php if (isset($archivos) && count($archivos) > 0): ?>
                        <div class="table-container">
                            <table class="table is-fullwidth is-hoverable">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Título</th>
                                        <th>Categoría</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($archivos as $archivo): ?>
                                        <tr>
                                            <!-- Formatear la fecha para que se lea mejor -->
                                            <td><?= date('d/m/Y', strtotime($archivo['creado_en'])) ?></td>
                                            <td><?= htmlspecialchars($archivo['titulo_documento']) ?></td>
                                            <td><span class="tag is-info is-light"><?= ucfirst(htmlspecialchars($archivo['tipo_archivo'])) ?></span></td>
                                            <td>
                                                <!-- Enlace directo al archivo abriendo en nueva pestaña -->
                                                <a href="<?= htmlspecialchars($archivo['ruta_archivo']) ?>" target="_blank" class="button is-small is-link is-outlined">
                                                    Ver Documento
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="has-text-grey">No hay archivos ni exámenes adjuntos para este paciente.</p>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($rol == 'paciente' && !$paciente_id): ?>
            <div class="notification is-warning">Aún no tienes un perfil de paciente vinculado a tu cuenta.</div>
        <?php endif; ?>

        <div class="has-text-centered mt-5">
            <a href="ver_citas.php" class="button is-link is-light">Volver al inicio</a>
        </div>

    </div>
</body>
</html>
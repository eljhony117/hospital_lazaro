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
                WHERE c.estado = 'programada'
                ORDER BY c.fecha ASC, c.hora ASC";
                
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        // 3. Extraer todos los registros como un arreglo asociativo
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
    <style>
        /* Estilos básicos para que la tabla se vea limpia */
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; max-width: 1000px; margin: 0 auto; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #007BFF; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>

    <h2 style="text-align: center;">Citas Médicas Programadas</h2>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Paciente</th>
                <th>Médico</th>
                <th>Especialidad</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($citas) > 0): ?>
                <!-- 4. Recorrer el arreglo con un foreach para imprimir cada fila -->
                <?php foreach ($citas as $cita): ?>
                    <tr>
                        <!-- Uso de htmlspecialchars para seguridad contra ataques XSS -->
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
                    <td colspan="6" style="text-align: center;">No hay citas programadas en este momento.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
<?php
require 'db.php';
$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['nombre_usuario'];
    $email = $_POST['correo'];
    // Encriptación segura de la contraseña
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT); 

    try {
        // 1. Crear las credenciales de acceso en la tabla usuarios
        $sql_user = "INSERT INTO usuarios (correo, password, rol) VALUES (:email, :pass, 'paciente')";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bindParam(':email', $email);
        $stmt_user->bindParam(':pass', $pass);
        $stmt_user->execute();
        
        // 2. Obtener el ID que MySQL le asignó a este nuevo usuario
        $id_usuario = $conn->lastInsertId();
        
        // 3. Crear el perfil del paciente vinculado a ese usuario
        // Nota: Como 'apellidos' es obligatorio en la BD, lo enviamos vacío por ahora 
        // hasta que agregues ese campo a tu formulario HTML
        $sql_paciente = "INSERT INTO pacientes (usuario_id, nombre, apellidos) VALUES (:id, :nombre, '')";
        $stmt_paciente = $conn->prepare($sql_paciente);
        $stmt_paciente->bindParam(':id', $id_usuario);
        $stmt_paciente->bindParam(':nombre', $user);
        $stmt_paciente->execute();
        
        $mensaje = "Registro exitoso. Puedes iniciar sesión.";
    } catch(PDOException $e) {
        $mensaje = "Error al registrar: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registro - Hospital</title>
    <!-- Asegúrate de tener el enlace a Bulma en tu head -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body>
    <!-- 1. Contenedor principal que ocupa toda la altura de la pantalla (100vh) y centra el contenido vertical y horizontalmente -->
    <section class="section is-flex is-align-items-center is-justify-content-center" style="min-height: 100vh; background-color: #f5f5f5;">
        <div class="container">
            <!-- 2. Fila centrada -->
            <div class="columns is-centered">
                <!-- 3. Columna que restringe el ancho (ocupa 1/3 de la pantalla en escritorio) -->
                <div class="column is-one-third-desktop is-half-tablet">
                    <!-- 4. La clase 'box' crea el cuadro con fondo blanco, bordes redondeados y sombra -->
                    <div class="box">
                        <h2 class="title is-4 has-text-centered">Registro de Pacientes</h2>
                        
                        <?php if(!empty($mensaje)) echo "<div class='notification is-info is-light'>$mensaje</div>"; ?>
                        
                        <form method="POST" action="">
                            <!-- Estructura de campos nativa de Bulma -->
                            <div class="field">
                                <label class="label">Usuario:</label>
                                <div class="control">
                                    <input class="input" type="text" name="nombre_usuario" required>
                                </div>
                            </div>
                            
                            <div class="field">
                                <label class="label">Correo:</label>
                                <div class="control">
                                    <input class="input" type="email" name="correo" placeholder="ejemplo@correo.com" required>
                                </div>
                            </div>
                            
                            <div class="field">
                                <label class="label">Contraseña:</label>
                                <div class="control">
                                    <input class="input" type="password" name="password" placeholder="********" required>
                                </div>
                            </div>
                            
                            <div class="field mt-5">
                                <div class="control">
                                    <button class="button is-primary is-fullwidth" type="submit">Registrarse</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
<?php
session_start();
require 'db.php';
$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['correo'];
    $pass = $_POST['password'];

    // Actualizamos la consulta para pedir solo las columnas que sí existen
    $sql = "SELECT id, correo, password, rol FROM usuarios WHERE correo = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificación de la contraseña
    if ($usuario && password_verify($pass, $usuario['password'])) {
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['correo'] = $usuario['correo']; // Guardamos el correo en lugar del nombre_usuario
        $_SESSION['rol'] = $usuario['rol'];
        
        header("Location: ver_citas.php"); // Redirigir a tu tabla
        exit();
    } else {
        $mensaje = "Correo o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Hospital</title>
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
                        <h2 class="title is-4 has-text-centered">Ingreso a Sistema</h2>
                        
                        <?php if(!empty($mensaje)) echo "<div class='notification is-info is-light'>$mensaje</div>"; ?>
                        
                        <form method="POST" action="">
                            <!-- Estructura de campos nativa de Bulma -->                            
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
                            <h1 class="is-size-6 has-text-centered"> ¿Aun no tienes cuenta? <a href="register.php">Registrate</a></h1>
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
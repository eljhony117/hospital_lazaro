<?php
// 1. Iniciar la sesión para poder identificarla
    session_start();

// 2. Liberar y destruir la sesión por completo
    session_unset(); 
    session_destroy(); 

// (Opcional) Si quieres que a los 3 segundos los regrese automáticamente al login, puedes usar esto:
// header("refresh:3;url=login.php");
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sesion Cerrada - Hospital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body>
    <section class="section is-flex is-align-items-center is-justify-content-center" style="min-height: 100vh; background-color: #f5f5f5;">
        <div class="container">
            <!-- 2. Fila centrada -->
            <div class="columns is-centered">
                <!-- 3. Columna que restringe el ancho (ocupa 1/3 de la pantalla en escritorio) -->
                <div class="column is-one-third-desktop is-half-tablet">
                    <!-- 4. La clase 'box' crea el cuadro con fondo blanco, bordes redondeados y sombra -->
                    <div class="box">
                        <h2 class="title is-4 has-text-centered">Sesion Finalizada</h2>
                        <div class="notification is-danger is-light">
                            <p>Has cerrado sesión correctamente.</p>
                            <p>Por favor, inicia sesión nuevamente para continuar.</p>
                        </div>
                        

                        <div class="field mt-5">
                            <div class="control">
                                <a class="button is-link is-fullwidth" href="index.html">Página Principal</a>
                            </div>
                            </div>
                        <div class="field mt-5">
                            <h2 class=" is-5 has-text-centered"> Si desea ingresar nuevamente, haga click en el siguiente boton</h2>
                        </div>
                        <div class="field mt-5">
                            <div class="control">
                                <a class="button is-primary is-fullwidth" href="login.php">Iniciar Sesión</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
</body>
</html>
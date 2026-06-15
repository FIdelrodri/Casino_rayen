<?php
 session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuarios</title>
    <link rel="stylesheet" href="Registro.css">
</head>
<body>

    <div class="contenedor-fondo"> 
        <!-- logo -->
        <div class="barra_superior">
            <img class="imagen_logo" src="../../../imagenes_y_3D/Imagenes/logo_casino_rayen.png" alt="">
        </div>
        <!-- el formulario del madafakin profe -->
        <div class="container">
            <h2>Registrar Nuevo Usuario</h2>
            <form action="registro.php" method="POST">    
                <!--  todo el form para ingresar datos -->
                <label>Ingrese nombre de usuario:</label>
                 <p></p>
                <input type="text" name="Nombre" placeholder="ej:juan123" required>
                 <!-- -->
                <label>Nombre:</label>
                <input type="text" name="Nombre_persona" placeholder="Federico" >
                <!-- -->
                 <label>Apellido:</label>
                <input type="text" name="apellido" placeholder="Bouson" >
                <!-- --> 
                <label>DNI:</label>
                <input type="number" name="DNI" placeholder="ej: 31532532" >
                <!-- --> 
                <label>Mail:</label>
                <input type="email" name="correo_electronico" placeholder="juan@ejemplo.com"  >
                <!-- -->
                <label>Contraseña:</label>
                <input type="password" name="contraseña" placeholder="ej: seguridad123" required>
                <!-- -->
                <button type="submit">Registrase</button>
                <!-- -->
            </form>
            <!-- el link para iniciar sesion -->
            <p> </p>
            <div class="iniciar_sesion_relocate">¿ya tienes una cuenta?  <a href="../inicio/inicio.php">inicia sesion</a></div>

        </div>
    </div>
</body>
</html>
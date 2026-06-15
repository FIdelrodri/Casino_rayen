<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuarios</title>
    <link rel="stylesheet" href="inicio.css">
</head>
<body>

    <div class="contenedor-fondo">
        
        <div class="barra_superior">
            <img class="imagen_logo" src="../../../imagenes_y_3D/Imagenes/logo_casino_rayen.png" alt="">
        </div>
        <div class="container">
            <h2>Iniciar Sesion</h2>
            <form action="../../../Metodo\login.php" method="POST">
                 <p></p>
                <label>Nombre usuario:</label>
                <input type="text" name="Nombre" placeholder="su usuario" required>
                
                <label>contraseña:</label>
                <input type="password" name="pass" placeholder="su contrasñea" required>
                
                <button type="submit">iniciar sesion</button>
            
            </form>
            <!-- por si no tienen una cuntardopolis -->
            <p> </p>
            <div class="iniciar_sesion_relocate">¿ya tienes una cuenta?  <a href="../inicio/inicio.php">inicia sesion</a></div>
        </div>

    </div>
</body>
</html>
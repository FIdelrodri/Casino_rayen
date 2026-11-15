<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuarios</title>
    <link rel="stylesheet" href="../../componentes/barra_superior.css">
    <link rel="stylesheet" href="inicio.css">
</head>
<body>

    <div class="contenedor-fondo">
        
        <?php include(__DIR__ . "/../../componentes/barra_superior.php"); ?>
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
            <div class="iniciar_sesion_relocate">¿Todavia no tienes una cuenta?  <a href="../registro/vista_registro.php">registrate</a></div>
        </div>

    </div>
</body>
</html>
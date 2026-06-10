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
            <img class="logowich_arriba" src="../../imagenes\reallogo.png" alt="">
        </div>
        <!-- el formulario del madafakin profe -->
        <div class="container">
            <h2>Registrar Nuevo Usuario</h2>
            <form action="registro.php" method="POST">
                <!-- if de error de trigger -->
                <?php if (isset($_SESSION['trigger_regirtro_error'])): ?>
                        <div class="alerta_verde_afuera">
                           <div class="alerta_verde_dentro">
                                !Cuenta o mail ya existente¡ o hay carcater invalido
                           </div>
                        </div>
                <?php 
                   unset($_SESSION['trigger_regirtro_error']); 
                   endif; 
                 ?>
                <!--  todo el form para ingresar datos -->
                <label>Ingrese nombre de usuario:</label>
                 <p></p>
                <input type="text" name="Nombre" placeholder="ej:juan123" required>
                 <!-- -->
                <label>Ingrese su nombre:</label>
                <input type="text" name="Nombre_persona" placeholder="Federico" >
                <!-- -->
                 <label>Ingrese su Apellido:</label>
                <input type="text" name="apellido" placeholder="Bouson" >
                <!-- --> 
                <label>Ingrese su DNI:</label>
                <input type="number" name="DNI" placeholder="Ejm: 31532532" >
                <!-- --> 
                <label>Ingrese su Mail:</label>
                <input type="email" name="correo_electronico" placeholder="juan@ejemplo.com"  >
                <!-- -->
                <label>Contraseña:</label>
                <input type="password" name="contraseña" placeholder="contraseañ segura" required>
                <!-- -->
                <button type="submit">Registrase</button>
                <!-- -->
            </form>
            <!-- el link para iniciar sesion -->
               <a> <div class="iniciar_sesion_relocate">¿ya tienes una cuenta? </div> <a href="../inicio/inicio.php">inicia sesion</a>
        </div>
    </div>
</body>
</html>
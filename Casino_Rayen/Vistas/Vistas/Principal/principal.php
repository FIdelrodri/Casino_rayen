<?php
session_start();
if (!isset($_SESSION['usuario_logueado'])) {
    header("Location: ../inicio/inicio.php");
    exit();
}
include("../../metodos/conexion.php");

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pipilibre - Principal</title>
    <link rel="stylesheet" href="principal.css">
</head>
<body>

    <div class="contenedor-fondo">
        <header class="barra_superior">
            <img class="logowich_arriba" src="../../../imagenes y 3D\Imagenes\logo_casino_rayen.png" alt="Logo">
            <div class="cosas_derecha">
                <div class="barra_derecha">
                    <div class="dropdown">
                        <button class="btn-menu">Mi Cuenta ▼</button>
                        <div class="dropdown-content">
                            
                            <a href="../../../metodos/logout.php" class="opcion-logout">❌ Cerrar sesion</a> 
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <div class="conteiner_de_productos">
            
        </div>
    </div>

</body>
</html>

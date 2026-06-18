<?php
session_start();
if (!isset($_SESSION['usuario_logueado'])) {
    header("Location: ../inicio/inicio.php");
    exit();
}
include("../../../Metodo/conexion.php");
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

        <header class="barra_superior">
            <img class="logowich_arriba" src="../../../imagenes_y_3D\Imagenes\logo_casino_rayen.png" alt="Logo">
            <div class="cosas_derecha">
                <div class="dinero_continer">
                        <img class="imagen_croquetas_dinero" src="../../../imagenes_y_3D\Imagenes\croqueta.png">
                    <div>[variable dinero]</div>
                </div>
                <div class="barra_derecha">
                    <div class="dropdown">
                        <button class="btn-menu">Mi Cuenta ▼</button>
                        <div class="dropdown-content">
                            
                            <a href="../../../Metodo/logout.php" class="opcion-logout">❌ Cerrar sesion</a> 
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <div class="conteiner_de_ranking">   
        </div>
        <div class="Texto_juegos"><h1> JUEGOS</h1></div>
        <div class="contenedor_juegos_grid">
            <a href="../../juego/coinflip/hola.html" class="tarjeta_juego">
                <!-- Fuente usada: darumadrop ONE -->
                <img src="../../../imagenes_y_3D/Imagenes/Tarjeta_principal_coinflip.png" alt="Juego 2">
            </a>
        </div>

</body>
</html>

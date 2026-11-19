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
    <link rel="stylesheet" href="../../componentes/barra_superior.css">
    <link rel="stylesheet" href="principal.css">
</head>
<body>

<?php
$barra_boton = 'cuenta';
$barra_pagina = 'principal';
include(__DIR__ . "/../../componentes/barra_superior.php");
?>
            <a href="../ranking/vista_ranking.php">
            <img class="conteiner_de_ranking" src="proyecto nuevo.png" alt="Cartel de Juegos">
            </a>
        </div>
            <img class="imagen_cartel_responsiva" src="../../../imagenes_y_3D/Imagenes/Juegos_Cartel.png" alt="Cartel de Juegos">

        <div class="contenedor_juegos_grid">
            <a href="../../juego\coinflip\coinflip.php" class="tarjeta_juego">
                <!-- Fuente usada: darumadrop ONE -->
                <img src="../../../imagenes_y_3D/Imagenes/Tarjeta_principal_coinflip.png" alt="Juego 2">
            </a>
            <a href="../../juego/crash/crash.php" class="tarjeta_juego">
                <img src="../../../imagenes_y_3D/Imagenes/Tarjeta_principal_crash.png" alt="Crash">
            </a>
            <a href="../../juego/blackjack/blackjack.php" class="tarjeta_juego">
                <img src="../../../imagenes_y_3D/Imagenes/Tarjeta_principal_blackjack.png" alt="Blackjack">
            </a>
            <a href="../../juego/mayormenor/mayormenor.php" class="tarjeta_juego">
                <img src="../../../imagenes_y_3D/Imagenes/Tarjeta_principal_mayormenor.png" alt="Mayor o Menor">
            </a>
        </div>
</body>

</html>

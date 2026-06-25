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
                        <div class="texto_dinero">
                        <?php
                            $id_usuario = $_SESSION['id_usuario'];
                            $sql = "select Saldo from Usuario where id_usuario = $id_usuario";
                            if ($resultado = mysqli_query($conexion, $sql)) {
                            $datos_usuario = mysqli_fetch_assoc($resultado);
                            $saldo_actual = $datos_usuario['Saldo'];
                           echo number_format($saldo_actual, 0, '', '.');
                         }
                         ?>
                         </div>
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
            <img class="imagen_cartel_responsiva" src="../../../imagenes_y_3D/Imagenes/Juegos_Cartel.png" alt="Cartel de Juegos">
        <div class="contenedor_juegos_grid">
            <a href="../../juego\coinflip\coinflip.php" class="tarjeta_juego">
                <!-- Fuente usada: darumadrop ONE -->
                <img src="../../../imagenes_y_3D/Imagenes/Tarjeta_principal_coinflip.png" alt="Juego 2">
            </a>
        </div>
</body>

</html>

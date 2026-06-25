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
    <link rel="stylesheet" href="ranking.css">
</head>
<body>

    <header class="barra_superior">
        <img class="logowich_arriba" src="../../../imagenes_y_3D/Imagenes/logo_casino_rayen.png" alt="Logo">
        <div class="cosas_derecha">
            <div class="dinero_continer">
                 <img class="imagen_croquetas_dinero" src="../../../imagenes_y_3D/Imagenes/croqueta.png">
                <div class="texto_dinero">
                  <?php
                $id_usuario = $_SESSION['id_usuario'];
                $sql = "SELECT Saldo FROM Usuario WHERE id_usuario = $id_usuario";
                if ($res = mysqli_query($conexion, $sql)) {
                $datos_usuario = mysqli_fetch_assoc($res);
                echo number_format($datos_usuario['Saldo'], 0, '', '.');
        }
        ?>
    </div>
        </div>
            <div class="barra_derecha">
                <a href="../../Vistas/Principal/principal.php" class="btn-menu">Inicio</a>
            </div>
        </div>
    </header>

    <div class="container_coin">
    <?php
    $sql = "SELECT Nombre_usuario, Saldo FROM Usuario ORDER BY Saldo DESC"; 
    $resultado = mysqli_query($conexion, $sql); 

    $puesto = 1; // Contador para saber el top

    while ($fila = mysqli_fetch_assoc($resultado)) { 
        // Asignamos una clase especial según el puesto para el CSS
        $clase_top = "";
        $icono_puesto = $puesto; // Por defecto muestra el número (4, 5, 6...)

        if ($puesto == 1) {
            $clase_top = "top-1";
            $icono_puesto = "🥇";
        } elseif ($puesto == 2) {
            $clase_top = "top-2";
            $icono_puesto = "🥈";
        } elseif ($puesto == 3) {
            $clase_top = "top-3";
            $icono_puesto = "🥉";
        }
    ?>
        <div class="usuario-tarjeta <?php echo $clase_top; ?>">
            <div class="puesto-badge"><?php echo $icono_puesto; ?></div>
            <div class="info-usuario">
                <span class="etiqueta-usuario">Usuario</span>
                <strong class="nombre-usuario"><?php echo htmlspecialchars($fila['Nombre_usuario']); ?></strong>
            </div>
            <div class="saldo-usuario">
                <span class="etiqueta-saldo">Saldo</span>
                <strong class="monto-saldo">$<?php echo number_format($fila['Saldo'], 2, ',', '.'); ?></strong>
            </div>
        </div>
    <?php 
        $puesto++; // Sumamos uno en cada vuelta del ciclo
        } 
    ?>
    </div>

    </div>


</body>
</html>
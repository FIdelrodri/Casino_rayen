<?php
session_start();
if (!isset($_SESSION['usuario_logueado'])) {
    header("Location: ../inicio/inicio.php");
    exit();
}
include("../../../Metodo/conexion.php");

$resultado_color = isset($_SESSION['color']) ? $_SESSION['color'] : null;
unset($_SESSION['color']);
$id_usuario = $_SESSION['id_usuario'];
$sql = "SELECT Saldo FROM Usuario WHERE id_usuario = $id_usuario";
$res = mysqli_query($conexion, $sql);
$datos_usuario = mysqli_fetch_assoc($res);
$saldo_actual = $datos_usuario['Saldo'];

$saldo_mostrar = isset($_SESSION['saldo_antes']) ? $_SESSION['saldo_antes'] : $saldo_actual;
unset($_SESSION['saldo_antes']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pipilibre - Principal</title>
    <link rel="stylesheet" href="coinflip.css">
</head>
<body>

    <header class="barra_superior">
        <img class="logowich_arriba" src="../../../imagenes_y_3D/Imagenes/logo_casino_rayen.png" alt="Logo">
        <div class="cosas_derecha">
            <div class="dinero_continer">
                <img class="imagen_croquetas_dinero" src="../../../imagenes_y_3D/Imagenes/croqueta.png">
                <div class="texto_dinero" 
                    id="saldo-display"
                    data-saldo-real="<?= number_format($saldo_actual, 0, '', '.') ?>">
                    <?= number_format($saldo_mostrar, 0, '', '.') ?>
                </div>
            </div>
            <div class="barra_derecha">
                <a href="../../Vistas/Principal/principal.php" class="btn-menu">Inicio</a>
            </div>
        </div>
    </header>

    <!-- Se envía el resultado de PHP de manera segura mediante data-resultado -->
    <div class="continer_coin">
        <canvas id="coin-canvas" data-resultado="<?= htmlspecialchars($resultado_color ?? '') ?>"></canvas>
    </div>

    <div class="continer_formulario">
        <form action="coinflip_action.php" method="POST">
            <h2 class="form-titulo">⚡ Coin Flip</h2>

            <div class="form-group">
                <label class="form-label">Dinero a apostar</label>
                <div class="input-wrap">
                    <span class="input-icon">🪙</span>
                    <input class="form-input" type="number" name="Dinero_apostado" min="1" placeholder="0" required>
                </div>
            </div>

            <div class="form-group">
                <div class="range-header">
                    <label class="form-label" for="rango">Probabilidad de ganar</label>
                    <span class="prob-badge" id="prob-out">50%</span>
                </div>
                <div class="range-track">
                    <div class="range-fill" id="range-fill"></div>
                    <input class="form-range" type="range" id="rango" name="rango_apuesta"
                        min="1" max="99" value="50" step="1"
                        oninput="document.getElementById('prob-out').textContent=this.value+'%';
                                 document.getElementById('range-fill').style.width=this.value+'%'">
                </div>
                <div class="range-labels">
                    <span>1%</span><span>50%</span><span>99%</span>
                </div>
            </div>

            <div class="form-divider"></div>
            <button class="btn-apostar" type="submit">Apostar</button>
        </form>
    </div>

    <!-- Mapeo necesario para que Three.js funcione de forma modular -->
    <script type="importmap">
    {
        "imports": {
            "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js",
            "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/"
        }
    }
    </script>

    <!-- Inclusión de tu lógica de renderizado y animación desde el archivo externo -->
    <script type="module" src="../../../Java_script/coinflip_render.js"></script>

</body>
</html>
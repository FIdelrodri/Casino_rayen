<?php
session_start();
if (!isset($_SESSION['usuario_logueado'])) {
    header("Location: ../../Vistas/inicio/inicio.php");
    exit();
}
include("../../../Metodo/conexion.php");
include("crash_funciones.php");

// si había una ronda abierta al entrar, se retoma donde quedó
$habia_ronda = isset($_SESSION['crash']);
$ronda = estado_ronda();

$estado_inicial = 'ninguna';
$apuesta_ronda = 0;
$transcurrido = 0;
$multiplicador_final = 100;
if ($habia_ronda) {
    $estado_inicial = $ronda['estado'];
    $apuesta_ronda = $ronda['apuesta'];
    if ($estado_inicial == 'volando') {
        $transcurrido = $ronda['transcurrido'];
    } else {
        $multiplicador_final = $ronda['multiplicador'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crash - Casino Rayen</title>
    <link rel="stylesheet" href="../../componentes/barra_superior.css">
    <link rel="stylesheet" href="crash.css">
</head>
<body>

<?php
$barra_boton = 'inicio';
include(__DIR__ . "/../../componentes/barra_superior.php");
?>

    <div class="continer_coin" id="escena"
         data-estado="<?= $estado_inicial ?>"
         data-apuesta="<?= $apuesta_ronda ?>"
         data-transcurrido="<?= $transcurrido ?>"
         data-multiplicador="<?= $multiplicador_final ?>">
        <div class="multiplicador" id="multiplicador">1.00x</div>
        <img class="cohete" id="cohete" src="../../../imagenes_y_3D/Imagenes/choete_gino_1.png" alt="Cohete">
    </div>

    <div class="continer_formulario">
        <form id="form_crash">
            <h2 class="form-titulo">Crash</h2>

            <div class="form-group">
                <label class="form-label" for="campo_apuesta">Dinero a apostar</label>
                <div class="input-wrap">
                    <span class="input-icon">🪙</span>
                    <input class="form-input" type="number" id="campo_apuesta" min="1" step="1" placeholder="0" required>
                </div>
            </div>

            <p class="aviso_crash" id="aviso_crash"></p>

            <div class="form-divider"></div>
            <button class="btn-apostar" type="submit" id="boton_crash">Apostar</button>
        </form>
    </div>

    <script src="../../../Java_script/crash.js"></script>

</body>
</html>

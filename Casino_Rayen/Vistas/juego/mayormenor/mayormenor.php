<?php
session_start();
if (!isset($_SESSION['usuario_logueado'])) {
    header("Location: ../../Vistas/inicio/inicio.php");
    exit();
}
include("../../../Metodo/conexion.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mayor o Menor - Casino Rayen</title>
    <link rel="stylesheet" href="../../componentes/barra_superior.css">
    <link rel="stylesheet" href="mayormenor.css">
</head>
<body>

<?php
$barra_boton = 'inicio';
include(__DIR__ . "/../../componentes/barra_superior.php");
?>

    <div class="continer_coin" id="mesa">
        <div class="datos_ronda">
            <div class="dato">
                <span class="dato_titulo">Multiplicador</span>
                <span class="dato_valor" id="texto_multiplicador">1.00x</span>
            </div>
            <div class="dato">
                <span class="dato_titulo">Si cobrás</span>
                <span class="dato_valor" id="texto_premio">0</span>
            </div>
        </div>

        <div class="cartas_centro">
            <div class="hueco">
                <span class="rotulo">Actual</span>
                <img class="carta" id="carta_actual" src="../../../imagenes_y_3D/Imagenes/cartas/dorso.png" alt="Carta actual">
            </div>
            <div class="hueco">
                <span class="rotulo">Siguiente</span>
                <img class="carta" id="carta_siguiente" src="../../../imagenes_y_3D/Imagenes/cartas/dorso.png" alt="Carta siguiente">
            </div>
        </div>

        <p class="resultado_ronda" id="resultado_ronda"></p>

        <div class="historial" id="historial"></div>
    </div>

    <div class="continer_formulario">
        <form id="form_mayormenor">
            <h2 class="form-titulo">Mayor o Menor</h2>

            <div class="form-group">
                <label class="form-label" for="campo_apuesta">Dinero a apostar</label>
                <div class="input-wrap">
                    <span class="input-icon">🪙</span>
                    <input class="form-input" type="number" id="campo_apuesta" min="1" step="1" placeholder="0" required>
                </div>
            </div>

            <p class="aviso_mayormenor" id="aviso_mayormenor"></p>

            <div class="form-divider"></div>
            <button class="btn-apostar" type="submit" id="boton_empezar">Empezar</button>
        </form>

        <div class="controles" id="controles" hidden>
            <div class="fila_opciones">
                <button class="btn-apostar btn-secundario" type="button" id="boton_mayor"></button>
                <button class="btn-apostar btn-secundario" type="button" id="boton_menor"></button>
            </div>
            <button class="btn-apostar btn-secundario" type="button" id="boton_saltar">Saltar carta</button>
            <button class="btn-apostar" type="button" id="boton_cobrar">Cobrar</button>
        </div>
    </div>

    <script src="../../../Java_script/mayormenor.js"></script>

</body>
</html>

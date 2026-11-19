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
    <title>Traga Rayen - Casino Rayen</title>
    <link rel="stylesheet" href="../../componentes/barra_superior.css">
    <link rel="stylesheet" href="tragaperras.css">
</head>
<body>

<?php
$barra_boton = 'inicio';
include(__DIR__ . "/../../componentes/barra_superior.php");
?>

    <div class="continer_coin" id="mesa">
        <div class="cartel">
            <img src="../../../imagenes_y_3D/Imagenes/tragaperras/dorado.svg" alt="">
            <div class="cartel_texto">
                <span class="cartel_titulo">Traga Rayen</span>
                <span class="cartel_premio">5 Rayen dorados en una línea: 1000x la apuesta</span>
            </div>
            <img src="../../../imagenes_y_3D/Imagenes/tragaperras/dorado.svg" alt="">
        </div>

        <div class="aviso_bono" id="aviso_bono" hidden></div>

        <div class="maquina">
            <div class="rodillos" id="rodillos"></div>
            <svg class="lineas" id="lineas" viewBox="0 0 500 300" preserveAspectRatio="none"></svg>
        </div>

        <div class="fila_mensaje">
            <span class="texto_mensaje" id="texto_mensaje"></span>
            <span class="texto_ganancia" id="texto_ganancia"></span>
        </div>

        <div class="festejo" id="festejo" hidden>
            <img class="festejo_imagen" id="festejo_imagen" src="../../../imagenes_y_3D/Imagenes/tragaperras/dorado.svg" alt="" hidden>
            <div class="festejo_titulo" id="festejo_titulo"></div>
            <div class="festejo_monto" id="festejo_monto"></div>
            <button class="btn-apostar" type="button" id="boton_seguir" hidden>Seguir</button>
        </div>
    </div>

    <div class="continer_formulario">
        <form id="form_tragaperras">
            <h2 class="form-titulo">Traga Rayen</h2>

            <div class="form-group">
                <label class="form-label">Apuesta por giro</label>
                <div class="selector_apuesta">
                    <button class="boton_paso" type="button" id="boton_menos">−</button>
                    <input class="campo_apuesta" type="number" id="campo_apuesta" min="20" step="1" value="20">
                    <button class="boton_paso" type="button" id="boton_mas">+</button>
                </div>
                <p class="detalle_apuesta">20 líneas, <span id="texto_linea">1</span> por línea</p>
                <button class="btn-apostar btn-secundario" type="button" id="boton_maxima">Apuesta máxima</button>
            </div>

            <p class="aviso_tragaperras" id="aviso_tragaperras"></p>

            <div class="form-divider"></div>
            <button class="btn-apostar" type="submit" id="boton_girar">Girar</button>

            <div class="fila_opciones">
                <button class="btn-chico" type="button" id="boton_auto">Automático</button>
                <button class="btn-chico" type="button" id="boton_sonido">Sonido</button>
                <button class="btn-chico" type="button" id="boton_pagos">Premios</button>
            </div>
            <p class="ayuda">Con la barra espaciadora también se gira.</p>
        </form>
    </div>

    <dialog class="dialogo_pagos" id="dialogo_pagos">
        <div id="contenido_pagos"></div>
        <button class="btn-apostar" type="button" id="cerrar_pagos">Cerrar</button>
    </dialog>

    <canvas class="lluvia" id="lluvia"></canvas>

    <script src="../../../Java_script/tragaperras.js"></script>

</body>
</html>

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
    <title>Blackjack - Casino Rayen</title>
    <link rel="stylesheet" href="../../componentes/barra_superior.css">
    <link rel="stylesheet" href="blackjack.css">
</head>
<body>

<?php
$barra_boton = 'inicio';
include(__DIR__ . "/../../componentes/barra_superior.php");
?>

    <div class="continer_coin" id="mesa">
        <div class="zona_mano">
            <div class="titulo_mano">
                <span>Crupier</span>
                <span class="puntos" id="puntos_crupier"></span>
            </div>
            <div class="cartas" id="cartas_crupier"></div>
        </div>

        <p class="resultado_ronda" id="resultado_ronda"></p>

        <div class="zona_mano">
            <div class="cartas" id="cartas_jugador"></div>
            <div class="titulo_mano">
                <span>Vos</span>
                <span class="puntos" id="puntos_jugador"></span>
            </div>
        </div>
    </div>

    <div class="continer_formulario">
        <form id="form_blackjack">
            <h2 class="form-titulo">Blackjack</h2>

            <div class="form-group">
                <label class="form-label" for="campo_apuesta">Dinero a apostar</label>
                <div class="input-wrap">
                    <span class="input-icon">🪙</span>
                    <input class="form-input" type="number" id="campo_apuesta" min="1" step="1" placeholder="0" required>
                </div>
            </div>

            <p class="aviso_blackjack" id="aviso_blackjack"></p>

            <div class="form-divider"></div>
            <button class="btn-apostar" type="submit" id="boton_repartir">Repartir</button>
        </form>

        <div class="controles" id="controles" hidden>
            <button class="btn-apostar" type="button" id="boton_pedir">Pedir</button>
            <button class="btn-apostar btn-secundario" type="button" id="boton_plantarse">Plantarse</button>
            <button class="btn-apostar btn-secundario" type="button" id="boton_doblar">Doblar</button>
        </div>
    </div>

    <script src="../../../Java_script/blackjack.js"></script>

</body>
</html>

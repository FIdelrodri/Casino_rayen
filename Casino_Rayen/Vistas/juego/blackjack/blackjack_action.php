<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

function responder($datos) {
    echo json_encode($datos);
    exit();
}
function fallo($mensaje) {
    responder(array('ok' => false, 'error' => $mensaje));
}

if (!isset($_SESSION['usuario_logueado'])) {
    fallo('Tenés que iniciar sesión');
}
include("../../../Metodo/conexion.php");
include("blackjack_funciones.php");

$id_usuario = (int)$_SESSION['id_usuario'];
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

function mover_saldo($conexion, $id_usuario, $monto) {
    try {
        return mysqli_query($conexion, "CALL actualizar_saldo($id_usuario, $monto)");
    } catch (mysqli_sql_exception $e) {
        return false;
    }
}

function ronda_en_curso() {
    if (!isset($_SESSION['blackjack'])) {
        fallo('No hay ninguna ronda en curso');
    }
    return $_SESSION['blackjack'];
}

function seguir_jugando($conexion, $id_usuario, $ronda) {
    $_SESSION['blackjack'] = $ronda;
    $saldo = saldo_de($conexion, $id_usuario);
    responder(array_merge(datos_ronda($ronda, false), array(
        'ok' => true,
        'estado' => 'jugando',
        'saldo' => $saldo,
        'puede_doblar' => count($ronda['jugador']) == 2 && $saldo >= $ronda['apuesta']
    )));
}

function cerrar_ronda($conexion, $id_usuario, $ronda, $resultado) {
    $apuesta = $ronda['apuesta'];
    $premio = 0;
    if ($resultado == 'blackjack') {
        $premio = intdiv($apuesta * 5, 2);
    } elseif ($resultado == 'ganaste') {
        $premio = $apuesta * 2;
    } elseif ($resultado == 'empate') {
        $premio = $apuesta;
    }

    if ($premio > 0 && !mover_saldo($conexion, $id_usuario, $premio)) {
        fallo('No se pudo pagar la ronda, probá de nuevo');
    }

    unset($_SESSION['blackjack']);
    $final = array_merge(datos_ronda($ronda, true), array(
        'ok' => true,
        'estado' => 'terminada',
        'resultado' => $resultado,
        'premio' => $premio,
        'saldo' => saldo_de($conexion, $id_usuario)
    ));
    $_SESSION['blackjack_ultimo'] = $final;
    responder($final);
}

function plantar($conexion, $id_usuario, $ronda) {
    jugar_crupier($ronda);
    cerrar_ronda($conexion, $id_usuario, $ronda, resultado_final($ronda));
}

// apostar
if ($accion == 'apostar') {
    $apuesta = filter_var(isset($_POST['apuesta']) ? $_POST['apuesta'] : '', FILTER_VALIDATE_INT);
    if ($apuesta === false || $apuesta < 1) {
        fallo('La apuesta tiene que ser un número entero mayor a 0');
    }
    if (isset($_SESSION['blackjack'])) {
        fallo('Ya tenés una ronda en curso');
    }
    if ($apuesta > saldo_de($conexion, $id_usuario)) {
        fallo('No tenés saldo suficiente');
    }
    if (!mover_saldo($conexion, $id_usuario, -$apuesta)) {
        fallo('No se pudo realizar la apuesta');
    }

    $mazo = crear_mazo();
    $ronda = array(
        'apuesta' => $apuesta,
        'jugador' => array(array_pop($mazo)),
        'crupier' => array(array_pop($mazo)),
        'mazo' => $mazo
    );
    $ronda['jugador'][] = array_pop($ronda['mazo']);
    $ronda['crupier'][] = array_pop($ronda['mazo']);
    unset($_SESSION['blackjack_ultimo']);

    $jugador_blackjack = es_blackjack($ronda['jugador']);
    $crupier_blackjack = es_blackjack($ronda['crupier']);
    if ($jugador_blackjack && $crupier_blackjack) {
        cerrar_ronda($conexion, $id_usuario, $ronda, 'empate');
    }
    if ($jugador_blackjack) {
        cerrar_ronda($conexion, $id_usuario, $ronda, 'blackjack');
    }
    if ($crupier_blackjack) {
        cerrar_ronda($conexion, $id_usuario, $ronda, 'perdiste');
    }
    seguir_jugando($conexion, $id_usuario, $ronda);
}

// estado (al entrar o recargar la página)
if ($accion == 'estado') {
    if (isset($_SESSION['blackjack'])) {
        seguir_jugando($conexion, $id_usuario, $_SESSION['blackjack']);
    }
    if (isset($_SESSION['blackjack_ultimo'])) {
        $ultimo = $_SESSION['blackjack_ultimo'];
        $ultimo['saldo'] = saldo_de($conexion, $id_usuario);
        responder($ultimo);
    }
    responder(array('ok' => true, 'estado' => 'ninguna'));
}

// pedir
if ($accion == 'pedir') {
    $ronda = ronda_en_curso();
    $ronda['jugador'][] = array_pop($ronda['mazo']);
    $puntos = puntos_de($ronda['jugador']);

    if ($puntos > 21) {
        cerrar_ronda($conexion, $id_usuario, $ronda, 'pasaste');
    }
    if ($puntos == 21) {
        plantar($conexion, $id_usuario, $ronda);
    }
    seguir_jugando($conexion, $id_usuario, $ronda);
}

// plantarse
if ($accion == 'plantarse') {
    $ronda = ronda_en_curso();
    plantar($conexion, $id_usuario, $ronda);
}

// doblar
if ($accion == 'doblar') {
    $ronda = ronda_en_curso();
    if (count($ronda['jugador']) != 2) {
        fallo('Solo se puede doblar con las dos primeras cartas');
    }
    if ($ronda['apuesta'] > saldo_de($conexion, $id_usuario)) {
        fallo('No tenés saldo suficiente para doblar');
    }
    if (!mover_saldo($conexion, $id_usuario, -$ronda['apuesta'])) {
        fallo('No se pudo doblar la apuesta');
    }

    $ronda['apuesta'] = $ronda['apuesta'] * 2;
    $ronda['jugador'][] = array_pop($ronda['mazo']);
    if (puntos_de($ronda['jugador']) > 21) {
        cerrar_ronda($conexion, $id_usuario, $ronda, 'pasaste');
    }
    plantar($conexion, $id_usuario, $ronda);
}

fallo('Acción inválida');

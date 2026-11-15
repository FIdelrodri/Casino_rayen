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
include("crash_funciones.php");

$id_usuario = (int)$_SESSION['id_usuario'];
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

if ($accion == 'apostar') {
    $apuesta = filter_var(isset($_POST['apuesta']) ? $_POST['apuesta'] : '', FILTER_VALIDATE_INT);
    if ($apuesta === false || $apuesta < 1) {
        fallo('La apuesta tiene que ser un número entero mayor a 0');
    }

    $ronda = estado_ronda();
    if ($ronda['estado'] == 'volando') {
        fallo('Ya tenés una ronda en curso');
    }

    if ($apuesta > saldo_de($conexion, $id_usuario)) {
        fallo('No tenés saldo suficiente');
    }

    try {
        $descontado = mysqli_query($conexion, "CALL actualizar_saldo($id_usuario, -$apuesta)");
    } catch (mysqli_sql_exception $e) {
        $descontado = false;
    }
    if (!$descontado) {
        fallo('No se pudo realizar la apuesta');
    }

    unset($_SESSION['crash_ultimo']);
    $_SESSION['crash'] = array(
        'apuesta' => $apuesta,
        'punto' => punto_de_explosion(),
        'inicio' => microtime(true)
    );

    responder(array(
        'ok' => true,
        'estado' => 'volando',
        'multiplicador' => 100,
        'saldo' => saldo_de($conexion, $id_usuario)
    ));
}

if ($accion == 'estado') {
    $ronda = estado_ronda();
    responder(array_merge(array('ok' => true), $ronda));
}

if ($accion == 'retirar') {
    $ronda = estado_ronda();

    if ($ronda['estado'] == 'ninguna') {
        fallo('No hay ninguna ronda en curso');
    }
    if ($ronda['estado'] == 'explotado') {
        $ronda['ok'] = true;
        $ronda['saldo'] = saldo_de($conexion, $id_usuario);
        responder($ronda);
    }

    $premio = intdiv($ronda['apuesta'] * $ronda['multiplicador'], 100);
    try {
        $pagado = mysqli_query($conexion, "CALL actualizar_saldo($id_usuario, $premio)");
    } catch (mysqli_sql_exception $e) {
        $pagado = false;
    }
    if (!$pagado) {
        fallo('No se pudo retirar, probá de nuevo');
    }

    unset($_SESSION['crash']);
    unset($_SESSION['crash_ultimo']);

    responder(array(
        'ok' => true,
        'estado' => 'retirado',
        'multiplicador' => $ronda['multiplicador'],
        'premio' => $premio,
        'saldo' => saldo_de($conexion, $id_usuario)
    ));
}

fallo('Acción inválida');

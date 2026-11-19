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
include("mayormenor_funciones.php");

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
    if (!isset($_SESSION['mayormenor'])) {
        fallo('No hay ninguna ronda en curso');
    }
    return $_SESSION['mayormenor'];
}

function seguir_jugando($conexion, $id_usuario, $ronda, $extra) {
    $_SESSION['mayormenor'] = $ronda;
    responder(array_merge(datos_ronda($ronda), $extra, array(
        'ok' => true,
        'estado' => 'jugando',
        'saldo' => saldo_de($conexion, $id_usuario)
    )));
}

function cerrar_ronda($conexion, $id_usuario, $ronda, $resultado, $actual, $revelada) {
    $premio = 0;
    if ($resultado != 'perdiste') {
        $premio = premio_de($ronda['apuesta'], $ronda['multiplicador']);
    }

    if ($premio > 0 && !mover_saldo($conexion, $id_usuario, $premio)) {
        fallo('No se pudo pagar la ronda, probá de nuevo');
    }

    unset($_SESSION['mayormenor']);
    $final = array(
        'ok' => true,
        'estado' => 'terminada',
        'resultado' => $resultado,
        'actual' => $actual,
        'revelada' => $revelada,
        'apuesta' => $ronda['apuesta'],
        'multiplicador' => $ronda['multiplicador'],
        'aciertos' => $ronda['aciertos'],
        'premio' => $premio,
        'historial' => $ronda['historial'],
        'saldo' => saldo_de($conexion, $id_usuario)
    );
    $_SESSION['mayormenor_ultimo'] = $final;
    responder($final);
}

// apostar
if ($accion == 'apostar') {
    $apuesta = filter_var(isset($_POST['apuesta']) ? $_POST['apuesta'] : '', FILTER_VALIDATE_INT);
    if ($apuesta === false || $apuesta < 1) {
        fallo('La apuesta tiene que ser un número entero mayor a 0');
    }
    if (isset($_SESSION['mayormenor'])) {
        fallo('Ya tenés una ronda en curso');
    }
    if ($apuesta > saldo_de($conexion, $id_usuario)) {
        fallo('No tenés saldo suficiente');
    }
    if (!mover_saldo($conexion, $id_usuario, -$apuesta)) {
        fallo('No se pudo realizar la apuesta');
    }

    unset($_SESSION['mayormenor_ultimo']);
    $ronda = array(
        'apuesta' => $apuesta,
        'actual' => carta_al_azar(),
        'multiplicador' => UNIDAD,
        'aciertos' => 0,
        'historial' => array()
    );
    seguir_jugando($conexion, $id_usuario, $ronda, array());
}

// estado (al entrar o recargar la página)
if ($accion == 'estado') {
    if (isset($_SESSION['mayormenor'])) {
        seguir_jugando($conexion, $id_usuario, $_SESSION['mayormenor'], array());
    }
    if (isset($_SESSION['mayormenor_ultimo'])) {
        $ultimo = $_SESSION['mayormenor_ultimo'];
        $ultimo['saldo'] = saldo_de($conexion, $id_usuario);
        responder($ultimo);
    }
    responder(array('ok' => true, 'estado' => 'ninguna'));
}

// adivinar
if ($accion == 'adivinar') {
    $ronda = ronda_en_curso();
    $eleccion = isset($_POST['eleccion']) ? $_POST['eleccion'] : '';
    if ($eleccion != 'mayor' && $eleccion != 'menor') {
        fallo('Elegí mayor o menor');
    }

    $opciones = opciones_de($ronda['actual'], $ronda['multiplicador']);
    if ($opciones[$eleccion] === null) {
        fallo('Esa opción no es posible con esta carta');
    }

    $anterior = $ronda['actual'];
    $revelada = carta_al_azar();
    $rango_anterior = rango_de($anterior['valor']);
    $rango_nuevo = rango_de($revelada['valor']);

    $acerto = false;
    if ($eleccion == 'mayor' && $rango_nuevo > $rango_anterior) {
        $acerto = true;
    }
    if ($eleccion == 'menor' && $rango_nuevo < $rango_anterior) {
        $acerto = true;
    }

    if (!$acerto) {
        cerrar_ronda($conexion, $id_usuario, $ronda, 'perdiste', $anterior, $revelada);
    }

    $ronda['multiplicador'] = $opciones[$eleccion]['multiplicador'];
    $ronda['aciertos']++;
    $ronda['historial'][] = $anterior;
    $ronda['actual'] = $revelada;

    if ($ronda['multiplicador'] >= MULTIPLICADOR_MAXIMO) {
        $ronda['multiplicador'] = MULTIPLICADOR_MAXIMO;
        cerrar_ronda($conexion, $id_usuario, $ronda, 'maximo', $anterior, $revelada);
    }
    seguir_jugando($conexion, $id_usuario, $ronda, array('anterior' => $anterior, 'revelada' => $revelada));
}

// saltar
if ($accion == 'saltar') {
    $ronda = ronda_en_curso();
    $ronda['actual'] = carta_al_azar();
    seguir_jugando($conexion, $id_usuario, $ronda, array());
}

// cobrar
if ($accion == 'cobrar') {
    $ronda = ronda_en_curso();
    if ($ronda['aciertos'] < 1) {
        fallo('Tenés que acertar al menos una vez para cobrar');
    }
    cerrar_ronda($conexion, $id_usuario, $ronda, 'cobrado', $ronda['actual'], null);
}

fallo('Acción inválida');

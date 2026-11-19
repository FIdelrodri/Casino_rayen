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
include("tragaperras_funciones.php");

$id_usuario = (int)$_SESSION['id_usuario'];
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

function mover_saldo($conexion, $id_usuario, $monto) {
    if ($monto == 0) {
        return true;
    }
    try {
        return mysqli_query($conexion, "CALL actualizar_saldo($id_usuario, $monto)");
    } catch (mysqli_sql_exception $e) {
        return false;
    }
}

// giros gratis pendientes (los guarda la sesión)
function datos_bono() {
    if (!isset($_SESSION['tragaperras_bono'])) {
        return array('restantes' => 0, 'ganado' => 0);
    }
    $bono = $_SESSION['tragaperras_bono'];
    return array('restantes' => $bono['restantes'], 'ganado' => $bono['ganado'], 'apuesta' => $bono['apuesta']);
}

// estado (al entrar o recargar la página)
if ($accion == 'estado') {
    global $PAGOS, $PAGO_BOLSA, $GIROS_BOLSA;
    responder(array(
        'ok' => true,
        'saldo' => saldo_de($conexion, $id_usuario),
        'bono' => datos_bono(),
        'pagos' => $PAGOS,
        'pago_bolsa' => $PAGO_BOLSA,
        'giros_bolsa' => $GIROS_BOLSA,
        'multiplicador_bono' => MULTIPLICADOR_BONO,
        'minima' => APUESTA_MINIMA
    ));
}

// girar
if ($accion == 'girar') {
    $gratis = isset($_SESSION['tragaperras_bono']) && $_SESSION['tragaperras_bono']['restantes'] > 0;

    if ($gratis) {
        $apuesta_total = $_SESSION['tragaperras_bono']['apuesta'];
        $tiras = armar_rodillos($RODILLOS_BONO);
    } else {
        $apuesta = filter_var(isset($_POST['apuesta']) ? $_POST['apuesta'] : '', FILTER_VALIDATE_INT);
        if ($apuesta === false || $apuesta < APUESTA_MINIMA) {
            fallo('La apuesta mínima es de ' . APUESTA_MINIMA);
        }
        if ($apuesta > saldo_de($conexion, $id_usuario)) {
            fallo('No tenés saldo suficiente');
        }
        $apuesta_total = $apuesta;
        $tiras = armar_rodillos($RODILLOS_NORMAL);
    }

    $tirada = jugar_tirada($tiras, $apuesta_total, $gratis);

    // en un giro común se cobra la apuesta y se paga el premio en un solo movimiento
    $movimiento = $tirada['premio'];
    if (!$gratis) {
        $movimiento -= $apuesta_total;
    }
    if (!mover_saldo($conexion, $id_usuario, $movimiento)) {
        fallo('No se pudo completar el giro, probá de nuevo');
    }

    // giros gratis
    $bono_nuevo = 0;
    $bono_terminado = false;
    $ganado_bono = 0;
    if ($gratis) {
        $_SESSION['tragaperras_bono']['restantes']--;
        $_SESSION['tragaperras_bono']['ganado'] += $tirada['premio'];
        $ganado_bono = $_SESSION['tragaperras_bono']['ganado'];
        if ($_SESSION['tragaperras_bono']['restantes'] <= 0) {
            $bono_terminado = true;
            unset($_SESSION['tragaperras_bono']);
        }
    } elseif ($tirada['giros_nuevos'] > 0) {
        $bono_nuevo = $tirada['giros_nuevos'];
        $_SESSION['tragaperras_bono'] = array(
            'restantes' => $bono_nuevo,
            'apuesta' => $apuesta_total,
            'ganado' => 0
        );
    }

    responder(array(
        'ok' => true,
        'grilla' => $tirada['grilla'],
        'lineas' => $tirada['lineas'],
        'bolsas' => $tirada['bolsas'],
        'premio_lineas' => $tirada['premio_lineas'],
        'premio_bolsa' => $tirada['premio_bolsa'],
        'premio' => $tirada['premio'],
        'apuesta' => $apuesta_total,
        'gratis' => $gratis,
        'nivel' => nivel_de_premio($tirada['premio'], $apuesta_total, $tirada['lineas']),
        'bono_nuevo' => $bono_nuevo,
        'bono_terminado' => $bono_terminado,
        'ganado_bono' => $ganado_bono,
        'bono' => datos_bono(),
        'saldo' => saldo_de($conexion, $id_usuario)
    ));
}

fallo('Acción no válida');
?>

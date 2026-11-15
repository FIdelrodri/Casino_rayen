<?php
// el multiplicador va en centésimas: 100 = 1.00x
function multiplicador_en($milisegundos) {
    return (int)floor(100 * exp(0.0001 * $milisegundos));
}

// 1% de las rondas explotan en 1.00x, el resto se reparte como 0.99 / x
function punto_de_explosion() {
    $azar = random_int(0, 99999);
    $punto = intdiv(9900000, 100000 - $azar);
    if ($punto < 100) {
        $punto = 100;
    }
    if ($punto > 1000000) {
        $punto = 1000000;
    }
    return $punto;
}

function estado_ronda() {
    if (!isset($_SESSION['crash'])) {
        if (isset($_SESSION['crash_ultimo'])) {
            return $_SESSION['crash_ultimo'];
        }
        return array('estado' => 'ninguna');
    }

    $ronda = $_SESSION['crash'];
    $milisegundos = (microtime(true) - $ronda['inicio']) * 1000;
    $multiplicador = multiplicador_en($milisegundos);

    if ($multiplicador >= $ronda['punto']) {
        unset($_SESSION['crash']);
        $_SESSION['crash_ultimo'] = array(
            'estado' => 'explotado',
            'multiplicador' => $ronda['punto'],
            'apuesta' => $ronda['apuesta']
        );
        return $_SESSION['crash_ultimo'];
    }

    return array(
        'estado' => 'volando',
        'multiplicador' => $multiplicador,
        'apuesta' => $ronda['apuesta'],
        'transcurrido' => (int)$milisegundos
    );
}

function saldo_de($conexion, $id_usuario) {
    $consulta = mysqli_query($conexion, "select Saldo from Usuario where id_usuario = $id_usuario");
    $fila = mysqli_fetch_assoc($consulta);
    return (int)$fila['Saldo'];
}
?>

<?php
// el multiplicador va en diezmilésimas: 10000 = 1.0000x
define('UNIDAD', 10000);
// el máximo que se puede acumular en una ronda (1000x)
define('MULTIPLICADOR_MAXIMO', 10000000);
// 13 valores por 99%: la casa se queda con el 1% en cada acierto
define('PAGO_BASE', 128700);

// cartas
function rango_de($valor) {
    if ($valor == 'as') {
        return 1;
    }
    if ($valor == 'j') {
        return 11;
    }
    if ($valor == 'q') {
        return 12;
    }
    if ($valor == 'k') {
        return 13;
    }
    return (int)$valor;
}

function carta_al_azar() {
    $palos = array('real', 'bosque', 'elegante', 'crupier');
    $valores = array('as', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'j', 'q', 'k');
    return array(
        'palo' => $palos[random_int(0, 3)],
        'valor' => $valores[random_int(0, 12)]
    );
}

// opciones
// el empate pierde, así que solo cuentan los valores estrictamente mayores o menores
function opcion($favorables, $multiplicador_actual) {
    if ($favorables < 1) {
        return null;
    }
    $paso = intdiv(PAGO_BASE, $favorables);
    return array(
        'probabilidad' => round($favorables * 100 / 13, 1),
        'paso' => $paso,
        'multiplicador' => intdiv($multiplicador_actual * $paso, UNIDAD)
    );
}

function opciones_de($carta, $multiplicador_actual) {
    $rango = rango_de($carta['valor']);
    return array(
        'mayor' => opcion(13 - $rango, $multiplicador_actual),
        'menor' => opcion($rango - 1, $multiplicador_actual)
    );
}

function premio_de($apuesta, $multiplicador) {
    return intdiv($apuesta * $multiplicador, UNIDAD);
}

// ronda
function datos_ronda($ronda) {
    return array(
        'actual' => $ronda['actual'],
        'apuesta' => $ronda['apuesta'],
        'multiplicador' => $ronda['multiplicador'],
        'aciertos' => $ronda['aciertos'],
        'premio_posible' => premio_de($ronda['apuesta'], $ronda['multiplicador']),
        'historial' => $ronda['historial'],
        'opciones' => opciones_de($ronda['actual'], $ronda['multiplicador'])
    );
}

function saldo_de($conexion, $id_usuario) {
    $consulta = mysqli_query($conexion, "select Saldo from Usuario where id_usuario = $id_usuario");
    $fila = mysqli_fetch_assoc($consulta);
    return (int)$fila['Saldo'];
}
?>

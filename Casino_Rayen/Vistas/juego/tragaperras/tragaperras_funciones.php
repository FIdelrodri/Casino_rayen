<?php
// Tragaperras de Rayen: 5 rodillos x 3 filas, 20 líneas

define('LINEAS', 20);
define('APUESTA_MINIMA', 20);
define('MULTIPLICADOR_BONO', 2);

// recorrido de cada línea (fila que usa en cada rodillo)
$RECORRIDOS = array(
    array(1,1,1,1,1), array(0,0,0,0,0), array(2,2,2,2,2), array(0,1,2,1,0), array(2,1,0,1,2),
    array(0,0,1,2,2), array(2,2,1,0,0), array(1,0,0,0,1), array(1,2,2,2,1), array(0,1,1,1,0),
    array(2,1,1,1,2), array(1,0,1,2,1), array(1,2,1,0,1), array(0,1,0,1,0), array(2,1,2,1,2),
    array(1,1,0,1,1), array(1,1,2,1,1), array(0,0,2,0,0), array(2,2,0,2,2), array(0,2,2,2,0)
);

// premios por 3, 4 y 5 iguales (en veces la apuesta de una línea, o sea apuesta / 20)
$PAGOS = array(
    'pata'   => array(3, 9, 27),
    'hueso'  => array(3, 11, 32),
    'pelota' => array(5, 16, 52),
    'plato'  => array(8, 22, 76),
    'collar' => array(13, 42, 152),
    'casita' => array(21, 77, 305),
    'dorado' => array(55, 270, 20000)
);

// bolsa de croquetas: 3, 4 o 5 en cualquier lugar (veces la apuesta total) y giros gratis
$PAGO_BOLSA = array(3 => 2, 4 => 10, 5 => 50);
$GIROS_BOLSA = array(3 => 8, 4 => 12, 5 => 20);

// cantidad de cada símbolo en cada rodillo (40 posiciones)
$RODILLOS_NORMAL = array(
    array('pata'=>10,'hueso'=>9,'pelota'=>7,'plato'=>5,'collar'=>3,'casita'=>2,'rayen'=>0,'dorado'=>2,'bolsa'=>2),
    array('pata'=>8,'hueso'=>8,'pelota'=>6,'plato'=>5,'collar'=>3,'casita'=>2,'rayen'=>4,'dorado'=>2,'bolsa'=>2),
    array('pata'=>8,'hueso'=>8,'pelota'=>6,'plato'=>5,'collar'=>3,'casita'=>2,'rayen'=>4,'dorado'=>2,'bolsa'=>2),
    array('pata'=>8,'hueso'=>8,'pelota'=>6,'plato'=>5,'collar'=>3,'casita'=>2,'rayen'=>4,'dorado'=>2,'bolsa'=>2),
    array('pata'=>10,'hueso'=>9,'pelota'=>7,'plato'=>5,'collar'=>3,'casita'=>2,'rayen'=>0,'dorado'=>2,'bolsa'=>2)
);
// rodillos de los giros gratis: más comodines y sin bolsas
$RODILLOS_BONO = array(
    array('pata'=>10,'hueso'=>9,'pelota'=>7,'plato'=>6,'collar'=>4,'casita'=>2,'rayen'=>0,'dorado'=>2,'bolsa'=>0),
    array('pata'=>8,'hueso'=>7,'pelota'=>6,'plato'=>5,'collar'=>4,'casita'=>3,'rayen'=>5,'dorado'=>2,'bolsa'=>0),
    array('pata'=>8,'hueso'=>7,'pelota'=>6,'plato'=>5,'collar'=>4,'casita'=>3,'rayen'=>5,'dorado'=>2,'bolsa'=>0),
    array('pata'=>8,'hueso'=>7,'pelota'=>6,'plato'=>5,'collar'=>4,'casita'=>3,'rayen'=>5,'dorado'=>2,'bolsa'=>0),
    array('pata'=>10,'hueso'=>9,'pelota'=>7,'plato'=>6,'collar'=>4,'casita'=>2,'rayen'=>0,'dorado'=>2,'bolsa'=>0)
);

// arma la tira de un rodillo: las bolsas bien repartidas y el resto intercalado
function armar_rodillo($cantidades) {
    $largo = array_sum($cantidades);
    $tira = array_fill(0, $largo, null);

    $bolsas = $cantidades['bolsa'];
    for ($k = 0; $k < $bolsas; $k++) {
        $tira[intdiv($k * $largo, $bolsas)] = 'bolsa';
    }

    unset($cantidades['bolsa']);
    $nombres = array_keys($cantidades);
    $turno = 0;
    for ($i = 0; $i < $largo; $i++) {
        if ($tira[$i] !== null) {
            continue;
        }
        while ($cantidades[$nombres[$turno % count($nombres)]] == 0) {
            $turno++;
        }
        $nombre = $nombres[$turno % count($nombres)];
        $tira[$i] = $nombre;
        $cantidades[$nombre]--;
        $turno++;
    }
    return $tira;
}

function armar_rodillos($conjunto) {
    $tiras = array();
    foreach ($conjunto as $cantidades) {
        $tiras[] = armar_rodillo($cantidades);
    }
    return $tiras;
}

// tirada: devuelve la grilla $grilla[fila][columna]
function girar_rodillos($tiras) {
    $grilla = array(array(), array(), array());
    for ($c = 0; $c < 5; $c++) {
        $largo = count($tiras[$c]);
        $parada = random_int(0, $largo - 1);
        for ($f = 0; $f < 3; $f++) {
            $grilla[$f][$c] = $tiras[$c][($parada + $f) % $largo];
        }
    }
    return $grilla;
}

// busca las líneas que pagan (de izquierda a derecha, el comodín reemplaza todo menos bolsa y dorado)
function evaluar_lineas($grilla, $apuesta) {
    global $RECORRIDOS, $PAGOS;
    $ganadoras = array();

    for ($n = 0; $n < LINEAS; $n++) {
        $recorrido = $RECORRIDOS[$n];
        $simbolo = $grilla[$recorrido[0]][0];
        if (!isset($PAGOS[$simbolo])) {
            continue;
        }

        $cantidad = 1;
        for ($c = 1; $c < 5; $c++) {
            $actual = $grilla[$recorrido[$c]][$c];
            $comodin = ($actual == 'rayen' && $simbolo != 'dorado');
            if ($actual == $simbolo || $comodin) {
                $cantidad++;
            } else {
                break;
            }
        }

        if ($cantidad >= 3) {
            $pago = intdiv($PAGOS[$simbolo][$cantidad - 3] * $apuesta, LINEAS);
            $ganadoras[] = array(
                'linea' => $n + 1,
                'recorrido' => $recorrido,
                'simbolo' => $simbolo,
                'cantidad' => $cantidad,
                'pago' => $pago
            );
        }
    }
    return $ganadoras;
}

function contar_bolsas($grilla) {
    $celdas = array();
    for ($f = 0; $f < 3; $f++) {
        for ($c = 0; $c < 5; $c++) {
            if ($grilla[$f][$c] == 'bolsa') {
                $celdas[] = array($f, $c);
            }
        }
    }
    return $celdas;
}

// juega una tirada completa y devuelve el resultado sin tocar el saldo
function jugar_tirada($tiras, $apuesta, $gratis) {
    global $PAGO_BOLSA, $GIROS_BOLSA;

    $grilla = girar_rodillos($tiras);
    $ganadoras = evaluar_lineas($grilla, $apuesta);

    $premio_lineas = 0;
    foreach ($ganadoras as $g) {
        $premio_lineas += $g['pago'];
    }
    if ($gratis) {
        $premio_lineas *= MULTIPLICADOR_BONO;
    }

    $bolsas = contar_bolsas($grilla);
    $cantidad_bolsas = count($bolsas);
    $premio_bolsa = 0;
    $giros_nuevos = 0;
    if (!$gratis && $cantidad_bolsas >= 3) {
        $premio_bolsa = $PAGO_BOLSA[$cantidad_bolsas] * $apuesta;
        $giros_nuevos = $GIROS_BOLSA[$cantidad_bolsas];
    }

    return array(
        'grilla' => $grilla,
        'lineas' => $ganadoras,
        'premio_lineas' => $premio_lineas,
        'bolsas' => $bolsas,
        'premio_bolsa' => $premio_bolsa,
        'giros_nuevos' => $giros_nuevos,
        'premio' => $premio_lineas + $premio_bolsa
    );
}

// tamaño del festejo según lo que se ganó respecto a la apuesta total
function nivel_de_premio($premio, $apuesta_total, $lineas) {
    foreach ($lineas as $g) {
        if ($g['simbolo'] == 'dorado' && $g['cantidad'] == 5) {
            return 'jackpot';
        }
    }
    if ($premio <= 0) {
        return 'nada';
    }
    $veces = $premio / $apuesta_total;
    if ($veces >= 100) {
        return 'super';
    }
    if ($veces >= 30) {
        return 'mega';
    }
    if ($veces >= 10) {
        return 'grande';
    }
    return 'normal';
}

function saldo_de($conexion, $id_usuario) {
    $consulta = mysqli_query($conexion, "select Saldo from Usuario where id_usuario = $id_usuario");
    $fila = mysqli_fetch_assoc($consulta);
    return (int)$fila['Saldo'];
}
?>

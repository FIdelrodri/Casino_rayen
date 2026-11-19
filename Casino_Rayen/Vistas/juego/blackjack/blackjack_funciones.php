<?php
// mazo: cada palo es una versión de Rayen
function crear_mazo() {
    $palos = array('real', 'bosque', 'elegante', 'crupier');
    $valores = array('as', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'j', 'q', 'k');
    $mazo = array();
    foreach ($palos as $palo) {
        foreach ($valores as $valor) {
            $mazo[] = array('palo' => $palo, 'valor' => $valor);
        }
    }

    for ($i = count($mazo) - 1; $i > 0; $i--) {
        $j = random_int(0, $i);
        $auxiliar = $mazo[$i];
        $mazo[$i] = $mazo[$j];
        $mazo[$j] = $auxiliar;
    }
    return $mazo;
}

// puntos
function puntos_de($mano) {
    $total = 0;
    $ases = 0;
    foreach ($mano as $carta) {
        if ($carta['valor'] == 'as') {
            $total += 11;
            $ases++;
        } elseif (in_array($carta['valor'], array('j', 'q', 'k'))) {
            $total += 10;
        } else {
            $total += (int)$carta['valor'];
        }
    }
    while ($total > 21 && $ases > 0) {
        $total -= 10;
        $ases--;
    }
    return $total;
}

function es_blackjack($mano) {
    return count($mano) == 2 && puntos_de($mano) == 21;
}

// crupier: se planta con 17 o más
function jugar_crupier(&$ronda) {
    while (puntos_de($ronda['crupier']) < 17) {
        $ronda['crupier'][] = array_pop($ronda['mazo']);
    }
}

function resultado_final($ronda) {
    $jugador = puntos_de($ronda['jugador']);
    $crupier = puntos_de($ronda['crupier']);
    if ($crupier > 21 || $jugador > $crupier) {
        return 'ganaste';
    }
    if ($jugador < $crupier) {
        return 'perdiste';
    }
    return 'empate';
}

// lo que se le manda al navegador (la carta tapada del crupier va como null)
function datos_ronda($ronda, $revelado) {
    $crupier = $ronda['crupier'];
    if (!$revelado) {
        $crupier = array($crupier[0], null);
    }
    return array(
        'jugador' => $ronda['jugador'],
        'crupier' => $crupier,
        'puntos_jugador' => puntos_de($ronda['jugador']),
        'puntos_crupier' => $revelado ? puntos_de($ronda['crupier']) : null,
        'apuesta' => $ronda['apuesta']
    );
}

function saldo_de($conexion, $id_usuario) {
    $consulta = mysqli_query($conexion, "select Saldo from Usuario where id_usuario = $id_usuario");
    $fila = mysqli_fetch_assoc($consulta);
    return (int)$fila['Saldo'];
}
?>

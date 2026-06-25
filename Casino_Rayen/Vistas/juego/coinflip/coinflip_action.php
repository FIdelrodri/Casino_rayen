<?php
session_start();
if (!isset($_SESSION['usuario_logueado'])) {
    header("Location: ../inicio/inicio.php");
    exit();
}
include("../../../Metodo/conexion.php");

$dinero_apostado = (int)$_POST['Dinero_apostado']; 
$rango = (int)$_POST['rango_apuesta'];
$aleatorio = random_int(1, 100);

if ($aleatorio <= $rango) {
    $ganancia = round($dinero_apostado * (100 / $rango), 2);
    $_SESSION['color']     = "verde";
    $_SESSION['resultado'] = "ganaste";
    $_SESSION['monto']     = $ganancia;
} else {
    $_SESSION['color']     = "rojo";
    $_SESSION['resultado'] = "perdiste";
    $_SESSION['monto']     = $dinero_apostado;
}

header("Location: coinflip.php");
exit();
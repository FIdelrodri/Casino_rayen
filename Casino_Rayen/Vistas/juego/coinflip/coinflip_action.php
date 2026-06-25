<?php
session_start();
if (!isset($_SESSION['usuario_logueado'])) {
    header("Location: ../inicio/inicio.php");
    exit();
}
include("../../../Metodo/conexion.php");

$dinero_apostado = (int)$_POST['Dinero_apostado']; 
$rango = (int)$_POST['rango_apuesta'];
$aleatorio = random_int(0, 100);

if ($rango < 0  || $rango > 100) {
    $_Trigger_error['error_rango_apuesta'] = "Error";
    header("Location: coinflip.php");
    exit();
}
if ($dinero_apostado < 0.01) {
    $_Trigger_error['error_dinero_apostado'] = "Error";
    header("Location: coinflip.php");
    exit();
}
$id_usuario = $_SESSION['id_usuario'];
$sql = "select Saldo from Usuario where id_usuario = $id_usuario";
$dinero_consulta = mysqli_query($conexion, $sql);
$datos_consulta = mysqli_fetch_assoc($dinero_consulta);
$dinero_disponible = $datos_consulta['Saldo'];
if ($dinero_apostado > $dinero_disponible) {
    $_Trigger_error['error_dinero_apostado'] = "Error";
    header("Location: coinflip.php");
    exit();
}
$_SESSION['saldo_antes'] = $dinero_disponible;

if ($aleatorio <= $rango) {
    $ganancia = round($dinero_apostado * (100 / $rango), 2);
    $monto_final = $ganancia - $dinero_apostado; 
    $_SESSION['color']     = "verde";
    $_SESSION['resultado'] = "ganaste";
    $_SESSION['monto']     = $ganancia;
    mysqli_query($conexion, "CALL actualizar_saldo($id_usuario, $monto_final)");
} else {
    $_SESSION['color']     = "rojo";
    $_SESSION['resultado'] = "perdiste";
    $_SESSION['monto']     = $dinero_apostado;
    $resta = -$dinero_apostado;
    mysqli_query($conexion, "CALL actualizar_saldo($id_usuario, $resta)");
}

header("Location: coinflip.php");
exit();
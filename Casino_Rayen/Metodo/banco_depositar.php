<?php
session_start();

if (!isset($_SESSION['usuario_logueado'])) {
    header("Location: ../Vistas/Vistas/inicio/inicio.php");
    exit();
}
include("conexion.php");

$volver = "../Vistas/Vistas/banco/banco.php";
$id_usuario = (int)$_SESSION['id_usuario'];
$monto = filter_var(isset($_POST['monto']) ? $_POST['monto'] : '', FILTER_VALIDATE_INT);

if ($monto === false || $monto < 1) {
    header("Location: $volver?err=" . urlencode("El monto tiene que ser un número entero mayor a 0"));
    exit();
}

try {
    $hecho = mysqli_query($conexion, "CALL depositar_banco($id_usuario, $monto)");
} catch (mysqli_sql_exception $e) {
    $hecho = false;
}

if ($hecho) {
    header("Location: $volver?msg=" . urlencode("Guardaste en el banco " . number_format($monto, 0, '', '.') . " croquetas"));
} else {
    // 1644 = error lanzado por el procedimiento, el resto es un fallo de la base
    if (mysqli_errno($conexion) == 1644) {
        $mensaje = mysqli_error($conexion);
    } else {
        $mensaje = "No se pudo depositar";
    }
    header("Location: $volver?err=" . urlencode($mensaje));
}

mysqli_close($conexion);
?>

<?php
session_start();

if (!isset($_SESSION['usuario_logueado'])) {
    header("Location: ../Vistas/Vistas/inicio/inicio.php");
    exit();
}
include("conexion.php");

if (isset($_POST['pagina']) && $_POST['pagina'] == 'admin') {
    $volver = "../Vistas/Vistas/administracion/administracion.php";
} elseif (isset($_POST['pagina']) && $_POST['pagina'] == 'banco') {
    $volver = "../Vistas/Vistas/banco/banco.php";
} else {
    $volver = "../Vistas/Vistas/Principal/principal.php";
}
$id_usuario = (int)$_SESSION['id_usuario'];
$codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';

if ($codigo == '' || mb_strlen($codigo) > 50) {
    header("Location: $volver?canje_err=" . urlencode("Ingresá un código válido"));
    exit();
}
$codigo = mysqli_real_escape_string($conexion, $codigo);

try {
    $resultado = mysqli_query($conexion, "CALL canjear_codigo($id_usuario, '$codigo')");
} catch (mysqli_sql_exception $e) {
    $resultado = false;
}

if ($resultado) {
    $fila = mysqli_fetch_assoc($resultado);
    mysqli_free_result($resultado);
    $premio = number_format($fila['premio'], 0, '', '.');
    header("Location: $volver?canje_msg=" . urlencode("Código canjeado, recibiste $premio croquetas"));
} else {
    // 1644 = error lanzado por el procedimiento, el resto es un fallo de la base
    if (mysqli_errno($conexion) == 1644) {
        $mensaje = mysqli_error($conexion);
    } else {
        $mensaje = "No se pudo canjear el código";
    }
    header("Location: $volver?canje_err=" . urlencode($mensaje));
}

mysqli_close($conexion);
?>

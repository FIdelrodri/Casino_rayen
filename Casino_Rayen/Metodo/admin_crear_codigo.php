<?php
session_start();

// Solo admins
if (!isset($_SESSION['usuario_logueado'])) {
    header("Location: ../Vistas/Vistas/inicio/inicio.php");
    exit();
}
if ($_SESSION['rol'] != 2) {
    header("Location: ../Vistas/Vistas/Principal/principal.php");
    exit();
}

include("conexion.php");

$id_admin = $_SESSION['id_usuario'];
$codigo   = isset($_POST['codigo'])   ? mysqli_real_escape_string($conexion, trim($_POST['codigo'])) : '';
$premio   = isset($_POST['premio'])   ? (float)$_POST['premio']   : 0;
$cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad']   : 0;

if (!$codigo || $premio <= 0 || $cantidad <= 0) {
    header("Location: ../Vistas/Vistas/administracion/administracion.php?err=Completá todos los campos correctamente");
    exit();
}

$sql = "CALL admin_crear_codigo($id_admin, '$codigo', $premio, $cantidad)";

if (mysqli_query($conexion, $sql)) {
    header("Location: ../Vistas/Vistas/administracion/administracion.php?msg=Código creado correctamente");
} else {
    header("Location: ../Vistas/Vistas/administracion/administracion.php?err=" . urlencode(mysqli_error($conexion)));
}

mysqli_close($conexion);
?>

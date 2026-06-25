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

$id_admin    = $_SESSION['id_usuario'];
$id_usuario  = isset($_POST['id_usuario'])  ? (int)$_POST['id_usuario']    : 0;
$nuevo_saldo = isset($_POST['nuevo_saldo']) ? (float)$_POST['nuevo_saldo'] : null;

if (!$id_usuario || $nuevo_saldo === null) {
    header("Location: ../Vistas/Vistas/administracion/administracion.php?err=Datos inválidos");
    exit();
}

$sql = "CALL admin_modificar_saldo($id_admin, $id_usuario, $nuevo_saldo)";

if (mysqli_query($conexion, $sql)) {
    header("Location: ../Vistas/Vistas/administracion/administracion.php?msg=Saldo actualizado correctamente");
} else {
    header("Location: ../Vistas/Vistas/administracion/administracion.php?err=" . urlencode(mysqli_error($conexion)));
}

mysqli_close($conexion);
?>

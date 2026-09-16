<?php
session_start();

// Solo admins
if (!isset($_SESSION['usuario_logueado'])) {
    header("Location: ../Vistas/Vistas/inicio/inicio.php");
    exit();
}
include("conexion.php");

$id_usuario = $_SESSION['id_usuario'];
$sql = "select Rol from Usuario where id_usuario = $id_usuario";
if ($resultado = mysqli_query($conexion, $sql)) {
$Array_rol = mysqli_fetch_assoc($resultado);
$rol = $Array_rol['Rol']; 
}
if ($rol != 2) {
    header("Location: ../principal/principal.php");
    exit();
}

$id_admin  = $_SESSION['id_usuario'];
$id_codigo = isset($_POST['id_codigo']) ? (int)$_POST['id_codigo'] : 0;

if (!$id_codigo) {
    header("Location: ../Vistas/Vistas/administracion/administracion.php?err=Código inválido");
    exit();
}

$sql = "CALL admin_borrar_codigo($id_admin, $id_codigo)";

if (mysqli_query($conexion, $sql)) {
    header("Location: ../Vistas/Vistas/administracion/administracion.php?msg=Código eliminado correctamente");
} else {
    header("Location: ../Vistas/Vistas/administracion/administracion.php?err=" . urlencode(mysqli_error($conexion)));
}

mysqli_close($conexion);
?>

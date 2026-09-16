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

$id_admin   = $_SESSION['id_usuario'];
$id_usuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;

// No puede borrarse a sí mismo
if (!$id_usuario || $id_usuario == $id_admin) {
    header("Location: ../Vistas/Vistas/administracion/administracion.php?err=Acción no permitida");
    exit();
}

$sql = "CALL admin_borrar_usuario($id_admin, $id_usuario)";

if (mysqli_query($conexion, $sql)) {
    header("Location: ../Vistas/Vistas/administracion/administracion.php?msg=Usuario eliminado correctamente");
} else {
    header("Location: ../Vistas/Vistas/administracion/administracion.php?err=" . urlencode(mysqli_error($conexion)));
}

mysqli_close($conexion);
?>

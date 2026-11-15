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

$id_admin = $_SESSION['id_usuario'];
$codigo   = isset($_POST['codigo'])   ? mysqli_real_escape_string($conexion, trim($_POST['codigo'])) : '';
$premio   = filter_var(isset($_POST['premio'])   ? $_POST['premio']   : '', FILTER_VALIDATE_INT);
$cantidad = filter_var(isset($_POST['cantidad']) ? $_POST['cantidad'] : '', FILTER_VALIDATE_INT);

if (!$codigo || $premio === false || $premio <= 0 || $cantidad === false || $cantidad <= 0) {
    header("Location: ../Vistas/Vistas/administracion/administracion.php?err=El premio y los usos tienen que ser números enteros mayores a 0");
    exit();
}

$sql = "CALL admin_crear_codigo($id_admin, '$codigo', $premio, $cantidad)";

try {
    $creado = mysqli_query($conexion, $sql);
} catch (mysqli_sql_exception $e) {
    $creado = false;
}

if ($creado) {
    header("Location: ../Vistas/Vistas/administracion/administracion.php?msg=Código creado correctamente");
} else {
    header("Location: ../Vistas/Vistas/administracion/administracion.php?err=" . urlencode(mysqli_error($conexion)));
}

mysqli_close($conexion);
?>

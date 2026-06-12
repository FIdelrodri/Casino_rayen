<?php
session_start(); 
include("conexion.php");

$nombre = $_POST['Nombre'];
$pass = $_POST['pass']; 


$consulta = "CALL verificar_usuario('$nombre', '$pass')";
$resultado = mysqli_query($conexion, $consulta);

if (!$resultado) {
    die("Error en la consulta de la base de datos: " . mysqli_error($conexion));
}

if (mysqli_num_rows($resultado) > 0) {

    $fila = mysqli_fetch_assoc($resultado);
    $_SESSION['usuario_logueado'] = $nombre;
    header("Location: ../Vistas/Vistas/principal/principal.php");
} else {
    header("Location: ../vistas/Vistas/inicio/inicio.php");
}
?>
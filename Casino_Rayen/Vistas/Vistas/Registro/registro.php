<?php
session_start();
include("../../../Metodo/conexion.php");

$nombre = $_POST['Nombre']; 
$correo = $_POST['correo_electronico'];
$contrasena = $_POST['contraseña'];
$dni = $_POST['DNI'];
$nombre_persona = $_POST['Nombre_persona'];
$apellido = $_POST['apellido'];
$Registrar_consulta = "CALL crearcuenta('$nombre', '$nombre_persona', '$apellido', '$dni', '$correo', '$contrasena')";

if (mysqli_query($conexion, $Registrar_consulta)) {
    header("Location: ../inicio/inicio.php");
    exit();
} else {
    echo mysqli_error($conexion);
}
mysqli_close($conexion);
// $_SESSION['trigger_regirtro_exitoso'] = 'se madafakin logro el register';
?>
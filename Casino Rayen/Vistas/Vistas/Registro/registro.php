<?php
session_start();
// conexion
include("../../metodos/conexion.php");
// agarra variables del form
$nombre = $_POST['Nombre']; 
$correo = $_POST['correo_electronico'];
$pass = $_POST['contraseña'];
$dni = $_POST['DNI'];
$nombre_persona = $_POST['Nombre_persona'];
$apellido = $_POST['apellido'];
// la madafakin consulta para ingresar los datos
$Registrar_consulta = "CALL crearcuenta('$nombre', '$nombre_persona', '$apellido', '$dni', '$correo', '$pass')";

if (mysqli_query($conexion, $Registrar_consulta)) {

    // osea se logran incertar los datos crea el trigger de que se logro y va a la pagina de inciio de sesion
    $_SESSION['trigger_regirtro_exitoso'] = 'se madafakin logro el register';
    header("Location: ../inicio/inicio.php");
    exit();
} else {
    // fallo el registro y crea el trigger de error
    $_SESSION['trigger_regirtro_error'] = 'opaaa NO se madafakin logro el register';
    echo mysqli_error($conexion);
}
// no se que cierra pero cierra algo
mysqli_close($conexion);
?>
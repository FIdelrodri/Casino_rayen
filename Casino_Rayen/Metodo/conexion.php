<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "Casino_rayen";

$conexion = mysqli_connect($host, $user, $pass, $db, '3307');

if (!$conexion) {
    echo '1';
    die("Error de conexión: " . mysqli_connect_error());
}
?>
<?php

session_start(); 
session_destroy(); 

session_start(); 
header("Location: ../Vistas\Vistas\inicio\inicio.php");
?>
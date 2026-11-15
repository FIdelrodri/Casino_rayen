<?php
session_start();
if (!isset($_SESSION['usuario_logueado'])) {
    header("Location: ../inicio/inicio.php");
    exit();
}
include("../../../Metodo/conexion.php");

$usuarios = mysqli_query($conexion, "select Nombre_usuario, Saldo from Usuario order by Saldo desc");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking - Casino Rayen</title>
    <link rel="stylesheet" href="../../componentes/barra_superior.css">
    <link rel="stylesheet" href="ranking.css">
</head>
<body>

<?php
$barra_boton = 'inicio';
include(__DIR__ . "/../../componentes/barra_superior.php");
?>

    <main class="ranking">
        <h1>Ranking</h1>
        <table>
            <tr>
                <th>#</th>
                <th>Usuario</th>
                <th class="derecha">Saldo</th>
            </tr>
            <?php $puesto = 1; ?>
            <?php while ($fila = mysqli_fetch_assoc($usuarios)) { ?>
            <tr>
                <td><?= $puesto ?></td>
                <td><?= htmlspecialchars($fila['Nombre_usuario']) ?></td>
                <td class="derecha"><?= number_format($fila['Saldo'], 0, '', '.') ?></td>
            </tr>
            <?php $puesto++; ?>
            <?php } ?>
        </table>
    </main>

</body>
</html>

<?php
session_start();
if (!isset($_SESSION['usuario_logueado'])) {
    header("Location: ../inicio/inicio.php");
    exit();
}
include("../../../Metodo/conexion.php");

$id_usuario = (int)$_SESSION['id_usuario'];

$consulta = mysqli_query($conexion, "select Monto from vista_banco where id_usuario = $id_usuario");
$fila = mysqli_fetch_assoc($consulta);
$en_banco = $fila ? (int)$fila['Monto'] : 0;

$consulta = mysqli_query($conexion, "select Saldo from Usuario where id_usuario = $id_usuario");
$fila = mysqli_fetch_assoc($consulta);
$saldo_actual = (int)$fila['Saldo'];

$movimientos = mysqli_query($conexion, "select Monto_trasaccionado, Fecha from vista_banco_movimientos where id_usuario = $id_usuario order by id_hs desc limit 20");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banco - Casino Rayen</title>
    <link rel="stylesheet" href="../../componentes/barra_superior.css">
    <link rel="stylesheet" href="banco.css">
</head>
<body>

<?php
$barra_boton = 'cuenta';
$barra_pagina = 'banco';
include(__DIR__ . "/../../componentes/barra_superior.php");
?>

    <main class="banco">

        <?php if (isset($_GET['msg'])) { ?>
            <p class="aviso"><?= htmlspecialchars($_GET['msg']) ?></p>
        <?php } ?>
        <?php if (isset($_GET['err'])) { ?>
            <p class="aviso error"><?= htmlspecialchars($_GET['err']) ?></p>
        <?php } ?>

        <section class="resumen">
            <div>
                <span>En el banco</span>
                <strong><?= number_format($en_banco, 0, '', '.') ?></strong>
            </div>
            <div>
                <span>Saldo para jugar</span>
                <strong><?= number_format($saldo_actual, 0, '', '.') ?></strong>
            </div>
        </section>

        <div class="operaciones">
            <section>
                <h2>Depositar</h2>
                <form action="../../../Metodo/banco_depositar.php" method="POST">
                    <input type="number" name="monto" min="1" step="1" placeholder="Monto" required>
                    <button type="submit">Depositar</button>
                </form>
            </section>

            <section>
                <h2>Retirar</h2>
                <form action="../../../Metodo/banco_retirar.php" method="POST">
                    <input type="number" name="monto" min="1" step="1" placeholder="Monto" required>
                    <button type="submit">Retirar</button>
                </form>
            </section>
        </div>

        <section>
            <h2>Movimientos</h2>
            <table>
                <tr>
                    <th>Fecha</th>
                    <th>Movimiento</th>
                    <th class="derecha">Monto</th>
                </tr>
                <?php if (mysqli_num_rows($movimientos) == 0) { ?>
                <tr><td colspan="3">Todavía no hiciste ningún movimiento</td></tr>
                <?php } ?>
                <?php while ($mov = mysqli_fetch_assoc($movimientos)) { ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($mov['Fecha'])) ?></td>
                    <td><?= $mov['Monto_trasaccionado'] > 0 ? 'Depósito' : 'Retiro' ?></td>
                    <td class="derecha"><?= number_format(abs($mov['Monto_trasaccionado']), 0, '', '.') ?></td>
                </tr>
                <?php } ?>
            </table>
        </section>

    </main>

</body>
</html>

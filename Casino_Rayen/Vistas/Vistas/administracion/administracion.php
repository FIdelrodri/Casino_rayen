<?php
session_start();

if (!isset($_SESSION['usuario_logueado'])) {
    header("Location: ../inicio/inicio.php");
    exit();
}
include("../../../Metodo/conexion.php");

$id_admin = $_SESSION['id_usuario'];
$consulta = mysqli_query($conexion, "select Rol from Usuario where id_usuario = $id_admin");
$fila = mysqli_fetch_assoc($consulta);
if ($fila['Rol'] != 2) {
    header("Location: ../Principal/principal.php");
    exit();
}

$usuarios = mysqli_query($conexion, "select id_usuario, Nombre_usuario, Nombre, Apellido, Dni, Correo_eletronico, Saldo, Rol from Usuario order by id_usuario");
$codigos = mysqli_query($conexion, "select id_codigo, codigo, premio, cantidad from codigos order by id_codigo");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración - Casino Rayen</title>
    <link rel="stylesheet" href="../../componentes/barra_superior.css">
    <link rel="stylesheet" href="administracion.css">
</head>
<body>

<?php
$barra_boton = 'cuenta';
$barra_pagina = 'admin';
include(__DIR__ . "/../../componentes/barra_superior.php");
?>

    <main class="admin">

        <?php if (isset($_GET['msg'])) { ?>
            <p class="aviso"><?= htmlspecialchars($_GET['msg']) ?></p>
        <?php } ?>
        <?php if (isset($_GET['err'])) { ?>
            <p class="aviso error"><?= htmlspecialchars($_GET['err']) ?></p>
        <?php } ?>

        <section>
            <h2>Usuarios</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Nombre</th>
                    <th>DNI</th>
                    <th>Correo</th>
                    <th>Saldo</th>
                    <th>Rol</th>
                    <th>Nuevo saldo</th>
                    <th></th>
                </tr>
                <?php while ($u = mysqli_fetch_assoc($usuarios)) { ?>
                <tr>
                    <td><?= $u['id_usuario'] ?></td>
                    <td><?= htmlspecialchars($u['Nombre_usuario']) ?></td>
                    <td><?= htmlspecialchars($u['Nombre'] . ' ' . $u['Apellido']) ?></td>
                    <td><?= htmlspecialchars($u['Dni']) ?></td>
                    <td><?= htmlspecialchars($u['Correo_eletronico']) ?></td>
                    <td><?= number_format($u['Saldo'], 0, '', '.') ?></td>
                    <td><?= $u['Rol'] == 2 ? 'Admin' : 'Usuario' ?></td>
                    <td>
                        <form class="en_linea" action="../../../Metodo/admin_modificar_saldo.php" method="POST">
                            <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                            <input type="number" name="nuevo_saldo" min="0" step="1" required>
                            <button type="submit">Guardar</button>
                        </form>
                    </td>
                    <td>
                        <?php if ($u['id_usuario'] != $id_admin) { ?>
                        <form action="../../../Metodo/admin_borrar_usuario.php" method="POST" onsubmit="return confirm('¿Borrar a <?= htmlspecialchars($u['Nombre_usuario']) ?>?');">
                            <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                            <button class="borrar" type="submit">Borrar</button>
                        </form>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </section>

        <section>
            <h2>Códigos</h2>

            <form class="en_linea nuevo_codigo" action="../../../Metodo/admin_crear_codigo.php" method="POST">
                <input type="text" name="codigo" placeholder="Código" maxlength="50" required>
                <input type="number" name="premio" min="1" step="1" placeholder="Premio" required>
                <input type="number" name="cantidad" min="1" step="1" placeholder="Usos" required>
                <button type="submit">Crear</button>
            </form>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Código</th>
                    <th>Premio</th>
                    <th>Usos</th>
                    <th></th>
                </tr>
                <?php if (!$codigos) { ?>
                <tr><td colspan="5">No se pudo leer la tabla codigos: <?= htmlspecialchars(mysqli_error($conexion)) ?></td></tr>
                <?php } else { ?>
                <?php while ($c = mysqli_fetch_assoc($codigos)) { ?>
                <tr>
                    <td><?= $c['id_codigo'] ?></td>
                    <td><?= htmlspecialchars($c['codigo']) ?></td>
                    <td><?= number_format($c['premio'], 0, '', '.') ?></td>
                    <td><?= $c['cantidad'] ?></td>
                    <td>
                        <form action="../../../Metodo/admin_borrar_codigo.php" method="POST" onsubmit="return confirm('¿Borrar el código <?= htmlspecialchars($c['codigo']) ?>?');">
                            <input type="hidden" name="id_codigo" value="<?= $c['id_codigo'] ?>">
                            <button class="borrar" type="submit">Borrar</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
                <?php } ?>
            </table>
        </section>

    </main>

</body>
</html>

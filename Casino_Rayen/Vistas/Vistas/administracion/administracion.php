<?php
session_start();

// Solo admins (rol 2) pueden entrar
if (!isset($_SESSION['usuario_logueado'])) {
    header("Location: ../inicio/inicio.php");
    exit();
}
if ($_SESSION['rol'] != 2) {
    header("Location: ../principal/principal.php");
    exit();
}

include("../../../Metodo/conexion.php");

$id_admin = $_SESSION['id_usuario'];

// Traer todos los usuarios
$sql_usuarios = "SELECT id_usuario, Nombre_usuario, Nombre, Apellido, Dni, Correo_eletronico, Saldo, Rol FROM Usuario ORDER BY id_usuario ASC";
$res_usuarios = mysqli_query($conexion, $sql_usuarios);

// Traer todos los códigos
$sql_codigos = "SELECT id_codigo, codigo, premio, cantidad FROM codigos ORDER BY id_codigo ASC";
$res_codigos = mysqli_query($conexion, $sql_codigos);
$error_codigos = !$res_codigos ? mysqli_error($conexion) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Casino Rayen</title>
    <link rel="stylesheet" href="administracion.css">
</head>
<body>

    <!-- HEADER -->
    <header class="barra_superior">
        <img class="logowich_arriba" src="../../../imagenes_y_3D/Imagenes/logo_casino_rayen.png" alt="Logo">
        <span class="titulo_admin">⚙️ Panel Admin</span>
        <div class="cosas_derecha">
            <div class="dropdown">
                <button class="btn-menu">Mi Cuenta ▼</button>
                <div class="dropdown-content">
                    <a href="../principal/principal.php">🏠 Volver al inicio</a>
                    <a href="../../../Metodo/logout.php" class="opcion-logout">❌ Cerrar sesión</a>
                </div>
            </div>
        </div>
    </header>

    <div class="contenido_admin">

        <!-- MENSAJES FLASH -->
        <?php if (isset($_GET['msg'])): ?>
            <div class="mensaje mensaje_ok"><?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['err'])): ?>
            <div class="mensaje mensaje_error"><?php echo htmlspecialchars($_GET['err']); ?></div>
        <?php endif; ?>

        <!-- =============================== -->
        <!-- SECCIÓN: USUARIOS               -->
        <!-- =============================== -->
        <div class="seccion">
            <h2>👤 Usuarios registrados</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>DNI</th>
                        <th>Correo</th>
                        <th>Saldo actual</th>
                        <th>Rol</th>
                        <th>Modificar saldo</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($u = mysqli_fetch_assoc($res_usuarios)): ?>
                    <tr>
                        <td><?php echo $u['id_usuario']; ?></td>
                        <td><?php echo htmlspecialchars($u['Nombre_usuario']); ?></td>
                        <td><?php echo htmlspecialchars($u['Nombre'] . ' ' . $u['Apellido']); ?></td>
                        <td><?php echo htmlspecialchars($u['Dni']); ?></td>
                        <td><?php echo htmlspecialchars($u['Correo_eletronico']); ?></td>
                        <td><?php echo number_format($u['Saldo'], 2, ',', '.'); ?></td>
                        <td>
                            <?php if ($u['Rol'] == 2): ?>
                                <span class="badge_admin">Admin</span>
                            <?php else: ?>
                                <span class="badge_user">Usuario</span>
                            <?php endif; ?>
                        </td>

                        <!-- Formulario modificar saldo -->
                        <td>
                            <form action="../../../Metodo/admin_modificar_saldo.php" method="POST" style="display:flex;gap:6px;align-items:center;">
                                <input type="hidden" name="id_usuario" value="<?php echo $u['id_usuario']; ?>">
                                <input class="input_saldo" type="number" name="nuevo_saldo" step="0.01" placeholder="<?php echo $u['Saldo']; ?>" required>
                                <button class="btn_accion btn_guardar" type="submit">Guardar</button>
                            </form>
                        </td>

                        <!-- Botón borrar cuenta -->
                        <td>
                            <?php if ($u['id_usuario'] != $id_admin): ?>
                                <form action="../../../Metodo/admin_borrar_usuario.php" method="POST" onsubmit="return confirm('¿Seguro que querés borrar al usuario <?php echo htmlspecialchars($u['Nombre_usuario']); ?>?');">
                                    <input type="hidden" name="id_usuario" value="<?php echo $u['id_usuario']; ?>">
                                    <button class="btn_accion btn_borrar" type="submit">Borrar</button>
                                </form>
                            <?php else: ?>
                                <span style="color:rgba(255,255,255,0.3);font-size:12px;">Sos vos</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- =============================== -->
        <!-- SECCIÓN: CÓDIGOS                -->
        <!-- =============================== -->
        <div class="seccion">
            <h2>🎟️ Códigos promocionales</h2>

            <!-- Formulario crear código -->
            <form class="form_crear_codigo" action="../../../Metodo/admin_crear_codigo.php" method="POST">
                <label>
                    Código
                    <input type="text" name="codigo" placeholder="ej: PROMO2025" maxlength="50" required>
                </label>
                <label>
                    Premio ($)
                    <input type="number" name="premio" step="0.01" placeholder="ej: 500" required>
                </label>
                <label>
                    Cantidad de usos
                    <input type="number" name="cantidad" placeholder="ej: 100" required>
                </label>
                <button type="submit">+ Crear código</button>
            </form>

            <!-- Tabla de códigos -->
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Código</th>
                        <th>Premio</th>
                        <th>Usos restantes</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($error_codigos): ?>
                    <tr><td colspan="5" style="color:#e74c3c;text-align:center;">
                        ⚠️ La tabla <b>codigos</b> no existe todavía. Corré el SQL de creación primero.<br>
                        <small style="opacity:0.6;"><?php echo htmlspecialchars($error_codigos); ?></small>
                    </td></tr>
                <?php elseif (mysqli_num_rows($res_codigos) == 0): ?>
                    <tr><td colspan="5" style="color:rgba(255,255,255,0.4);text-align:center;">No hay códigos cargados</td></tr>
                <?php else: ?>
                    <?php while ($c = mysqli_fetch_assoc($res_codigos)): ?>
                    <tr>
                        <td><?php echo $c['id_codigo']; ?></td>
                        <td><?php echo htmlspecialchars($c['codigo']); ?></td>
                        <td>$<?php echo number_format($c['premio'], 2, ',', '.'); ?></td>
                        <td><?php echo $c['cantidad']; ?></td>
                        <td>
                            <form action="../../../Metodo/admin_borrar_codigo.php" method="POST" onsubmit="return confirm('¿Borrar el código <?php echo htmlspecialchars($c['codigo']); ?>?');">
                                <input type="hidden" name="id_codigo" value="<?php echo $c['id_codigo']; ?>">
                                <button class="btn_accion btn_borrar" type="submit">Borrar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div><!-- fin contenido_admin -->

</body>
</html>

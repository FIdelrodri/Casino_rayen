<?php
// antes de incluir: $barra_boton = 'cuenta' | 'inicio' | (vacio = solo logo)
// opcional: $barra_pagina (principal / admin), $barra_saldo_texto
$boton = isset($barra_boton) ? $barra_boton : '';
$pagina = isset($barra_pagina) ? $barra_pagina : '';

if ($boton != '') {
    $id_usuario = $_SESSION['id_usuario'];
    $consulta = mysqli_query($conexion, "select Saldo, Rol from Usuario where id_usuario = $id_usuario");
    $usuario = mysqli_fetch_assoc($consulta);
    $saldo = number_format($usuario['Saldo'], 0, '', '.');
    $rol = $usuario['Rol'];
    $saldo_mostrado = isset($barra_saldo_texto) ? $barra_saldo_texto : $saldo;
}
?>
<header class="barra_superior">
    <img class="logowich_arriba" src="../../../imagenes_y_3D/Imagenes/logo_casino_rayen.png" alt="Logo">

    <?php if ($boton != '') { ?>
    <div class="cosas_derecha">

        <!-- saldo -->
        <div class="dinero_continer">
            <img class="imagen_croquetas_dinero" src="../../../imagenes_y_3D/Imagenes/croqueta.png" alt="">
            <div class="texto_dinero" id="saldo-display" data-saldo-real="<?= $saldo ?>"><?= $saldo_mostrado ?></div>
        </div>

        <!-- boton -->
        <div class="barra_derecha">
            <?php if ($boton == 'inicio') { ?>
                <a href="../../Vistas/Principal/principal.php" class="btn-menu">Inicio</a>
            <?php } else { ?>
                <div class="dropdown">
                    <button class="btn-menu" type="button">Mi Cuenta ▼</button>
                    <div class="dropdown-content">
                        <?php if ($pagina != 'principal') { ?>
                            <a href="../../Vistas/Principal/principal.php">🏠 Volver al inicio</a>
                        <?php } ?>
                        <?php if ($pagina != 'banco') { ?>
                            <a href="../../Vistas/banco/banco.php">🏦 Banco</a>
                        <?php } ?>
                        <a href="#" id="abrir_canje">🎟️Canjear código</a>
                        <?php if ($rol == 2 && $pagina != 'admin') { ?>
                            <a href="../../Vistas/administracion/administracion.php">👑 Modo administracion</a>
                        <?php } ?>
                        <a href="../../../Metodo/logout.php" class="opcion-logout">❌ Cerrar sesion</a>
                    </div>
                </div>
            <?php } ?>
        </div>

    </div>
    <?php } ?>
</header>

<?php if ($boton == 'cuenta') { ?>
<dialog class="ventana_canje" id="ventana_canje">
    <div class="ventana_canje_contenido">
        <h2>Canjear código</h2>

        <?php if (isset($_GET['canje_msg'])) { ?>
            <p class="canje_aviso"><?= htmlspecialchars($_GET['canje_msg']) ?></p>
        <?php } ?>
        <?php if (isset($_GET['canje_err'])) { ?>
            <p class="canje_aviso canje_error"><?= htmlspecialchars($_GET['canje_err']) ?></p>
        <?php } ?>

        <form action="../../../Metodo/canjear_codigo.php" method="POST">
            <input type="hidden" name="pagina" value="<?= $pagina ?>">
            <input type="text" name="codigo" placeholder="Código promocional" maxlength="50" required>
            <div class="canje_botones">
                <button type="button" id="cerrar_canje">Cerrar</button>
                <button type="submit">Canjear</button>
            </div>
        </form>
    </div>
</dialog>

<script>
    var ventana = document.getElementById('ventana_canje');

    document.getElementById('abrir_canje').addEventListener('click', function (e) {
        e.preventDefault();
        ventana.showModal();
    });
    document.getElementById('cerrar_canje').addEventListener('click', function () {
        ventana.close();
    });
    ventana.addEventListener('click', function (e) {
        if (e.target === ventana) {
            ventana.close();
        }
    });

    <?php if (isset($_GET['canje_msg']) || isset($_GET['canje_err'])) { ?>
    ventana.showModal();
    history.replaceState(null, '', location.pathname);
    <?php } ?>
</script>
<?php } ?>

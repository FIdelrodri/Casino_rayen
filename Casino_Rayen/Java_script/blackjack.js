var formulario = document.getElementById('form_blackjack');
var campoApuesta = document.getElementById('campo_apuesta');
var botonRepartir = document.getElementById('boton_repartir');
var controles = document.getElementById('controles');
var botonPedir = document.getElementById('boton_pedir');
var botonPlantarse = document.getElementById('boton_plantarse');
var botonDoblar = document.getElementById('boton_doblar');
var aviso = document.getElementById('aviso_blackjack');
var textoResultado = document.getElementById('resultado_ronda');
var cartasCrupier = document.getElementById('cartas_crupier');
var cartasJugador = document.getElementById('cartas_jugador');
var puntosCrupier = document.getElementById('puntos_crupier');
var puntosJugador = document.getElementById('puntos_jugador');
var saldoBarra = document.getElementById('saldo-display');

var RUTA_CARTAS = '../../../imagenes_y_3D/Imagenes/cartas/';

// formato
function formatoNumero(numero) {
    return numero.toLocaleString('es-AR');
}

function actualizarSaldo(saldo) {
    if (!saldoBarra || saldo === undefined) return;
    saldoBarra.textContent = formatoNumero(saldo);
    saldoBarra.setAttribute('data-saldo-real', formatoNumero(saldo));
}

function avisar(mensaje) {
    aviso.textContent = mensaje;
    aviso.className = mensaje === '' ? 'aviso_blackjack' : 'aviso_blackjack error';
}

// servidor
function llamar(accion, datos) {
    var cuerpo = new FormData();
    cuerpo.append('accion', accion);
    for (var clave in datos) {
        cuerpo.append(clave, datos[clave]);
    }
    return fetch('blackjack_action.php', { method: 'POST', body: cuerpo })
        .then(function (respuesta) { return respuesta.json(); })
        .catch(function () { return { ok: false, error: 'No se pudo conectar con el servidor' }; });
}

// cartas
function imagenCarta(carta) {
    var imagen = document.createElement('img');
    imagen.className = 'carta';
    if (carta === null) {
        imagen.src = RUTA_CARTAS + 'dorso.png';
        imagen.alt = 'Carta tapada';
    } else {
        imagen.src = RUTA_CARTAS + carta.palo + '/' + carta.valor + '.png';
        imagen.alt = carta.valor + ' ' + carta.palo;
    }
    return imagen;
}

function dibujarMano(contenedor, cartas) {
    contenedor.innerHTML = '';
    contenedor.className = cartas.length > 4 ? 'cartas apretada' : 'cartas';
    cartas.forEach(function (carta) {
        contenedor.appendChild(imagenCarta(carta));
    });
}

function mostrarMesa(ronda) {
    dibujarMano(cartasCrupier, ronda.crupier);
    dibujarMano(cartasJugador, ronda.jugador);
    puntosJugador.textContent = ronda.puntos_jugador;
    puntosCrupier.textContent = ronda.puntos_crupier === null ? '' : ronda.puntos_crupier;
}

// resultado
function mostrarResultado(ronda) {
    var texto = '';
    var clase = 'resultado_ronda';
    var apuesta = formatoNumero(ronda.apuesta);

    if (ronda.resultado === 'blackjack') {
        texto = '¡Blackjack! Cobrás ' + formatoNumero(ronda.premio);
        clase += ' ganaste';
    } else if (ronda.resultado === 'ganaste') {
        texto = 'Ganaste, cobrás ' + formatoNumero(ronda.premio);
        clase += ' ganaste';
    } else if (ronda.resultado === 'empate') {
        texto = 'Empate, te devolvemos ' + apuesta;
    } else if (ronda.resultado === 'pasaste') {
        texto = 'Te pasaste de 21, perdiste ' + apuesta;
        clase += ' perdiste';
    } else {
        texto = 'Perdiste ' + apuesta;
        clase += ' perdiste';
    }
    textoResultado.textContent = texto;
    textoResultado.className = clase;
}

function limpiarResultado() {
    textoResultado.textContent = '';
    textoResultado.className = 'resultado_ronda';
}

// botones
function pasarAEspera() {
    controles.hidden = true;
    botonRepartir.hidden = false;
    botonRepartir.disabled = false;
    campoApuesta.disabled = false;
}

function pasarARonda(ronda) {
    controles.hidden = false;
    botonRepartir.hidden = true;
    campoApuesta.disabled = true;
    campoApuesta.value = ronda.apuesta;
    botonPedir.disabled = false;
    botonPlantarse.disabled = false;
    botonDoblar.disabled = false;
    botonDoblar.hidden = !ronda.puede_doblar;
}

function aplicarRespuesta(respuesta) {
    mostrarMesa(respuesta);
    actualizarSaldo(respuesta.saldo);
    if (respuesta.estado === 'jugando') {
        limpiarResultado();
        pasarARonda(respuesta);
    } else {
        mostrarResultado(respuesta);
        campoApuesta.value = respuesta.apuesta;
        pasarAEspera();
    }
}

function jugada(accion) {
    botonPedir.disabled = true;
    botonPlantarse.disabled = true;
    botonDoblar.disabled = true;
    llamar(accion, {}).then(function (respuesta) {
        if (!respuesta.ok) {
            avisar(respuesta.error);
            botonPedir.disabled = false;
            botonPlantarse.disabled = false;
            botonDoblar.disabled = false;
            return;
        }
        avisar('');
        aplicarRespuesta(respuesta);
    });
}

function repartir() {
    var valor = campoApuesta.value.trim();
    if (!/^[0-9]+$/.test(valor) || parseInt(valor, 10) < 1) {
        avisar('La apuesta tiene que ser un número entero mayor a 0');
        return;
    }

    botonRepartir.disabled = true;
    llamar('apostar', { apuesta: valor }).then(function (respuesta) {
        if (!respuesta.ok) {
            avisar(respuesta.error);
            botonRepartir.disabled = false;
            return;
        }
        avisar('');
        aplicarRespuesta(respuesta);
    });
}

formulario.addEventListener('submit', function (e) {
    e.preventDefault();
    repartir();
});
botonPedir.addEventListener('click', function () { jugada('pedir'); });
botonPlantarse.addEventListener('click', function () { jugada('plantarse'); });
botonDoblar.addEventListener('click', function () { jugada('doblar'); });

// al cargar la página
llamar('estado', {}).then(function (respuesta) {
    if (respuesta.ok && respuesta.estado !== 'ninguna') {
        aplicarRespuesta(respuesta);
    }
});

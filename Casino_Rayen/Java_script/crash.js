var escena = document.getElementById('escena');
var cohete = document.getElementById('cohete');
var textoMultiplicador = document.getElementById('multiplicador');
var formulario = document.getElementById('form_crash');
var campoApuesta = document.getElementById('campo_apuesta');
var boton = document.getElementById('boton_crash');
var aviso = document.getElementById('aviso_crash');
var saldoBarra = document.getElementById('saldo-display');

var RUTA = '../../../imagenes_y_3D/Imagenes/';
var IMAGEN_QUIETO = RUTA + 'choete_gino_1.png';
var IMAGEN_VOLANDO = RUTA + 'choete_gino_2.png';
var IMAGEN_EXPLOTADO = RUTA + 'choete_gino_3.png';

var RUTA_AUDIO = '../../../imagenes_y_3D/audio/';
var sonidoVuelo = new Audio(RUTA_AUDIO + 'Vuelo_crash_sound.mp3');
var sonidoExplosion = new Audio(RUTA_AUDIO + 'explosion_crash_sound.mp3');
sonidoVuelo.loop = true;
sonidoVuelo.volume = 0.5;
sonidoExplosion.volume = 0.7;

var TIEMPO_SUBIDA = 3000;
var ESPERA_CONSULTA = 120;

var estado = 'espera';
var apuesta = 0;
var intervalo = null;
var consultando = false;

[IMAGEN_QUIETO, IMAGEN_VOLANDO, IMAGEN_EXPLOTADO].forEach(function (ruta) {
    new Image().src = ruta;
});

function reproducir(sonido) {
    sonido.currentTime = 0;
    var intento = sonido.play();
    if (intento) {
        intento.catch(function () {});
    }
}

function frenar(sonido) {
    sonido.pause();
    sonido.currentTime = 0;
}

function formato(multiplicador) {
    return (multiplicador / 100).toFixed(2) + 'x';
}

function formatoNumero(numero) {
    return numero.toLocaleString('es-AR');
}

function actualizarSaldo(saldo) {
    if (!saldoBarra) return;
    saldoBarra.textContent = formatoNumero(saldo);
    saldoBarra.setAttribute('data-saldo-real', formatoNumero(saldo));
}

function avisar(mensaje, tipo) {
    aviso.textContent = mensaje;
    aviso.className = 'aviso_crash ' + tipo;
}

// posiciones verticales del cohete (top, en px)
function posiciones() {
    var alto = escena.clientHeight;
    var altoCohete = cohete.clientHeight;
    return {
        suelo: alto - altoCohete * 0.66,
        medio: alto / 2 - altoCohete * 0.33,
        arriba: -altoCohete
    };
}

function mover(y, segundos) {
    if (segundos > 0) {
        cohete.style.transition = 'top ' + segundos + 's ease-in-out';
    } else {
        cohete.style.transition = 'none';
    }
    cohete.style.top = y + 'px';
}

function posicionActual() {
    return cohete.getBoundingClientRect().top - escena.getBoundingClientRect().top;
}

function mostrarMultiplicador(multiplicador) {
    textoMultiplicador.textContent = formato(multiplicador);
    boton.textContent = 'Retirar ' + formatoNumero(Math.floor(apuesta * multiplicador / 100));
}

function llamar(accion, datos) {
    var cuerpo = new FormData();
    cuerpo.append('accion', accion);
    for (var clave in datos) {
        cuerpo.append(clave, datos[clave]);
    }
    return fetch('crash_action.php', { method: 'POST', body: cuerpo })
        .then(function (respuesta) { return respuesta.json(); })
        .catch(function () { return { ok: false, error: 'No se pudo conectar con el servidor' }; });
}

function detenerConsultas() {
    clearInterval(intervalo);
    intervalo = null;
}

function habilitar() {
    campoApuesta.disabled = false;
    boton.disabled = false;
    boton.textContent = 'Apostar';
}

function despegar(transcurrido) {
    estado = 'volando';
    campoApuesta.value = apuesta;
    campoApuesta.disabled = true;
    boton.disabled = false;
    textoMultiplicador.className = 'multiplicador';
    mostrarMultiplicador(100);

    frenar(sonidoExplosion);
    reproducir(sonidoVuelo);

    var lugar = posiciones();
    cohete.src = IMAGEN_VOLANDO;
    if (transcurrido >= TIEMPO_SUBIDA) {
        mover(lugar.medio, 0);
    } else {
        mover(lugar.suelo, 0);
        void cohete.offsetHeight;
        mover(lugar.medio, (TIEMPO_SUBIDA - transcurrido) / 1000);
    }

    intervalo = setInterval(consultar, ESPERA_CONSULTA);
}

function explotar(respuesta, conSonido) {
    detenerConsultas();
    estado = 'espera';
    frenar(sonidoVuelo);
    if (conSonido) {
        reproducir(sonidoExplosion);
    }
    mover(posicionActual(), 0);
    cohete.src = IMAGEN_EXPLOTADO;
    textoMultiplicador.textContent = formato(respuesta.multiplicador);
    textoMultiplicador.className = 'multiplicador explotado';
    avisar('Explotó en ' + formato(respuesta.multiplicador) + ', perdiste ' + formatoNumero(apuesta), 'error');
    if (respuesta.saldo !== undefined) actualizarSaldo(respuesta.saldo);
    habilitar();
}

function retirado(respuesta) {
    detenerConsultas();
    estado = 'espera';
    frenar(sonidoVuelo);
    textoMultiplicador.textContent = formato(respuesta.multiplicador);
    textoMultiplicador.className = 'multiplicador retirado';
    avisar('Retiraste en ' + formato(respuesta.multiplicador) + ' y recibiste ' + formatoNumero(respuesta.premio), 'ok');
    actualizarSaldo(respuesta.saldo);
    mover(posicionActual(), 0);
    void cohete.offsetHeight;
    mover(posiciones().arriba, 1.2);
    habilitar();
}

function consultar() {
    if (consultando) return;
    consultando = true;
    llamar('estado', {}).then(function (respuesta) {
        consultando = false;
        if (estado !== 'volando' || !respuesta.ok) return;
        if (respuesta.estado === 'volando') {
            mostrarMultiplicador(respuesta.multiplicador);
        } else if (respuesta.estado === 'explotado') {
            explotar(respuesta, true);
        } else {
            detenerConsultas();
            estado = 'espera';
            frenar(sonidoVuelo);
            avisar('La ronda terminó', 'error');
            habilitar();
        }
    });
}

function apostar() {
    var valor = campoApuesta.value.trim();
    if (!/^[0-9]+$/.test(valor) || parseInt(valor, 10) < 1) {
        avisar('La apuesta tiene que ser un número entero mayor a 0', 'error');
        return;
    }

    boton.disabled = true;
    llamar('apostar', { apuesta: valor }).then(function (respuesta) {
        if (!respuesta.ok) {
            avisar(respuesta.error, 'error');
            boton.disabled = false;
            return;
        }
        apuesta = parseInt(valor, 10);
        actualizarSaldo(respuesta.saldo);
        avisar('', '');
        cohete.src = IMAGEN_QUIETO;
        mover(posiciones().suelo, 0);
        void cohete.offsetHeight;
        despegar(0);
    });
}

function retirar() {
    boton.disabled = true;
    llamar('retirar', {}).then(function (respuesta) {
        if (estado !== 'volando') return;
        if (!respuesta.ok) {
            avisar(respuesta.error, 'error');
            boton.disabled = false;
            return;
        }
        if (respuesta.estado === 'retirado') {
            retirado(respuesta);
        } else {
            explotar(respuesta, true);
        }
    });
}

formulario.addEventListener('submit', function (e) {
    e.preventDefault();
    if (estado === 'espera') {
        apostar();
    } else {
        retirar();
    }
});

// estado con el que se carga la página
var inicial = escena.getAttribute('data-estado');
apuesta = parseInt(escena.getAttribute('data-apuesta'), 10);

if (inicial === 'volando') {
    despegar(parseInt(escena.getAttribute('data-transcurrido'), 10));
} else if (inicial === 'explotado') {
    campoApuesta.value = apuesta;
    explotar({ multiplicador: parseInt(escena.getAttribute('data-multiplicador'), 10) }, false);
    mover(posiciones().medio, 0);
} else {
    mover(posiciones().suelo, 0);
}

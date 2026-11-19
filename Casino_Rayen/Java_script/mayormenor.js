var formulario = document.getElementById('form_mayormenor');
var campoApuesta = document.getElementById('campo_apuesta');
var botonEmpezar = document.getElementById('boton_empezar');
var controles = document.getElementById('controles');
var botonMayor = document.getElementById('boton_mayor');
var botonMenor = document.getElementById('boton_menor');
var botonSaltar = document.getElementById('boton_saltar');
var botonCobrar = document.getElementById('boton_cobrar');
var aviso = document.getElementById('aviso_mayormenor');
var textoResultado = document.getElementById('resultado_ronda');
var textoMultiplicador = document.getElementById('texto_multiplicador');
var textoPremio = document.getElementById('texto_premio');
var cartaActual = document.getElementById('carta_actual');
var cartaSiguiente = document.getElementById('carta_siguiente');
var historial = document.getElementById('historial');
var saldoBarra = document.getElementById('saldo-display');

var RUTA_CARTAS = '../../../imagenes_y_3D/Imagenes/cartas/';
var DEMORA_REVELAR = 1100;

// formato
function formatoNumero(numero) {
    return numero.toLocaleString('es-AR');
}

// se corta (no se redondea) para no mostrar nunca más de lo que se paga
function formatoMultiplicador(multiplicador) {
    return (Math.floor(multiplicador / 100) / 100).toFixed(2) + 'x';
}

function actualizarSaldo(saldo) {
    if (!saldoBarra || saldo === undefined) return;
    saldoBarra.textContent = formatoNumero(saldo);
    saldoBarra.setAttribute('data-saldo-real', formatoNumero(saldo));
}

function avisar(mensaje) {
    aviso.textContent = mensaje;
    aviso.className = mensaje === '' ? 'aviso_mayormenor' : 'aviso_mayormenor error';
}

function nombreCarta(carta) {
    if (carta.valor === 'as') return 'un As';
    if (carta.valor === 'j') return 'una J';
    if (carta.valor === 'q') return 'una Q';
    if (carta.valor === 'k') return 'una K';
    return 'un ' + carta.valor;
}

function rango(carta) {
    var letras = { as: 1, j: 11, q: 12, k: 13 };
    return letras[carta.valor] || parseInt(carta.valor, 10);
}

// servidor
function llamar(accion, datos) {
    var cuerpo = new FormData();
    cuerpo.append('accion', accion);
    for (var clave in datos) {
        cuerpo.append(clave, datos[clave]);
    }
    return fetch('mayormenor_action.php', { method: 'POST', body: cuerpo })
        .then(function (respuesta) { return respuesta.json(); })
        .catch(function () { return { ok: false, error: 'No se pudo conectar con el servidor' }; });
}

// cartas
function ponerCarta(imagen, carta) {
    if (carta === null) {
        imagen.src = RUTA_CARTAS + 'dorso.png';
        imagen.alt = 'Carta tapada';
    } else {
        imagen.src = RUTA_CARTAS + carta.palo + '/' + carta.valor + '.png';
        imagen.alt = carta.valor + ' ' + carta.palo;
    }
}

function dibujarHistorial(cartas) {
    historial.innerHTML = '';
    cartas.slice(-6).forEach(function (carta) {
        var miniatura = document.createElement('img');
        miniatura.className = 'miniatura';
        miniatura.src = RUTA_CARTAS + carta.palo + '/' + carta.valor + '.png';
        miniatura.alt = carta.valor + ' ' + carta.palo;
        historial.appendChild(miniatura);
    });
}

// botones
function textoOpcion(boton, nombre, opcion) {
    boton.innerHTML = '';
    var titulo = document.createElement('span');
    titulo.textContent = nombre;
    boton.appendChild(titulo);

    var detalle = document.createElement('small');
    if (opcion === null) {
        detalle.textContent = 'No se puede';
        boton.disabled = true;
    } else {
        detalle.textContent = opcion.probabilidad + '% · ' + formatoMultiplicador(opcion.multiplicador);
        boton.disabled = false;
    }
    boton.appendChild(detalle);
}

function bloquearBotones() {
    botonMayor.disabled = true;
    botonMenor.disabled = true;
    botonSaltar.disabled = true;
    botonCobrar.disabled = true;
}

function pasarAEspera() {
    controles.hidden = true;
    botonEmpezar.hidden = false;
    botonEmpezar.disabled = false;
    campoApuesta.disabled = false;
}

function mostrarRonda(ronda) {
    ponerCarta(cartaActual, ronda.actual);
    ponerCarta(cartaSiguiente, null);
    textoMultiplicador.textContent = formatoMultiplicador(ronda.multiplicador);
    textoPremio.textContent = formatoNumero(ronda.premio_posible);
    dibujarHistorial(ronda.historial);
    actualizarSaldo(ronda.saldo);

    controles.hidden = false;
    botonEmpezar.hidden = true;
    campoApuesta.disabled = true;
    campoApuesta.value = ronda.apuesta;

    textoOpcion(botonMayor, 'Mayor ▲', ronda.opciones.mayor);
    textoOpcion(botonMenor, 'Menor ▼', ronda.opciones.menor);
    botonSaltar.disabled = false;
    botonCobrar.disabled = ronda.aciertos < 1;
    botonCobrar.textContent = ronda.aciertos < 1 ? 'Cobrar' : 'Cobrar ' + formatoNumero(ronda.premio_posible);
}

// resultado
function mostrarResultado(texto, tipo) {
    textoResultado.textContent = texto;
    textoResultado.className = tipo === '' ? 'resultado_ronda' : 'resultado_ronda ' + tipo;
}

function mostrarFinal(ronda) {
    ponerCarta(cartaActual, ronda.actual);
    ponerCarta(cartaSiguiente, ronda.revelada);
    textoMultiplicador.textContent = formatoMultiplicador(ronda.multiplicador);
    dibujarHistorial(ronda.historial);
    actualizarSaldo(ronda.saldo);
    campoApuesta.value = ronda.apuesta;

    if (ronda.resultado === 'perdiste') {
        textoMultiplicador.textContent = '0.00x';
        textoPremio.textContent = '0';
        var empate = ronda.revelada && rango(ronda.revelada) === rango(ronda.actual);
        var detalle = empate ? 'Salió el mismo valor y el empate pierde' : 'Salió ' + nombreCarta(ronda.revelada);
        mostrarResultado(detalle + ', perdiste ' + formatoNumero(ronda.apuesta), 'perdiste');
    } else if (ronda.resultado === 'maximo') {
        textoPremio.textContent = formatoNumero(ronda.premio);
        mostrarResultado('Llegaste al máximo, cobrás ' + formatoNumero(ronda.premio), 'ganaste');
    } else {
        textoPremio.textContent = formatoNumero(ronda.premio);
        mostrarResultado('Cobraste ' + formatoNumero(ronda.premio) + ' (' + formatoMultiplicador(ronda.multiplicador) + ')', 'ganaste');
    }
    pasarAEspera();
}

function aplicarRespuesta(respuesta) {
    if (respuesta.estado === 'jugando') {
        mostrarResultado('', '');
        mostrarRonda(respuesta);
    } else {
        mostrarFinal(respuesta);
    }
}

// jugadas
function adivinar(eleccion) {
    bloquearBotones();
    llamar('adivinar', { eleccion: eleccion }).then(function (respuesta) {
        if (!respuesta.ok) {
            avisar(respuesta.error);
            botonSaltar.disabled = false;
            return;
        }
        avisar('');

        if (respuesta.estado === 'terminada') {
            mostrarFinal(respuesta);
            return;
        }

        // acertó: se muestra la carta que salió y después pasa a ser la actual
        ponerCarta(cartaActual, respuesta.anterior);
        ponerCarta(cartaSiguiente, respuesta.revelada);
        mostrarResultado('¡Acertaste! Salió ' + nombreCarta(respuesta.revelada), 'ganaste');
        setTimeout(function () {
            aplicarRespuesta(respuesta);
        }, DEMORA_REVELAR);
    });
}

function simple(accion) {
    bloquearBotones();
    llamar(accion, {}).then(function (respuesta) {
        if (!respuesta.ok) {
            avisar(respuesta.error);
            botonSaltar.disabled = false;
            return;
        }
        avisar('');
        aplicarRespuesta(respuesta);
    });
}

function empezar() {
    var valor = campoApuesta.value.trim();
    if (!/^[0-9]+$/.test(valor) || parseInt(valor, 10) < 1) {
        avisar('La apuesta tiene que ser un número entero mayor a 0');
        return;
    }

    botonEmpezar.disabled = true;
    llamar('apostar', { apuesta: valor }).then(function (respuesta) {
        if (!respuesta.ok) {
            avisar(respuesta.error);
            botonEmpezar.disabled = false;
            return;
        }
        avisar('');
        aplicarRespuesta(respuesta);
    });
}

formulario.addEventListener('submit', function (e) {
    e.preventDefault();
    empezar();
});
botonMayor.addEventListener('click', function () { adivinar('mayor'); });
botonMenor.addEventListener('click', function () { adivinar('menor'); });
botonSaltar.addEventListener('click', function () { simple('saltar'); });
botonCobrar.addEventListener('click', function () { simple('cobrar'); });

// al cargar la página
llamar('estado', {}).then(function (respuesta) {
    if (respuesta.ok && respuesta.estado !== 'ninguna') {
        aplicarRespuesta(respuesta);
    }
});

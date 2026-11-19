var mesa = document.getElementById('mesa');
var contenedorRodillos = document.getElementById('rodillos');
var capaLineas = document.getElementById('lineas');
var formulario = document.getElementById('form_tragaperras');
var botonGirar = document.getElementById('boton_girar');
var botonMenos = document.getElementById('boton_menos');
var botonMas = document.getElementById('boton_mas');
var botonMaxima = document.getElementById('boton_maxima');
var botonAuto = document.getElementById('boton_auto');
var botonSonido = document.getElementById('boton_sonido');
var botonPagos = document.getElementById('boton_pagos');
var campoApuesta = document.getElementById('campo_apuesta');
var textoLinea = document.getElementById('texto_linea');
var textoMensaje = document.getElementById('texto_mensaje');
var textoGanancia = document.getElementById('texto_ganancia');
var avisoBono = document.getElementById('aviso_bono');
var avisoError = document.getElementById('aviso_tragaperras');
var festejo = document.getElementById('festejo');
var festejoTitulo = document.getElementById('festejo_titulo');
var festejoMonto = document.getElementById('festejo_monto');
var festejoImagen = document.getElementById('festejo_imagen');
var botonSeguir = document.getElementById('boton_seguir');
var dialogoPagos = document.getElementById('dialogo_pagos');
var contenidoPagos = document.getElementById('contenido_pagos');
var lienzo = document.getElementById('lluvia');
var saldoBarra = document.getElementById('saldo-display');

var RUTA = '../../../imagenes_y_3D/Imagenes/tragaperras/';
var SIMBOLOS = ['pata', 'hueso', 'pelota', 'plato', 'collar', 'casita', 'rayen', 'dorado', 'bolsa'];
var APUESTA_MINIMA = 20;
var COLORES_LINEA = ['#ff4d6d', '#4dd0ff', '#7dff6a', '#ffb347', '#d47bff', '#ffee58', '#ff7a45', '#4dffc3', '#ff6ad5', '#9aa8ff',
                     '#c6ff4d', '#ff9a9a', '#5ad1ff', '#ffd166', '#b28dff', '#6bffb0', '#ff8f4d', '#4d9dff', '#e8ff6a', '#ff5ac8'];

var saldo = 0;
var tabla = null;
var rodando = false;
var automatico = false;
var sonidoActivo = true;
var giroGratis = 0;
var grillaActual = [];
var cicloLineas = null;
var saltarYa = false;
var despertar = null;

try {
    if (localStorage.getItem('rayen_tragaperras_sonido') === 'no') sonidoActivo = false;
} catch (e) {}

// formato
function formatoNumero(numero) {
    return numero.toLocaleString('es-AR');
}

function actualizarSaldo(valor) {
    if (!saldoBarra || valor === undefined) return;
    saldoBarra.textContent = formatoNumero(valor);
    saldoBarra.setAttribute('data-saldo-real', formatoNumero(valor));
}

function avisar(mensaje) {
    avisoError.textContent = mensaje;
}

function imagenDe(nombre) {
    return RUTA + nombre + '.svg';
}

// esperas que se pueden saltar con el botón girar
function esperar(ms) {
    return new Promise(function (listo) {
        var temporizador = setTimeout(fin, ms);
        despertar = fin;
        function fin() {
            clearTimeout(temporizador);
            despertar = null;
            listo();
        }
    });
}

function pedirSalto() {
    saltarYa = true;
    if (despertar) despertar();
}

// sonido
var audio = null;
function contexto() {
    if (!sonidoActivo) return null;
    if (!audio) {
        try {
            audio = new (window.AudioContext || window.webkitAudioContext)();
        } catch (e) {
            return null;
        }
    }
    if (audio.state === 'suspended') audio.resume();
    return audio;
}

function tono(frecuencia, duracion, tipo, volumen, demora, hasta) {
    var c = contexto();
    if (!c) return;
    var inicio = c.currentTime + (demora || 0);
    var oscilador = c.createOscillator();
    var ganancia = c.createGain();
    oscilador.type = tipo || 'sine';
    oscilador.frequency.setValueAtTime(frecuencia, inicio);
    if (hasta) oscilador.frequency.exponentialRampToValueAtTime(hasta, inicio + duracion);
    ganancia.gain.setValueAtTime(0.0001, inicio);
    ganancia.gain.exponentialRampToValueAtTime(volumen || 0.15, inicio + 0.01);
    ganancia.gain.exponentialRampToValueAtTime(0.0001, inicio + duracion);
    oscilador.connect(ganancia);
    ganancia.connect(c.destination);
    oscilador.start(inicio);
    oscilador.stop(inicio + duracion + 0.05);
}

function sonidoGiro() {
    tono(180, 0.5, 'sawtooth', 0.05, 0, 520);
}
function sonidoParada() {
    tono(140, 0.12, 'triangle', 0.22, 0, 60);
}
function sonidoMoneda(demora) {
    tono(1320, 0.09, 'square', 0.07, demora || 0);
    tono(1980, 0.16, 'square', 0.07, (demora || 0) + 0.07);
}
function sonidoGanar(nivel) {
    var notas = [523, 659, 784, 1047, 1319, 1568, 2093];
    var cantidad = { normal: 3, grande: 5, mega: 6, super: 7, jackpot: 7 }[nivel] || 3;
    for (var i = 0; i < cantidad; i++) {
        tono(notas[i], 0.25, 'triangle', 0.14, i * 0.09);
        tono(notas[i] * 2, 0.2, 'sine', 0.05, i * 0.09);
    }
}
function sonidoJackpot() {
    for (var vuelta = 0; vuelta < 4; vuelta++) {
        var notas = [523, 659, 784, 1047, 784, 1047, 1319, 1568];
        for (var i = 0; i < notas.length; i++) {
            tono(notas[i], 0.22, 'square', 0.08, vuelta * 0.9 + i * 0.1);
            tono(notas[i] / 2, 0.22, 'triangle', 0.12, vuelta * 0.9 + i * 0.1);
        }
    }
}
function sonidoCuenta(paso) {
    tono(600 + (paso % 12) * 40, 0.05, 'square', 0.04);
}

function vibrar(patron) {
    if (navigator.vibrate) navigator.vibrate(patron);
}

// lluvia de monedas
var pincel = lienzo.getContext('2d');
var monedas = [];
var animandoLluvia = false;

function ajustarLienzo() {
    lienzo.width = window.innerWidth;
    lienzo.height = window.innerHeight;
}
window.addEventListener('resize', ajustarLienzo);
ajustarLienzo();

function lluvia(cantidad) {
    for (var i = 0; i < cantidad; i++) {
        monedas.push({
            x: Math.random() * lienzo.width,
            y: -Math.random() * lienzo.height * 0.7 - 20,
            vy: 3 + Math.random() * 5,
            vx: (Math.random() - 0.5) * 2.5,
            radio: 9 + Math.random() * 9,
            angulo: Math.random() * 6.28,
            giro: 0.08 + Math.random() * 0.2,
            croqueta: Math.random() < 0.18
        });
    }
    if (!animandoLluvia) {
        animandoLluvia = true;
        requestAnimationFrame(pintarLluvia);
    }
}

function pintarLluvia() {
    pincel.clearRect(0, 0, lienzo.width, lienzo.height);
    for (var i = monedas.length - 1; i >= 0; i--) {
        var m = monedas[i];
        m.y += m.vy;
        m.x += m.vx;
        m.vy += 0.06;
        m.angulo += m.giro;
        if (m.y > lienzo.height + 30) {
            monedas.splice(i, 1);
            continue;
        }
        var ancho = Math.max(2, Math.abs(Math.cos(m.angulo)) * m.radio);
        if (m.croqueta) {
            pincel.fillStyle = '#b9743a';
            pincel.strokeStyle = '#6b3c18';
            pincel.lineWidth = 2;
            pincel.beginPath();
            pincel.ellipse(m.x, m.y, ancho * 0.8, m.radio * 0.8, 0, 0, 6.28);
            pincel.fill();
            pincel.stroke();
        } else {
            var degradado = pincel.createLinearGradient(m.x - ancho, m.y - m.radio, m.x + ancho, m.y + m.radio);
            degradado.addColorStop(0, '#fff7a8');
            degradado.addColorStop(1, '#d99a00');
            pincel.fillStyle = degradado;
            pincel.strokeStyle = '#8a5d00';
            pincel.lineWidth = 2;
            pincel.beginPath();
            pincel.ellipse(m.x, m.y, ancho, m.radio, 0, 0, 6.28);
            pincel.fill();
            pincel.stroke();
        }
    }
    if (monedas.length > 0) {
        requestAnimationFrame(pintarLluvia);
    } else {
        animandoLluvia = false;
        pincel.clearRect(0, 0, lienzo.width, lienzo.height);
    }
}

// contador que sube
function contar(desde, hasta, duracion, alCambiar) {
    return new Promise(function (listo) {
        var inicio = performance.now();
        var ultimo = 0;
        var paso = 0;
        function cuadro(ahora) {
            var avance = Math.min(1, (ahora - inicio) / duracion);
            if (saltarYa) avance = 1;
            var suave = 1 - Math.pow(1 - avance, 3);
            var valor = Math.round(desde + (hasta - desde) * suave);
            alCambiar(valor);
            if (ahora - ultimo > 70 && avance < 1) {
                sonidoCuenta(paso++);
                ultimo = ahora;
            }
            if (avance < 1) {
                requestAnimationFrame(cuadro);
            } else {
                listo();
            }
        }
        requestAnimationFrame(cuadro);
    });
}

// grilla
function simboloAlAzar() {
    return SIMBOLOS[Math.floor(Math.random() * SIMBOLOS.length)];
}

function crearCelda(simbolo) {
    var celda = document.createElement('div');
    celda.className = 'celda';
    var imagen = document.createElement('img');
    imagen.src = imagenDe(simbolo);
    imagen.alt = '';
    imagen.draggable = false;
    celda.appendChild(imagen);
    return celda;
}

function armarRodillos() {
    contenedorRodillos.innerHTML = '';
    grillaActual = [[], [], []];
    for (var c = 0; c < 5; c++) {
        var rodillo = document.createElement('div');
        rodillo.className = 'rodillo';
        var cinta = document.createElement('div');
        cinta.className = 'cinta';
        for (var f = 0; f < 3; f++) {
            var simbolo = simboloAlAzar();
            grillaActual[f][c] = simbolo;
            cinta.appendChild(crearCelda(simbolo));
        }
        rodillo.appendChild(cinta);
        contenedorRodillos.appendChild(rodillo);
    }
}

function celdaEn(fila, columna) {
    return contenedorRodillos.children[columna].querySelector('.cinta').children[fila];
}

// giro visual de un rodillo
function girarRodillo(columna, nuevos, duracion) {
    return new Promise(function (listo) {
        var rodillo = contenedorRodillos.children[columna];
        var cinta = rodillo.querySelector('.cinta');
        var viejos = [grillaActual[0][columna], grillaActual[1][columna], grillaActual[2][columna]];
        var relleno = 12 + columna * 5;
        var lista = nuevos.slice();
        for (var i = 0; i < relleno; i++) lista.push(simboloAlAzar());
        lista = lista.concat(viejos);

        cinta.innerHTML = '';
        lista.forEach(function (simbolo) {
            cinta.appendChild(crearCelda(simbolo));
        });

        var total = lista.length;
        cinta.style.transition = 'none';
        cinta.style.transform = 'translateY(-' + ((total - 3) / total * 100) + '%)';
        void cinta.offsetHeight;

        rodillo.classList.add('moviendo');
        cinta.style.transition = 'transform ' + duracion + 'ms cubic-bezier(.2,.75,.25,1.04)';
        cinta.style.transform = 'translateY(0)';

        setTimeout(function () {
            rodillo.classList.remove('moviendo');
        }, duracion - 260);

        setTimeout(function () {
            // se deja solo las 3 celdas que se ven
            cinta.style.transition = 'none';
            cinta.innerHTML = '';
            nuevos.forEach(function (simbolo, fila) {
                grillaActual[fila][columna] = simbolo;
                cinta.appendChild(crearCelda(simbolo));
            });
            cinta.style.transform = 'translateY(0)';
            rodillo.classList.remove('anticipando');
            sonidoParada();
            listo();
        }, duracion);
    });
}

function girarTodos(grilla, bolsas) {
    var esperadas = [];
    var bolsasPrevias = 0;
    for (var c = 0; c < 5; c++) {
        var nuevos = [grilla[0][c], grilla[1][c], grilla[2][c]];
        var duracion = 900 + c * 320;
        if (c == 4 && bolsasPrevias >= 2) {
            duracion += 1500;
            contenedorRodillos.children[4].classList.add('anticipando');
        }
        for (var f = 0; f < 3; f++) {
            if (nuevos[f] == 'bolsa' && c < 4) bolsasPrevias++;
        }
        esperadas.push(girarRodillo(c, nuevos, duracion));
    }
    return Promise.all(esperadas);
}

// resultado en pantalla
function limpiarGanadoras() {
    if (cicloLineas) {
        clearInterval(cicloLineas);
        cicloLineas = null;
    }
    capaLineas.innerHTML = '';
    contenedorRodillos.classList.remove('hay_ganadoras');
    var celdas = contenedorRodillos.querySelectorAll('.celda');
    for (var i = 0; i < celdas.length; i++) {
        celdas[i].classList.remove('gana');
    }
    textoGanancia.textContent = '';
    textoGanancia.classList.remove('sube');
}

function dibujarLinea(linea, grosor, opacidad) {
    var puntos = linea.recorrido.slice(0, linea.cantidad).map(function (fila, c) {
        return (c * 100 + 50) + ',' + (fila * 100 + 50);
    }).join(' ');
    var trazo = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
    trazo.setAttribute('points', puntos);
    trazo.setAttribute('fill', 'none');
    trazo.setAttribute('stroke', COLORES_LINEA[linea.linea - 1]);
    trazo.setAttribute('stroke-width', grosor);
    trazo.setAttribute('stroke-linecap', 'round');
    trazo.setAttribute('stroke-linejoin', 'round');
    trazo.setAttribute('opacity', opacidad);
    trazo.setAttribute('vector-effect', 'non-scaling-stroke');
    capaLineas.appendChild(trazo);
}

function marcarCeldas(datos) {
    contenedorRodillos.classList.add('hay_ganadoras');
    datos.lineas.forEach(function (linea) {
        for (var c = 0; c < linea.cantidad; c++) {
            celdaEn(linea.recorrido[c], c).classList.add('gana');
        }
    });
    datos.bolsas.forEach(function (lugar) {
        if (datos.premio_bolsa > 0) celdaEn(lugar[0], lugar[1]).classList.add('gana');
    });
}

function nombrePlural(simbolo, cantidad) {
    var nombres = { pata: 'patas', hueso: 'huesos', pelota: 'pelotas', plato: 'platos', collar: 'collares', casita: 'casitas', dorado: 'Rayen dorados' };
    return cantidad + ' ' + nombres[simbolo];
}

function ciclarLineas(datos) {
    if (datos.lineas.length < 2) return;
    var turno = 0;
    cicloLineas = setInterval(function () {
        var linea = datos.lineas[turno % datos.lineas.length];
        capaLineas.innerHTML = '';
        dibujarLinea(linea, 5, 1);
        textoMensaje.textContent = 'Línea ' + linea.linea + ': ' + nombrePlural(linea.simbolo, linea.cantidad) + ' = ' + formatoNumero(linea.pago * (datos.gratis ? 2 : 1));
        turno++;
    }, 1300);
}

function temblor(nivel) {
    var clase = { grande: 'temblor_1', mega: 'temblor_2', super: 'temblor_2', jackpot: 'temblor_3' }[nivel];
    if (!clase) return;
    mesa.classList.add(clase);
    setTimeout(function () { mesa.classList.remove(clase); }, nivel == 'jackpot' ? 5000 : 1500);
}

var TITULOS = {
    grande: '¡GRAN PREMIO!',
    mega: '¡MEGA PREMIO!',
    super: '¡SUPER MEGA PREMIO!',
    jackpot: '¡¡JACKPOT RAYEN DORADA!!'
};

async function mostrarCartel(titulo, montoFinal, duracionCuenta, nivel, conImagen) {
    festejoTitulo.textContent = titulo;
    festejoMonto.textContent = '0';
    festejoImagen.hidden = !conImagen;
    festejo.className = 'festejo nivel_' + nivel;
    festejo.hidden = false;
    await contar(0, montoFinal, duracionCuenta, function (v) {
        festejoMonto.textContent = formatoNumero(v);
    });
}

async function festejar(datos, saldoPrevio) {
    var nivel = datos.nivel;
    marcarCeldas(datos);
    datos.lineas.forEach(function (linea) { dibujarLinea(linea, 3, 0.9); });
    var scatter = datos.premio_bolsa > 0;
    textoMensaje.textContent = scatter ? '¡Bolsas de croquetas!' : (datos.lineas.length + (datos.lineas.length == 1 ? ' línea ganadora' : ' líneas ganadoras'));

    sonidoGanar(nivel);
    vibrar(nivel == 'jackpot' ? [200, 100, 200, 100, 400] : (nivel == 'normal' ? 40 : [80, 40, 80]));
    temblor(nivel);

    var duracion = { normal: 1100, grande: 2200, mega: 3200, super: 4200, jackpot: 7000 }[nivel];

    if (nivel == 'normal') {
        lluvia(datos.premio >= datos.apuesta ? 25 : 8);
        textoGanancia.classList.add('sube');
        await contar(0, datos.premio, duracion, function (v) {
            textoGanancia.textContent = '+' + formatoNumero(v);
        });
        textoGanancia.textContent = '+' + formatoNumero(datos.premio);
        actualizarSaldo(datos.saldo);
        saldo = datos.saldo;
    } else {
        var cantidad = { grande: 90, mega: 180, super: 280, jackpot: 520 }[nivel];
        lluvia(cantidad);
        var repeticion = null;
        if (nivel != 'grande') {
            repeticion = setInterval(function () { lluvia(nivel == 'jackpot' ? 90 : 50); }, 700);
        }
        if (nivel == 'jackpot') sonidoJackpot();
        var cuenta = mostrarCartel(TITULOS[nivel], datos.premio, duracion, nivel, nivel == 'jackpot');
        var saldoMostrado = contar(saldoPrevio, datos.saldo, duracion, actualizarSaldo);
        await Promise.all([cuenta, saldoMostrado]);
        saldo = datos.saldo;
        textoGanancia.textContent = '+' + formatoNumero(datos.premio);
        for (var i = 0; i < 3; i++) sonidoMoneda(i * 0.12);

        if (nivel == 'jackpot') {
            botonSeguir.hidden = false;
            await new Promise(function (listo) {
                var cierre = automatico ? setTimeout(fin, 7000) : null;
                botonSeguir.onclick = fin;
                function fin() {
                    clearTimeout(cierre);
                    listo();
                }
            });
            botonSeguir.hidden = true;
        } else {
            await esperar(1500);
        }
        if (repeticion) clearInterval(repeticion);
        festejo.hidden = true;
        festejo.className = 'festejo';
    }
    ciclarLineas(datos);
}

async function cartelSimple(titulo, monto, nivel, esperaMs) {
    festejoTitulo.textContent = titulo;
    festejoMonto.textContent = monto;
    festejoImagen.hidden = true;
    festejo.className = 'festejo nivel_' + nivel;
    festejo.hidden = false;
    await esperar(esperaMs);
    festejo.hidden = true;
    festejo.className = 'festejo';
}

// giros gratis
function mostrarBono(bono, ganado) {
    if (bono && bono.restantes > 0) {
        giroGratis = bono.restantes;
        mesa.classList.add('modo_bono');
        avisoBono.hidden = false;
        avisoBono.textContent = 'GIROS GRATIS x2 · quedan ' + bono.restantes + ' · ganado ' + formatoNumero(ganado || bono.ganado || 0);
        botonGirar.textContent = 'Giro gratis';
    } else {
        giroGratis = 0;
        mesa.classList.remove('modo_bono');
        avisoBono.hidden = true;
        botonGirar.textContent = 'Girar';
    }
    mostrarControles();
}

// apuesta
function apuestaActual() {
    var valor = parseInt(campoApuesta.value, 10);
    if (isNaN(valor)) return 0;
    return valor;
}

function apuestaValida() {
    var valor = apuestaActual();
    return valor >= APUESTA_MINIMA;
}

function pasoDeApuesta(valor) {
    return Math.max(10, Math.pow(10, Math.floor(Math.log10(Math.max(valor, 1)))));
}

function formatoLinea(apuesta) {
    return (Math.round(apuesta / 20 * 100) / 100).toLocaleString('es-AR');
}

function mostrarControles() {
    var apuesta = apuestaActual();
    textoLinea.textContent = formatoLinea(apuesta);
    var bloqueado = rodando || giroGratis > 0;
    campoApuesta.disabled = bloqueado;
    botonMenos.disabled = bloqueado || apuesta <= APUESTA_MINIMA;
    botonMas.disabled = bloqueado;
    botonMaxima.disabled = bloqueado;
}

function fijarApuesta(valor) {
    valor = Math.max(APUESTA_MINIMA, valor);
    campoApuesta.value = valor;
    mostrarControles();
}

function ajustarApuestaAlSaldo() {
    if (saldo >= APUESTA_MINIMA && apuestaActual() > saldo) fijarApuesta(saldo);
}

// servidor
function llamar(accion, datos) {
    var cuerpo = new FormData();
    cuerpo.append('accion', accion);
    for (var clave in datos) cuerpo.append(clave, datos[clave]);
    return fetch('tragaperras_action.php', { method: 'POST', body: cuerpo }).then(function (r) { return r.json(); });
}

// una vuelta completa
async function jugar() {
    rodando = true;
    saltarYa = false;
    botonGirar.classList.add('saltable');
    mostrarControles();
    limpiarGanadoras();
    avisar('');
    textoMensaje.textContent = '';
    contexto();

    var apuesta = apuestaActual();
    var saldoPrevio = saldo;
    var datos;
    try {
        datos = await llamar('girar', { apuesta: apuesta });
    } catch (e) {
        datos = { ok: false, error: 'No se pudo conectar con el servidor' };
    }
    if (!datos.ok) {
        avisar(datos.error);
        automatico = false;
        botonAuto.classList.remove('activo');
        rodando = false;
        mostrarControles();
        return false;
    }

    if (!datos.gratis) {
        saldoPrevio = saldo - datos.apuesta;
        actualizarSaldo(saldoPrevio);
    }
    saldo = datos.saldo;

    sonidoGiro();
    await girarTodos(datos.grilla, datos.bolsas);

    if (datos.premio > 0) {
        await festejar(datos, saldoPrevio);
    } else {
        actualizarSaldo(datos.saldo);
        textoMensaje.textContent = datos.gratis ? 'Sin premio en este giro' : '';
    }

    if (datos.bono_nuevo > 0) {
        mostrarBono(datos.bono, 0);
        sonidoJackpot();
        lluvia(120);
        await cartelSimple('¡GIROS GRATIS!', datos.bono_nuevo + ' giros · premios x2', 'bono', 2600);
    } else if (datos.gratis) {
        mostrarBono(datos.bono, datos.ganado_bono);
    }

    if (datos.bono_terminado) {
        mostrarBono(null);
        var total = datos.ganado_bono;
        if (total > 0) {
            lluvia(total >= datos.apuesta * 30 ? 300 : 100);
            sonidoGanar('mega');
            await mostrarCartel('TOTAL DE LOS GIROS GRATIS', total, 2200, 'bono', false);
            await esperar(1800);
            festejo.hidden = true;
            festejo.className = 'festejo';
        }
    }

    rodando = false;
    botonGirar.classList.remove('saltable');
    ajustarApuestaAlSaldo();
    mostrarControles();
    return true;
}

async function siguiente() {
    var ok = await jugar();
    if (ok && (giroGratis > 0 || automatico)) {
        if (automatico && giroGratis == 0 && apuestaActual() > saldo) {
            automatico = false;
            botonAuto.classList.remove('activo');
            avisar('Se frenó el automático: no alcanza el saldo');
            return;
        }
        setTimeout(siguiente, 450);
    }
}

function alGirar() {
    if (rodando) {
        pedirSalto();
        return;
    }
    if (giroGratis == 0) {
        if (!apuestaValida()) {
            avisar('La apuesta mínima es de ' + formatoNumero(APUESTA_MINIMA));
            return;
        }
        if (apuestaActual() > saldo) {
            avisar('No tenés saldo suficiente para esa apuesta');
            return;
        }
    }
    siguiente();
}

formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();
    alGirar();
});

document.addEventListener('keydown', function (evento) {
    if (evento.code == 'Space' && evento.target.tagName != 'INPUT' && evento.target.tagName != 'BUTTON' && !dialogoPagos.open) {
        evento.preventDefault();
        alGirar();
    }
});

botonMenos.addEventListener('click', function () {
    var apuesta = apuestaActual();
    var paso = pasoDeApuesta(apuesta - 1);
    fijarApuesta(Math.ceil((apuesta - paso) / paso) * paso);
});
botonMas.addEventListener('click', function () {
    var apuesta = apuestaActual();
    var paso = pasoDeApuesta(apuesta);
    fijarApuesta(Math.floor((apuesta + paso) / paso) * paso);
});
botonMaxima.addEventListener('click', function () {
    fijarApuesta(saldo);
});
campoApuesta.addEventListener('input', mostrarControles);
campoApuesta.addEventListener('change', function () {
    if (isNaN(parseInt(campoApuesta.value, 10))) campoApuesta.value = APUESTA_MINIMA;
    fijarApuesta(apuestaActual());
});

botonAuto.addEventListener('click', function () {
    automatico = !automatico;
    botonAuto.classList.toggle('activo', automatico);
    if (automatico && !rodando) alGirar();
});

function mostrarSonido() {
    botonSonido.textContent = sonidoActivo ? 'Sonido: sí' : 'Sonido: no';
    botonSonido.classList.toggle('activo', sonidoActivo);
}
botonSonido.addEventListener('click', function () {
    sonidoActivo = !sonidoActivo;
    try {
        localStorage.setItem('rayen_tragaperras_sonido', sonidoActivo ? 'si' : 'no');
    } catch (e) {}
    mostrarSonido();
    if (sonidoActivo) sonidoMoneda();
});

// tabla de premios
function armarTabla() {
    var orden = ['dorado', 'casita', 'collar', 'plato', 'pelota', 'hueso', 'pata'];
    var apuesta = apuestaActual();
    var html = '<h3>Premios por línea (apuesta actual)</h3><table class="tabla_pagos"><tr><th></th><th>3</th><th>4</th><th>5</th></tr>';
    orden.forEach(function (simbolo) {
        var p = tabla.pagos[simbolo];
        html += '<tr><td><img src="' + imagenDe(simbolo) + '" alt="' + simbolo + '"></td>' +
            '<td>' + formatoNumero(Math.floor(p[0] * apuesta / 20)) + '</td><td>' + formatoNumero(Math.floor(p[1] * apuesta / 20)) + '</td><td>' + formatoNumero(Math.floor(p[2] * apuesta / 20)) + '</td></tr>';
    });
    html += '</table>';
    html += '<p><img class="mini" src="' + imagenDe('rayen') + '" alt="comodín"> <b>Rayen comodín</b>: aparece en los rodillos 2, 3 y 4 y reemplaza a todos menos a la bolsa y al Rayen dorado.</p>';
    var b = tabla.pago_bolsa;
    var g = tabla.giros_bolsa;
    html += '<p><img class="mini" src="' + imagenDe('bolsa') + '" alt="bolsa"> <b>Bolsa de croquetas</b>: en cualquier lugar. 3 pagan ' + b[3] + 'x la apuesta, 4 pagan ' + b[4] + 'x y 5 pagan ' + b[5] + 'x, y dan ' + g[3] + ', ' + g[4] + ' o ' + g[5] + ' giros gratis con todos los premios x' + tabla.multiplicador_bono + '.</p>';
    html += '<p>Hay 20 líneas. Se paga de izquierda a derecha empezando en el primer rodillo. Si una línea tiene varios premios se paga el mayor. El retorno teórico es de 96,4%.</p>';
    contenidoPagos.innerHTML = html;
}

botonPagos.addEventListener('click', function () {
    if (!tabla) return;
    armarTabla();
    dialogoPagos.showModal();
});
document.getElementById('cerrar_pagos').addEventListener('click', function () {
    dialogoPagos.close();
});

// al entrar
armarRodillos();
mostrarSonido();
mostrarControles();
llamar('estado', {}).then(function (datos) {
    if (!datos.ok) {
        avisar(datos.error);
        return;
    }
    tabla = datos;
    saldo = datos.saldo;
    actualizarSaldo(saldo);
    ajustarApuestaAlSaldo();
    mostrarBono(datos.bono, datos.bono.ganado);
});

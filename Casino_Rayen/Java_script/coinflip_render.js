import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

const canvas   = document.getElementById('coin-canvas');
const RESULTADO = canvas.getAttribute('data-resultado');

const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
renderer.setPixelRatio(window.devicePixelRatio);
renderer.outputColorSpace = THREE.SRGBColorSpace;

const scene  = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(45, 1, 0.1, 100);
camera.position.set(0, 0, 4);

// --- CONFIGURACIÓN DE AUDIO ---
const listener = new THREE.AudioListener();
camera.add(listener); // Añadimos el listener a la cámara

const sonidoMoneda = new THREE.Audio(listener);
const audioLoader  = new THREE.AudioLoader();

// Cargamos el archivo de sonido (ajustá la ruta según tus carpetas)
audioLoader.load('../../../imagenes_y_3D/audio/Moneda_audio.mp3', (buffer) => {
    sonidoMoneda.setBuffer(buffer);
    sonidoMoneda.setVolume(0.5); // Volumen del 0 al 1
}, undefined, (err) => console.error("Error cargando el audio:", err));
// ------------------------------
function resize() {
    const w = canvas.clientWidth;
    const h = canvas.clientHeight;
    renderer.setSize(w, h, false);
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
}

scene.add(new THREE.AmbientLight(0xffffff, 1.2));
const dirLight = new THREE.DirectionalLight(0xffffff, 2);
dirLight.position.set(5, 5, 5);
scene.add(dirLight);
const fillLight = new THREE.DirectionalLight(0xffe066, 0.8);
fillLight.position.set(-4, -2, 3);
scene.add(fillLight);

let coin        = null;
let spinning    = false;
let targetRot   = 0;
let currentRot  = 0;
let startTime   = 0;
const DURATION  = 2800; // La animación dura 2.8 segundos

const loader = new GLTFLoader();
loader.load(
    '../../../imagenes_y_3D/3D/moneda_Rayen.glb',
    (gltf) => {
        coin = gltf.scene;
        const box    = new THREE.Box3().setFromObject(coin);
        const center = box.getCenter(new THREE.Vector3());
        coin.position.sub(center);
        const size   = box.getSize(new THREE.Vector3());
        const maxDim = Math.max(size.x, size.y, size.z);
        coin.scale.setScalar(2.5 / maxDim);

        scene.add(coin);
        resize();

        if (RESULTADO) {
            setTimeout(() => iniciarSpin(RESULTADO), 300);
        }
    },
    undefined,
    (err) => console.error('Error cargando modelo:', err)
);

function iniciarSpin(color) {
    if (!coin || spinning) return;
    spinning  = true;
    startTime = performance.now();
    
    // ACTIVACIÓN DEL SONIDO: Si el buffer ya cargó, se reproduce justo al empezar a girar
    if (sonidoMoneda && sonidoMoneda.buffer) {
        if (sonidoMoneda.isPlaying) sonidoMoneda.stop(); // Por seguridad, si estaba sonando lo frena
        sonidoMoneda.play();
    }
    
    targetRot = color === 'verde' ? Math.PI * 10 : Math.PI * 11; 
}

function easeOutCubic(t) {
    return 1 - Math.pow(1 - t, 3);
}

function animate() {
    requestAnimationFrame(animate);

    if (coin) {
        if (spinning) {
            const elapsed = performance.now() - startTime;
            const t       = Math.min(elapsed / DURATION, 1);
            
            currentRot = targetRot * easeOutCubic(t);
            coin.rotation.y = currentRot; 
            coin.rotation.x = 0;
            coin.rotation.z = 0;
            
            if (t >= 1) {
                spinning        = false;
                coin.rotation.y = targetRot;

                // Actualizar saldo después de que termina la animación
                const display = document.getElementById('saldo-display');
                if (display) {
                    display.textContent = display.getAttribute('data-saldo-real');
                }
            }
        } else if (!RESULTADO) {
            coin.rotation.y += 0.005;
            coin.rotation.x = 0;
            coin.rotation.z = 0;
        }
    }

    renderer.render(scene, camera);
}

window.addEventListener('resize', resize);
resize();
animate();
// Script para verificar qué configuración está usando realmente la app
// Inyectar esto en Console de Chrome/Edge

console.log("=== DIAGNÓSTICO DE CONFIGURACIÓN RUNTIME ===");

// 1. Verificar entorno detectado
const hostname = window.location.hostname;
const isProduction = hostname !== 'localhost';
console.log(`Hostname: ${hostname}`);
console.log(`¿Es producción?: ${isProduction}`);

// 2. Buscar la configuración en variables globales de React
// React apps a veces exponen config en window o en el DOM
console.log("Variables globales disponibles:", Object.keys(window));

// 3. Interceptar fetch calls para ver qué URLs se están usando
const originalFetch = window.fetch;
window.fetch = function(...args) {
    console.log("🌐 FETCH INTERCEPTADO:", args[0]);
    return originalFetch.apply(this, args);
};

// 4. Verificar si hay múltiples archivos JS cargados
const scripts = document.querySelectorAll('script[src]');
console.log(`📜 Scripts cargados (${scripts.length}):`);
scripts.forEach((script, i) => {
    console.log(`${i+1}. ${script.src}`);
});

// 5. Verificar elementos img para logos
const images = document.querySelectorAll('img');
console.log(`🖼️ Imágenes en DOM (${images.length}):`);
images.forEach((img, i) => {
    console.log(`${i+1}. ${img.src}`);
});

// 6. Buscar elementos con estilos en línea (colores)
const elementsWithStyle = document.querySelectorAll('[style*="color"], [style*="background"]');
console.log(`🎨 Elementos con estilos inline (${elementsWithStyle.length}):`);
elementsWithStyle.forEach((el, i) => {
    console.log(`${i+1}. ${el.tagName}: ${el.style.cssText}`);
});

console.log("=== FIN DIAGNÓSTICO ===");
console.log("💡 Ahora haz login y observa las URLs que aparecen en FETCH INTERCEPTADO");
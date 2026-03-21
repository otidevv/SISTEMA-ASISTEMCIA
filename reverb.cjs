const { spawn } = require('child_process');
const path = require('path');

/**
 * Lanzador de Reverb para Windows / PM2
 * Este archivo asegura que artisan se ejecute correctamente a través de PHP
 * y que los argumentos no se pierdan en el entorno de Windows.
 */

const artisanPath = path.join(__dirname, 'artisan');

console.log('🚀 Iniciando Servidor de Notificaciones Reverb...');

const child = spawn('php', [artisanPath, 'reverb:start'], { 
    windowsHide: true,
    shell: false, 
    stdio: 'inherit' 
});

child.on('exit', (code) => {
    console.log(`❌ El proceso de Reverb terminó con código: ${code}`);
    process.exit(code);
});

child.on('error', (err) => {
    console.error('💥 Error al intentar iniciar Reverb:', err);
});

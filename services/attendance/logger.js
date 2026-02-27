const moment = require('moment');
const fs = require('fs');
const path = require('path');

// Ruta al log de Laravel para que sea accesible desde la web
const logPath = path.join(__dirname, '../../storage/logs/biometric.log');

// Asegurar que el directorio existe
const logDir = path.dirname(logPath);
if (!fs.existsSync(logDir)) {
    fs.mkdirSync(logDir, { recursive: true });
}

function writeToFile(level, msg) {
    const timestamp = moment().format('YYYY-MM-DD HH:mm:ss');
    const logLine = `[${timestamp}] [${level}] ${msg}\n`;
    try {
        fs.appendFileSync(logPath, logLine);
    } catch (err) {
        console.error('Error escribiendo en log file:', err);
    }
}

const logger = {
    info: (msg) => {
        const timestamp = moment().format('YYYY-MM-DD HH:mm:ss');
        console.log(`[\x1b[32m${timestamp}\x1b[0m] [INFO] ${msg}`);
        writeToFile('INFO', msg);
    },
    error: (msg, err = '') => {
        const timestamp = moment().format('YYYY-MM-DD HH:mm:ss');
        console.error(`[\x1b[31m${timestamp}\x1b[0m] [ERROR] ${msg}`, err);
        writeToFile('ERROR', `${msg} ${err}`);
    },
    warn: (msg) => {
        const timestamp = moment().format('YYYY-MM-DD HH:mm:ss');
        console.warn(`[\x1b[33m${timestamp}\x1b[0m] [WARN] ${msg}`);
        writeToFile('WARN', msg);
    }
};

module.exports = logger;

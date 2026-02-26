const moment = require('moment');

const logger = {
    info: (msg) => {
        const timestamp = moment().format('YYYY-MM-DD HH:mm:ss');
        console.log(`[\x1b[32m${timestamp}\x1b[0m] [INFO] ${msg}`);
    },
    error: (msg, err = '') => {
        const timestamp = moment().format('YYYY-MM-DD HH:mm:ss');
        console.error(`[\x1b[31m${timestamp}\x1b[0m] [ERROR] ${msg}`, err);
    },
    warn: (msg) => {
        const timestamp = moment().format('YYYY-MM-DD HH:mm:ss');
        console.warn(`[\x1b[33m${timestamp}\x1b[0m] [WARN] ${msg}`);
    }
};

module.exports = logger;

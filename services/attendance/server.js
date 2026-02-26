// -------------------------------------------------------------------------------------
// Nombre del Archivo: server.js
// Descripción: Servicio de Asistencia Biométrica y Notificaciones para Laravel.
// Ubicación: services/attendance/server.js
// Versión: 3.0.0 (Integrado en Laravel)
// -------------------------------------------------------------------------------------

/**
 * 🎓 Visión General del Proyecto
 * * Este script de Node.js actúa como el cerebro de un sistema de asistencia. Sus funciones principales son:
 * * 1. 🔄 **Recepción de Datos:** Escucha en un puerto específico para recibir registros de asistencia de dispositivos
 * de control de acceso ZKTeco (dispositivos de huella digital, tarjetas, etc.).
 * * 2. 💾 **Gestión de Base de Datos:** Almacena los registros de asistencia en una base de datos MySQL para su
 * posterior análisis y reporte.
 * * 3. 🔔 **Notificaciones en Tiempo Real:** Envía alertas automáticas y personalizadas a los padres a través de
 * múltiples canales:
 * - WhatsApp (a través de la librería whatsapp-web.js)
 * - Telegram (a través de la librería node-telegram-bot-api)
 * - SMS (a través de un servicio de terceros como sms-gate.app)
 * * 4. 🤖 **Bot de Telegram:** Funciona como un bot de Telegram interactivo, permitiendo a los usuarios (padres)
 * auto-registrarse para recibir notificaciones y consultar su estado en el sistema, e incluye
 * **COMUNICADOS URGENTES**.
 * -------------------------------------------------------------------------------------
 */

// =====================================================================================
// 📦 Sección 1: Importación de Módulos y Librerías
// =====================================================================================
// Importamos las dependencias necesarias para el funcionamiento del servidor.
const express = require('express'); // Framework para crear el servidor web.
const bodyParser = require('body-parser'); // Middleware para parsear los cuerpos de las peticiones HTTP.
const mysql = require('mysql2/promise'); // Cliente MySQL, con soporte para promesas (más moderno y fácil de usar).
const moment = require('moment'); // Librería para manipular y formatear fechas y horas.
const fs = require('fs'); // Módulo de Node.js para trabajar con el sistema de archivos (leer/escribir).
const path = require('path'); // Módulo para manejar rutas de archivos y directorios.

// Cargar variables de entorno desde el archivo .env de Laravel raíz
require('dotenv').config({ path: path.join(__dirname, '../../.env') });

const logger = require('./logger'); // Módulo de logging personalizado para un registro más limpio.
const fetch = require('node-fetch'); // Módulo para hacer peticiones HTTP (necesario para la API de SMS).

// =====================================================================================
// ⚙️ Sección 2: Configuración Global
// =====================================================================================

// 🚀 Variables de entorno para habilitar/deshabilitar servicios (Cambiado a 'let' para control desde el Dashboard)
// 🚀 Variables de entorno para habilitar/deshabilitar servicios
let WHATSAPP_HABILITADO = process.env.WHATSAPP_ENABLED === 'true';
let TELEGRAM_HABILITADO = process.env.TELEGRAM_ENABLED !== 'false'; // Habilitado por defecto
let SMS_HABILITADO = process.env.SMS_ENABLED === 'true';

// 📈 Estadísticas Globales (Para el Dashboard)
let statsSMS = { enviados: 0, fallidos: 0 };
let logsRecientes = []; // Almacén en memoria de los últimos logs para el frontend

// 🚨 Nueva Variable de Control para Anuncios Únicos
// Si esta en 'true', ANULA la notificación de asistencia y la reemplaza por el COMUNICADO DE EXAMEN para estudiantes.
const ACTIVAR_COMUNICADO_EXAMEN_GLOBAL = false; // <-- CAMBIAR A 'false' PARA REANUDAR NOTIFICACIONES DE ASISTENCIA

// 📝 Contenido del Comunicado Global
const EXAMEN_SMS = "[CEPRE UNAMAD] ATENCION: SEGUNDO EXAMEN SABADO 15/11/2025. DNI, Carnet, utiles. Responda OK para confirmar. Coord. Acad.";

const EXAMEN_TELEGRAM = `🚨🚨 **ATENCIÓN: COMUNICADO URGENTE** 🚨🚨
        
🎓 **CEPRE UNAMAD** - SEGUNDO EXAMEN
        
👋 **Estimado(a) Padre/Madre de familia:**
    
Le informamos sobre la fecha del **SEGUNDO EXAMEN** del ciclo.
    
📅 **FECHA OFICIAL DEL EXAMEN:**
🏆 **SÁBADO 15 DE NOVIEMBRE DE 2025** 🏆
    
📢 **DOCUMENTOS Y ÚTILES PERMITIDOS:**
• Documento de identidad original y vigente
• Carnet de postulante
• Útiles básicos: lápiz, borrador y tajador

📝 **NOTA:** Este mensaje reemplaza temporalmente la notificación de asistencia.

✨ **¡ÉXITOS EN SU PREPARACIÓN!**
*CEPRE UNAMAD - Coordinación Académica*`;

// 🔑 Credenciales y tokens
// ATENCIÓN: En producción, estos valores deberían estar en un archivo .env
// 🔑 Credenciales y tokens
const TELEGRAM_BOT_TOKEN = process.env.TELEGRAM_BOT_TOKEN || '7271731673:AAHaLL8SsIdVuYS8LigOkw6PQ26rz7Z6NcE';

// 📱 Configuración para los servicios de SMS-Gate (Soporte Multi-Dispositivo)
// 📱 Configuración para los servicios de SMS-Gate (Soporte Multi-Dispositivo)
const SMS_GATEWAYS = [
    {
        url: process.env.SMS_GATEWAY_URL || 'https://api.sms-gate.app/3rdparty/v1/messages',
        user: process.env.SMS_GATEWAY_USER || 'NYAO6N',
        pass: process.env.SMS_GATEWAY_PASS || '_s4hxf_4zrbghi',
        deviceId: process.env.SMS_GATEWAY_DEVICE_ID || null
    },
    {
        url: process.env.SMS_GATEWAY_2_URL || 'https://api.sms-gate.app/3rdparty/v1/messages',
        user: process.env.SMS_GATEWAY_2_USER || '_PI0DH',
        pass: process.env.SMS_GATEWAY_2_PASS || 'sdaedsw2rrwwwqrr',
        deviceId: process.env.SMS_GATEWAY_2_DEVICE_ID || '7c31LlagPDA3-UuZy_dzH'
    }
];
let gatewaysBusy = new Array(SMS_GATEWAYS.length).fill(false); // Seguimiento de qué celular está ocupado enviado ahora mismo
let nextGatewayToStart = 0; // Para rotar el inicio y balancear la carga entre celulares

// 📁 Ruta del archivo de configuración para mapear teléfonos a Chat IDs de Telegram
const TELEGRAM_CONFIG_FILE = path.join(__dirname, 'telegram_users.json');

// =====================================================================================
// 💾 Sección 3: Gestión de la Configuración de Telegram
// =====================================================================================

/**
 * Carga los Chat IDs de los usuarios de Telegram desde un archivo JSON.
 * Es crucial para el funcionamiento de las notificaciones ya que asocia un número
 * de teléfono con un ID de chat de Telegram.
 * @returns {Object} Un objeto con el formato { "numero_telefono": "chat_id" }.
 */
function cargarConfiguracionTelegram() {
    try {
        if (fs.existsSync(TELEGRAM_CONFIG_FILE)) {
            const data = fs.readFileSync(TELEGRAM_CONFIG_FILE, 'utf8');
            const config = JSON.parse(data);
            logger.info(`Configuración de Telegram cargada: ${Object.keys(config).length} usuarios`);
            return config;
        } else {
            // Si el archivo no existe, lo creamos para evitar errores.
            fs.writeFileSync(TELEGRAM_CONFIG_FILE, '{}');
            logger.info('Archivo de configuración de Telegram creado');
            return {};
        }
    } catch (error) {
        logger.error('Error al cargar configuración de Telegram:', error);
        return {};
    }
}

/**
 * Guarda la configuración de Telegram en el archivo JSON.
 * Se llama cada vez que un usuario nuevo se registra.
 * @param {Object} config El objeto de configuración actualizado.
 */
function guardarConfiguracionTelegram(config) {
    try {
        fs.writeFileSync(TELEGRAM_CONFIG_FILE, JSON.stringify(config, null, 2)); // `null, 2` para un formato JSON legible.
        logger.info('Configuración de Telegram guardada');
    } catch (error) {
        logger.error('Error al guardar configuración de Telegram:', error);
    }
}

// Carga la configuración al iniciar el servidor para tenerla disponible.
let telegramConfig = cargarConfiguracionTelegram();

// =====================================================================================
// 🌐 Sección 4: Configuración del Servidor Express
// =====================================================================================
const app = express();

/**
 * Middleware para capturar el cuerpo de la solicitud en su forma cruda.
 * Esto es necesario porque los dispositivos ZKTeco a veces envían datos
 * con un Content-Type que `bodyParser` no maneja por defecto.
 */
app.use((req, res, next) => {
    // Si la solicitud es para la API del Dashboard, dejamos que bodyParser lo maneje
    if (req.path.startsWith('/api/')) {
        return next();
    }

    // Si la solicitud ya tiene un cuerpo o es un GET, pasamos al siguiente middleware.
    if (req.body !== undefined || req.method === 'GET' || req.readableEnded) {
        return next();
    }
    // Si no, leemos los datos del stream y los guardamos en `req.rawBody`.
    let data = '';
    req.on('data', chunk => {
        data += chunk;
    });
    req.on('end', () => {
        req.rawBody = data;
        next();
    });
    req.on('error', (err) => {
        logger.error('Error al leer el stream de la solicitud:', err);
        next(err);
    });
});

// Middlewares estándar para parsear los cuerpos de las solicitudes
app.use(bodyParser.urlencoded({ extended: true })); // Para formularios URL-encoded.
app.use(bodyParser.json()); // Para peticiones con JSON.

// =====================================================================================
// 🔗 Sección 5: Configuración de la Base de Datos MySQL
// =====================================================================================
const DB_CONFIG = {
    host: process.env.DB_HOST || '127.0.0.1',
    port: process.env.DB_PORT || 3306,
    database: process.env.DB_DATABASE || 'cepre_asistencia',
    user: process.env.DB_USERNAME || 'root',
    password: process.env.DB_PASSWORD || '',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
};

// Crea un pool de conexiones para gestionar la carga y reutilizar conexiones.
const pool = mysql.createPool(DB_CONFIG);

// =====================================================================================
// 🤖 Sección 6: Inicialización de Clientes de Notificación y Bot de Telegram
// =====================================================================================

// Arrays para encolar los mensajes y procesarlos de forma controlada.
// Esto evita saturar las APIs de notificación con demasiadas peticiones.
let mensajesWhatsApp = [];
let procesandoMensajesWhatsApp = false;
let mensajesTelegram = [];
let procesandoMensajesTelegram = false;
let mensajesSMS = [];
let procesandoMensajesSMS = false;
let ultimoEnvioSMS = new Map(); // Para controlar la frecuencia de envío por número

// --- INICIALIZACIÓN DE WHATSAPP ---
if (WHATSAPP_HABILITADO) {
    logger.info('WhatsApp está HABILITADO - Inicializando cliente...');
    const { Client, LocalAuth } = require('whatsapp-web.js');
    const qrcode = require('qrcode-terminal');
    clientWhatsApp = new Client({
        authStrategy: new LocalAuth({ clientId: "sistema-asistencia" }),
        puppeteer: {
            headless: true, // Ejecuta el navegador en segundo plano.
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        }
    });
    // Evento para mostrar el código QR la primera vez que se inicia la sesión.
    clientWhatsApp.on('qr', (qr) => {
        logger.info('QR RECIBIDO, escanea con WhatsApp (solo es necesario la primera vez):');
        qrcode.generate(qr, { small: true });
    });
    // Evento que se dispara cuando el cliente de WhatsApp está listo.
    clientWhatsApp.on('ready', () => {
        logger.info('Cliente WhatsApp listo!');
    });
    // Manejo de errores de autenticación.
    clientWhatsApp.on('auth_failure', (error) => {
        logger.error('Error de autenticación de WhatsApp:', error);
    });
    // Lógica para reconectar si la conexión se pierde.
    clientWhatsApp.on('disconnected', (reason) => {
        logger.warn('Cliente de WhatsApp desconectado:', reason);
        logger.info('Intentando reconectar WhatsApp...');
        setTimeout(() => {
            clientWhatsApp.initialize();
        }, 10000);
    });
    clientWhatsApp.initialize();
} else {
    logger.info('WhatsApp está DESHABILITADO - Las notificaciones de WhatsApp no se enviarán');
}

// --- INICIALIZACIÓN DE TELEGRAM ---
if (TELEGRAM_HABILITADO) {
    logger.info('Telegram está HABILITADO - Inicializando bot...');
    const TelegramBot = require('node-telegram-bot-api');
    botTelegram = new TelegramBot(TELEGRAM_BOT_TOKEN, { polling: true }); // `polling: true` para que el bot escuche los mensajes.

    // Manejador para el comando `/start`
    botTelegram.onText(/\/start/, async (msg) => {
        const chatId = msg.chat.id;
        const username = msg.from.username || msg.from.first_name || 'Usuario';
        logger.info(`Usuario ${username} (Chat ID: ${chatId}) inició conversación con el bot`);

        // Mensaje de bienvenida con Markdown para un formato más atractivo.
        const mensaje = `🎓═══════════════════════════════════🎓
🏫 **CEPRE UNAMAD** - SISTEMA DE ASISTENCIA Y COMUNICADOS 🏫
🎓═══════════════════════════════════🎓

¡Hola **${username}**! 👋

🚀 **REGISTRO AUTOMÁTICO DE NOTIFICACIONES**

📱 **Tu Chat ID:** \`${chatId}\`

🔥 **PARA RECIBIR NOTIFICACIONES:**
┌─────────────────────────────────┐
│  📞 Envía tu número de teléfono  │
│     Ejemplo: 987654321          │
└─────────────────────────────────┘

⚠️ **IMPORTANTE:**
• Debe ser el teléfono registrado en CEPRE UNAMAD
• Debe ser del padre/madre (no del estudiante)
• Solo números, sin espacios ni símbolos

🎯 **QUÉ RECIBIRÁS:**
✅ Notificaciones de entrada puntual
⏰ Alertas de entrada tardía  
🏠 Confirmación de salida normal
📊 Reportes automáticos de asistencia
🚨 **COMUNICADOS URGENTES** (Usa el comando /comunicado)

💫 **COMANDOS DISPONIBLES:**
/comunicado - Ver el comunicado urgente actual (Examen)
/estado - Ver tu estado de registro
/reporte - Generar reporte de asistencia
/info - Información del sistema
/help - Centro de ayuda

🎓═══════════════════════════════════🎓
✨ **¡BIENVENIDO AL FUTURO EDUCATIVO!** ✨
🎓═══════════════════════════════════🎓`;

        // Botones inline para una mejor interacción con el usuario.
        const botones = {
            reply_markup: {
                inline_keyboard: [
                    [
                        { text: '🚨 VER COMUNICADO (Examen)', callback_data: 'ver_comunicado' }
                    ],
                    [
                        { text: '🚀 REGISTRARME AHORA', callback_data: 'tutorial_registro' },
                        { text: '📱 ESTADO', callback_data: 'verificar_estado' }
                    ]
                ]
            },
            parse_mode: 'Markdown'
        };

        try {
            await botTelegram.sendMessage(chatId, mensaje, botones);
        } catch (error) {
            logger.error(`Error al enviar mensaje de bienvenida a ${chatId}:`, error);
        }
    });

    // Manejador para el comando `/comunicado`
    botTelegram.onText(/\/comunicado/, async (msg) => {
        await enviarComunicadoExamen(msg.chat.id, true); // Pasar true para indicar que es solo una previsualización
    });

    // Manejador para cualquier mensaje de texto del usuario.
    botTelegram.on('message', async (msg) => {
        const chatId = msg.chat.id;
        const texto = msg.text;
        if (!texto || texto.startsWith('/')) {
            return; // Ignoramos los comandos y los mensajes vacíos.
        }
        // Validamos si el texto es un número de teléfono.
        const telefonoRegex = /^[\d\s\-\+\(\)]{9,15}$/;
        if (telefonoRegex.test(texto.trim())) {
            await procesarRegistroTelefono(chatId, texto.trim(), msg.from);
        }
    });

    /**
     * Lógica para procesar un registro de teléfono.
     * Busca el número en la base de datos y, si lo encuentra, asocia el Chat ID.
     * @param {number} chatId El ID del chat del usuario.
     * @param {string} telefonoTexto El número de teléfono enviado por el usuario.
     * @param {Object} usuario El objeto de usuario de Telegram.
     */
    async function procesarRegistroTelefono(chatId, telefonoTexto, usuario) {
        let conn;
        try {
            const telefonoLimpio = telefonoTexto.replace(/\D/g, '');
            // Validamos la longitud del número.
            if (telefonoLimpio.length < 9 || telefonoLimpio.length > 11) {
                await botTelegram.sendMessage(chatId,
                    `❌ **NÚMERO INVÁLIDO**\n\n` +
                    `El número ${telefonoTexto} no tiene el formato correcto.\n\n` +
                    `📱 **Formato correcto:**\n` +
                    `• 987654321 (9 dígitos)\n` +
                    `• 51987654321 (con código país)\n\n` +
                    `🔄 **Inténtalo nuevamente**`,
                    { parse_mode: 'Markdown' }
                );
                return;
            }
            conn = await pool.getConnection();
            // Realizamos la búsqueda en la base de datos.
            const [padres] = await conn.execute(
                `SELECT u.nombre, u.apellido_paterno, u.telefono, COUNT(p.estudiante_id) as num_hijos
                 FROM users u
                 JOIN parentescos p ON u.id = p.padre_id
                 WHERE u.telefono LIKE ? OR u.telefono LIKE ? OR u.telefono LIKE ?
                 GROUP BY u.id`,
                [`%${telefonoLimpio}%`, `%${telefonoLimpio.substring(2)}%`, `%51${telefonoLimpio}%`]
            );
            conn.release();
            if (padres.length === 0) {
                // Si no se encuentra el teléfono.
                await botTelegram.sendMessage(chatId,
                    `❌ **TELÉFONO NO ENCONTRADO**\n\n` +
                    `El número **${telefonoTexto}** no está registrado en nuestro sistema.\n\n` +
                    `✅ **VERIFICA QUE:**\n` +
                    `• Sea el teléfono del padre/madre\n` +
                    `• Esté registrado en CEPRE UNAMAD\n` +
                    `• No tenga errores de escritura\n\n` +
                    `💡 **¿Problemas?**\n` +
                    `Contacta al administrador del sistema.\n\n` +
                    `📞 **Central:** 123-456-789\n` +
                    `📧 **Email:** admin@cepre-unamad.edu.pe`,
                    { parse_mode: 'Markdown' }
                );
                return;
            }
            if (padres.length > 1) {
                // Si se encuentran múltiples registros, alertamos al usuario.
                await botTelegram.sendMessage(chatId,
                    `⚠️ **MÚLTIPLES REGISTROS ENCONTRADOS**\n\n` +
                    `Se encontraron varios registros con el teléfono **${telefonoTexto}**.\n\n` +
                    `📞 **Contacta al administrador** para resolver esta situación:\n` +
                    `• Central: 123-456-789\n` +
                    `• Email: admin@cepre-unamad.edu.pe`,
                    { parse_mode: 'Markdown' }
                );
                return;
            }
            // Proceso de registro exitoso.
            const padre = padres[0];
            telegramConfig[padre.telefono] = chatId.toString(); // Asocia el teléfono con el Chat ID.
            guardarConfiguracionTelegram(telegramConfig); // Guarda el cambio en el archivo.
            const username = usuario.username || usuario.first_name || 'Usuario';
            await botTelegram.sendMessage(chatId,
                `🎉═══════════════════════════════════🎉
🎊 **¡REGISTRO EXITOSO!** 🎊
🎉═══════════════════════════════════🎉

👤 **PADRE/MADRE:** ${padre.nombre} ${padre.apellido_paterno}
📱 **TELÉFONO:** ${padre.telefono}
👶 **HIJOS REGISTRADOS:** ${padre.num_hijos}
💬 **CHAT ID:** ${chatId}

🚀 **¡YA ESTÁS CONFIGURADO!**

📲 **RECIBIRÁS NOTIFICACIONES DE:**
┌─────────────────────────────────┐
│ ✅ Entrada puntual              │
│ ⏰ Entrada tardía               │
│ 🏠 Salida normal                │
│ 📋 Registros especiales         │
└─────────────────────────────────┘

🎯 **COMANDOS ÚTILES:**
• /comunicado - Ver el comunicado urgente
• /estado - Ver tu estado actual
• /reporte - Generar reporte de asistencia
• /info - Información del sistema

🎉═══════════════════════════════════🎉
🎓 **¡BIENVENIDO A CEPRE UNAMAD!** 🎓
🎉═══════════════════════════════════🎉`,
                {
                    parse_mode: 'Markdown',
                    reply_markup: {
                        inline_keyboard: [
                            [
                                { text: '🚨 VER COMUNICADO (Examen)', callback_data: 'ver_comunicado' }
                            ],
                            [
                                { text: '📊 VER REPORTE AHORA', callback_data: 'generar_reporte' },
                            ]
                        ]
                    }
                }
            );
            logger.info(`✅ Registro automático exitoso: ${padre.nombre} ${padre.apellido_paterno} (${padre.telefono}) → Chat ID: ${chatId}`);
        } catch (error) {
            logger.error(`Error en registro automático para ${chatId}:`, error);
            await botTelegram.sendMessage(chatId,
                `❌ **ERROR EN EL REGISTRO**\n\n` +
                `Ocurrió un problema técnico. Por favor:\n\n` +
                `🔄 Inténtalo nuevamente en unos minutos\n` +
                `📞 O contacta al administrador si persiste\n\n` +
                `**Central:** 123-456-789`,
                { parse_mode: 'Markdown' }
            );
            return; // Añadido return para respetar la lógica de flujo
        } finally {
            if (conn) conn.release(); // Liberamos la conexión al pool.
        }
    }

    // Manejador para los callbacks de los botones inline.
    botTelegram.on('callback_query', async (callbackQuery) => {
        const chatId = callbackQuery.message.chat.id;
        const data = callbackQuery.data;
        try {
            await botTelegram.answerCallbackQuery(callbackQuery.id); // Notifica a Telegram que el callback fue recibido.
            switch (data) {
                case 'ver_comunicado': // NUEVO: Callback para el comunicado
                    await enviarComunicadoExamen(chatId, true);
                    break;
                case 'tutorial_registro':
                    await botTelegram.sendMessage(chatId,
                        `🚀 **TUTORIAL DE REGISTRO**\n\n` +
                        `**PASO 1:** Prepara tu número de teléfono\n` +
                        `**PASO 2:** Envíalo como mensaje (solo números)\n` +
                        `**PASO 3:** ¡El bot te registra automáticamente!\n\n` +
                        `📱 **Ejemplo:** 987654321\n\n` +
                        `⚠️ Debe ser el teléfono registrado en CEPRE UNAMAD`,
                        { parse_mode: 'Markdown' }
                    );
                    break;
                case 'ver_ejemplo':
                    await mostrarEjemploNotificacion(chatId);
                    break;
                case 'verificar_estado':
                    await verificarEstadoRegistro(chatId);
                    break;
                case 'ayuda_completa':
                    await mostrarAyudaCompleta(chatId);
                    break;
                default:
                    await botTelegram.sendMessage(chatId, '⚠️ Función en desarrollo. Estamos trabajando en ello.');
            }
        } catch (error) {
            logger.error('Error en callback query:', error);
        }
    });

    /**
     * Envía el comunicado urgente sobre la fecha del examen. 
     * Se usa como PREVISUALIZACIÓN si se llama desde el bot, o como LÓGICA DE ENVÍO si se llama desde la asistencia.
     * @param {number} chatId El ID del chat.
     * @param {boolean} isPreview Indica si es solo una previsualización.
     */
    async function enviarComunicadoExamen(chatId, isPreview = false) {

        // --- Contenido del Mensaje de Telegram (Versión detallada) ---
        const comunicadoTelegram = EXAMEN_TELEGRAM;

        if (isPreview) {
            await botTelegram.sendMessage(chatId, comunicadoTelegram, { parse_mode: 'Markdown' });

            // Simulación de envío SMS para confirmación del administrador
            await botTelegram.sendMessage(chatId,
                `📣 **ESTE SERÍA EL MENSAJE ENVIADO POR SMS (Máx. 160 caracteres):**\n\n` +
                `\`\`\`\n${EXAMEN_SMS}\n\`\`\`\n\n` +
                `*NOTA: La notificación a los padres se activa automáticamente cuando un estudiante pica su asistencia, SIEMPRE Y CUANDO la variable \`ACTIVAR_COMUNICADO_EXAMEN_GLOBAL\` esté en \`true\` en la Sección 2 del código.*`,
                { parse_mode: 'Markdown' }
            );
        } else {
            // Lógica de envío real, que se ejecutará en procesarAsistencia.
            // Para fines de esta función auxiliar, simplemente devolveremos el contenido.
            return {
                telegram: comunicadoTelegram,
                sms: EXAMEN_SMS
            };
        }
    }

    /**
     * Envía un mensaje de ejemplo a un chat específico.
     * @param {number} chatId El ID del chat.
     */
    async function mostrarEjemploNotificacion(chatId) {
        const mensajeEjemplo = `📢═══════════════════════════════════📢
🏫 **CEPRE UNAMAD** - NOTIFICACIÓN DE ASISTENCIA
📢═══════════════════════════════════📢

👋 **Estimado(a) María García:**

✅ **Su hijo(a) Juan García López ingresó puntualmente** el día **13/07/2025**

🏫 **Aula:** General
🌅 **Turno:** MAÑANA
🕒 **Hora de ingreso:** 07:15:30
📌 **Situación:** ENTRADA NORMAL

📢═══════════════════════════════════📢
✨ **ESTE ES UN EJEMPLO DE NOTIFICACIÓN** ✨
🎓 *Centro de Estudios Pre Universitarios UNAMAD*
📢═══════════════════════════════════📢`;

        await botTelegram.sendMessage(chatId, mensajeEjemplo, { parse_mode: 'Markdown' });
    }

    /**
     * Verifica el estado de registro de un usuario de Telegram.
     * @param {number} chatId El ID del chat.
     */
    async function verificarEstadoRegistro(chatId) {
        // Buscamos si el Chat ID existe en nuestra configuración.
        const telefonoRegistrado = Object.keys(telegramConfig).find(tel => telegramConfig[tel] === chatId.toString());
        if (telefonoRegistrado) {
            let conn;
            try {
                conn = await pool.getConnection();
                // Si lo encontramos, buscamos los datos del padre en la BD.
                const [padres] = await conn.execute(
                    `SELECT u.*, COUNT(p.estudiante_id) as num_hijos
                     FROM users u
                     JOIN parentescos p ON u.id = p.padre_id
                     WHERE u.telefono LIKE ?
                     GROUP BY u.id`,
                    [`%${telefonoRegistrado}%`]
                );
                conn.release();
                if (padres.length > 0) {
                    const padre = padres[0];
                    await botTelegram.sendMessage(chatId,
                        `✅ **¡ESTÁS REGISTRADO CORRECTAMENTE!**\n\n` +
                        `👤 **Nombre:** ${padre.nombre} ${padre.apellido_paterno}\n` +
                        `📱 **Teléfono:** ${padre.telefono}\n` +
                        `👶 **Hijos registrados:** ${padre.num_hijos}\n` +
                        `💬 **Chat ID:** ${chatId}\n\n` +
                        `🎉 **Estado:** ACTIVO ✅\n\n` +
                        `📲 **Recibirás notificaciones de:**\n` +
                        `• Entrada puntual ✅\n` +
                        `• Entrada tardía ⏰\n` +
                        `• Salida normal 🏠\n\n` +
                        `🎓 **Sistema funcionando correctamente**`,
                        { parse_mode: 'Markdown' }
                    );
                }
            } catch (error) {
                logger.error('Error al verificar estado:', error);
            } finally {
                if (conn) conn.release();
            }
        } else {
            await botTelegram.sendMessage(chatId,
                `❌ **NO ESTÁS REGISTRADO**\n\n` +
                `Para recibir notificaciones:\n\n` +
                `📱 **Envía tu número de teléfono**\n` +
                `Ejemplo: 987654321\n\n` +
                `⚠️ Debe estar registrado en CEPRE UNAMAD`,
                { parse_mode: 'Markdown' }
            );
        }
    }

    /**
     * Envía un mensaje de ayuda completo a un chat.
     * @param {number} chatId El ID del chat.
     */
    async function mostrarAyudaCompleta(chatId) {
        const mensajeAyuda = `🆘 **CENTRO DE AYUDA COMPLETO** 🆘

📋 **PROBLEMAS COMUNES:**

❓ **Mi teléfono no se registra**
• Verifica que esté registrado en CEPRE UNAMAD
• Debe ser el teléfono del padre/madre
• Envía solo números (987654321)

❓ **No recibo notificaciones**
• Usa /estado para verificar tu registro
• Asegúrate de no haber bloqueado al bot
• Contacta al administrador

❓ **Cómo generar reportes**
• Usa el comando /reporte
• Debes estar registrado primero

📞 **CONTACTO DIRECTO:**
• Central: 123-456-789
• Email: soporte@cepre-unamad.edu.pe

🕒 **HORARIO DE ATENCIÓN:**
• Lunes a Viernes: 8:00 AM - 6:00 PM

💡 **COMANDOS ÚTILES:**
/start - Iniciar registro
/comunicado - Ver el comunicado urgente (Examen 15/11/2025)
/estado - Ver estado actual
/reporte - Generar reporte
/info - Información del sistema
/help - Esta ayuda`;

        await botTelegram.sendMessage(chatId, mensajeAyuda, { parse_mode: 'Markdown' });
    }

    // Manejadores para los comandos de texto directos.
    botTelegram.onText(/\/estado/, async (msg) => {
        await verificarEstadoRegistro(msg.chat.id);
    });
    botTelegram.onText(/\/info/, async (msg) => {
        const chatId = msg.chat.id;
        const telefonoRegistrado = Object.keys(telegramConfig).find(tel => telegramConfig[tel] === chatId.toString());
        const estadoRegistro = telefonoRegistrado ? '✅ REGISTRADO' : '❌ NO REGISTRADO';
        const mensaje = `ℹ️ **INFORMACIÓN DEL SISTEMA** ℹ️

🏫 **CEPRE UNAMAD** - Sistema Automático

📱 **Tu Chat ID:** \`${chatId}\`
📊 **Estado:** ${estadoRegistro}
${telefonoRegistrado ? `📞 **Teléfono:** ${telefonoRegistrado}` : ''}

🤖 **CARACTERÍSTICAS:**
• Registro 100% automático
• Notificaciones en tiempo real
• **Comunicados Urgentes** (Usa /comunicado)
• Reportes personalizados
• Sin intervención manual

📊 **ESTADÍSTICAS:**
• Usuarios registrados: ${Object.keys(telegramConfig).length}
• Sistema: Completamente automático

${!telefonoRegistrado ? '\n💡 **Para registrarte:** Envía tu teléfono' : '\n🎉 **¡Sistema activo y funcionando!**'}

🎓 **CEPRE UNAMAD** - Innovación Educativa`;
        await botTelegram.sendMessage(chatId, mensaje, { parse_mode: 'Markdown' });
    });
    botTelegram.onText(/\/help/, async (msg) => {
        await mostrarAyudaCompleta(msg.chat.id);
    });
    // Manejadores de errores del bot de Telegram.
    botTelegram.on('error', (error) => {
        logger.error('Error en el bot de Telegram:', error);
    });
    botTelegram.on('polling_error', (error) => {
        logger.error('Error de polling en Telegram:', error);
    });
    logger.info('Bot de Telegram inicializado correctamente');
} else {
    logger.info('Telegram está DESHABILITADO - Las notificaciones de Telegram no se enviarán');
}

// =====================================================================================
// 📬 Sección 7: Gestión de Colas de Mensajes y Notificaciones
// =====================================================================================

/**
 * Busca el Chat ID de Telegram asociado a un número de teléfono.
 * Considera múltiples formatos de número para mayor flexibilidad.
 * @param {string} telefono El número de teléfono a buscar.
 * @returns {string|null} El Chat ID si se encuentra, de lo contrario `null`.
 */
function buscarChatIdPorTelefono(telefono) {
    const telefonoLimpio = telefono.replace(/\D/g, '');
    const posiblesFormatos = [
        telefonoLimpio,
        telefonoLimpio.substring(2),
        '51' + telefonoLimpio,
    ];
    for (const formato of posiblesFormatos) {
        if (telegramConfig[formato]) {
            return telegramConfig[formato];
        }
    }
    return null;
}

/**
 * Procesa la cola de mensajes de WhatsApp.
 * Usa un bucle `while` para enviar mensajes de uno en uno con un retardo,
 * lo que ayuda a evitar bloqueos por parte de WhatsApp.
 */
async function procesarColaMensajesWhatsApp() {
    if (!WHATSAPP_HABILITADO || !clientWhatsApp || procesandoMensajesWhatsApp || mensajesWhatsApp.length === 0) {
        return;
    }
    procesandoMensajesWhatsApp = true;
    while (mensajesWhatsApp.length > 0) {
        const mensaje = mensajesWhatsApp.shift();
        try {
            await clientWhatsApp.sendMessage(mensaje.numeroFormateado, mensaje.texto);
            logger.info(`Notificación WhatsApp enviada correctamente a ${mensaje.numero}`);
        } catch (error) {
            logger.error(`Error al enviar WhatsApp al número ${mensaje.numero}:`, error);
        }
        if (mensajesWhatsApp.length > 0) {
            // Retardo aleatorio para simular comportamiento humano y evitar ser detectado como bot.
            const delay = Math.floor(Math.random() * 5000) + 10000;
            logger.info(`Esperando ${delay / 1000} segundos antes del siguiente mensaje WhatsApp...`);
            await new Promise(resolve => setTimeout(resolve, delay));
        }
    }
    procesandoMensajesWhatsApp = false;
}

/**
 * Procesa la cola de mensajes de Telegram.
 * Similar a la cola de WhatsApp, usa un retardo para evitar exceder los límites
 * de la API de Telegram.
 */
async function procesarColaMensajesTelegram() {
    if (!TELEGRAM_HABILITADO || !botTelegram || procesandoMensajesTelegram || mensajesTelegram.length === 0) {
        return;
    }
    procesandoMensajesTelegram = true;
    while (mensajesTelegram.length > 0) {
        const mensaje = mensajesTelegram.shift();
        try {
            await botTelegram.sendMessage(mensaje.chatId, mensaje.texto, { parse_mode: 'Markdown' });
            logger.info(`Notificación Telegram enviada correctamente a Chat ID: ${mensaje.chatId}`);
        } catch (error) {
            logger.error(`Error al enviar Telegram al Chat ID ${mensaje.chatId}:`, error);
        }
        if (mensajesTelegram.length > 0) {
            // Retardo aleatorio para evitar límites de la API de Telegram.
            const delay = Math.floor(Math.random() * 2000) + 3000;
            logger.info(`Esperando ${delay / 1000} segundos antes del siguiente mensaje Telegram...`);
            await new Promise(resolve => setTimeout(resolve, delay));
        }
    }
    procesandoMensajesTelegram = false;
}

/**
 * Agrega un mensaje de WhatsApp a la cola de procesamiento.
 * @param {string} numero El número del destinatario.
 * @param {string} numeroFormateado El número formateado con el dominio de WhatsApp.
 * @param {string} texto El contenido del mensaje.
 */
function agregarMensajeWhatsAppACola(numero, numeroFormateado, texto) {
    if (!WHATSAPP_HABILITADO) {
        logger.info(`WhatsApp deshabilitado - No se agregará mensaje para ${numero}`);
        return;
    }
    mensajesWhatsApp.push({ numero, numeroFormateado, texto });
    procesarColaMensajesWhatsApp();
}

/**
 * Agrega un mensaje de Telegram a la cola de procesamiento.
 * @param {string} chatId El ID del chat del destinatario.
 * @param {string} texto El contenido del mensaje.
 */
function agregarMensajeTelegramACola(chatId, texto) {
    if (!TELEGRAM_HABILITADO) {
        logger.info(`Telegram deshabilitado - No se agregará mensaje para Chat ID ${chatId}`);
        return;
    }
    mensajesTelegram.push({ chatId, texto });
    procesarColaMensajesTelegram();
}

/**
 * Procesa la cola de mensajes SMS de forma paralela.
 * Lanza un "trabajador" por cada celular disponible que esté libre.
 */
async function procesarColaMensajesSMS() {
    logger.info(`[DEBUG] Intentando procesar cola. SMS_HABILITADO: ${SMS_HABILITADO}, Pendientes: ${mensajesSMS.length}`);
    if (!SMS_HABILITADO || mensajesSMS.length === 0) {
        return;
    }

    // Rotamos el turno de inicio para que el trabajo se reparta de forma equitativa (50/50)
    for (let count = 0; count < SMS_GATEWAYS.length; count++) {
        let i = (nextGatewayToStart + count) % SMS_GATEWAYS.length;

        logger.info(`[DEBUG] Revisando Celular ${i + 1}: gatewaysBusy=${gatewaysBusy[i]}`);
        if (!gatewaysBusy[i] && mensajesSMS.length > 0) {
            gatewaysBusy[i] = true;
            nextGatewayToStart = (i + 1) % SMS_GATEWAYS.length; // La próxima vez empezamos por el que sigue
            lanzarTrabajadorSMS(i);
        }
    }
}

/**
 * Lógica de un celular individual (Trabajador).
 * Envía mensajes hasta que la cola esté vacía y luego se detiene.
 */
async function lanzarTrabajadorSMS(index) {
    const gateway = SMS_GATEWAYS[index];
    logger.info(`🚀 [PARALELO] Celular ${index + 1} (${gateway.user}) activado para procesar mensajes.`);

    while (mensajesSMS.length > 0 && SMS_HABILITADO) {
        const mensaje = mensajesSMS.shift();
        if (!mensaje) break;

        // Inicializar contador de intentos si no existe
        if (!mensaje.intentos) mensaje.intentos = 0;

        try {
            const enviado = await ejecutarEnvioSMS(mensaje.numero, mensaje.texto, gateway);

            if (!enviado) {
                mensaje.intentos++;
                if (mensaje.intentos < 3) {
                    logger.warn(`[REINTENTO] El mensaje a ${mensaje.numero} falló. Reencolando para otro intento (Intento ${mensaje.intentos}/3)`);
                    mensajesSMS.push(mensaje); // Lo devolvemos a la cola
                } else {
                    logger.error(`[FALLO FINAL] El mensaje a ${mensaje.numero} ha fallado tras 3 intentos. Se descarta.`);
                }
            }
        } catch (error) {
            logger.error(`Error crítico en el celular ${index + 1}:`, error);
        }

        // Si aún hay más mensajes, esperamos el tiempo de seguridad
        if (mensajesSMS.length > 0) {
            const delay = Math.floor(Math.random() * 5000) + 8000;
            logger.info(`[Celular ${index + 1}] Esperando ${delay / 1000}s para el siguiente... (Pendientes: ${mensajesSMS.length})`);
            await new Promise(resolve => setTimeout(resolve, delay));
        }
    }

    logger.info(`😴 [PARALELO] Celular ${index + 1} ha terminado sus tareas y vuelve a reposo.`);
    gatewaysBusy[index] = false; // Liberamos el celular para que pueda ser re-activado después
}

/**
 * Realiza el envío físico del SMS a través de la API, usando el gateway especificado.
 * @param {string} numero El número de teléfono.
 * @param {string} texto El contenido.
 * @param {Object} gateway La configuración del gateway a usar via Round Robin.
 */
async function ejecutarEnvioSMS(numero, texto, gateway) {
    logger.info(`[DEBUG] Iniciando ejecución de envío a ${numero} via ${gateway.user}`);
    try {
        const authHeader = 'Basic ' + Buffer.from(gateway.user + ':' + gateway.pass).toString('base64');

        // Formatear el número al formato E.164 (+<código_país><número>)
        let numeroFormateado = numero.replace(/\D/g, '');
        if (numeroFormateado.length === 9) {
            numeroFormateado = '+51' + numeroFormateado;
        } else if (numeroFormateado.length > 9 && !numeroFormateado.startsWith('51')) {
            numeroFormateado = '+51' + numeroFormateado;
        } else if (numeroFormateado.startsWith('51') && !numeroFormateado.startsWith('+')) {
            numeroFormateado = '+' + numeroFormateado;
        }

        const payload = {
            phoneNumbers: [numeroFormateado],
            textMessage: {
                text: texto
            },
            withDeliveryReport: true
        };

        // Si el gateway tiene un Device ID específico, lo agregamos al payload
        if (gateway.deviceId) {
            payload.deviceId = gateway.deviceId;
        }

        const response = await fetch(gateway.url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': authHeader
            },
            body: JSON.stringify(payload),
        });
        const result = await response.json();
        if (response.ok) {
            statsSMS.enviados++;
            logger.info(`SMS enviado correctamente a ${numero} usando ${gateway.user}. Respuesta API: ${JSON.stringify(result)}`);
            return true;
        } else {
            statsSMS.fallidos++;
            logger.error(`Error al enviar SMS a ${numero} con ${gateway.user}. Estado HTTP: ${response.status}, Respuesta API: ${JSON.stringify(result)}`);
            return false;
        }
    } catch (error) {
        statsSMS.fallidos++;
        logger.error(`Error de conexión al enviar SMS a ${numero} con ${gateway.user}:`, error);
        return false;
    }
}

/**
 * Agrega un mensaje SMS a la cola para su envío controlado.
 * @param {string} numero El número del destinatario.
 * @param {string} texto El contenido del mensaje.
 */
async function enviarSMS(numero, texto) {
    if (!SMS_HABILITADO) {
        logger.info(`SMS deshabilitado - No se encolará SMS al número ${numero}`);
        return;
    }

    // Control de frecuencia (Rate Limiting) - Evita spam al mismo número en menos de 5 minutos
    const ahora = Date.now();
    const ultimoEnvio = ultimoEnvioSMS.get(numero);
    if (ultimoEnvio && (ahora - ultimoEnvio) < 300000) { // 300,000 ms = 5 minutos
        const segundosRestantes = Math.ceil((300000 - (ahora - ultimoEnvio)) / 1000);
        logger.info(`Filtro anti-spam: Se omitió SMS para ${numero} (Ya se envió uno hace poco, restan ${segundosRestantes}s de bloqueo)`);
        return;
    }

    logger.info(`Agregando SMS a la cola para: ${numero}`);
    ultimoEnvioSMS.set(numero, ahora); // Actualizar el tiempo del último envío
    mensajesSMS.push({ numero, texto });
    procesarColaMensajesSMS();
}

// =====================================================================================
// ⚙️ Sección 8: Lógica de Procesamiento de Asistencia
// =====================================================================================

/**
 * Función principal para procesar los datos de asistencia.
 * Es el corazón del sistema, se encarga de:
 * 1. Parsear los datos.
 * 2. Guardar el registro en la BD.
 * 3. Buscar al usuario y sus roles.
 * 4. Determinar la situación de la asistencia (entrada, salida, tarde, etc.).
 * 5. Generar los mensajes de notificación.
 * 6. Encolar los mensajes para su envío.
 * @param {string} sn_dispositivo El número de serie del dispositivo ZKTeco.
 * @param {string} datos_asistencia Los datos del registro en formato de texto tabulado.
 * @returns {boolean} `true` si el procesamiento fue exitoso, `false` si hubo un error.
 */
async function procesarAsistencia(sn_dispositivo, datos_asistencia) {
    let conn;
    try {
        // Parseamos la línea de datos tabulados.
        const partes = datos_asistencia.trim().split('\t');
        if (partes.length < 2) {
            logger.error(`Formato de datos inválido: ${datos_asistencia}`);
            return false;
        }

        const nro_documento = partes[0].trim();
        const fecha_hora = partes[1].trim();
        const tipo_verificacion = partes.length > 2 && partes[2].trim() ? parseInt(partes[2]) : 0;
        const estado = partes.length > 3 && partes[3].trim() ? parseInt(partes[3]) : 0;
        const codigo_trabajo = partes.length > 4 ? partes[4].trim() : '';

        conn = await pool.getConnection();

        // 1. Insertar el registro de asistencia en la tabla `registros_asistencia`.
        const [result] = await conn.execute(
            `INSERT INTO registros_asistencia
             (nro_documento, fecha_hora, tipo_verificacion, estado, codigo_trabajo, sn_dispositivo)
             VALUES (?, ?, ?, ?, ?, ?)`,
            [nro_documento, fecha_hora, tipo_verificacion, estado, codigo_trabajo, sn_dispositivo]
        );
        const registroId = result.insertId;
        logger.info(`Registro de asistencia guardado con ID: ${registroId}`);

        // 2. Insertar un evento en la tabla `asistencia_eventos` para el monitoreo en tiempo real.
        // Esta tabla es crucial para sistemas que necesitan un historial de eventos procesados.
        await conn.execute(
            `INSERT INTO asistencia_eventos (registros_asistencia_id, procesado, created_at, updated_at)
             VALUES (?, 0, NOW(), NOW())`,
            [registroId]
        );
        logger.info(`Evento insertado en asistencia_eventos para registro ID: ${registroId}`);

        // Si no hay notificaciones habilitadas, salimos de la función.
        if (!WHATSAPP_HABILITADO && !TELEGRAM_HABILITADO && !SMS_HABILITADO) {
            logger.info('Notificaciones deshabilitadas - No se enviarán mensajes');
            return true;
        }

        // Obtener la fecha y hora exacta del registro desde la BD para mayor precisión.
        const [registrosInsertados] = await conn.execute(
            `SELECT fecha_registro FROM registros_asistencia WHERE id = ?`,
            [registroId]
        );
        if (registrosInsertados.length === 0) {
            logger.error(`No se pudo obtener el registro insertado con ID: ${registroId}`);
            return false;
        }

        const fecha_registro = registrosInsertados[0].fecha_registro;
        logger.info(`Fecha de registro obtenida: ${fecha_registro}`);

        // 3. Buscar al usuario por su número de documento.
        const [usuarios] = await conn.execute(
            `SELECT * FROM users WHERE numero_documento = ? LIMIT 1`,
            [nro_documento]
        );
        if (usuarios.length === 0) {
            logger.warn(`No se encontró usuario con documento: ${nro_documento}`);
            return true;
        }
        const usuario = usuarios[0];
        logger.info(`Usuario encontrado: ${usuario.nombre} ${usuario.apellido_paterno} (ID: ${usuario.id})`);

        // 4. Verificar los roles del usuario.
        const [roles] = await conn.execute(
            `SELECT r.nombre FROM user_roles ur
             JOIN roles r ON ur.rol_id = r.id
             WHERE ur.usuario_id = ?`,
            [usuario.id]
        );
        const esEstudiante = roles.some(rol => rol.nombre === 'estudiante');
        const esProfesor = roles.some(rol => rol.nombre === 'profesor');

        const fechaHoraRegistro = moment(fecha_registro);
        const fechaMostrar = fechaHoraRegistro.format('DD/MM/YYYY');
        const horaMostrar = fechaHoraRegistro.format('HH:mm:ss');

        // ==========================================================
        // >>> INICIO: LÓGICA DE OVERRIDE (COMUNICADO EXAMEN) <<<
        // Esto reemplaza la notificación de asistencia para estudiantes
        // mientras la variable global esté en 'true'.
        // ==========================================================
        if (esEstudiante && ACTIVAR_COMUNICADO_EXAMEN_GLOBAL) {
            logger.warn(`MODO EXAMEN ACTIVO: Anulando notificación de asistencia para estudiante ${usuario.nombre} para enviar comunicado de examen.`);

            const [padresComunicado] = await conn.execute(
                // Seleccionar padres que reciben notificaciones.
                `SELECT u.nombre, u.apellido_paterno, u.telefono FROM parentescos p
                 JOIN users u ON p.padre_id = u.id
                 WHERE p.estudiante_id = ? AND p.recibe_notificaciones = 1 AND p.estado = 1`,
                [usuario.id]
            );

            for (const padre of padresComunicado) {
                const nombrePadre = `${padre.nombre || ''} ${padre.apellido_paterno || ''}`.trim();

                // 1. Envío de Telegram
                if (TELEGRAM_HABILITADO && padre.telefono) {
                    const chatId = buscarChatIdPorTelefono(padre.telefono);
                    if (chatId) {
                        // Se reemplaza el marcador de posición del nombre del padre
                        const mensajeTelegramFinal = EXAMEN_TELEGRAM.replace('Estimado(a) Padre/Madre de familia:', `Estimado(a) Padre/Madre de familia ${nombrePadre}:`);
                        agregarMensajeTelegramACola(chatId, mensajeTelegramFinal);
                        logger.info(`Comunicado EXAMEN Telegram enviado a Chat ID: ${chatId}`);
                    }
                }

                // 2. Envío de SMS (Usando la constante global EXAMEN_SMS)
                if (SMS_HABILITADO && padre.telefono) {
                    await enviarSMS(padre.telefono, EXAMEN_SMS);
                    logger.info(`Comunicado EXAMEN SMS enviado a Teléfono: ${padre.telefono}`);
                }

                // Nota: No enviamos WhatsApp aquí para mantener la simplicidad.
            }

            return true; // Detenemos el flujo de procesamiento de asistencia normal.
        }
        // ==========================================================
        // >>> FIN: LÓGICA DE OVERRIDE (COMUNICADO EXAMEN) <<<
        // ==========================================================


        if (esProfesor) {
            // Lógica para notificar a los profesores.
            // FIX: El arreglo ahora filtra los valores nulos o indefinidos.
            const numerosDestino = new Set([usuario.telefono, '901230144', '993111037'].filter(Boolean));
            const numerosDirector = ['901230144', '993111037'];

            // Mensaje para el docente
            const mensajeTelegramProfesor = `📢═══════════════════════════════════📢
🏫 **CEPRE UNAMAD** - NOTIFICACIÓN DE DOCENTE
📢═══════════════════════════════════📢
👋 **Estimado(a) ${usuario.nombre}:**
✅ **Su ingreso a las instalaciones se ha registrado correctamente.**
🕒 **Hora de registro:** ${horaMostrar}
📌 **IMPORTANTE:** Su asistencia es obligatoria para el desarrollo de su tema. Por favor, ni bien termine su sesión, ingrese el tema desarrollado en el sistema de asistencia.
📢═══════════════════════════════════📢
🎓 *Centro de Estudios Pre Universitarios UNAMAD*
📢═══════════════════════════════════📢`;

            const mensajeSMSProfesor = `[CEPRE UNAMAD] Sr(a) ${usuario.nombre}, su ingreso se ha registrado a las ${horaMostrar}. Por favor, ni bien termine su sesion, ingrese su tema en el sistema de asistencia.`;

            // Mensajes para el director
            const mensajeTelegramDirector = `[CEPRE UNAMAD] Ingreso del profesor(a) ${usuario.nombre} ${usuario.apellido_paterno} a las ${horaMostrar} del ${fechaMostrar}.`;
            const mensajeSMSDirector = `[CEPRE UNAMAD] Ingreso del profesor(a) ${usuario.nombre} ${usuario.apellido_paterno} a las ${horaMostrar} del ${fechaMostrar}.`;


            logger.info(`Usuario es un profesor. Se enviarán notificaciones a los siguientes números: ${Array.from(numerosDestino).join(', ')}`);

            for (const numero of numerosDestino) {
                const esNumeroDirector = numerosDirector.includes(numero);
                const mensajeTelegram = esNumeroDirector ? mensajeTelegramDirector : mensajeTelegramProfesor;
                const mensajeSMS = esNumeroDirector ? mensajeSMSDirector : mensajeSMSProfesor;

                if (TELEGRAM_HABILITADO) {
                    const chatId = buscarChatIdPorTelefono(numero);
                    if (chatId) {
                        agregarMensajeTelegramACola(chatId, mensajeTelegram);
                    }
                }
                if (SMS_HABILITADO) {
                    await enviarSMS(numero, mensajeSMS);
                }
            }
        }

        if (esEstudiante) {
            // Lógica para notificar a los padres de los estudiantes. (Solo se ejecuta si el override está en false)
            const [padres] = await conn.execute(
                `SELECT u.id, u.nombre, u.apellido_paterno, u.apellido_materno, u.telefono, p.estudiante_id
                 FROM parentescos p
                 JOIN users u ON p.padre_id = u.id
                 WHERE p.estudiante_id = ? AND p.recibe_notificaciones = 1 AND p.estado = 1`,
                [usuario.id]
            );
            if (padres.length === 0) {
                logger.warn(`No se encontraron padres para el estudiante ID: ${usuario.id}`);
                return true;
            }

            const minutosTotales = fechaHoraRegistro.hours() * 60 + fechaHoraRegistro.minutes();
            let turno, mensajePlantillaWhatsApp, mensajePlantillaTelegram, mensajePlantillaSMS, situacion;
            const aula = "General";

            const mananaEntradaNormal = { inicio: 6 * 60 + 30, fin: 7 * 60 + 25 };
            const mananaEntradaTarde = { inicio: 7 * 60 + 26, fin: 10 * 60 + 20 };
            const mananaSalidaNormal = { inicio: 10 * 60 + 21, fin: 14 * 60 + 0 };

            const tardeEntradaNormal = { inicio: 14 * 60 + 30, fin: 15 * 60 + 15 };
            const tardeEntradaTarde = { inicio: 15 * 60 + 16, fin: 19 * 60 + 29 };
            const tardeSalidaNormal = { inicio: 19 * 60 + 25, fin: 22 * 60 + 0 };

            if (minutosTotales >= mananaEntradaNormal.inicio && minutosTotales <= mananaSalidaNormal.fin) {
                turno = "MAÑANA";
                if (minutosTotales >= mananaEntradaNormal.inicio && minutosTotales <= mananaEntradaNormal.fin) {
                    situacion = "ENTRADA NORMAL";
                    mensajePlantillaWhatsApp = `📢 Estimado(a) padre/madre de familia *[NOMBRE]*:\n\nLe informamos que su hijo(a) 👦👧 *[NOMBRE_ESTUDIANTE]* ingresó puntualmente ✅ el día  *[FECHA]* al aula 🏫 *[AULA]* en el turno *[TURNO]*.`
                    mensajePlantillaTelegram = `📢═══════════════════════════════════📢
🏫 **CEPRE UNAMAD** - NOTIFICACIÓN DE ASISTENCIA
📢═══════════════════════════════════📢
👋 **Estimado(a) [NOMBRE]:**
✅ **Su hijo(a) [NOMBRE_ESTUDIANTE] ingresó puntualmente** el día **[FECHA]**
🏫 **Aula:** [AULA]
🌅 **Turno:** [TURNO]  
🕒 **Hora de ingreso:** [HORA]
📌 **Situación:** ENTRADA NORMAL
📢═══════════════════════════════════📢
✨ **SISTEMA AUTOMÁTICO DE NOTIFICACIONES** ✨
🎓 *Centro de Estudios Pre Universitarios UNAMAD*
📢═══════════════════════════════════📢`;
                    mensajePlantillaSMS = `[CEPRE UNAMAD] Sr(a) [NOMBRE], su hijo(a) [NOMBRE_ESTUDIANTE] ingreso puntualmente el [FECHA] a las [HORA]. Responda OK para confirmar.`
                }
                else if (minutosTotales >= mananaEntradaTarde.inicio && minutosTotales <= mananaEntradaTarde.fin) {
                    situacion = "ENTRADA TARDE";
                    mensajePlantillaWhatsApp = `📢 Estimado(a) padre/madre de familia *[NOMBRE]*:\n\nLe informamos que su hijo(a) 👦👧 *[NOMBRE_ESTUDIANTE]* ingresó tarde ⏰ el día  *[FECHA]* al aula 🏫 *[AULA]* en el turno *[TURNO]*.`;
                    mensajePlantillaTelegram = `⚠️═══════════════════════════════════⚠️
🏫 **CEPRE UNAMAD** - NOTIFICACIÓN DE ASISTENCIA
⚠️═══════════════════════════════════⚠️
👋 **Estimado(a) [NOMBRE]:**
⏰ **Su hijo(a) [NOMBRE_ESTUDIANTE] ingresó tarde** el día **[FECHA]**
🏫 **Aula:** [AULA]
🌅 **Turno:** [TURNO]  
🕒 **Hora de ingreso:** [HORA]
📌 **Situación:** ENTRADA TARDE
⚠️═══════════════════════════════════⚠️
✨ **SISTEMA AUTOMÁTICO DE NOTIFICACIONES** ✨
🎓 *Centro de Estudios Pre Universitarios UNAMAD*
⚠️═══════════════════════════════════⚠️`;
                    mensajePlantillaSMS = `[CEPRE UNAMAD] Sr(a) [NOMBRE], su hijo(a) [NOMBRE_ESTUDIANTE] ingreso tarde el [FECHA] a las [HORA]. Responda OK para confirmar.`
                }
                else if (minutosTotales >= mananaSalidaNormal.inicio && minutosTotales <= mananaSalidaNormal.fin) {
                    situacion = "SALIDA NORMAL";
                    mensajePlantillaWhatsApp = `📢 Estimado(a) padre/madre de familia *[NOMBRE]*:\n\nLe informamos que su hijo(a) 👦👧 *[NOMBRE_ESTUDIANTE]* finalizó sus clases con normalidad ✅ el día  *[FECHA]* en el aula 🏫 *[AULA]*, turno *[TURNO]*.`;
                    mensajePlantillaTelegram = `🏠═══════════════════════════════════🏠
🏫 **CEPRE UNAMAD** - NOTIFICACIÓN DE ASISTENCIA
🏠═══════════════════════════════════🏠
👋 **Estimado(a) [NOMBRE]:**
🏠 **Su hijo(a) [NOMBRE_ESTUDIANTE] finalizó sus clases** con normalidad el día **[FECHA]**
🏫 **Aula:** [AULA]
🌅 **Turno:** [TURNO]  
🕒 **Hora de salida:** [HORA]
📌 **Situación:** SALIDA NORMAL
🏠═══════════════════════════════════🏠
✨ **SISTEMA AUTOMÁTICO DE NOTIFICACIONES** ✨
🎓 *Centro de Estudios Pre Universitarios UNAMAD*
🏠═══════════════════════════════════🏠`;
                    mensajePlantillaSMS = `[CEPRE UNAMAD] Sr(a) [NOMBRE], su hijo(a) [NOMBRE_ESTUDIANTE] salio del centro educativo el [FECHA] a las [HORA]. Responda OK para confirmar.`
                }
            }
            else if (minutosTotales >= tardeEntradaNormal.inicio && minutosTotales <= tardeSalidaNormal.fin) {
                turno = "TARDE";
                if (minutosTotales >= tardeEntradaNormal.inicio && minutosTotales <= tardeEntradaNormal.fin) {
                    situacion = "ENTRADA NORMAL";
                    mensajePlantillaWhatsApp = `📢 Estimado(a) padre/madre de familia *[NOMBRE]*:\n\nLe informamos que su hijo(a) 👦👧 *[NOMBRE_ESTUDIANTE]* ingresó puntualmente ✅ el día 📅 *[FECHA]* al aula 🏫 *[AULA]* en el turno *[TURNO]*.`;
                    mensajePlantillaTelegram = `📢═══════════════════════════════════📢
🏫 **CEPRE UNAMAD** - NOTIFICACIÓN DE ASISTENCIA
📢═══════════════════════════════════📢
👋 **Estimado(a) [NOMBRE]:**
✅ **Su hijo(a) [NOMBRE_ESTUDIANTE] ingresó puntualmente** el día **[FECHA]**
🏫 **Aula:** [AULA]
🌆 **Turno:** [TURNO]  
🕒 **Hora de ingreso:** [HORA]
📌 **Situación:** ENTRADA NORMAL
📢═══════════════════════════════════📢
✨ **SISTEMA AUTOMÁTICO DE NOTIFICACIONES** ✨
🎓 *Centro de Estudios Pre Universitarios UNAMAD*
📢═══════════════════════════════════📢`;
                    mensajePlantillaSMS = `[CEPRE UNAMAD] Sr(a) [NOMBRE  ], su hijo(a) [NOMBRE_ESTUDIANTE] ingreso puntualmente el [FECHA] a las [HORA]. Responda OK para confirmar.`
                }
                else if (minutosTotales >= tardeEntradaTarde.inicio && minutosTotales <= tardeEntradaTarde.fin) {
                    situacion = "ENTRADA TARDE";
                    mensajePlantillaWhatsApp = `📢 Estimado(a) padre/madre de familia *[NOMBRE]*:\n\nLe informamos que su hijo(a) 👦👧 *[NOMBRE_ESTUDIANTE]* ingresó tarde ⏰ el día 📅 *[FECHA]* al aula 🏫 *[AULA]* en el turno *[TURNO]*.`;
                    mensajePlantillaTelegram = `⚠️═══════════════════════════════════⚠️
🏫 **CEPRE UNAMAD** - NOTIFICACIÓN DE ASISTENCIA
⚠️═══════════════════════════════════⚠️
👋 **Estimado(a) [NOMBRE]:**
⏰ **Su hijo(a) [NOMBRE_ESTUDIANTE] ingresó tarde** el día **[FECHA]**
🏫 **Aula:** [AULA]
🌆 **Turno:** [TURNO]  
🕒 **Hora de ingreso:** [HORA]
📌 **Situación:** ENTRADA TARDE
⚠️═══════════════════════════════════⚠️
✨ **SISTEMA AUTOMÁTICO DE NOTIFICACIONES** ✨
🎓 *Centro de Estudios Pre Universitarios UNAMAD*
⚠️═══════════════════════════════════⚠️`;
                    mensajePlantillaSMS = `[CEPRE UNAMAD] Sr(a) [NOMBRE], su hijo(a) [NOMBRE_ESTUDIANTE] ingreso tarde el [FECHA] a las [HORA]. Responda OK para confirmar.`
                }
                else if (minutosTotales >= tardeSalidaNormal.inicio && minutosTotales <= tardeSalidaNormal.fin) {
                    situacion = "SALIDA NORMAL";
                    mensajePlantillaWhatsApp = `📢 Estimado(a) padre/madre de familia *[NOMBRE]*:\n\nLe informamos que su hijo(a) 👦👧 *[NOMBRE_ESTUDIANTE]* finalizó sus clases con normalidad ✅ el día 📅 *[FECHA]* en el aula 🏫 *[AULA]*, turno *[TURNO]*.`;
                    mensajePlantillaTelegram = `🏠═══════════════════════════════════🏠
🏫 **CEPRE UNAMAD** - NOTIFICACIÓN DE ASISTENCIA
🏠═══════════════════════════════════🏠
👋 **Estimado(a) [NOMBRE]:**
🏠 **Su hijo(a) [NOMBRE_ESTUDIANTE] finalizó sus clases** con normalidad el día **[FECHA]**
🏫 **Aula:** [AULA]
🌆 **Turno:** [TURNO]  
🕒 **Hora de salida:** [HORA]
📌 **Situación:** SALIDA NORMAL
🏠═══════════════════════════════════🏠
✨ **SISTEMA AUTOMÁTICO DE NOTIFICACIONES** ✨
🎓 *Centro de Estudios Pre Universitarios UNAMAD*
🏠═══════════════════════════════════🏠`;
                    mensajePlantillaSMS = `[CEPRE UNAMAD] Sr(a) [NOMBRE], su hijo(a) [NOMBRE_ESTUDIANTE] salio del centro educativo el [FECHA] a las [HORA]. Responda OK para confirmar.`
                }
            }
            else {
                // Caso por defecto para registros fuera de los rangos definidos.
                if (minutosTotales < mananaEntradaNormal.inicio || minutosTotales > tardeSalidaNormal.fin) {
                    turno = minutosTotales < 12 * 60 ? "MAÑANA" : "TARDE";
                } else if (minutosTotales > mananaSalidaNormal.fin && minutosTotales < tardeEntradaNormal.inicio) {
                    turno = "INTERMEDIO";
                }
                situacion = "REGISTRO";
                mensajePlantillaWhatsApp = `📢 Estimado(a) padre/madre de familia *[NOMBRE]*:\n\nLe informamos que su hijo(a) 👦👧 *[NOMBRE_ESTUDIANTE]* ha registrado su *INGRESO* el día 📅 *[FECHA]* en el centro educativo.`;
                mensajePlantillaTelegram = `📋═══════════════════════════════════📋
🏫 **CEPRE UNAMAD** - NOTIFICACIÓN DE ASISTENCIA
📋═══════════════════════════════════📋
👋 **Estimado(a) [NOMBRE]:**
📋 **Su hijo(a) [NOMBRE_ESTUDIANTE] registró su ingreso** el día **[FECHA]**
🕒 **Hora de registro:** [HORA]
📌 **Situación:** REGISTRO GENERAL (TURNO ${turno})
📋═══════════════════════════════════📋
✨ **SISTEMA AUTOMÁTICO DE NOTIFICACIONES** ✨
🎓 *Centro de Estudios Pre Universitarios UNAMAD*
📋═══════════════════════════════════📋`;
                mensajePlantillaSMS = `[CEPRE UNAMAD] Sr(a) [NOMBRE], su hijo(a) [NOMBRE_ESTUDIANTE] registro su ingreso el [FECHA] a las [HORA]. Responda OK para confirmar.`
            }

            // Enviar notificaciones a cada padre.
            for (const padre of padres) {
                // Se construye el nombre completo del padre para mayor claridad en el mensaje.
                const nombrePadre = `${padre.nombre || ''} ${padre.apellido_paterno || ''}`.trim();
                const nombreEstudiante = `${usuario.nombre} ${usuario.apellido_paterno} ${usuario.apellido_materno}`;

                // --- Envío de WhatsApp ---
                if (WHATSAPP_HABILITADO && padre.telefono) {
                    let numeroWhatsApp = padre.telefono.replace(/\D/g, '');
                    if (!numeroWhatsApp.startsWith('51') && numeroWhatsApp.length === 9) {
                        numeroWhatsApp = '51' + numeroWhatsApp;
                    }
                    const mensajeWhatsApp = mensajePlantillaWhatsApp
                        .replace(/\[NOMBRE\]/g, nombrePadre)
                        .replace(/\[NOMBRE_ESTUDIANTE\]/g, nombreEstudiante)
                        .replace(/\[FECHA\]/g, fechaMostrar)
                        .replace(/\[HORA\]/g, horaMostrar)
                        .replace(/\[AULA\]/g, aula)
                        .replace(/\[TURNO\]/g, turno) +
                        `\n\n*Atentamente,*\n🏫 CEPRE UNAMAD 🏫`;
                    try {
                        logger.info(`Agregando notificación WhatsApp a la cola para padre ID: ${padre.id} (${numeroWhatsApp})`);
                        const numeroFormateado = `${numeroWhatsApp}@c.us`;
                        agregarMensajeWhatsAppACola(numeroWhatsApp, numeroFormateado, mensajeWhatsApp);
                    } catch (error) {
                        logger.error(`Error al agregar mensaje WhatsApp a la cola para el número ${numeroWhatsApp}:`, error);
                    }
                }

                // --- Envío de Telegram ---
                if (TELEGRAM_HABILITADO && padre.telefono) {
                    const chatId = buscarChatIdPorTelefono(padre.telefono);
                    if (chatId) {
                        const mensajeTelegram = mensajePlantillaTelegram
                            .replace(/\[NOMBRE\]/g, nombrePadre)
                            .replace(/\[NOMBRE_ESTUDIANTE\]/g, nombreEstudiante)
                            .replace(/\[FECHA\]/g, fechaMostrar)
                            .replace(/\[HORA\]/g, horaMostrar)
                            .replace(/\[AULA\]/g, aula)
                            .replace(/\[TURNO\]/g, turno);
                        try {
                            logger.info(`Agregando notificación Telegram a la cola para padre ID: ${padre.id} (Chat ID: ${chatId})`);
                            agregarMensajeTelegramACola(chatId, mensajeTelegram);
                        } catch (error) {
                            logger.error(`Error al agregar mensaje Telegram a la cola para Chat ID ${chatId}:`, error);
                        }
                    } else {
                        logger.info(`Padre ID: ${padre.id} (teléfono: ${padre.telefono}) no tiene Chat ID de Telegram configurado`);
                    }
                }

                // --- Envío de SMS ---
                if (SMS_HABILITADO && padre.telefono) {
                    const mensajeSMS = mensajePlantillaSMS
                        .replace(/\[NOMBRE\]/g, nombrePadre)
                        .replace(/\[NOMBRE_ESTUDIANTE\]/g, nombreEstudiante)
                        .replace(/\[FECHA\]/g, fechaMostrar)
                        .replace(/\[HORA\]/g, horaMostrar);
                    try {
                        logger.info(`Agregando notificación SMS a la cola para padre ID: ${padre.id} (${padre.telefono})`);
                        await enviarSMS(padre.telefono, mensajeSMS);
                    } catch (error) {
                        logger.error(`Error al enviar mensaje SMS al número ${padre.telefono}:`, error);
                    }
                }
            }
        }
        return true;
    } catch (error) {
        logger.error(`Error al procesar asistencia:`, error);
        return false;
    } finally {
        if (conn) conn.release();
    }
}

// =====================================================================================
// 🖥️ Sección 9: API del Dashboard Administrativo
// =====================================================================================
app.use(express.static(path.join(__dirname, 'public'))); // Servir archivos estáticos con ruta absoluta

// Endpoint para obtener el estado completo del sistema
app.get('/api/status', (req, res) => {
    res.json({
        servicios: {
            whatsapp: WHATSAPP_HABILITADO,
            telegram: TELEGRAM_HABILITADO,
            sms: SMS_HABILITADO
        },
        cola: {
            sms: mensajesSMS.length,
            whatsapp: mensajesWhatsApp.length,
            telegram: mensajesTelegram.length
        },
        gateways: SMS_GATEWAYS.map((g, i) => ({
            id: i + 1,
            user: g.user,
            deviceId: g.deviceId,
            busy: gatewaysBusy[i]
        })),
        stats: statsSMS,
        logs: logsRecientes.slice(-20) // Últimos 20 logs
    });
});

// Endpoint para alternar servicios
app.post('/api/toggle', (req, res) => {
    const { servicio, estado } = req.body;
    logger.info(`Dashboard: Solicitando cambiar ${servicio} a ${estado}`);

    if (servicio === 'sms') SMS_HABILITADO = estado;
    if (servicio === 'telegram') TELEGRAM_HABILITADO = estado;
    if (servicio === 'whatsapp') WHATSAPP_HABILITADO = estado;

    res.json({ success: true, servicio, estado });
});

// Endpoint para envío masivo MANUAL
app.post('/api/send-bulk', async (req, res) => {
    const { numeros, mensaje } = req.body;
    logger.info(`[DEBUG API] Peticion recibida: ${JSON.stringify(numeros)}`);
    if (!numeros || !mensaje) return res.status(400).json({ error: 'Datos incompletos' });

    const listaNumeros = Array.isArray(numeros) ? numeros : numeros.split(/[\n,]/).map(n => n.trim()).filter(n => n);
    logger.info(`Dashboard: Agregando ${listaNumeros.length} mensajes.`);

    for (const numero of listaNumeros) {
        mensajesSMS.push({ numero, texto: mensaje });
    }

    logger.info(`Dashboard: Total en cola ahora: ${mensajesSMS.length}`);
    procesarColaMensajesSMS();
    res.json({ success: true, encolados: listaNumeros.length });
});

// NUEVO: Endpoint para obtener todos los contactos registrados (Padres y Docentes)
app.get('/api/contacts', async (req, res) => {
    let conn;
    try {
        conn = await pool.getConnection();

        // Obtener Padres
        const [padres] = await conn.execute(`
            SELECT DISTINCT u.telefono, u.nombre, u.apellido_paterno
            FROM users u
            JOIN parentescos p ON u.id = p.padre_id
            WHERE u.telefono IS NOT NULL AND u.telefono != ''
        `);

        // Obtener Docentes
        const [docentes] = await conn.execute(`
            SELECT DISTINCT u.telefono, u.nombre, u.apellido_paterno
            FROM users u
            JOIN user_roles ur ON u.id = ur.usuario_id
            JOIN roles r ON ur.rol_id = r.id
            WHERE r.nombre = 'profesor' AND u.telefono IS NOT NULL AND u.telefono != ''
        `);

        res.json({
            padres: padres.map(p => ({ tel: p.telefono, label: `${p.nombre} ${p.apellido_paterno}` })),
            docentes: docentes.map(d => ({ tel: d.telefono, label: `${d.nombre} ${d.apellido_paterno}` }))
        });
    } catch (error) {
        logger.error('Error al obtener contactos:', error);
        res.status(500).json({ error: 'Error interno' });
    } finally {
        if (conn) conn.release();
    }
});

// Helper para interceptar logs para el dashboard (Mejorado para evitar crashes)
const originalInfo = logger.info;
logger.info = function (msg) {
    try {
        const timestamp = moment().format('HH:mm:ss');
        let colorClass = '';

        // Convertir msg a string de forma segura
        const msgStr = typeof msg === 'string' ? msg : JSON.stringify(msg);

        if (msgStr.includes('correctamente')) colorClass = 'style="color: #4ade80;"';
        if (msgStr.includes('Error')) colorClass = 'style="color: #f87171;"';
        if (msgStr.includes('Esperando')) colorClass = 'style="color: #fbbf24;"';

        logsRecientes.push(`<span style="opacity:0.5">[${timestamp}]</span> <span ${colorClass}>${msgStr}</span>`);
        if (logsRecientes.length > 50) logsRecientes.shift();
    } catch (e) {
        // Silenciar errores internos del logger del dashboard
    }
    originalInfo.apply(logger, arguments);
};

// =====================================================================================
// 📡 Sección 10: Rutas del Servidor para Dispositivos ZKTECO
// =====================================================================================

// Definimos las rutas que los dispositivos ZKTeco utilizan para enviar datos.
app.all('/', (req, res) => {
    handleRequest('raiz', req, res);
});
app.all('/attendance', (req, res) => {
    handleRequest('attendance', req, res);
});
app.all('/:path(*)', (req, res) => {
    handleRequest(req.params.path, req, res);
});

/**
 * Manejador principal para todas las solicitudes.
 * Analiza la solicitud, extrae el número de serie y los datos, y llama a `procesarAsistencia`.
 * @param {string} endpoint El endpoint de la solicitud.
 * @param {Object} req El objeto de la solicitud.
 * @param {Object} res El objeto de la respuesta.
 */
async function handleRequest(endpoint, req, res) {
    try {
        const method = req.method;
        const ip = req.ip || req.connection.remoteAddress;
        logger.info(`[${endpoint}] Solicitud ${method} recibida desde: ${ip}`);

        if (req.query && endpoint.includes('iclock/cdata')) {
            logger.info(`[${endpoint}] Parámetros URL: ${JSON.stringify(req.query)}`);
            if (req.query.table === 'ATTLOG') {
                try {
                    let rawData = '';
                    if (req.rawBody) {
                        rawData = req.rawBody;
                    } else if (typeof req.body === 'string') {
                        rawData = req.body;
                    } else if (Buffer.isBuffer(req.body)) {
                        rawData = req.body.toString('utf8');
                    }
                    if (!rawData || !rawData.includes('\t')) {
                        logger.error(`[${endpoint}] Datos recibidos no tienen el formato esperado: ${rawData}`);
                        return res.send("OK");
                    }

                    const sn_dispositivo = req.query.SN || '';
                    logger.info(`Procesando registro de asistencia del dispositivo ${sn_dispositivo}`);
                    const lineas = rawData.trim().split('\n');
                    for (const linea of lineas) {
                        if (linea.trim()) {
                            await procesarAsistencia(sn_dispositivo, linea);
                        }
                    }
                    logger.info("Registros de asistencia procesados correctamente");
                } catch (error) {
                    logger.error(`[${endpoint}] Error al procesar datos:`, error);
                }
            }
        }
        // Lógica de respuesta para los dispositivos ZKTeco.
        if (endpoint.includes('iclock/cdata') || endpoint.includes('iclock')) {
            if (req.url.includes('options=all')) {
                // Respuesta necesaria para que el dispositivo ZKTeco se configure.
                return res.send("GET OPTION FROM:" + (req.query.SN || '') + "\nTransTimes=1\nTransInterval=1\nTransTables=User,Fptemp,AttLog\nOper=SetDeviceInfo\n");
            } else {
                return res.send("OK"); // Respuesta estándar de OK.
            }
        } else {
            // Respuesta para otros endpoints (por ejemplo, para pruebas).
            return res.json({
                "status": "success",
                "message": `Datos recibidos correctamente en endpoint: ${endpoint}`,
                "timestamp": new Date().toISOString()
            });
        }
    } catch (error) {
        logger.error(`[${endpoint}] Error: ${error.message}`);
        return res.send("OK");
    }
}

// =====================================================================================
// 🚀 Sección 10: Inicio y Apagado del Servidor
// =====================================================================================
/**
 * Función para probar la conexión a la base de datos al inicio.
 */
async function testDBConnection() {
    try {
        const conn = await pool.getConnection();
        logger.info("✅ Conexión a MySQL exitosa, servidor listo para recibir datos");
        conn.release();
        return true;
    } catch (error) {
        logger.error(`❌ Error al conectar a MySQL: ${error.message}`);
        logger.error("El servidor se iniciará pero los registros no se guardarán hasta resolver este problema");
        return false;
    }
}

/**
 * Función para verificar la conexión al bot de Telegram.
 */
async function testTelegramBot() {
    if (!TELEGRAM_HABILITADO) {
        return true;
    }
    try {
        const me = await botTelegram.getMe();
        logger.info(`✅ Bot de Telegram configurado correctamente: @${me.username} (${me.first_name})`);
        return true;
    } catch (error) {
        logger.error(`❌ Error al verificar bot de Telegram: ${error.message}`);
        logger.error("Verifica que el token del bot sea correcto");
        return false;
    }
}

const PORT = process.env.PORT || 5000;
app.listen(PORT, async () => {
    logger.info(`🚀 Servidor ZKTeco iniciado en puerto ${PORT}`);
    logger.info(`📱 WhatsApp: ${WHATSAPP_HABILITADO ? 'HABILITADO' : 'DESHABILITADO'}`);
    logger.info(`📱 Telegram: ${TELEGRAM_HABILITADO ? 'HABILITADO' : 'DESHABILITADO'}`);
    logger.info(`💬 SMS: ${SMS_HABILITADO ? 'HABILITADO' : 'DESHABILITADO'}`);

    await testDBConnection();
    await testTelegramBot();

    if (TELEGRAM_HABILITADO) {
        logger.info(`📋 Usuarios de Telegram configurados: ${Object.keys(telegramConfig).length}`);
        if (Object.keys(telegramConfig).length > 0) {
            logger.info(`📱 Teléfonos configurados: ${Object.keys(telegramConfig).join(', ')}`);
        }
    }
    if (ACTIVAR_COMUNICADO_EXAMEN_GLOBAL) {
        logger.warn("🚨 MODO DE COMUNICADO DE EXAMEN GLOBAL ACTIVO. LAS NOTIFICACIONES DE ASISTENCIA DIARIA ESTÁN TEMPORALMENTE DESHABILITADAS PARA ESTUDIANTES.");
    }
    logger.info("🎯 Sistema listo para recibir registros de asistencia");
});

// Manejo del evento de apagado para cerrar los recursos de forma segura.
process.on('SIGINT', async () => {
    logger.info('Cerrando aplicación...');
    try {
        await pool.end();
        logger.info('Conexiones a MySQL cerradas correctamente');

        if (WHATSAPP_HABILITADO && clientWhatsApp) {
            await clientWhatsApp.destroy();
            logger.info('Cliente de WhatsApp cerrado correctamente');
        }

        if (TELEGRAM_HABILITADO && botTelegram) {
            await botTelegram.stopPolling();
            logger.info('Bot de Telegram cerrado correctamente');
        }
    } catch (err) {
        logger.error('Error al cerrar recursos:', err);
    }
    process.exit(0);
});
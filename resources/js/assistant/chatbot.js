/* resources/js/assistant/chatbot.js */

document.addEventListener('DOMContentLoaded', () => {
    // DOM Elements
    const launcher = document.getElementById('chatbot-launcher');
    const chatWindow = document.getElementById('chatbot-window');
    const closeBtn = document.getElementById('chatbot-close');
    const chatbotInput = document.getElementById('chatbot-input');
    const sendBtn = document.getElementById('chatbot-send');
    const messagesContainer = document.getElementById('chatbot-messages');
    const bubblesContainer = document.getElementById('launcher-bubbles');

    // Configuration & Data (Enriched Knowledge Base)
    let data = {
        ciclo: {
            nombre: "Cargando...",
            inscripciones: "...",
            inicio: "...",
            examenes: [
                { n: "1° Examen", f: "..." },
                { n: "2° Examen", f: "..." },
                { n: "3° Examen (Final)", f: "..." }
            ]
        },
        pagos: {
            matricula: "S/ 100.00",
            ensenanza: "S/ 1050.00",
            total: "S/ 1,150.00",
            codigos: "Matrícula (Código 582) | Enseñanza (Código 583)"
        },
        contactos: {
            telefono_1: "993 110 927",
            telefono_2: "981 123 456",
            direccion: "Av. Dos de Mayo N° 960 (1er Piso) - Tambopata",
            redes: "Facebook: @CepreUnamad | Instagram: @cepre_unamad"
        },
        grupos: {
            A: "💻 **Ingenierías**: Sistemas, Forestal, Agroindustrial.",
            B: "🏥 **Salud**: Medicina Veterinaria, Enfermería.",
            C: "⚖️ **Letras/Negocios**: Derecho, Contabilidad, Educación, Turismo."
        },
        vacantes: []
    };

    // INTENTS Definition for Smarter Detection
    const intents = [
        {
            name: 'info_ciclo',
            keywords: ['info', 'ciclo', 'inicio', 'cuándo empieza', 'comienza', 'inscripcion', 'inscribirme'],
            response: (d) => `🎓 **Bienvenido al Ciclo ${d.ciclo.nombre}**
Centro Preuniversitario UNAMAD 🚀

📅 **Cronograma Oficial**
🟢 Inscripciones: **${d.ciclo.inscripciones}**
🚀 Inicio de clases: **${d.ciclo.inicio}**

📍 **Sede Central**
🏢 ${d.contactos.direccion}

✨ *¡Asegura tu ingreso directo con nosotros!*`
        },
        {
            name: 'pagos',
            keywords: ['pago', 'costo', 'precio', 'cuanto', 'cuánto', 'mensualidad', 'pagar', 'monto', 'banco', 'código'],
            response: (d) => `💰 **Información de Inversión**
Ciclo Académico ${d.ciclo.nombre} 💳

🔖 **Matrícula**: **${d.pagos.matricula}** (Cód. 701)
📚 **Enseñanza**: **${d.pagos.ensenanza}** (Cód. 702)

⭐ **Inversión Total**: **${d.pagos.total}**
*(Incluye todos los servicios académicos)*

📍 *Puedes pagar en Caja CEPRE o vía Banco de la Nación.*`
        },
        {
            name: 'requisitos',
            keywords: ['requisito', 'necesito', 'papeles', 'documentos', 'dni', 'foto', 'estudio', 'si', 'sí', 'por favor', 'claro', 'yes', 'ok'],
            response: (d) => `📄 **Requisitos de Inscripción**
Para postular al ciclo ${d.ciclo.nombre}:

✅ Copia de DNI legible.
✅ Certificado de Estudios Original. (O constancia de estar cursando 5to)
✅ 02 Fotos tamaño carnet.
✅ Voucher de pago originales.

🚀 *¡Ven a nuestras oficinas y empieza hoy!*`
        },
        {
            name: 'carreras',
            keywords: ['carrera', 'grupo', 'estudiar', 'escuela', 'profesional', 'opción', 'ofrecen'],
            response: (d) => `🏫 **Nuestras Carreras por Grupos**
Contamos con 3 áreas de formación técnica:

🅰️ **Grupo A**
${d.grupos.A}

🅱️ **Grupo B**
${d.grupos.B}

🅲 **Grupo C**
${d.grupos.C}

🎯 *Elige tu vocación y asegura tu futuro.*`
        },
        {
            name: 'vacantes',
            keywords: ['vacante', 'plaza', 'cupo', 'cuanto hay', 'cuantos hay', 'disponible'],
            response: (d) => {
                let list = d.vacantes.slice(0, 5).map(v => `• **${v.c}**: ${v.v} Plazas`).join('\n');
                return `📊 **Cuadro de Vacantes (${d.ciclo.nombre})**
Ingreso Directo CEPRE UNAMAD:

${list || 'Consulte la web para ver el detalle.'}

📌 *El número de vacantes es oficial y aprobado por consejo.*`;
            }
        },
        {
            name: 'examenes',
            keywords: ['fecha', 'examen', 'cronograma', 'cuando es', 'evaluación', 'evaluacion'],
            response: (d) => `📅 **Calendario de Exámenes ${d.ciclo.nombre}**
Puntaje acumulativo para Ingreso Directo:

📝 **1° Examen**: **${d.ciclo.examenes[0].f}**
📝 **2° Examen**: **${d.ciclo.examenes[1].f}**
🏆 **Examen Final**: **${d.ciclo.examenes[2].f}**

⚠️ *Recuerda llegar 30 min antes con tu carnet y DNI.*`
        },
        {
            name: 'contacto',
            keywords: ['contacto', 'donde', 'llamar', 'redes', 'ubica', 'dirección', 'direccion', 'oficina', 'whatsapp', 'teléfono'],
            response: (d) => `📞 **Canales de Atención**
Estamos para ayudarte 24/7 🦉

📱 **WhatsApp**: **${d.contactos.telefono_1}**
📱 **Informes**: **${d.contactos.telefono_2}**
🏢 **Oficina**: **${d.contactos.direccion}**

📱 **Búscanos en Redes**: 
${d.contactos.redes}`
        },
        {
            name: 'resultados',
            keywords: ['resultado', 'ingresante', 'puntaje', 'lista', 'cachimbo', 'muro'],
            response: (d) => `🏆 **Resultados de Admisión**
¿Quieres saber si ya eres Cachimbo? 🎓

📍 Los resultados oficiales se publican aquí:
🔗 [Clic para ver Resultados](${window.location.origin}/resultados-examenes/public)

🚀 *¡Muchos éxitos a todos los postulantes!*`
        }
    ];

    // Dynamic Data Fetching
    async function fetchAssistantConfig() {
        try {
            console.log("Boni-Bot: Sincronizando con base de datos...");
            const response = await fetch('/api/assistant/config');
            if (response.ok) {
                const apiData = await response.json();
                data.ciclo = apiData.ciclo;
                data.vacantes = apiData.vacantes;
                data.grupos = apiData.grupos;
                console.log("Boni-Bot: Datos actualizados en tiempo real.");
            }
        } catch (error) {
            console.warn("Boni-Bot: Error de conexión, usando datos locales.", error);
        }
    }

    fetchAssistantConfig();

    // Helper: Scroll to bottom
    function scrollToBottom() {
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }

    // Recursive Typewriter for HTML
    async function typeHtml(element, html, speed = 12) {
        if (!element) return;
        element.innerHTML = '';
        const temp = document.createElement('div');
        temp.innerHTML = html;
        const nodes = Array.from(temp.childNodes);

        async function processNode(node, target) {
            if (node.nodeType === Node.TEXT_NODE) {
                const text = node.textContent;
                for (let i = 0; i < text.length; i++) {
                    target.appendChild(document.createTextNode(text.charAt(i)));
                    scrollToBottom();
                    await new Promise(r => setTimeout(r, speed));
                }
            } else if (node.nodeType === Node.ELEMENT_NODE) {
                const tag = document.createElement(node.tagName);
                for (let attr of node.attributes) {
                    tag.setAttribute(attr.name, attr.value);
                }
                target.appendChild(tag);
                for (let child of node.childNodes) {
                    await processNode(child, tag);
                }
            }
        }

        for (let node of nodes) {
            await processNode(node, element);
        }
    }

    // Add message to UI
    function addMessage(text, type = 'bot') {
        return new Promise((resolve) => {
            if (!messagesContainer) return resolve();
            const msgElement = document.createElement('div');
            msgElement.className = `message ${type}`;
            
            if (type === 'bot') {
                const content = document.createElement('div');
                content.className = 'bot-content';
                msgElement.appendChild(content);
                const time = document.createElement('span');
                time.className = 'message-time';
                time.innerText = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                msgElement.appendChild(time);
                messagesContainer.appendChild(msgElement);
                
                // Process dynamic variables {data.path}
                let processedText = text.replace(/\{(.*?)\}/g, (match, tag) => {
                    const keys = tag.split('.');
                    let val = data;
                    for (const key of keys) {
                        if (val && val[key] !== undefined) val = val[key];
                        else return match;
                    }
                    return val;
                });

                // Convert simple markdown-like bold to HTML
                const htmlText = processedText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                                             .replace(/\n/g, '<br>');
                typeHtml(content, htmlText, 8).then(resolve);
            } else {
                msgElement.innerHTML = `
                    <div>${text.replace(/\n/g, '<br>')}</div>
                    <span class="message-time">${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                `;
                messagesContainer.appendChild(msgElement);
                scrollToBottom();
                resolve();
            }
        });
    }

    // New: Send multiple messages with delays (Human-like)
    async function sendBotBurst(messages) {
        for (const msg of messages) {
            // Show typing indicator
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message bot typing-indicator-msg';
            typingDiv.innerHTML = `<div class="typing-dots"><span></span><span></span><span></span></div>`;
            messagesContainer.appendChild(typingDiv);
            scrollToBottom();

            // Random delay between 800ms and 1500ms
            const delay = 600 + Math.random() * 800;
            await new Promise(r => setTimeout(r, delay));
            
            typingDiv.remove();
            await addMessage(msg);
        }
    }

    // Bot Responses (The Knowledge Engine)
    function handleBotResponse(query) {
        const q = query.toLowerCase();
        let bestIntent = null;
        let highestScore = 0;

        // 1. Check for Specific Career Focus (Improved Matching)
        const careerFound = data.vacantes.find(v => {
            const cName = v.c.toLowerCase();
            // Match if query contains career name OR career name contains query words (e.g. "sistemas" in "Ingeniería de Sistemas")
            return q.includes(cName) || cName.includes(q) || cName.split(' ').some(word => word.length > 4 && q.includes(word));
        });
        
        if (careerFound) {
            let careerMsg = [];
            
            if (careerFound.img) {
                careerMsg.push(`<div style="text-align:center; margin-bottom:10px;"><img src="${careerFound.img}" style="max-height:80px; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));"></div>`);
            }
            
            careerMsg.push(`¡Claro! He buscado la información de **${careerFound.c}** para ti. 😊`);
            careerMsg.push(`Para el ciclo **${data.ciclo.nombre}**, contamos con **${careerFound.v} vacantes** de ingreso directo.`);
            
            if (careerFound.desc) {
                const cleanDesc = careerFound.desc.replace(/<[^>]*>?/gm, ''); // Remove any HTML tags from DB
                careerMsg.push(`Breve reseña: *${cleanDesc.substring(0, 180)}...*`);
            }
            
            careerMsg.push(`¿Te gustaría saber los requisitos de inscripción para asegurar tu plaza en **${careerFound.c}**? ✨`);
            
            sendBotBurst(careerMsg);
            return;
        }

        // 2. Smart Intent Detection (Global)
        intents.forEach(intent => {
            let score = 0;
            intent.keywords.forEach(keyword => {
                if (q.includes(keyword)) score++;
            });
            if (score > highestScore) {
                highestScore = score;
                bestIntent = intent;
            }
        });

        if (bestIntent && highestScore > 0) {
            sendBotBurst([bestIntent.response(data)]);
        } else {
            sendBotBurst([
                `¡Hola! 👋 Soy **Boni-Bot**, tu asistente virtual de la UNAMAD.`,
                `Puedo ayudarte con Info del Ciclo, Costos, Vacantes por carrera o requisitos de inscripción.\n\n¿De qué carrera te gustaría recibir información hoy? ✨`
            ]);
        }
    }

    // Event Listeners
    launcher.addEventListener('click', () => {
        chatWindow.classList.remove('hidden');
        document.getElementById('cepre-chatbot').classList.add('chat-open');
        if (messagesContainer.children.length === 0) {
            sendBotBurst([
                "¡Hola! 👋 Soy **Boni-Bot**, tu asistente virtual de la UNAMAD.",
                "¿Deseas información sobre el nuevo ciclo **{ciclo.nombre}** o los requisitos de inscripción?"
            ]);
        }
    });

    closeBtn.addEventListener('click', () => {
        chatWindow.classList.add('hidden');
        document.getElementById('cepre-chatbot').classList.remove('chat-open');
    });

    function sendMessage() {
        const text = chatbotInput.value.trim();
        if (text) {
            addMessage(text, 'user');
            chatbotInput.value = '';
            handleBotResponse(text);
        }
    }

    sendBtn.addEventListener('click', sendMessage);
    chatbotInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });

    // Quick Action Chips
    document.querySelectorAll('.qa-chip').forEach(btn => {
        btn.addEventListener('click', () => {
            const action = btn.dataset.action || btn.innerText;
            addMessage(btn.innerText, 'user');
            handleBotResponse(action);
        });
    });

    // Launcher Sequence
    async function showLauncherSequence(messages) {
        if (!bubblesContainer) return;
        const bubble = document.createElement('div');
        bubble.className = 'launcher-bubble';
        bubblesContainer.appendChild(bubble);

        let currentIndex = 0;
        while (true) {
            if (!chatWindow.classList.contains('hidden')) {
                bubble.classList.remove('show');
                break;
            }
            const text = messages[currentIndex];
            bubble.classList.add('show');
            await typeHtml(bubble, text, 35);
            await new Promise(r => setTimeout(r, 4500));
            if (!chatWindow.classList.contains('hidden')) break;
            
            await new Promise(resolve => {
                let interval = setInterval(() => {
                    let content = bubble.innerText;
                    if (content.length > 0) {
                        bubble.innerText = content.substring(0, content.length - 1);
                    } else {
                        clearInterval(interval);
                        resolve();
                    }
                }, 15);
            });

            currentIndex = (currentIndex + 1) % messages.length;
            await new Promise(r => setTimeout(r, 600));
        }
    }

    setTimeout(() => {
        const sequence = [
            "¡Hola! 👋 Soy Boni-Bot.",
            "Solicita vacantes para el ciclo 2026. 🚀",
            "Pregúntame por costos y requisitos. 💰",
            "¿Sabías que tenemos Ingreso Directo? 🏆"
        ];
        showLauncherSequence(sequence);
    }, 1200);
});

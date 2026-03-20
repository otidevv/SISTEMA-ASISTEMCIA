<!-- resources/views/partials/chatbot.blade.php -->
<div id="cepre-chatbot" class="cepre-chatbot-container">
    <!-- Botón Flotante (Mascota) -->
    <div id="chatbot-launcher" class="chatbot-launcher animate-bounce-subtle">
        <div class="launcher-bubbles-container" id="launcher-bubbles"></div>
        <div class="launcher-avatar">
            <img src="{{ asset('assets/images/asistente/asistente_virtual.svg') }}" alt="CEPRE-Bot Mascot"
                id="chatbot-mascot-img">
        </div>
    </div>

    <!-- Ventana de Chat -->
    <div id="chatbot-window" class="chatbot-window hidden">
        <div class="chatbot-header">
            <div class="header-info">
                <div class="avatar-mini">
                    <img src="{{ asset('assets/images/asistente/asistente_virtual.svg') }}" alt="Bot">
                </div>
                <div>
                    <h5>Boni-Bot</h5>
                    <span class="status-online">En línea</span>
                </div>
            </div>
            <button id="chatbot-close" class="btn-close-chat">&times;</button>
        </div>

        <div id="chatbot-messages" class="chatbot-messages">
            <!-- Los mensajes se cargarán dinámicamente -->
        </div>

        <div class="chatbot-footer">
            <div class="quick-actions" id="chat-quick-actions">
                <button class="qa-chip" data-action="ciclo">Info Ciclo</button>
                <button class="qa-chip" data-action="requisitos">Requisitos</button>
                <button class="qa-chip" data-action="pagos">Pagos</button>
                <button class="qa-chip" data-action="contacto">Contacto</button>
            </div>
            <div class="input-area">
                <input type="text" id="chatbot-input" placeholder="Pregunta algo..." autocomplete="off">
                <button id="chatbot-send">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>
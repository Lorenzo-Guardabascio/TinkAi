document.addEventListener('DOMContentLoaded', () => {
    const chatContainer = document.getElementById('chat-container');
    const userInput = document.getElementById('user-input');
    const sendBtn = document.getElementById('send-btn');
    
    // Storico della conversazione locale
    let chatHistory = [];
    let isProcessing = false;

    // Carica conversazione salvata
    loadConversation();

    // Inizializza tema
    initTheme();

    // Focus input on load
    userInput.focus();

    // LocalStorage management
    function saveConversation() {
        try {
            localStorage.setItem('tinkai_history', JSON.stringify(chatHistory));
            localStorage.setItem('tinkai_timestamp', new Date().toISOString());
        } catch (e) {
            console.warn('Could not save conversation:', e);
        }
    }

    function loadConversation() {
        try {
            const saved = localStorage.getItem('tinkai_history');
            const timestamp = localStorage.getItem('tinkai_timestamp');
            
            if (saved) {
                // Verifica se la conversazione è recente (meno di 24 ore)
                const savedDate = new Date(timestamp);
                const now = new Date();
                const hoursDiff = (now - savedDate) / (1000 * 60 * 60);
                
                if (hoursDiff < 24) {
                    chatHistory = JSON.parse(saved);
                    // Ripristina messaggi nella UI
                    chatHistory.forEach(msg => {
                        addMessage(msg.text, msg.role === 'user');
                    });
                } else {
                    // Conversazione troppo vecchia, elimina
                    clearConversation();
                }
            }
        } catch (e) {
            console.warn('Could not load conversation:', e);
        }
    }

    function clearConversation() {
        localStorage.removeItem('tinkai_history');
        localStorage.removeItem('tinkai_timestamp');
        chatHistory = [];
    }

    function exportConversation() {
        if (chatHistory.length === 0) {
            showToast('⚠️ Nessuna conversazione da esportare');
            return;
        }

        const timestamp = new Date().toLocaleString('it-IT');
        let text = `TinkAi - Conversazione del ${timestamp}\n`;
        text += `${'='.repeat(60)}\n\n`;
        
        chatHistory.forEach((msg, index) => {
            const speaker = msg.role === 'user' ? 'Tu' : 'TinkAi';
            text += `${speaker}: ${msg.text}\n\n`;
        });

        // Crea e scarica file
        const blob = new Blob([text], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `tinkai-conversazione-${Date.now()}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        
        showToast('📥 Conversazione esportata');
    }

    // Esponi funzioni globalmente per i pulsanti
    window.clearTinkAiChat = () => {
        if (confirm('Vuoi davvero cancellare questa conversazione?')) {
            clearConversation();
            location.reload();
        }
    };
    window.exportTinkAiChat = exportConversation;

    // Theme Management
    function initTheme() {
        const savedTheme = localStorage.getItem('tinkai_theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);
        
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', toggleTheme);
        }
    }

    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('tinkai_theme', newTheme);
        updateThemeIcon(newTheme);
        
        showToast(newTheme === 'dark' ? '🌙 Tema scuro attivato' : '☀️ Tema chiaro attivato');
    }

    function updateThemeIcon(theme) {
        const sunIcon = document.querySelector('.sun-icon');
        const moonIcon = document.querySelector('.moon-icon');
        
        if (sunIcon && moonIcon) {
            if (theme === 'dark') {
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
            } else {
                sunIcon.style.display = 'block';
                moonIcon.style.display = 'none';
            }
        }
    }

    // Toast Notifications
    function showToast(message, duration = 2000) {
        // Rimuovi toast esistenti
        const existingToast = document.querySelector('.toast');
        if (existingToast) existingToast.remove();
        
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        document.body.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);
        
        // Remove after duration
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    // Keyboard Shortcuts
    document.addEventListener('keydown', (e) => {
        // Ctrl/Cmd + K: Clear conversation
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            window.clearTinkAiChat();
        }
        
        // Ctrl/Cmd + E: Export conversation
        if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
            e.preventDefault();
            window.exportTinkAiChat();
        }
        
        // Ctrl/Cmd + D: Toggle dark mode
        if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
            e.preventDefault();
            toggleTheme();
        }
        
        // Escape: Focus input
        if (e.key === 'Escape') {
            userInput.focus();
        }
    });

    function addMessage(text, isUser = false, isError = false) {
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('message');
        messageDiv.classList.add(isUser ? 'user-message' : 'bot-message');
        if (isError) messageDiv.classList.add('error-message');
        
        const textSpan = document.createElement('span');
        textSpan.classList.add('message-text');
        textSpan.textContent = text;
        messageDiv.appendChild(textSpan);
        
        // Aggiungi feedback buttons solo per messaggi bot non di errore
        if (!isUser && !isError) {
            const feedbackDiv = document.createElement('div');
            feedbackDiv.classList.add('feedback-buttons');
            feedbackDiv.innerHTML = `
                <button class="feedback-btn" data-type="helpful" title="Mi ha fatto riflettere" aria-label="Risposta utile">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button class="feedback-btn" data-type="direct" title="Troppo diretta" aria-label="Risposta troppo diretta">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h2.67A2.31 2.31 0 0 1 22 4v7a2.31 2.31 0 0 1-2.33 2H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            `;
            messageDiv.appendChild(feedbackDiv);
            
            // Event listeners per feedback
            feedbackDiv.querySelectorAll('.feedback-btn').forEach(btn => {
                btn.addEventListener('click', () => handleFeedback(btn, messageDiv));
            });
        }
        
        messageDiv.setAttribute('role', 'log');
        messageDiv.setAttribute('aria-live', 'polite');
        chatContainer.appendChild(messageDiv);
        chatContainer.scrollTop = chatContainer.scrollHeight;
        return messageDiv;
    }

    function handleFeedback(button, messageDiv) {
        const type = button.dataset.type;
        const feedbackDiv = messageDiv.querySelector('.feedback-buttons');
        
        // Disabilita tutti i pulsanti dopo il click
        feedbackDiv.querySelectorAll('.feedback-btn').forEach(btn => {
            btn.disabled = true;
            btn.classList.remove('active');
        });
        
        // Attiva il pulsante cliccato
        button.classList.add('active');
        
        // Salva feedback in localStorage per analytics
        saveFeedback(type);
        
        // Mostra messaggio di ringraziamento
        if (type === 'direct') {
            const hint = document.createElement('div');
            hint.classList.add('feedback-hint');
            hint.textContent = 'Grazie! TinkAi imparerà da questo.';
            messageDiv.appendChild(hint);
            setTimeout(() => hint.remove(), 3000);
        }
    }

    function saveFeedback(type) {
        try {
            const feedback = JSON.parse(localStorage.getItem('tinkai_feedback') || '[]');
            feedback.push({
                type: type,
                timestamp: new Date().toISOString()
            });
            // Mantieni solo ultimi 100 feedback
            if (feedback.length > 100) feedback.shift();
            localStorage.setItem('tinkai_feedback', JSON.stringify(feedback));
        } catch (e) {
            console.warn('Could not save feedback:', e);
        }
    }

    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.classList.add('message', 'bot-message', 'typing-indicator');
        typingDiv.id = 'typing-indicator';
        typingDiv.innerHTML = '<span></span><span></span><span></span>';
        typingDiv.setAttribute('aria-label', 'TinkAi sta pensando');
        chatContainer.appendChild(typingDiv);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    function removeTypingIndicator() {
        const typingDiv = document.getElementById('typing-indicator');
        if (typingDiv) typingDiv.remove();
    }

    async function handleSendMessage() {
        const text = userInput.value.trim();
        if (!text || isProcessing) return;

        // Validazione input base
        if (text.length > 2000) {
            addMessage('Il messaggio è troppo lungo. Proviamo a sintetizzare il pensiero?', false, true);
            return;
        }

        isProcessing = true;
        sendBtn.disabled = true;
        userInput.disabled = true;

        // Add user message
        addMessage(text, true);
        userInput.value = '';
        
        // Aggiungi al history temporaneo (verrà confermato dopo la risposta)
        const currentHistory = [...chatHistory];

        // Mostra indicatore typing
        showTypingIndicator();

        try {
            // Determina l'URL dell'API. Se non siamo sulla porta 3000, punta esplicitamente lì.
            const API_PORT = 3000;
            let apiUrl = '/api/chat';
            if (window.location.port !== String(API_PORT)) {
                apiUrl = `${window.location.protocol}//${window.location.hostname}:${API_PORT}/api/chat`;
            }

            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 
                    message: text,
                    history: currentHistory 
                })
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Server Error:', response.status, errorText);
                throw new Error(`Server error: ${response.status}`);
            }

            const data = await response.json();
            removeTypingIndicator();
            addMessage(data.reply, false, false);
            
            // Aggiorna lo storico solo se tutto è andato a buon fine
            chatHistory.push({ role: 'user', text: text });
            chatHistory.push({ role: 'model', text: data.reply });
            saveConversation();
            
            showToast('💬 Risposta ricevuta');

        } catch (error) {
            console.error('Error:', error);
            removeTypingIndicator();
            
            let errorMessage = 'Mi dispiace, sto avendo difficoltà tecniche. ';
            if (!navigator.onLine) {
                errorMessage += 'Sembra che tu sia offline. Controlla la connessione.';
            } else if (error.message.includes('500')) {
                errorMessage += 'Il servizio è temporaneamente non disponibile.';
            } else {
                errorMessage += 'Riprova tra qualche istante.';
            }
            addMessage(errorMessage, false, true);
        } finally {
            isProcessing = false;
            sendBtn.disabled = false;
            userInput.disabled = false;
            userInput.focus();
        }
    }

    sendBtn.addEventListener('click', handleSendMessage);

    userInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            handleSendMessage();
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const chatContainer = document.getElementById('chat-container');
    const userInput = document.getElementById('user-input');
    const sendBtn = document.getElementById('send-btn');
    
    // Storico della conversazione locale
    let chatHistory = [];
    let isProcessing = false;

    // Carica conversazione salvata
    loadConversation();

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
            alert('Nessuna conversazione da esportare.');
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
    }

    // Esponi funzioni globalmente per i pulsanti
    window.clearTinkAiChat = () => {
        if (confirm('Vuoi davvero cancellare questa conversazione?')) {
            clearConversation();
            location.reload();
        }
    };
    window.exportTinkAiChat = exportConversation;

    function addMessage(text, isUser = false, isError = false) {
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('message');
        messageDiv.classList.add(isUser ? 'user-message' : 'bot-message');
        if (isError) messageDiv.classList.add('error-message');
        messageDiv.textContent = text;
        messageDiv.setAttribute('role', 'log');
        messageDiv.setAttribute('aria-live', 'polite');
        chatContainer.appendChild(messageDiv);
        chatContainer.scrollTop = chatContainer.scrollHeight;
        return messageDiv;
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
            addMessage(data.reply);
            
            // Aggiorna lo storico solo se tutto è andato a buon fine
            chatHistory.push({ role: 'user', text: text });
            chatHistory.push({ role: 'model', text: data.reply });
            saveConversation();

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

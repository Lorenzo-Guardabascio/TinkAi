document.addEventListener('DOMContentLoaded', () => {
    const chatContainer = document.getElementById('chat-container');
    const userInput = document.getElementById('user-input');
    const sendBtn = document.getElementById('send-btn');
    
    // Storico della conversazione locale
    let chatHistory = [];

    // Focus input on load
    userInput.focus();

    function addMessage(text, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('message');
        messageDiv.classList.add(isUser ? 'user-message' : 'bot-message');
        messageDiv.textContent = text;
        chatContainer.appendChild(messageDiv);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    async function handleSendMessage() {
        const text = userInput.value.trim();
        if (!text) return;

        // Add user message
        addMessage(text, true);
        userInput.value = '';
        
        // Aggiungi al history temporaneo (verrà confermato dopo la risposta)
        const currentHistory = [...chatHistory];

        // Show loading state (optional, minimal)
        // const loadingDiv = document.createElement('div'); ...

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
            addMessage(data.reply);
            
            // Aggiorna lo storico solo se tutto è andato a buon fine
            chatHistory.push({ role: 'user', text: text });
            chatHistory.push({ role: 'model', text: data.reply });

        } catch (error) {
            console.error('Error:', error);
            addMessage('Mi dispiace, al momento non riesco a connettermi al mio centro di pensiero. Riprova tra poco.');
        }
    }

    sendBtn.addEventListener('click', handleSendMessage);

    userInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            handleSendMessage();
        }
    });
});

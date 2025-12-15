document.addEventListener('DOMContentLoaded', () => {
    const chatContainer = document.getElementById('chat-container');
    const userInput = document.getElementById('user-input');
    const sendBtn = document.getElementById('send-btn');
    
    // Storico della conversazione locale
    let chatHistory = [];
    let isProcessing = false;
    
    // Session tracking per database
    let sessionId = localStorage.getItem('tinkai_session_id');
    if (!sessionId) {
        sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('tinkai_session_id', sessionId);
    }
    let sessionStartTime = Date.now();
    let messageCount = 0;
    let currentVariant = null; // Track A/B test variant

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
        
        // Apri modal per commento
        showQuickFeedbackModal(type, messageDiv);
    }
    
    function showQuickFeedbackModal(type, messageDiv) {
        const existingModal = document.getElementById('quick-feedback-modal');
        if (existingModal) existingModal.remove();
        
        const modal = document.createElement('div');
        modal.id = 'quick-feedback-modal';
        modal.className = 'feedback-modal';
        modal.innerHTML = `
            <div class="modal-overlay" onclick="document.getElementById('quick-feedback-modal').remove(); resetFeedbackButtons();"></div>
            <div class="modal-content">
                <h2>${type === 'helpful' ? '👍 Feedback Positivo' : '👎 Feedback Negativo'}</h2>
                <p>Aiutaci a migliorare! Raccontaci la tua esperienza:</p>
                
                <div class="form-group">
                    <label for="quick-comment">Il tuo commento (opzionale):</label>
                    <textarea id="quick-comment" rows="4" placeholder="Cosa ti è piaciuto o cosa potremmo migliorare?"></textarea>
                </div>
                
                <div class="modal-actions">
                    <button class="btn-primary" onclick="submitQuickFeedback('${type}')">Invia Feedback</button>
                    <button class="btn-secondary" onclick="submitQuickFeedback('${type}', true)">Salta commento</button>
                    <button class="btn-secondary" onclick="document.getElementById('quick-feedback-modal').remove(); resetFeedbackButtons();">Annulla</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }
    
    window.resetFeedbackButtons = function() {
        document.querySelectorAll('.feedback-btn').forEach(btn => {
            btn.disabled = false;
            btn.classList.remove('active');
        });
    };
    
    window.submitQuickFeedback = async function(type, skipComment = false) {
        const comment = skipComment ? '' : document.getElementById('quick-comment')?.value || '';
        
        await saveFeedback(type, comment, currentVariant);
        
        document.getElementById('quick-feedback-modal')?.remove();
        showToast('✅ Grazie per il tuo feedback!', 2000);
    };

    async function saveFeedback(type, comment = '', variant = null) {
        // Quick feedback salvato su localStorage per legacy
        try {
            const feedback = JSON.parse(localStorage.getItem('tinkai_feedback') || '[]');
            feedback.push({
                type: type,
                comment: comment,
                variant: variant ? variant.name : 'default',
                timestamp: new Date().toISOString()
            });
            if (feedback.length > 100) feedback.shift();
            localStorage.setItem('tinkai_feedback', JSON.stringify(feedback));
        } catch (e) {
            console.warn('Could not save feedback to localStorage:', e);
        }
        
        // Salva su database se in modalità WordPress
        if (!window.tinkaiConfig) return;
        
        try {
            // Ottieni ultimo scambio user/AI
            const lastUser = chatHistory.filter(m => m.role === 'user').slice(-1)[0];
            const lastBot = chatHistory.filter(m => m.role === 'model').slice(-1)[0];
            
            const formData = new FormData();
            formData.append('action', 'tinkai_submit_feedback');
            formData.append('nonce', window.tinkaiConfig.nonce);
            formData.append('rating', type === 'helpful' ? 5 : 2);
            formData.append('session_id', sessionId);
            formData.append('message_id', 'msg_' + Date.now());
            
            // Aggiungi variant info
            if (variant) {
                formData.append('variant_id', variant.id);
                formData.append('variant_name', variant.name);
            }
            
            // Mappa il commento nei campi appropriati
            if (comment) {
                if (type === 'helpful') {
                    formData.append('what_worked', comment);
                } else {
                    formData.append('what_failed', comment);
                }
            }
            
            if (lastUser) formData.append('user_question', lastUser.text);
            if (lastBot) formData.append('ai_response', lastBot.text);
            
            await fetch(window.tinkaiConfig.ajaxUrl, {
                method: 'POST',
                body: formData
            });
        } catch (e) {
            console.warn('Could not save feedback to database:', e);
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
            // Usa WordPress AJAX proxy se disponibile, altrimenti backend diretto
            const isWordPress = typeof tinkaiConfig !== 'undefined';
            
            let response;
            if (isWordPress) {
                // WordPress AJAX Proxy
                const formData = new FormData();
                formData.append('action', 'tinkai_proxy');
                formData.append('nonce', tinkaiConfig.nonce);
                formData.append('endpoint', 'chat');
                formData.append('data', JSON.stringify({ 
                    message: text,
                    history: currentHistory 
                }));
                
                response = await fetch(tinkaiConfig.ajaxUrl, {
                    method: 'POST',
                    body: formData
                });
            } else {
                // Backend diretto (standalone mode)
                const API_PORT = 3000;
                let apiUrl = '/api/chat';
                if (window.location.port !== String(API_PORT)) {
                    apiUrl = `${window.location.protocol}//${window.location.hostname}:${API_PORT}/api/chat`;
                }
                
                response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ 
                        message: text,
                        history: currentHistory 
                    })
                });
            }

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Server Error:', response.status, errorText);
                throw new Error(`Server error: ${response.status}`);
            }

            const data = await response.json();
            removeTypingIndicator();
            addMessage(data.reply, false, false);
            
            // Salva variant se presente
            if (data._variant) {
                currentVariant = data._variant;
            }
            
            // Aggiorna lo storico solo se tutto è andato a buon fine
            chatHistory.push({ role: 'user', text: text });
            chatHistory.push({ role: 'model', text: data.reply });
            saveConversation();
            
            // Salva conversazione su database con variant info
            messageCount += 2; // user + bot
            const variantName = data._variant ? data._variant.name : 'default';
            await saveConversationToDatabase(text, data.reply, variantName);
            
            // Track interaction
            await trackInteraction();
            
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

    // ===== TESTING PLATFORM FEATURES =====
    
    // Funzione per salvare conversazione nel database
    async function saveConversationToDatabase(userQuestion, aiResponse, variantName = 'default') {
        if (!window.tinkaiConfig) return; // Solo in modalità WordPress
        
        try {
            const sessionDuration = Math.floor((Date.now() - sessionStartTime) / 1000);
            
            const formData = new FormData();
            formData.append('action', 'tinkai_save_conversation');
            formData.append('nonce', window.tinkaiConfig.nonce);
            formData.append('session_id', sessionId);
            formData.append('message_count', messageCount);
            formData.append('duration_seconds', sessionDuration);
            formData.append('user_question', userQuestion);
            formData.append('ai_response', aiResponse);
            formData.append('system_prompt_variant', variantName);
            
            // Estrai topic keywords dalla domanda (semplice estrazione)
            const keywords = extractKeywords(userQuestion);
            if (keywords.length > 0) {
                formData.append('topics', keywords.join(', '));
            }
            
            await fetch(window.tinkaiConfig.ajaxUrl, {
                method: 'POST',
                body: formData
            });
        } catch (error) {
            console.warn('Could not save conversation to database:', error);
        }
    }
    
    function extractKeywords(text) {
        // Rimuovi stopwords comuni e estrai parole chiave
        const stopwords = ['il', 'lo', 'la', 'i', 'gli', 'le', 'un', 'uno', 'una', 'di', 'a', 'da', 'in', 'con', 'su', 'per', 'tra', 'fra', 'come', 'cosa', 'che', 'mi', 'ti', 'si', 'ci', 'vi', 'ne', 'e', 'o', 'ma', 'se', 'quando', 'dove', 'chi', 'puoi', 'può', 'vorrei', 'voglio'];
        const words = text.toLowerCase()
            .replace(/[^\w\sàèéìòù]/g, ' ')
            .split(/\\s+/)
            .filter(word => word.length > 3 && !stopwords.includes(word));
        
        // Restituisci prime 5 parole uniche
        return [...new Set(words)].slice(0, 5);
    }
    
    // Check user quota on load
    checkUserQuota();
    
    // Track interaction
    trackInteraction();
    
    // Auto-save conversation every 30 seconds
    setInterval(() => {
        if (messageCount > 0 && window.tinkaiConfig) {
            const sessionDuration = Math.floor((Date.now() - sessionStartTime) / 1000);
            const variantName = currentVariant ? currentVariant.name : 'default';
            
            const formData = new FormData();
            formData.append('action', 'tinkai_save_conversation');
            formData.append('nonce', window.tinkaiConfig.nonce);
            formData.append('session_id', sessionId);
            formData.append('message_count', messageCount);
            formData.append('duration_seconds', sessionDuration);
            formData.append('system_prompt_variant', variantName);
            
            fetch(window.tinkaiConfig.ajaxUrl, {
                method: 'POST',
                body: formData
            }).catch(err => console.warn('Auto-save failed:', err));
        }
    }, 30000);
    
    // Save on page unload
    window.addEventListener('beforeunload', () => {
        if (messageCount > 0 && window.tinkaiConfig) {
            const sessionDuration = Math.floor((Date.now() - sessionStartTime) / 1000);
            const variantName = currentVariant ? currentVariant.name : 'default';
            
            const formData = new FormData();
            formData.append('action', 'tinkai_save_conversation');
            formData.append('nonce', window.tinkaiConfig.nonce);
            formData.append('session_id', sessionId);
            formData.append('message_count', messageCount);
            formData.append('duration_seconds', sessionDuration);
            formData.append('system_prompt_variant', variantName);
            
            navigator.sendBeacon(window.tinkaiConfig.ajaxUrl, formData);
        }
    });
    
    // Initialize feedback modal
    initializeFeedbackModal();
    
    // Initialize bug reporter
    initializeBugReporter();
    
    async function checkUserQuota() {
        if (!window.tinkaiConfig) return; // Solo in modalità WordPress
        
        try {
            const formData = new FormData();
            formData.append('action', 'tinkai_check_quota');
            formData.append('nonce', window.tinkaiConfig.nonce);
            
            const response = await fetch(window.tinkaiConfig.ajaxUrl, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                updateQuotaDisplay(data.data);
                
                if (data.data.quota_exceeded) {
                    showQuotaWarning(data.data);
                }
            }
        } catch (error) {
            console.warn('Could not check quota:', error);
        }
    }
    
    function updateQuotaDisplay(quotaData) {
        // Crea o aggiorna il widget della quota
        let quotaWidget = document.getElementById('quota-widget');
        
        if (!quotaWidget) {
            quotaWidget = document.createElement('div');
            quotaWidget.id = 'quota-widget';
            quotaWidget.className = 'quota-widget';
            
            const header = document.querySelector('.chat-header');
            if (header) {
                header.appendChild(quotaWidget);
            }
        }
        
        const dailyPercent = (quotaData.daily_used / quotaData.daily_quota) * 100;
        const weeklyPercent = (quotaData.weekly_used / quotaData.weekly_quota) * 100;
        
        quotaWidget.innerHTML = `
            <div class="quota-info" title="Utilizzo giornaliero e settimanale">
                📊 ${quotaData.daily_used}/${quotaData.daily_quota} oggi | ${quotaData.weekly_used}/${quotaData.weekly_quota} questa settimana
            </div>
        `;
        
        if (dailyPercent > 80 || weeklyPercent > 80) {
            quotaWidget.classList.add('quota-warning');
        }
    }
    
    function showQuotaWarning(quotaData) {
        const warning = document.createElement('div');
        warning.className = 'quota-exceeded-warning';
        warning.innerHTML = `
            <h3>⚠️ Quota Raggiunta</h3>
            <p>${quotaData.message}</p>
            <button onclick="this.parentElement.remove()">OK</button>
        `;
        document.body.appendChild(warning);
    }
    
    async function trackInteraction() {
        if (!window.tinkaiConfig) return;
        
        try {
            const formData = new FormData();
            formData.append('action', 'tinkai_track_interaction');
            formData.append('nonce', window.tinkaiConfig.nonce);
            
            await fetch(window.tinkaiConfig.ajaxUrl, {
                method: 'POST',
                body: formData
            });
        } catch (error) {
            console.warn('Could not track interaction:', error);
        }
    }
    
    function initializeFeedbackModal() {
        // Aggiungi pulsante feedback nella header
        const header = document.querySelector('.chat-header');
        if (!header) return;
        
        const feedbackBtn = document.createElement('button');
        feedbackBtn.className = 'header-btn feedback-modal-btn';
        feedbackBtn.innerHTML = '⭐ Feedback';
        feedbackBtn.title = 'Lascia un feedback dettagliato';
        feedbackBtn.onclick = showDetailedFeedbackModal;
        
        header.appendChild(feedbackBtn);
    }
    
    function showDetailedFeedbackModal() {
        // Rimuovi modal esistente
        const existingModal = document.getElementById('detailed-feedback-modal');
        if (existingModal) existingModal.remove();
        
        const modal = document.createElement('div');
        modal.id = 'detailed-feedback-modal';
        modal.className = 'feedback-modal';
        modal.innerHTML = `
            <div class="modal-overlay" onclick="document.getElementById('detailed-feedback-modal').remove()"></div>
            <div class="modal-content">
                <h2>📝 Lascia un Feedback Dettagliato</h2>
                
                <div class="rating-section">
                    <label>Valutazione generale:</label>
                    <div class="star-rating">
                        ${[1,2,3,4,5].map(n => `<span class="star" data-rating="${n}">⭐</span>`).join('')}
                    </div>
                    <input type="hidden" id="feedback-rating" value="0">
                </div>
                
                <div class="form-group">
                    <label for="what-worked">Cosa ha funzionato bene?</label>
                    <textarea id="what-worked" rows="3" placeholder="Cosa ti è piaciuto di TinkAi?"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="what-failed">Cosa non ha funzionato?</label>
                    <textarea id="what-failed" rows="3" placeholder="Cosa potrebbe essere migliorato?"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="suggestion">Suggerimenti:</label>
                    <textarea id="suggestion" rows="3" placeholder="Le tue idee per migliorare TinkAi"></textarea>
                </div>
                
                <div class="modal-actions">
                    <button class="btn-primary" onclick="submitDetailedFeedback()">Invia Feedback</button>
                    <button class="btn-secondary" onclick="document.getElementById('detailed-feedback-modal').remove()">Annulla</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Star rating functionality
        modal.querySelectorAll('.star').forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                document.getElementById('feedback-rating').value = rating;
                
                modal.querySelectorAll('.star').forEach((s, idx) => {
                    s.classList.toggle('active', idx < rating);
                });
            });
        });
    }
    
    window.submitDetailedFeedback = async function() {
        const rating = document.getElementById('feedback-rating').value;
        const whatWorked = document.getElementById('what-worked').value;
        const whatFailed = document.getElementById('what-failed').value;
        const suggestion = document.getElementById('suggestion').value;
        
        if (rating == 0) {
            alert('Per favore seleziona una valutazione!');
            return;
        }
        
        if (!window.tinkaiConfig) {
            console.warn('Feedback available only in WordPress mode');
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('action', 'tinkai_submit_feedback');
            formData.append('nonce', window.tinkaiConfig.nonce);
            formData.append('rating', rating);
            formData.append('session_id', sessionId);
            formData.append('what_worked', whatWorked);
            formData.append('what_failed', whatFailed);
            formData.append('suggestion', suggestion);
            
            // Aggiungi variant info se disponibile
            if (currentVariant) {
                formData.append('variant_id', currentVariant.id);
                formData.append('variant_name', currentVariant.name);
            }
            
            // Aggiungi ultimo messaggio se disponibile
            if (chatHistory.length > 0) {
                const lastUser = chatHistory.filter(m => m.role === 'user').slice(-1)[0];
                const lastBot = chatHistory.filter(m => m.role === 'model').slice(-1)[0];
                
                if (lastUser) formData.append('user_question', lastUser.text);
                if (lastBot) formData.append('ai_response', lastBot.text);
            }
            
            const response = await fetch(window.tinkaiConfig.ajaxUrl, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast('✅ Grazie per il tuo feedback!', 3000);
                document.getElementById('detailed-feedback-modal').remove();
            } else {
                alert('Errore nell\'invio del feedback. Riprova.');
            }
        } catch (error) {
            console.error('Feedback error:', error);
            alert('Errore nell\'invio del feedback.');
        }
    };
    
    function initializeBugReporter() {
        const header = document.querySelector('.chat-header');
        if (!header) return;
        
        const bugBtn = document.createElement('button');
        bugBtn.className = 'header-btn bug-report-btn';
        bugBtn.innerHTML = '🐛 Segnala Bug';
        bugBtn.title = 'Segnala un problema';
        bugBtn.onclick = showBugReportModal;
        
        header.appendChild(bugBtn);
        
        // Cattura errori console
        window.tinkaiConsoleLogs = [];
        const originalError = console.error;
        console.error = function(...args) {
            window.tinkaiConsoleLogs.push(`ERROR: ${args.join(' ')}`);
            originalError.apply(console, args);
        };
    }
    
    function showBugReportModal() {
        const existingModal = document.getElementById('bug-report-modal');
        if (existingModal) existingModal.remove();
        
        const modal = document.createElement('div');
        modal.id = 'bug-report-modal';
        modal.className = 'feedback-modal';
        modal.innerHTML = `
            <div class="modal-overlay" onclick="document.getElementById('bug-report-modal').remove()"></div>
            <div class="modal-content">
                <h2>🐛 Segnala un Bug</h2>
                
                <div class="form-group">
                    <label for="bug-description">Descrivi il problema:</label>
                    <textarea id="bug-description" rows="5" required placeholder="Cosa non ha funzionato? Quando è successo?"></textarea>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="include-logs" checked>
                        Includi log della console (aiuta a risolvere il problema)
                    </label>
                </div>
                
                <div class="modal-actions">
                    <button class="btn-primary" onclick="submitBugReport()">Invia Segnalazione</button>
                    <button class="btn-secondary" onclick="document.getElementById('bug-report-modal').remove()">Annulla</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }
    
    window.submitBugReport = async function() {
        const description = document.getElementById('bug-description').value;
        const includeLogs = document.getElementById('include-logs').checked;
        
        if (!description.trim()) {
            alert('Descrivi il problema prima di inviare!');
            return;
        }
        
        if (!window.tinkaiConfig) {
            console.warn('Bug reporting available only in WordPress mode');
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('action', 'tinkai_report_bug');
            formData.append('nonce', window.tinkaiConfig.nonce);
            formData.append('description', description);
            formData.append('url', window.location.href);
            formData.append('user_agent', navigator.userAgent);
            
            if (includeLogs && window.tinkaiConsoleLogs) {
                formData.append('console_logs', window.tinkaiConsoleLogs.join('\n'));
            }
            
            const response = await fetch(window.tinkaiConfig.ajaxUrl, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast('✅ Bug segnalato! Grazie per il tuo aiuto.', 3000);
                document.getElementById('bug-report-modal').remove();
            } else {
                alert('Errore nell\'invio della segnalazione.');
            }
        } catch (error) {
            console.error('Bug report error:', error);
            alert('Errore nell\'invio della segnalazione.');
        }
    };
});

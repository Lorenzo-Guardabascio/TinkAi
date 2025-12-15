# Changelog

Tutte le modifiche rilevanti a questo progetto saranno documentate in questo file.

## [1.3.0] - 2024-01-XX

### 🆕 Aggiunto
- **WordPress Plugin Integration**: Conversione completa a plugin WordPress
  - Shortcode `[tinkai]` per embedding facile
  - Admin panel con configurazione API keys
  - Dashboard metriche cognitive integrata
  - Documentazione completa nell'admin
  - AJAX proxy per comunicazione sicura con backend Node.js
  
- **Admin Features**:
  - Pagina impostazioni con test connessione backend
  - Dashboard metriche real-time con auto-refresh
  - Documentazione interattiva integrata
  - Status backend con health check
  
- **Dark Mode** (v1.3.0):
  - Toggle tema chiaro/scuro
  - Persistenza preferenza in localStorage
  - CSS variables per theming consistente
  - Animazioni smooth per transizioni tema
  
- **Keyboard Shortcuts** (v1.3.0):
  - `Ctrl/Cmd + K`: Nuova conversazione
  - `Ctrl/Cmd + E`: Esporta conversazione
  - `Ctrl/Cmd + D`: Toggle dark mode
  - `Esc`: Focus su input
  - Modal shortcuts accessibile con `?`
  
- **Toast Notifications** (v1.3.0):
  - Feedback visivo per azioni utente
  - Animazioni fade in/out
  - Auto-dismiss dopo 3 secondi
  
- **Daily Statistics** (v1.3.0):
  - Tracking metriche giornaliere
  - Visualizzazione calendario ultimi 7 giorni
  - Retention 30 giorni
  - Breakdown riflessioni vs risposte dirette per giorno

### 🔄 Modificato
- **Architettura**: WordPress come layer di hosting, Node.js come backend API
- **Frontend**: Script.js adattato per dual-mode (standalone + WordPress)
- **Backend**: Aggiunto endpoint `/api/health` per health checks
- **Settings**: Centralizzate in WordPress admin invece di .env

### 🐛 Risolto
- Dark mode styling in feedback.html
- Body overflow issue in pagine standalone
- Compatibilità cross-browser per keyboard shortcuts

## [1.2.0] - 2024-01-XX

### 🆕 Aggiunto
- **Enhanced System Prompt**:
  - Anti-gaming detection avanzato
  - Contextual adaptation (matematica vs letteratura)
  - Homework detection con risposta educativa
  
- **Feedback System**:
  - Thumbs up/down su ogni risposta
  - Analytics dashboard (feedback.html)
  - localStorage tracking (max 100 feedback)
  - Visualizzazione statistiche con grafici
  
- **Mobile Optimization**:
  - Touch targets aumentati a 44px
  - Responsive design migliorato
  - Viewport meta tag ottimizzato
  
- **Privacy Compliance**:
  - GDPR-compliant privacy banner
  - Trasparenza su gestione dati
  - Link policy dettagliate

### 🔒 Sicurezza
- Rate limiting: 20 richieste / 15 minuti
- Input sanitization migliorata
- Validazione rigorosa parametri API

## [1.1.0] - 2024-01-XX

### 🆕 Aggiunto
- **Typing Indicator**: Animazione "..." durante elaborazione
- **localStorage Persistence**: 
  - Salvataggio automatico conversazioni
  - Retention 24 ore
  - Timestamp tracking
- **README.md**: Documentazione completa del progetto
- **Cognitive Metrics System**:
  - TinkAi Score (0-100)
  - Tracking risposte riflessive vs dirette
  - Dashboard metriche (metrics.html)
  - Analisi pattern interazioni

### 🔄 Modificato
- Improved UI/UX con animazioni smooth
- Better error handling con messaggi user-friendly

### 🐛 Risolto
- Nullish coalescing operator (richiede Node.js 14+)
- Memory leaks in rate limiting
- Edge cases input validation

## [1.0.0] - 2024-01-XX

### 🆕 Rilascio Iniziale (MVP)
- Chat interface base con UI minimale
- Integrazione Google Gemini API
- Integrazione OpenAI GPT API (opzionale)
- System prompt cognitivo
- Express.js backend
- Frontend vanilla JavaScript
- Compressione gzip
- CORS configurato

### ✨ Features Core
- Stimolazione pensiero critico
- Risposta adattiva basata su contesto
- Storico conversazione in sessione
- Export conversazione in TXT
- Clear chat functionality

---

## Legenda

- 🆕 `Aggiunto`: Nuove feature
- 🔄 `Modificato`: Modifiche a feature esistenti
- 🐛 `Risolto`: Bug fix
- 🔒 `Sicurezza`: Security improvements
- ⚠️ `Deprecato`: Feature in dismissione
- 🗑️ `Rimosso`: Feature rimosse

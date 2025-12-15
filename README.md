# TinkAi - WordPress Plugin

**The intelligence that keeps you thinking**

![Version](https://img.shields.io/badge/version-1.3.0-blue)
![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)
![Node.js](https://img.shields.io/badge/Node.js-18%2B-green)
![License](https://img.shields.io/badge/license-MIT-green)

Un assistente AI progettato per **stimolare il pensiero critico** invece di sostituirlo. TinkAi è un plugin WordPress che integra un backend Node.js per offrire un'esperienza cognitiva unica.

## 📋 Indice

- [Caratteristiche](#-caratteristiche)
- [Requisiti](#-requisiti)
- [Installazione](#-installazione)
- [Configurazione](#-configurazione)
- [Utilizzo](#-utilizzo)
- [Architettura Tecnica](#-architettura-tecnica)
- [Troubleshooting](#-troubleshooting)
- [FAQ](#-faq)
- [Licenza](#-licenza)

## ✨ Caratteristiche

- 🧠 **Pensiero Critico**: Stimola il ragionamento autonomo invece di fornire risposte pronte
- 🎯 **Sistema Anti-Gaming**: Riconosce tentativi di aggirare il processo educativo
- 📊 **Metriche Cognitive**: Dashboard dettagliata con TinkAi Score e statistiche giornaliere
- 👍👎 **Sistema Feedback**: Raccolta feedback utenti con analytics
- 🌓 **Dark Mode**: Tema chiaro/scuro con persistenza localStorage
- ⌨️ **Keyboard Shortcuts**: Scorciatoie da tastiera per power users
- 🔒 **Privacy-First**: Dati salvati solo nel browser (localStorage), GDPR compliant
- 📱 **Responsive**: Design ottimizzato per mobile (touch targets 44px)
- ⚡ **Performance**: Compressione gzip, lazy loading, rate limiting

## 🔧 Requisiti

### Software Necessario

- **WordPress**: 5.8 o superiore
- **PHP**: 7.4 o superiore
- **Node.js**: 18.0 o superiore
- **NPM**: 8.0 o superiore

### Hosting Requirements

- Accesso SSH al server
- Possibilità di eseguire processi Node.js persistenti (PM2 consigliato)
- Reverse proxy configurato (Nginx/Apache)

### API Keys

Almeno una delle seguenti:

- **Google Gemini API** (consigliato per iniziare - gratuita): [Google AI Studio](https://makersuite.google.com/app/apikey)
- **OpenAI API** (GPT-3.5/GPT-4): [OpenAI Platform](https://platform.openai.com/api-keys)

## 📦 Installazione

### Metodo 1: Installazione Manuale

1. **Download del plugin**:
   ```bash
   cd /path/to/wordpress/wp-content/plugins/
   # Copia la cartella tinkai-plugin qui
   ```

2. **Installazione dipendenze Node.js**:
   ```bash
   cd tinkai-plugin/backend/
   npm install
   ```

3. **Configurazione variabili ambiente**:
   ```bash
   cd tinkai-plugin/backend/
   cp .env.example .env
   nano .env
   ```
   
   Aggiungi le tue API keys:
   ```env
   AI_PROVIDER=gemini
   GEMINI_API_KEY=tua_api_key_gemini
   OPENAI_API_KEY=tua_api_key_openai
   PORT=3000
   ```

4. **Attivazione plugin in WordPress**:
   - Vai su `Plugin > Plugin installati`
   - Trova "TinkAi - Cognitive AI Assistant"
   - Clicca "Attiva"

5. **Avvio backend Node.js**:
   
   **Opzione A: Esecuzione Manuale** (per test)
   ```bash
   cd wp-content/plugins/tinkai-plugin/backend/
   node server.js
   ```
   
   **Opzione B: PM2 (consigliato per produzione)**
   ```bash
   # Installa PM2 globalmente
   npm install -g pm2
   
   # Avvia il backend
   cd wp-content/plugins/tinkai-plugin/backend/
   pm2 start ecosystem.config.json
   
   # Verifica status
   pm2 status
   pm2 logs tinkai-backend
   
   # Salva configurazione per avvio automatico
   pm2 save
   pm2 startup
   ```

### Metodo 2: Via ZIP

1. Comprimi la cartella `wordpress-plugin` in `tinkai-plugin.zip`
2. In WordPress: `Plugin > Aggiungi nuovo > Carica plugin`
3. Carica il file ZIP
4. Attiva il plugin
5. Segui i passi 2-5 del Metodo 1

## ⚙️ Configurazione

### 1. Configurazione WordPress Admin

Vai su `TinkAi > Impostazioni` e configura:

#### API Configuration
- **Provider AI**: Scegli tra Gemini o OpenAI
- **Gemini API Key**: Inserisci la tua API key di Google
- **OpenAI API Key**: Inserisci la tua API key di OpenAI

#### Node.js Backend Configuration
- **Host Node.js**: `localhost` (o IP interno se su server diverso)
- **Porta Node.js**: `3000` (default)

#### Funzionalità
- ✅ Metriche Cognitive
- ✅ Sistema Feedback
- ✅ Dark Mode

### 2. Test Connessione

Nella pagina impostazioni, clicca su **"🔍 Verifica Connessione"** per testare il backend Node.js.

### 3. Reverse Proxy (se necessario)

Se il tuo hosting usa Nginx, aggiungi al file di configurazione:

```nginx
location /tinkai-api/ {
    proxy_pass http://localhost:3000/api/;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'upgrade';
    proxy_set_header Host $host;
    proxy_cache_bypass $http_upgrade;
}
```

Per Apache (`.htaccess`):

```apache
<IfModule mod_proxy.c>
    ProxyPass /tinkai-api/ http://localhost:3000/api/
    ProxyPassReverse /tinkai-api/ http://localhost:3000/api/
</IfModule>
```

## 🎯 Utilizzo

### Shortcode Base

Inserisci TinkAi in qualsiasi pagina o post:

```
[tinkai]
```

### Opzioni Shortcode

```
[tinkai theme="dark"]                    # Tema scuro di default
[tinkai height="800px"]                  # Altezza personalizzata
[tinkai width="90%"]                     # Larghezza personalizzata
[tinkai theme="dark" height="700px"]     # Combinazione opzioni
```

### Keyboard Shortcuts

Gli utenti possono usare queste scorciatoie:

- `Ctrl/Cmd + K` - Nuova conversazione
- `Ctrl/Cmd + E` - Esporta conversazione
- `Ctrl/Cmd + D` - Cambia tema (chiaro/scuro)
- `Esc` - Focus sull'input

### Dashboard Admin

#### Metriche Cognitive (`TinkAi > Metrics`)
- 🎯 **TinkAi Score**: Indice di qualità cognitiva (0-100)
- 💭 **Risposte Riflessive**: Quante volte TinkAi ha stimolato riflessione
- 📝 **Risposte Dirette**: Risposte informative dirette
- 📅 **Statistiche Giornaliere**: Calendario ultimi 7 giorni

#### Documentazione (`TinkAi > Documentation`)
- Guida completa all'uso
- Best practices
- Risoluzione problemi

## 🏗️ Architettura Tecnica

### Stack Tecnologico

```
Frontend Layer (WordPress)
├── PHP 7.4+
├── WordPress 5.8+
└── AJAX Proxy

Application Layer (Node.js)
├── Express.js
├── Google Gemini AI
├── OpenAI GPT
└── Cognitive Metrics Engine

Client Layer
├── Vanilla JavaScript
├── HTML5 / CSS3
└── localStorage (persistenza)
```

### Flusso Dati

```
Utente WordPress
    ↓
WordPress Frontend (shortcode [tinkai])
    ↓
WordPress AJAX Proxy (PHP)
    ↓
Node.js Backend Express (port 3000)
    ↓
AI Provider (Gemini/OpenAI)
    ↓
Cognitive Metrics Analysis
    ↓
Response + Metrics
    ↓
Utente WordPress
```

### Perché Node.js Separato?

Il backend Node.js è mantenuto separato per:

1. **Scalabilità**: Permette load balancing indipendente
2. **Manutenibilità**: Logica AI separata da WordPress
3. **Flessibilità**: Può essere usato con altre applicazioni
4. **Futura Migrazione**: Preparato per React/Angular frontend

## 🔧 Troubleshooting

### ❌ "Backend non connesso"

**Causa**: Il server Node.js non è in esecuzione

**Soluzione**:
```bash
cd wp-content/plugins/tinkai-plugin/backend/
node server.js
```

Oppure verifica PM2:
```bash
pm2 list
pm2 logs tinkai-backend
pm2 restart tinkai-backend
```

### ❌ "Errore API"

**Causa**: API key mancante o errata

**Soluzione**:
1. Verifica `.env` nel backend:
   ```bash
   cat wp-content/plugins/tinkai-plugin/backend/.env
   ```
2. Controlla che l'API key sia valida sul provider
3. Verifica limiti di utilizzo nell'account

### ❌ "Rate limit exceeded"

**Causa**: Troppe richieste (20 in 15 minuti)

**Soluzione**: Attendi qualche minuto. Il rate limiting previene abusi.

### ❌ Chat non si carica

**Possibili cause**:
- Conflitti con altri plugin JavaScript
- Theme WordPress incompatibile
- File CSS/JS non caricati

**Soluzione**:
1. Apri Console del browser (F12) per vedere errori
2. Prova a disabilitare temporaneamente altri plugin
3. Testa con theme WordPress standard (Twenty Twenty-Three)
4. Verifica che i file in `assets/` siano accessibili

### ❌ "Module not found" (Node.js)

**Causa**: Dipendenze non installate

**Soluzione**:
```bash
cd wp-content/plugins/tinkai-plugin/backend/
npm install
```

### ❌ Porta 3000 già in uso

**Soluzione 1**: Cambia porta nel `.env`
```env
PORT=3001
```

**Soluzione 2**: Trova processo che occupa porta 3000
```bash
lsof -i :3000
kill -9 <PID>
```

## ❓ FAQ

### Dove vengono salvati i dati delle conversazioni?

Solo nel browser dell'utente tramite `localStorage`. Nessun dato viene salvato sui server WordPress o Node.js.

### È compatibile con Multisite WordPress?

Sì, ma il backend Node.js deve essere condiviso tra i siti o configurato per istanza separata.

### Posso usare TinkAi senza Node.js?

No. TinkAi richiede il backend Node.js per comunicare con i provider AI. L'architettura è progettata per scalabilità e futura migrazione a SPA.

### Quanto costa l'API di Google Gemini?

Google Gemini offre un tier gratuito generoso. Consulta: [Gemini Pricing](https://ai.google.dev/pricing)

### Posso personalizzare il sistema prompt?

Sì! Modifica il file `backend/systemPrompt.js` per personalizzare il comportamento di TinkAi.

### Come disinstallo completamente il plugin?

1. Disattiva il plugin in WordPress
2. Ferma il backend Node.js: `pm2 delete tinkai-backend`
3. Elimina la cartella: `rm -rf wp-content/plugins/tinkai-plugin/`

### È GDPR compliant?

Sì. TinkAi:
- Non traccia identificatori personali
- Salva dati solo nel browser dell'utente
- Mostra banner informativo sulla privacy
- I messaggi inviati ai provider AI seguono le loro policy

## 📄 Licenza

MIT License - Copyright (c) 2024 Lorenzo Guardabascio

## 🤝 Supporto

- 📧 **Email**: support@tinkai.local
- 📖 **Documentazione**: Disponibile in `TinkAi > Documentation` nell'admin WordPress
- 🐛 **Bug Report**: Controlla prima la sezione Troubleshooting

## 🚀 Roadmap

- [ ] Supporto multilingua (i18n)
- [ ] Dashboard analytics avanzato
- [ ] Export conversazioni in PDF
- [ ] Integrazione con LMS (Moodle, LearnDash)
- [ ] Mobile app (React Native)
- [ ] API pubbliche per integrazioni terze parti

---

**Sviluppato con ❤️ per stimolare il pensiero critico nell'era dell'AI**

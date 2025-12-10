# TinkAi

**The intelligence that keeps you thinking**

TinkAi non è un assistente AI tradizionale. È un progetto culturale che dimostra come l'intelligenza artificiale dovrebbe comportarsi per preservare e potenziare l'autonomia cognitiva dell'utente.

## 🎯 Missione

Proteggere la mente umana dall'atrofia cognitiva causata dall'IA "spoon-feeding". TinkAi fa domande prima di rispondere, stimola la riflessione e guida il percorso mentale senza sostituirsi al pensiero.

---

## 🚀 Quick Start (Hestia Control Panel)

### Prerequisiti
- Server con Hestia Control Panel
- Node.js 18+ installato
- Accesso SSH
- API Key di Gemini o OpenAI

### 1. Setup del Progetto

```bash
# Naviga nella cartella public_html del tuo dominio
cd /home/dev/web/tinkai.local/public_html

# Installa dipendenze backend
cd backend
npm install

# Copia e configura file ambiente
cp .env.example .env
nano .env
```

### 2. Configurazione .env

```env
# Provider AI: "gemini" o "openai"
AI_PROVIDER=gemini

# Gemini API Key (consigliato per MVP)
GEMINI_API_KEY=your_gemini_api_key_here

# OpenAI API Key (opzionale)
OPENAI_API_KEY=your_openai_api_key_here

# Porta del server (default 3000)
PORT=3000
```

**Ottieni una API Key:**
- **Gemini**: https://makersuite.google.com/app/apikey
- **OpenAI**: https://platform.openai.com/api-keys

### 3. Avvio del Server

#### Opzione A: Manuale (per test)
```bash
cd /home/dev/web/tinkai.local/public_html/backend
node server.js
```

#### Opzione B: Con PM2 (consigliato per produzione)
```bash
# Installa PM2 globalmente (se non presente)
npm install -g pm2

# Avvia il server
cd /home/dev/web/tinkai.local/public_html/backend
pm2 start server.js --name tinkai

# Salva la configurazione per riavvio automatico
pm2 save
pm2 startup
```

### 4. Configurazione Nginx (Hestia)

Crea un proxy reverse per collegare il frontend statico al backend Node.js:

```bash
# Modifica la configurazione Nginx del dominio
sudo nano /etc/nginx/conf.d/tinkai.local.conf
```

Aggiungi dentro il blocco `server`:

```nginx
location /api/ {
    proxy_pass http://localhost:3000;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'upgrade';
    proxy_set_header Host $host;
    proxy_cache_bypass $http_upgrade;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
}
```

Riavvia Nginx:
```bash
sudo systemctl restart nginx
```

### 5. Test

Apri il browser su `http://tinkai.local` (o il tuo dominio) e inizia a chattare!

---

## 📁 Struttura del Progetto

```
public_html/
├── index.html          # Frontend UI
├── style.css           # Stili minimal
├── script.js           # Logica frontend + localStorage
├── robots.txt          # SEO
├── .gitignore          # Esclusioni Git
└── backend/
    ├── server.js       # Server Express + API
    ├── systemPrompt.js # Regole cognitive TinkAi
    ├── .env            # Configurazione (NON committare)
    ├── .env.example    # Template configurazione
    └── package.json    # Dipendenze
```

---

## ⚙️ Funzionalità Implementate

### Frontend
- ✅ Chat UI minimal e pulita
- ✅ Indicatore "typing" durante elaborazione
- ✅ Gestione errori UX-friendly
- ✅ Persistenza conversazioni (localStorage, 24h)
- ✅ Export conversazione in TXT
- ✅ Accessibilità (ARIA labels, keyboard support)
- ✅ Responsive design

### Backend
- ✅ API REST `/api/chat`
- ✅ Rate limiting (20 req/15min per IP)
- ✅ Validazione e sanitizzazione input
- ✅ Supporto multi-provider (Gemini/OpenAI)
- ✅ Gestione storico conversazione
- ✅ Error handling robusto

### System Prompt
- ✅ Regole cognitive TinkAi
- ✅ Prevenzione spoon-feeding
- ✅ Stimolazione metacognizione

---

## 🔧 Troubleshooting

### Il server non parte
```bash
# Verifica che Node.js sia installato
node --version  # deve essere 18+

# Verifica le dipendenze
cd backend && npm install

# Controlla i log
pm2 logs tinkai
```

### Errore 404 Not Found (Gemini)
Abilita l'API Google Generative Language:
https://console.cloud.google.com/apis/library/generativelanguage.googleapis.com

### La chat non si collega al backend
Verifica che:
1. Il server Node.js sia avviato (`pm2 status`)
2. La configurazione Nginx sia corretta
3. Il firewall permetta la porta 3000 in locale

### Conversazione non si salva
Controlla la console del browser (F12) per errori localStorage.
Alcuni browser in modalità privata disabilitano lo storage.

---

## 🔐 Sicurezza e Privacy

- **Rate Limiting**: Previene abusi (20 richieste/15min)
- **Input Validation**: Sanitizzazione messaggi
- **API Keys**: Mai esposte al frontend
- **localStorage**: Dati salvati solo localmente (non inviati a server esterni)
- **HTTPS**: Consigliato per produzione (usa Hestia Let's Encrypt)

---

## 🚧 Roadmap Futura

- [ ] Dashboard metriche cognitive
- [ ] Multi-lingua (EN, IT, ES)
- [ ] Profili utente (studente, professionista, etc.)
- [ ] Analytics: domande vs risposte dirette
- [ ] Integrazione con altri LLM (Claude, Llama)
- [ ] Modalità "insegnante" con report

---

## 📄 Licenza e Filosofia

TinkAi è un **manifesto culturale**. Non è solo codice, è una visione su come l'IA dovrebbe comportarsi.

**Principi fondamentali:**
1. L'IA deve fare domande prima di rispondere
2. Deve stimolare il pensiero critico, non sostituirlo
3. Deve insegnare a ragionare, non a chiedere
4. Deve proteggere la metacognizione

---

## 🤝 Contributi

Questo progetto è nato come MVP educativo. Contributi che rispettano il manifesto sono benvenuti.

**Repository**: https://github.com/Lorenzo-Guardabascio/TinkAi

---

## 📧 Supporto

Per domande tecniche o filosofiche sul progetto, apri una issue su GitHub.

**TinkAi** - L'intelligenza che tiene accesa la tua. 🧠

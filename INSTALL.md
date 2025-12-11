# TinkAi WordPress Plugin - Quick Start

## 🚀 Installazione Rapida (5 minuti)

### 1. Prerequisiti
- ✅ WordPress 5.8+
- ✅ PHP 7.4+
- ✅ Node.js 18+
- ✅ Accesso SSH

### 2. Installa Plugin

```bash
# Copia questa cartella in WordPress plugins
cp -r wordpress-plugin /path/to/wordpress/wp-content/plugins/tinkai-plugin

# Vai alla cartella backend
cd /path/to/wordpress/wp-content/plugins/tinkai-plugin/backend/

# Installa dipendenze Node.js
npm install
```

### 3. Configura API Keys

```bash
# Copia il template .env
cp .env.example .env

# Modifica con le tue API keys
nano .env
```

Aggiungi:
```env
AI_PROVIDER=gemini
GEMINI_API_KEY=tua_api_key_qui
PORT=3000
```

**Ottieni API Key gratis**: https://makersuite.google.com/app/apikey

### 4. Avvia Backend

```bash
# Metodo 1: Test rapido
node server.js

# Metodo 2: Produzione (consigliato)
npm install -g pm2
pm2 start ecosystem.config.json
pm2 save
```

### 5. Attiva in WordPress

1. Vai su `Plugin > Plugin installati`
2. Trova "TinkAi - Cognitive AI Assistant"
3. Clicca **Attiva**

### 6. Configura WordPress

1. Vai su `TinkAi > Impostazioni`
2. Verifica connessione backend (dovrebbe essere verde ✅)
3. Configura opzioni a piacere

### 7. Usa il Plugin

Crea una nuova pagina WordPress e inserisci:

```
[tinkai]
```

Salva e visualizza!

## 🎯 Comandi Utili

```bash
# Verifica status backend
pm2 status

# Vedi logs
pm2 logs tinkai-backend

# Riavvia backend
pm2 restart tinkai-backend

# Ferma backend
pm2 stop tinkai-backend

# Elimina backend da PM2
pm2 delete tinkai-backend
```

## 🔧 Problemi Comuni

### Backend non si connette?
```bash
# Verifica che sia in esecuzione
pm2 list

# Controlla i logs per errori
pm2 logs tinkai-backend

# Riavvia
pm2 restart tinkai-backend
```

### API non funziona?
1. Controlla che l'API key sia corretta in `.env`
2. Verifica limiti di utilizzo sul provider
3. Testa manualmente: `curl http://localhost:3000/api/health`

### Porta 3000 occupata?
```bash
# Cambia porta nel .env
echo "PORT=3001" >> .env

# Riavvia backend
pm2 restart tinkai-backend

# Aggiorna porta anche in WordPress (TinkAi > Impostazioni)
```

## 📚 Documentazione Completa

Leggi il [README.md](README.md) per documentazione dettagliata.

---

**Pronto a stimolare il pensiero critico! 🧠**

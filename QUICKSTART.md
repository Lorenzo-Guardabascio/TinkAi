# TinkAi - Quick Start Guide

## 🚀 Setup Rapido (3 minuti)

### 1. Installa dipendenze
```bash
cd /home/dev/web/tinkai.local/public_html/backend
npm install
```

### 2. Configura API Key
```bash
cp .env.example .env
nano .env
```
Inserisci la tua API Key di Gemini o OpenAI.

### 3. Avvia il server

**Metodo A - Test veloce:**
```bash
node server.js
```

**Metodo B - Produzione con PM2:**
```bash
npm install -g pm2
pm2 start ecosystem.config.json
pm2 save
```

### 4. Configura Nginx
Aggiungi al file `/etc/nginx/conf.d/tinkai.local.conf`:

```nginx
location /api/ {
    proxy_pass http://localhost:3000;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'upgrade';
    proxy_set_header Host $host;
    proxy_cache_bypass $http_upgrade;
}
```

Riavvia: `sudo systemctl restart nginx`

### 5. Test
Apri il browser: `http://tinkai.local`

## 📊 Visualizza Metriche
Vai su: `http://tinkai.local/metrics.html`

## 🔧 Comandi Utili

```bash
# Stato server
pm2 status

# Log in tempo reale
pm2 logs tinkai

# Riavvia
pm2 restart tinkai

# Stop
pm2 stop tinkai
```

## ❓ Problemi?
Leggi il README completo: `README.md`

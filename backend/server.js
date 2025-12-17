const express = require('express');
const cors = require('cors');
const dotenv = require('dotenv');
const path = require('path');
const compression = require('compression');
const { OpenAI } = require('openai');
const { GoogleGenerativeAI } = require('@google/generative-ai');
const systemPrompt = require('./systemPrompt');
const CognitiveMetrics = require('./cognitiveMetrics');

// Load only WP_URL and PORT from .env
dotenv.config();

const app = express();
const PORT = process.env.PORT || 3000;

// Configuration from WordPress
let config = {
    AI_PROVIDER: 'gemini',
    AI_MODEL: 'gemini-2.5-flash',
    OPENAI_API_KEY: '',
    GEMINI_API_KEY: ''
};

// Function to load configuration from WordPress
async function loadConfigFromWordPress() {
    try {
        const fetch = (await import('node-fetch')).default;
        const wpUrl = process.env.WP_URL || 'http://localhost';
        const response = await fetch(`${wpUrl}/wp-admin/admin-ajax.php?action=tinkai_get_config`);
        
        if (response.ok) {
            const data = await response.json();
            if (data.success && data.data) {
                config = {
                    AI_PROVIDER: data.data.AI_PROVIDER || 'gemini',
                    AI_MODEL: data.data.AI_MODEL || 'gemini-2.5-flash',
                    OPENAI_API_KEY: data.data.OPENAI_API_KEY || '',
                    GEMINI_API_KEY: data.data.GEMINI_API_KEY || ''
                };
                console.log('✓ Configuration loaded from WordPress');
                console.log(`  Provider: ${config.AI_PROVIDER}`);
                console.log(`  Model: ${config.AI_MODEL}`);
                initializeAIClients();
                return true;
            }
        }
        console.warn('⚠ Failed to load config from WordPress, using defaults');
        return false;
    } catch (error) {
        console.error('⚠ Error loading config from WordPress:', error.message);
        return false;
    }
}

// Rate limiting: max 20 richieste per IP ogni 15 minuti
const requestCounts = new Map();
const RATE_LIMIT_WINDOW = 15 * 60 * 1000; // 15 minuti
const MAX_REQUESTS = 20;

function rateLimitMiddleware(req, res, next) {
    const ip = req.ip || req.connection.remoteAddress;
    const now = Date.now();
    
    if (!requestCounts.has(ip)) {
        requestCounts.set(ip, []);
    }
    
    const requests = requestCounts.get(ip);
    // Rimuovi richieste vecchie
    const recentRequests = requests.filter(time => now - time < RATE_LIMIT_WINDOW);
    
    if (recentRequests.length >= MAX_REQUESTS) {
        return res.status(429).json({ 
            error: 'Troppi tentativi. Prenditi un momento per riflettere prima di continuare.' 
        });
    }
    
    recentRequests.push(now);
    requestCounts.set(ip, recentRequests);
    next();
}

// Pulizia periodica della mappa
setInterval(() => {
    const now = Date.now();
    for (const [ip, requests] of requestCounts.entries()) {
        const recent = requests.filter(time => now - time < RATE_LIMIT_WINDOW);
        if (recent.length === 0) {
            requestCounts.delete(ip);
        } else {
            requestCounts.set(ip, recent);
        }
    }
}, RATE_LIMIT_WINDOW);

// Middleware
app.use(cors());
app.use(express.json());
app.use(compression()); // Compressione gzip per performance

// Serve static files from the parent directory (public_html)
app.use(express.static(path.join(__dirname, '../')));

// AI Clients Initialization
let openai;
let genAI;

function initializeAIClients() {
    if (config.OPENAI_API_KEY) {
        openai = new OpenAI({ apiKey: config.OPENAI_API_KEY });
        console.log('✓ OpenAI client initialized');
    }

    if (config.GEMINI_API_KEY) {
        genAI = new GoogleGenerativeAI(config.GEMINI_API_KEY);
        console.log('✓ Gemini client initialized');
    }
}

// Cognitive Metrics System
const cognitiveMetrics = new CognitiveMetrics();

// Chat Endpoint
app.post('/api/chat', rateLimitMiddleware, async (req, res) => {
    const { message, history, systemPromptOverride, model } = req.body;
    const provider = config.AI_PROVIDER || 'gemini';

    // Validazione input
    if (!message) {
        return res.status(400).json({ error: 'Message is required' });
    }

    if (typeof message !== 'string') {
        return res.status(400).json({ error: 'Message must be a string' });
    }

    if (message.length > 2000) {
        return res.status(400).json({ error: 'Message too long (max 2000 characters)' });
    }

    if (message.trim().length === 0) {
        return res.status(400).json({ error: 'Message cannot be empty' });
    }

    // Validazione history
    if (history && (!Array.isArray(history) || history.length > 50)) {
        return res.status(400).json({ error: 'Invalid conversation history' });
    }

    // Sanitizzazione base (rimuove caratteri di controllo potenzialmente pericolosi)
    const sanitizedMessage = message.replace(/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/g, '');
    
    // Usa systemPromptOverride se fornito (per A/B testing), altrimenti usa quello di default
    const activePrompt = systemPromptOverride || systemPrompt;

    try {
        let reply = '';

        if (provider === 'openai' && openai) {
            const messages = [
                { role: "system", content: activePrompt }
            ];

            // Aggiungi history per OpenAI
            if (history && Array.isArray(history)) {
                history.forEach(msg => {
                    messages.push({
                        role: msg.role === 'user' ? 'user' : 'assistant',
                        content: msg.text
                    });
                });
            }
            messages.push({ role: "user", content: sanitizedMessage });

            const modelName = model && model.startsWith('gpt') ? model : "gpt-4o-mini";
            const completion = await openai.chat.completions.create({
                messages: messages,
                model: modelName,
            });
            reply = completion.choices[0].message.content;

        } else if (provider === 'gemini' && genAI) {
            // Using user-specified model or default
            const modelName = model && model.startsWith('gemini') ? model : "gemini-2.5-flash";
            const geminiModel = genAI.getGenerativeModel({ model: modelName });
            
            // Costruisci la history per Gemini con il prompt attivo (può essere variant)
            const geminiHistory = [
                {
                    role: "user",
                    parts: [{ text: activePrompt }],
                },
                {
                    role: "model",
                    parts: [{ text: "Ho capito. Sono pronto a comportarmi come TinkAi." }],
                }
            ];

            // Aggiungi lo storico della conversazione se presente
            if (history && Array.isArray(history)) {
                history.forEach(msg => {
                    geminiHistory.push({
                        role: msg.role === 'user' ? 'user' : 'model',
                        parts: [{ text: msg.text }]
                    });
                });
            }

            const chat = geminiModel.startChat({
                history: geminiHistory,
            });
            const result = await chat.sendMessage(sanitizedMessage);
            const response = await result.response;
            reply = response.text();

        } else {
            // Fallback or Mock for MVP if no keys
            console.log("No API keys configured or provider not found. Using mock response.");
            reply = "Questa è una risposta simulata (MVP). Configura le API Key nel file .env per attivare l'intelligenza reale. \n\nDomanda di riflessione: Cosa ti aspetti che io ti dica ora?";
        }

        // Analizza risposta per metriche cognitive
        cognitiveMetrics.analyzeResponse(reply);

        res.json({ reply });

    } catch (error) {
        console.error('AI Error:', error);
        if (error.message && (error.message.includes('404') || error.message.includes('Not Found'))) {
            console.error("\n!!! ATTENZIONE !!!\nL'errore 404 indica spesso che l'API 'Google Generative Language API' non è abilitata nel tuo progetto Google Cloud.\nVai su: https://console.cloud.google.com/apis/library/generativelanguage.googleapis.com\nE assicurati di averla abilitata per il progetto associato alla tua API Key.\n");
        }
        res.status(500).json({ error: 'Internal Server Error processing your thought.' });
    }
});

// Health check endpoint (per WordPress plugin)
app.get('/api/health', (req, res) => {
    res.json({ 
        status: 'ok', 
        version: '1.3.0',
        uptime: process.uptime(),
        timestamp: new Date().toISOString()
    });
});

// Endpoint per visualizzare le metriche cognitive
app.get('/api/metrics', (req, res) => {
    const report = cognitiveMetrics.getReport();
    res.json(report);
});

// Endpoint per resettare le metriche (solo per admin/dev)
app.post('/api/metrics/reset', (req, res) => {
    cognitiveMetrics.reset();
    res.json({ message: 'Metrics reset successfully' });
});

// Endpoint per feedback analytics
app.get('/api/feedback/stats', (req, res) => {
    // Restituisce statistiche aggregate (non dati personali)
    const stats = {
        message: 'Feedback analytics disponibili solo lato client (localStorage)',
        hint: 'Controlla localStorage per feedback individuali'
    };
    res.json(stats);
});

app.listen(PORT, '0.0.0.0', async () => {
    console.log(`TinkAi Server running on port ${PORT}`);
    console.log(`Test locally: http://localhost:${PORT}`);
    console.log('Loading configuration from WordPress...');
    await loadConfigFromWordPress();
    
    // Reload config every 5 minutes to pick up changes
    setInterval(loadConfigFromWordPress, 5 * 60 * 1000);
});

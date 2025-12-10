const express = require('express');
const cors = require('cors');
const dotenv = require('dotenv');
const path = require('path');
const { OpenAI } = require('openai');
const { GoogleGenerativeAI } = require('@google/generative-ai');
const systemPrompt = require('./systemPrompt');

dotenv.config();

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());

// Serve static files from the parent directory (public_html)
app.use(express.static(path.join(__dirname, '../')));

// AI Clients Initialization
let openai;
let genAI;

if (process.env.OPENAI_API_KEY) {
    openai = new OpenAI({ apiKey: process.env.OPENAI_API_KEY });
}

if (process.env.GEMINI_API_KEY) {
    genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);
}

// Chat Endpoint
app.post('/api/chat', async (req, res) => {
    const { message, history } = req.body;
    const provider = process.env.AI_PROVIDER || 'gemini'; // Default to gemini or openai

    if (!message) {
        return res.status(400).json({ error: 'Message is required' });
    }

    try {
        let reply = '';

        if (provider === 'openai' && openai) {
            const messages = [
                { role: "system", content: systemPrompt }
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
            messages.push({ role: "user", content: message });

            const completion = await openai.chat.completions.create({
                messages: messages,
                model: "gpt-4", // or gpt-3.5-turbo
            });
            reply = completion.choices[0].message.content;

        } else if (provider === 'gemini' && genAI) {
            // Using user-specified model gemini-2.5-flash
            const model = genAI.getGenerativeModel({ model: "gemini-2.5-flash" });
            
            // Costruisci la history per Gemini
            const geminiHistory = [
                {
                    role: "user",
                    parts: [{ text: systemPrompt }],
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

            const chat = model.startChat({
                history: geminiHistory,
            });
            const result = await chat.sendMessage(message);
            const response = await result.response;
            reply = response.text();

        } else {
            // Fallback or Mock for MVP if no keys
            console.log("No API keys configured or provider not found. Using mock response.");
            reply = "Questa è una risposta simulata (MVP). Configura le API Key nel file .env per attivare l'intelligenza reale. \n\nDomanda di riflessione: Cosa ti aspetti che io ti dica ora?";
        }

        res.json({ reply });

    } catch (error) {
        console.error('AI Error:', error);
        if (error.message && (error.message.includes('404') || error.message.includes('Not Found'))) {
            console.error("\n!!! ATTENZIONE !!!\nL'errore 404 indica spesso che l'API 'Google Generative Language API' non è abilitata nel tuo progetto Google Cloud.\nVai su: https://console.cloud.google.com/apis/library/generativelanguage.googleapis.com\nE assicurati di averla abilitata per il progetto associato alla tua API Key.\n");
        }
        res.status(500).json({ error: 'Internal Server Error processing your thought.' });
    }
});

app.listen(PORT, '0.0.0.0', () => {
    console.log(`TinkAi Server running on port ${PORT}`);
    console.log(`Test locally: http://localhost:${PORT}`);
});

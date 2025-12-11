<?php
/**
 * Template: Admin Documentation
 */

if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1>📚 Documentazione TinkAi</h1>
    
    <div class="tinkai-docs-container">
        
        <!-- Introduction -->
        <div class="doc-section">
            <h2>🎯 Cos'è TinkAi?</h2>
            <p>
                TinkAi è un assistente AI progettato per <strong>stimolare il pensiero critico</strong> invece di sostituirlo. 
                Non è un semplice chatbot che fornisce risposte pronte, ma un companion intellettuale che ti aiuta a:
            </p>
            <ul>
                <li>✨ Sviluppare il pensiero critico e analitico</li>
                <li>🧠 Imparare a ragionare autonomamente</li>
                <li>🔍 Approfondire concetti senza scorciatoie</li>
                <li>💡 Trasformare domande superficiali in riflessioni profonde</li>
            </ul>
        </div>
        
        <!-- Setup Instructions -->
        <div class="doc-section">
            <h2>⚙️ Installazione e Configurazione</h2>
            
            <h3>1. Configurazione API</h3>
            <p>TinkAi può utilizzare due provider AI:</p>
            <ul>
                <li>
                    <strong>Google Gemini</strong> (consigliato per iniziare)
                    <ul>
                        <li>Vai su <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a></li>
                        <li>Crea una nuova API key</li>
                        <li>Incollala nelle impostazioni del plugin</li>
                    </ul>
                </li>
                <li>
                    <strong>OpenAI (GPT)</strong>
                    <ul>
                        <li>Vai su <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a></li>
                        <li>Crea una nuova API key</li>
                        <li>Incollala nelle impostazioni del plugin</li>
                    </ul>
                </li>
            </ul>
            
            <h3>2. Avvio del Backend Node.js</h3>
            <p>
                <strong>⚠️ Importante:</strong> TinkAi utilizza un backend Node.js separato che deve essere avviato manualmente.
            </p>
            
            <h4>Metodo 1: Esecuzione Manuale</h4>
            <pre>cd <?php echo ABSPATH; ?>wp-content/plugins/tinkai-plugin/backend/
node server.js</pre>
            
            <h4>Metodo 2: Esecuzione Persistente con PM2 (consigliato)</h4>
            <pre># Installa PM2 globalmente
npm install -g pm2

# Avvia il backend
cd <?php echo ABSPATH; ?>wp-content/plugins/tinkai-plugin/backend/
pm2 start ecosystem.config.json

# Verifica status
pm2 status

# Salva configurazione per avvio automatico
pm2 save
pm2 startup</pre>
            
            <h3>3. Inserimento in Pagine/Post</h3>
            <p>Usa lo shortcode <code>[tinkai]</code> per inserire la chat:</p>
            
            <h4>Esempi:</h4>
            <pre>// Configurazione base
[tinkai]

// Con tema scuro
[tinkai theme="dark"]

// Altezza personalizzata
[tinkai height="800px"]

// Larghezza personalizzata  
[tinkai width="90%"]

// Combinazione opzioni
[tinkai theme="dark" height="700px" width="100%"]</pre>
        </div>
        
        <!-- Usage Guide -->
        <div class="doc-section">
            <h2>🎓 Come Usare TinkAi</h2>
            
            <h3>❌ Cosa NON Fare</h3>
            <div class="warning-box">
                <p><strong>Evita domande pigre come:</strong></p>
                <ul>
                    <li>"Dammi la risposta a questo problema"</li>
                    <li>"Fai i compiti al posto mio"</li>
                    <li>"Qual è la soluzione?"</li>
                </ul>
                <p>TinkAi è progettato per riconoscere questi pattern e ti guiderà verso il pensiero critico.</p>
            </div>
            
            <h3>✅ Cosa Fare</h3>
            <div class="success-box">
                <p><strong>Esempi di domande efficaci:</strong></p>
                <ul>
                    <li>"Non capisco questo concetto di [argomento], puoi aiutarmi a ragionarci?"</li>
                    <li>"Sto cercando di risolvere [problema], da dove potrei iniziare?"</li>
                    <li>"Come posso migliorare il mio approccio a [argomento]?"</li>
                </ul>
            </div>
            
            <h3>⌨️ Keyboard Shortcuts</h3>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Scorciatoia</th>
                        <th>Azione</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>Ctrl/Cmd + K</code></td>
                        <td>Nuova conversazione</td>
                    </tr>
                    <tr>
                        <td><code>Ctrl/Cmd + E</code></td>
                        <td>Esporta conversazione</td>
                    </tr>
                    <tr>
                        <td><code>Ctrl/Cmd + D</code></td>
                        <td>Cambia tema (chiaro/scuro)</td>
                    </tr>
                    <tr>
                        <td><code>Esc</code></td>
                        <td>Focus sull'input</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Metrics Explanation -->
        <div class="doc-section">
            <h2>📊 Comprendere le Metriche</h2>
            
            <h3>🎯 TinkAi Score</h3>
            <p>
                Indice da 0 a 100 che misura la qualità cognitiva delle interazioni. 
                Un punteggio alto indica che TinkAi sta stimolando efficacemente il pensiero critico.
            </p>
            
            <h3>💭 Risposte Riflessive</h3>
            <p>
                Risposte che guidano l'utente verso la riflessione autonoma attraverso domande strategiche, 
                esempi guidati e inviti all'approfondimento.
            </p>
            
            <h3>📝 Risposte Dirette</h3>
            <p>
                Risposte che forniscono informazioni dirette a domande specifiche e ben formulate. 
                Non sempre "diretta" significa "peggiore" - dipende dal contesto.
            </p>
        </div>
        
        <!-- Privacy & GDPR -->
        <div class="doc-section">
            <h2>🔐 Privacy e GDPR</h2>
            
            <h3>Dove vengono salvati i dati?</h3>
            <ul>
                <li><strong>Conversazioni:</strong> Solo nel browser dell'utente (localStorage), mai sui server</li>
                <li><strong>Feedback (👍/👎):</strong> Solo nel browser dell'utente</li>
                <li><strong>Metriche Cognitive:</strong> Salvate nel backend Node.js per analisi aggregate (nessun dato personale)</li>
            </ul>
            
            <h3>Cosa viene inviato ai provider AI?</h3>
            <p>
                I messaggi vengono inviati a Google Gemini o OpenAI per elaborazione, secondo le loro policy di privacy:
            </p>
            <ul>
                <li><a href="https://policies.google.com/privacy" target="_blank">Google Privacy Policy</a></li>
                <li><a href="https://openai.com/policies/privacy-policy" target="_blank">OpenAI Privacy Policy</a></li>
            </ul>
            
            <h3>Banner Privacy</h3>
            <p>
                TinkAi mostra automaticamente un banner informativo al primo utilizzo, 
                spiegando trasparenza e gestione dati.
            </p>
        </div>
        
        <!-- Troubleshooting -->
        <div class="doc-section">
            <h2>🔧 Risoluzione Problemi</h2>
            
            <h3>❌ "Backend non connesso"</h3>
            <p><strong>Causa:</strong> Il server Node.js non è in esecuzione</p>
            <p><strong>Soluzione:</strong></p>
            <pre>cd <?php echo ABSPATH; ?>wp-content/plugins/tinkai-plugin/backend/
node server.js</pre>
            
            <h3>❌ "Errore API"</h3>
            <p><strong>Causa:</strong> API key mancante o errata</p>
            <p><strong>Soluzione:</strong></p>
            <ul>
                <li>Verifica che l'API key sia inserita correttamente nelle impostazioni</li>
                <li>Controlla che il provider selezionato corrisponda all'API key inserita</li>
                <li>Verifica eventuali limiti di utilizzo nell'account del provider</li>
            </ul>
            
            <h3>❌ "Rate limit exceeded"</h3>
            <p><strong>Causa:</strong> Troppe richieste in poco tempo</p>
            <p><strong>Soluzione:</strong></p>
            <p>TinkAi implementa un rate limiting di 20 richieste ogni 15 minuti. Attendi qualche minuto prima di continuare.</p>
            
            <h3>❌ Chat non si carica</h3>
            <p><strong>Possibili cause:</strong></p>
            <ul>
                <li>Conflitti con altri plugin JavaScript</li>
                <li>Theme WordPress incompatibile</li>
                <li>File CSS/JS non caricati correttamente</li>
            </ul>
            <p><strong>Soluzione:</strong></p>
            <ul>
                <li>Disabilita temporaneamente altri plugin per identificare conflitti</li>
                <li>Verifica nella Console del browser (F12) eventuali errori JavaScript</li>
                <li>Prova con un theme WordPress standard (Twenty Twenty-Three)</li>
            </ul>
        </div>
        
        <!-- Technical Architecture -->
        <div class="doc-section">
            <h2>🏗️ Architettura Tecnica</h2>
            
            <h3>Stack Tecnologico</h3>
            <ul>
                <li><strong>Frontend:</strong> Vanilla JavaScript, HTML5, CSS3</li>
                <li><strong>Backend:</strong> Node.js v18+ con Express.js</li>
                <li><strong>AI Providers:</strong> Google Gemini / OpenAI GPT</li>
                <li><strong>WordPress Integration:</strong> PHP 7.4+ con AJAX proxy</li>
            </ul>
            
            <h3>Flusso Dati</h3>
            <pre>Utente WordPress
    ↓
WordPress Frontend (shortcode)
    ↓
WordPress AJAX Proxy (PHP)
    ↓
Node.js Backend (port 3000)
    ↓
AI Provider (Gemini/OpenAI)
    ↓
Response + Cognitive Metrics
    ↓
Utente WordPress</pre>
            
            <h3>Perché Node.js separato?</h3>
            <p>
                Il backend Node.js è separato per permettere una futura migrazione a React/Angular 
                senza dover riscrivere la logica AI. WordPress funge da layer di hosting e autenticazione.
            </p>
        </div>
        
        <!-- Support -->
        <div class="doc-section">
            <h2>💬 Supporto</h2>
            <p>
                Per domande, problemi o suggerimenti:
            </p>
            <ul>
                <li>📧 Email: <a href="mailto:support@tinkai.local">support@tinkai.local</a></li>
                <li>📖 README: <code><?php echo ABSPATH; ?>wp-content/plugins/tinkai-plugin/README.md</code></li>
                <li>🐛 Issues: Verifica prima la sezione "Risoluzione Problemi" sopra</li>
            </ul>
        </div>
        
    </div>
</div>

<style>
.tinkai-docs-container {
    max-width: 900px;
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.doc-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 1px solid #e5e5e5;
}

.doc-section:last-child {
    border-bottom: none;
}

.doc-section h2 {
    color: #2271b1;
    margin-top: 0;
}

.doc-section h3 {
    color: #50575e;
    margin-top: 25px;
}

.doc-section h4 {
    color: #646970;
    margin-top: 20px;
}

.doc-section pre {
    background: #f5f5f5;
    padding: 15px;
    border-left: 4px solid #2271b1;
    overflow-x: auto;
    font-size: 13px;
    line-height: 1.6;
}

.doc-section code {
    background: #f5f5f5;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 13px;
}

.warning-box {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 15px;
    margin: 15px 0;
}

.success-box {
    background: #d4edda;
    border-left: 4px solid #28a745;
    padding: 15px;
    margin: 15px 0;
}

.doc-section ul {
    line-height: 1.8;
}

.doc-section table {
    margin-top: 15px;
}

.doc-section table th {
    background: #f5f5f5;
    font-weight: 600;
}

.doc-section table code {
    background: #fff;
    border: 1px solid #ddd;
}
</style>

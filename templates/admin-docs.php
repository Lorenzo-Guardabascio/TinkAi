<?php
/**
 * Template: Admin Documentation
 */

if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1>📚 TinkAi Documentation</h1>
    
    <div class="tinkai-docs-container">
        
        <!-- Introduction -->
        <div class="doc-section">
            <h2>🎯 What is TinkAi?</h2>
            <p>
                TinkAi is an AI assistant designed to <strong>stimulate critical thinking</strong> instead of replacing it. 
                It's not a simple chatbot that provides ready-made answers, but an intellectual companion that helps you:
            </p>
            <ul>
                <li>✨ Develop critical and analytical thinking</li>
                <li>🧠 Learn to reason independently</li>
                <li>🔍 Deepen concepts without shortcuts</li>
                <li>💡 Transform superficial questions into deep reflections</li>
            </ul>
        </div>
        
        <!-- Setup Instructions -->
        <div class="doc-section">
            <h2>⚙️ Installation and Configuration</h2>
            
            <h3>1. API Configuration</h3>
            <p>TinkAi can use two AI providers:</p>
            <ul>
                <li>
                    <strong>Google Gemini</strong> (recommended to get started)
                    <ul>
                        <li>Go to <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a></li>
                        <li>Create a new API key</li>
                        <li>Paste it in the plugin settings</li>
                    </ul>
                </li>
                <li>
                    <strong>OpenAI (GPT)</strong>
                    <ul>
                        <li>Go to <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a></li>
                        <li>Create a new API key</li>
                        <li>Paste it in the plugin settings</li>
                    </ul>
                </li>
            </ul>
            
            <h3>2. Starting the Node.js Backend</h3>
            <p>
                <strong>⚠️ Important:</strong> TinkAi uses a separate Node.js backend that must be started manually.
            </p>
            
            <h4>Method 1: Manual Execution</h4>
            <pre>cd <?php echo ABSPATH; ?>wp-content/plugins/tinkai-plugin/backend/
node server.js</pre>
            
            <h4>Method 2: Persistent Execution with PM2 (recommended)</h4>
            <pre># Install PM2 globally
npm install -g pm2

# Start the backend
cd <?php echo ABSPATH; ?>wp-content/plugins/tinkai-plugin/backend/
pm2 start ecosystem.config.json

# Check status
pm2 status

# Save configuration for automatic startup
pm2 save
pm2 startup</pre>
            
            <h3>3. Inserting in Pages/Posts</h3>
            <p>Use the shortcode <code>[tinkai]</code> to insert the chat:</p>
            
            <h4>Examples:</h4>
            <pre>// Basic configuration
[tinkai]

// With dark theme
[tinkai theme="dark"]

// Custom height
[tinkai height="800px"]

// Custom width  
[tinkai width="90%"]

// Combined options
[tinkai theme="dark" height="700px" width="100%"]</pre>
        </div>
        
        <!-- Usage Guide -->
        <div class="doc-section">
            <h2>🎓 How to Use TinkAi</h2>
            
            <h3>❌ What NOT to Do</h3>
            <div class="warning-box">
                <p><strong>Avoid lazy questions like:</strong></p>
                <ul>
                    <li>"Give me the answer to this problem"</li>
                    <li>"Do my homework for me"</li>
                    <li>"What is the solution?"</li>
                </ul>
                <p>TinkAi is designed to recognize these patterns and will guide you toward critical thinking.</p>
            </div>
            
            <h3>✅ What to Do</h3>
            <div class="success-box">
                <p><strong>Examples of effective questions:</strong></p>
                <ul>
                    <li>"I don't understand this concept of [topic], can you help me reason through it?"</li>
                    <li>"I'm trying to solve [problem], where could I start?"</li>
                    <li>"How can I improve my approach to [topic]?"</li>
                </ul>
            </div>
            
            <h3>⌨️ Keyboard Shortcuts</h3>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Shortcut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>Ctrl/Cmd + K</code></td>
                        <td>New conversation</td>
                    </tr>
                    <tr>
                        <td><code>Ctrl/Cmd + E</code></td>
                        <td>Export conversation</td>
                    </tr>
                    <tr>
                        <td><code>Ctrl/Cmd + D</code></td>
                        <td>Toggle theme (light/dark)</td>
                    </tr>
                    <tr>
                        <td><code>Esc</code></td>
                        <td>Focus on input</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Metrics Explanation -->
        <div class="doc-section">
            <h2>📊 Understanding Metrics</h2>
            
            <h3>🎯 TinkAi Score</h3>
            <p>
                An index from 0 to 100 that measures the cognitive quality of interactions. 
                A high score indicates that TinkAi is effectively stimulating critical thinking.
            </p>
            
            <h3>💭 Reflective Responses</h3>
            <p>
                Responses that guide the user toward independent reflection through strategic questions, 
                guided examples and invitations to deepen.
            </p>
            
            <h3>📝 Direct Responses</h3>
            <p>
                Responses that provide direct information to specific and well-formed questions. 
                Not always "direct" means "worse" - it depends on the context.
            </p>
        </div>
        
        <!-- Privacy & GDPR -->
        <div class="doc-section">
            <h2>🔐 Privacy and GDPR</h2>
            
            <h3>Where is data saved?</h3>
            <ul>
                <li><strong>Conversations:</strong> Only in the user's browser (localStorage), never on servers</li>
                <li><strong>Feedback (👍/👎):</strong> Only in the user's browser</li>
                <li><strong>Cognitive Metrics:</strong> Saved in the Node.js backend for aggregate analysis (no personal data)</li>
            </ul>
            
            <h3>What is sent to AI providers?</h3>
            <p>
                Messages are sent to Google Gemini or OpenAI for processing, according to their privacy policies:
            </p>
            <ul>
                <li><a href="https://policies.google.com/privacy" target="_blank">Google Privacy Policy</a></li>
                <li><a href="https://openai.com/policies/privacy-policy" target="_blank">OpenAI Privacy Policy</a></li>
            </ul>
            
            <h3>Privacy Banner</h3>
            <p>
                TinkAi automatically displays an informative banner on first use, 
                explaining transparency and data management.
            </p>
        </div>
        
        <!-- Troubleshooting -->
        <div class="doc-section">
            <h2>🔧 Troubleshooting</h2>
            
            <h3>❌ "Backend not connected"</h3>
            <p><strong>Cause:</strong> The Node.js server is not running</p>
            <p><strong>Solution:</strong></p>
            <pre>cd <?php echo ABSPATH; ?>wp-content/plugins/tinkai-plugin/backend/
node server.js</pre>
            
            <h3>❌ "API Error"</h3>
            <p><strong>Cause:</strong> Missing or incorrect API key</p>
            <p><strong>Solution:</strong></p>
            <ul>
                <li>Verify that the API key is entered correctly in the settings</li>
                <li>Check that the selected provider matches the entered API key</li>
                <li>Check for any usage limits in the provider's account</li>
            </ul>
            
            <h3>❌ "Rate limit exceeded"</h3>
            <p><strong>Cause:</strong> Too many requests in a short time</p>
            <p><strong>Solution:</strong></p>
            <p>TinkAi implements rate limiting of 20 requests every 15 minutes. Wait a few minutes before continuing.</p>
            
            <h3>❌ Chat not loading</h3>
            <p><strong>Possible causes:</strong></p>
            <ul>
                <li>Conflicts with other JavaScript plugins</li>
                <li>Incompatible WordPress theme</li>
                <li>CSS/JS files not loaded correctly</li>
            </ul>
            <p><strong>Solution:</strong></p>
            <ul>
                <li>Temporarily disable other plugins to identify conflicts</li>
                <li>Check the browser Console (F12) for JavaScript errors</li>
                <li>Try with a standard WordPress theme (Twenty Twenty-Three)</li>
            </ul>
        </div>
        
        <!-- Technical Architecture -->
        <div class="doc-section">
            <h2>🏗️ Technical Architecture</h2>
            
            <h3>Technology Stack</h3>
            <ul>
                <li><strong>Frontend:</strong> Vanilla JavaScript, HTML5, CSS3</li>
                <li><strong>Backend:</strong> Node.js v18+ with Express.js</li>
                <li><strong>AI Providers:</strong> Google Gemini / OpenAI GPT</li>
                <li><strong>WordPress Integration:</strong> PHP 7.4+ with AJAX proxy</li>
            </ul>
            
            <h3>Data Flow</h3>
            <pre>WordPress User
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
WordPress User</pre>
            
            <h3>Why separate Node.js?</h3>
            <p>
                The Node.js backend is separate to allow future migration to React/Angular 
                without having to rewrite the AI logic. WordPress acts as a hosting and authentication layer.
            </p>
        </div>
        
        <!-- Support -->
        <div class="doc-section">
            <h2>💬 Support</h2>
            <p>
                For questions, issues or suggestions:
            </p>
            <ul>
                <li>📧 Email: <a href="mailto:support@tinkai.local">support@tinkai.local</a></li>
                <li>📖 README: <code><?php echo ABSPATH; ?>wp-content/plugins/tinkai-plugin/README.md</code></li>
                <li>🐛 Issues: Check the "Troubleshooting" section above first</li>
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

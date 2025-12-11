<?php
/**
 * Template: Admin Settings
 */

if (!defined('ABSPATH')) exit;

$settings = get_option('tinkai_settings', array());
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="tinkai-admin-header">
        <img src="<?php echo TINKAI_PLUGIN_URL; ?>assets/logo.png" alt="TinkAi Logo" style="max-width: 200px; margin-bottom: 20px;" onerror="this.style.display='none'">
        <p class="description">
            <strong>TinkAi</strong> - The intelligence that keeps you thinking.<br>
            Un assistente AI che stimola il pensiero critico invece di sostituirlo.
        </p>
    </div>
    
    <?php settings_errors('tinkai_settings'); ?>
    
    <form method="post" action="options.php">
        <?php settings_fields('tinkai_settings_group'); ?>
        
        <div class="tinkai-settings-container">
            
            <!-- API Configuration -->
            <div class="postbox">
                <h2 class="hndle"><span>🔑 Configurazione API</span></h2>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="api_provider">Provider AI</label>
                            </th>
                            <td>
                                <select name="tinkai_settings[api_provider]" id="api_provider">
                                    <option value="gemini" <?php selected($settings['api_provider'] ?? 'gemini', 'gemini'); ?>>Google Gemini</option>
                                    <option value="openai" <?php selected($settings['api_provider'] ?? 'gemini', 'openai'); ?>>OpenAI (GPT)</option>
                                </select>
                                <p class="description">Scegli il provider di intelligenza artificiale da utilizzare.</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="gemini_api_key">Gemini API Key</label>
                            </th>
                            <td>
                                <input type="password" 
                                       name="tinkai_settings[gemini_api_key]" 
                                       id="gemini_api_key" 
                                       value="<?php echo esc_attr($settings['gemini_api_key'] ?? ''); ?>" 
                                       class="regular-text">
                                <p class="description">
                                    Ottieni una API key gratuita da 
                                    <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="openai_api_key">OpenAI API Key</label>
                            </th>
                            <td>
                                <input type="password" 
                                       name="tinkai_settings[openai_api_key]" 
                                       id="openai_api_key" 
                                       value="<?php echo esc_attr($settings['openai_api_key'] ?? ''); ?>" 
                                       class="regular-text">
                                <p class="description">
                                    Ottieni una API key da 
                                    <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Node.js Backend Configuration -->
            <div class="postbox">
                <h2 class="hndle"><span>⚙️ Configurazione Node.js Backend</span></h2>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="nodejs_host">Host Node.js</label>
                            </th>
                            <td>
                                <input type="text" 
                                       name="tinkai_settings[nodejs_host]" 
                                       id="nodejs_host" 
                                       value="<?php echo esc_attr($settings['nodejs_host'] ?? 'localhost'); ?>" 
                                       class="regular-text">
                                <p class="description">Indirizzo del server Node.js (solitamente "localhost" o IP interno)</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="nodejs_port">Porta Node.js</label>
                            </th>
                            <td>
                                <input type="number" 
                                       name="tinkai_settings[nodejs_port]" 
                                       id="nodejs_port" 
                                       value="<?php echo esc_attr($settings['nodejs_port'] ?? 3000); ?>" 
                                       min="1000" 
                                       max="65535">
                                <p class="description">Porta su cui gira il backend Node.js (default: 3000)</p>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="tinkai-backend-status">
                        <h4>Stato Backend:</h4>
                        <button type="button" class="button" id="check-backend-status">
                            🔍 Verifica Connessione
                        </button>
                        <span id="backend-status-result"></span>
                    </div>
                </div>
            </div>
            
            <!-- Features Configuration -->
            <div class="postbox">
                <h2 class="hndle"><span>🎨 Funzionalità</span></h2>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th scope="row">Metriche Cognitive</th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           name="tinkai_settings[enable_metrics]" 
                                           value="1" 
                                           <?php checked($settings['enable_metrics'] ?? true); ?>>
                                    Abilita dashboard metriche cognitive
                                </label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Feedback Utenti</th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           name="tinkai_settings[enable_feedback]" 
                                           value="1" 
                                           <?php checked($settings['enable_feedback'] ?? true); ?>>
                                    Abilita sistema feedback (👍/👎)
                                </label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">Dark Mode</th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           name="tinkai_settings[enable_dark_mode]" 
                                           value="1" 
                                           <?php checked($settings['enable_dark_mode'] ?? true); ?>>
                                    Abilita toggle tema chiaro/scuro
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Usage Instructions -->
            <div class="postbox">
                <h2 class="hndle"><span>📖 Come Usare TinkAi</span></h2>
                <div class="inside">
                    <h4>Shortcode:</h4>
                    <p>Inserisci TinkAi in qualsiasi pagina o post usando lo shortcode:</p>
                    <code style="padding: 10px; background: #f5f5f5; display: block; margin: 10px 0;">
                        [tinkai]
                    </code>
                    
                    <h4>Opzioni Shortcode:</h4>
                    <ul>
                        <li><code>[tinkai theme="dark"]</code> - Tema scuro di default</li>
                        <li><code>[tinkai height="800px"]</code> - Altezza personalizzata</li>
                        <li><code>[tinkai width="100%"]</code> - Larghezza personalizzata</li>
                    </ul>
                    
                    <h4>Backend Node.js:</h4>
                    <p>
                        <strong>⚠️ Importante:</strong> Il backend Node.js deve essere avviato separatamente.<br>
                        Vai alla cartella <code>/wp-content/plugins/tinkai-plugin/backend/</code> e esegui:
                    </p>
                    <code style="padding: 10px; background: #f5f5f5; display: block; margin: 10px 0;">
                        node server.js
                    </code>
                    <p>Oppure usa PM2 per esecuzione persistente:</p>
                    <code style="padding: 10px; background: #f5f5f5; display: block; margin: 10px 0;">
                        pm2 start ecosystem.config.json
                    </code>
                </div>
            </div>
            
        </div>
        
        <?php submit_button('Salva Impostazioni'); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#check-backend-status').on('click', function() {
        var $btn = $(this);
        var $result = $('#backend-status-result');
        
        $btn.prop('disabled', true).text('⏳ Verifica in corso...');
        $result.html('');
        
        var host = $('#nodejs_host').val();
        var port = $('#nodejs_port').val();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tinkai_check_backend',
                nonce: '<?php echo wp_create_nonce('tinkai_check_backend'); ?>',
                host: host,
                port: port
            },
            success: function(response) {
                if (response.success) {
                    $result.html('<span style="color: green;">✅ Backend connesso!</span>');
                } else {
                    $result.html('<span style="color: red;">❌ ' + response.data.message + '</span>');
                }
            },
            error: function() {
                $result.html('<span style="color: red;">❌ Errore di connessione</span>');
            },
            complete: function() {
                $btn.prop('disabled', false).text('🔍 Verifica Connessione');
            }
        });
    });
});
</script>

<style>
.tinkai-settings-container .postbox {
    margin-bottom: 20px;
}
.tinkai-settings-container .postbox h2 {
    padding: 15px;
    font-size: 16px;
}
.tinkai-settings-container .inside {
    padding: 15px;
}
.tinkai-backend-status {
    margin-top: 20px;
    padding: 15px;
    background: #f9f9f9;
    border-left: 4px solid #2271b1;
}
.tinkai-admin-header {
    margin-bottom: 30px;
    padding: 20px;
    background: #fff;
    border-left: 4px solid #2271b1;
}
</style>

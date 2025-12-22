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
            An AI assistant that stimulates critical thinking instead of replacing it.
        </p>
    </div>
    
    <?php settings_errors('tinkai_settings'); ?>
    
    <form method="post" action="options.php">
        <?php settings_fields('tinkai_settings_group'); ?>
        
        <div class="tinkai-settings-container">
            
            <!-- API Configuration -->
            <div class="postbox">
                <h2 class="hndle"><span>🔑 API Configuration</span></h2>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="api_provider">AI Provider</label>
                            </th>
                            <td>
                                <select name="tinkai_settings[api_provider]" id="api_provider">
                                    <option value="gemini" <?php selected($settings['api_provider'] ?? 'gemini', 'gemini'); ?>>Google Gemini</option>
                                    <option value="openai" <?php selected($settings['api_provider'] ?? 'gemini', 'openai'); ?>>OpenAI (GPT)</option>
                                </select>
                                <p class="description">Choose the artificial intelligence provider to use.</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ai_model">AI Model</label>
                            </th>
                            <td>
                                <select name="tinkai_settings[ai_model]" id="ai_model">
                                    <optgroup label="Google Gemini (Series 3 and 2.5)">
                                        <option value="gemini-3-pro" <?php selected($settings['ai_model'] ?? '', 'gemini-3-pro'); ?>>Gemini 3 Pro (New - Best Quality)</option>
                                        <option value="gemini-2.5-pro" <?php selected($settings['ai_model'] ?? '', 'gemini-2.5-pro'); ?>>Gemini 2.5 Pro</option>
                                        
                                        <option value="gemini-2.5-flash" <?php selected($settings['ai_model'] ?? 'gemini-2.5-flash', 'gemini-2.5-flash'); ?>>Gemini 2.5 Flash (Recommended)</option>
                                        <option value="gemini-2.5-flash-lite" <?php selected($settings['ai_model'] ?? '', 'gemini-2.5-flash-lite'); ?>>Gemini 2.5 Flash-Lite</option>
                                        
                                        <option value="gemini-2.0-flash" <?php selected($settings['ai_model'] ?? '', 'gemini-2.0-flash'); ?>>Gemini 2.0 Flash (Stable)</option>
                                    </optgroup>

                                    <optgroup label="OpenAI (Series o3 and GPT-5)">
                                        <option value="o3" <?php selected($settings['ai_model'] ?? '', 'o3'); ?>>OpenAI o3 (Reasoning SOTA)</option>
                                        <option value="o4-mini" <?php selected($settings['ai_model'] ?? '', 'o4-mini'); ?>>OpenAI o4-mini (Fast Reasoning)</option>
                                        <option value="o1" <?php selected($settings['ai_model'] ?? '', 'o1'); ?>>OpenAI o1</option>

                                        <option value="gpt-5" <?php selected($settings['ai_model'] ?? '', 'gpt-5'); ?>>GPT-5 (Flagship)</option>
                                        <option value="gpt-5-mini" <?php selected($settings['ai_model'] ?? '', 'gpt-5-mini'); ?>>GPT-5 Mini</option>
                                        
                                        <option value="gpt-4o" <?php selected($settings['ai_model'] ?? '', 'gpt-4o'); ?>>GPT-4o (Legacy)</option>
                                    </optgroup>
                                </select>
                                <p class="description">Select the most recent AI model. "Legacy" models might be deprecated soon.</p>
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
                                    Get a free API key from 
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
                                    Get an API key from 
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
                                <p class="description">Node.js server address (usually "localhost" or internal IP)</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="nodejs_port">Node.js Port</label>
                            </th>
                            <td>
                                <input type="number" 
                                       name="tinkai_settings[nodejs_port]" 
                                       id="nodejs_port" 
                                       value="<?php echo esc_attr($settings['nodejs_port'] ?? 3000); ?>" 
                                       min="1000" 
                                       max="65535">
                                <p class="description">Port on which the Node.js backend runs (default: 3000)</p>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="tinkai-backend-status">
                        <h4>Backend Status:</h4>
                        <button type="button" class="button" id="check-backend-status">
                            🔍 Check Connection
                        </button>
                        <span id="backend-status-result"></span>
                    </div>
                </div>
            </div>
            
            <!-- Features Configuration -->
            <div class="postbox">
                <h2 class="hndle"><span>🎨 Features</span></h2>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th scope="row">Cognitive Metrics</th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           name="tinkai_settings[enable_metrics]" 
                                           value="1" 
                                           <?php checked($settings['enable_metrics'] ?? true); ?>>
                                    Enable cognitive metrics dashboard
                                </label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">User Feedback</th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           name="tinkai_settings[enable_feedback]" 
                                           value="1" 
                                           <?php checked($settings['enable_feedback'] ?? true); ?>>
                                    Enable feedback system (👍/👎)
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
                                    Enable light/dark theme toggle
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Usage Instructions -->
            <div class="postbox">
                <h2 class="hndle"><span>📖 How to Use TinkAi</span></h2>
                <div class="inside">
                    <h4>Shortcode:</h4>
                    <p>Insert TinkAi into any page or post using the shortcode:</p>
                    <code style="padding: 10px; background: #f5f5f5; display: block; margin: 10px 0;">
                        [tinkai]
                    </code>
                    
                    <h4>Shortcode Options:</h4>
                    <ul>
                        <li><code>[tinkai theme="dark"]</code> - Dark theme by default</li>
                        <li><code>[tinkai height="800px"]</code> - Custom height</li>
                        <li><code>[tinkai width="100%"]</code> - Custom width</li>
                    </ul>
                    
                    <h4>Node.js Backend:</h4>
                    <p>
                        <strong>⚠️ Important:</strong> The Node.js backend must be started separately.<br>
                        Go to the folder <code>/wp-content/plugins/tinkai-plugin/backend/</code> and run:
                    </p>
                    <code style="padding: 10px; background: #f5f5f5; display: block; margin: 10px 0;">
                        node server.js
                    </code>
                    <p>Or use PM2 for persistent execution:</p>
                    <code style="padding: 10px; background: #f5f5f5; display: block; margin: 10px 0;">
                        pm2 start ecosystem.config.json
                    </code>
                </div>
            </div>
            
        </div>
        
        <?php submit_button('Save Settings'); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#check-backend-status').on('click', function() {
        var $btn = $(this);
        var $result = $('#backend-status-result');
        
        $btn.prop('disabled', true).text('⏳ Checking...');
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
                    $result.html('<span style="color: green;">✅ Backend connected!</span>');
                } else {
                    $result.html('<span style="color: red;">❌ ' + response.data.message + '</span>');
                }
            },
            error: function() {
                $result.html('<span style="color: red;">❌ Connection error</span>');
            },
            complete: function() {
                $btn.prop('disabled', false).text('🔍 Check Connection');
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

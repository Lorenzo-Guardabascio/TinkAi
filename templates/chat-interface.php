<?php
/**
 * Template: Chat Interface
 * 
 * Renders the TinkAi chat interface via shortcode
 */

if (!defined('ABSPATH')) exit;

$settings = get_option('tinkai_settings', array());
$theme = $atts['theme'] ?? 'light';
$width = $atts['width'] ?? '100%';
?>

<div class="tinkai-wrapper" style="width: <?php echo esc_attr($width); ?>; max-width: 800px; margin: 0 auto;" data-theme="<?php echo esc_attr($theme); ?>">
    <div class="container">
        <header>
            <div class="header-actions">  
                
                <button onclick="exportTinkAiChat()" class="icon-btn" title="Export conversation" aria-label="Export conversation">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7 10L12 15L17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                
                <button onclick="clearTinkAiChat()" class="icon-btn" title="New conversation" aria-label="New conversation">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 6H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M19 6V20C19 21 18 22 17 22H7C6 22 5 21 5 20V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 6V4C8 3 9 2 10 2H14C15 2 16 3 16 4V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </header>

        <main class="chat-container" id="chat-container">
            <div class="message bot-message">
                Hi, I'm TinkAi. Before giving you answers, I help you think better. Where shall we start?
            </div>
        </main>

        <footer class="input-area">
            <input type="text" id="user-input" placeholder="Write your thought here..." autocomplete="off">
            <button id="send-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22 2L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </footer>
    </div>
</div>

<!-- Privacy Banner (WordPress version) -->
<div id="tinkai-privacy-banner" class="privacy-banner" style="display: none;">
    <div class="privacy-content">
        <p>
            <strong>🔐 Privacy & Transparency</strong><br>
            TinkAi saves conversations only in your browser (localStorage). 
            Messages are sent to <span id="provider-name"><?php echo esc_html($settings['api_provider'] ?? 'Gemini'); ?></span> for processing, 
            but are not stored on our servers.
        </p>
        <div class="privacy-actions">
            <button onclick="acceptPrivacy()" class="btn btn-primary">I Understand</button>
            <a href="#" onclick="showPrivacyDetails(); return false;" class="privacy-link">Details</a>
        </div>
    </div>
</div>

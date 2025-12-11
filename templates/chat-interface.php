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
$height = $atts['height'] ?? '600px';
?>

<div class="tinkai-wrapper" style="width: <?php echo esc_attr($width); ?>; max-width: 800px; margin: 0 auto;" data-theme="<?php echo esc_attr($theme); ?>">
    <div class="container" style="height: <?php echo esc_attr($height); ?>;">
        <header>
            <div class="logo">TinkAi</div>
            <div class="payoff">The intelligence that keeps you thinking</div>
            <div class="header-actions">
                <?php if ($settings['enable_dark_mode'] ?? true): ?>
                <button id="tinkai-theme-toggle" class="theme-toggle" title="Cambia tema" aria-label="Toggle dark mode">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path class="sun-icon" d="M12 17C14.7614 17 17 14.7614 17 12C17 9.23858 14.7614 7 12 7C9.23858 7 7 9.23858 7 12C7 14.7614 9.23858 17 12 17Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path class="sun-icon" d="M12 1V3M12 21V23M4.22 4.22L5.64 5.64M18.36 18.36L19.78 19.78M1 12H3M21 12H23M4.22 19.78L5.64 18.36M18.36 5.64L19.78 4.22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path class="moon-icon" style="display: none;" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <?php endif; ?>
                
                <?php if ($settings['enable_feedback'] ?? true): ?>
                <a href="#" class="icon-btn tinkai-feedback-link" title="Analisi feedback" aria-label="Feedback analytics">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <?php endif; ?>
                
                <?php if ($settings['enable_metrics'] ?? true): ?>
                <a href="#" class="icon-btn tinkai-metrics-link" title="Visualizza metriche cognitive" aria-label="Visualizza metriche">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 3V21H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M18 9L13 14L9 10L3 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <?php endif; ?>
                
                <button onclick="tinkaiExportChat()" class="icon-btn" title="Esporta conversazione" aria-label="Esporta conversazione">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7 10L12 15L17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                
                <button onclick="tinkaiClearChat()" class="icon-btn" title="Nuova conversazione" aria-label="Nuova conversazione">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 6H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M19 6V20C19 21 18 22 17 22H7C6 22 5 21 5 20V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 6V4C8 3 9 2 10 2H14C15 2 16 3 16 4V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </header>

        <main class="chat-container" id="tinkai-chat-container">
            <div class="message bot-message">
                Ciao, sono TinkAi. Prima di darti risposte, ti aiuto a pensare meglio. Da dove partiamo?
            </div>
        </main>

        <footer class="input-area">
            <input type="text" id="tinkai-user-input" placeholder="Scrivi qui il tuo pensiero..." autocomplete="off">
            <button id="tinkai-send-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22 2L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <button id="tinkai-shortcuts-btn" class="icon-btn" title="Mostra shortcuts" aria-label="Mostra shortcuts tastiera" style="margin-left: 5px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="2" y="4" width="20" height="16" rx="2" stroke="currentColor" stroke-width="2"/>
                    <path d="M6 8H6.01M10 8H10.01M14 8H14.01M18 8H18.01M6 12H6.01M10 12H10.01M14 12H14.01M18 12H18.01M6 16H14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </footer>
    </div>
</div>

<!-- Privacy Banner (WordPress version) -->
<div id="tinkai-privacy-banner" class="privacy-banner" style="display: none;">
    <div class="privacy-content">
        <p>
            <strong>🔐 Privacy & Trasparenza</strong><br>
            TinkAi salva le conversazioni solo nel tuo browser (localStorage). 
            I messaggi vengono inviati a <span id="provider-name"><?php echo esc_html($settings['api_provider'] ?? 'Gemini'); ?></span> per elaborazione, 
            ma non vengono memorizzati sui nostri server.
        </p>
        <div class="privacy-actions">
            <button onclick="tinkaiAcceptPrivacy()" class="btn btn-primary">Ho capito</button>
            <a href="#" onclick="tinkaiShowPrivacyDetails(); return false;" class="privacy-link">Dettagli</a>
        </div>
    </div>
</div>

<script>
// Adatta le funzioni globali per WordPress
window.tinkaiClearChat = window.clearTinkAiChat;
window.tinkaiExportChat = window.exportTinkAiChat;
window.tinkaiAcceptPrivacy = window.acceptPrivacy;
window.tinkaiShowPrivacyDetails = window.showPrivacyDetails;
</script>

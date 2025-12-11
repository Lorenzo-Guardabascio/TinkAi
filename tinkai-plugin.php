<?php
/**
 * Plugin Name: TinkAi - Cognitive AI Assistant
 * Plugin URI: https://github.com/Lorenzo-Guardabascio/TinkAi
 * Description: The intelligence that keeps you thinking. Un assistente AI che stimola il pensiero critico invece di sostituirlo.
 * Version: 1.3.0
 * Author: Lorenzo Guardabascio
 * Author URI: https://github.com/Lorenzo-Guardabascio
 * License: MIT
 * Text Domain: tinkai
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TINKAI_VERSION', '1.3.0');
define('TINKAI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TINKAI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TINKAI_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main TinkAi Plugin Class
 */
class TinkAi_Plugin {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation/Deactivation
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Admin
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Frontend
        add_shortcode('tinkai', array($this, 'render_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        
        // AJAX endpoints
        add_action('wp_ajax_tinkai_proxy', array($this, 'ajax_proxy'));
        add_action('wp_ajax_nopriv_tinkai_proxy', array($this, 'ajax_proxy'));
        add_action('wp_ajax_tinkai_check_backend', array($this, 'ajax_check_backend'));
        add_action('wp_ajax_tinkai_get_metrics', array($this, 'ajax_get_metrics'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create default options
        $default_options = array(
            'api_provider' => 'gemini',
            'gemini_api_key' => '',
            'openai_api_key' => '',
            'nodejs_port' => '3000',
            'nodejs_host' => 'localhost',
            'enable_metrics' => true,
            'enable_feedback' => true,
            'enable_dark_mode' => true,
        );
        
        add_option('tinkai_settings', $default_options);
        
        // Create necessary database tables if needed
        $this->create_tables();
        
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table for storing metrics (optional, can use Node.js backend)
        $table_metrics = $wpdb->prefix . 'tinkai_metrics';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_metrics (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            session_id varchar(255) NOT NULL,
            metric_type varchar(50) NOT NULL,
            metric_value text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY session_id (session_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'TinkAi Settings',
            'TinkAi',
            'manage_options',
            'tinkai-settings',
            array($this, 'render_settings_page'),
            'dashicons-brain',
            30
        );
        
        add_submenu_page(
            'tinkai-settings',
            'TinkAi Metrics',
            'Metrics',
            'manage_options',
            'tinkai-metrics',
            array($this, 'render_metrics_page')
        );
        
        add_submenu_page(
            'tinkai-settings',
            'TinkAi Documentation',
            'Documentation',
            'manage_options',
            'tinkai-docs',
            array($this, 'render_docs_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('tinkai_settings_group', 'tinkai_settings', array($this, 'sanitize_settings'));
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        $sanitized['api_provider'] = sanitize_text_field($input['api_provider'] ?? 'gemini');
        $sanitized['gemini_api_key'] = sanitize_text_field($input['gemini_api_key'] ?? '');
        $sanitized['openai_api_key'] = sanitize_text_field($input['openai_api_key'] ?? '');
        $sanitized['nodejs_port'] = absint($input['nodejs_port'] ?? 3000);
        $sanitized['nodejs_host'] = sanitize_text_field($input['nodejs_host'] ?? 'localhost');
        $sanitized['enable_metrics'] = !empty($input['enable_metrics']);
        $sanitized['enable_feedback'] = !empty($input['enable_feedback']);
        $sanitized['enable_dark_mode'] = !empty($input['enable_dark_mode']);
        
        return $sanitized;
    }
    
    /**
     * Enqueue admin scripts
     */
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'tinkai') === false) {
            return;
        }
        
        wp_enqueue_style('tinkai-admin', TINKAI_PLUGIN_URL . 'assets/admin-style.css', array(), TINKAI_VERSION);
        wp_enqueue_script('tinkai-admin', TINKAI_PLUGIN_URL . 'assets/admin-script.js', array('jquery'), TINKAI_VERSION, true);
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Load on all pages - WordPress will only output if actually used
        $this->load_tinkai_assets();
    }
    
    /**
     * Load TinkAi assets (CSS/JS)
     */
    public function load_tinkai_assets() {
        // Enqueue existing TinkAi assets
        wp_enqueue_style('tinkai-style', TINKAI_PLUGIN_URL . 'assets/style.css', array(), TINKAI_VERSION);
        wp_enqueue_script('tinkai-script', TINKAI_PLUGIN_URL . 'assets/script.js', array(), TINKAI_VERSION, true);
        
        // Google Fonts
        wp_enqueue_style('tinkai-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&display=swap', array(), null);
        
        // Pass settings to JavaScript
        $settings = get_option('tinkai_settings', array());
        wp_localize_script('tinkai-script', 'tinkaiConfig', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tinkai_nonce'),
            'apiProvider' => $settings['api_provider'] ?? 'gemini',
            'enableMetrics' => $settings['enable_metrics'] ?? true,
            'enableFeedback' => $settings['enable_feedback'] ?? true,
            'enableDarkMode' => $settings['enable_dark_mode'] ?? true,
        ));
    }
    
    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        // NO need to force load here - wp_enqueue_scripts already ran
        
        $atts = shortcode_atts(array(
            'theme' => 'light',
            'width' => '100%',
            'height' => '600px',
        ), $atts, 'tinkai');
        
        ob_start();
        include TINKAI_PLUGIN_DIR . 'templates/chat-interface.php';
        return ob_get_clean();
    }
    
    /**
     * AJAX proxy to Node.js backend
     */
    public function ajax_proxy() {
        check_ajax_referer('tinkai_nonce', 'nonce');
        
        $settings = get_option('tinkai_settings', array());
        $nodejs_host = $settings['nodejs_host'] ?? 'localhost';
        $nodejs_port = $settings['nodejs_port'] ?? 3000;
        
        $endpoint = sanitize_text_field($_POST['endpoint'] ?? 'chat');
        $data = json_decode(stripslashes($_POST['data'] ?? '{}'), true);
        
        // Forward request to Node.js backend
        $url = "http://{$nodejs_host}:{$nodejs_port}/api/{$endpoint}";
        
        $response = wp_remote_post($url, array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode($data),
            'timeout' => 30,
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array(
                'message' => 'Failed to connect to TinkAi backend: ' . $response->get_error_message()
            ));
        }
        
        $body = wp_remote_retrieve_body($response);
        $status = wp_remote_retrieve_response_code($response);
        
        wp_send_json(json_decode($body, true), $status);
    }
    
    /**
     * AJAX check backend status
     */
    public function ajax_check_backend() {
        check_ajax_referer('tinkai_check_backend', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        $host = sanitize_text_field($_POST['host'] ?? 'localhost');
        $port = absint($_POST['port'] ?? 3000);
        
        $url = "http://{$host}:{$port}/api/health";
        
        $response = wp_remote_get($url, array(
            'timeout' => 5,
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array(
                'message' => 'Backend offline: ' . $response->get_error_message()
            ));
        }
        
        $status = wp_remote_retrieve_response_code($response);
        
        if ($status === 200) {
            wp_send_json_success(array('message' => 'Backend online'));
        } else {
            wp_send_json_error(array('message' => 'Backend returned status ' . $status));
        }
    }
    
    /**
     * AJAX get metrics from Node.js backend
     */
    public function ajax_get_metrics() {
        check_ajax_referer('tinkai_get_metrics', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        $settings = get_option('tinkai_settings', array());
        $nodejs_host = $settings['nodejs_host'] ?? 'localhost';
        $nodejs_port = $settings['nodejs_port'] ?? 3000;
        
        $url = "http://{$nodejs_host}:{$nodejs_port}/api/metrics";
        
        $response = wp_remote_get($url, array(
            'timeout' => 10,
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array(
                'message' => 'Failed to fetch metrics: ' . $response->get_error_message()
            ));
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($data) {
            wp_send_json_success($data);
        } else {
            wp_send_json_error(array('message' => 'Invalid metrics data'));
        }
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        include TINKAI_PLUGIN_DIR . 'templates/admin-settings.php';
    }
    
    /**
     * Render metrics page
     */
    public function render_metrics_page() {
        include TINKAI_PLUGIN_DIR . 'templates/admin-metrics.php';
    }
    
    /**
     * Render docs page
     */
    public function render_docs_page() {
        include TINKAI_PLUGIN_DIR . 'templates/admin-docs.php';
    }
}

// Initialize plugin
function tinkai_init() {
    return TinkAi_Plugin::get_instance();
}

// Start plugin
add_action('plugins_loaded', 'tinkai_init');

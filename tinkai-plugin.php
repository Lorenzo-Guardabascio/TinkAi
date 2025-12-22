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
        
        // Check and create tables on every init (safe, dbDelta checks if exists)
        add_action('init', array($this, 'ensure_tables_exist'));
        
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
        
        // New AJAX endpoints for testing platform
        add_action('wp_ajax_tinkai_submit_feedback', array($this, 'ajax_submit_feedback'));
        add_action('wp_ajax_nopriv_tinkai_submit_feedback', array($this, 'ajax_submit_feedback'));
        add_action('wp_ajax_tinkai_report_bug', array($this, 'ajax_report_bug'));
        add_action('wp_ajax_nopriv_tinkai_report_bug', array($this, 'ajax_report_bug'));
        add_action('wp_ajax_tinkai_check_quota', array($this, 'ajax_check_quota'));
        add_action('wp_ajax_nopriv_tinkai_check_quota', array($this, 'ajax_check_quota'));
        add_action('wp_ajax_tinkai_track_interaction', array($this, 'ajax_track_interaction'));
        add_action('wp_ajax_nopriv_tinkai_track_interaction', array($this, 'ajax_track_interaction'));
        add_action('wp_ajax_tinkai_save_conversation', array($this, 'ajax_save_conversation'));
        add_action('wp_ajax_nopriv_tinkai_save_conversation', array($this, 'ajax_save_conversation'));
        add_action('wp_ajax_tinkai_export_feedback', array($this, 'ajax_export_feedback'));
        add_action('wp_ajax_tinkai_get_analytics', array($this, 'ajax_get_analytics'));
        
        // Backend config endpoint (no auth required for local backend)
        add_action('wp_ajax_tinkai_get_config', array($this, 'ajax_get_config'));
        add_action('wp_ajax_nopriv_tinkai_get_config', array($this, 'ajax_get_config'));
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
     * Ensure tables exist (called on every init, safe with dbDelta)
     */
    public function ensure_tables_exist() {
        $this->create_tables();
    }
    
    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Table: Feedback dettagliato
        $table_feedback = $wpdb->prefix . 'tinkai_feedback';
        $sql_feedback = "CREATE TABLE IF NOT EXISTS $table_feedback (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            session_id varchar(255) NOT NULL,
            message_id varchar(255) NOT NULL,
            rating int(1) NOT NULL,
            what_worked text,
            what_failed text,
            suggestion text,
            user_question text,
            ai_response text,
            variant_id int(11) DEFAULT NULL,
            variant_name varchar(255) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY rating (rating),
            KEY variant_id (variant_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta($sql_feedback);
        
        // Table: User analytics & quotas
        $table_users = $wpdb->prefix . 'tinkai_users';
        $sql_users = "CREATE TABLE IF NOT EXISTS $table_users (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            role varchar(50) DEFAULT 'beta_tester',
            status varchar(20) DEFAULT 'active',
            daily_quota int DEFAULT 50,
            weekly_quota int DEFAULT 300,
            daily_used int DEFAULT 0,
            weekly_used int DEFAULT 0,
            total_interactions int DEFAULT 0,
            total_time_seconds int DEFAULT 0,
            last_interaction datetime,
            joined_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_id (user_id),
            KEY status (status),
            KEY role (role)
        ) $charset_collate;";
        dbDelta($sql_users);
        
        // Table: Conversazioni per analytics
        $table_conversations = $wpdb->prefix . 'tinkai_conversations';
        $sql_conversations = "CREATE TABLE IF NOT EXISTS $table_conversations (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            session_id varchar(255) NOT NULL,
            message_count int DEFAULT 0,
            duration_seconds int DEFAULT 0,
            topics text,
            system_prompt_variant varchar(50) DEFAULT 'default',
            avg_response_time float DEFAULT 0,
            started_at datetime DEFAULT CURRENT_TIMESTAMP,
            ended_at datetime,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY session_id (session_id),
            KEY system_prompt_variant (system_prompt_variant),
            KEY started_at (started_at)
        ) $charset_collate;";
        dbDelta($sql_conversations);
        
        // Table: A/B Testing System Prompts
        $table_prompts = $wpdb->prefix . 'tinkai_prompt_variants';
        $sql_prompts = "CREATE TABLE IF NOT EXISTS $table_prompts (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            variant_name varchar(100) NOT NULL,
            prompt_content text NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            weight int DEFAULT 50,
            total_uses int DEFAULT 0,
            avg_rating float DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY variant_name (variant_name),
            KEY is_active (is_active)
        ) $charset_collate;";
        dbDelta($sql_prompts);
        
        // Table: Bug Reports
        $table_bugs = $wpdb->prefix . 'tinkai_bug_reports';
        $sql_bugs = "CREATE TABLE IF NOT EXISTS $table_bugs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            session_id varchar(255),
            description text NOT NULL,
            console_logs text,
            user_agent text,
            url text,
            status varchar(20) DEFAULT 'open',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta($sql_bugs);
        
        // Insert default system prompt variant
        $existing = $wpdb->get_var("SELECT COUNT(*) FROM $table_prompts");
        if ($existing == 0) {
            $wpdb->insert($table_prompts, array(
                'variant_name' => 'default',
                'prompt_content' => file_get_contents(TINKAI_PLUGIN_DIR . 'backend/systemPrompt.js'),
                'is_active' => 1,
                'weight' => 100
            ));
        }
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
            'Testing Analytics',
            'Analytics',
            'manage_options',
            'tinkai-analytics',
            array($this, 'render_analytics_page')
        );
        
        add_submenu_page(
            'tinkai-settings',
            'User Management',
            'Users',
            'manage_options',
            'tinkai-users',
            array($this, 'render_users_page')
        );
        
        add_submenu_page(
            'tinkai-settings',
            'Feedback',
            'Feedback',
            'manage_options',
            'tinkai-feedback',
            array($this, 'render_feedback_page')
        );
        
        add_submenu_page(
            'tinkai-settings',
            'Bug Reports',
            'Bug Reports',
            'manage_options',
            'tinkai-bugs',
            array($this, 'render_bugs_page')
        );
        
        add_submenu_page(
            'tinkai-settings',
            'A/B Testing',
            'A/B Testing',
            'manage_options',
            'tinkai-ab-testing',
            array($this, 'render_ab_testing_page')
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
        $sanitized['ai_model'] = sanitize_text_field($input['ai_model'] ?? 'gemini-2.5-flash');
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
            'aiModel' => $settings['ai_model'] ?? 'gemini-2.5-flash',
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
        
        // A/B Testing: seleziona prompt variant casuale se disponibile
        if ($endpoint === 'chat') {
            $variant = $this->select_prompt_variant();
            if ($variant) {
                $data['systemPromptOverride'] = $variant['prompt_content'];
                $data['variantId'] = $variant['id'];
                $data['variantName'] = $variant['variant_name'];
            }
        }
        
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
        
        // Se c'è un variant, incrementa il counter
        if (isset($variant) && $variant && $status === 200) {
            $this->track_variant_usage($variant['id']);
        }
        
        $result = json_decode($body, true);
        
        // Aggiungi info variant alla risposta per tracking frontend
        if (isset($variant) && $variant) {
            $result['_variant'] = array(
                'id' => $variant['id'],
                'name' => $variant['variant_name']
            );
        }
        
        wp_send_json($result, $status);
    }
    
    /**
     * Seleziona un prompt variant casuale basato sui pesi
     */
    private function select_prompt_variant() {
        global $wpdb;
        $table = $wpdb->prefix . 'tinkai_prompt_variants';
        
        // Get active variants
        $variants = $wpdb->get_results(
            "SELECT * FROM $table WHERE is_active = 1",
            ARRAY_A
        );
        
        if (empty($variants)) {
            return null; // Usa prompt di default
        }
        
        // Weighted random selection
        $totalWeight = array_sum(array_column($variants, 'weight'));
        $random = mt_rand(1, $totalWeight);
        
        $cumulativeWeight = 0;
        foreach ($variants as $variant) {
            $cumulativeWeight += $variant['weight'];
            if ($random <= $cumulativeWeight) {
                return $variant;
            }
        }
        
        return $variants[0]; // Fallback
    }
    
    /**
     * Incrementa contatore utilizzi variant
     */
    private function track_variant_usage($variant_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'tinkai_prompt_variants';
        
        $wpdb->query($wpdb->prepare(
            "UPDATE $table SET total_uses = total_uses + 1 WHERE id = %d",
            $variant_id
        ));
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
    
    /**
     * Render analytics page
     */
    public function render_analytics_page() {
        include TINKAI_PLUGIN_DIR . 'templates/admin-analytics.php';
    }
    
    /**
     * Render users management page
     */
    public function render_users_page() {
        include TINKAI_PLUGIN_DIR . 'templates/admin-users.php';
    }
    
    /**
     * Render feedback page
     */
    public function render_feedback_page() {
        include TINKAI_PLUGIN_DIR . 'templates/admin-feedback.php';
    }
    
    /**
     * Render bugs page
     */
    public function render_bugs_page() {
        include TINKAI_PLUGIN_DIR . 'templates/admin-bugs.php';
    }
    
    /**
     * Render A/B testing page
     */
    public function render_ab_testing_page() {
        include TINKAI_PLUGIN_DIR . 'templates/admin-ab-testing.php';
    }
    
    /**
     * AJAX: Submit detailed feedback
     */
    public function ajax_submit_feedback() {
        check_ajax_referer('tinkai_nonce', 'nonce');
        
        global $wpdb;
        $table = $wpdb->prefix . 'tinkai_feedback';
        
        $user_id = get_current_user_id();
        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        $message_id = sanitize_text_field($_POST['message_id'] ?? '');
        $rating = absint($_POST['rating'] ?? 0);
        $what_worked = sanitize_textarea_field($_POST['what_worked'] ?? '');
        $what_failed = sanitize_textarea_field($_POST['what_failed'] ?? '');
        $suggestion = sanitize_textarea_field($_POST['suggestion'] ?? '');
        $user_question = sanitize_textarea_field($_POST['user_question'] ?? '');
        $ai_response = sanitize_textarea_field($_POST['ai_response'] ?? '');
        $variant_id = isset($_POST['variant_id']) ? absint($_POST['variant_id']) : null;
        $variant_name = sanitize_text_field($_POST['variant_name'] ?? '');
        
        $result = $wpdb->insert($table, array(
            'user_id' => $user_id ?: null,
            'session_id' => $session_id,
            'message_id' => $message_id,
            'rating' => $rating,
            'what_worked' => $what_worked,
            'what_failed' => $what_failed,
            'suggestion' => $suggestion,
            'user_question' => $user_question,
            'ai_response' => $ai_response,
            'variant_id' => $variant_id,
            'variant_name' => $variant_name
        ));
        
        if ($result) {
            wp_send_json_success(array('message' => 'Feedback saved successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to save feedback'));
        }
    }
    
    /**
     * AJAX: Report bug
     */
    public function ajax_report_bug() {
        check_ajax_referer('tinkai_nonce', 'nonce');
        
        global $wpdb;
        $table = $wpdb->prefix . 'tinkai_bug_reports';
        
        $user_id = get_current_user_id();
        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        $description = sanitize_textarea_field($_POST['description'] ?? '');
        $console_logs = sanitize_textarea_field($_POST['console_logs'] ?? '');
        $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');
        $url = sanitize_text_field($_POST['url'] ?? '');
        
        $result = $wpdb->insert($table, array(
            'user_id' => $user_id ?: null,
            'session_id' => $session_id,
            'description' => $description,
            'console_logs' => $console_logs,
            'user_agent' => $user_agent,
            'url' => $url,
            'status' => 'open'
        ));
        
        if ($result) {
            wp_send_json_success(array('message' => 'Bug report submitted'));
        } else {
            wp_send_json_error(array('message' => 'Failed to submit bug report'));
        }
    }
    
    /**
     * AJAX: Check user quota
     */
    public function ajax_check_quota() {
        check_ajax_referer('tinkai_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            // Guest user - use default limits
            wp_send_json_success(array(
                'daily_quota' => 20,
                'daily_used' => 0,
                'weekly_quota' => 100,
                'weekly_used' => 0,
                'can_interact' => true
            ));
            return;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'tinkai_users';
        
        $user_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d", $user_id
        ), ARRAY_A);
        
        if (!$user_data) {
            // Get WordPress user role
            $wp_user = get_userdata($user_id);
            $wp_role = !empty($wp_user->roles) ? $wp_user->roles[0] : 'subscriber';
            
            // Get default quotas based on role
            $quotas = $this->get_role_quotas($wp_role);
            
            // Create new user record
            $wpdb->insert($table, array(
                'user_id' => $user_id,
                'role' => $wp_role,
                'status' => 'active',
                'daily_quota' => $quotas['daily'],
                'weekly_quota' => $quotas['weekly']
            ));
            $user_data = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE user_id = %d", $user_id
            ), ARRAY_A);
        }
        
        // Reset counters if needed (daily/weekly)
        $this->reset_quotas_if_needed($user_id);
        
        // Refresh data after reset
        $user_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d", $user_id
        ), ARRAY_A);
        
        $daily_exceeded = $user_data['daily_used'] >= $user_data['daily_quota'];
        $weekly_exceeded = $user_data['weekly_used'] >= $user_data['weekly_quota'];
        $can_interact = ($user_data['status'] === 'active') && !$daily_exceeded && !$weekly_exceeded;
        
        // Calcola tempo rimanente per il reset
        $last_interaction = $user_data['last_interaction'] ? strtotime($user_data['last_interaction']) : time();
        $now = time();
        
        // Reset giornaliero: 24 ore dall'ultima interazione
        $daily_reset_time = $last_interaction + 86400;
        $hours_until_daily_reset = max(0, ceil(($daily_reset_time - $now) / 3600));
        
        // Reset settimanale: 7 giorni dall'ultima interazione
        $weekly_reset_time = $last_interaction + 604800;
        $days_until_weekly_reset = max(0, ceil(($weekly_reset_time - $now) / 86400));
        
        // Create appropriate message
        $quota_message = '';
        if ($daily_exceeded) {
            $quota_message = sprintf(
            '⏰ You have reached the daily limit of %d uses. The quota will reset in %d %s. If needed, please contact the administrators.',
            $user_data['daily_quota'],
            $hours_until_daily_reset,
            $hours_until_daily_reset == 1 ? 'hour' : 'hours'
            );
        } elseif ($weekly_exceeded) {
            $quota_message = sprintf(
            '📅 You have reached the weekly limit of %d uses. The quota will reset in %d %s. If needed, please contact the administrators.',
            $user_data['weekly_quota'],
            $days_until_weekly_reset,
            $days_until_weekly_reset == 1 ? 'day' : 'days'
            );
        }
        
        wp_send_json_success(array(
            'daily_quota' => (int)$user_data['daily_quota'],
            'daily_used' => (int)$user_data['daily_used'],
            'weekly_quota' => (int)$user_data['weekly_quota'],
            'weekly_used' => (int)$user_data['weekly_used'],
            'can_interact' => $can_interact,
            'status' => $user_data['status'],
            'quota_exceeded' => $daily_exceeded || $weekly_exceeded,
            'quota_message' => $quota_message,
            'hours_until_daily_reset' => $hours_until_daily_reset,
            'days_until_weekly_reset' => $days_until_weekly_reset
        ));
    }
    
    /**
     * AJAX: Track interaction (increment quotas)
     */
    public function ajax_track_interaction() {
        check_ajax_referer('tinkai_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_success(array('tracked' => false));
            return;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'tinkai_users';
        
        // Ensure user exists in database
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE user_id = %d", $user_id
        ));
        
        if (!$exists) {
            // Get WordPress user role
            $wp_user = get_userdata($user_id);
            $wp_role = !empty($wp_user->roles) ? $wp_user->roles[0] : 'subscriber';
            
            // Get default quotas based on role
            $quotas = $this->get_role_quotas($wp_role);
            
            $wpdb->insert($table, array(
                'user_id' => $user_id,
                'role' => $wp_role,
                'status' => 'active',
                'daily_quota' => $quotas['daily'],
                'weekly_quota' => $quotas['weekly'],
                'daily_used' => 1,
                'weekly_used' => 1,
                'total_interactions' => 1,
                'last_interaction' => current_time('mysql'),
                'joined_at' => current_time('mysql')
            ));
        } else {
            $wpdb->query($wpdb->prepare(
                "UPDATE $table SET 
                    daily_used = daily_used + 1,
                    weekly_used = weekly_used + 1,
                    total_interactions = total_interactions + 1,
                    last_interaction = NOW()
                WHERE user_id = %d",
                $user_id
            ));
        }
        
        wp_send_json_success(array('tracked' => true));
    }
    
    /**
     * AJAX: Save conversation to database
     */
    public function ajax_save_conversation() {
        check_ajax_referer('tinkai_nonce', 'nonce');
        
        global $wpdb;
        $table = $wpdb->prefix . 'tinkai_conversations';
        
        $user_id = get_current_user_id();
        $session_id = sanitize_text_field($_POST['session_id']);
        $message_count = intval($_POST['message_count']);
        $duration = intval($_POST['duration_seconds']);
        $topics = sanitize_text_field($_POST['topics'] ?? '');
        $variant = sanitize_text_field($_POST['system_prompt_variant'] ?? 'default');
        
        // Calcola tempo medio di risposta (approssimato)
        $avg_response_time = $duration > 0 && $message_count > 0 
            ? round($duration / ($message_count / 2), 2) 
            : null;
        
        // Check if session exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE session_id = %s",
            $session_id
        ));
        
        if ($existing) {
            // Update existing session
            $wpdb->update(
                $table,
                array(
                    'message_count' => $message_count,
                    'duration_seconds' => $duration,
                    'topics' => $topics,
                    'system_prompt_variant' => $variant,
                    'avg_response_time' => $avg_response_time
                ),
                array('session_id' => $session_id)
            );
        } else {
            // Insert new session
            $wpdb->insert(
                $table,
                array(
                    'user_id' => $user_id ?: null,
                    'session_id' => $session_id,
                    'message_count' => $message_count,
                    'duration_seconds' => $duration,
                    'topics' => $topics,
                    'system_prompt_variant' => $variant,
                    'avg_response_time' => $avg_response_time,
                    'started_at' => current_time('mysql')
                )
            );
        }
        
        wp_send_json_success(array('saved' => true));
    }
    
    /**
     * Get default quotas based on WordPress role
     */
    private function get_role_quotas($role) {
        $quotas = array(
            'administrator' => array('daily' => 500, 'weekly' => 3000),
            'editor' => array('daily' => 200, 'weekly' => 1000),
            'author' => array('daily' => 100, 'weekly' => 500),
            'contributor' => array('daily' => 50, 'weekly' => 300),
            'subscriber' => array('daily' => 10, 'weekly' => 150),
            'beta_tester' => array('daily' => 50, 'weekly' => 300),
        );
        
        return isset($quotas[$role]) ? $quotas[$role] : array('daily' => 10, 'weekly' => 150);
    }
    
    /**
     * Reset quotas if needed (daily/weekly)
     */
    private function reset_quotas_if_needed($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'tinkai_users';
        
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT last_interaction FROM $table WHERE user_id = %d", $user_id
        ));
        
        if (!$user || !$user->last_interaction) {
            return;
        }
        
        $last = strtotime($user->last_interaction);
        $now = time();
        
        // Reset daily if more than 24 hours
        if ($now - $last > 86400) {
            $wpdb->query($wpdb->prepare(
                "UPDATE $table SET daily_used = 0 WHERE user_id = %d", $user_id
            ));
        }
        
        // Reset weekly if more than 7 days
        if ($now - $last > 604800) {
            $wpdb->query($wpdb->prepare(
                "UPDATE $table SET weekly_used = 0 WHERE user_id = %d", $user_id
            ));
        }
    }
    
    /**
     * AJAX: Export feedback to CSV (admin only)
     */
    public function ajax_export_feedback() {
        check_ajax_referer('tinkai_export', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'tinkai_feedback';
        
        $feedback = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC", ARRAY_A);
        
        if (empty($feedback)) {
            wp_send_json_error(array('message' => 'No feedback data'));
        }
        
        // Generate CSV
        $csv = "ID,User ID,Session ID,Rating,What Worked,What Failed,Suggestion,Created At\n";
        foreach ($feedback as $row) {
            $csv .= sprintf(
                "%d,%s,%s,%d,\"%s\",\"%s\",\"%s\",%s\n",
                $row['id'],
                $row['user_id'] ?: 'Guest',
                $row['session_id'],
                $row['rating'],
                str_replace('"', '""', $row['what_worked']),
                str_replace('"', '""', $row['what_failed']),
                str_replace('"', '""', $row['suggestion']),
                $row['created_at']
            );
        }
        
        wp_send_json_success(array('csv' => $csv));
    }
    
    /**
     * AJAX: Get advanced analytics
     */
    public function ajax_get_analytics() {
        check_ajax_referer('tinkai_analytics', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        global $wpdb;
        
        // Get feedback stats
        $feedback_table = $wpdb->prefix . 'tinkai_feedback';
        $feedback_stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total,
                AVG(rating) as avg_rating,
                COUNT(DISTINCT user_id) as unique_users
            FROM $feedback_table
        ", ARRAY_A);
        
        // Get user stats
        $users_table = $wpdb->prefix . 'tinkai_users';
        $user_stats = $wpdb->get_results("
            SELECT 
                status,
                COUNT(*) as count,
                SUM(total_interactions) as total_interactions,
                AVG(total_interactions) as avg_interactions
            FROM $users_table
            GROUP BY status
        ", ARRAY_A);
        
        // Get top users
        $top_users = $wpdb->get_results("
            SELECT 
                u.user_id,
                u.total_interactions,
                u.joined_at,
                u.last_interaction,
                w.display_name
            FROM $users_table u
            LEFT JOIN {$wpdb->users} w ON u.user_id = w.ID
            ORDER BY u.total_interactions DESC
            LIMIT 10
        ", ARRAY_A);
        
        // Get bug reports count
        $bugs_table = $wpdb->prefix . 'tinkai_bug_reports';
        $bug_stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved
            FROM $bugs_table
        ", ARRAY_A);
        
        wp_send_json_success(array(
            'feedback' => $feedback_stats,
            'users' => $user_stats,
            'top_users' => $top_users,
            'bugs' => $bug_stats
        ));
    }
    
    /**
     * AJAX: Get configuration for Node.js backend
     * Provides API keys and settings from WordPress to the backend server
     */
    public function ajax_get_config() {
        // Verifica che la richiesta provenga da localhost/server locale
        $remote_addr = $_SERVER['REMOTE_ADDR'] ?? '';
        $allowed_ips = array('127.0.0.1', '::1', 'localhost');
        
        // Per sicurezza, accetta solo richieste locali
        if (!in_array($remote_addr, $allowed_ips) && !filter_var($remote_addr, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            // Se non è localhost e non è IP privato, blocca
            // Commenta questa riga se il backend gira su server separato
            // wp_send_json_error(array('message' => 'Access denied'));
        }
        
        $settings = get_option('tinkai_settings', array());
        
        // Prepara la configurazione per il backend
        $config = array(
            'AI_PROVIDER' => $settings['api_provider'] ?? 'gemini',
            'AI_MODEL' => $settings['ai_model'] ?? 'gemini-2.5-flash',
            'OPENAI_API_KEY' => $settings['openai_api_key'] ?? '',
            'GEMINI_API_KEY' => $settings['gemini_api_key'] ?? '',
            'PORT' => $settings['nodejs_port'] ?? 3000
        );
        
        wp_send_json_success($config);
    }
}

// Initialize plugin
function tinkai_init() {
    return TinkAi_Plugin::get_instance();
}

// Start plugin
add_action('plugins_loaded', 'tinkai_init');

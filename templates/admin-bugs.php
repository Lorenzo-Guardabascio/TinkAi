<?php
/**
 * Template: Admin Bug Reports
 */

if (!defined('ABSPATH')) exit;

global $wpdb;
$table = $wpdb->prefix . 'tinkai_bug_reports';

// Handle status change
if (isset($_POST['bug_id']) && isset($_POST['new_status']) && current_user_can('manage_options')) {
    check_admin_referer('tinkai_bug_action');
    
    $wpdb->update($table,
        array('status' => sanitize_text_field($_POST['new_status'])),
        array('id' => intval($_POST['bug_id']))
    );
    
    echo '<div class="notice notice-success"><p>Bug status updated!</p></div>';
}

// Filter
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';

// Get bugs
$where = $status_filter !== 'all' ? $wpdb->prepare("WHERE status = %s", $status_filter) : "";
$bugs = $wpdb->get_results("
    SELECT b.*, u.display_name, u.user_email
    FROM $table b
    LEFT JOIN {$wpdb->users} u ON b.user_id = u.ID
    $where
    ORDER BY b.created_at DESC
    LIMIT 100
", ARRAY_A);

// Stats
$total_bugs = $wpdb->get_var("SELECT COUNT(*) FROM $table");
$open_bugs = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'open'");
$in_progress = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'in_progress'");
$resolved_bugs = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'resolved'");
?>

<div class="wrap">
    <h1>🐛 Bug Reports</h1>
    
    <!-- Stats -->
    <div class="bug-stats">
        <div class="stat-card">
            <h3>Total Reports</h3>
            <div class="stat-value"><?php echo $total_bugs; ?></div>
        </div>
        
        <div class="stat-card status-open">
            <h3>Open</h3>
            <div class="stat-value"><?php echo $open_bugs; ?></div>
        </div>
        
        <div class="stat-card status-in-progress">
            <h3>In Progress</h3>
            <div class="stat-value"><?php echo $in_progress; ?></div>
        </div>
        
        <div class="stat-card status-resolved">
            <h3>Resolved</h3>
            <div class="stat-value"><?php echo $resolved_bugs; ?></div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <select id="bug-status-filter">
                <option value="all" <?php selected($status_filter, 'all'); ?>>All Statuses</option>
                <option value="open" <?php selected($status_filter, 'open'); ?>>Open</option>
                <option value="in_progress" <?php selected($status_filter, 'in_progress'); ?>>In Progress</option>
                <option value="resolved" <?php selected($status_filter, 'resolved'); ?>>Resolved</option>
            </select>
        </div>
    </div>
    
    <!-- Bugs List -->
    <div class="bug-list">
        <?php if (empty($bugs)): ?>
            <div class="no-bugs">
                <p>✨ No bug reports found!</p>
                <p class="description">Gli utenti possono segnalare bug dalla chat usando l'icona 🐛 nel menu.</p>
            </div>
        <?php else: ?>
            <?php foreach ($bugs as $bug): ?>
                <div class="bug-card status-<?php echo $bug['status']; ?>">
                    <div class="bug-header">
                        <div class="bug-meta">
                            <span class="bug-id">#<?php echo $bug['id']; ?></span>
                            <span class="bug-status badge-<?php echo $bug['status']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $bug['status'])); ?>
                            </span>
                            <span class="bug-date">
                                <?php echo human_time_diff(strtotime($bug['created_at']), current_time('timestamp')); ?> ago
                            </span>
                        </div>
                        
                        <div class="bug-actions">
                            <form method="post" style="display: inline;">
                                <?php wp_nonce_field('tinkai_bug_action'); ?>
                                <input type="hidden" name="bug_id" value="<?php echo $bug['id']; ?>">
                                
                                <select name="new_status" onchange="this.form.submit()">
                                    <option value="">Change Status...</option>
                                    <option value="open">Open</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="resolved">Resolved</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    
                    <div class="bug-body">
                        <div class="bug-user">
                            <strong>👤 Reporter:</strong> 
                            <?php echo esc_html($bug['display_name'] ?: 'Anonymous'); ?>
                            <?php if ($bug['user_email']): ?>
                                <a href="mailto:<?php echo esc_attr($bug['user_email']); ?>">
                                    <?php echo esc_html($bug['user_email']); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="bug-description">
                            <strong>📝 Description:</strong>
                            <p><?php echo nl2br(esc_html($bug['description'])); ?></p>
                        </div>
                        
                        <?php if ($bug['console_logs']): ?>
                            <details class="bug-console">
                                <summary><strong>🖥️ Console Logs</strong></summary>
                                <pre><?php echo esc_html($bug['console_logs']); ?></pre>
                            </details>
                        <?php endif; ?>
                        
                        <div class="bug-technical">
                            <div class="tech-info">
                                <strong>🌐 URL:</strong> 
                                <a href="<?php echo esc_url($bug['url']); ?>" target="_blank">
                                    <?php echo esc_html($bug['url']); ?>
                                </a>
                            </div>
                            
                            <div class="tech-info">
                                <strong>💻 User Agent:</strong> 
                                <small><?php echo esc_html($bug['user_agent']); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.bug-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.stat-card {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    text-align: center;
}

.stat-card.status-open { border-left: 4px solid #dc3545; }
.stat-card.status-in-progress { border-left: 4px solid #ffc107; }
.stat-card.status-resolved { border-left: 4px solid #28a745; }

.stat-card h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #646970;
}

.stat-value {
    font-size: 32px;
    font-weight: bold;
    color: #2271b1;
}

.bug-list {
    margin-top: 20px;
}

.bug-card {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border-left: 4px solid #ccc;
}

.bug-card.status-open { border-left-color: #dc3545; }
.bug-card.status-in_progress { border-left-color: #ffc107; }
.bug-card.status-resolved { border-left-color: #28a745; }

.bug-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.bug-meta {
    display: flex;
    gap: 10px;
    align-items: center;
}

.bug-id {
    font-weight: bold;
    color: #646970;
}

.bug-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.badge-open { background: #f8d7da; color: #721c24; }
.badge-in_progress { background: #fff3cd; color: #856404; }
.badge-resolved { background: #d4edda; color: #155724; }

.bug-date {
    color: #646970;
    font-size: 13px;
}

.bug-body {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.bug-user, .bug-description, .tech-info {
    line-height: 1.6;
}

.bug-description p {
    margin: 5px 0 0 0;
    color: #333;
}

.bug-console {
    background: #f5f5f5;
    padding: 10px;
    border-radius: 4px;
}

.bug-console pre {
    margin: 10px 0 0 0;
    background: #2d2d2d;
    color: #f8f8f2;
    padding: 15px;
    border-radius: 4px;
    overflow-x: auto;
    font-size: 12px;
}

.bug-technical {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding-top: 10px;
    border-top: 1px solid #eee;
}

.no-bugs {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: 8px;
}

.no-bugs p {
    font-size: 18px;
    color: #646970;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('#bug-status-filter').on('change', function() {
        var status = $(this).val();
        var url = new URL(window.location);
        if (status === 'all') {
            url.searchParams.delete('status');
        } else {
            url.searchParams.set('status', status);
        }
        window.location = url;
    });
});
</script>

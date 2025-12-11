<?php
/**
 * Template: Admin Feedback Management
 */

if (!defined('ABSPATH')) exit;

global $wpdb;
$table = $wpdb->prefix . 'tinkai_feedback';

// Handle actions
if (isset($_GET['action']) && $_GET['action'] === 'export' && current_user_can('manage_options')) {
    $feedback = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC", ARRAY_A);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=tinkai-feedback-' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'User ID', 'Rating', 'What Worked', 'What Failed', 'Suggestion', 'Question', 'AI Response', 'Created At']);
    
    foreach ($feedback as $row) {
        fputcsv($output, [
            $row['id'],
            $row['user_id'] ?: 'Guest',
            $row['rating'],
            $row['what_worked'],
            $row['what_failed'],
            $row['suggestion'],
            substr($row['user_question'], 0, 100),
            substr($row['ai_response'], 0, 100),
            $row['created_at']
        ]);
    }
    
    fclose($output);
    exit;
}

// Get feedback with pagination
$per_page = 20;
$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($page - 1) * $per_page;

$total = $wpdb->get_var("SELECT COUNT(*) FROM $table");
$feedback = $wpdb->get_results($wpdb->prepare(
    "SELECT f.*, u.display_name 
    FROM $table f
    LEFT JOIN {$wpdb->users} u ON f.user_id = u.ID
    ORDER BY f.created_at DESC
    LIMIT %d OFFSET %d",
    $per_page, $offset
), ARRAY_A);

$total_pages = ceil($total / $per_page);

// Get stats
$stats = $wpdb->get_row("
    SELECT 
        COUNT(*) as total,
        AVG(rating) as avg_rating,
        COUNT(DISTINCT user_id) as unique_users,
        SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive,
        SUM(CASE WHEN rating <= 2 THEN 1 ELSE 0 END) as negative
    FROM $table
", ARRAY_A);
?>

<div class="wrap">
    <h1>💬 User Feedback</h1>
    
    <!-- Stats Cards -->
    <div class="feedback-stats">
        <div class="stat-card">
            <h3>Total Feedback</h3>
            <div class="stat-value"><?php echo number_format($stats['total']); ?></div>
        </div>
        
        <div class="stat-card">
            <h3>Average Rating</h3>
            <div class="stat-value">
                <?php echo number_format($stats['avg_rating'], 1); ?> ⭐
            </div>
        </div>
        
        <div class="stat-card">
            <h3>Positive Feedback</h3>
            <div class="stat-value">
                <?php echo number_format(($stats['positive'] / max(1, $stats['total'])) * 100, 1); ?>%
            </div>
        </div>
        
        <div class="stat-card">
            <h3>Unique Users</h3>
            <div class="stat-value"><?php echo number_format($stats['unique_users']); ?></div>
        </div>
    </div>
    
    <!-- Actions -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <a href="?page=tinkai-feedback&action=export" class="button">
                📥 Export to CSV
            </a>
        </div>
    </div>
    
    <!-- Feedback Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 50px;">ID</th>
                <th>User</th>
                <th style="width: 80px;">Rating</th>
                <th>What Worked</th>
                <th>What Failed</th>
                <th>Suggestion</th>
                <th style="width: 150px;">Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($feedback)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px;">
                        No feedback yet. Encourage users to rate their interactions!
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($feedback as $item): ?>
                    <tr>
                        <td><?php echo $item['id']; ?></td>
                        <td>
                            <?php echo $item['display_name'] ?: 'Guest'; ?>
                            <?php if ($item['user_id']): ?>
                                <br><small>ID: <?php echo $item['user_id']; ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $stars = str_repeat('⭐', $item['rating']);
                            $empty = str_repeat('☆', 5 - $item['rating']);
                            echo $stars . $empty;
                            ?>
                        </td>
                        <td><?php echo esc_html(substr($item['what_worked'], 0, 100)); ?></td>
                        <td><?php echo esc_html(substr($item['what_failed'], 0, 100)); ?></td>
                        <td><?php echo esc_html(substr($item['suggestion'], 0, 100)); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($item['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <?php
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total' => $total_pages,
                    'current' => $page
                ));
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.feedback-stats {
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

.stat-card h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #646970;
    font-weight: 600;
}

.stat-value {
    font-size: 32px;
    font-weight: bold;
    color: #2271b1;
}
</style>

<?php
/**
 * Template: Admin Analytics Dashboard
 */

if (!defined('ABSPATH')) exit;

global $wpdb;

// Get analytics data
$conversations_table = $wpdb->prefix . 'tinkai_conversations';
$users_table = $wpdb->prefix . 'tinkai_users';
$feedback_table = $wpdb->prefix . 'tinkai_feedback';

// Conversation length distribution
$conv_lengths = $wpdb->get_results("
    SELECT 
        CASE 
            WHEN message_count BETWEEN 1 AND 3 THEN '1-3'
            WHEN message_count BETWEEN 4 AND 7 THEN '4-7'
            WHEN message_count BETWEEN 8 AND 15 THEN '8-15'
            ELSE '16+'
        END as length_range,
        COUNT(*) as count
    FROM $conversations_table
    GROUP BY length_range
", ARRAY_A);

// Response time by hour
$response_times = $wpdb->get_results("
    SELECT 
        HOUR(created_at) as hour,
        AVG(avg_response_time) as avg_time
    FROM $conversations_table
    WHERE avg_response_time IS NOT NULL
    GROUP BY hour
    ORDER BY hour
", ARRAY_A);

// User retention (returning users)
$retention_data = $wpdb->get_results("
    SELECT 
        DATEDIFF(CURDATE(), DATE(joined_at)) as days_since_join,
        COUNT(*) as users,
        SUM(CASE WHEN last_interaction >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) as active_1d,
        SUM(CASE WHEN last_interaction >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as active_7d,
        SUM(CASE WHEN last_interaction >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as active_30d
    FROM $users_table
    GROUP BY days_since_join
    HAVING days_since_join IN (1, 7, 30)
", ARRAY_A);

// Topic extraction (most common keywords in user questions)
$topics = $wpdb->get_results("
    SELECT 
        LOWER(SUBSTRING_INDEX(SUBSTRING_INDEX(user_question, ' ', numbers.n), ' ', -1)) as word,
        COUNT(*) as frequency
    FROM $feedback_table
    CROSS JOIN (
        SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
    ) numbers
    WHERE user_question IS NOT NULL
        AND CHAR_LENGTH(user_question) - CHAR_LENGTH(REPLACE(user_question, ' ', '')) >= numbers.n - 1
        AND LENGTH(SUBSTRING_INDEX(SUBSTRING_INDEX(user_question, ' ', numbers.n), ' ', -1)) > 3
    GROUP BY word
    ORDER BY frequency DESC
    LIMIT 20
", ARRAY_A);

// Overall stats
$total_conversations = $wpdb->get_var("SELECT COUNT(*) FROM $conversations_table");
$avg_session_duration = $wpdb->get_var("SELECT AVG(duration_seconds) FROM $conversations_table");
$total_users = $wpdb->get_var("SELECT COUNT(*) FROM $users_table");
$avg_messages_per_conv = $wpdb->get_var("SELECT AVG(message_count) FROM $conversations_table");
?>

<div class="wrap">
    <h1>📊 Analytics Dashboard</h1>
    
    <!-- Summary Stats -->
    <div class="analytics-stats">
        <div class="stat-card">
            <h3>Total Conversations</h3>
            <div class="stat-value"><?php echo number_format($total_conversations); ?></div>
        </div>
        
        <div class="stat-card">
            <h3>Avg Session Duration</h3>
            <div class="stat-value">
                <?php echo $avg_session_duration ? round($avg_session_duration / 60) . 'm' : 'N/A'; ?>
            </div>
        </div>
        
        <div class="stat-card">
            <h3>Total Users</h3>
            <div class="stat-value"><?php echo number_format($total_users); ?></div>
        </div>
        
        <div class="stat-card">
            <h3>Avg Messages/Conv</h3>
            <div class="stat-value">
                <?php echo $avg_messages_per_conv ? round($avg_messages_per_conv, 1) : 'N/A'; ?>
            </div>
        </div>
    </div>
    
    <!-- Conversation Length Distribution -->
    <div class="analytics-section">
        <h2>📏 Conversation Length Distribution</h2>
        <div class="chart-container">
            <?php if (!empty($conv_lengths)): ?>
                <?php 
                $max_count = max(array_column($conv_lengths, 'count'));
                foreach ($conv_lengths as $range): 
                    $percentage = ($range['count'] / $max_count) * 100;
                ?>
                    <div class="bar-chart-row">
                        <div class="bar-label"><?php echo $range['length_range']; ?> messages</div>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: <?php echo $percentage; ?>%"></div>
                        </div>
                        <div class="bar-value"><?php echo number_format($range['count']); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-data">No conversation data yet</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Response Time by Hour -->
    <div class="analytics-section">
        <h2>⏱️ Average Response Time by Hour</h2>
        <div class="chart-container">
            <?php if (!empty($response_times)): ?>
                <?php 
                $max_time = max(array_column($response_times, 'avg_time'));
                foreach ($response_times as $time): 
                    $percentage = $max_time > 0 ? ($time['avg_time'] / $max_time) * 100 : 0;
                    $hour_label = sprintf('%02d:00', $time['hour']);
                ?>
                    <div class="bar-chart-row">
                        <div class="bar-label"><?php echo $hour_label; ?></div>
                        <div class="bar-container">
                            <div class="bar-fill bar-blue" style="width: <?php echo $percentage; ?>%"></div>
                        </div>
                        <div class="bar-value"><?php echo round($time['avg_time'], 1); ?>s</div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-data">No response time data yet</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- User Retention -->
    <div class="analytics-section">
        <h2>👥 User Retention Rates</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Cohort</th>
                    <th>Total Users</th>
                    <th>Active (1 Day)</th>
                    <th>Active (7 Days)</th>
                    <th>Active (30 Days)</th>
                    <th>Retention %</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($retention_data)): ?>
                    <?php foreach ($retention_data as $cohort): 
                        $retention = $cohort['users'] > 0 ? round(($cohort['active_30d'] / $cohort['users']) * 100, 1) : 0;
                    ?>
                        <tr>
                            <td>Day <?php echo $cohort['days_since_join']; ?></td>
                            <td><?php echo number_format($cohort['users']); ?></td>
                            <td><?php echo number_format($cohort['active_1d']); ?></td>
                            <td><?php echo number_format($cohort['active_7d']); ?></td>
                            <td><?php echo number_format($cohort['active_30d']); ?></td>
                            <td>
                                <strong><?php echo $retention; ?>%</strong>
                                <div class="retention-bar">
                                    <div class="retention-fill" style="width: <?php echo $retention; ?>%"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No retention data available yet</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Topic Frequency -->
    <div class="analytics-section">
        <h2>🏷️ Most Common Topics (Keywords)</h2>
        <div class="topics-grid">
            <?php if (!empty($topics)): ?>
                <?php 
                $max_freq = max(array_column($topics, 'frequency'));
                foreach ($topics as $topic): 
                    $size = 12 + (($topic['frequency'] / $max_freq) * 20);
                ?>
                    <div class="topic-tag" style="font-size: <?php echo $size; ?>px;">
                        <?php echo esc_html($topic['word']); ?>
                        <span class="topic-count">(<?php echo $topic['frequency']; ?>)</span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-data">No topic data yet - users need to provide feedback</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.analytics-stats {
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
}

.stat-value {
    font-size: 32px;
    font-weight: bold;
    color: #2271b1;
}

.analytics-section {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin: 20px 0;
}

.analytics-section h2 {
    margin-top: 0;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f1;
}

.chart-container {
    margin-top: 20px;
}

.bar-chart-row {
    display: grid;
    grid-template-columns: 100px 1fr 80px;
    gap: 15px;
    align-items: center;
    margin-bottom: 12px;
}

.bar-label {
    font-size: 13px;
    font-weight: 600;
    color: #646970;
}

.bar-container {
    background: #e5e5e5;
    height: 30px;
    border-radius: 4px;
    overflow: hidden;
}

.bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #2271b1, #135e96);
    transition: width 0.3s;
}

.bar-fill.bar-blue {
    background: linear-gradient(90deg, #0073aa, #005177);
}

.bar-value {
    text-align: right;
    font-weight: 600;
    color: #333;
}

.retention-bar {
    width: 100%;
    height: 6px;
    background: #e0e0e0;
    border-radius: 3px;
    margin-top: 4px;
    overflow: hidden;
}

.retention-fill {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #1e7e34);
}

.topics-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.topic-tag {
    display: inline-block;
    padding: 8px 15px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px;
    font-weight: 600;
    transition: transform 0.2s;
}

.topic-tag:hover {
    transform: scale(1.1);
}

.topic-count {
    font-size: 0.8em;
    opacity: 0.8;
    margin-left: 4px;
}

.no-data {
    text-align: center;
    padding: 40px;
    color: #646970;
    font-size: 14px;
}
</style>

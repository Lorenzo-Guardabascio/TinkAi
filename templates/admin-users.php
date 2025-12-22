<?php
/**
 * Template: Admin User Management
 */

if (!defined('ABSPATH')) exit;

global $wpdb;
$table = $wpdb->prefix . 'tinkai_users';

// Handle user actions
if (isset($_POST['action']) && current_user_can('manage_options')) {
    check_admin_referer('tinkai_user_action');
    
    $user_id = intval($_POST['user_id']);
    
    // Check if user exists in tinkai_users table
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table WHERE user_id = %d", $user_id
    ));
    
    switch ($_POST['action']) {
        case 'update_quota':
            if ($exists) {
                $wpdb->update($table, 
                    array(
                        'daily_quota' => intval($_POST['daily_quota']),
                        'weekly_quota' => intval($_POST['weekly_quota'])
                    ),
                    array('user_id' => $user_id)
                );
            } else {
                // Create new record
                $wp_user = get_userdata($user_id);
                $wp_role = !empty($wp_user->roles) ? $wp_user->roles[0] : 'subscriber';
                
                $wpdb->insert($table, array(
                    'user_id' => $user_id,
                    'role' => $wp_role,
                    'status' => 'active',
                    'daily_quota' => intval($_POST['daily_quota']),
                    'weekly_quota' => intval($_POST['weekly_quota'])
                ));
            }
            echo '<div class="notice notice-success"><p>Quotas updated successfully!</p></div>';
            break;
            
        case 'change_status':
            if ($exists) {
                $wpdb->update($table,
                    array('status' => sanitize_text_field($_POST['status'])),
                    array('user_id' => $user_id)
                );
            } else {
                // Create new record
                $wp_user = get_userdata($user_id);
                $wp_role = !empty($wp_user->roles) ? $wp_user->roles[0] : 'subscriber';
                
                $wpdb->insert($table, array(
                    'user_id' => $user_id,
                    'role' => $wp_role,
                    'status' => sanitize_text_field($_POST['status'])
                ));
            }
            echo '<div class="notice notice-success"><p>User status updated!</p></div>';
            break;
            
        case 'reset_quotas':
            if ($exists) {
                $wpdb->update($table,
                    array('daily_used' => 0, 'weekly_used' => 0),
                    array('user_id' => $user_id)
                );
                echo '<div class="notice notice-success"><p>Quotas reset!</p></div>';
            }
            break;
    }
}

// Get role labels for display
function get_role_display_name($role) {
    $roles = array(
        'administrator' => 'Administrator',
        'editor' => 'Editor',
        'author' => 'Author',
        'contributor' => 'Contributor',
        'subscriber' => 'Subscriber',
        'beta_tester' => 'Beta Tester'
    );
    return isset($roles[$role]) ? $roles[$role] : ucfirst($role);
}

// Get default quotas based on WordPress role
function get_default_quotas($role) {
    $quotas = array(
        'administrator' => array('daily' => 500, 'weekly' => 3000),
        'editor' => array('daily' => 200, 'weekly' => 1000),
        'author' => array('daily' => 100, 'weekly' => 500),
        'contributor' => array('daily' => 50, 'weekly' => 300),
        'subscriber' => array('daily' => 10, 'weekly' => 150),
    );
    return isset($quotas[$role]) ? $quotas[$role] : array('daily' => 10, 'weekly' => 150);
}

// Get ALL WordPress users with their TinkAi data (if exists)
$users = $wpdb->get_results("
    SELECT 
        u.ID as user_id,
        u.display_name,
        u.user_email,
        t.id,
        t.role,
        t.status,
        t.daily_quota,
        t.weekly_quota,
        t.daily_used,
        t.weekly_used,
        t.total_interactions,
        t.last_interaction
    FROM {$wpdb->users} u
    LEFT JOIN $table t ON u.ID = t.user_id
    ORDER BY t.total_interactions DESC, u.display_name ASC
", ARRAY_A);

// Get WordPress user meta for roles
foreach ($users as &$user) {
    $wp_user = get_userdata($user['user_id']);
    $wp_role = !empty($wp_user->roles) ? $wp_user->roles[0] : 'subscriber';
    
    // If user has no TinkAi record, set defaults based on WP role
    if (!$user['id']) {
        $quotas = get_default_quotas($wp_role);
        $user['role'] = $wp_role;
        $user['status'] = 'active';
        $user['daily_quota'] = $quotas['daily'];
        $user['weekly_quota'] = $quotas['weekly'];
        $user['daily_used'] = 0;
        $user['weekly_used'] = 0;
        $user['total_interactions'] = 0;
        $user['last_interaction'] = null;
    }
}
unset($user);

// Stats
$total_users = count($users);
$active_users = count(array_filter($users, fn($u) => $u['status'] === 'active'));
$total_interactions = array_sum(array_column($users, 'total_interactions'));

// Get stats by role
$subscribers = count(array_filter($users, fn($u) => $u['role'] === 'subscriber'));
$contributors = count(array_filter($users, fn($u) => $u['role'] === 'contributor'));
$other_roles = $total_users - $subscribers - $contributors;
?>

<div class="wrap">
    <h1>👥 User Management</h1>
    
    <div class="notice notice-info">
        <p><strong>ℹ️ Default Quota by Role:</strong></p>
        <ul style="margin: 10px 0; padding-left: 20px;">
            <li><strong>Subscriber:</strong> 10 messages/day, 150/week</li>
            <li><strong>Contributor:</strong> 50 messages/day, 300/week</li>
            <li><strong>Author:</strong> 100 messages/day, 500/week</li>
            <li><strong>Editor:</strong> 200 messages/day, 1000/week</li>
            <li><strong>Administrator:</strong> 500 messages/day, 3000/week</li>
        </ul>
        <p><em>Note: You can customize quotas for individual users below.</em></p>
    </div>
    
    <!-- Stats -->
    <div class="user-stats">
        <div class="stat-card">
            <h3>Total Users</h3>
            <div class="stat-value"><?php echo $total_users; ?></div>
        </div>
        
        <div class="stat-card">
            <h3>Active Users</h3>
            <div class="stat-value"><?php echo $active_users; ?></div>
        </div>
        
        <div class="stat-card">
            <h3>Subscribers</h3>
            <div class="stat-value"><?php echo $subscribers; ?></div>
            <small style="color: #646970;">10 msg/day</small>
        </div>
        
        <div class="stat-card">
            <h3>Contributors+</h3>
            <div class="stat-value"><?php echo $contributors; ?></div>
            <small style="color: #646970;">50 msg/day</small>
        </div>
        
        <div class="stat-card">
            <h3>Total Interactions</h3>
            <div class="stat-value"><?php echo number_format($total_interactions); ?></div>
        </div>
        
        <div class="stat-card">
            <h3>Avg per User</h3>
            <div class="stat-value">
                <?php echo $total_users ? round($total_interactions / $total_users) : 0; ?>
            </div>
        </div>
    </div>
    
    <!-- Users Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>User</th>
                <th>Email</th>
                <th>Status</th>
                <th>Role</th>
                <th>Daily Quota</th>
                <th>Weekly Quota</th>
                <th>Total Interactions</th>
                <th>Last Active</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 40px;">
                        No users registered yet.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($user['display_name']); ?></strong>
                            <br><small>ID: <?php echo $user['user_id']; ?></small>
                            <?php if (!$user['id']): ?>
                                <br><small style="color: #999;">⚪ Never used chat</small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($user['user_email']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $user['status']; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="role-badge role-<?php echo $user['role']; ?>">
                                <?php echo get_role_display_name($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo $user['daily_used']; ?> / <?php echo $user['daily_quota']; ?>
                            <div class="quota-bar">
                                <div class="quota-fill" style="width: <?php echo min(100, ($user['daily_used'] / max(1, $user['daily_quota'])) * 100); ?>%"></div>
                            </div>
                        </td>
                        <td>
                            <?php echo $user['weekly_used']; ?> / <?php echo $user['weekly_quota']; ?>
                            <div class="quota-bar">
                                <div class="quota-fill" style="width: <?php echo min(100, ($user['weekly_used'] / max(1, $user['weekly_quota'])) * 100); ?>%"></div>
                            </div>
                        </td>
                        <td><strong><?php echo number_format($user['total_interactions']); ?></strong></td>
                        <td>
                            <?php 
                            if ($user['last_interaction']) {
                                echo human_time_diff(strtotime($user['last_interaction']), current_time('timestamp')) . ' ago';
                            } else {
                                echo 'Never';
                            }
                            ?>
                        </td>
                        <td>
                            <button class="button button-small edit-user-btn" data-user='<?php echo json_encode($user); ?>'>
                                ✏️ Edit
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Edit User Modal -->
<div id="edit-user-modal" style="display: none;">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <h2>Edit User</h2>
        <form method="post" id="edit-user-form">
            <?php wp_nonce_field('tinkai_user_action'); ?>
            <input type="hidden" name="user_id" id="edit-user-id">
            
            <table class="form-table">
                <tr>
                    <th>Daily Quota</th>
                    <td><input type="number" name="daily_quota" id="edit-daily-quota" min="0" class="regular-text"></td>
                </tr>
                <tr>
                    <th>Weekly Quota</th>
                    <td><input type="number" name="weekly_quota" id="edit-weekly-quota" min="0" class="regular-text"></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <select name="status" id="edit-status">
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                            <option value="blacklisted">Blacklisted</option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" name="action" value="update_quota" class="button button-primary">Save Changes</button>
                <button type="submit" name="action" value="reset_quotas" class="button">Reset Quotas</button>
                <button type="button" class="button close-modal-btn">Cancel</button>
            </p>
        </form>
    </div>
</div>

<style>
.user-stats {
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

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.status-active { background: #d4edda; color: #155724; }
.status-suspended { background: #fff3cd; color: #856404; }
.status-blacklisted { background: #f8d7da; color: #721c24; }

.role-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.role-administrator { background: #e3f2fd; color: #1565c0; }
.role-editor { background: #f3e5f5; color: #6a1b9a; }
.role-author { background: #fff3e0; color: #e65100; }
.role-contributor { background: #e8f5e9; color: #2e7d32; }
.role-subscriber { background: #fce4ec; color: #c2185b; }
.role-beta_tester { background: #e0f2f1; color: #00695c; }

.quota-bar {
    width: 100%;
    height: 6px;
    background: #e0e0e0;
    border-radius: 3px;
    margin-top: 4px;
    overflow: hidden;
}

.quota-fill {
    height: 100%;
    background: linear-gradient(90deg, #2271b1, #135e96);
    transition: width 0.3s;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 99999;
}

.modal-content {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    z-index: 100000;
    min-width: 500px;
}

.modal-content h2 {
    margin-top: 0;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('.edit-user-btn').on('click', function() {
        var user = $(this).data('user');
        $('#edit-user-id').val(user.user_id);
        $('#edit-daily-quota').val(user.daily_quota);
        $('#edit-weekly-quota').val(user.weekly_quota);
        $('#edit-status').val(user.status);
        $('#edit-user-modal').show();
    });
    
    $('.close-modal-btn, .modal-overlay').on('click', function() {
        $('#edit-user-modal').hide();
    });
});
</script>

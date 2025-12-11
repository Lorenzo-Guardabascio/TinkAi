<?php
/**
 * Template: Admin A/B Testing
 */

if (!defined('ABSPATH')) exit;

global $wpdb;
$table = $wpdb->prefix . 'tinkai_prompt_variants';

// Handle variant actions
if (isset($_POST['action']) && current_user_can('manage_options')) {
    check_admin_referer('tinkai_variant_action');
    
    switch ($_POST['action']) {
        case 'add_variant':
            $wpdb->insert($table, array(
                'variant_name' => sanitize_text_field($_POST['variant_name']),
                'prompt_content' => wp_kses_post($_POST['prompt_content']),
                'is_active' => 1,
                'weight' => intval($_POST['weight'])
            ));
            echo '<div class="notice notice-success"><p>Variant created successfully!</p></div>';
            break;
            
        case 'update_variant':
            $wpdb->update($table, array(
                'variant_name' => sanitize_text_field($_POST['variant_name']),
                'prompt_content' => wp_kses_post($_POST['prompt_content']),
                'weight' => intval($_POST['weight']),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ), array('id' => intval($_POST['variant_id'])));
            echo '<div class="notice notice-success"><p>Variant updated!</p></div>';
            break;
            
        case 'delete_variant':
            $wpdb->delete($table, array('id' => intval($_POST['variant_id'])));
            echo '<div class="notice notice-success"><p>Variant deleted!</p></div>';
            break;
    }
}

// Get variants
$variants = $wpdb->get_results("SELECT * FROM $table ORDER BY total_uses DESC", ARRAY_A);

// Calculate stats
$total_tests = array_sum(array_column($variants, 'total_uses'));
$active_variants = count(array_filter($variants, fn($v) => $v['is_active']));
?>

<div class="wrap">
    <h1>🧪 A/B Testing - Prompt Variants</h1>
    
    <!-- Info Box -->
    <div class="notice notice-info">
        <p>
            <strong>How it works:</strong> Create multiple prompt variants to test which performs better. 
            Each variant has a weight (higher = more likely to be shown). 
            The system tracks usage and ratings for each variant.
        </p>
    </div>
    
    <!-- Stats -->
    <div class="ab-stats">
        <div class="stat-card">
            <h3>Total Variants</h3>
            <div class="stat-value"><?php echo count($variants); ?></div>
        </div>
        
        <div class="stat-card">
            <h3>Active Variants</h3>
            <div class="stat-value"><?php echo $active_variants; ?></div>
        </div>
        
        <div class="stat-card">
            <h3>Total Tests</h3>
            <div class="stat-value"><?php echo number_format($total_tests); ?></div>
        </div>
    </div>
    
    <!-- Add New Variant -->
    <div class="variant-form-container">
        <h2>➕ Create New Variant</h2>
        <form method="post" class="variant-form">
            <?php wp_nonce_field('tinkai_variant_action'); ?>
            <input type="hidden" name="action" value="add_variant">
            
            <table class="form-table">
                <tr>
                    <th><label for="variant_name">Variant Name</label></th>
                    <td>
                        <input type="text" name="variant_name" id="variant_name" class="regular-text" required>
                        <p class="description">e.g., "Friendly Tone", "Professional Style", "Concise Responses"</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="prompt_content">System Prompt</label></th>
                    <td>
                        <textarea name="prompt_content" id="prompt_content" rows="10" class="large-text" required></textarea>
                        <p class="description">The system prompt that will be used for this variant</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="weight">Weight</label></th>
                    <td>
                        <input type="number" name="weight" id="weight" value="1" min="1" max="100" class="small-text">
                        <p class="description">Higher weight = more likely to be selected (1-100)</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" class="button button-primary">Create Variant</button>
            </p>
        </form>
    </div>
    
    <!-- Variants List -->
    <h2>📊 Existing Variants</h2>
    
    <?php if (empty($variants)): ?>
        <div class="no-variants">
            <p>No variants created yet. Create your first variant above!</p>
        </div>
    <?php else: ?>
        <div class="variants-grid">
            <?php foreach ($variants as $variant): ?>
                <?php
                $avg_rating = $variant['avg_rating'] ? round($variant['avg_rating'], 1) : 0;
                $usage_percent = $total_tests > 0 ? round(($variant['total_uses'] / $total_tests) * 100, 1) : 0;
                ?>
                
                <div class="variant-card <?php echo $variant['is_active'] ? 'active' : 'inactive'; ?>">
                    <div class="variant-header">
                        <h3><?php echo esc_html($variant['variant_name']); ?></h3>
                        <span class="variant-status">
                            <?php echo $variant['is_active'] ? '✅ Active' : '⏸️ Inactive'; ?>
                        </span>
                    </div>
                    
                    <div class="variant-stats-mini">
                        <div class="mini-stat">
                            <span class="label">Uses:</span>
                            <strong><?php echo number_format($variant['total_uses']); ?></strong>
                            <span class="percent">(<?php echo $usage_percent; ?>%)</span>
                        </div>
                        <div class="mini-stat">
                            <span class="label">Avg Rating:</span>
                            <strong><?php echo $avg_rating; ?></strong>
                            <span class="stars"><?php echo str_repeat('⭐', round($avg_rating)); ?></span>
                        </div>
                        <div class="mini-stat">
                            <span class="label">Weight:</span>
                            <strong><?php echo $variant['weight']; ?></strong>
                        </div>
                    </div>
                    
                    <details class="variant-prompt">
                        <summary>View Prompt</summary>
                        <pre><?php echo esc_html($variant['prompt_content']); ?></pre>
                    </details>
                    
                    <div class="variant-actions">
                        <button class="button edit-variant-btn" data-variant='<?php echo json_encode($variant); ?>'>
                            ✏️ Edit
                        </button>
                        <form method="post" style="display: inline;" onsubmit="return confirm('Delete this variant?');">
                            <?php wp_nonce_field('tinkai_variant_action'); ?>
                            <input type="hidden" name="action" value="delete_variant">
                            <input type="hidden" name="variant_id" value="<?php echo $variant['id']; ?>">
                            <button type="submit" class="button button-link-delete">🗑️ Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Edit Variant Modal -->
<div id="edit-variant-modal" style="display: none;">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <h2>Edit Variant</h2>
        <form method="post">
            <?php wp_nonce_field('tinkai_variant_action'); ?>
            <input type="hidden" name="action" value="update_variant">
            <input type="hidden" name="variant_id" id="edit-variant-id">
            
            <table class="form-table">
                <tr>
                    <th><label>Variant Name</label></th>
                    <td><input type="text" name="variant_name" id="edit-variant-name" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label>System Prompt</label></th>
                    <td><textarea name="prompt_content" id="edit-prompt-content" rows="10" class="large-text" required></textarea></td>
                </tr>
                <tr>
                    <th><label>Weight</label></th>
                    <td><input type="number" name="weight" id="edit-weight" min="1" max="100" class="small-text"></td>
                </tr>
                <tr>
                    <th><label>Status</label></th>
                    <td>
                        <label>
                            <input type="checkbox" name="is_active" id="edit-is-active" value="1">
                            Active
                        </label>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" class="button button-primary">Save Changes</button>
                <button type="button" class="button close-modal-btn">Cancel</button>
            </p>
        </form>
    </div>
</div>

<style>
.ab-stats {
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

.stat-value {
    font-size: 32px;
    font-weight: bold;
    color: #2271b1;
}

.variant-form-container {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.variants-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.variant-card {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border-left: 4px solid #ccc;
}

.variant-card.active { border-left-color: #28a745; }
.variant-card.inactive { border-left-color: #6c757d; opacity: 0.7; }

.variant-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.variant-header h3 {
    margin: 0;
    font-size: 16px;
}

.variant-status {
    font-size: 12px;
    font-weight: 600;
}

.variant-stats-mini {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
}

.mini-stat {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.mini-stat .label {
    color: #646970;
}

.mini-stat .percent {
    color: #646970;
    font-size: 12px;
}

.variant-prompt {
    margin: 15px 0;
}

.variant-prompt pre {
    background: #f5f5f5;
    padding: 10px;
    border-radius: 4px;
    overflow-x: auto;
    font-size: 12px;
    margin-top: 10px;
}

.variant-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.no-variants {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: 8px;
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
    min-width: 600px;
    max-width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('.edit-variant-btn').on('click', function() {
        var variant = $(this).data('variant');
        $('#edit-variant-id').val(variant.id);
        $('#edit-variant-name').val(variant.variant_name);
        $('#edit-prompt-content').val(variant.prompt_content);
        $('#edit-weight').val(variant.weight);
        $('#edit-is-active').prop('checked', variant.is_active == 1);
        $('#edit-variant-modal').show();
    });
    
    $('.close-modal-btn, .modal-overlay').on('click', function() {
        $('#edit-variant-modal').hide();
    });
});
</script>

<?php
/**
 * Template: Admin Metrics Dashboard
 */

if (!defined('ABSPATH')) exit;

$settings = get_option('tinkai_settings', array());
$nodejs_host = $settings['nodejs_host'] ?? 'localhost';
$nodejs_port = $settings['nodejs_port'] ?? 3000;
?>

<div class="wrap">
    <h1>📊 Metriche Cognitive TinkAi</h1>
    
    <div class="tinkai-metrics-dashboard">
        
        <!-- TinkAi Score Card -->
        <div class="tinkai-card tinkai-score-card">
            <h2>🎯 TinkAi Score</h2>
            <div class="score-display" id="tinkai-score">
                <div class="score-value">--</div>
                <div class="score-label">Qualità Cognitiva</div>
            </div>
            <p class="description">
                Indice di qualità delle interazioni: quanto TinkAi stimola il pensiero critico.
            </p>
        </div>
        
        <!-- Metrics Grid -->
        <div class="metrics-grid">
            <div class="metric-card">
                <h3>💭 Riflessioni</h3>
                <div class="metric-value" id="reflective-count">--</div>
                <div class="metric-percentage" id="reflective-percentage">--%</div>
                <p>Risposte che stimolano riflessione</p>
            </div>
            
            <div class="metric-card">
                <h3>📝 Domande Dirette</h3>
                <div class="metric-value" id="direct-count">--</div>
                <div class="metric-percentage" id="direct-percentage">--%</div>
                <p>Domande con risposta diretta</p>
            </div>
            
            <div class="metric-card">
                <h3>📚 Interazioni Totali</h3>
                <div class="metric-value" id="total-count">--</div>
                <div class="metric-percentage">100%</div>
                <p>Tutte le conversazioni elaborate</p>
            </div>
        </div>
        
        <!-- Daily Statistics -->
        <div class="tinkai-card">
            <h2>📅 Statistiche Giornaliere (ultimi 7 giorni)</h2>
            <div id="daily-stats-container">
                <p class="loading-message">Caricamento statistiche...</p>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="tinkai-card">
            <h2>📈 Tendenze nel Tempo</h2>
            <div id="trends-chart-container">
                <canvas id="trends-chart"></canvas>
            </div>
        </div>
        
        <!-- Backend Status -->
        <div class="tinkai-card tinkai-status-card">
            <h3>🔌 Stato Backend Node.js</h3>
            <div id="backend-status">
                <span class="status-badge status-checking">⏳ Verifica in corso...</span>
            </div>
            <p class="description">
                Endpoint: <code id="backend-endpoint">http://<?php echo esc_html($nodejs_host); ?>:<?php echo esc_html($nodejs_port); ?></code>
            </p>
            <button type="button" class="button" id="refresh-metrics">🔄 Aggiorna Metriche</button>
        </div>
        
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    
    function loadMetrics() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tinkai_get_metrics',
                nonce: '<?php echo wp_create_nonce('tinkai_get_metrics'); ?>'
            },
            success: function(response) {
                if (response.success && response.data) {
                    updateMetricsDashboard(response.data);
                    $('#backend-status').html('<span class="status-badge status-online">✅ Online</span>');
                } else {
                    $('#backend-status').html('<span class="status-badge status-offline">❌ Offline</span>');
                }
            },
            error: function() {
                $('#backend-status').html('<span class="status-badge status-offline">❌ Errore di connessione</span>');
            }
        });
    }
    
    function updateMetricsDashboard(data) {
        var metrics = data.metrics || {};
        var dailyStats = data.dailyStats || {};
        
        // Update TinkAi Score
        var score = metrics.tinkaiScore || 0;
        $('#tinkai-score .score-value').text(score.toFixed(1));
        
        // Update metric cards
        $('#reflective-count').text(metrics.reflective || 0);
        $('#direct-count').text(metrics.direct || 0);
        $('#total-count').text(metrics.total || 0);
        
        var reflectivePerc = metrics.total > 0 ? ((metrics.reflective / metrics.total) * 100).toFixed(1) : 0;
        var directPerc = metrics.total > 0 ? ((metrics.direct / metrics.total) * 100).toFixed(1) : 0;
        
        $('#reflective-percentage').text(reflectivePerc + '%');
        $('#direct-percentage').text(directPerc + '%');
        
        // Update daily stats
        renderDailyStats(dailyStats);
    }
    
    function renderDailyStats(dailyStats) {
        if (Object.keys(dailyStats).length === 0) {
            $('#daily-stats-container').html('<p class="no-data">Nessun dato disponibile</p>');
            return;
        }
        
        var html = '<div class="daily-stats-grid">';
        var dates = Object.keys(dailyStats).sort().reverse().slice(0, 7);
        
        dates.forEach(function(date) {
            var stats = dailyStats[date];
            var totalInteractions = stats.interactions || 0;
            var reflective = stats.reflective || 0;
            var direct = stats.direct || 0;
            
            html += '<div class="daily-stat-card">';
            html += '<div class="stat-date">' + formatDate(date) + '</div>';
            html += '<div class="stat-value">' + totalInteractions + ' interazioni</div>';
            html += '<div class="stat-breakdown">';
            html += '<span class="stat-reflective">💭 ' + reflective + '</span>';
            html += '<span class="stat-direct">📝 ' + direct + '</span>';
            html += '</div>';
            html += '</div>';
        });
        
        html += '</div>';
        $('#daily-stats-container').html(html);
    }
    
    function formatDate(dateStr) {
        var parts = dateStr.split('-');
        return parts[2] + '/' + parts[1];
    }
    
    // Initial load
    loadMetrics();
    
    // Auto-refresh every 30 seconds
    setInterval(loadMetrics, 30000);
    
    // Manual refresh
    $('#refresh-metrics').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('⏳ Aggiornamento...');
        loadMetrics();
        setTimeout(function() {
            $btn.prop('disabled', false).text('🔄 Aggiorna Metriche');
        }, 2000);
    });
});
</script>

<style>
.tinkai-metrics-dashboard {
    max-width: 1200px;
}

.tinkai-card {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border-radius: 8px;
}

.tinkai-score-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-align: center;
}

.score-display {
    margin: 20px 0;
}

.score-value {
    font-size: 72px;
    font-weight: bold;
    line-height: 1;
}

.score-label {
    font-size: 18px;
    opacity: 0.9;
    margin-top: 10px;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.metric-card {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    text-align: center;
}

.metric-card h3 {
    margin: 0 0 15px 0;
    font-size: 16px;
    color: #666;
}

.metric-value {
    font-size: 48px;
    font-weight: bold;
    color: #2271b1;
    line-height: 1;
}

.metric-percentage {
    font-size: 20px;
    color: #666;
    margin-top: 10px;
}

.metric-card p {
    margin: 10px 0 0 0;
    font-size: 14px;
    color: #666;
}

.daily-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
}

.daily-stat-card {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 6px;
    border-left: 4px solid #2271b1;
}

.stat-date {
    font-weight: bold;
    color: #2271b1;
    margin-bottom: 8px;
}

.stat-value {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 8px;
}

.stat-breakdown {
    display: flex;
    gap: 10px;
    font-size: 13px;
    color: #666;
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 4px;
    font-weight: bold;
}

.status-online {
    background: #d4edda;
    color: #155724;
}

.status-offline {
    background: #f8d7da;
    color: #721c24;
}

.status-checking {
    background: #fff3cd;
    color: #856404;
}

.tinkai-status-card {
    border-left: 4px solid #2271b1;
}

.loading-message, .no-data {
    text-align: center;
    color: #666;
    padding: 20px;
}
</style>

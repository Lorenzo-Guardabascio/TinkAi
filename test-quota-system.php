<!-- Test Quota System - TinkAi Plugin -->
<!-- 
    Questo file può essere usato per testare il sistema di quota
    Aprirlo nel browser WordPress come admin per vedere le query SQL
-->

<?php
/**
 * Test Script per Sistema Quota TinkAi
 * 
 * ISTRUZIONI:
 * 1. Accedere a WordPress come amministratore
 * 2. Salvare questo file come test-quota-system.php nella root del plugin
 * 3. Accedere a: your-site.com/wp-content/plugins/tinkai-plugin/test-quota-system.php
 * 4. Eseguire i vari test
 * 
 * ATTENZIONE: Usare solo in ambiente di sviluppo!
 */

// Carica WordPress
require_once('../../../../../wp-load.php');

// Verifica permessi admin
if (!current_user_can('manage_options')) {
    die('⛔ Accesso negato. Solo amministratori possono accedere a questa pagina.');
}

global $wpdb;
$table = $wpdb->prefix . 'tinkai_users';
$user_id = get_current_user_id();

// Gestione azioni
$action = $_GET['action'] ?? '';
$message = '';

switch ($action) {
    case 'set_quota_exceeded':
        $wpdb->update(
            $table,
            array(
                'daily_used' => 50,
                'daily_quota' => 50,
                'weekly_used' => 300,
                'weekly_quota' => 300
            ),
            array('user_id' => $user_id)
        );
        $message = '✅ Quota impostata come raggiunta';
        break;
        
    case 'set_daily_exceeded':
        $wpdb->update(
            $table,
            array('daily_used' => 50, 'daily_quota' => 50),
            array('user_id' => $user_id)
        );
        $message = '✅ Quota giornaliera impostata come raggiunta';
        break;
        
    case 'set_weekly_exceeded':
        $wpdb->update(
            $table,
            array('weekly_used' => 300, 'weekly_quota' => 300),
            array('user_id' => $user_id)
        );
        $message = '✅ Quota settimanale impostata come raggiunta';
        break;
        
    case 'reset_all':
        $wpdb->update(
            $table,
            array('daily_used' => 0, 'weekly_used' => 0),
            array('user_id' => $user_id)
        );
        $message = '✅ Quote resettate a zero';
        break;
        
    case 'set_old_interaction':
        $wpdb->update(
            $table,
            array('last_interaction' => date('Y-m-d H:i:s', strtotime('-25 hours'))),
            array('user_id' => $user_id)
        );
        $message = '✅ Last interaction impostata a 25 ore fa (per testare reset automatico)';
        break;
        
    case 'create_user':
        // Verifica se l'utente esiste già
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE user_id = %d", $user_id
        ));
        
        if (!$exists) {
            $wpdb->insert($table, array(
                'user_id' => $user_id,
                'role' => 'beta_tester',
                'status' => 'active'
            ));
            $message = '✅ Record utente creato nel database';
        } else {
            $message = 'ℹ️ Record utente già esistente';
        }
        break;
}

// Ottieni stato attuale
$user_data = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $table WHERE user_id = %d", $user_id
), ARRAY_A);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Sistema Quota - TinkAi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 1rem;
        }
        
        .message {
            padding: 16px 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            font-weight: 600;
        }
        
        .warning {
            background: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section h2 {
            color: #555;
            margin-bottom: 20px;
            font-size: 1.3rem;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        
        .info-card label {
            display: block;
            font-weight: 600;
            color: #666;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .info-card .value {
            font-size: 1.5rem;
            color: #333;
            font-weight: 700;
        }
        
        .info-card.quota-ok .value {
            color: #28a745;
        }
        
        .info-card.quota-warning .value {
            color: #ffc107;
        }
        
        .info-card.quota-exceeded .value {
            color: #dc3545;
        }
        
        .buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .btn {
            padding: 14px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        .code-block {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .no-data {
            padding: 40px;
            text-align: center;
            color: #999;
            background: #f8f9fa;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Sistema Quota TinkAi</h1>
        <p class="subtitle">Pannello di test per sviluppatori</p>
        
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="warning">
            <strong>⚠️ ATTENZIONE:</strong> Questo strumento modifica direttamente il database.
            Usare solo in ambiente di sviluppo/test!
        </div>
        
        <?php if ($user_data): ?>
            
            <div class="section">
                <h2>📊 Stato Attuale Quota</h2>
                <div class="info-grid">
                    <div class="info-card <?php echo ($user_data['daily_used'] >= $user_data['daily_quota']) ? 'quota-exceeded' : 'quota-ok'; ?>">
                        <label>Quota Giornaliera</label>
                        <div class="value"><?php echo $user_data['daily_used']; ?> / <?php echo $user_data['daily_quota']; ?></div>
                    </div>
                    
                    <div class="info-card <?php echo ($user_data['weekly_used'] >= $user_data['weekly_quota']) ? 'quota-exceeded' : 'quota-ok'; ?>">
                        <label>Quota Settimanale</label>
                        <div class="value"><?php echo $user_data['weekly_used']; ?> / <?php echo $user_data['weekly_quota']; ?></div>
                    </div>
                    
                    <div class="info-card">
                        <label>Totale Interazioni</label>
                        <div class="value"><?php echo number_format($user_data['total_interactions']); ?></div>
                    </div>
                    
                    <div class="info-card">
                        <label>Ultima Interazione</label>
                        <div class="value" style="font-size: 1rem;">
                            <?php 
                            if ($user_data['last_interaction']) {
                                $time = strtotime($user_data['last_interaction']);
                                $diff = time() - $time;
                                $hours = floor($diff / 3600);
                                echo $hours . 'h fa';
                            } else {
                                echo 'Mai';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2>🔧 Azioni di Test</h2>
                <div class="buttons">
                    <a href="?action=set_quota_exceeded" class="btn btn-danger">
                        🚫 Imposta Quota Raggiunta
                    </a>
                    <a href="?action=set_daily_exceeded" class="btn btn-warning">
                        📅 Solo Quota Giornaliera
                    </a>
                    <a href="?action=set_weekly_exceeded" class="btn btn-warning">
                        📆 Solo Quota Settimanale
                    </a>
                    <a href="?action=reset_all" class="btn btn-success">
                        ♻️ Reset Tutte le Quote
                    </a>
                    <a href="?action=set_old_interaction" class="btn btn-info">
                        ⏰ Simula Vecchia Interazione
                    </a>
                    <a href="?" class="btn btn-primary">
                        🔄 Ricarica Stato
                    </a>
                </div>
            </div>
            
            <div class="section">
                <h2>💾 Dati Database</h2>
                <div class="code-block">
<?php echo json_encode($user_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?>
                </div>
            </div>
            
        <?php else: ?>
            
            <div class="no-data">
                <h3>📭 Nessun record trovato</h3>
                <p>Non esiste ancora un record per questo utente nel database.</p>
                <br>
                <a href="?action=create_user" class="btn btn-primary">
                    ➕ Crea Record Utente
                </a>
            </div>
            
        <?php endif; ?>
        
        <div class="section">
            <h2>📖 Comandi SQL Utili</h2>
            <div class="code-block">-- Visualizza tutti gli utenti
SELECT * FROM <?php echo $table; ?>;

-- Reset quota specifico utente
UPDATE <?php echo $table; ?> 
SET daily_used = 0, weekly_used = 0 
WHERE user_id = <?php echo $user_id; ?>;

-- Simula quota raggiunta
UPDATE <?php echo $table; ?> 
SET daily_used = daily_quota 
WHERE user_id = <?php echo $user_id; ?>;

-- Forza reset (24h fa)
UPDATE <?php echo $table; ?> 
SET last_interaction = DATE_SUB(NOW(), INTERVAL 25 HOUR)
WHERE user_id = <?php echo $user_id; ?>;</div>
        </div>
        
    </div>
</body>
</html>

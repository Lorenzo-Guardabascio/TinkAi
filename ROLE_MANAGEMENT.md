# Gestione Ruoli e Quote TinkAi

## Panoramica
Il plugin TinkAi ora gestisce automaticamente le quote basate sui ruoli WordPress nativi, inclusi i sottoscrittori (subscribers).

## Ruoli WordPress Supportati

Tutti i ruoli WordPress standard sono ora supportati con quote predefinite:

### Quote Predefinite per Ruolo

| Ruolo WordPress | Quota Giornaliera | Quota Settimanale |
|----------------|-------------------|-------------------|
| **Subscriber** | 30 messaggi | 150 messaggi |
| **Contributor** | 50 messaggi | 300 messaggi |
| **Author** | 100 messaggi | 500 messaggi |
| **Editor** | 200 messaggi | 1000 messaggi |
| **Administrator** | 500 messaggi | 3000 messaggi |

### Utenti Non Loggati (Guest)
- **Quota Giornaliera**: 20 messaggi
- **Quota Settimanale**: 100 messaggi

## Funzionalità

### 1. Rilevamento Automatico del Ruolo
Quando un utente interagisce con la chat per la prima volta:
- Il sistema rileva automaticamente il suo ruolo WordPress
- Crea un record nel database con le quote appropriate
- Applica le limitazioni in base al ruolo

### 2. Gestione Quote Personalizzate
Gli amministratori possono:
- Visualizzare tutti gli utenti nella sezione **TinkAi → Users**
- Modificare le quote per utenti specifici
- Resettare manualmente le quote
- Cambiare lo status utente (active, suspended, blacklisted)

### 3. Dashboard Utenti
La pagina di gestione utenti mostra:
- Statistiche generali (totale utenti, utenti attivi, interazioni totali)
- Statistiche per ruolo (subscribers, contributors, altri ruoli)
- Tabella dettagliata con:
  - Nome utente ed email
  - Ruolo WordPress con badge colorato
  - Status (attivo/sospeso/bloccato)
  - Utilizzo quota giornaliera e settimanale (con barra di progressione)
  - Totale interazioni
  - Ultima attività

### 4. Sistema di Reset Automatico
Le quote si resettano automaticamente:
- **Quota Giornaliera**: dopo 24 ore dall'ultima interazione
- **Quota Settimanale**: dopo 7 giorni dall'ultima interazione

## Come Funziona

### Per gli Utenti
1. Un utente accede al sito WordPress
2. Utilizza lo shortcode `[tinkai]` per accedere alla chat
3. Il sistema verifica automaticamente:
   - Se è loggato o meno
   - Il suo ruolo WordPress
   - Le sue quote rimanenti
4. Se le quote sono esaurite, l'utente riceve un messaggio informativo

### Per gli Amministratori
1. Accedi a **TinkAi → Users** nel menu WordPress
2. Visualizza tutti gli utenti che hanno usato la chat
3. Clicca su "Edit" per modificare le quote di un utente specifico
4. Modifica:
   - Quota giornaliera
   - Quota settimanale
   - Status dell'utente
5. Salva le modifiche o resetta le quote

## Badge Ruoli

I ruoli vengono visualizzati con colori distintivi:
- 🔵 **Administrator** - Blu
- 🟣 **Editor** - Viola
- 🟠 **Author** - Arancione
- 🟢 **Contributor** - Verde
- 🔴 **Subscriber** - Rosa
- 🟦 **Beta Tester** - Turchese

## Migrazione Utenti Esistenti

Gli utenti già registrati nel sistema con il vecchio ruolo "beta_tester" manterranno:
- Il loro ruolo attuale nel database TinkAi
- Le loro quote personalizzate (se modificate manualmente)
- Le loro statistiche di utilizzo

I nuovi utenti verranno automaticamente registrati con il loro ruolo WordPress reale.

## Personalizzazione Quote

Per modificare le quote predefinite, gli amministratori possono:

1. **Per un singolo utente**: Usa l'interfaccia di gestione utenti
2. **Per modificare i default globali**: Modifica il file `tinkai-plugin.php` nella funzione `get_role_quotas()`

```php
private function get_role_quotas($role) {
    $quotas = array(
        'administrator' => array('daily' => 500, 'weekly' => 3000),
        'editor' => array('daily' => 200, 'weekly' => 1000),
        'author' => array('daily' => 100, 'weekly' => 500),
        'contributor' => array('daily' => 50, 'weekly' => 300),
        'subscriber' => array('daily' => 30, 'weekly' => 150),
        'beta_tester' => array('daily' => 50, 'weekly' => 300),
    );
    
    return isset($quotas[$role]) ? $quotas[$role] : array('daily' => 30, 'weekly' => 150);
}
```

## Note Tecniche

- Il ruolo viene rilevato usando `get_userdata($user_id)->roles[0]`
- Se un utente ha più ruoli, viene usato il primo
- Il database memorizza il ruolo al momento della prima interazione
- Le modifiche ai ruoli WordPress non aggiornano automaticamente il database TinkAi (bisogna modificare manualmente)

## Sicurezza

- Tutti gli endpoint AJAX sono protetti con nonce
- Solo gli amministratori possono modificare quote e status utenti
- Gli utenti non loggati hanno accesso limitato (20 msg/giorno)
- Gli utenti bloccati (blacklisted) non possono inviare messaggi

## Troubleshooting

### Un subscriber non vede le sue quote
- Verifica che l'utente sia loggato in WordPress
- Controlla nella pagina **TinkAi → Users** se l'utente è registrato
- Se non appare, l'utente deve inviare almeno un messaggio per essere tracciato

### Le quote non si resettano
- Il reset avviene solo alla prossima interazione dell'utente
- Controlla che siano passate 24 ore (per daily) o 7 giorni (per weekly) dall'ultima interazione

### Un utente ha quote sbagliate
- Vai su **TinkAi → Users**
- Clicca "Edit" sull'utente
- Modifica manualmente le quote
- Salva le modifiche

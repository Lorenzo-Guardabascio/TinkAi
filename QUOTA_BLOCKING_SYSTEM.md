# Sistema di Blocco Quota TinkAi

## Descrizione

Il sistema di gestione delle quote in TinkAi blocca automaticamente l'utilizzo della chat quando l'utente raggiunge i limiti giornalieri o settimanali, mostrando un messaggio informativo chiaro e disabilitando l'input.

## Funzionalità Implementate

### 1. Controllo Quota al Caricamento
- La quota viene controllata automaticamente quando la chat si carica
- Se la quota è già raggiunta, l'input viene immediatamente disabilitato
- Viene mostrato un banner informativo permanente

### 2. Controllo Quota Prima dell'Invio
- Prima di ogni messaggio viene verificato lo stato della quota
- Se la quota è raggiunta, il messaggio non viene inviato
- Viene mostrato un toast di notifica

### 3. Controllo Quota Dopo Ogni Interazione
- Dopo ogni risposta ricevuta, la quota viene ricontrollata
- Se viene raggiunta durante la conversazione, l'input viene bloccato
- Il banner informativo appare automaticamente

### 4. Messaggi Informativi

Il sistema mostra messaggi diversi in base al tipo di limite raggiunto:

#### Limite Giornaliero Raggiunto
```
⏰ Hai raggiunto il limite giornaliero di X utilizzi. 
La quota si resetterà tra Y ore. 
In caso di necessità, contatta gli amministratori.
```

#### Limite Settimanale Raggiunto
```
📅 Hai raggiunto il limite settimanale di X utilizzi. 
La quota si resetterà tra Y giorni. 
In caso di necessità, contatta gli amministratori.
```

### 5. Blocco dell'Input

Quando la quota è raggiunta:
- ❌ L'input di testo viene disabilitato
- ❌ Il pulsante di invio viene disabilitato
- 🔒 Il placeholder cambia in "⛔ Quota raggiunta - Chat non disponibile"
- 🎨 L'input assume uno stile visivo disabilitato (grigio, cursore not-allowed)

## Configurazione Quote

### Limiti Predefiniti

#### Utenti Autenticati (Beta Testers)
- **Quota Giornaliera**: 50 utilizzi
- **Quota Settimanale**: 300 utilizzi

#### Utenti Guest (Non autenticati)
- **Quota Giornaliera**: 20 utilizzi
- **Quota Settimanale**: 100 utilizzi

### Reset Automatico

Le quote vengono automaticamente resettate:
- **Reset Giornaliero**: 24 ore dall'ultima interazione
- **Reset Settimanale**: 7 giorni dall'ultima interazione

## Implementazione Tecnica

### File Modificati

1. **tinkai-plugin.php**
   - `ajax_check_quota()`: Calcola quota rimanente e tempo di reset
   - `reset_quotas_if_needed()`: Resetta le quote se scadute

2. **assets/script.js**
   - `checkUserQuota()`: Controlla lo stato della quota
   - `handleSendMessage()`: Verifica quota prima dell'invio
   - `trackInteraction()`: Incrementa contatore e ricontrolla quota
   - `disableChatInput()`: Disabilita l'interfaccia
   - `showQuotaWarning()`: Mostra il banner informativo

3. **assets/style.css**
   - Stili per `.quota-exceeded-warning`
   - Stili per input disabilitato
   - Animazioni e responsive design

### Variabile di Stato

```javascript
let quotaStatus = {
    can_interact: true,      // L'utente può inviare messaggi
    quota_exceeded: false,   // La quota è stata raggiunta
    quota_message: ''        // Messaggio personalizzato da mostrare
};
```

### Flusso di Controllo

```
1. Caricamento Pagina
   ↓
2. checkUserQuota() → AJAX call
   ↓
3. Risposta con stato quota
   ↓
4. Se quota_exceeded = true:
   - showQuotaWarning()
   - disableChatInput()
   ↓
5. handleSendMessage():
   - Controlla quotaStatus.can_interact
   - Se false → blocca e mostra toast
   ↓
6. Dopo risposta AI:
   - trackInteraction() → incrementa contatore
   - checkUserQuota() → ricontrolla stato
```

## API Endpoint

### `tinkai_check_quota`

**Request:**
```javascript
{
    action: 'tinkai_check_quota',
    nonce: 'xxx'
}
```

**Response:**
```javascript
{
    success: true,
    data: {
        daily_quota: 50,
        daily_used: 45,
        weekly_quota: 300,
        weekly_used: 280,
        can_interact: true,
        status: 'active',
        quota_exceeded: false,
        quota_message: '',
        hours_until_daily_reset: 8,
        days_until_weekly_reset: 3
    }
}
```

## Testing

### Test Manuale

1. **Test Limite Raggiunto:**
   - Modificare `daily_used >= daily_quota` nel database
   - Ricaricare la pagina
   - Verificare che l'input sia disabilitato
   - Verificare il messaggio mostrato

2. **Test Durante Conversazione:**
   - Avviare conversazione
   - Modificare quota durante la conversazione
   - Inviare messaggio
   - Verificare blocco dopo risposta

3. **Test Reset:**
   - Modificare `last_interaction` a più di 24 ore fa
   - Verificare reset automatico quota giornaliera

### SQL per Test

```sql
-- Impostare quota raggiunta
UPDATE wp_tinkai_users 
SET daily_used = 50, daily_quota = 50 
WHERE user_id = 1;

-- Forzare reset (24+ ore fa)
UPDATE wp_tinkai_users 
SET last_interaction = DATE_SUB(NOW(), INTERVAL 25 HOUR)
WHERE user_id = 1;

-- Reset manuale
UPDATE wp_tinkai_users 
SET daily_used = 0, weekly_used = 0 
WHERE user_id = 1;
```

## Stile Visivo

Il banner di avviso quota ha:
- 🎨 Gradiente giallo/arancio per attirare l'attenzione
- 📐 Design responsive e centrato
- 🌙 Supporto tema scuro
- ✨ Animazione slide-down all'apparizione
- 📧 Sezione con info di contatto amministratori

## Supporto Multi-Lingua

Tutti i messaggi sono in italiano. Per internazionalizzazione futura:
- Usare `__()` per i messaggi PHP
- Creare oggetto traduzioni per JavaScript
- Aggiungere file `.pot` per traduzioni

## Note di Sicurezza

- ✅ Nonce verification su tutti gli endpoint AJAX
- ✅ Controllo lato server (non affidamento al solo frontend)
- ✅ Sanitizzazione input utente
- ✅ Prepared statements SQL per prevenire injection

## Changelog

### Versione 1.0 (16 Dicembre 2025)
- ✅ Implementazione sistema di blocco quota
- ✅ Messaggi informativi personalizzati
- ✅ Calcolo tempo di reset
- ✅ Disabilitazione completa input
- ✅ Stili CSS per warning e input disabilitato
- ✅ Controllo quota multi-livello (caricamento, pre-invio, post-risposta)

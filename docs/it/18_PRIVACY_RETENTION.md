# Privacy e retention

`PRIVACY_IT.md` nella root è la sintesi per l'utente. Questo documento descrive il contratto implementativo corrente.

## Mappa dei dati personali

| Tabella/area | Dati personali | Scopo |
|---|---|---|
| `videotrack_seg` | Utente, sessione, intervalli media, velocità, timestamp, esito validazione | Evidenze viste e audit. |
| `videotrack_state` | Utente, intervalli uniti, posizione, percentuale, completion | Stato derivato efficiente. |
| `videotrack_reactev` | Utente, reazione/nota/bookmark, testo privato, tempo media | Studio e completion. |
| `videotrack_integrity` | Utente, tipo diagnostico limitato, sessione/tempo | Diagnostica integrità opzionale. |
| `videotrack_acknowledge` | Utente, hash/versione dichiarazione, snapshot visto, conferma | Presa visione versionata. |
| File Moodle | Video, VTT, poster e icone reazioni nel contesto modulo | Contenuto attività. |

Configurazione attività e definizioni reazioni non sono record di proprietà learner, anche se testo docente può
comunque essere personale nel normale contesto corso Moodle.

## Privacy API Moodle

`classes/privacy/provider.php` dichiara metadata, contesti e user list, esporta i dati dell'utente corrente e delega
la cancellazione al privacy manager circoscritto. Sono supportate cancellazione di tutti i dati utente, di tutti i
dati in un contesto e di utenti selezionati in un contesto. L'export proprietario include testo privato di note e
bookmark soltanto per il soggetto autorizzato.

## Retention

`retentionperioddays` definisce la finestra mobile di cancellazione. Il task pianificato rimuove record granulari
scaduti con operazioni limitate e ricostruisce/rimuove lo stato derivato, che non conserva progresso privo di
evidenze personali residue. Retention illimitata (`0`) è accettata soltanto con conferma esplicita
`retentionunlimitedconfirmed` dell'amministratore.

`validationfallbackdays` è un'impostazione separata e limitata per fallback di validazione legacy; non è il periodo
generale di retention.

## Privacy nei report

L'accesso aggregato applica il mascheramento `analyticsminusers` e non espone filtri learner. L'accesso individuale
può mostrare aggregati esatti e dettaglio learner soltanto nello scope Moodle. Le capability export aggregato e
individuale sono distinte. I campi identificativi CSV opzionali vanno abilitati intenzionalmente.

Testo note ed etichette bookmark sono esclusi dagli Analytics aggregati. I segnali integrità sono diagnostici e
non devono essere presentati come rilievi automatici di comportamento scorretto.

## Backup, restore e cancellazione

I backup con dati utente includono soltanto record dentro la retention corrente. Il restore rimappa gli utenti e
ricostruisce lo stato derivato. Cancellazione attività, reset, Privacy API e retention sono percorsi distinti che
devono rimuovere o ricalcolare coerentemente le stesse famiglie di dati.

## Checklist amministrativa

- documentare finalità e periodo prima di abilitare raccolte opzionali;
- usare il periodo più breve compatibile con l'esigenza didattica;
- lasciare disabilitata la retention illimitata salvo motivazione e conferma;
- minimizzare campi CSV identificativi e assegnazioni capability individuali;
- verificare task pianificati, export/cancellazioni Privacy API e backup dopo cambi configurazione.

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

L'impostazione amministrativa elabora la decisione in tre fasi separate: risolve il valore precedente, rifiuta un
invio illimitato non confermato e registra la transizione soltanto dopo che Moodle ha salvato il nuovo valore. Un
valore finito azzera il marker di conferma; una nuova attivazione illimitata scrive la voce dedicata nel log di
configurazione quando l'helper Moodle è disponibile. Un errore di validazione non registra mai una transizione.

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

## Significato di tempi e completamento esportati

I metadati privacy distinguono i tempi diagnostici dei segmenti playback (valori browser limitati), i record
di avvio con tempo server e il registro autorevole del credito server. Esportare un timestamp di segmento
non lo certifica come evidenza autorevole del tempo server. `videotrack_state.iscompleted` descrive la regola
custom VideoTrack, non tutti i requisiti di completamento dell’attività Moodle. Queste descrizioni non
cambiano payload export, retention/cancellazione o valori salvati.

## Regressione del task dopo il ripristino

La candidata invoca il task Moodle registrato dopo il restore dei dati studente, cambia la finestra retention
di sito e ripete la pulizia. Verifica credito validato residuo, rimozione stati vuoti e conservazione di file
attività/voti core. Vedere [perimetro e CI in attesa](28_USCITA_PRODUZIONE.md); non prova gli orari cron.

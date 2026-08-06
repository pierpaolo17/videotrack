# Indicatori di integrità e controlli del focus dello studente

## Ambito e garanzie

La release 1.6.18 consolida controlli del focus opzionali per singola attività e indicatori diagnostici limitati. Ogni opzione è disattivata per impostazione predefinita. La funzione supporta la valutazione di condizioni di riproduzione anomale; non è un sistema di sorveglianza e non stabilisce se lo studente fosse attento o abbia agito in modo scorretto.

VideoTrack non usa webcam, microfono, eye tracking, biometria, cattura dello schermo, key logging, contenuti di altre schede o note comportamentali a testo libero. Un segnale contiene soltanto identificativi dell’attività, id utente Moodle, id della sessione di riproduzione, tipo di segnale, posizione approssimativa del video e data di creazione.

I segnali devono essere interpretati insieme al contesto didattico. Non devono essere considerati prove conclusive né essere l’unica base per voti automatici, modifica del completamento, sanzioni o limitazioni di accesso.

## Impostazioni dell’istanza

Il modulo dell’attività espone quattro opzioni indipendenti:

- **Registra indicatori di integrità**: salva i tipi diagnostici elencati di seguito.
- **Metti in pausa quando la pagina perde il focus**: interrompe quando la scheda del video viene nascosta. La perdita di focus della finestra interrompe solo con la politica rigida del sito.
- **Impedisci Picture-in-Picture**: applica una protezione best-effort del browser/provider.
- **Abilita pause casuali di attenzione**: durante la riproduzione mette in pausa dopo un intervallo casuale definito a livello sito dall’ultima interazione. L’intervallo predefinito è 300–1800 secondi. Lo studente riprende manualmente.

L’amministratore configura minimo e massimo delle pause casuali (predefiniti 300 e 1800 secondi), la politica sulla perdita di focus e una tolleranza di 0–30 secondi (predefinita 5). La politica **solo scheda nascosta**, consigliata per l’accessibilità, registra la perdita di focus della finestra dopo la tolleranza ma non interrompe. La politica **rigida** interrompe anche dopo una perdita prolungata del focus. Il ritorno del focus o l’interazione con l’iframe del provider annulla l’azione pendente.

Il focus può essere perso per motivi legittimi, tra cui comandi del browser, password manager, tecnologie assistive e finestre del sistema operativo. Picture-in-Picture non può essere bloccato in modo assoluto se un’estensione o un provider esterno ignora le policy dell’iframe o dell’elemento multimediale.

## Segnali

`videotrack_integrity.eventtype` è limitato alla seguente allowlist:

- `forwardseek`: tentativo di avanzamento non consentito e bloccato;
- `tabhidden`: documento del video nascosto durante la riproduzione;
- `windowblur`: finestra del browser senza focus durante la riproduzione;
- `outofviewport`: meno del 25% del player visibile mentre il video era in riproduzione;
- `pipattempt`: ingresso dell’elemento HTML5 in Picture-in-Picture e tentativo di uscita da parte di VideoTrack;
- `randompause`: attivazione di una pausa casuale abilitata;
- `ratechange`: tentativo di superare la politica di velocità configurata;
- `callbackmissing`: interruzione delle callback di progresso mentre il player risultava in riproduzione;
- `trackinggap`: movimento del tempo video incoerente con il tempo reale e con le azioni recenti dello studente.

I provider si comportano diversamente. HTML5 espone un evento Picture-in-Picture diretto; YouTube e Vimeo vengono protetti rimuovendo il permesso dell’iframe quando possibile, ma il provider può non esporre il tentativo di attivazione.

## Flusso runtime

```text
azione del player / segnale di visibilità browser
-> core/player/focus_guard
-> pausa opzionale specifica del provider
-> mod_videotrack_save_integrity_event
-> validazione capability + sesskey + allowlist + debounce
-> videotrack_integrity
```

Il controller condiviso `focus_guard` viene inizializzato separatamente da HTML5, YouTube e Vimeo. Le callback di pausa, lettura tempo e stato restano specifiche del player. Qualsiasi click o attivazione da tastiera nella shell, insieme a play, pausa, seek, indietro, avanti, reazioni, note e segnalibri, riavvia il termine della pausa casuale.

Il servizio esterno applica un secondo debounce server-side per utente/sessione/tipo prima dell’inserimento. Questo limita callback duplicate senza presentare il segnale come unico o definitivo.

## Report e analytics

Il report docente può mostrare il numero totale di indicatori per studente quando la registrazione è abilitata. Il report cumulativo e la scheda Analytics mostrano conteggi per tipo di segnale. La sezione Analytics è sempre visibile: indica se la registrazione è abilitata, avvisa quando i controlli sono attivi senza registrazione e mostra lo stato senza dati quando non esistono segnali. Gli aggregati applicano `analyticsminusers` separatamente a ogni tipo; i totali esatti di eventi e studenti distinti vengono nascosti sotto soglia.

I report non includono dettagli del browser, URL, testo libero o dati di altre schede. L’introduzione ricorda che i valori sono diagnostici e non costituiscono prova di comportamento scorretto.

## Privacy, retention e ciclo di vita

La tabella è dichiarata nella Privacy API. L’export dell’interessato include tipi localizzati e tempo del video formattato. La cancellazione utente/contesto elimina le righe pertinenti. La retention programmata anonimizza utente, sessione e tempo video, conservando soltanto tipi non personali per aggregazioni. Eliminazione attività, reset progresso studente e reset corso rimuovono le righe corrispondenti.

Backup e restore includono le nuove impostazioni. Le righe degli indicatori sono incluse soltanto quando vengono richiesti i dati utente, gli id utente vengono rimappati e i tipi sconosciuti vengono scartati durante il restore.

## Matrice di regressione richiesta

- impostazioni disattivate: nessun controllo del focus e nessuna riga salvata;
- sola registrazione abilitata: i segnali possono essere salvati ma la riproduzione non viene modificata;
- pausa per scheda nascosta su HTML5, YouTube e Vimeo, con ritorno e ripresa manuale;
- politica accessibile: la sola perdita di focus viene registrata dopo la tolleranza senza pausa;
- politica rigida: la perdita prolungata del focus interrompe solo dopo la tolleranza configurata;
- uscita da Picture-in-Picture HTML5 quando l’API browser lo consente;
- rimozione del permesso iframe YouTube/Vimeo senza rompere riproduzione o fullscreen;
- intervallo casuale entro i limiti inclusivi configurati a livello sito, predefiniti 300–1800 secondi, e riavvio dopo le interazioni;
- seek avanti, velocità, tracking, resume, replay, note, reazioni e segnalibri ancora funzionanti;
- gruppi, capability e mascheramento `analyticsminusers` nei report;
- export/cancellazione Privacy API, retention, backup/restore e tutti i reset;
- PHPUnit, PHPCS Moodle + Extra e `grunt amd` reale con file generati inclusi.

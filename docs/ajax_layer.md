# mod_videotrack — AMD AJAX layer design note

**Versione**: 1.4.86 (build 2026060234)

Questo documento motiva il layer AJAX AMD personalizzato usato dai player di `mod_videotrack`.
Il layer non cambia la semantica didattica del plugin: serve a rendere più robusti gli invii asincroni di segmenti, note e reazioni in condizioni reali di rete.

## Perché esiste un wrapper AJAX dedicato

I player inviano eventi mentre lo studente guarda un video. Questi eventi possono arrivare durante pause, seek, cambio tab, rete instabile o chiusura pagina. Il wrapper centralizza:

- validazione client-side del payload prima di chiamare i web service Moodle;
- retry limitato solo per errori transitori;
- jitter per evitare burst simultanei;
- timeout esplicito;
- gestione offline non fatale;
- request scope per ignorare continuazioni obsolete.

La validazione client-side è solo un guard-rail di resilienza e carico. La validazione di sicurezza resta lato server nelle classi `classes/external/*` e nella logica tracker PHP.

## Retry

Il retry è limitato e viene applicato solo agli errori classificati come transitori, ad esempio problemi di rete o risposte temporanee del server.
Non viene usato per errori di autenticazione, permessi, parametri non validi o metodi non consentiti.

Limiti principali:

- `AJAX_MAX_RETRIES = 2` per evitare sovraccarico del server;
- `AJAX_RETRY_DELAY_MS = 750` come ritardo base;
- backoff lineare semplice, senza cicli infiniti.

## Jitter

Il jitter aggiunge una piccola variazione casuale al ritardo di retry.
Serve a ridurre il rischio che molti client ripetano la stessa richiesta nello stesso istante dopo una breve instabilità di rete.

## Timeout

`AJAX_TIMEOUT_MS = 15000` limita la durata massima di una chiamata AJAX lato client.
Il timeout evita che una richiesta pendente blocchi indefinitamente i flussi UI, ma non sostituisce i controlli server-side.

## Limiti payload

Il layer limita la dimensione e la profondità degli argomenti prima dell'invio:

- `AJAX_MAX_PAYLOAD_BYTES = 64 * 1024`;
- `AJAX_MAX_STRING_ARG_LENGTH = 10000`;
- `AJAX_MAX_ARG_DEPTH = 4`;
- `AJAX_MAX_ARRAY_LENGTH = 100`;
- `AJAX_MAX_OBJECT_KEYS = 50`;
- `AJAX_MAX_OBJECT_KEY_LENGTH = 64`.

Questi limiti non autorizzano dati: riducono solo payload anomali o accidentali prima che raggiungano Moodle.

## Request scope e concorrenza

`createRequestScope()` permette di associare una sequenza incrementale alle richieste asincrone.
Quando una risposta arriva dopo che una richiesta più recente ha già aggiornato lo stato, la continuazione obsoleta viene ignorata.

Questo pattern è usato per ridurre race condition lato client senza alterare:

- frequenze heartbeat;
- segmentazione;
- timestamp didattici;
- note;
- reazioni;
- analytics.

## Beacon fallback

Per alcune chiusure pagina il codice usa un fallback `sendBeacon` dove appropriato.
Il beacon è limitato a payload piccoli e mantiene lo stesso endpoint Moodle AJAX con `sesskey` valido.

## Responsabilità

Il layer AJAX:

- prepara e invia richieste;
- classifica errori;
- applica retry limitato;
- evita continuazioni obsolete.

Il layer AJAX non deve:

- manipolare il DOM;
- decidere regole didattiche;
- modificare direttamente lo stato persistente;
- sostituire validazioni server-side.

## Riferimenti codice

- `amd/src/core/api.js` — wrapper AJAX, validazione, retry, request scope.
- `amd/src/core/tracker.js` — uso del wrapper per salvataggi segmenti e stato tracker.
- `classes/external/*.php` — validazione server-side dei web service Moodle.
- `classes/local/tracker.php` — logica persistente di tracking e aggiornamento stato.

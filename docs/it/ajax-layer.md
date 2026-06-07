# Videotrack - Layer AJAX

## Scopo

Questo documento descrive il layer AJAX di Videotrack. Il layer gestisce le chiamate didatticamente rilevanti inviate dal browser, inclusi segmenti di visione, reazioni, note e aggiornamenti di stato.

## Principi

- Le richieste devono essere validate prima di raggiungere la logica applicativa.
- I retry sono limitati e usati solo per errori transitori.
- I timeout evitano richieste bloccate indefinitamente.
- Gli scope token impediscono che una risposta obsoleta aggiorni uno stato non più valido.

## Componenti

- `amd/src/core/api/validator.js`: validazione difensiva dei payload.
- `amd/src/core/api/retry.js`: gestione dei retry con backoff controllato.
- `amd/src/core/api/transport.js`: trasporto AJAX e sendBeacon.
- `amd/src/core/api/scope.js`: controllo dello scope corrente della pagina/player.
- `amd/src/core/debug.js`: logging localizzato e centralizzato.

## Flusso

Il player prepara il payload, il validator ne limita forma e dimensioni, il transport invia la richiesta, il retry interviene solo su errori temporanei e lo scope impedisce aggiornamenti UI provenienti da richieste superate.

## Rationale

La complessità del layer è intenzionale: preserva dati didattici importanti in condizioni di rete instabile senza introdurre duplicazioni o aggiornamenti fuori contesto.

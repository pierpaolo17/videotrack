# Guida sviluppatore

## Regola della baseline

Partire dall’ultimo ZIP reale fornito dal maintainer. Registrare checksum e contenuto di `version.php`, XMLDB, asset AMD generati ed esiti locali prima di modificare. Non dedurre mai che una patch precedente sia installata.

## Workflow di modifica

1. Estrarre l’archivio in una worktree pulita e creare il commit baseline.
2. Ricostruire il percorso richiesta/runtime effettivo. Per i player analizzare separatamente HTML5, YouTube e Vimeo.
3. Applicare la modifica coerente più piccola. Quando cambia il contratto dati includere privacy, accessibilità, backup/restore, completamento, report e traduzioni.
4. Aggiornare inglese e tutti i sette language pack tradotti, preservando esattamente i placeholder Moodle.
5. Aggiornare documentazione numerata e le coppie README/privacy nella root.
6. Se cambia `amd/src/*`, eseguire il vero task Moodle `grunt amd` e includere `.min.js` e `.map` corrispondenti.
7. Eseguire controlli statici, PHPUnit e PHPCS quando disponibili; riportare gli errori senza attenuarli.
8. Generare la patch dalla root del plugin con path `a/` e `b/`.
9. Verificare `git apply --check`, applicazione reale su baseline pulita, uguaglianza degli alberi e `patch -p1 --dry-run`.

## Confini di fiducia

- Il browser non è affidabile per proprietà, posizione vista o decisioni di completamento.
- Ogni servizio di scrittura learner valida parametri, login, contesto modulo, `mod/videotrack:participate`, proprietà e stato della funzione.
- I testi privati non vengono copiati nei report aggregati.
- Focus e Picture-in-Picture sono best effort: non promettere controlli che browser/provider non consentono.
- Gli indicatori sono diagnostici e non dimostrano autonomamente un comportamento scorretto.

## Convenzioni

Seguire coding style Moodle, regole XMLDB e API Moodle 5.0. Trasferire configurazioni grandi tramite elemento JSON nel DOM, non tramite payload estesi di `js_call_amd()`. Mantenere sincronizzati sorgenti e build AMD. Usare classi namespaced per logica riusabile ed eventi Moodle espliciti per azioni auditabili.

## Definizione di completato

Una release è completa soltanto quando codice, schema, servizi, chiavi/placeholder, asset generati, Privacy API, reset/cancellazione, backup/restore, report/export e documentazione descrivono lo stesso contratto corrente.

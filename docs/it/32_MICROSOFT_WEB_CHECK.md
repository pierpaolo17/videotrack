# Verifica Microsoft dall’amministrazione del sito

VideoTrack **1.7.261** (`2026111801`).

## Apertura e configurazione

Da **Amministrazione del sito → Plugin → Moduli attività → VideoTrack**, salvare
il **servizio OAuth Microsoft** scelto e seguire **Verifica connessione Microsoft**.
Il percorso equivalente è `/mod/videotrack/provider_check.php`. Solo chi possiede
`moodle/site:config` nel contesto di sistema può aprire o inviare questa pagina.
Il permesso di modifica del docente nel corso non concede questo accesso.

Aprire la pagina legge soltanto la configurazione locale, senza creare client OAuth
né contattare Microsoft. Il JSON diagnostico mostra HTTPS del sito, tipo/abilitazione/
uso per servizi, configurazione client, HTTPS degli endpoint e account di sistema
memorizzato. Una configurazione completa non prova validità del token o accesso
remoto. Un sito di test HTTP mostra `site_https: false` e non esegue la verifica remota.

La [guida Microsoft esistente](30_MICROSOFT_GRAPH.md) descrive OAuth di core, scope,
accesso dell’account ed endpoint della cloud pubblica supportati. Le credenziali
restano in Moodle core. Non ci sono nuovi segreti o interruttori per player cloud.

## Verifica esplicita dei metadati

1. Inserire **ID drive**. Aggiungere **ID file video (facoltativo)** per verificare
   un video del drive; lasciarlo vuoto verifica solo i metadati del drive.
2. Usare ID opachi ottenuti da strumenti amministrativi/API Microsoft attendibili.
   Sono sensibili alle maiuscole e accettano 1–256 lettere ASCII, cifre, `!`, `_` o `-`.
   Non sono URL di condivisione, percorsi, email o ID numerici Moodle. La pagina non
   esplora né enumera il tenant. Gli spazi iniziali/finali sono rimossi; altri
   caratteri non validi generano un errore sul campo senza essere eliminati.
3. Premere **Verifica accesso Microsoft**. È un POST esplicito protetto dal sesskey
   Moodle; i parametri GET non possono inviarlo. I campi non validi sono respinti
   prima della diagnostica. Con configurazione incompleta il servizio restituisce
   uno stato locale senza tentare Graph.
4. Leggere l’esito: `drive_accessible` o `video_accessible` indica metadati letti
   con l’**account di sistema configurato**. Non prova l’autorizzazione del singolo
   studente. Gli altri stati, comprese risorse mancanti/non autorizzate o limiti di
   frequenza, seguono le [definizioni CLI](30_MICROSOFT_GRAPH.md).

La pagina riutilizza il servizio esistente: core può aggiornare e memorizzare le
credenziali OAuth, poi viene eseguito un solo GET limitato ai metadati. Non richiede
il contenuto del file, non importa video, non esegue streaming tramite Moodle, non
avvia lavori in background e non modifica dati VideoTrack o demo. Nessun tentativo
automatico aggiuntivo. Reinviare il POST dal browser può ripetere esplicitamente
la verifica. La pagina non è memorizzabile in cache; VideoTrack non salva gli esiti.

L’output è sottoposto a escape e usa il contratto diagnostico ridotto esistente:
stato, fase, codice HTTP e MIME/dimensione/durata/tipo drive consentiti, senza nomi
file, ID risorsa, URL privati, token o errori grezzi del provider. I campi inseriti
restano visibili all’amministratore nel modulo. La configurazione locale include
l’ID del servizio OAuth Moodle, senza segreti client o token dell’account.

## Perimetro e validazione

È diagnostica dei metadati, **non riproduzione Microsoft**. Il selettore dell’attività
non riceve nuove sorgenti. Autorizzazione studente, rinnovo dei link temporanei e un
collegamento supportato al player per tempo/play/pausa/seek devono ancora essere
implementati e validati prima di accreditare visione. Zoom e Google restano tappe
successive; il repository Zoom opzionale è ancora una valutazione architetturale.

Nove metodi unitari nativi coprono identificatori, POST/GET, sesskey, accesso al nodo
amministrativo e ordine dei controlli nel controller. Tre scenari browser coprono
stato locale, URL non validi e configurazione incompleta senza rete reale. Sono
regressioni superate dalla prima CI 261 a sei job (Unit472/5183, Behat61/1268).
La run successiva risolve PHPStan/Psalm, ma richiede una correzione PHPDoc e nuova
CI dopo l’indisponibilità del servizio HTTP 5.0/MariaDB. L’[audit documentale](09_DOCUMENTATION_AUDIT.md)
riporta le evidenze esatte.
Non sono stati provati un tenant Microsoft collegato o la telemetria del player ufficiale. Conservare la
demo; la verifica manuale separata di accessibilità Moodle 5.3 resta in standby.

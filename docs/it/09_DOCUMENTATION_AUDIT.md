# Audit della documentazione

Albero corrente: VideoTrack **1.7.265** (`2026112000`). Release baseline accettata:
**1.7.264** (`2026111901`). La candidata265 non ha ancora eseguito CI Moodle nativa.

## Sorgenti264 accettati e CI nativa

Candidato corretto `f77cd0c2897bcce5d3ecf3529af03f5e3b87a120` e main
`247bfb4b3791b8bb32fc66992b10319d4ac67ded` condividono l’albero
`bea744d925670e857ea486f2c0d2a5831de57414`. Ricostruito il repository completo483file
dal pacchetto reale e24blob solo repository: albero Git identico, modi inclusi.
Tutti459file distribuiti coincidono con l’albero e l’insieme export-ignore.
L’equivalenza dei sorgenti è indipendente dalle prove native.

Il [run44572433050](https://github.com/pierpaolo17/videotrack/actions/runs/44572433050)
del candidato corretto supera sei job: Unit530/5612, Behat67/1327 senza rerun interni,
strict10/0/0 e Grunt. Il [run44702501925](https://github.com/pierpaolo17/videotrack/actions/runs/44702501925)
main termina anch’esso con sei job superati, al tentativo2. I sei digest dei report
finali riusciti coincidono con quelli ufficiali. Ognuno ha Unit530/5612 e strict10/0/0,
ma viene conservato esplicitamente lo storico Behat main:

- 5.1 fallisce inizialmente la visibilità di una nota YouTube dopo il rollback. Il
  rerun interno supera uno scenario/43step. La vecchia condizione di caricamento
  esclude i dump browser del primo errore dagli artefatti. Causa prodotto non dimostrata.
- Il primo5.2 perde localhost:8000 durante Behat; i rerun interni non raggiungono il
  servizio. Un nuovo job completo136212821667 supera67/1327 senza rerun interno con
  sorgenti identici. La causa dell’interruzione non è dimostrata; non si deduce OOM.
- Gli altri quattro job main superano67/1327 senza rerun interni. Cinque report finali
  riusciti hanno quindi esecuzioni browser complete pulite;5.1 conserva il proprio rerun.

Superati lint/PHPCS191PHPdistribuiti+1helper, PHPDoc, metadati e206savepoint. PHPMD
conserva31rilievi revisionati di baseline. PHPStan/Psalm senza errori configurati;
restano750/778informazioni Psalm, quattro in più della263, senza tipi stampati.
Non si attribuisce né dichiara risolto quel delta. Le tre genealogie lifecycle
storica/parziale/precedente passano su50/53MariaDB: sei hash snapshot ricostruiti
autonomamente e126prove effettive dei contenuti condivisi nell’accettazione main.

La prima candidata264 `e07044204677edd6aba4d79714aa6f6ed41fd810` supera i test
funzionali ma fallisce quattro spaziature boilerplate e aggiunge un flag booleano
PHPMD. Le correzioni alla stessa versione ripristinano intestazioni, spostano la
lettura errore callback nel completamento con solo nonce dopo verifica richiesta e
rafforzano rifiuto/replay. Le prove corrette sopra completano quell’accettazione
inizialmente incompleta; il lifecycle50 saltato allora non è descritto come pass.

## Pacchetto264 reale e server maintainer

`videotrack-1.7.264.zip`:1.946.922byte,459file/191PHP; commento archivio main247bfb4b
accettato. SHA256:
`9a4809bd0731c2019a2dc09591bc448df0c33e73528cb38199324d4f2c96aceb`.
`report_completo_videotrack_1.7.264_20261120_113541.zip` SHA256:
`b844fc9132bab20cc22d586c9e20bcff371e842428c5c9a8b13b4835a5a7cff4`.

Superati tutti otto gli esiti richiesti. PHPCS/lint sono una sola esecuzione
condivisa su codice identico:192PHP, zero errori/warning, stderr PHPCS vuoto.
Unit530/5612 superati separatamente su Moodle5.0.11 e5.3+, PHP8.3.6/MariaDB11.4.14.
Entrambi Grunt completati, hash build prima/dopo registrato invariato:
`ac160febbcd2a2cacabe5ac1b95e5263d04db57d5aa298af2e6c59aef5ccb0cb`.
Tutte48mappe sorgenti dello ZIP corrispondono autonomamente ai sorgenti. Nessuna
ricostruzione autonoma dell’hash aggregato build server viene dichiarata.

Strict maintainer:10pass/0warn/0fail per server; elementi/righe voto0/0 su5.0 e34/34
su5.3. Installato/file2026111901, schema7tabelle/151campi/22indici espliciti/
22chiavi esterne,9AJAX,8pacchetti×1040chiavi e48gruppiAMD coerenti. Accettazione264
completata; i controlli locali265 non estendono quelle prove native alla nuova candidata.

## Modifiche265 correnti e confini delle prove

Il workflow tenta il caricamento dei dump browser per ogni esito, incluso un rerun
interno riuscito. Dump assenti ignorati; nomi e conservazione14giorni mantenuti.
Subito dopo uno step Behat completato e prima del lifecycle, uno step distinto registra
esito, ora UTC, memoria e nomi/RSS dei processi, senza argomenti né ambiente. Nessuna
sonda HTTP dopo il normale arresto dei server dello strumento. La fotografia non
diagnostica un’interruzione precedente. [Guida alle prove](36_CI_BEHAT_EVIDENCE.md).

Comando Behat originale, propagazione pipefail/exit, rerun predefiniti, matrice a sei
voci, asserzioni/regole qualità e condizione lifecycle invariati. Nessuna modifica
al comportamento di produzione oltre ai metadati release; PHP di produzione, test,
AMD, lingue, schema/upgrade, servizi e demo identici nei byte alla264 accettata.
Aggiunti due file guida abbinati. Conteggi attesi:485file repository/461distribuiti,
191PHPdistribuiti,1376funzioni PHP nominate (1369distribuite+7helper),654elementiAMD,
530metodi unitari,67scenari espansi,8×1040chiavi e48gruppi sorgente/build/mapAMD.

Le verifiche locali coprono struttura YAML e gate invariati, sintassi/esecuzione
shell della fotografia con esiti successo/fallimento e utility risorse indisponibili,
equivalenza sorgenti, link/conteggi documentazione e applicazione/inversione pulita
della patch. Sono controlli locali, non risultati nativi265 PHP/Moodle/Actions.
Per accettare265 servono sei job candidato, sei main, ZIP/server reale e due strict.

## Roadmap e limiti mantenuti

Ritorno OAuth Microsoft personale con browser reale e accesso Graph/video delegato
reale non eseguiti. Diagnostica amministrativa o avviso di connessione riuscita non
dimostrano diritti studente né telemetria player. Riferimenti stabili nell’attività/
risoluzione all’avvio, autenticazione provider supportata e telemetria tempo/play/
pausa/seek restano gate prima del player Microsoft tracciato. Prima Microsoft, poi
Zoom (repository Moodle separato opzionale), Google Drive e infine App. Nessun import
né proxy streaming Moodle introdotto. Analytics di produzione non ottimizzati qui.

Accessibilità manuale Moodle5.3 ancora **STANDBY**. Conservare dataset demo e debito
degli harness Node storici; nessuno dei due viene dichiarato risolto o cancellato.
Documentazione inglese/italiana ed etichette ER descrivono lo stesso albero corrente.

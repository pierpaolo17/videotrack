# Copertura browser e uscita U-007

**U-007 è chiuso sulla 1.7.240 accettata.** Il pacchetto reale coincide con main
`2d047f5c59792e4c8798a31209f19cfa9ecc15f7`. I sei job release corretta/main superano **50 scenari /
1.074 step** e **379 test PHPUnit / 4.076 asserzioni**. PHPCS/lint, Grunt, PHPUnit server e due strict passano;
il report server non esegue Behat. Vedere l’[audit di uscita](26_USCITA_BROWSER.md). La candidata 1.7.241
cambia solo documentazione/metadati versione; richiede comunque i propri gate CI/pacchetto.

## Setup ed esecuzione

Configurare il sito Behat Moodle isolato (`behat_wwwroot`, `behat_dataroot`, `behat_prefix`), poi dalla root
Moodle, dopo modifiche a generatori, step o feature:

```bash
php admin/tool/behat/cli/init.php
php admin/tool/behat/cli/run.php --tags='@mod_videotrack'
```

Usare un database di test isolato. Il workflow usa il profilo `chrome` su sei job Moodle/PHP/database:
un solo profilo browser in sei ambienti. Non è evidenza per altri motori browser.

## Fixture deterministiche ed evidenze

- `behathtml5fixture=1`: MP4 locale caricato, durata 60 secondi.
- `behatproviderfixture=youtube` / `vimeo`: id riservati e SDK double locali, abilitati solo sotto Behat.
- `behatlinkedforum=<nome>`: fixture Forum reale nello stesso corso.
- Restano in uso adapter produzione, normali endpoint AJAX, credito server e completamento Moodle nativo.
- I casi di visione senza seed leggono righe persistite reali, senza fabbricare progressi o risposte.
- I casi resume/UI con seed verificano intenzionalmente uno stato precedente, distinti dalla visione guadagnata.

L’obiettivo provider della roadmap è il contratto dell’adapter. Disponibilità pubblica di iframe/rete è
esterna al gate deterministico U-007. Le prove provider reali restano nell’accettazione deploy/contenuti;
percezione con tastiera/screen reader appartiene a [U-020](19_ACCESSIBILITY.md).

## Matrice di copertura essenziale

| Contratto | Evidenza HTML5 | Evidenza YouTube/Vimeo |
|---|---|---|
| Play/pausa, resume salvato, seek indietro/bloccato | `html5_playback_contract.feature`, `html5_seek_policy.feature` | `youtube_provider_contract.feature`, `vimeo_provider_contract.feature` |
| Completamento da visione browser e reload | `browser_tracking_completion.feature` | Stessa feature, un caso per adapter |
| Seek avanti consentito e gap escluso | `browser_allowed_seek_tracking.feature` | Stessa feature, un caso per adapter |
| Timestamp reaction/note/bookmark/Forum dopo rollback | `browser_rollback_interactions.feature` | Stessa feature, un caso per adapter |
| Fine naturale, replay già visto, velocità autorizzata e riserva oltre tetto | `browser_ended_replay_rate.feature` | Stessa feature, un caso per adapter |
| Policy attenzione effettiva, pausa reale e nuovo handshake | `random_attention_policy.feature` | Stessa feature, un caso temporizzato per adapter |
| Anteprima docente e completamento con doppio ruolo | `browser_role_tracking.feature` | Stessa feature, un caso per adapter |
| Report limitati al gruppo, senza gruppi e con tutti i gruppi | `browser_report_groups.feature`, dati HTML5 guadagnati | Confine comune report PHP/ACL Moodle; nessuna replica SDK dichiarata |
| Presa visione, combinazioni completamento e messaggi stato | `html5_acknowledgement_contract.feature`, `html5_completion_contract.feature`, `html5_playback_contract.feature` | Contratti UI/server comuni; non tutte le combinazioni sono replicate per adapter |
| Controlli ruoli, storici opzionali e layout completamento | `learner_role_controls.feature`, `student_personal_sections.feature`, `completion_requirements_layout.feature` | Controlli renderizzati comuni |
| Risoluzione gruppo eccezione focus | `focus_exception_policy.feature` | Policy player effettiva comune |

Ogni riga ha esecuzione Chrome reale nella milestone accettata sui sei ambienti. La tabella dichiara lo
scope effettivo: un caso PHP/UI comune non diventa evidenza separata su provider pubblici o motori browser.

## Asserzioni credito, completamento e seek

Le fixture completamento senza seed disabilitano autoplay/resume e completamento per sola apertura/presa
visione; usano durata 60 secondi e soglia 5%, senza evidenze validate/completamento iniziali. Riprodurre fino
a 5–10 secondi e mettere in pausa deve persistire riga terminale accettata della stessa sessione, credito
server chiuso e completamento nativo, conservato dopo reload. Polling in sola lettura attende le scritture.

I casi seek consentito riproducono fino a 4–10 secondi, saltano a 30, continuano fino a 33–39 e mettono in
pausa. Ledger validato e intervalli aggregati devono lasciare scoperto 12–29,5; copertura unica sotto 30 e
completamento nativo incompleto anche dopo reload. I limiti includono tolleranza di polling/seek provider.

I casi dopo rollback guadagnano prima la soglia del 5%, poi rifiutano il seek a 30. Si osserva il recupero
finché una fase in pausa o avanzamento resta stabile per 6,5 secondi; l’avanzamento deve raggiungere almeno sei secondi
video, con limite osservazione 15 e media sotto 25. Segue la pausa reale. Frontiera/copertura restano sotto
22; timestamp reaction/note/bookmark/URL Forum coincidono entro 1,25 secondi, con scope attività/sessione
esatto. Il composer Forum viene aperto, non inviato. Il completamento resta acquisito.

## Fine, replay e velocità

La fixture docente seleziona 1x/2x, tetto 2x, riserva autorizzata 1x e soglia vista 95%. Una richiesta 4x deve tornare a 1x;
segue 2x selezionato fino alla fine naturale dei 60 secondi entro un’osservazione massima di 45 secondi,
senza seek alla fine né orologi alterati. Per Vimeo si attende il risultato numerico asincrono della velocità.

Righe reali `playstart`/`ended` accettato devono chiudere il credito; posizioni finali/resume tra 59 e 60,001,
copertura unica tra 58 e 60,001 e completamento nativo completo. Un intervallo unito copre 5–20 secondi.
Il replay dal secondo cinque a 1x arriva a 8–12 e mette in pausa: nuovo avvio dopo la fine accettata e pausa
positiva accettata nella stessa sessione browser. Copertura unica e percentuale coincidono entro 0,001 con
la fotografia finale, anche dopo reload. Resume può arretrare. Il replay interno a copertura nota evita
di confondere una frazione iniziale non accreditata per l’handshake con il doppio conteggio.

Retry request-id ed effetti completamento hanno anche copertura PHPUnit comportamentale in `tracker_test.php`
e `tracking_effects_test.php`; non si dichiara un test browser con timeout di rete forzato.

## Attenzione e partecipazione

Cinque casi configurazione coprono terzi automatici, singoli limiti, controllo spento e riserva sito con
durata zero. Tre casi temporizzati selezionano esplicitamente 0,5x/1x e usano fixture da 60 secondi invariate
a metà velocità, così la fine non anticipa la scadenza reale 60–60 secondi di attenzione. Osservano messaggio,
pausa accettata/credito chiuso e frontiera stabile, poi riprendono manualmente fino a 31–38 secondi video e
rimettono in pausa con nuovo handshake. Nessun orologio runtime viene accelerato.

L’anteprima docente deve esporre `trackingenabled=false`, senza righe segmenti/stato plugin né stato nativo
completato dopo playback/reload. Sono ammesse righe native assenti/incomplete; il normale evento apertura
Moodle è legittimo. Iscrizione studente più ruolo docente con modifica abilitano invece il tracking e devono
guadagnare riproduzione accettata/completamento nativo tramite le asserzioni positive esistenti.

## Contratti report gruppi e rifiuto atteso

Il corso forza gruppi separati. I generatori Moodle creano `Group A`/`Group B` con idnumber `A`/`B`, usati
nelle appartenenze. Si previene `moodle/site:accessallgroups` per il docente senza modifica nel contesto
corso; il docente con modifica lo conserva esplicitamente. Due studenti guadagnano progresso HTML5 reale.

Il revisore limitato vede solo gli id del proprio gruppo; quello senza gruppi nessuno; chi accede a tutti
i gruppi vede entrambi gli studenti reali ed esclude lo staff solo report. Le opzioni mantengono l’intera
popolazione autorizzata anche dopo un filtro. Un’asserzione distinta richiede id selezionato e una sola riga
corrispondente nella tabella con caption Per studente localizzata, con sola email propria facoltativa.

Le richieste vietate visitano l’endpoint reale. L’asserzione atomica richiede un solo box fatale/codice
documentale `invaliduser` atteso, senza selettore/tabella/nome escluso né avvisi PHP/debug aggiuntivi.
Riapre poi il report Tutti autorizzato prima del normale hook automatico Moodle. Gli hook restano attivi.
Selettore e nomi esclusi conservano lo scope al rientro. Errori inattesi o risposta con dati fanno fallire.

## Manutenzione e lavoro residuo

Usare selector CSS/data stabili, id esatti e caption localizzate dove servono. Mantenere polling limitato,
asserzioni in sola lettura e separazione tra SDK/rete di test e produzione. Tutti i casi precedenti restano
nella suite. Eseguire CI su sei job dopo modifiche; conservare provenienza commit/pacchetto esatta.

Matrice originaria U-007 e milestone multi-versione sono complete sulla 1.7.240. Altri motori, iframe pubblici,
consenso/cookie e tecnologie assistive si registrano separatamente quando provati, senza dedurli da Chrome/
SDK double. U-020 manuale, review semantica traduzioni e gate integrato produzione/lifecycle/privacy/benchmark
restano aperti. Una regressione comprovata può riaprire la cella interessata.

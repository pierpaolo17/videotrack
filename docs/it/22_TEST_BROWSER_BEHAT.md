# Automazione browser con Behat

VideoTrack ha avviato la fase di automazione browser nella release 1.7.45; la 1.7.108 aggiunge la parity Vimeo deterministica dopo il gate verde dell’esatto albero 1.7.107. L’ambiente Behat del maintainer è operativo su Moodle 5.0–5.3 e la suite usa selector CSS univoci sui `<summary>` nativi invece di click testuali ambigui. Il plugin distribuisce un generator Moodle in `tests/generator/lib.php` e gli scenari browser in `tests/behat/`.

## Scopo

PHPUnit, PHPCS e Grunt sono necessari ma non riproducono timing e callback reali di browser/provider. Behat viene quindi usato per i contratti deterministici della pagina learner che richiedono un browser reale. La matrice specifica dei provider viene aggiunta progressivamente.

## Requisiti

Usare il normale ambiente Behat di Moodle su installazione locale o staging. Non eseguire test browser distruttivi su dati di produzione. Configurare `$CFG->behat_wwwroot`, `$CFG->behat_dataroot` e `$CFG->behat_prefix` secondo la documentazione Moodle, poi inizializzare il sito Behat dalla root Moodle:

```bash
php admin/tool/behat/cli/init.php
```

Dopo aver aggiunto o modificato feature, generator o step Behat, inizializzare nuovamente prima della suite.

## Eseguire gli scenari VideoTrack

Dalla root Moodle:

```bash
php admin/tool/behat/cli/run.php --tags='@mod_videotrack'
```

Per eseguire soltanto lo scenario delle sezioni personali compatte:

```bash
php admin/tool/behat/cli/run.php --name='Active controls stay visible and saved personal history is collapsed by default'
```

## Copertura automatica corrente

La copertura corrente, rafforzata nella 1.7.50:

- crea un'attività VideoTrack tramite `mod_videotrack_generator`;
- abilita reazioni, note personali e segnalibri personali;
- apre l'attività come learner;
- verifica che i pulsanti reazione, il composer nota e il composer segnalibro restino visibili fuori dai `<details>`;
- verifica l'ordine pulsanti reazione → **Le mie reazioni** → form nota → **Le mie note** → form segnalibro → **I miei segnalibri**;
- verifica che le tre cronologie partano chiuse e possano essere aperte indipendentemente.
- aggiunge una regressione ruoli learner/docente/dual-role: i controlli sono attivi per learner e dual-role e read-only per il solo docente.

Il contratto nativo `<details>/<summary>` non richiede JavaScript VideoTrack: le sezioni restano quindi utilizzabili anche da tastiera se un modulo AMD non viene caricato.


### Harness locale deterministica per il seek HTML5

Il generator accetta il campo di solo test `behathtml5fixture=1`. Crea un'attività VideoTrack con sorgente upload usando `tests/fixtures/behat-video.mp4.b64`, una piccola fixture locale di 60 secondi. `tests/behat/behat_mod_videotrack.php` espone step browser che attendono i metadata, eseguono un seek sul media HTML5 e verificano il timestamp risultante.

Per eseguire soltanto la regressione seek HTML5:

```bash
php admin/tool/behat/cli/run.php --tags='@mod_videotrack_html5_seek'
```

Le asserzioni deterministiche correnti coprono entrambe le policy: un salto avanti bloccato a 20 secondi deve tornare alla frontier già vista, mentre lo stesso salto resta a 20 secondi quando il seek avanti è consentito. Il test usa l'adapter HTML5 reale e la File API Moodle locale, senza dipendere dalla disponibilità di YouTube o Vimeo. Dalla 1.7.50 il generator accetta anche `behatlinkedforum=<nome>` per risolvere un Forum fixture dello stesso corso usato nello scenario composer post-rollback.

La release 1.7.51 ha aggiunto `tests/provider_seek_snapshot_contract_test.php`: protegge staticamente l’ordine dello snapshot pre-seek e l’uso di timestamp rollback-safe per YouTube, HTML5 e Vimeo. È copertura complementare: non rende complete le harness browser YouTube/Vimeo ancora aperte.

La release 1.7.53 ha aggiunto `tests/player_resume_completion_alert_contract_test.php`; la 1.7.54 ha corretto quel test senza cambiare il runtime. La 1.7.55 elimina il residuo failure del marker acknowledgement e aggiunge copertura PHPUnit comportamentale per firma completion e versione corrente della presa visione. La 1.7.97 porta il resume HTML5 nel browser deterministico, la 1.7.105 aggiunge gli alert impilati e le release 1.7.107–1.7.108 aggiungono la parity specifica dei provider.

Il gate Behat reale 1.7.98 ha superato **13/13 scenari e 195/195 step** sia su Moodle 5.0 sia su Moodle 5.3 con Chrome 151/Selenium.

## Presa visione deterministica HTML5 — 1.7.98

La 1.7.98 aggiunge `html5_acknowledgement_contract.feature`, senza modificare runtime o step PHP Behat. I nuovi scenari verificano:

- conferma immediata disponibile e persistita dopo il submit;
- checkbox e pulsante disabilitati quando la presa visione richiede il video-end ma il progresso validato non ha ancora raggiunto l’ultimo secondo;
- sblocco della conferma dopo evidenza validata fino a 59,5 secondi sul fixture locale da 60 secondi.


## Completion Moodle end-to-end — 1.7.99

La 1.7.99 aggiunge `html5_completion_contract.feature` e una sola asserzione Behat sullo stato persistito in `course_modules_completion`. I tre scenari verificano:

- completion per sola percentuale: insufficiente prima della soglia e completa dopo il raggiungimento della soglia;
- completion per sola presa visione: completa dopo la conferma;
- logica AND percentuale + presa visione: resta incompleta finché non sono soddisfatte entrambe.

Il gate reale 1.7.99 ha evidenziato un gap del fixture: il seed diretto dello stato validato non chiamava la stessa sincronizzazione completion Moodle usata dalle write runtime, quindi lo scenario per sola visione restava incompleto sia su Moodle 5.0 sia su 5.3. La 1.7.100 corregge soltanto il fixture Behat: per le attività con completion automatica il seed richiama `tracker::refresh_completion()` e `tracker::update_moodle_completion_if_changed()` dopo aver scritto l’evidenza server-validata. Gli scenari acknowledgement continuano a usare submit browser reali. L’asserzione sul record core evita dipendenze dal markup completion tra versioni Moodle.

La 1.7.101 aggiunge `focus_exception_policy.feature`. Due scenari candidati ispezionano il JSON reale del player: un learner esterno al gruppo corso nascosto mantiene `strict`, mentre un membro riceve `hiddenonly`. Il contratto server di split-view/accessibilità viene così verificato senza simulare in modo non portabile il blur del window manager. La suite distribuita passa a 7 feature e 18 scenari candidati; i due nuovi scenari non sono dichiarati verdi finché il maintainer non esegue Behat sull’esatto albero patchato.

La 1.7.103 estende `html5_seek_policy.feature` con un’asserzione reale sullo snapshot pre-seek persistito. Lo scenario riproduce il media locale prima di un salto bloccato, acquisisce `currentTime` immediatamente prima di assegnare il target vietato e attende la riga database risultante con `endreason = 'seek'`. L’asserzione richiede un intervallo non vuoto validato dal server il cui estremo coincide con il confine browser entro 0,75 secondi. Questo chiude la parte HTML5 deterministica del precedente gap coperto soltanto staticamente, senza modificare il JavaScript di produzione.

Il gate reale 1.7.103 ha mostrato che l’aspettativa aggiuntiva `servervalidated = 1` era più restrittiva del contratto sullo snapshot persistito. Sia su Moodle 5.0 sia su 5.3 la riga grezza terminava entro circa 0,22 secondi dal tempo pre-seek acquisito, ma il guard server la conservava come evidenza non autorevole. La 1.7.104 mantiene l’asserzione sull’estremo grezzo e verifica inoltre che copertura unica aggregata e posizione resume non entrino nel gap saltato. Questo valida sia gli esiti accettati sia quelli conservativamente rifiutati dal ledger senza indebolire il guard server-authoritative.

L’esatto albero 1.7.104 ha poi superato PHPCS canonico e PHP lint, 263 test PHPUnit / 2342 asserzioni e tutti i 19 scenari Behat / 271 step sia su Moodle 5.0 sia su Moodle 5.3.

La release 1.7.105 estende `html5_playback_contract.feature` con uno scenario deterministico sugli alert impilati. Il progresso validato attiva il normale avviso di resume, mentre la policy dell’attività attiva indipendentemente l’avviso di seek avanti disabilitato. Un invio vuoto tramite il vero form note learner crea quindi l’alert di validazione transiente. Lo scenario verifica che i tre avvisi coesistano e che chiudere l’alert transiente non rimuova nessuno dei due avvisi persistenti. Non cambia JavaScript di produzione né helper PHP.

L’esatto albero 1.7.105 ha superato PHPCS canonico e PHP lint, 263 test PHPUnit / 2342 asserzioni e tutti i 20 scenari Behat / 291 step sia su Moodle 5.0 sia su Moodle 5.3.

La release 1.7.106 rafforza lo scenario HTML5 play/pause esistente. Dopo almeno due secondi di media locale realmente riprodotto, lo step finale attende la scrittura asincrona del ledger e richiede un `playstart` non validato, una `pause` server-validata e non vuota con lo stesso identificatore di sessione e lo svuotamento di `serverplaybacksessionid` / `serverlastactivity`. È la verifica browser/runtime esplicita del lifecycle terminale AC-01; rifiuto cross-session e confini del budget restano coperti dai test PHPUnit comportamentali. La suite candidata resta a 7 feature / 20 scenari e sale a 293 step attesi.

L’esatto albero 1.7.106 ha superato PHPCS canonico e PHP lint, 263 test PHPUnit / 2342 asserzioni e tutti i 20 scenari Behat / 293 step sia su Moodle 5.0 sia su Moodle 5.3.

La release 1.7.107 aggiunge `youtube_provider_contract.feature` e il campo test-only `behatproviderfixture=youtube`. Solo quando `BEHAT_SITE_RUNNING` è definita e l’attività usa l’identificatore video riservato `VTBehat0001`, `view.php` carica un doppio SDK locale prima dell’entrypoint AMD YouTube di produzione invariato. Lo scenario esercita quindi i percorsi reali di resume, AJAX play/pausa, polling, seek indietro e blocco del seek avanti senza caricare `youtube.com`. Verifica inoltre il vero ledger della pausa accettata e richiede che il rollback bloccato coincida con una frontiera persistita invariata. La suite candidata contiene 8 feature / 21 scenari / 311 step eseguiti attesi; non viene dichiarata verde prima del run del maintainer su questo esatto albero.

L’esatto albero 1.7.107 ha superato PHPCS canonico e PHP lint, 265 test PHPUnit / 2352 asserzioni e tutti i 21 scenari Behat / 311 step sia su Moodle 5.0 sia su Moodle 5.3.

La release 1.7.108 aggiunge `vimeo_provider_contract.feature` e `behatproviderfixture=vimeo`. L’identificatore numerico riservato `987654321` attiva un doppio SDK Vimeo locale soltanto in modalità Behat. `view.php` lascia invariato l’adapter Vimeo di produzione e ne seleziona il costruttore su container, senza creare iframe o richieste SDK pubbliche. Lo scenario copre resume validato, seek indietro, recovery del seek avanti bloccato su una frontiera database stabile, continuità della riproduzione dopo il recovery e pausa terminale accettata. La suite esatta contiene 9 feature / 22 scenari / 331 step eseguiti ed è passata su Moodle 5.0 e 5.3.

La release 1.7.112 ha aggiunto `completion_requirements_layout.feature` e la 1.7.113 ne ha allineato i selettori alle rappresentazioni Moodle con elenco nativo o ARIA. La suite esatta 1.7.113 ha superato 23 scenari / 341 step su Moodle 5.0–5.3. La 1.7.114 ha aggiunto un’asserzione geometrica: il contenitore raggruppato deve risultare una colonna flex verticale e ogni requisito deve iniziare sotto il precedente. Il suo gate esatto ha superato 23 scenari / 342 step su Moodle 5.0 e 5.1, ma ha evidenziato che Moodle 5.2/5.3 può collocare il marker della regione completion su un antenato dell’elenco. La 1.7.115 seleziona quindi direttamente il marker univoco di raggruppamento VideoTrack e verifica separatamente che l’elenco marcato appartenga alla regione completion Moodle. La suite resta a 23 scenari / 342 step attesi per ramo e non viene dichiarata verde prima del gate server sull’albero esatto.

## Limiti correnti della copertura browser

La suite distribuita copre deterministicamente HTML5, YouTube e Vimeo per resume, seek indietro e recovery del seek avanti bloccato. Il gate browser esatto 1.7.108 su Moodle 5.0/5.3 è passato e chiude questa tranche provider U-007; rendering e disponibilità del provider pubblico restano aspetti di integrazione esterna, non dipendenze di correttezza della suite.

Gli scenari provider dovrebbero evitare dipendenze dalla disponibilità della rete pubblica quando una harness locale deterministica può esercitare lo stesso contratto dell'adapter.

## Evidenze di release

I risultati Behat appartengono all'albero esatto su cui sono stati eseguiti. Registrare versione Moodle, browser/driver, numero scenari e failure. PHPUnit/PHPCS/Grunt verdi non sostituiscono l'automazione browser e un risultato Behat di una release precedente non va attribuito a un tree successivo.

La feature HTML5 può pre-caricare un intervallo di visione validato; nella 1.7.50 lo scenario verifica che reazione, nota, segnalibro e accesso al composer del Forum collegato restino validi dopo un seek avanti bloccato e il conseguente rollback su una posizione già vista. L'asserzione Forum controlla anche che l'URL del composer contenga un timestamp interno all'intervallo già validato.


## Stabilizzazione selector 1.7.83

La matrice 1.7.82 ha reso operativo Behat su Moodle 5.0–5.3 ma ha mostrato due failure identici con Chrome 151 sul click testuale di `My notes`. La 1.7.83 mantiene invariato il markup runtime e usa nei feature i selector CSS univoci `.videotrack-student-section-… > summary`, così il click punta direttamente al nodo `<summary>` interattivo. Le etichette visibili restano verificate separatamente.

## Estensione deterministica HTML5 — 1.7.97

La 1.7.97 aggiunge `html5_playback_contract.feature` e riusa il fixture MP4 locale già impiegato per il seek. I nuovi scenari verificano senza rete pubblica:

- resume HTML5 vicino alla posizione validata salvata;
- seek indietro consentito all'interno del progresso già validato;
- transizione reale play/pause tramite il control bar VideoTrack.

Il nuovo step Behat legge direttamente lo stato `paused` del media HTML5 e attende la transizione, evitando sleep fissi. Gli alert impilati sono coperti dalla 1.7.105 e la matrice provider-specifica candidata è completata dalle release 1.7.107–1.7.108.

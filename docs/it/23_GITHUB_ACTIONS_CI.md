# Integrazione continua con GitHub Actions

VideoTrack esegue l'integrazione continua del repository tramite `.github/workflows/ci.yml`. Per ogni voce della
matrice GitHub prepara un runner Ubuntu nuovo; il workflow avvia soltanto il servizio database selezionato e
`moodle-plugin-ci` installa il ramo Moodle richiesto e VideoTrack nell'ambiente temporaneo. Non viene modificata
l'installazione Moodle di produzione e non servono segreti del repository.

## Attivazione e permessi

Il workflow parte con i push su `main` e `release/**`, con le pull request e manualmente tramite
`workflow_dispatch`. Le esecuzioni concorrenti dello stesso workflow e riferimento vengono annullate quando
superate da una nuova. Il solo permesso repository è `contents: read` e il checkout non conserva credenziali Git.

GitHub Actions deve essere abilitato nel repository. Una pull request proveniente da un fork non riceve token
privilegiati o credenziali di deploy da questo workflow.

## Matrice dei test

| Moodle | Ramo upstream | PHP | Database | Scopo |
|---|---|---:|---|---|
| 5.0 | `MOODLE_500_STABLE` | 8.2 | MariaDB | ramo minimo supportato e job qualità canonico |
| 5.0 | `MOODLE_500_STABLE` | 8.2 | PostgreSQL | portabilità database al limite inferiore |
| 5.1 | `MOODLE_501_STABLE` | 8.3 | MariaDB | compatibilità intermedia |
| 5.2 | `MOODLE_502_STABLE` | 8.3 | MariaDB | compatibilità intermedia |
| 5.3 sviluppo | `main` | 8.3 | MariaDB | limite superiore finché non esiste il ramo stabile |
| 5.3 sviluppo | `main` | 8.3 | PostgreSQL | portabilità database al limite superiore |

`fail-fast` è disattivato: un fallimento non nasconde l'esito degli altri ambienti. Moodle 5.3 usa `main` finché
Moodle non pubblica `MOODLE_503_STABLE`; modificare quel selettore sarà un'operazione di manutenzione esplicita.

## Controlli bloccanti

Ogni voce della matrice installa gli ambienti PHPUnit e Behat, esegue il task AMD di Grunt Moodle con il confronto
canonico degli artefatti, installa il database Moodle ordinario, esegue il validatore VideoTrack in sola lettura e
infine lancia PHPUnit e Behat. Gli schemi normale, PHPUnit e Behat usano i prefissi indipendenti già configurati. La
voce canonica Moodle 5.0/MariaDB esegue inoltre lint PHP, PHPCS senza warning, PHPDoc senza warning, validazione
plugin, controllo dei savepoint di upgrade e lint Mustache.

Il workflow usa `set -o pipefail` prima di inviare l'output a `tee`: un checker fallito resta quindi uno step
fallito. I log della console sono raccolti anche in un artefatto scaricabile `videotrack-ci-<matrix-id>` per 14
giorni. Se fallisce Behat viene pubblicato separatamente il faildump del browser.

## Analisi statica consultiva

Il `phpmd.xml` versionato seleziona regole revisionate di clean code, dimensione, design e codice inutilizzato sul
PHP di produzione ed esclude test, lingue, documentazione e strumenti. Moodle PHPCS resta autorevole per naming e
convenzioni del framework. Prima del ruleset, l'esecuzione generica 1.7.123 aveva rilevato 215 finding in 42 file:
56 complessità ciclomatica, 35 NPath, 34 variabili lunghe, 23 metodi eccessivi, 14 flag booleani, 13 parametri
inutilizzati, 13 classi con troppi metodi pubblici, otto variabili locali inutilizzate, tre liste parametri eccessive
e 16 altri finding. È un riferimento pre-ruleset, non la baseline corrente. L'exit 2 di PHPMD (finding) è
consultivo; ogni altro stato non zero è un errore dello strumento/configurazione e blocca il job.

PHPStan 2.2.13 e Psalm 6.16.1 sono fissati da `.github/static-analysis/composer.json`. Nei job MariaDB Moodle 5.0 e
5.3 vengono eseguiti dopo l'installazione del sito ordinario, solo sui percorsi VideoTrack di produzione. Le
configurazioni versionate caricano `tools/static-analysis/bootstrap.php`; `MOODLE_ROOT` seleziona l'esatto albero
testato, con fallback deterministico classico/`public/` per i runner maintainer. PHPStan parte dal livello 1 e Psalm
dal livello 8. I finding sono temporaneamente
consultivi finché si raccolgono le prime baseline valide cross-versione; `static-tools.txt`, `phpstan.txt` e
`psalm.txt` conservano identità degli strumenti e output completo.

I vecchi output fallback 1.7.122 sono baseline invalide: PHPStan conteneva oltre 1.000 simboli Moodle perlopiù non
risolti e Psalm mescolava 1.712 errori del plugin, core, vendor e strumenti. Non vanno confrontati numericamente con
i report Moodle-aware limitati. Ogni step CI accetta un exit con finding soltanto se l'output contiene il riepilogo
di analisi completata dello strumento; un riepilogo assente resta un errore bloccante di esecuzione/configurazione.
I finding vengono raggruppati per causa e sanati in tranche circoscritte; i gate diventano bloccanti soltanto dopo
una baseline accettata a zero o una policy di baseline esplicita e revisionata.

## Evidenza delle build AMD

Grunt è limitato esplicitamente al task `amd`. `moodle-plugin-ci grunt` salva una copia del plugin installato,
elimina la directory build, rigenera gli artefatti AMD, confronta i contenuti con il backup e fallisce se un file
versionato è assente o obsoleto. Dopo il confronto ripristina il plugin installato: un diff successivo sul checkout
repository separato non può quindi validare il risultato generato. Il log `grunt.txt` conservato e l'exit status
dello step sono l'evidenza autorevole. I gruppi lint più ampi di Grunt non vengono presentati impropriamente come
gate della build AMD: il debito lint CSS e JavaScript richiede gate dedicati e revisionati.

## Bootstrap del validatore dell'installazione

`moodle-plugin-ci install` prepara i database isolati di PHPUnit e Behat, ma non installa lo schema ordinario del
sito usato da `cli/validate.php`. Subito dopo l'installazione il workflow risolve i percorsi dalla root Moodle nota,
in ordine deterministico: prima `moodle/public/mod/videotrack`, poi il layout classico
`moodle/mod/videotrack`. L'installer Moodle viene verificato separatamente nel percorso
`moodle/admin/cli/install_database.php`.

Il resolver non scandisce l'intero workspace. Questo è essenziale sui rami Moodle in cui il task Grunt `amdtypes`
crea il mirror generato `.types/amd/public/mod/videotrack`: il percorso somiglia a quello del plugin ma non contiene
un'attività installabile. Un controllo difensivo rifiuta qualsiasi risultato `.types`. I percorsi validati vengono
scritti una volta nell'ambiente del job e riutilizzati sia dall'installazione ordinaria sia dal validatore strict.
`paths.txt`, `site-install.txt` e `videotrack-validate.txt` conservano il contratto dei percorsi e le evidenze dei
comandi. La password amministratore viene generata nel job usa e getta e non viene né versionata né conservata. Il
validatore deve terminare in modalità strict senza fallimenti.

## Lettura del risultato

1. Aprire **Actions → Moodle Plugin CI** e scegliere il commit o la pull request.
2. Controllare tutti i sei job. I report riconosciuti con finding PHPStan/Psalm/PHPMD sono consultivi durante la
   fase di baseline dichiarata; un errore di strumento/configurazione rende rosso il job.
3. Scaricare gli artefatti `videotrack-ci-*` per i log completi; verificare `paths.txt`, `grunt.txt`,
   `site-install.txt`, `videotrack-validate.txt` e, dove previsti, `static-tools.txt`, `phpstan.txt` e `psalm.txt`.
4. In caso di errore Behat, scaricare il faildump corrispondente e controllare screenshot, HTML e diagnostica
   browser.
5. Associare ogni risultato allo SHA del commit testato; un commit successivo richiede una nuova esecuzione.

La CI GitHub integra ma non sostituisce i controlli maintainer su installazione reale, upgrade, backup/restore,
privacy, provider e accessibilità manuale. Un runner isolato verde non dimostra lo stato dei dati di produzione o
la disponibilità dei provider esterni.

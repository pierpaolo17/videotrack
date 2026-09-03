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

Ogni voce della matrice esegue installazione, Grunt Moodle, validatore VideoTrack in sola lettura, PHPUnit e Behat.
La voce canonica Moodle 5.0/MariaDB esegue inoltre lint PHP, PHPCS senza warning, PHPDoc senza warning, validazione
plugin, controllo dei savepoint di upgrade e lint Mustache.

Il workflow usa `set -o pipefail` prima di inviare l'output a `tee`: un checker fallito resta quindi uno step
fallito. I log della console sono raccolti anche in un artefatto scaricabile `videotrack-ci-<matrix-id>` per 14
giorni. Se fallisce Behat viene pubblicato separatamente il faildump del browser.

## Controlli consultivi

PHPMD è inizialmente `continue-on-error`. Le regole generiche segnalano nomi Moodle obbligatori come `$DB`, nomi
prescritti delle classi backup/restore e API Moodle statiche; l'output è materiale per refactoring, non una lista
sicura di riscritture meccaniche. Potrà diventare bloccante soltanto dopo un ruleset VideoTrack revisionato e una
baseline esplicita.

PHPStan e Psalm non sono ancora attivati dal workflow. L'esecuzione fallback 1.7.120 non caricava un ambiente Moodle
completo: PHPStan si è fermato dopo oltre 1.000 simboli Moodle non risolti e Psalm ha analizzato anche core/vendor
oltre al plugin, restituendo 1.712 errori. Per aggiungere uno dei due serve una configurazione bootstrap/stub
riproducibile, versionata, limitata a VideoTrack e verificata sui rami supportati.

## Evidenza delle build AMD

Dopo Grunt il workflow salva `git diff -- amd/build` in `amd-build.diff`. Un diff non vuoto genera un warning e
rimane disponibile nell'artefatto del report. Durante il bootstrap CI è consultivo; quando tutte le build e source
map versionate saranno canoniche potrà diventare un controllo bloccante dell'albero pulito.

## Lettura del risultato

1. Aprire **Actions → Moodle Plugin CI** e scegliere il commit o la pull request.
2. Controllare tutti i sei job della matrice; durante la baseline PHPMD dichiarata, il suo step consultivo può
   fallire senza rendere rosso il job.
3. Scaricare gli artefatti `videotrack-ci-*` per i log completi e verificare gli eventuali warning in
   `amd-build.diff`.
4. In caso di errore Behat, scaricare il faildump corrispondente e controllare screenshot, HTML e diagnostica
   browser.
5. Associare ogni risultato allo SHA del commit testato; un commit successivo richiede una nuova esecuzione.

La CI GitHub integra ma non sostituisce i controlli maintainer su installazione reale, upgrade, backup/restore,
privacy, provider e accessibilità manuale. Un runner isolato verde non dimostra lo stato dei dati di produzione o
la disponibilità dei provider esterni.

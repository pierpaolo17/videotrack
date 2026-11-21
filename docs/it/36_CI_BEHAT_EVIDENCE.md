# Conservare e interpretare le prove Behat

VideoTrack **1.7.268** (`2026112101`).

## Motivo di questa release di manutenzione

Il [run44702501925](https://github.com/pierpaolo17/videotrack/actions/runs/44702501925)
del main264 accettato termina con sei job superati, ma conta anche lo storico dei
tentativi. Su Moodle5.1 fallisce inizialmente la visibilità di una nota YouTube dopo
il rollback; il rerun interno supera uno scenario/43step. Il workflow caricava i
dump browser solo quando falliva lo step Behat finale: il rerun riuscito escludeva
quindi dagli artefatti pubblicati le eventuali prove browser del primo errore.

Il primo job5.2 perde l’accesso a localhost:8000 durante Behat. Un nuovo job completo,
senza modifiche ai sorgenti, supera67scenari/1327step senza rerun interno. I log
disponibili non dimostrano perché il servizio HTTP sia diventato indisponibile.
Non si dichiara né una correzione prodotto per la nota né una diagnosi di memoria esaurita.

## Artefatti dalla265

| Artefatto | Contenuti e interpretazione |
|---|---|
| `videotrack-ci-<matrix-id>` | Log comandi esistenti, incluso `behat.txt` completo, più `behat-runtime.txt` se lo step Behat termina con successo o fallimento e il job non è annullato. |
| `videotrack-behat-faildump-<matrix-id>` | File disponibili sotto `moodledata/behat_dump`, anche se un rerun interno successivo riesce. Caricamento tentato per ogni esito del job; directory assente ignorata. Nessun dump inventato o garantito. |

La conservazione resta14giorni. Un run pulito normalmente non produce un artefatto
faildump. Confrontare log completo, dump, commit, voce della matrice e tentativo del
run Actions: lo step finale verde non cancella un fallimento precedente. I rerun
interni automatici e un nuovo job Actions completo sono eventi diversi da riportare
separatamente.

La fotografia finale registra esito dello step Behat, ora UTC, `free -m` e
`ps -eo pid,ppid,stat,comm,rss`. Esclude argomenti dei processi e ambiente. Se una
delle due utility fallisce, il report indica che quella fotografia non è disponibile.
La raccolta segue immediatamente Behat, prima che il lifecycle storico modifichi il
sito temporaneo; non parte con Behat saltato o job annullato.

È una fotografia del runner dopo il comando, non un test HTTP né un monitor continuo.
Il [BehatCommand upstream](https://github.com/moodlehq/moodle-plugin-ci/blob/ab2928c7982a33f325a413a4dfc9ef4ad6a7e23e/src/Command/BehatCommand.php)
arresta i server avviati quando termina il comando di test. Non si esegue quindi
una sonda HTTP successiva: un rifiuto di connessione in quel momento potrebbe essere
il normale arresto. La fotografia non prova lo stato precedente di un processo,
un picco di memoria o la causa di un errore transitorio. Conservare le prove del
fallimento quando si rilancia un job: il pass successivo dimostra solo quel tentativo.

## Gate e lavoro rimanente

Comando Behat, gestione pipefail/exit, impostazioni dei rerun automatici, matrice a
sei voci, asserzioni, regole qualità e condizione di successo del lifecycle restano
invariati. Gli step di raccolta non trasformano un test fallito in successo. Il
caricamento faildump non cambia l’esito di uno step precedente fallito. Per la266
servono ancora CI nativa candidato/main, pacchetto/server reale e due strict.

La manutenzione265 ha modificato prove CI, documentazione e metadati versione.
La raccolta delle prove non modifica riproduzione o dati di produzione. L’anteprima
diagnostica266 è descritta nella [guida dedicata](37_MICROSOFT_PRIVATE_PREVIEW.md). Accessibilità manuale5.3 ancora in standby. Il ritorno completo
Microsoft delegato con browser reale non è stato eseguito; autenticazione provider
supportata e telemetria tempo/play/pausa/seek restano gate prima del player Microsoft
tracciato. Le priorità successive restano Microsoft, Zoom, Google Drive e infine App.

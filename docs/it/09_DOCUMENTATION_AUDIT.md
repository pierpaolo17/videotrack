# Audit della documentazione

Candidata corrente: VideoTrack **1.7.270** (`2026112400`), basata sulla269 accettata.

## Baseline accettata ed evidenze

ZIP269 effettivo caricato, SHA25694200cf7c2d8da43478abd44e39f3c31b6ecae23af2487e28add19f61d7dd45e;
475file distribuiti corrispondono a mainad9dc8ecc0f963f0f6e7bbcc41906e6e734c5471,
albero823ae1cce5c5f29522b0b7ee316718009e224552. Candidato45251028653 e main45260313398
superano sei job ciascuno: Unit595/6280, Behat75/1428 senza retry, strict10/0/0, Grunt
nativo, test Node e matrici aggiornamento/ripetizione/disinstallazione popolata.
I sei hash di conservazione per esecuzione sono ricostruiti indipendentemente.

Il report server20261123_230428 supera otto esiti: canonici/lint198condivisi senza
errori/warning, Unit595/6280 separatamente su50/53, entrambi Grunt con hash invariati.
Entrambi i validator maintainer sono10/0/0. Il manutentore riferisce installazione e
ricalcolo riusciti il2026-11-24; non sono stati eseguiti direttamente dall’assistente.
Restano PHPMD35avvisi e Psalm1rilievo advisory sulla guardia per esecuzione più757/785
informazioni. PHPStan è pulito. La precedente indicazione di zero errori Psalm era errata.

## Modifiche correnti e gate rimanenti

La [ricerca personale](40_MICROSOFT_VIDEO_SEARCH.md) riprende la268 sospesa sulla269.
Conservati tracciamento, recupero,48gruppi AMD nativi,9AJAX e XMLDB. La casella
temporanea di recupero resta finché il percorso straordinario sarà ritirato in una
release dedicata. Il generatore demo resta esterno al plugin e limitato alla5.3.

Aggiunti25metodi unitari e3scenari locali. Conservati tutti595metodi precedenti e
75scenari, estendendo soltanto l’asserzione Privacy esatta con searchquery.
Conteggi:509file repository/483distribuiti,202PHP distribuiti,1542funzioni PHP nominate
incluse7helper CI,660callableAMD,620metodi unitari,78scenari espansi/1458passi,
8lingue con1068chiavi e48gruppiAMD. Inventari bilingui alle posizioni effettive.

Controlli locali: contratti sorgente/lessicali, lingue/placeholder, conservazione dei
file hotfix, mappe esatte, regressioni Node sorgenti/compilati, applicazione/inversione
patch e link documentali. PHP/Moodle/MariaDB non sono disponibili localmente: restano
da eseguire lint/PHPCS, PHPUnit, Behat, Grunt, analisi, lifecycle e test server270.
OAuth/ricerca/anteprima Microsoft reali e telemetria studente restano gate distinti.
Roadmap: Microsoft, Zoom, Google Drive e infine App. Accessibilità manuale5.3 STANDBY.

## Prima CI270 nativa e correzione del DocBlock di file

Il run45385930743 verifica commit9974b13a807ac396a0fd5adb2bb3053d5c44c124,
albero38f819bb014cc2f46ba6033bff98ddd5264d16ea. Il job qualità supera lint202,
PHPDoc, Grunt, strict10/0/0, test Node, Unit620/6483 e Behat78/1458.
Fallisce solo PHPCS: quattro rilievi documentali nel nuovo form. L’annotazione
inline @var precede un DocBlock di file. Aggiunti descrizione/package/copyright/
license prima del namespace, conservando annotazione e tutti i token PHP eseguibili.
Aggiornati entrambi gli inventari alle nuove posizioni delle funzioni.

Anche gli altri job conclusi superano Unit620/6483 e Behat78/1458; il lifecycle53
MariaDB supera i tre percorsi. Quello50MariaDB è saltato dopo l’errore PHPCS.
La candidata corretta richiede una nuova CI completa: non vengono dichiarati
PHPCS nativo locale o esecuzioni PHPUnit/Behat della correttiva. Restano aperti
gli avvisi PHPMD e le informazioni Psalm758/786.

Il manutentore conferma seed demo53, validator post-seed10/0/0 e dashboard riusciti
il2026-11-24. Conservare tutti i dati demo. Questa correzione non cambia SQL,
tracciamento/recupero, sorgenti/buildAMD, test, schema, lingue o workflow.

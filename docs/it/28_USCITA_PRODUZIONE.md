# Evidenze integrate sul ciclo di vita e sul gate di produzione

Candidata corrente: VideoTrack **1.7.252** (`2026111202`). Audit registrato 2026-11-12.

## Baseline accettata

La **1.7.251 / 2026111201** reale è accettata: 415 file coincidono con main
`823a6ac458c5cc8a2ed5a5fbea729dabfdac6330`. SHA-256 del pacchetto:
`4954f75e11f146814e2d410c59bc2457e75f81eea93cfa26208311826717bbab`.
Il report server di 10 file ha SHA-256
`c87760ceadc01bfbc24b47cabc78f2e512acedc3132313c04cbf3123b5bdf7e2`.
CI candidata/main e i due report Moodle 5.0/5.3 reali superano PHPUnit **381/4296**. I sei report browser
selezionati superano **52 scenari / 1154 passi**, senza riesecuzioni di scenari. PHPCS/lint puliti su 167 file;
Grunt e strict forniti superati, con strict **10/0/0** su entrambi i siti e hash build AMD invariati. Conservare
la storia dei nove errori di formato PSR12 corretti prima del merge. Nel workspace non sono state eseguite
prove Moodle native. PHPMD conserva 24 rilievi rivisti e Psalm 733/761 consultivi; i gate configurati passano.

## Rilievo sul callback di reset corso e correzione mirata

Su Moodle 5.0 e 5.3 `reset_course_userdata()` cerca la funzione attività `$modname . '_reset_userdata'`,
dopo avere impostato `courseid` dall'`id` del corso inviato. La 251 definisce solo
`videotrack_reset_course_userdata()`: opzione e cancellazione esistono, ma il framework non può trovare
l'implementazione attraverso il nome atteso. È evidenza sul sorgente; non si dichiara un reset fallito
riprodotto sull'istanza reale del manutentore.

La 252 aggiunge **`videotrack_reset_userdata(stdClass $data): array`**, delegando direttamente all'helper
conservato. Corpo eseguibile dell'helper, flag `reset_videotrack_userdata`, default disattivato, filtri tabelle,
reset voti e stato restituito non cambiano. I chiamanti diretti possono continuare a usare il vecchio helper.
Login, controlli sessione/form e `moodle/course:reset` restano nella pagina Moodle di reset corso. Non si
introducono servizi AJAX o endpoint web diretti. Il reset per studente dal report conserva gli specifici
controlli report completo/gestione/sesskey/scope.

L'opzione VideoTrack cancella le cinque famiglie di dati studente e i valori voto del modulo, conservando
impostazioni, definizioni reazioni e file condivisi. La completion nativa resta controllata dalla separata
opzione Moodle di reset completion: il caso positivo la seleziona esplicitamente. Il reset globale gradebook
resta disattivato, così il test esercita proprio il percorso di reset voti del plugin.

## CI candidata iniziale e correzione di formato nella stessa versione

I sei artifact caricati coincidono con i digest ufficiali del commit
`df3fe608eabe24d4a8c1b1e650dc73ee3e73c79e`, run **43233266763**, tentativo **2**.
Ciascuno supera **383 test PHPUnit / 4350 asserzioni**, **52 scenari Behat / 1154 passi**,
strict **10/0/0** e Grunt. Il lint PHP copre 167 file; PHPDoc è pulito.
La run complessiva ha cinque job riusciti e un job Moodle 5.0/MariaDB fallito:
PHPCS segnala **due errori alla riga 185** di `tests/lifecycle_integration_test.php`.
Riguardano più argomenti sulla stessa riga in una chiamata multilinea a `get_records()`.

La correzione incrementale si applica a quell'albero 252 già committato, conserva
release/versione e dispone gli argomenti su righe separate. Token eseguibili e
letterali del test sono identici; i file operativi sono identici byte per byte.
Entrambi gli inventari callable riflettono le due righe aggiunte. Controllare gli
argomenti delle chiamate oltre agli header di controllo multilinea: il precedente
controllo sugli header non copriva questa regola PHPCS. Nessuna regola CI è indebolita.
PHPCS nativo e matrice corretta completa di sei job restano da eseguire prima del
merge; i PASS funzionali iniziali non rendono accettabile il gate qualità fallito.

I 24 rilievi PHPMD coincidono semanticamente con quelli accettati sulla 251, ignorando
lo spostamento delle righe. I gate configurati Psalm passano con **732/760 consultivi**,
uno in meno per ramo rispetto alla 251; non si deduce una causa non osservata.

## Casi reali di integrazione

| Caso | Evidenza e perimetro |
|---|---|
| Backup/restore senza studenti | Accettato sulla 251: controller corso reali, configurazione condivisa, mapping Forum/reazioni/file, grade item canonico; dati studente/voti/completion esclusi e record plugin di origine conservati. |
| Backup/restore con studenti e privacy dopo il ripristino | Accettato sulla 251: la retention di destinazione esclude gli input scaduti; segmenti non validati e aggregate/completion obsolete non restituiscono credito; verificati export utente approvato, cancellazione utente/contesto e conservazione altri studenti/origine/file/voti di competenza core. |
| Reset corso standard con opzione disattivata/attivata | Superato sulla 252 iniziale nei sei ambienti CI; CI corretta da eseguire. Invoca la vera funzione reset Moodle: prima VideoTrack disattivato, poi attivato su due attività nello stesso corso. Verifica stato modulo, configurazione/definizioni/file conservati, dati studente/voti azzerati e nessuna completion completa quando è selezionato il reset completion core. Un secondo corso conserva esattamente i record studente plugin. |
| Cancellazione sincrona attività | Superata sulla 252 iniziale nei sei ambienti CI; CI corretta da eseguire. Invoca l'API pubblica Moodle: verifica rimozione attività, sei tabelle figlie plugin, completion nativa, file/contesto e grade item/voti propri. Restano il Forum collegato e l'attività vicina nello stesso corso, con dati studente/configurazione/file. |

Il test cancellazione usa `core_courseformat\formatactions::cm()->delete()` quando esiste l'implementazione
`cmactions::delete()`; sui rami precedenti usa `course_delete_module()`. Moodle 5.2+ depreca l'helper globale:
nessuna deprecazione viene soppressa. I casi backup esistenti conservano il percorso fixture predefinito;
l'helper accetta un corso esistente opzionale solo per le nuove prove di isolamento nello stesso corso.

Sono vere integrazioni framework/DB, senza dichiarare che il browser abbia prodotto i record della fixture.
I test backup usano `MODE_IMPORT`, con XML/file reali e senza compressione. Non certificano download/estrazione
MBZ, migrazione tra siti o compatibilità backup storici. I nuovi test reset/delete hanno superato la matrice
nativa iniziale; la candidata corretta deve superare una nuova run completa. Contratti e applicazione patch
locali non sostituiscono tale esecuzione.

Suite corretta attesa: **383 test PHPUnit / 4350 asserzioni**, misurati nella CI nativa iniziale.
Behat resta a **52 scenari / 1154 passi**. L'albero conserva **415 file**, **167 PHP**, **32 documenti per
lingua**, **346 righe inventario file** e **654 callable AMD**. I callable PHP nominati diventano **1113**:
un callback standard e due metodi test. XMLDB resta `2026110601`, **7/151/22/22** per tabelle, campi, indici e
chiavi esterne. Nove servizi AJAX, otto lingue da 997 chiavi e 48 terne sorgente/build/map AMD restano invariati;
l'ER cambia solo etichetta versione. Nessun valore lingua, grant, policy tracking/player, implementazione
privacy/backup/restore, aggregazione SQL o regola CI cambia.

## Gate di produzione residuo e roadmap

- Nuove installazioni e matrice Moodle supportata usano la normale CI; non ne deriva un upgrade storico eseguito.
- Upgrade storici/interrotti e uninstall completo con dati e verifica zero residui restano evidenze distinte;
  i test upgrade/uninstall mirati non certificano ogni storia di deployment.
- I test retention esistenti restano accettati; nessuna nuova prova scheduler o fault injection è dichiarata.
- SQL/indici/scope Analytics sono invariati. Nessun benchmark eseguito o dedotto da test/query; le osservazioni
  richieste su dataset ampio/più attività/gruppi/7/30/90 giorni vanno registrate con versioni e condizioni dati/cache.
- Restano otto PASS manuali dichiarati sulla 248/Moodle 5.0/Academi/Windows 11/Chrome/NVDA nel loro scope;
  Moodle 5.3 non è registrato. Non si deducono nuovi PASS manuali o certificazioni WCAG.
- Il supporto Moodle App dedicato non è dichiarato (`db/mobile.php` ha addon vuoti); nessuna parità nativa/WebView/offline è promessa.

U-017 e U-007 essenziale restano chiusi. Completare il precedente lavoro integrato web/ciclo vita/privacy/
benchmark/manuale e la decisione App esplicita, poi VTT multilingua concordati, base autenticata per provider,
OneDrive/SharePoint → Zoom Cloud Recording → Google Drive e consolidamento tra provider. La release avanza
specifiche evidenze sul ciclo di vita, senza chiudere l'intero gate con la documentazione.

Vedere [ciclo di vita](14_INSTALL_UPGRADE_BACKUP_RESTORE.md), [permessi](13_SECURITY_TRUST_BOUNDARIES.md),
[privacy](18_PRIVACY_RETENTION.md), [CLI/benchmark](21_CLI_DIAGNOSTICS.md), [evidenze manuali](19_ACCESSIBILITY.md)
e [revisione lingue](27_REVISIONE_LINGUE.md).

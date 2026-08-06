# mod_videotrack - Struttura tecnica

**Versione documentata**: 1.6.16

Questo documento storico e stato sostituito dalla documentazione tecnica modulare introdotta nella release 1.4.248. La fonte aggiornata e composta dai file numerati nella stessa cartella.

## Dove trovare le informazioni

- `00_INDEX.md`: indice generale.
- `01_DEVELOPER_GUIDE.md`: regole operative e workflow patch.
- `02_ARCHITECTURE.md`: architettura, servizi AJAX e database.
- `03_FILE_INVENTORY.md`: responsabilita di tutti i file del plugin.
- `04_FUNCTION_INVENTORY.md`: inventario completo delle funzioni PHP e AMD.
- `05_VARIABLE_INVENTORY.md`: inventario statico delle variabili PHP e JavaScript.
- `06_RUNTIME_FLOWS.md`: flussi runtime di tracking, reazioni, note e replay.
- `07_BUILD_TEST_RELEASE.md`: comandi di build, test, PHPCS, PHPUnit e Grunt.
- `08_LESSONS_LEARNED.md`: lezioni apprese e regole di manutenzione.

Il contenuto precedente e stato rimpiazzato per evitare informazioni duplicate e obsolete. Per audit storici restano disponibili i file con suffisso versione.


## Componenti integrazione Forum (1.5.0)

`forum_post.php`, `classes/form/forum_post_form.php`, `classes/local/forum_bridge.php` e `amd/src/core/player/forum.js` implementano il collegamento opzionale. Il bridge verifica due volte visibilità, capability, gruppi e limiti e delega la creazione a `mod_forum_external::add_discussion()`.

## Componenti analytics (1.6.0)

`classes/local/analytics.php` esegue l’aggregazione server-side in streaming su `videotrack_seg`. `classes/local/analytics_scope.php` riconosce lo stesso video tecnico tra attività e filtra ogni istanza tramite capability; `report.php` applica per-istanza i gruppi consentiti, usa la soglia privacy configurabile e genera grafici SVG accessibili con tabella equivalente. Il filtro tra corsi è temporaneo e non aggiunge tabelle aggregate o cache: prima di introdurre persistenza devono essere misurate le query su dataset reali.

## Servizio dashboard di corso (1.6.9)

`classes/local/course_analytics.php` costruisce le righe del corso filtrate per capability e gruppi. Riusa `analytics::build_from_states()` e `analytics::apply_privacy_threshold()` per retention e cali, applica la stessa soglia minima ai conteggi e lascia a `reports_course.php` il solo rendering accessibile. `analytics_scope::accessible_group_ids()` e condiviso dagli analytics di istanza e di corso, cosi la visibilita dei gruppi usa una sola implementazione.

## Export tabella analytics (1.6.11)

`classes/local/analytics_table_export.php` costruisce intestazioni e righe privacy-safe condivise tra la tabella HTML e i download. `report.php` valida formato e sesskey prima di qualsiasi output, registra l'evento di export e delega a `core\dataformat::download_data()`. Sono esposti solo i writer core CSV, Excel e ODS abilitati nel sito; nessun record utente viene caricato dall'exporter.

## Componenti dei segnalibri personali (1.6.14–1.6.16)

`amd/src/core/player/bookmarks.js` fornisce il comportamento browser condiviso. `save_bookmark.php` e `delete_bookmark.php` sono i contratti AJAX protetti per il proprietario. `{videotrack_reactev}` memorizza il record privato tramite `notetype='bookmark'`. `report.php`, `course_analytics.php` e `teacher_analytics.php` espongono soltanto conteggi aggregati protetti dalla soglia. I dettagli completi sono in `10_BOOKMARKS_AND_ANALYTICS.md`.

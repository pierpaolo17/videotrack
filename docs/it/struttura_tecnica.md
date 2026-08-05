# mod_videotrack - Struttura tecnica

**Versione documentata**: 1.6.8

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

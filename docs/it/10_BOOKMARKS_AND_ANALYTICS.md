# Segnalibri personali e analytics dei segnalibri

**Versione documentata:** 1.6.16

## Ambito

I segnalibri personali sono uno strumento di studio opzionale per singola attività. La funzione è disattivata per impostazione predefinita. Il docente la abilita nella sezione aperta **Strumenti personali di studio** del modulo dell'attività. Il valore predefinito del sito è controllato da `setting:bookmarksenabled`.

Un segnalibro appartiene a un solo utente e a una sola attività VideoTrack. Memorizza un'etichetta privata e un timestamp del video già visualizzato. Etichetta e timestamp sono visibili soltanto al proprietario.

## Modello dati

I segnalibri riutilizzano `{videotrack_reactev}` con:

- `notetype = 'bookmark'`;
- `reactionkey = 'bookmark'`;
- `notetext` contenente l'etichetta privata;
- `videotime` contenente la posizione già visualizzata;
- `isdeleted` per la cancellazione logica.

Non viene introdotta una tabella dedicata. Backup, restore, retention e Privacy API riutilizzano il ciclo di vita già previsto per i dati evento.

## Flusso studente

`view.php` carica soltanto i segnalibri attivi dell'utente corrente e passa ai player una configurazione privacy-safe. `amd/src/core/player/bookmarks.js` è condiviso da HTML5, YouTube e Vimeo. Il modulo:

1. salva il progresso corrente prima di creare il segnalibro;
2. risolve il timestamp già accettato dal tracking;
3. chiama `mod_videotrack_save_bookmark`;
4. inserisce la nuova riga in ordine di timestamp;
5. delega il replay all'handler specifico del player;
6. chiama `mod_videotrack_delete_bookmark` per la cancellazione del proprietario.

Il server verifica capability, sessione, impostazione dell'attività, lunghezza dell'etichetta, rate limit e che il timestamp sia già stato visualizzato.

## Export del proprietario

`bookmarks.php` esporta in CSV soltanto i segnalibri dell'utente corrente. La richiesta richiede login, capability, POST e sesskey. I campi CSV sono protetti dalla formula injection. L'export genera l'evento `bookmark_exported`.

## Report docente e analytics

I dati mostrati al docente sono solo aggregati:

- report studente: numero di segnalibri per partecipante;
- dashboard di corso: numero di eventi segnalibro protetto dalla privacy;
- dashboard personale docente: numero di eventi segnalibro protetto dalla privacy;
- Analytics di istanza: sezione dedicata **Uso dei segnalibri** con card per segnalibri salvati e studenti distinti che li hanno utilizzati.

La sezione Analytics compare ogni volta che i segnalibri sono abilitati, anche quando non esiste ancora alcun segnalibro, mostrando esplicitamente valori pari a zero. Se gli utenti distinti sono meno di `analyticsminusers`, i valori esatti vengono sostituiti dall'indicazione standard di dato nascosto per privacy e da un avviso.

Gli Analytics tra corsi includono soltanto le attività corrispondenti nelle quali `bookmarksenabled` è attivo. Etichette e timestamp individuali non vengono mai esposti nei report, nei grafici o negli export del docente.

## Privacy e retention

La Privacy API esporta al proprietario i segnalibri attivi ed eliminati in sezioni separate. Cancellazione e retention programmata trattano i segnalibri insieme agli altri record evento dell'utente. Gli analytics docente usano soltanto conteggi e utenti distinti dopo i filtri di capability e gruppi.

## Backup e restore

`bookmarksenabled` è incluso nel backup e nel restore dell'attività. I dati utente dei segnalibri sono inclusi nella struttura eventi esistente quando il backup comprende i dati utente.

## File principali

- `bookmarks.php`
- `classes/external/save_bookmark.php`
- `classes/external/delete_bookmark.php`
- `classes/event/bookmark_saved.php`
- `classes/event/bookmark_deleted.php`
- `classes/event/bookmark_exported.php`
- `amd/src/core/player/bookmarks.js`
- `classes/local/analytics.php`
- `classes/local/course_analytics.php`
- `classes/local/teacher_analytics.php`
- `report.php`
- `reports_course.php`
- `reports_teacher.php`
- `tests/save_bookmark_test.php`
- `tests/lib_test.php`

## Validazione

Una release che modifica i segnalibri deve verificare:

- persistenza del checkbox dell'attività sia attivo sia disattivo;
- salvataggio, replay, eliminazione ed export del proprietario;
- comportamento runtime separato per HTML5, YouTube e Vimeo;
- conteggi aggregati nei report studente, corso, docente e istanza;
- mascheramento sotto `analyticsminusers`;
- assenza di etichette o timestamp individuali nell'output docente;
- export/cancellazione Privacy API;
- backup/restore con e senza dati utente;
- PHPUnit, PHPCS Moodle Extra e Grunt AMD quando cambiano i sorgenti AMD.

# Segnalibri personali e Analytics

I segnalibri sono disabilitati per default e abilitati per istanza. Appartengono a un solo utente, hanno un’etichetta privata limitata dal sito e puntano a un timestamp già visto. Creazione/eliminazione usano servizi AJAX ed eventi dedicati. Il replay passa dall’adapter attivo e non aggira le policy di seek.

Il proprietario può elencare, riaprire, eliminare ed esportare in CSV. Etichette e timestamp esatti non sono mostrati al docente. I report per studente possono mostrare conteggi. In `report.php`, chi possiede soltanto l’accesso aggregato mantiene il masking configurato da `analyticsminusers`, mentre chi possiede l’accesso individuale vede valori aggregati esatti nello stesso scope Moodle di attività/gruppi. Etichette e timestamp esatti dei segnalibri restano privati al proprietario. Le dashboard di corso e docente mantengono il proprio comportamento capability/privacy già esistente.

I segnalibri riusano `videotrack_reactev` con `notetype='bookmark'`. Privacy API, retention basata sulla cancellazione, reset e backup/restore li includono. L’eliminazione delle reazioni standard non può eliminare righe bookmark.

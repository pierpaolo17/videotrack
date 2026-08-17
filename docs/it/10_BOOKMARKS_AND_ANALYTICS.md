# Segnalibri personali e Analytics

I segnalibri sono disabilitati per default e abilitati per istanza. Appartengono a un solo utente, hanno un’etichetta privata limitata dal sito e puntano a un timestamp già visto. Creazione/eliminazione usano servizi AJAX ed eventi dedicati. Il replay passa dall’adapter attivo e non aggira le policy di seek.

Il proprietario può elencare, riaprire, eliminare ed esportare in CSV. Etichette e timestamp esatti non sono mostrati al docente. I report per studente possono mostrare conteggi. Gli Analytics di istanza, la dashboard di corso e la dashboard docente trasversale mostrano valori aggregati esatti ai report viewer autorizzati entro lo scope Moodle già consentito per attività/corso/gruppi. Etichette e timestamp esatti dei segnalibri restano privati al proprietario. `analyticsminusers` non è usato da queste dashboard Analytics e resta disponibile soltanto per riepiloghi aggregati esterni alle viste Analytics esatte.

I segnalibri riusano `videotrack_reactev` con `notetype='bookmark'`. Privacy API, retention basata sulla cancellazione, reset e backup/restore li includono. L’eliminazione delle reazioni standard non può eliminare righe bookmark.

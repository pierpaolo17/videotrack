# Segnalibri personali e Analytics

I segnalibri sono disabilitati per default e abilitati per istanza. Appartengono a un solo utente, hanno un’etichetta privata limitata dal sito e puntano a un timestamp già visto. Creazione/eliminazione usano servizi AJAX ed eventi dedicati. Il replay passa dall’adapter attivo e non aggira le policy di seek.

Il proprietario può elencare, riaprire, eliminare ed esportare in CSV. Etichette e timestamp esatti non sono mostrati al docente. I report per studente possono mostrare conteggi. Gli Analytics di istanza mostrano conteggi aggregati esatti di eventi/utenti distinti ai report viewer autorizzati mantenendo private etichette e timestamp esatti dei segnalibri; la dashboard docente trasversale usa ancora `analyticsminusers` per mascherare le popolazioni piccole in attesa della sua revisione dedicata; la dashboard di corso è esatta per i viewer autorizzati del report di corso.

I segnalibri riusano `videotrack_reactev` con `notetype='bookmark'`. Privacy API, retention basata sulla cancellazione, reset e backup/restore li includono. L’eliminazione delle reazioni standard non può eliminare righe bookmark.

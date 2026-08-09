# Lezioni apprese

- Fare audit dell’archivio reale: la cronologia patch non dimostra lo stato installato.
- Ricostruire il percorso player eseguito, non correggere un’astrazione ipotizzata.
- HTML5, YouTube e Vimeo condividono obiettivi ma non semantica delle callback.
- Seek utente, programmatico, resume e replay richiedono stati distinti.
- Dichiarazione Web Service, validazione parametri e risposta JSON reale formano un unico contratto.
- Applicare soglie privacy separatamente a popolazioni che possono differire.
- Il testo privato non deve filtrare in report aggregati o debug.
- Un problema accessibile può dipendere da un antenato `aria-hidden`, non solo da label mancanti.
- Il blur finestra non equivale a contenuto nascosto: i controlli rigidi richiedono default accessibile e limiti espliciti.
- Gli export Privacy grandi devono azzerare il buffer dopo ogni blocco emesso.
- I test devono verificare lo schema export corrente; conteggi colonna vecchi sono failure reali.
- Un README che accumula note release diventa fuorviante: separare contratto corrente e storia.
- La parità delle chiavi non prova la qualità delle traduzioni: verificare testi copiati e placeholder.
- Gli asset AMD generati sono evidenza solo dopo il completamento della build reale.

- Una scrittura soggetta a retry richiede un identificativo di idempotenza generato prima del livello di retry: il successo del trasporto non dimostra che la prima risposta sia arrivata al browser.
- Un guard di riproduzione deve partire con credito zero. La tolleranza tra clock provider e server deve restare un debito cumulativo fra richieste e handshake; altrimenti cicli ripetuti play/pausa possono creare progresso artificiale.
- Stato compatto e aggregati esatti hanno responsabilità diverse: limitare l’elenco degli intervalli memorizzati, ma calcolare la copertura autorevole dalle righe grezze validate e mantenere monotona la completion.
- Quando una correzione privacy nasconde un denominatore, i test devono usare un dataset non inferibile oppure verificare la soppressione; indebolire la regola privacy per soddisfare un’aspettativa vecchia è errato.

# Segnalibri personali e Analytics

I segnalibri sono disabilitati per default e abilitati per istanza. Appartengono a un solo utente, hanno un'etichetta privata limitata dal sito e puntano a un timestamp già visto. Creazione/eliminazione usano servizi AJAX ed eventi dedicati. Il replay passa dall'adapter attivo e non aggira le policy di seek.

Il servizio di salvataggio valida parametri external e sesskey AJAX prima di caricare contesto attività e scope learner. Applica quindi abilitazione della funzione, normalizzazione in testo semplice dell'etichetta con lunghezza limitata, limite alla durata, evidenza vista precedente o della sessione corrente e un massimo di dieci segnalibri in dieci secondi per utente e attività attraverso tutte le sessioni browser. Soltanto dopo questi guard inserisce la riga canonica ed emette `bookmark_saved`.

Il proprietario può elencare, riaprire, eliminare ed esportare in CSV. Etichette e timestamp esatti non sono mostrati al docente. I report per studente possono mostrare conteggi. In `report.php`, chi possiede soltanto l'accesso aggregato mantiene il masking configurato da `analyticsminusers`, mentre chi possiede l'accesso individuale vede valori aggregati esatti nello stesso scope Moodle di attività/gruppi. Etichette e timestamp esatti dei segnalibri restano privati al proprietario. Le dashboard di corso e docente mantengono il proprio comportamento capability/privacy già esistente.

I segnalibri riusano `videotrack_reactev` con `notetype='bookmark'`. Privacy API, retention basata sulla cancellazione, reset e backup/restore li includono. L’eliminazione delle reazioni standard non può eliminare righe bookmark.

Gli Analytics tra corsi raggruppano le attività mediante un'identità tecnica stabile del contenuto. YouTube e Vimeo
usano l'identificatore esatto del provider, i file caricati usano il content hash Moodle e i media esterni usano
un URL canonico prodotto da `analytics_scope::normalise_external_url()`. L'identità URL richiede un host, usa HTTPS
quando lo schema è omesso, rende minuscoli schema e host, elimina la porta 80 per HTTP e 443 per HTTPS, conserva le
altre porte, comprime i separatori ripetuti del percorso, ignora i frammenti e ordina le chiavi query di primo livello
con codifica RFC 3986. La normalizzazione di authority, percorso e query è isolata in helper privati; i chiamanti
continuano a usare l'unico contratto pubblico di normalizzazione.

## Limite dei cluster di reazioni e soglia effettiva

La tabella dei cluster usa la soglia privacy per utenti distinti applicabile all’ambito del report selezionato.
L’accesso ai report individuali di tutte le attività incluse consente aggregati esatti; altrimenti vale la
soglia configurata per l’accesso solo aggregato. Si visualizzano al massimo 2000 cluster idonei. Se appare
l’avviso sul limite, ulteriori cluster idonei sono stati omessi. Restringere l’ambito può evitare il limite,
senza garantire una vista completa. Disabilitare la sovrapposizione nasconde i cluster senza recuperare quelli
omessi. Non si promettono il primo sottoinsieme cronologico tra tipi di reazione o un export completo.
Conteggi riepilogativi e filtri privacy/accesso conservano il comportamento esistente. Le correzioni dei
testi non modificano la logica dei report.

## Fonti delle visioni, conteggi zero e riepiloghi indipendenti

Gli Analytics possono usare segmenti grezzi o ricostruire la copertura unica dagli intervalli dello stato
aggregato conservato, quando questo mantiene più evidenze di visione. La ricostruzione dallo stato non
fornisce metriche di revisione. L’oscuramento privacy protegge sottogruppi non vuoti sotto soglia effettiva;
i conteggi zero possono restare visibili. Quando popolazioni di intervallo protette rivelerebbero il
denominatore, vengono nascoste anche le percentuali di retention.

In assenza di intervalli validi, i riepiloghi di eventi restano indipendenti dalla copertura di visione.
Riepiloghi reazioni, segnalibri, prese visione e integrità dipendono dai propri dati/funzioni, permessi e
regole privacy. Diversi riepiloghi precedono l’avviso senza visioni; una tabella cluster può seguirlo. Il
testo non promette quindi posizione o disponibilità. Sono comportamenti esistenti: la patch linguistica
non cambia scelta delle fonti, oscuramento, metriche, export o disposizione del report.

## Testi dei report cumulativi, export e ricalcolo

I report cumulativi delle reazioni usano `reportclusterlimit`, distinto dal limite fisso dei cluster Analytics.
Se raggiunto senza filtro sul tempo video, tabella nascosta e CSV cumulativo fermato prima delle righe cluster.
Un filtro permette righe limitate con avviso: restringere non garantisce dati completi. Heatmap supplementare
e tabella condividono il sottoinsieme conservato.

La modalità CSV complessiva include tutti gli studenti consentiti indipendentemente dal singolo selezionato,
conservando i filtri di accesso. I bookmark restano conteggi, senza etichette/timestamp privati in ogni formato.
Il ricalcolo usa segmenti conservati validati dal server e durata salvata; non ripristina evidenze eliminate.
Le intestazioni italiane del tempo video ora dicono «Tempo del video»; valori/ordine colonne CSV invariati.

Dopo il precedente lavoro web e la decisione App, il manutentore richiede sottotitoli VTT multilingue prima dei
provider autenticati. Le trascrizioni multilingue esistenti restano distinte. Qui si registra solo l’ordine:
selettore HTML5/lifecycle file e fattibilità provider esterni saranno implementati/provati nella fase dedicata.

## Scope corrente reset e review

Il reset del singolo studente ora verifica il permesso gestione anche sul server, oltre che nell’interfaccia:
vedere i [permessi](16_ROLES_CAPABILITIES.md#autorizzazione-reset-del-singolo-studente-nel-report). La review
assistita copre tutte le 997 chiavi negli otto pacchetti, senza equivalere ad approvazione madrelingua.
Sono dichiarati otto PASS manuali su 1.7.248/Moodle 5.0/Academi/Windows 11/Chrome/NVDA; manca copertura 5.3.
Gate candidata pendenti: attesi PHPUnit 379/4076 e Behat 52/1154. Lavoro web/App prima della fase concordata
VTT multilingua, poi base autenticata e OneDrive/SharePoint, Zoom e Google Drive.

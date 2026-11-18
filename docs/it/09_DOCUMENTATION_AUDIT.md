# Audit della documentazione

Baseline: VideoTrack **1.7.260** (`2026111800`).

## Seconda CI 1.7.260 e correzione del contratto AJAX

Il run `44358218951`, commit `d9a4bcb23b37b4b4155b091f34d812a341047dc5`, corrisponde
ai 462 blob/permessi sorgente e ai sei digest degli artifact allegati. Ogni job
supera **58 scenari Behat / 1241 step**, senza retry, Grunt e strict **10/0/0**.
Passano le regressioni su conservazione handshake e avviso di indisponibilità.
Il job qualità supera PHPCS/lint su 182 PHP distribuiti più l'helper repository,
PHPDoc/metadati e 206 contratti savepoint.

Ogni suite PHPUnit esegue **463 test / 5122 asserzioni / 1 fallimento**, in
`ajax_contract_test::test_sesskey_is_checked_before_context_loading`. Il contratto
statico cerca ancora il vecchio nome del caricamento contesto nelle cancellazioni.
Il controllo sesskey e il caricamento esplicito del partecipante sono presenti
nell'ordine corretto. Il contratto aggiornato assegna a ciascuno dei nove endpoint
il metodo previsto e verifica che la mappa copra tutti i servizi AJAX dichiarati.
Conserva tutte le asserzioni di presenza/ordine per ciascun endpoint.

PHPMD torna a **31** advisory: i due nuovi rilievi della 260 sono rimossi e le altre
identità normalizzate corrispondono alla 259 accettata. PHPStan/Psalm non riportano
errori configurati; Psalm elenca **743/771** informazioni (una in più per ramo
rispetto alla 259, di tipo non stampato). I lifecycle sono saltati dopo il fallimento
Unit, non superati.

Questa patch incrementale parte dal secondo commit fallito sopra indicato, su
`release/v1.7.260`. Cambia soltanto il test statico e le evidenze/inventari funzioni
paralleli; codice applicativo, AMD, lingue, schema, servizi, gate CI, versione e dati
sono identici byte per byte. Restano **1.7.260 / 2026111800**, 463 metodi di test e
1264 callable PHP nominati. Serve nuova CI candidata prima del merge; seguono
accettazione main/pacchetto/server. Conservare il dataset demo.

## Prima CI 1.7.260 e correzione

Il run `44349419297`, commit `fcaa45fe518633cd55323f5eaf51311ed914ca6a`, fallisce
tutti i sei job. I 462 blob/permessi sorgente, i sei digest CI e i sei digest faildump
coincidono con il run ufficiale. Ogni suite esegue **463 test / 5141 asserzioni /
1 fallimento** e **58 scenari Behat / 1236 step**, con uno scenario fallito e due
step saltati. I due retry automatici falliscono sullo stesso scenario. I 18 snapshot
HTML mostrano il blocco previsto della sorgente come pagina di errore fatale Moodle,
senza iframe del fornitore.

Il test della scheda precedente ipotizzava erroneamente l'assenza di segmenti dopo
l'avvio: l'handshake normale crea già un record di audit `playstart`. La regressione
corretta fotografa tutti i segmenti/stati prima della disabilitazione e ne verifica
la conservazione esatta dopo il rifiuto, mantenendo l'asserzione sul blocco.
La vista presenta ora un avviso normale con ritorno al corso, prima di completamento
su apertura, presa visione e avvio del player. Lo scenario mantiene i controlli e
verifica anche assenza di player/errori fatali nel DOM e ritorno funzionante al
corso. Il rifiuto di API/scritture rimane rigoroso.

Il job qualità supera PHPCS/lint su 182 PHP distribuiti più l'helper repository,
PHPDoc/metadati e 206 contratti savepoint. Grunt e strict **10/0/0** passano nei sei
job. I lifecycle sono saltati dopo i fallimenti, non superati. PHPStan/Psalm non
riportano errori configurati; Psalm conta 742/770 informazioni. PHPMD riporta **33**
advisory, due in più della 259: parametro booleano del caricamento contesto e nuova
dipendenza del modulo.

La correzione sostituisce il flag con ingressi espliciti per riproduzione/accesso
del partecipante, conservando login/contesto/capability/partecipazione e proprietà
nelle cancellazioni. Selettore e validazione disponibilità passano agli helper di
definizione/validazione del modulo già esistenti. I due nuovi rilievi PHPMD sono
affrontati nel sorgente; il nuovo report nativo dovrà confermare il conteggio.
Regole, baseline e gate CI non vengono allentati.

Questa patch è **incrementale dal commit 1.7.260 fallito**, su `release/v1.7.260`,
non la precedente patch 259→260. Release/versione restano **1.7.260 / 2026111800**.
Restano 462 file repository/438 distribuiti, 182 PHP distribuiti e 463 metodi
PHPUnit; i callable PHP diventano 1264 (1257 distribuiti + 7 helper). Servono nuova
CI candidata, CI main e accettazione del pacchetto reale/server. Il dataset demo
resta conservato. Nessuna modifica ad AMD/schema/servizi/credenziali.

## Baseline 1.7.259 accettata

La baseline è main `243983ae3cc93af7c8a702014d4e58cad0d0f53f`, albero
`4aaddc578a96e73680853d7edb1768fd36124246`. Lo ZIP reale ha 433 file corrispondenti
ai blob relativi; tutti i 457 blob/permessi repository corrispondono alla baseline
isolata. SHA-256 del pacchetto:
`30b8c99c21fc01be2e4e51ddef40fa9458e3ccf908d54ec7ff465d03903e82c8`.

CI candidata corretta e main accettate superano i sei job, PHPUnit 448/5071,
Behat 54/1194, strict 10/0/0 e i percorsi selezionati di upgrade storico/interrotto/
release precedente, ripetizione e uninstall popolato. Il report server reale
conferma PHPCS/lint 181 puliti, Unit 448/5071 su Moodle 5.0/5.3 e build Grunt invariati.
Gli output rigorosi del maintainer sono 10/0/0 sui due siti. Sono risultati della
baseline, non esecuzione delle nuove regressioni.

Restano advisory 31 rilievi PHPMD e 742/770 informazioni Psalm; PHPStan/Psalm non
riportano errori nei perimetri configurati. Restano tre vecchi harness Node falliti:
assert Privacy/backup obsoleti e stub core/str assente nel tracker. Il manuale
accessibilità Moodle 5.3 resta in standby. Conservare il dataset demo.

Una verifica locale estesa dei 19 harness Node storici produce due PASS e 17
fallimenti identici alla 1.7.259 accettata dopo normalizzazione dei percorsi. Questi
fallimenti storici non sono risultati nativi Moodle né regressioni introdotte.

## Perimetro della candidata corrente

Tre interruttori indipendenti, abilitati per impostazione predefinita, si applicano
a modulo, callback, ingresso vista/completamento, nuova telemetria e file locali.
Il valore disabilitato esistente resta esplicito in modifica. Tutte le sorgenti
disabilitate non producono ripieghi alla creazione. Report e dati sono conservati;
le cancellazioni mantengono i controlli di accesso e il validatore registra i valori
effettivi. Vedere [policy sorgenti](31_SOURCE_POLICY.md).

Il repository Zoom separato e opzionale è una valutazione architetturale.
Tempi/controlli del player, autorizzazioni e distribuzione fornitore → browser
restano gate futuri. Diagnostica Microsoft, AMD, schema, upgrade e contratti dei
servizi rimangono invariati.

## Inventari e confini delle evidenze

L'albero ha 462 file repository / 438 distribuiti, 182 PHP distribuiti più un helper
lifecycle repository, 1264 callable PHP nominati (1257 distribuiti + 7 helper),
654 callable AMD e otto lingue con 1013 chiavi/placeholder corrispondenti.
L'inventario selezionato ha 371 voci (366 distribuite + cinque solo repository).
I metodi PHPUnit sono 463 (15 nuovi), con quattro scenari browser nuovi e 58 scenari
Behat espansi attesi, da confermare mediante esecuzione nativa.

I controlli locali sorgente/patch/inventari non dimostrano il comportamento PHP.
PHPCS, lint, PHPUnit, Behat, lifecycle e analizzatori devono essere eseguiti nella
CI candidata; seguono CI main, identità del pacchetto reale, controlli server e
validazione rigorosa. Non si dichiara un risultato nativo della candidata né accesso
a un fornitore reale. Guide e inventari paralleli descrivono la candidata.
La cronologia appartiene a CHANGELOG e ai relativi tag.

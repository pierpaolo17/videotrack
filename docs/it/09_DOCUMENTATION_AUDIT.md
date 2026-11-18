# Audit della documentazione

Baseline: VideoTrack **1.7.261** (`2026111801`).

## Prima CI 1.7.261 e correzione della stessa versione

La run `44388798465`, commit `d8ab79681ecbb5c4b9e30dba3bbbd7f12625b62a`,
albero `003728bd6ab13fd638a6c5295bdf5b500ba3231f`, mostra tutti i sei job verdi.
I sei digest degli ZIP caricati coincidono con gli artifact ufficiali; tutti i 468
blob/modi coincidono con il candidato consegnato. Ogni job supera Unit **472/5183**,
Behat **61/1268** senza ritentativi, strict **10/0/0** e Grunt. Controlli canonici su
**185 PHP distribuiti +1 helper repository**, PHPDoc/metadati e 206 contratti savepoint.
Tutte le tre genealogie lifecycle selezionate passano su 5.0/5.3 MariaDB; ricostruiti
sei hash degli snapshot e verificate 126 prove effettive dei contenuti file. PHPMD
conserva i medesimi 31 avvisi revisionati della baseline.

Gli step consultivi di analisi statica riportano un rilievo PHPStan e due errori
Psalm: `settings.php` dipende da `$hassiteconfig` fornita dal chiamante; il modulo
caricato dall’autoloader dichiara `global $CFG` a livello file. Psalm riporta inoltre
746/774 elementi informativi, senza stamparne i tipi. I nuovi errori non sono
accettati soltanto perché il workflow è verde. La correzione verifica direttamente
il permesso di sistema e usa un riferimento locale tipizzato a `$GLOBALS['CFG']`
per caricare la libreria dei moduli di core. Non aggiunge soppressioni, funzioni,
test, chiavi lingua, cambi schema o eccezioni CI. Conserva integralmente le
regressioni di modulo, sessione e accesso.

Questa patch è **incrementale da d8ab7968 sul ramo release/v1.7.261**, non la patch
completa 260-to261. Restano **1.7.261 / 2026111801**, 472 metodi di test e 61 scenari
browser. Controlli sorgente e confronti patch sono locali; la CI nativa del candidato
corretto e poi l’accettazione main/ZIP/server sono da svolgere. Nessuna prova su
tenant Microsoft collegato o telemetria player dichiarata. Conservare la demo.

## Baseline tecnica 1.7.260 accettata

Il main accettato `4d9fe8b0eb8b4a6587992f4ac86b2bec9d1dcb65` ha albero
`6b4041b583a66db1ebbe5d623b7b1e0ab0156a12`, identico al candidato
`a98ef230429373259943b7bc9634dafe5512ea2c`. Le run candidato `44365252939`
e main `44374062516` superano tutti i sei job ciascuna. Verificati i sei digest
ufficiali degli artifact e i 462 blob/modi sorgente per run. PHPUnit **463/5144**
su entrambi i rami; Behat **58/1241** senza ritentativi. Strict **10/0/0** e Grunt
completato. Controlli canonici su 182 PHP distribuiti più helper repository. Tutte
le tre genealogie lifecycle selezionate passano su 5.0/5.3 MariaDB, con upgrade
reale, ripetizione e disinstallazione popolata. Ogni run riproduce sei hash degli
snapshot e 126 verifiche effettive dei contenuti file.

Lo ZIP reale 260 contiene **438 file**, tutti corrispondenti al main accettato;
commento archivio uguale al commit, SHA-256
`f2557bf69f15af618d4a4c1a20045f3b44ddad32c59ac354a889fc87fee1b9d5`.
Il report `report_completo_videotrack_1.7.260_20261118_203639.zip` ha SHA-256
`9ef587aa5bca60361fa066a4c34efdf8d4c98447ca23a2caf3a8dd7c5bdc072e`,
dieci log e otto comandi richiesti superati. Unit server **463/5144** su entrambi
i siti; PHPCS/lint **183 puliti** in una verifica condivisa del codice identico,
non due esecuzioni canoniche distinte. Gli hash Grunt registrati prima/dopo sono
uguali e le mappe corrispondono ai sorgenti; non si dichiara ricostruzione autonoma
dell’hash aggregato server. Gli strict del manutentore passano **10/0/0** su entrambi,
con elementi/righe voto **0/0** e **34/34**. Schema: 7 tabelle/151 campi/22 indici
espliciti/22 chiavi esterne; nove AJAX, otto lingue da 1013 chiavi e 48 gruppi AMD.

Restano 31 avvisi PHPMD revisionati; PHPStan/Psalm senza errori configurati. Psalm
mostra 743/771 elementi informativi. Il debito dei vecchi harness Node autonomi e
l’avviso Browserslist restano registrati, non risolti. La verifica manuale separata
di accessibilità Moodle 5.3 resta **STANDBY**. Conservare il dataset demo.

## Perimetro 1.7.261 e limiti dell’evidenza

La nuova [verifica Microsoft dalla GUI](32_MICROSOFT_WEB_CHECK.md) richiede il
permesso di configurazione sito nel sistema e un modulo POST/sesskey. GET legge
solo lo stato locale. Riutilizza il servizio Graph/OAuth: core può aggiornare le
credenziali, senza importare/trasmettere file o modificare attività e demo.
Diagnostica ridotta con escape e pagina non memorizzabile in cache. ID validati
prima della verifica. Nessuna sorgente cloud, integrazione player, rinnovo link,
nuovo schema, servizio o AMD.

Scritti nove metodi unitari nativi e tre scenari browser deterministici;
**l’esecuzione nativa del candidato 261 corretto è da svolgere**, come l’accesso a un tenant reale collegato.
La prima esecuzione 261 ha superato le nuove regressioni, ma non esegue questa correzione.
Controlli sorgente/lessicali/stile, parità lingue, inventari, schema/AMD invariati
e confronti applicazione/inversione patch sono locali e non sostituiscono PHP/Moodle.
Servono sei job CI candidato prima del merge, sei main, ZIP reale/report server e
due strict. Nessuna modifica Git remota dall’assistente. Repository Zoom, Google
e App restano tappe successive.

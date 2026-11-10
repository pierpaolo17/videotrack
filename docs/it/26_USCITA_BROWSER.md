# Audit di uscita U-007: automazione browser

Albero corrente: VideoTrack **1.7.246** (`2026111005`). Decisione registrata il 2026-11-09.

## Decisione

**U-007 è chiuso sulla baseline 1.7.240 accettata.** La matrice browser/provider/ruoli essenziale ha esecuzione
reale nei sei job Moodle/PHP/database indicati sotto. La 1.7.241 accettata ha consolidato evidenze e passaggio
all’audit manuale; ZIP reale/server/strict e sei job CI release/main sono superati. La candidata linguistica
1.7.246 è documentata nella [review mirata](27_REVISIONE_LINGUE.md).
U-017 resta chiuso. U-020/accessibilità manuale, review semantica lingue e gate produzione integrati sono
separati e restano aperti. Questo documento non certifica accessibilità o deploy con provider pubblici.

## Baseline accettata esatta

| Evidenza | Valore accettato |
|---|---|
| Release reale / versione | 1.7.240 / `2026110603` |
| File distribuiti nello ZIP | 408 |
| SHA-256 ZIP | `be83a2bbf806c6e95bc05d971b9a12d291575e8909b2b9a1f45af5930e71ae1e` |
| Commit main dopo merge | `2d047f5c59792e4c8798a31209f19cfa9ecc15f7` |
| Candidata corretta | `3447cdfbadcef64332f21655b17a58f5d27045ba` |
| Run CI main / candidata | `42634054361` / `42630177127`, sei job superati, tentativo 1 |
| Uguaglianza repository | Tutti i 430 blob Git coincidono tra candidata e main; coincidono i 408 file distribuiti |
| Browser per job | 50 scenari / 1.074 step, tutti passati |
| PHPUnit per job | 379 test / 4.076 asserzioni, tutti passati |
| Validatore strict | 10 PASS / 0 WARN / 0 FAIL nei job e nelle due istanze server fornite |
| SHA-256 report server | `5a9f16b2c4bc89d0fa951855be8728cfceee3cc5abbe4fa4eb50b5a23660d577` |

Il report server reale esegue **canon, lint, grunt, phpunit** su Moodle 5.0/5.3, PHP 8.3.6: 166 file PHP
puliti, PHPCS zero errori/warning, PHPUnit 379/4076 e Grunt Done. Gli hash build AMD prima/dopo coincidono:
`325cbe9919b087c4d4e1e01cebd7108530e943c212c4699aa6525adcd3f7db51`. Il report non esegue Behat né il comando
locale `--preset=full`. Evidenze browser e milestone multiversione provengono dalla CI registrata, senza
dedurre esecuzioni server. PHPMD conserva 24 segnalazioni revisionate; i gate analisi configurati passano,
con informativi Psalm 732/760 invariati. Il debito consultivo revisionato non viene dichiarato nullo.

## Milestone browser multiversione

| Moodle | PHP | Database | Browser | Behat | PHPUnit |
|---|---|---|---|---|---|
| 5.0 | 8.2 | MariaDB | Chrome | 50/1074 passati | 379/4076 passati |
| 5.0 | 8.2 | PostgreSQL | Chrome | 50/1074 passati | 379/4076 passati |
| 5.1 | 8.3 | MariaDB | Chrome | 50/1074 passati | 379/4076 passati |
| 5.2 | 8.3 | MariaDB | Chrome | 50/1074 passati | 379/4076 passati |
| 5.3 | 8.3 | MariaDB | Chrome | 50/1074 passati | 379/4076 passati |
| 5.3 | 8.3 | PostgreSQL | Chrome | 50/1074 passati | 379/4076 passati |

Sono coperti entrambi i rami di confine e tutti i quattro rami Moodle supportati. I due database sui rami
di confine completano i job intermedi; sei ambienti rappresentano comunque un solo profilo browser.

## Criteri di uscita originari ed evidenze

| Criterio originario | Evidenza registrata | Decisione |
|---|---|---|
| Flussi essenziali playback/interazioni/integrità/ruoli coperti dal browser | Matrice esatta file/contratti nella [guida browser](22_TEST_BROWSER_BEHAT.md); tutti i 50 casi eseguiti | Soddisfatto |
| HTML5/YouTube/Vimeo conservano contratti seek/resume/tracking | Adapter produzione, media/SDK double locali e scritture server accettate per completamento, gap, rollback, fine/replay/velocità, attenzione e ruoli | Soddisfatto nello scope adapter |
| Fixture e locator stabili | Idnumber gruppi, rifiuto atteso e distinzione selettore/dati corretti; entrambi i run finali superano i 50 casi in ogni job | Soddisfatto sull’albero registrato |
| Matrice completa passa su Moodle 5.0 e 5.3 | Quattro job di confine con due database sopra | Soddisfatto |
| Milestone completa 5.0–5.3 senza regressioni specifiche | Milestone candidata/main su sei job, ZIP esatto corrispondente e controlli server forniti | Soddisfatto |

La roadmap originaria preferisce harness locali deterministiche e verifica i **contratti degli adapter**
esterni, evitando la disponibilità della rete pubblica come dipendenza automatica. Firefox/Safari/altri
motori e disponibilità degli iframe pubblici non erano prerequisiti aggiuntivi dei criteri di uscita.
Questo audit supera il testo recente che teneva U-007 aperto solo per tali controlli. Nessun test, guardia
o job CI viene eliminato o indebolito.

Il replay prova che la copertura già vista non viene contata due volte. Retry request-id ed effetti
completamento una sola volta hanno anche casi PHPUnit comportamentali in `tracker_test.php` e
`tracking_effects_test.php`. Non si dichiara un esperimento browser con timeout trasporto forzato né il
prodotto cartesiano di ogni ruolo/provider/azione. Casi UI/resume con seed e visione guadagnata senza seed
restano distinti esplicitamente.

## Lavoro residuo e condizioni di riapertura

- Eseguire il [protocollo manuale U-020](19_ACCESSIBILITY.md#protocollo-evidenze-manuali-u-020) su tema/browser/tecnologie assistive effettivi.
- Registrare esiti provider reali, cookie/consenso e accessibilità contenuti nell’accettazione deploy/contenuti.
- Completare review semantica degli otto pacchetti lingua; chiavi/placeholder uguali non approvano la traduzione.
- Eseguire il gate integrato produzione/lifecycle/privacy/benchmark su uno ZIP esatto.
- Poi valutazione Moodle App, base comune provider autenticati, OneDrive/SharePoint, Zoom Cloud Recording,
  Google Drive e consolidamento fra provider, nell’ordine concordato.

Una regressione adapter/UI comprovata riapre la cella U-007 interessata; un nuovo provider richiede contratti
propri. Altri motori si provano e registrano quando pertinenti al deploy, senza dedurli dalla milestone Chrome.
L’audit manuale non genera automaticamente una release: registrare un esito verde; produrre una correzione
mirata solo per un finding concreto. Non riaprire U-017 strutturale per le metriche consultive.

U-020 resta aperto ed è **rinviato su richiesta del maintainer (2026-11-10)**. Questa modifica non
esegue né approva alcun caso manuale. Il lavoro indipendente sulle lingue può proseguire; il gate finale
di produzione richiede ancora l’audit manuale e le eventuali correzioni.

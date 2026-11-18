# Audit della documentazione

Baseline: VideoTrack **1.7.260** (`2026111800`).

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
lifecycle repository, 1261 callable PHP nominati (1254 distribuiti + 7 helper),
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

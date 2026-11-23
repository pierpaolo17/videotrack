# Audit della documentazione

VideoTrack **1.7.269** (`2026112300`), basata sulla **1.7.267** (`2026112100`) accettata.

## Evidenze della baseline

La 267 accettata corrisponde a main `418475d361cfd65b06b35875288a0b1b68aa3e12`
e albero `87b084b6a85fc39eaed2a70db608f05ea2331ab5`. Candidato 44908095172 e main
44910680696 superano sei job ciascuno, PHPUnit 583/6243, Behat 73/1390, strict 10/0/0
e Grunt. Il pacchetto effettivo ha SHA256
`3fbc345220877f1449e3f9a1dd03341b4b9c29ff002637b0eeb7ff94813278dd`.
Il server supera canonici/lint condivisi su 196 PHP, PHPUnit 583/6243 separatamente
su Moodle 5.0/5.3 ed entrambi i comandi Grunt. Queste sono evidenze della 267, non
esecuzioni della 269.

## Hotfix corrente

Il [documento sul recupero del tracciamento](39_TRACKING_RECOVERY_HOTFIX.md) descrive
cause, comportamento e limiti. La ricerca Microsoft 268 è sospesa ed esclusa;
resta la diagnostica Microsoft già presente nella 267. Nessuna modifica a schema,
9 servizi AJAX, disponibilità sorgenti o conservazione token. Non introduce
ottimizzazioni delle query Analytics.

I test Node locali superano l’harness tracker e sei gruppi di regressione sia sui
sorgenti sia sulle build distribuite. Cinque gruppi riproducono il problema sulla
267 e passano con l’hotfix. Sono controllati mappe/sorgenti, contratti lessicali,
inventari, parità lingua, applicazione e inversione delle patch.

PHP/Moodle/MariaDB non sono disponibili localmente: lint/PHPCS, PHPUnit, Behat,
Grunt Moodle, analisi statica e accettazione server della 269 restano da eseguire.
I 12 gruppi AMD modificati sono compilati con Babel; non viene dichiarata
un’esecuzione Moodle o browser reale. La CI mantiene i gate già presenti e aggiunge
le regressioni Node su sorgenti e build.

Aggiunti 12 metodi PHPUnit e due scenari Behat; rafforzate le asserzioni sulla
conservazione della copertura in tre scenari di pausa casuale. Ricalcolo ordinario
e processi Privacy mantengono il comportamento esistente. Il recupero storico
richiede la nuova selezione esplicita del docente, aggiunge righe derivate
riconoscibili e conserva gli originali. Non ricostruisce prove mancanti o ambigue.

## Inventari e vincoli

500 file repository, 475 distribuiti, 197 PHP distribuiti più helper lifecycle;
1489 funzioni PHP nominate, 660 funzioni AMD nominate, 595 metodi unitari,
75 scenari Behat espansi, otto lingue con 1060 chiavi e 48 gruppi AMD.
Inventari bilingui aggiornati alle posizioni sorgente effettive. Lo schema conserva
7 tabelle, 151 campi, 22 indici espliciti e 22 chiavi esterne.

I precedenti 31 rilievi PHPMD e 751/779 informazioni Psalm non classificate sono
soltanto baseline 267: nessuna identità dei rilievi 269 è dichiarata prima delle
analisi native. Conservare dataset demo, standby dell’accessibilità manuale 5.3
e roadmap provider/App. Le nuove funzioni Microsoft saranno ribasate sull’hotfix
soltanto dopo la sua accettazione.

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

501 file repository, 475 distribuiti, 197 PHP distribuiti più helper lifecycle;
1490 funzioni PHP nominate, 660 funzioni AMD nominate, 595 metodi unitari,
75 scenari Behat espansi, otto lingue con 1060 chiavi e 48 gruppi AMD.
Inventari bilingui aggiornati alle posizioni sorgente effettive. Lo schema conserva
7 tabelle, 151 campi, 22 indici espliciti e 22 chiavi esterne.

I precedenti 31 rilievi PHPMD e 751/779 informazioni Psalm non classificate sono
soltanto baseline 267: nessuna identità dei rilievi 269 è dichiarata prima delle
analisi native. Conservare dataset demo, standby dell’accessibilità manuale 5.3
e roadmap provider/App. Le nuove funzioni Microsoft saranno ribasate sull’hotfix
soltanto dopo la sua accettazione.

## Esito della prima CI 269 e candidata correttiva

Il [run 45227903298](https://github.com/pierpaolo 17/videotrack/actions/runs/45227903298)
ha eseguito il commit `a8d58ffd21a7558b05e25bc5433344dca0aa1a16`, albero
`80a28a42cc2e3ff3002b8e7dd35b5bc5902aa53e`. Tutti i sei job falliscono. Ciascuno
esegue 595 metodi PHPUnit/6268 asserzioni con un solo fallimento in una vecchia
asserzione sul testo sorgente; i 12 nuovi metodi passano. Behat supera 73 scenari su 75;
i due scenari nuovi falliscono anche nei due retry automatici. YouTube raggiunge
circa 7,2 secondi in un’attesa di 7 secondi, HTML5 riparte vicino a 3 e raggiunge circa 9,4
in un’attesa di 6: il timeout non dimostra un player bloccato. Passano i 73 scenari
precedenti, incluse le verifiche rafforzate sulla conservazione dopo pausa casuale.

Il job qualità rileva 51 errori PHPCS e un warning per `$CFG->admin` deprecato.
Gli strumenti sono passati da PHPCS 3.13.6/moodle-cs 3.7.0 a 4.0.4/3.9.2, esponendo
anche spaziature di intestazione già presenti. Superati lint 197/197, PHPDoc e controlli
helper dove eseguiti; tutti i sei validator danno 10/0/0. Superati anche 12 gruppi Node
più harness tracker per matrice. Lifecycle saltato dopo i fallimenti, non accettato.
PHPMD su 50 riporta 35 avvisi rispetto ai 31 precedenti; le 757/785 informazioni Psalm
rispetto a 751/779 restano da classificare.

La correttiva sistema formattazione e DocBlock senza cambiare i token di recupero o
completamento. Aggiorna l’asserzione wallclock al cursore catturato conservando gli
altri controlli. Aggiunge un’attesa dedicata e limitata della riproduzione reale prima
delle due asserzioni 12–17 secondi, che restano invariate. Non modifica attese brevi
esistenti o retry. Il link impostazioni usa la navigazione core anziché la proprietà
admin deprecata. Nessun cambiamento a sorgenti AMD, ledger live, schema o politica
di recupero; restano tutti i 595 metodi unitari.

Grunt termina la compilazione, ma il controllo di freschezza respinge 24 file generati.
Le build Babel della prima patch non sono quelle canoniche Moodle. Il registry npm
locale risponde 403: non viene simulata una compilazione nativa. Il gate originale
resta obbligatorio. Poiché plugin-CI 4.5.13 ripristina i file iniziali dopo il confronto,
un secondo comando Grunt protetto e limitato alla CI raccoglie l’output reale, hash
sorgenti/commit e mappe esatte. Gli artifact immediati e i report normali includono
`native-amd.zip`; le regressioni sui compilati usano il vero output installato.
Servirà poi integrare quelle build verificate e rieseguire tutti i gate: questa
correttiva preparatoria da sola non chiude il problema Grunt.

La casella recupero è temporanea: dopo la bonifica verificata delle attività della
produzione 5.0 rimuovere interfaccia e procedura straordinaria in una release successiva,
conservando prove recuperate e ricalcolo normale. Demo 5.3 e ricerca Microsoft restano
in pausa. I controlli nativi della candidata corretta sono ancora da eseguire.

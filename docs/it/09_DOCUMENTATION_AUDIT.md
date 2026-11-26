# Audit della documentazione

Candidata corrente: VideoTrack **1.7.273** (`2026112501`).

## Baseline accettata

ZIP 272 effettivo SHA256 `4776fb1dda089a8e1ed701a477c751d44d5b78b5f4ba0c47399671879a5a2e64`.
I 493 file distribuiti coincidono con main `18305f5e3c62fdd25a128d9bc99c421323658767`,
albero `789f590de9d0f90a3a21eee1135d0704881b6d0b`. La run main 45658185075, tentativo 1,
supera sei job, PHPUnit 640/6618, Behat 82/1541, strict 10/0/0, build nativi e sei hash
di conservazione lifecycle ricostruiti indipendentemente. Il report server 20261125_184426
supera canon/lint 207 PHP condivisi, Grunt nativo e PHPUnit 640/6618 su 50/53. Entrambi
gli strict dell’utente sono 10/0/0 e il replay YouTube reale da reazioni è confermato.
Restano visibili 40 rilievi PHPMD e una segnalazione Psalm sulla guardia standard,
oltre a 763/791 informazioni. PHPStan è pulito. Non si attribuiscono alla 273 gli esiti 272.

## Modifica corrente

La [guida durata](43_SOURCE_DURATION_VALIDATION.md) documenta apertura impostazioni,
due pulsanti di salvataggio e Annulla, qualsiasi campo sia stato modificato. Una
durata superiore a quella nota blocca il salvataggio; durate uguali o inferiori
restano valide. Il metadato non disponibile consente un salvataggio manuale esplicito.
Annulla controlla e avvisa, poi scarta senza salvare. Le azioni intermedie che
aggiungono campi restano immediate. Sono gestiti risposte superate e doppi invii;
requestSubmit conserva il normale trattamento del modulo Moodle.

L’input temporaneo ha id esplicito e un test coerente con la configurazione AMD.
PHP accetta solo un piccolo rapporto corrispondente alla sorgente inviata; il
metadato del browser non viene dichiarato come una risposta API autenticata dal server.
Non si aggiungono impostazioni salvate, migrazioni, riscritture di tracking,
credenziali API o ricalcoli automatici. Restano fix tracking/replay, dati demo e
opzione straordinaria di recupero storico già accettati.

Conteggi: 523 file repository / 496 distribuiti, 207 PHP distribuiti, 1591 callable PHP,
668 callable AMD, 645 metodi unitari, 82 scenari Behat / 1541 passi, 8×1078 chiavi e 48 set AMD.
Schema invariato: 7 tabelle / 152 campi / 22 indici espliciti / 22 chiavi esterne; 9 servizi AJAX.
Gli inventari mantengono posizioni effettive e nomi AMD ripetuti in punti distinti.

## Build nativo e gate restanti

Verificato l’archivio del manutentore Moodle 5.0, SHA256 `b5c4292a4d9362e4ec95ad3e009273de75d1503c6853c67272351926cb650fb3`.
Node 22.23.4 / npm 10.9.9; npx --no-install grunt amd termina con 0. Coincidono 48 hash
sorgenti e 96 hash build, mappe, fixture e log. Cambiano solo modulo del form e due
output. Passano 10 gruppi sui sorgenti e 10 sui compilati, nel log effettivo e dopo
l’incorporazione. I sorgenti/build 272 falliscono tutti i 10 gruppi come previsto.
Passano tracker harness e 9 gruppi tracking sia sorgenti sia compilati. Il runner
indica il ripristino verificato degli originali. I due step browser di salvataggio
usano un SDK a durata fissa limitato al video riservato e cliccano il vero pulsante
nello stesso task JavaScript. Restano 82 scenari/1541passi, senza metadati esterni live.

Restano PHP/Moodle, PHPCS, PHPUnit, CI/lifecycle/server 273 e prova reale del modulo
durata. Sono preparati cinque metodi unitari PHP e le asserzioni sulla configurazione;
non si dichiarano nuove esecuzioni PHP/Moodle né prove browser/provider reali.
Restano aperti HTTPS/account Microsoft e roadmap provider/App.

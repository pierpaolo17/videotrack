# Completamento delle correzioni di sicurezza

VideoTrack **1.7.258** (`2026111700`).

La rimozione elimina fisicamente la riga della nota/segnalibro del proprietario.
Parametri e risposte AJAX restano compatibili. Login, contesto, partecipazione e sesskey
precedono i retry: un id assente è un successo senza scritture; righe esistenti di altro
utente o tipo vengono rifiutate. Un lock Moodle serializza le rimozioni e la transazione
comprende un solo evento senza testo privato. Una vecchia riga già marcata eliminata viene
rimossa senza duplicare l'evento.

Backup utente ed export Privacy escludono gli elementi personali eliminati; il ripristino
scarta quelli marcati eliminati nei vecchi XML. Strumenti attivi e storico reazioni mantengono
i propri contratti. Backup creati prima della cancellazione possono contenere copie allora
attive: la loro retention è distinta e il ripristino non può dedurre una cancellazione
successiva da una vecchia copia attiva. Nessuna migrazione generale o pulizia della demo.

Il recupero dello schema passa in db/upgradelib.php, riservato all'upgrade. Install/uninstall
conservano il recupero voti DML. Per contesti validi la disinstallazione usa
completion_info::delete_all_state; solo i moduli orfani richiedono DML di completamento
circoscritto. Voti ambigui/orfani di pre-produzione conservano il DML transazionale verificato:
proprio quelle righe malformate possono impedire alla Grade API di risolvere il contesto.

I controller usano data_submitted() con i controlli sesskey/capability esistenti. Il download
Analytics usa POST, con filtri e sesskey nel corpo; GET viene rifiutato prima dell'evento
e dell'output. Il server genera chiavi opache di ripresa per sito, studente partecipante,
modulo, sorgente e video. YouTube/Vimeo ignorano le vecchie chiavi condivise e disabilitano
lo storage per identità incomplete; HTML5 conserva la ripresa server. I suggerimenti del
browser non diventano evidenza di visione validata. L'indice pulisce l'HTML dei nomi sezione
senza ripetere i filtri: difesa aggiuntiva, non una XSS Tiles dichiarata come riprodotta.

| ID | Status / Stato |
|---|---|
| SH-01/02/03/05/08/09 | 257 accettata con regressioni native. |
| SH-04 | Cancellazione/retry/Privacy/backup implementati; prove native258 da eseguire. |
| SH-06 | DDL solo upgrade; API completamento nei contesti validi; DML orfani/voti ambigui motivato. |
| SH-07 | API dati inviati Moodle e guardie sesskey esistenti. |
| SH-A1/A2 | Ripresa isolata e Analytics POST implementati; prove native258 da eseguire. |
| MS-14 | Output formato corso pulito; nessuna XSS Tiles dichiarata riprodotta. |
| MS-38 | Recupero transazionale verificato nella257; corpi DML conservati. |

Baseline reale accettata257/main d6e8705e:422 file corrispondenti, PHPCS/lint server173
puliti, PHPUnit414/4695 e strict10/0/0 su50/53, build AMD invariati, CI candidata/main a sei
job, Behat52/1154 senza rerun e tre percorsi lifecycle nei job50/53MariaDB previsti.
SHA-256 pacchetto:59fff2d7214a6828c1544b896d489ecebb8bbefb1821e72c6a3384ca845cc504.

La candidata258 aggiunge15 metodi PHPUnit e due scenari browser, compreso backup/ripristino
reale con vecchio XML modificato. PHP/Moodle non disponibili localmente: prove native
PHPCS/lint/statiche/PHPUnit/Behat/lifecycle e accettazione di ZIP/server restano da eseguire.
Grunt AMD Moodle reale Node22 superato: solo due sorgenti e quattro build/mappe cambiano.
Smoke AMD e test adapter passano. Tre script Node storici falliscono ugualmente su257/258:
privacy_static_test e backup_restore_static_test hanno contratti precedenti ai refactor;
tracker_segment_test non ha la dipendenza core/str nel suo harness. È debito preesistente,
non test nativi superati. Un nuovo punteggio di sicurezza richiede una nuova scansione.
I rilievi nativi257 PHPMD28 e Psalm740/768 restano registrati, senza dichiarare zero warning.

Nessuna modifica a schema/savepoint/servizi/task retention/OAuth. Nessuna bonifica generale
dei dati pregressi, come richiesto; dataset demo conservato. Accessibilità manuale5.3 ancora
in standby; verifica visiva Tiles da eseguire. Dopo l'accettazione finale completa si passa
direttamente alla patch successiva, senza riconferma. Nuove sorgenti prima dell'App; prova
App → browser saltata.

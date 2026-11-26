# Copie delle attività, campi CSV e manutenzione del completamento

VideoTrack **1.7.274** (`2026112600`) conserva le impostazioni riutilizzabili,
chiarisce l’accesso ai campi CSV e ritira la casella temporanea di recupero storico.

## Duplicazione e salvataggio delle impostazioni

Il backup conteneva già velocità, scelte CSV e relazione con il Forum. Le correzioni
riguardano la loro elaborazione:

- Le velocità decimali usano nomi come `playbackspeed_0_5`. PHP trasforma i punti
  dei nomi ricevuti dal browser in underscore: definizione, valori iniziali e
  normalizzazione ora usano lo stesso nome. Gli alias storici con punto restano
  compatibili per le chiamate interne.
- Un salvataggio privo degli alias checkbox conserva i valori scalari già presenti
  per velocità e controlli. Le selezioni ereditate non modificate continuano a
  seguire i predefiniti del sito. Il CSV conserva le scelte disabilitate invece di
  cancellarle. Il valore vuoto continua a ereditare i predefiniti; `none` resta esplicito.
- Il ripristino disabilita inizialmente il link facoltativo al Forum e lo risolve
  in `after_restore`, quando tutte le mappature sono disponibili. Vale anche senza
  dati utente e con un Forum collocato dopo VideoTrack nel corso. Se manca la
  mappatura, conserva il Forum compatibile esistente soltanto sullo stesso sito
  e nello stesso corso originale/destinazione. Negli altri casi lascia il link
  disabilitato con avviso. Non sceglie un Forum estraneo soltanto per coincidenza di ID.

Non sono aggiunte migrazioni di schema o riscritture globali delle impostazioni.
La correzione vale per nuove copie e salvataggi successivi. Le copie che hanno già
perso preferenze richiedono riallineamento manuale rispetto all’originale desiderato.
La duplicazione continua a escludere visioni, reazioni, note e segnalibri degli studenti.

## Disponibilità CSV e preferenze salvate

Un valore compilato e la selezione nei predefiniti non attribuiscono da soli il
permesso di lettura. La disponibilità distingue i permessi reali Moodle dall’elenco
scelto per mostrare i campi identificativi nelle liste:

| Categoria | Ulteriore accesso consentito rispetto all’elenco identificativo |
|---|---|
| Amministratore del sito | Tutti i campi standard supportati e quelli personalizzati consentiti dalla visibilità amministrativa Moodle. |
| Nome utente, codice identificativo, istituzione, dipartimento | Permesso `moodle/user:viewalldetails` nel contesto effettivo, conservando i limiti sui campi nascosti. |
| Città e Stato | Permesso di vedere i dettagli del profilo e gli eventuali campi nascosti. |
| Email | Percorso identificativo già autorizzato oppure permesso Moodle sulle email del corso. |
| Telefoni e indirizzo | Elenco identificativo autorizzato esistente, oltre all’accesso amministrativo. |
| Campi personalizzati | API Moodle di visibilità e permesso profilo/identità; per i campi completamente nascosti serve il permesso di sistema. |

I campi non disponibili mostrano il motivo: configurazione dei campi identificativi,
permesso di lettura, campo nascosto o visibilità del profilo. Si usa il contesto
esplicito del corso, evitando la dipendenza dal corso globale. Il plugin non modifica
impostazioni identificative generali Moodle o capability dei ruoli.

Una casella disabilitata mantiene la preferenza salvata durante modifiche estranee.
Questo non rende esportabili i dati: al download restano i filtri sui permessi
correnti di chi esporta. L’amministratore può modificare normalmente quelle scelte.
Una checkbox contraffatta non aggiunge un campo nuovo non autorizzato. Restano il
caricamento utenti in blocco, la protezione delle formule CSV e i limiti dei report
sugli studenti visibili.

## Ritiro del recupero storico temporaneo

Il report non mostra e non legge più `recoverplayback`. Rimane il ricalcolo ordinario,
che usa evidenze conservate e validate dal server. Le righe `recovery269` già accettate
restano valide; l’installazione non cancella né rielabora automaticamente dati.
Il ricalcolo normale non aggiunge riparazioni. Il motore interno resta compatibile
con eventuale codice amministrativo esistente e con i test di idempotenza, senza
un percorso di attivazione dal browser. Installare questa versione non richiede
un nuovo ricalcolo.

## Verifiche e gate ancora aperti

La baseline è lo ZIP effettivo 273, corrispondente a main `e3097257` e albero
`e413a886`: passano sei job candidata/main, build native, entrambi i lifecycle
popolati, controlli server 50/53, validatori strict e prove manuali del maintainer.
La 274 non cambia sorgenti AMD, minificati o mappe.

Undici nuovi metodi PHPUnit coprono duplicazione/ripristino corso/parziale nativi,
parsing POST PHP con QuickForm, valori CSV effettivi, permessi nei contesti, campi
nascosti, input contraffatti e preferenze. Il test di recupero verifica anche che
il ricalcolo ordinario conservi le righe già accettate. Due scenari Behat aggiungono
la duplicazione reale con modifica estranea e il modulo di ricalcolo ordinario.
Restano le asserzioni precedenti sui permessi.

I controlli locali su sorgenti, inventari, lingue, schema e patch non sostituiscono
Moodle. PHP/PHPCS/PHPUnit, CI nativa 274, lifecycle, server e prove manuali restano
in attesa dei report effettivi. Il lavoro sui provider resta distinto dai gate
HTTPS/Microsoft pianificati.

Fonti primarie: [conversione nomi PHP](https://github.com/php/php-src/blob/PHP-8.3/main/php_variables.c),
[invio moduli Moodle](https://github.com/moodle/moodle/blob/MOODLE_500_STABLE/lib/formslib.php),
[permessi dettagli utente](https://github.com/moodle/moodle/blob/MOODLE_500_STABLE/user/lib.php),
[visibilità profili](https://github.com/moodle/moodle/blob/MOODLE_500_STABLE/user/profile/lib.php),
[hook ripristino differito](https://github.com/moodle/moodle/blob/MOODLE_500_STABLE/backup/util/plan/restore_structure_step.class.php).

## Correzione dopo la prima CI

La prima esecuzione nativa supera tutti gli 84 scenari browser e 1571 passi su
sei matrici, ma si ferma per fixture dei test e formattazione Code Checker.
La serializzazione POST passa ora un ampersand esplicito, indipendente dal
separatore URL HTML di PHP. La fixture dei permessi sostituisce il CAP_PREVENT
già presente e verifica i due contesti prima di leggere le opzioni CSV. Il
bootstrap degli analizzatori carica la vera libreria Moodle dei profili senza
soppressioni. La costante di visibilità nascosta è una stringa: entrambi i lati
sono convertiti in interi prima di scegliere il contesto di sistema richiesto.
Il test esteso include un campo privato e uno completamente nascosto con un
permesso limitato al corso. Restano asserzioni precedenti, conteggi, dati e
ricalcolo ordinario. Attesi i nuovi esiti nativi e i lifecycle popolati.

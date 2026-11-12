# Evidenze integrate sul ciclo di vita e sul gate di produzione

Candidata corrente: VideoTrack **1.7.253** (`2026111203`). Audit registrato 2026-11-12.

## Baseline accettata

La **1.7.252 / 2026111202** reale è accettata: tutti i 415 file dello ZIP coincidono con main
`3f9e62ab6485f36e4088b1c76b79aa644bc49fa3` e con la candidata corretta approvata.
SHA-256 pacchetto: `659f60d7e48de2d198ab070a71b6ca3ee98506553545d09d29e712443fde61f9`.
Il report server di 10 file ha SHA-256
`34ce4e5d1bfecd3fd874154fc7818797c43dcf2db14089328c82363bd40a21eb`.
CI candidata corretta (run 43246418318, commit `5b7ffd3`) e CI main
(run 43252265858) superano i sei ambienti. Ciascuno supera PHPUnit **383/4350**,
Behat **52 scenari / 1154 passi**, strict **10/0/0** e Grunt. PHPCS/lint puliti su
167 file. I due report server Moodle 5.0/5.3 superano la stessa suite PHPUnit e Grunt;
gli hash build AMD restano invariati. Entrambi gli strict forniti dal manutentore sono **10/0/0**.
Nel workspace non sono state eseguite prove Moodle native. Restano i 24 rilievi PHPMD rivisti e
i 732/760 consultivi Psalm della candidata corretta; i gate configurati passano.
I due errori PHPCS iniziali sugli argomenti sono stati corretti prima del merge, non ignorati.

## Perimetro integrato accettato

| Caso | Evidenza e perimetro |
|---|---|
| Backup/restore senza studenti | Controller corso reali, configurazione condivisa, mapping Forum/reazioni/file e grade item canonico; dati studente/voti/completion esclusi, record plugin di origine conservati. |
| Backup/restore con studenti e privacy dopo il ripristino | La retention di destinazione esclude input scaduti; segmenti non validati e aggregate/completion obsolete non restituiscono credito; verificati export utente approvato, cancellazione utente/contesto e conservazione altri studenti/origine/file/voti core. |
| Reset corso standard, opzione disattivata/attivata | L'ingresso Moodle reale scopre `videotrack_reset_userdata()`, che delega all'helper conservato. Due attività dello stesso corso vengono azzerate, conservando configurazione/definizioni/file e dati studente di un altro corso. Il reset completion core è selezionato separatamente e il reset globale gradebook è disattivato. |
| Cancellazione sincrona attività | L'API Moodle rimuove attività scelta, sei tabelle figlie plugin, completion nativa, file/contesto e grade item/voti propri. Restano il Forum collegato e un'attività vicina popolata. |

Il callback standard di reset e il comportamento esplicito/disattivato per default sono accettati sulla 252.
L'helper esistente resta disponibile ai chiamanti diretti storici. La pagina reset Moodle controlla
`moodle/course:reset`, login e sessione/form; il reset del singolo studente nel report conserva il distinto
gate gestione/sesskey/scope. Il test cancellazione seleziona `core_courseformat\formatactions::cm()->delete()`
quando supportato e l'helper previsto dai rami precedenti altrimenti, senza sopprimere deprecazioni.

I test backup usano `MODE_IMPORT` con XML/file reali, senza compressione dell'archivio. Non certificano
download/estrazione MBZ, migrazione tra siti o compatibilità backup storici. Le fixture popolano record
framework/DB direttamente: non sono evidenza che il browser abbia prodotto tali record.

## Nuova candidata: task retention registrato dopo il ripristino

Un nuovo test di integrazione ottiene `mod_videotrack\task\cleanup_task` tramite
`core\task\manager::get_scheduled_task()`, ne controlla il componente ed esegue il vero `execute()`.
Ripristina un'attività con dati studente mentre la retention è illimitata, poi esegue cinque invocazioni:

1. La retention illimitata conserva esattamente i record studente di origine e ripristinati.
2. La policy di dieci giorni elimina segmenti/eventi/integrità/prese visione scaduti da entrambe le attività.
   Solo i segmenti validati conservati danno credito; completion personalizzata e nativa diventano incomplete.
3. Ripetere la stessa policy conserva esattamente tutti i record plugin residui e riporta zero coppie elaborate.
4. La policy di un giorno fa scadere ogni input della fixture e rimuove le cinque famiglie studente, stato incluso.
5. Ripetere la pulizia totale lascia vuote le tabelle studente del plugin e riporta zero coppie elaborate.

Configurazione attività, definizioni reazioni, file condivisi e righe voto di competenza core restano invariati.
L'output esatto del task copre quattro coppie utente/attività e distingue stati ricostruiti da rimossi. La policy
di sito vale per entrambi i corsi: anche i dati scaduti di origine vengono intenzionalmente eliminati. Le fixture
usano date di due e venti giorni fa, lontane dai cutoff scelti; nessuno sleep o nuovo ingresso tempo di produzione.

È invocazione diretta del task registrato in Moodle, non prova di orari cron, lock scheduler, limiti backlog,
retry dopo guasti o fault injection. Queste osservazioni non si deducono dal test. La nuova prova attende la CI
nativa. Tutto il PHP operativo tranne il marcatore release, schema, AMD, valori lingua, ruoli, servizi,
registrazione task, implementazioni privacy/backup e regole CI restano identici byte per byte alla 252.

Suite candidata attesa: **384 metodi test PHPUnit**; asserzioni da misurare nella CI nativa.
Behat resta a **52 scenari / 1154 passi**. Restano **415 file**, **167 PHP**, **32 documenti per lingua**,
**346 righe inventario file** e **654 callable AMD**. I callable PHP nominati diventano **1114**.
XMLDB resta `2026110601` e **7/151/22/22** per tabelle/campi/indici/foreign key, con nove AJAX,
otto pacchetti da 997 chiavi e 48 terne sorgente/build/map AMD. L'ER cambia solo etichetta versione.

## Gate di produzione residuo e roadmap

- Upgrade storici/interrotti e uninstall popolato con verifica zero residui restano evidenze distinte.
- Pianificazione cron reale, contesa/ripresa dopo guasti e fault injection non sono certificati da questi casi.
- I benchmark Analytics su dataset ampi, più attività/gruppi e periodi 7/30/90 giorni richiedono versioni e
  condizioni dati/cache registrate; nessun benchmark viene dedotto da conteggi test/query.
- Restano otto PASS manuali dichiarati sulla 248/Moodle 5.0/Academi/Windows 11/Chrome/NVDA nel loro scope.
  Moodle 5.3 non è registrato; non si deducono nuovi PASS manuali o certificazioni WCAG.
- Il supporto Moodle App dedicato resta non dichiarato (`db/mobile.php` ha addon vuoti).

U-017 e U-007 essenziale restano chiusi. Completare il gate integrato web/ciclo vita/privacy/benchmark/manuale
e la decisione App esplicita, poi VTT multilingua concordati, base autenticata per provider,
OneDrive/SharePoint → Zoom Cloud Recording → Google Drive e consolidamento tra provider.
CI candidata/main e ZIP reale/report server/strict restano necessari prima di accettare la 253.

Vedere [ciclo di vita](14_INSTALL_UPGRADE_BACKUP_RESTORE.md), [privacy](18_PRIVACY_RETENTION.md),
[CLI/benchmark](21_CLI_DIAGNOSTICS.md), [evidenze manuali](19_ACCESSIBILITY.md)
e [revisione lingue](27_REVISIONE_LINGUE.md).

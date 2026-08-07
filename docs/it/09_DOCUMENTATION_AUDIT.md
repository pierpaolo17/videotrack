# Audit della documentazione

Baseline: VideoTrack **1.6.26** (`2026060441`).

## Copertura

- File non documentali inventariati: **234/234**.
- Funzioni/metodi PHP nominati inventariati: **470**.
- Callable AMD nominati rilevati e inventariati: **573**.
- Tabelle XMLDB documentate: **7**.
- Chiavi impostazioni sito documentate: **57**.
- Chiavi configurazione player documentate: **128**.
- Servizi AJAX documentati: **8**.
- Language pack: otto pacchetti con lo stesso contratto di **954 chiavi**; i testi operativi sono tradotti, mentre termini tecnici e nomi propri possono legittimamente coincidere.
- Panoramiche root: `README.md` (inglese) e `README_IT.md` (italiano).
- Sintesi privacy root: `PRIVACY.md` e `PRIVACY_IT.md`.

## Regole di aggiornamento

I documenti correnti non devono contenere affermazioni di release senza indicare versione/stato. Gli audit 1.4.x sono isolati in `archive/`. Ogni modifica a schema, servizi, File API, configurazione player, report, privacy, accessibilità o traduzioni richiede l’aggiornamento dei documenti numerati pertinenti.

## Audit automatici attesi

Ogni release deve confrontare inventario file/albero, inventario funzioni/sorgente, chiavi e placeholder, `get_string` statici, XMLDB/backup-restore, servizi/classi, sorgenti/build AMD e link Markdown/file esistenti.

## Copertura affidabilità strumenti player 1.6.26

- Una durata verificata pari a `0` disabilita esplicitamente percentuale vista e completamento percentuale, mantenendo il tracking degli intervalli validati e gli strumenti di studio abilitati.
- La durata ricavata dal browser o dal provider non viene promossa automaticamente a valore autorevole anti-manomissione; il docente può inserire una durata verificata quando servono percentuale o presa visione vincolata alla fine.
- L’intero fieldset dei controlli HTML5 è nascosto per YouTube e Vimeo e ricompare quando la sorgente diventa un file locale.
- I controlli delle reazioni learner restano disponibili in riproduzione e in pausa, ma il server accetta solo timestamp già visualizzati e validati.
- Note e segnalibri abilitati sono mostrati ai learner; chi può consultare i report riceve un’anteprima disabilitata e non può creare telemetria learner.
- Le segnalazioni PHPCS rilevate sulla 1.6.25 in `view.php`, `report.php` e `tests/tracker_test.php` sono corrette nell’albero 1.6.26.

## Copertura regressioni interfaccia 1.6.25

- Il renderer Analytics delle prese visione inizializza sia la soppressione dei conteggi sia quella del progresso.
- Le definizioni delle reazioni restano nella sezione principale Reazioni e non sono modellate come un insieme fisso di due elementi.
- L’ordine nella pagina attività è: reazioni, azione Forum opzionale, quindi storico personale delle reazioni/segnalibri.
- Anteprima per chi consulta i report e persistenza learner restano stati di fiducia distinti.

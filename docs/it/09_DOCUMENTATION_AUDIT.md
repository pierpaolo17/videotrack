# Audit della documentazione

Baseline: VideoTrack **1.6.29** (`2026060444`).

## Copertura

- File non documentali inventariati: **237/237**.
- Funzioni/metodi PHP nominati inventariati: **471**.
- Callable AMD nominati rilevati e inventariati: **596**.
- Tabelle XMLDB documentate: **7**.
- Chiavi impostazioni sito documentate: **57**.
- Chiavi configurazione player documentate: **128**.
- Servizi AJAX documentati: **8**.
- Language pack: otto pacchetti con lo stesso contratto di **960 chiavi**; i testi operativi sono tradotti, mentre termini tecnici e nomi propri possono legittimamente coincidere.
- Panoramiche root: `README.md` (inglese) e `README_IT.md` (italiano).
- Sintesi privacy root: `PRIVACY.md` e `PRIVACY_IT.md`.

## Regole di aggiornamento

I documenti correnti non devono contenere affermazioni di release senza indicare versione/stato. Gli audit 1.4.x sono isolati in `archive/`. Ogni modifica a schema, servizi, File API, configurazione player, report, privacy, accessibilità o traduzioni richiede l’aggiornamento dei documenti numerati pertinenti.

## Audit automatici attesi

Ogni release deve confrontare inventario file/albero, inventario funzioni/sorgente, chiavi e placeholder, `get_string` statici, XMLDB/backup-restore, servizi/classi, sorgenti/build AMD e link Markdown/file esistenti.

## Copertura partecipazione esplicita 1.6.29

- UI attività, servizi di scrittura learner e popolazioni dei report usano `mod/videotrack:participate`.
- L’accesso ai report non disabilita più un partecipante con ruolo multiplo.
- Docenti e manager standard restano non tracciati perché la capability è assegnata all’archetipo Studente e il privilegio amministrativo do-anything viene ignorato per questa decisione.
- Gli amministratori dei ruoli possono assegnare o revocare la capability ai ruoli personalizzati.

## Copertura trasporto configurazione durata 1.6.28

- La configurazione localizzata del detector è conservata in un elemento script JSON nel DOM del form attività.
- `js_call_amd()` riceve soltanto l’id dell’elemento di configurazione, mantenendo l’argomento serializzato molto sotto la soglia Moodle developer di 1024 caratteri.
- Il modulo AMD verifica e analizza la configurazione DOM prima di installare il detector.

## Copertura durata automatica 1.6.27

- Il form docente può proporre la durata dai metadati YouTube, Vimeo o del file locale.
- La proposta è modificabile, annunciata tramite una regione live accessibile e diventa autorevole soltanto quando il docente salva.
- La durata runtime learner non può aggiornare il valore memorizzato; se provider o browser non espongono i metadati, il campo resta manuale.

## Copertura affidabilità strumenti player 1.6.26

- Una durata verificata pari a `0` disabilita esplicitamente percentuale vista e completamento percentuale, mantenendo il tracking degli intervalli validati e gli strumenti di studio abilitati.
- L’intero fieldset dei controlli HTML5 è nascosto per YouTube e Vimeo e ricompare quando la sorgente diventa un file locale.
- I controlli delle reazioni learner restano disponibili in riproduzione e in pausa, ma il server accetta solo timestamp già visualizzati e validati.
- Note e segnalibri abilitati sono mostrati ai partecipanti espliciti; chi non possiede `mod/videotrack:participate` riceve un’anteprima disabilitata e non può creare telemetria learner.
- Le segnalazioni PHPCS rilevate sulla 1.6.25 in `view.php`, `report.php` e `tests/tracker_test.php` sono corrette nell’albero 1.6.26.

## Copertura regressioni interfaccia 1.6.25

- Il renderer Analytics delle prese visione inizializza sia la soppressione dei conteggi sia quella del progresso.
- Le definizioni delle reazioni restano nella sezione principale Reazioni e non sono modellate come un insieme fisso di due elementi.
- L’ordine nella pagina attività è: reazioni, azione Forum opzionale, quindi storico personale delle reazioni/segnalibri.
- Anteprima per chi consulta i report e persistenza learner restano stati di fiducia distinti.

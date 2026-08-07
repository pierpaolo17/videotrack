# Architettura

## Livelli

1. **Entry point Moodle** — `view.php`, pagine report, form e callback lifecycle eseguono i controlli e preparano stato posseduto dal server.
2. **Servizi di dominio** — le classi in `classes/local/` implementano tracking, Analytics, scope, export privacy-safe, timed text, integrità e presa visione.
3. **Servizi esterni** — `classes/external/` espone otto scritture AJAX tramite `db/services.php`; la validazione comune è in `external\helper`.
4. **Adapter player** — `html5_player.js`, `player.js` (YouTube) e `vimeo_player.js` traducono le callback dei provider nel contratto condiviso.
5. **Core AMD condiviso** — trasporto/retry API, lifecycle tracker, intervalli, reazioni, note, segnalibri, trascrizione, presa visione, focus guard, stato e UI.
6. **Persistenza** — sette tabelle XMLDB, aree Moodle File API e gradebook core.
7. **Report** — report per studente, dashboard corso/docente e Analytics di istanza/tra corsi con data-format export.

## Contratto player

Ogni adapter deve fornire tempo corrente, durata, play/pausa, seek, velocità e fine. I moduli condivisi non assumono comportamenti uguali tra provider. Resume, replay e correzione seek programmatici sono distinti dal seek utente. I limiti SDK di YouTube e Vimeo sono trattati esplicitamente.

## Modello dati

- `videotrack`: configurazione attività.
- `videotrack_seg`: segmenti di visione append-only.
- `videotrack_state`: intervalli uniti, progresso e completamento per utente/attività.
- `videotrack_react`: definizioni delle reazioni configurate.
- `videotrack_reactev`: reazioni standard, note personali e segnalibri privati distinti da `notetype`.
- `videotrack_integrity`: segnali diagnostici limitati.
- `videotrack_acknowledge`: conferme versionate e fotografia del progresso.

## Identità e scope

Contesto modulo e capability Moodle sono autorevoli. La visibilità dei gruppi usa la modalità effettiva dell’attività. Gli Analytics tra corsi rivalutano capability e gruppi per ogni attività e identificano lo stesso video tramite ID provider o content hash del file caricato.

## Architettura privacy

La raccolta è subordinata alle funzioni abilitate. Gli Analytics docente usano aggregati e soglie minime indipendenti. Testo note ed etichette segnalibri sono del proprietario. L’export Privacy elabora collezioni grandi in blocchi limitati. Cancellazione, reset, retention e backup/restore coprono tutte le tabelle utente.

## Architettura accessibile

I controlli sono da tastiera e hanno nomi accessibili; gli stati dinamici usano live region; sono supportati movimento ridotto e colori forzati; l’overlay poster espone il pulsante Play alle tecnologie assistive. La politica focus predefinita usa la visibilità del documento, non il semplice blur della finestra.

## Asset generati

`amd/src` è canonico. `amd/build` e source map sono artefatti generati e cambiano soltanto dopo una build Moodle reale.

# Audit della documentazione

Baseline: VideoTrack **1.6.24** (`2026060439`).

## Copertura

- File non documentali inventariati: **234/234**.
- Funzioni/metodi PHP nominati inventariati: **469**.
- Callable AMD nominati rilevati e inventariati: **572**.
- Tabelle XMLDB documentate: **7**.
- Chiavi impostazioni sito documentate: **57**.
- Chiavi configurazione player documentate: **128**.
- Servizi AJAX documentati: **8**.
- Language pack: otto pacchetti con contratto iniziale comune di 952 chiavi; i testi operativi copiati dall’inglese sono stati tradotti, mentre termini tecnici/propri possono legittimamente coincidere.
- Panoramiche root: `README.md` (inglese) e `README_IT.md` (italiano).
- Sintesi privacy root: `PRIVACY.md` e `PRIVACY_IT.md`.

## Regole di aggiornamento

I documenti correnti non devono contenere affermazioni di release senza indicare versione/stato. Gli audit 1.4.x sono isolati in `archive/`. Ogni modifica a schema, servizi, File API, configurazione player, report, privacy, accessibilità o traduzioni richiede l’aggiornamento dei documenti numerati pertinenti.

## Audit automatici attesi

Ogni release deve confrontare inventario file/albero, inventario funzioni/sorgente, chiavi e placeholder, `get_string` statici, XMLDB/backup-restore, servizi/classi, sorgenti/build AMD e link Markdown/file esistenti.

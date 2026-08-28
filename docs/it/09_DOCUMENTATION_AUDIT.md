# Audit della documentazione

Baseline: VideoTrack **1.7.118** (`2026082802`).

## Perimetro

La documentazione è stata verificata contro `version.php`, `db/install.xml`, `db/services.php`, `db/access.php`,
`settings.php`, `mod_form.php`, `classes/local`, `classes/privacy`, backup/restore, coppie sorgente/build AMD e
suite di test distribuite.

La copertura corrente comprende:

- guida completa funzionale e operativa per utenti/amministratori;
- architettura, flussi runtime e confini di fiducia;
- sette tabelle plugin, impostazioni, capability, nove servizi AJAX e file area;
- comportamento dei provider HTML5, YouTube e Vimeo;
- tracking, completion, gradebook, report, Analytics ed export;
- reazioni, note, bookmark, collegamento Forum e presa visione;
- privacy, retention, reset, backup/restore e diagnostica CLI;
- contratto di accessibilità, troubleshooting e gate build/release;
- inventari esaustivi dei file non documentali e dei callable nominati;
- artefatti database/ER Markdown, Mermaid e SVG accessibile.

## Politica di freschezza e storico

`docs/en/archive` e `docs/it/archive` non sono distribuite. Le narrazioni implementative release per release sono
state rimosse dalle guide correnti. Il `CHANGELOG.md` principale contiene soltanto sintesi; per il comportamento
esatto di una versione precedente si usa il relativo tag sorgente e la documentazione inclusa.

Le README principali forniscono una mappa completa delle funzionalità e indirizzano alle guide dettagliate. Non
duplicano intenzionalmente procedure implementative, dizionari dei campi o lunghe spiegazioni sicurezza/privacy.

## Rilievi sull'albero corrente

- Gli indici inglese e italiano hanno stesso perimetro e ordinamento.
- Marker documentali, README principali e artefatti ER identificano 1.7.118 / 2026082802.
- L'identità dell'attività include `pix/icon.png` a colori e `pix/icon.svg` trasparente per i formati corso.
- `db/install.xml` dichiara attualmente sette chiavi primarie e gli indici, ma nessun metadata di foreign key. I
  documenti ER descrivono correttamente i riferimenti senza attribuire vincoli fisici. Il punto è separato in un
  ciclo correttivo dati/schema perché i siti installati richiedono controllo degli orfani e upgrade XMLDB esplicito.
- Questa release documentale/grafica non cambia runtime, AJAX, privacy, completion, gradebook o database.

## Controlli di release

Prima della promozione verificare:

1. assenza di directory o link `archive/` nel pacchetto;
2. assenza di narrazioni di versioni obsolete nei documenti correnti;
3. risoluzione dei link Markdown e parità dei set EN/IT;
4. identità degli inventari file/callable con l'albero esatto;
5. validità PNG e percorso dell'icona;
6. contratti PHPUnit di release hygiene e gate server proporzionato verdi.

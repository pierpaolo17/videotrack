# Scope esplicito di partecipazione learner — 1.6.29

## Problema corretto

Le release precedenti consideravano l’assenza di `mod/videotrack:viewreport` come prova che l’utente fosse un learner. Questa euristica disabilitava reazioni, note, segnalibri e tracking per utenti con ruoli personalizzati o multipli che possedevano legittimamente sia permessi learner sia accesso ai report. Poiché la stessa condizione era applicata in `view.php` e nell’helper AJAX, tutti gli strumenti personali apparivano grigi e ogni scrittura veniva rifiutata.

## Capability canonica

VideoTrack 1.6.29 introduce `mod/videotrack:participate` come permesso canonico per telemetria learner e strumenti personali di studio. È una capability di scrittura di dati personali nel contesto modulo. L’archetipo Studente standard la riceve per default e i permessi iniziali sono clonati dalla capability Moodle `moodle/course:isincompletionreports`.

La capability governa:

- tracking dei segmenti visti e dati di completamento;
- reazioni, note e segnalibri;
- indicatori di integrità e conferma della presa visione;
- export personale dei segnalibri;
- popolazione learner usata da Analytics di istanza, corso e docente.

`mod/videotrack:viewreport` resta esclusivamente un permesso di consultazione dei report. Un utente può possedere entrambe le capability: in tal caso può partecipare ed è incluso nello scope learner perché la partecipazione è esplicita e non dedotta.

## Anteprime staff e amministratori

Docenti e manager non ricevono `mod/videotrack:participate` dai rispettivi archetipi standard. Il privilegio amministrativo do-anything viene deliberatamente ignorato quando si decide se la pagina corrente può generare telemetria learner. Per un test realistico l’amministratore deve cambiare ruolo in un ruolo partecipante oppure usare un account learner di prova.

## Ruoli personalizzati e upgrade

L’upgrade del plugin sincronizza la nuova capability. Nei siti con ruoli learner personalizzati occorre verificare che tali ruoli ricevano `mod/videotrack:participate`; l’amministratore dei ruoli può assegnarla o revocarla esplicitamente. Questo è più affidabile che dedurre lo stato learner da nomi di ruolo o capability di report non correlate.

## Contratto di regressione

La stessa capability deve essere usata da rendering server, Web Service di mutazione, scope SQL learner ed export. Le modifiche future non devono reintrodurre un gate separato basato su “non è report viewer”, perché ricreerebbe la regressione con tutti i controlli disabilitati.

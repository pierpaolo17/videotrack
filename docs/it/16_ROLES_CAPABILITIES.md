# Ruoli, capability e partecipazione

VideoTrack non deduce lo stato learner dal nome del ruolo o dal permesso di report. Sono autorevoli le capability
Moodle nel contesto corretto e uno stesso utente può avere legittimamente permessi learner e staff.

## Mappa delle capability

| Capability | Contesto | Scopo |
|---|---|---|
| `mod/videotrack:addinstance` | Corso | Aggiungere l'attività. |
| `mod/videotrack:view` | Modulo | Aprire l'attività. |
| `mod/videotrack:participate` | Modulo | Generare tracking learner e dati di studio personali. |
| `mod/videotrack:viewownreport` | Modulo | Vedere progresso/interazioni proprie. |
| `mod/videotrack:viewreport` | Modulo | Permesso generale/legacy di ingresso ai report. |
| `mod/videotrack:viewaggregatereport` | Modulo | Vedere aggregati con soglia privacy senza filtro learner. |
| `mod/videotrack:viewindividualreport` | Modulo | Vedere dati learner ed esatti entro lo scope. |
| `mod/videotrack:exportaggregatereport` | Modulo | Esportare dati aggregati. |
| `mod/videotrack:exportindividualreport` | Modulo | Esportare dati personali learner. |
| `mod/videotrack:viewcoursereport` | Corso | Aprire la dashboard VideoTrack del corso. |
| `mod/videotrack:managereactions` | Modulo | Gestire definizioni reazioni. |
| `mod/videotrack:grade` | Modulo | Gestire voti VideoTrack. |
| `mod/videotrack:overrideplayersettings` | Corso | Superare i default player imposti dal sito. |
| `mod/videotrack:overridecompletionsettings` | Corso | Superare i default completion VideoTrack del sito. |

## Contratto di partecipazione

Gli utenti con `participate` sono nel runtime learner: sessioni playback, progresso, reazioni, note, bookmark,
presa visione ed eventi correlati possono essere scritti quando abilitati. Senza la capability vedono un'anteprima
non tracciata anche se possono aprire i report. Un utente con doppio ruolo e partecipazione+report resta learner
tracciato e riceve anche i controlli report autorizzati.

I report corso applicano iscrizione Moodle, gruppi e cohort oltre alle capability. Utenti nascosti/cancellati e
cambi ruolo vanno gestiti con API Moodle, non con assunzioni sugli archetipi.

## Gruppo di eccezione focus

`mod_videotrack_focus_exception` è un gruppo corso nascosto e non partecipante usato soltanto con policy focus
strict. L'appartenenza riduce il blur della finestra visibile da strict a hidden-only. Non concede capability e non
bypassa scheda nascosta, credito playback, completion o report.

## Indicazioni amministrative

- concedere capability report/export di dati personali solo ai ruoli necessari;
- non assegnare `participate` allo staff in sola anteprima salvo tracking intenzionale;
- revocare le capability override quando la policy deve essere centralizzata;
- testare ruoli personalizzati e doppi dopo modifiche ai permessi;
- verificare scope gruppo/cohort prima di interpretare o esportare dati.

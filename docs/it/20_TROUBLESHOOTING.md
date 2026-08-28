# Risoluzione dei problemi

Partire da Console/Network del browser, debugging sviluppatore Moodle e validatore in sola lettura. Verificare sempre
codice/versione installati prima di indagare un comportamento che può dipendere dalla cache.

| Sintomo | Controlli | Confine probabile |
|---|---|---|
| Record attività non trovato | Verificare `id` URL, riga `course_modules`, nome modulo e istanza; navigare dal corso. | URL obsoleto o attività ricreata. |
| Modifica AMD non visibile | Verificare `amd/src` e `amd/build` deployati, purge cache, hard reload e bootstrap `require()`. | Codice/cache obsoleti o build mancante. |
| Durata YouTube non proposta | Validare URL/id, incorporamento pubblico, rete/consenso, console e timeout; inserire manualmente. | Readiness/accesso metadata provider. |
| Durata Vimeo non proposta | Validare id/URL, richiesta SDK, policy privacy/embed e rejection della promise. | Accesso provider o loader. |
| Percentuale a zero | Confermare durata positiva, `participate`, `start_playback`, segmenti accettati e cleanup. | Configurazione o credito server. |
| Seek avanti conteggiato | Ispezionare ultimo `videotrack_seg`, `servervalidated`, interval JSON e frontiera attendibile. | Regressione tracker/provider. |
| Resume errato | Confrontare `videotrack_state.lastposition`, durata, intervalli conservati e parametro replay diretto. | Stato derivato o readiness provider. |
| Label/layout completion errato | Confermare AMD `completion_requirements`, marker raggruppamento `1` e regione Moodle. | Deploy/cache o markup cross-versione. |
| Reazione/nota/bookmark rifiutati | Controllare capability, proprietà, timestamp attendibile, limiti duplicato/raffica e JSON AJAX. | Scope o validazione interazione. |
| Azione Forum non disponibile | Controllare Forum stesso corso, disponibilità, gruppi, permessi e `linkedforumid`. | Integrazione Forum Moodle. |
| Valori report mascherati | Controllare capability aggregate/individuali, `analyticsminusers` e scope learner/gruppo. | Policy privacy intenzionale. |
| Backup senza dati learner | Confermare `userinfo`, cutoff retention e mapping utenti validi. | Policy backup/retention. |
| Retention non eseguita | Controllare task pianificati, setting/conferma, log task, lock ed error log. | Configurazione cron/task. |
| Pause focus inattese | Controllare `focuslosspolicy`, flag attività, visibility documento e gruppo eccezione. | Policy focus o appartenenza. |

## Comandi diagnostici

```bash
php mod/videotrack/cli/validate.php --json
php admin/cli/purge_caches.php
php admin/cli/scheduled_task.php --execute='\mod_videotrack\task\cleanup_retention'
```

Eseguire task distruttivi/privacy solo nell'ambiente previsto e dopo aver confermato lo scope. Per un difetto codice
raccogliere release, ramo Moodle, sorgente/provider, passi, Console, risposta Network e righe database rilevanti con
dati personali oscurati.

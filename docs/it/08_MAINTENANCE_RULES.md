# Regole di manutenzione

Queste regole proteggono il runtime corrente e sono obbligatorie per ogni modifica.

1. **Usare la baseline reale.** Auditare l'ultimo ZIP/albero reale; non dedurne il contenuto da una patch proposta.
2. **Ricostruire il percorso eseguito.** Per difetti runtime tracciare pagina, entrypoint AMD, adapter, metodo AJAX,
   servizio server, persistenza e payload restituito prima di modificare.
3. **Separare i provider.** HTML5, YouTube e Vimeo implementano un contratto con timing SDK diversi.
4. **Mantenere autorevole il server.** Timestamp, progresso e focus del browser sono richieste o diagnostica.
5. **Preservare l'idempotenza.** Le scritture ritentabili richiedono request id stabili e duplicati sicuri.
6. **Rispettare lo scope Moodle.** Validare login, contesto, course module, capability, gruppo/cohort e proprietà.
7. **Separare partecipazione e report.** Non dedurre lo stato learner dall'accesso ai report.
8. **Minimizzare i dati personali.** Non esporre testo note/bookmark negli aggregati né ampliare export tacitamente.
9. **Trattare completion come regola composita.** Il layout non deve dividere o cambiare la semantica AND/OR.
10. **Usare API Moodle.** File, voto, completion, privacy, backup, eventi e lock passano dalle API core.
11. **Abbinare sorgente/build AMD.** Modificare `amd/src`, eseguire Moodle Grunt e distribuire build/mappe.
12. **Rendere gli upgrade riprendibili.** Step schema idempotenti, ordinati e conclusi da savepoint plugin.
13. **Testare gli effetti sul ciclo dati.** Modifiche dati richiedono privacy, retention, reset e backup/restore.
14. **Mantenere entrambe le lingue.** Documentazione inglese e italiana descrivono lo stesso contratto corrente.
15. **Non incorporare archeologia di release.** I documenti spiegano l'albero corrente; lo storico vive nei tag.
16. **Registrare evidenze esatte.** Un risultato di altro albero, ramo Moodle o browser non è trasferibile.

## Definizione di completato

Una modifica è completa soltanto quando sorgente, asset generati, schema/upgrade, placeholder lingua,
documentazione, test, verifica patch/pacchetto e controlli manuali richiesti concordano. I controlli differiti
sono dichiarati esplicitamente e restano bloccanti quando coprono il comportamento modificato.

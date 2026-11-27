# Riferimenti Microsoft nelle attività

VideoTrack **1.7.271** (`2026112401`) prepara un riferimento Microsoft stabile e
facoltativo nell’attività. Microsoft non è ancora disponibile come sorgente con
tracciamento. HTTPS/OAuth, permessi reali del tenant, eventi browser e avanzamento
studente richiedono le prove dal vivo. Il video attivo HTML5, YouTube o Vimeo resta
la sorgente riproducibile.

## Preparazione e modifica

Chi può gestire le attività del corso (`moodle/course:manageactivities`) può aprire
**Preparazione video Microsoft** e incollare il riferimento prodotto dalla verifica
Microsoft protetta. Il campo può restare vuoto in tutte le attività esistenti.
Il salvataggio valida localmente: non chiama Graph e non richiede token.

Il riferimento contiene esattamente questi campi scalari:

```json
{"version":1,"provider":"microsoft","issuerid":17,"driveid":"b!Drive","itemid":"Video"}
```

L’issuer positivo deve corrispondere al servizio OAuth Microsoft core selezionato,
esistente e abilitato nel sito. Gli ID di drive/video usano la grammatica circoscritta
già esistente. URL, percorsi, anteprime temporanee, segreti e campi aggiuntivi vengono
rifiutati. Il JSON canonico è salvato nel TEXT nullable `videotrack.microsoftreference`:
limite di 4096 byte e tipi rigorosi vengono verificati prima della scrittura.
Un riferimento invariato sopravvive al cambio di configurazione; aggiornamenti che
omettono il campo mantengono il precedente. Il docente autorizzato può svuotare anche
un riferimento obsoleto senza ripristinare prima il servizio.

La casella **Associa al servizio Microsoft selezionato in questo sito** cambia solo
l’associazione locale degli ID. Non viene memorizzata come impostazione, non concede
accesso e non dimostra che la risorsa esista nel nuovo tenant. Appena servizio e account
saranno disponibili bisognerà verificare di nuovo la risorsa dalla diagnostica già
esistente. Modificare il riferimento non cambia video attivo o durata e non avvia il
ricalcolo del completamento.

## Backup e ripristino

Il backup corso include un figlio `microsoft_identity` con soli `version`, `provider`,
`driveid` e `itemid`, con o senza dati utente. Esclude l’ID locale del servizio e tutte
le credenziali OAuth. Il JSON grezzo memorizzato non viene esportato. Il ripristino
valida esattamente questi quattro campi e salva `issuerid:0`, da associare, anche nel
sito originale. Ignora un eventuale campo grezzo `microsoftreference` del padre;
un backup senza il nuovo figlio lascia NULL. Non crea issuer, non cerca servizi
per nome e non associa automaticamente ID numerici coincidenti.

Una normale modifica mantiene il riferimento non associato. Il docente deve scegliere
esplicitamente l’associazione al servizio Microsoft abilitato del sito di destinazione,
oppure sostituirlo con un nuovo riferimento verificato localmente. Un secondo backup
resta portabile. I dati studente seguono ancora l’opzione utenti di Moodle, le mappature
e la retention già presenti.

## Aggiornamento e privacy

L’upgrade additivo `2026112401` aggiunge un solo campo TEXT nullable, controllando
`field_exists`. Le attività precedenti ricevono NULL: sette tabelle e 152 campi,
sempre 22 indici espliciti, 22 foreign key e nove servizi AJAX. L’upgrade non riscrive
i dati studente, non elimina record e non ricalcola il completamento. Un campo già
presente viene conservato. La prova lifecycle CI aggiunge soltanto il nuovo NULL
ai default storici attesi nella fotografia di conservazione.

I metadati Privacy dichiarano gli identificativi facoltativi della configurazione.
Non è un nuovo archivio di identità studente o token. Il campo resta fuori dalla
configurazione player studente e dagli export personali; il backup corso conserva
intenzionalmente gli ID portabili. Credenziali OAuth e revoca restano gestite dal core.

## Verifiche e prossimo passaggio

Nuovi test nativi coprono parsing rigoroso, permessi negati, servizio assente/disabilitato/
errato, modifiche parziali, associazione esplicita, callback effettive e backup/restore
corso reali con e senza utenti, rifiuto del campo grezzo legacy e backup ripetuti.
Due scenari browser locali coprono salvataggio vuoto ed errore di input. Tutti i 620
metodi unitari e 78 scenari precedenti restano presenti.

Controlli locali su sorgenti/contratti, lingue, mappe AMD e regressioni del tracciamento
restano distinti dall’esecuzione nativa PHP/Moodle. Alla consegna della patch sono da
eseguire CI 271 e installazioni sui due server test. Non si dichiarano prove reali
OAuth, riproduzione o telemetria Microsoft. Il prossimo passaggio richiede HTTPS e
account Microsoft personale configurato; gli eventi dell’iframe devono consentire
tracciamento attendibile prima di rendere selezionabile il player Microsoft.

Il riferimento salvato può essere verificato dal collegamento amministrativo: vedere il [flusso diagnostico 275](45_MICROSOFT_ACTIVITY_CHECK.md). Il player studente resta subordinato alle prove reali.

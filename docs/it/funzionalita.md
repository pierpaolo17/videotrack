# mod_videotrack - Funzionalita e potenzialita

**Versione documentata**: 1.6.0
**Compatibilita**: Moodle 5.0+
**Lingue incluse**: Italiano, Inglese, Tedesco, Spagnolo, Francese, Portoghese, Hindi, Polacco

## Cos'e mod_videotrack

`mod_videotrack` e un modulo attivita Moodle per erogare video didattici e tracciare in modo verificabile la fruizione degli studenti. Supporta video YouTube, Vimeo e file caricati localmente, registra segmenti visti, reazioni, note personali e stato di completamento.

Il modulo misura il comportamento di fruizione dichiarato dal player e validato lato server. Non puo dimostrare attenzione cognitiva reale, ma puo documentare quali porzioni del video sono state riprodotte, quando sono state inserite reazioni o note, e se le regole di completamento configurate sono state soddisfatte.

## Sorgenti video

- **YouTube** tramite IFrame API.
- **Vimeo** tramite Vimeo Player SDK con configurazione privacy-friendly.
- **Upload/HTML5** tramite file area Moodle e player browser nativo/custom.

Le sorgenti sono gestite da moduli AMD separati, con logica condivisa per tracking, reazioni, note e tabella delle interazioni.

## Tracking

Il tracking salva segmenti di visione con tempi video, tempi reali, velocita di riproduzione e motivo di chiusura del segmento. Il backend fonde gli intervalli sovrapposti e calcola i secondi unici visti.

Regola fondamentale: rivedere una porzione gia coperta non deve aumentare ne diminuire i secondi unici o la percentuale di completamento.

## Reazioni

Il docente puo configurare reazioni con:

- emoji;
- classi Font Awesome;
- immagini caricate.

Ogni reazione ha etichetta e descrizione. La UI studente registra reazioni timestamped e aggiorna la tabella "Le mie reazioni" in tempo reale. Il server applica controlli anti-duplicazione per evitare sovraccarichi statistici e spam didatticamente non utile.

## Note personali

Se abilitate, gli studenti possono salvare note personali collegate al tempo del video. Le note sono distinte dalle reazioni ma condividono parte della struttura eventi.

## Completion e gradebook

Il completamento puo dipendere da percentuale vista, numero di reazioni, reazioni obbligatorie o combinazioni logiche. Il plugin integra il gradebook Moodle quando configurato.

## Report

Il docente puo consultare report per singola attivita e report aggregati di corso. I report usano dati normalizzati da segmenti, stato utente, reazioni e note.

## Privacy, retention e backup

Il plugin implementa provider Privacy API, task di pulizia, anonimizzazione e supporto backup/restore Moodle.

## Documentazione tecnica

Per manutenzione del codice usare i documenti numerati in questa cartella, in particolare:

- `02_ARCHITECTURE.md`
- `03_FILE_INVENTORY.md`
- `04_FUNCTION_INVENTORY.md`
- `05_VARIABLE_INVENTORY.md`
- `06_RUNTIME_FLOWS.md`


## Pubblicazione opzionale nel Forum con timestamp (1.5.0)

Il docente può collegare l’attività a un Forum compatibile dello stesso corso. Il pulsante dello studente legge il tempo corrente del player, apre un form Moodle separato e precompila un link descrittivo al frammento. La pubblicazione è volontaria e usa l’API ufficiale Forum. Le note restano private. Dalla 1.5.1 il docente può personalizzare l’oggetto precompilato usando i segnaposto `{timestamp}` e `{activity}`; lo studente può modificarlo prima della pubblicazione.

## Heatmap e retention per istanza (1.6.0)

I docenti con accesso ai report dispongono di una scheda Analytics aggregata per ogni attività. Mostra spettatori distinti lungo la timeline, retention, tempo di visione unico, tempo rivisto, intervalli più visti e più rivisti e maggiori cali tra intervalli consecutivi. I risultati possono essere filtrati per un gruppo del corso disponibile al docente. Quando il corso contiene gruppi, gli utenti privi della capability di accesso a tutti i gruppi sono limitati all’unione dei gruppi di cui fanno parte, anche selezionando “Tutti gli utenti consentiti”. Una soglia minima configurabile nasconde le selezioni piccole e maschera i singoli intervalli positivi sotto soglia. Le metriche di revisione sono mascherate separatamente quando il sottogruppo che rivede è sotto soglia e vengono omessi i totali che permetterebbero di ricostruire valori nascosti. La sovrapposizione facoltativa delle reazioni usa solo cluster che rispettano la stessa soglia. Non vengono mostrati nominativi o testo delle note private.

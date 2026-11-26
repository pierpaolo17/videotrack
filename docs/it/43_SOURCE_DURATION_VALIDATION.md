# Durata della sorgente e controllo a ogni salvataggio

VideoTrack **1.7.273** (`2026112501`) confronta la durata impostata con quella della
sorgente all’apertura delle impostazioni e al clic su Salva e visualizza, Salva e torna al corso e Annulla.
Il controllo vale anche modificando soltanto reazioni, note, segnalibri o altre
impostazioni, e quando si cambia il link in un’attività duplicata. Una durata
positiva già presente non evita più il rilevamento. Il salvataggio non riutilizza
come verifica una risposta già conclusa in precedenza.

## Regola

| Metadato della sorgente | Durata impostata | Esito |
|---|---|---|
| Durata positiva valida | Maggiore di quella della sorgente | Mostra entrambe le durate e impedisce il salvataggio, riportando al campo durata. |
| Durata positiva valida | Uguale o inferiore | Mantiene il valore e prosegue con la normale validazione e il salvataggio Moodle. |
| Assente, zero, non finita o in timeout | Valore manuale altrimenti valido | Avvisa e consente di salvare manualmente oppure annullare; non sostituisce né azzera il valore. |
| Sorgente/input cambiati durante il controllo | Qualsiasi | Scarta la risposta superata e richiede una verifica corrente prima del salvataggio. |

Il confronto usa la precisione al millisecondo del campo salvato, senza tolleranze
arbitrarie in secondi o percentuale. La relazione corretta è impostata <= rilevata.
Resta consentita una durata volutamente più breve: ad esempio 5280 secondi per il
video demo più lungo. Zero mantiene il significato precedente; la presa visione
alla fine richiede ancora una durata positiva. Annulla verifica e mostra eventuali
avvisi, poi scarta le modifiche senza salvare: una durata errata non impedisce
di annullare. Aggiunta di campi reazione e altre azioni intermedie restano immediate. Un doppio clic durante il
controllo non genera due invii.

Si riutilizzano getDuration della IFrame API YouTube, getDuration del Player SDK
Vimeo e loadedmetadata del media HTML5 caricato. Sono sonde del modulo docente,
non riproduzioni studente. I player temporanei vengono distrutti a esito concluso;
non si importano video e non si aggiunge streaming a carico del server. Una
sorgente non disponibile viene interrogata di nuovo al prossimo salvataggio.
Restano i timeout limitati dei loader e dei metadati; un dato assente non viene
interpretato come un video certamente lungo zero secondi.

## Confine tra modulo e dati

Il listener asincrono controlla i due pulsanti di salvataggio e Annulla, riprendendoli
con requestSubmit e lo stesso pulsante. Restano attivi validazione client/server
Moodle, valori dei pulsanti ed editor. Se la durata non è disponibile, la scelta
di salvare manualmente vale soltanto per sorgente e durata rimaste identiche:
non autorizza dati modificati successivamente nel modulo.

Il JSON temporaneo durationprobe porta a PHP sorgente, URL/draft corrente e secondi
rilevati. La validazione PHP ignora risposte discordanti, non valide o troppo grandi
e blocca durate eccessive quando il rapporto corrisponde alla sorgente inviata.
È un metadato comunicato dal browser per validare il modulo, non una risposta API
autenticata dal server né una garanzia di sicurezza. Senza JavaScript o senza
risposta della sorgente resta il percorso manuale. Non si aggiungono credenziali API.

La sonda non è una colonna DB, un’impostazione salvata, un campo backup o un dato
di esportazione studente. Non ci sono migrazioni, riscritture delle evidenze di
visione, modifiche automatiche delle durate esistenti o ricalcoli globali.
Per una durata già salvata errata, correggerla nelle impostazioni e utilizzare
il ricalcolo del completamento esistente quando necessario: i segmenti restano.
Restano i fix replay/tracking. La 1.7.274 ritira la casella di recupero storico, conservando ricalcolo ordinario e righe di recupero già accettate.

## Verifiche di preparazione della 1.7.273 (storiche)

Dieci gruppi Node deterministici esercitano il modulo AMD reale: valori preesistenti,
salvataggi dopo modifiche estranee, duplicazione/cambio link, tre sorgenti, uguaglianza
e valori inferiori, indisponibilità/salvataggio manuale/retry, risposte superate,
doppio invio, modifica manuale in attesa, timeout, annulla/ripetizione/Invio e decimali.
Sulla 272 accettata falliscono tutti i dieci gruppi, come previsto. Cinque metodi PHPUnit
coprono la policy PHP; il test configurazione modulo include campo temporaneo e
messaggi tradotti. Restano gli 82 scenari Behat; due step di salvataggio usano metadati sorgente deterministici.

Verificato l’archivio Grunt effettivo del server Moodle 5.0 del manutentore,
SHA256 `b5c4292a4d9362e4ec95ad3e009273de75d1503c6853c67272351926cb650fb3`.
Registra Node 22.23.4 / npm 10.9.9 ed esito positivo di npx --no-install grunt amd.
Coincidono 48 hash sorgenti e 96 hash build/mappe; cambiano soltanto form/duration.js
e i due output nativi. Coincidono fixture e digest del log. Passano 10 gruppi sui
sorgenti e 10 sui compilati sia nel log nativo sia dopo l’incorporazione locale.
Il runner indica il ripristino esatto degli originali; non ha caricato Moodle
né eseguito comandi DB. È un output del server del manutentore, non di GitHub Actions.

Il campo nascosto temporaneo ha un id DOM esplicito, verificato dal test del modulo:
markup PHP e configurazione AMD usano così lo stesso identificatore.
Restano PHP/PHPCS/PHPUnit, CI/lifecycle/server 273 e prova reale del modulo durata.
Non si dichiarano esecuzioni PHP/Moodle locali o accettazione browser/provider.
Restano 82 scenari Behat. Due step di salvataggio preesistenti usano un SDK simulato
di 60 secondi e il vero handler del modulo, senza interrogare YouTube dal test.

Fonti primarie: [durata YouTube](https://developers.google.com/youtube/iframe_api_reference#getDuration),
[Vimeo Player SDK](https://github.com/vimeo/player.js/#getduration-promisenumber-error),
[Moodle Forms API](https://moodledev.io/docs/5.0/apis/subsystems/form).

La 1.7.273 è accettata: CI candidata/main, ZIP effettivo, server 50/53 e prove manuali durata/banner del maintainer superati. La 1.7.274 conserva la stessa policy durata. Vedere [manutenzione copie/CSV](44_ACTIVITY_COPY_CSV_MAINTENANCE.md).

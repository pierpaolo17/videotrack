# Aprire la pagina originale del video Microsoft

La266 aggiunge un’[anteprima diagnostica privata](37_MICROSOFT_PRIVATE_PREVIEW.md) con l’account aziendale/scolastico collegato dell’amministratore corrente.
CLI metadati e azione pagina originale conservano i propri contratti; player studente tracciato
e validazione Microsoft reale con browser restano gate distinti.

La 1.7.264 aggiunge [Collega il mio account Microsoft](35_MICROSOFT_USER_CONNECTION.md)
alla pagina amministrativa esistente. Avvia OAuth core solo con POST protetto esplicito;
il normale GET diagnostico resta locale. Prove native264 e tenant reale da svolgere.

VideoTrack **1.7.269** (`2026112300`).

## Procedura amministrativa

Aprire **Amministrazione del sito → Plugin → Moduli attività → VideoTrack → Verifica
connessione Microsoft**. Scegliere **Azione → Recupera il link alla pagina Microsoft**
e **Account da verificare → Il mio account Microsoft collegato**. Inserire entrambi
gli ID opachi di drive e file video, poi **Verifica accesso Microsoft**. Il default
resta verifica metadati, ancora disponibile con account sistema o personale.

Solo POST/sesskey validi e `moodle/site:config` nel sistema possono risolvere il link.
Modulo e controller richiedono account personale; il backend verifica inoltre permesso,
target, configurazione locale, endpoint OAuth supportati e login client prima di Graph.
Nessun ripiego su sistema o selettore di un altro utente. Usare lo stesso issuer e la
connessione core descritti nella [guida personale](33_MICROSOFT_USER_ACCESS.md). Core
può rinnovare/invalidare le proprie credenziali; VideoTrack non conserva token.

L’azione esegue un solo GET verso host fisso:
`/v1.0/drives/{driveId}/items/{itemId}?$select=id,file,video,size,deleted,webUrl`.
Riutilizza trasporto, controlli HTTP/JSON e validazione dei metadati video esistenti.
Redirect e retry restano disabilitati; timeout connessione/totale Graph5/15secondi.
Il limite1MiB riguarda il parsing JSON dopo la ricezione, non il trasferimento streaming.
Non usa endpoint contenuto, preview, download, condivisione o scrittura permessi.

## Esito e link privato

| Stato | Significato |
|---|---|
| `provider_link_ready` | Metadati video validi e destinazione della pagina supportata; è possibile mostrare il link privato |
| `provider_link_unavailable` | Metadati video validi ma webUrl assente o non stringa |
| `provider_link_unsupported` | Destinazione restituita fuori dai limiti URI di questo primo perimetro |
| Errori configurazione/OAuth/Graph esistenti | Nessun link; restano i significati diagnostici ridotti già previsti |

Con esito pronto compare **Apri in Microsoft**, in una nuova scheda. L’URL resta
separato dal JSON diagnostico; anche serializzare l’oggetto esito lo omette. È reso
solo nella risposta autorizzata, non memorizzato in VideoTrack, in cache di sessione
o in un evento privo di contenuti. Non viene inviato via email, copiato in un’attività
o pubblicato. Pagina non memorizzabile in cache; link con `noopener noreferrer` e
`referrerpolicy=no-referrer`. Non è un iframe né un redirect automatico.

Questo perimetro accetta sottodomini HTTPS di `sharepoint.com`, inclusi i siti personali
OneDrive for Business. Rifiuta userinfo, porte diverse da443, fragment, spazi/caratteri
di controllo/backslash grezzi, URL oltre8192byte e chiavi query note contenenti bearer:
`access_token`, `tempauth`, `authkey`, `token`, `sig`, `signature`, anche codificate.
OneDrive personale, cloud nazionali e altri host restano esclusi. I byte dell’URL non
sono ricostruiti o riscritti. Il controllo query è un limite conservativo, non una
prova di tutte le semantiche di autenticazione del tenant.

Gli ID stabili forniti vengono risolti a ogni invio esplicito: un file rinominato può
restituire il proprio webUrl corrente. Spostamenti fra drive o sostituzioni possono
richiedere nuovi ID. È apertura amministrativa, non un riferimento memorizzato
nell’attività o il futuro resolver automatico al lancio del video.

## Confine tra identità e tracciamento

Graph usa l’identità Microsoft delegata dell’utente Moodle corrente. La nuova scheda
usa l’accesso Microsoft del browser: può essere un altro account, richiedere login,
essere negato o comportarsi diversamente secondo le politiche tenant. La release
non prova riproduzione riuscita, stessa identità browser o diritti del singolo studente.
Microsoft mostra la pagina della risorsa; VideoTrack non fornisce un controller player.

Non vengono accreditati secondi visti, progresso, completamento nativo, voti o evento
VideoTrack di visualizzazione attività. Moodle non importa, trasmette o fa da proxy
ai media. I tre interruttori sorgente e il comportamento YouTube/Vimeo/HTML5 restano
invariati, come nove contratti AJAX, schema e AMD sorgenti/build/mappe.

## Futuro incorporamento nativo

Microsoft annuncia cambi di autenticazione delle preview dal1aprile2027: un iframe
non deve presumere un URL utilizzabile senza autenticazione. Serviranno audience
corretta e telemetria supportata di tempo/play/pausa/seek. Qui si apre solo la pagina
originale, senza chiamare API preview/contenuto.

Venti metodi unitari nativi coprono URI, serializzazione/riservatezza, un solo GET
metadati limitato, validazione video, errori, controlli personali/permessi, conservazione
dati e contratti modulo/controller. Tre scenari browser non selezionano issuer né usano
rete esterna. Esecuzione nativa263 e tenant/browser reali da verificare. La baseline262
accettata è Unit488/5267, Behat63/1287, server e due strict10/0/0; non è esecuzione263.
Conservare la demo; manuale53 in standby.

## Riferimenti primari

- [Proprietà driveItem webUrl](https://learn.microsoft.com/en-us/graph/api/resources/driveitem?view=graph-rest-1.0)
- [GET metadati Graph](https://learn.microsoft.com/en-us/graph/api/driveitem-get?view=graph-rest-1.0)
- [Annuncio Microsoft transizione autenticazione preview,8ottobre2026](https://devblogs.microsoft.com/microsoft365dev/pre-authenticated-tempauth-urls-are-retiring-move-to-microsoft-entra-id-tokens-before-april-1-2027/)


La fondazione corretta fino alla265 ha superato CI candidato/main, pacchetto/server
reale e due strict. La266 e tenant/browser reali sono ancora da verificare. Vedere
l’[audit](09_DOCUMENTATION_AUDIT.md).

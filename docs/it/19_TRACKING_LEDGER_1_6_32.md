# Registro di riproduzione autorevole lato server nella 1.6.32

## Scopo

La release 1.6.32 chiude due debolezze del confine di fiducia nella persistenza dei segmenti visti: il primo segmento non riceve più credito di riproduzione non maturato e un retry di trasporto non può più inserire due volte lo stesso segmento logico.

## Handshake di riproduzione

Prima di aprire un segmento tracciato, il player chiama `mod_videotrack_start_playback`. Il servizio:

- valida sesskey Moodle, contesto dell’attività e capability `mod/videotrack:participate`;
- registra una riga di ledger `playstart` a durata zero con `servervalidated = 0`;
- memorizza il timestamp server corrente in millisecondi;
- non assegna tempo visto né progresso di completamento.

Un evento PLAY del provider avvia il tracking soltanto dopo il successo dell’handshake. Pausa e fine invalidano la continuazione client pendente, impedendo a una risposta tardiva di aprire un segmento obsoleto. L’handshake usa un seriale di ciclo di vita dedicato ed è intenzionalmente indipendente dallo scope AJAX condiviso usato dagli altri controlli. Le notifiche `play` duplicate di Vimeo ricevute mentre un segmento è già aperto vengono ignorate dal ledger, quindi non possono azzerare la finestra di credito server.

## Credito basato sul tempo server

Ogni segmento candidato viene addebitato a un credito cumulativo autorizzato dal server:

1. viene misurato il tempo server trascorso dall’ultimo handshake o richiesta;
2. il tempo trascorso è limitato a una finestra heartbeat più il margine di rete già previsto e limitato;
3. la velocità consentita per l’attività converte il tempo reale nel massimo credito video;
4. il segmento è accettato soltanto se il totale dei secondi video accreditati resta entro il budget cumulativo, con meno di un secondo complessivo di tolleranza tra clock provider e server;
5. la deriva tollerata resta un debito fra richieste e nuovi handshake; l’eventuale margine positivo non usato viene eliminato, quindi pause, replay e richieste rifiutate non possono trasformare la tolleranza in credito ripetibile;
6. una richiesta segmento non può aprire implicitamente una finestra di credito: può farlo soltanto l’handshake esplicito di riproduzione.

Durata salvata dal docente, velocità consentite e policy sul seek avanti restano autorevoli. Durata e wall-clock dichiarati dal client restano soltanto diagnostici.

## Richieste segmento idempotenti

Ogni handshake e segmento trasporta un identificativo casuale di 32 caratteri, generato una sola volta prima del livello condiviso di retry. `videotrack_seg.requestid` è protetto da un indice univoco su attività, utente e identificativo richiesta.

Quando un retry riusa lo stesso identificativo:

- un payload identico già persistito restituisce lo stato corrente senza inserire una nuova riga;
- un payload differente con lo stesso identificativo viene rifiutato;
- completion ed eventi Moodle non vengono emessi due volte;
- secondi grezzi e Analytics delle revisioni non vengono gonfiati dalla perdita della risposta seguita da retry.

In upgrade le righe storiche ricevono identificativi deterministici `legacy…`. Il restore conserva gli identificativi validi e genera valori `restore…` deterministici per backup precedenti. I contatori del credito runtime restano esclusi dal backup e vengono azzerati al restore.

## Limite degli intervalli e progresso monotono

`intervaljson` resta limitato a 500 intervalli memorizzati per contenere payload e database. Al raggiungimento del limite, la copertura unica esatta viene ricalcolata da tutti i segmenti grezzi con `servervalidated = 1` prima di salvare la rappresentazione compatta. `uniquecoveredseconds` è monotono e non può diminuire solo perché piccoli frammenti vengono omessi dalla rappresentazione compatta.

## Protezione dei dati

L’identificativo richiesta è un token operativo di idempotenza, non una credenziale di autenticazione. È dichiarato nei metadati/esportazione Privacy API e nel backup con dati utente perché appartiene a un record di riproduzione identificabile. Non autorizza mai una richiesta: login Moodle, sesskey, contesto e capability restano i controlli autorevoli.

## Verifiche attese

Una release che applica questo contratto deve verificare:

- nuova installazione e upgrade dalla 1.6.31;
- rifiuto del primo segmento privo di handshake;
- assenza di credito iniziale anche su video brevi;
- velocità massima consentita limitata dal tempo server realmente trascorso;
- retry di richieste accettate o rifiutate senza nuove righe o progresso;
- copertura cumulativa esatta oltre 500 segmenti disgiunti;
- attesa dell’handshake e cancellazione delle risposte obsolete nei tre adapter;
- coerenza di PHP, XMLDB, Privacy API, backup/restore, PHPUnit, PHPCS e build AMD Moodle reale.

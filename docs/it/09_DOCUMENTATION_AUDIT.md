# Audit della documentazione

Candidata corrente: VideoTrack **1.7.275** (`2026112700`).

## Baseline accettata

ZIP reale 274 SHA256 31f6f085edfe3f1c82f4a6eb418bb3ce08c26b3ed934047741e9e61435bdb220:
501 file identici all’export Git, main 5e939e64/albero ec807599; tag v1.7.274 sullo stesso main.
Run candidata 45895305108 e main 45900637544 superano sei job ciascuno: Unit 656/6922,
Behat 84/1571 senza retry, JS 38+harness, strict 10/0/0 e hash nativi 48 sorgenti/96 build esatti.
Entrambi i lifecycle popolati superano tutte e tre le linee storiche; ricostruiti
indipendentemente sei hash di conservazione. Server PHP attivi fino alla pulizia voluta.
Report server SHA256 90e30bbfb023187db2a0fd404d9235cb5dd3e6c5db5140446a700d082649e82e:
otto esiti superati su 50/53, canonici/lint condivisi 210 puliti, Unit 656/6922 e
Grunt invariati. La baseline installabile ha 209 PHP; i 210 condivisi comprendono
l’helper lifecycle solo repository. Strict del maintainer 10/0/0 su entrambi.
Confermate il 2026-11-27 le prove manuali di duplicazione, CSV e ricalcolo ordinario.
La 274 è completamente accettata. Restano avviso Browserslist sui dati datati e
debito informativo Psalm: non sono dichiarati zero.

## Perimetro 275 ed esecuzioni attese

Vedere [diagnostica dell’attività salvata](45_MICROSOFT_ACTIVITY_CHECK.md). Permessi
sito e corso proprietario precedono la lettura del riferimento. Ogni richiesta lo
rilegge; il POST vincolato usa solo account personale e operazione allowlist.
GET locale. Nessun campo identità/account modificabile o credenziale nel link.
Conservati diagnostica generale, callback OAuth core, contratto Graph, anteprima
privata, tracciamento, voti, completamento, recupero, schema, servizi, ruoli, Privacy e AMD.
Aggiunti quindici metodi PHP e due scenari browser, mantenendo i test precedenti.
L’asserzione del controller conserva il POST con contesto opzionale del server.
Nessun runtime PHP/Moodle locale: attesi lint/PHPCS/PHPUnit/Behat/analisi statica
nativi 275, CI, entrambi i lifecycle popolati, server e prove manuali.
I controlli sorgenti e la 274 accettata non dimostrano esecuzione nativa 275.
HTTPS e account Microsoft non disponibili; accesso delegato reale e telemetria
supportata restano necessari prima del player studente. Nessuno ZIP prodotto qui.

## Conteggi correnti

534 file repository/505 distribuiti; 211 PHP repository/210 distribuiti.
1639 dichiarazioni PHP (1632 distribuite), 668 AMD; 671 metodi unitari;
86 scenari Behat espansi/1600 passi; 8×1083 chiavi; 48 set AMD.
Schema invariato: 7 tabelle/152 campi/22 indici espliciti/22 FK; 9 servizi AJAX.

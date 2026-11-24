# Audit della documentazione

Candidata corrente: **1.7.271** (`2026112401`), basata sullo ZIP270 effettivo accettato.

## Accettazione270

483file distribuiti corrispondono a main63325ff0923543bda9ae3548a2f713cb80bae4b9,
albero61bc66351d2a1cb24508049aa123011a9544bbe8; SHA256 ZIP
3927f8ec40f199f52fe5d5f92d490f648244cdbcb59fea17569bc06e1711aa2d.
Candidato corretto45392734528/main45424709649 superano sei job ciascuno, Unit620/6483,
Behat78/1458senza retry, Grunt nativo, strict10/0/0, regressioni JS e due matrici
storiche aggiornamento/ripetizione/disinstallazione popolata con fotografie verificate.
Il report server20261124_173403 supera otto comandi: canonici/lint203condivisi,
Unit620/6483su50/53 ed entrambi i Grunt con hash invariati. Entrambi i report strict
utente superano10/0/0. L’utente conferma dati demo e grafica corretti: conservarli.

Restano visibili PHPMD36avvisi e Psalm ParadoxicalCondition sulla guardia standard
MOODLE_INTERNAL in db/services.php25, più758/786informazioni. PHPStan pulito.
Sono risultati270, non prove dell’esecuzione271.

## Modifiche e verifiche da completare

La [guida riferimenti attività](41_MICROSOFT_ACTIVITY_REFERENCES.md) descrive metadati,
permessi, associazione locale esplicita e backup portabile, senza concedere accesso.
XMLDB aggiunge un campo nullable (152totali); restano9metodi AJAX e48gruppiAMD.
La fixture lifecycle attende il nuovo NULL conservando tutte le precedenti asserzioni.
Il fix urgente di tracciamento/recupero e la casella temporanea restano presenti.

Conteggi: 516file repository/490distribuiti, 206PHP distribuiti,
1582callablePHP incluse7helperCI,660AMD,640metodi unitari,
80scenari espansi/1484passi e8lingue con1076chiavi. Tutti620metodi unitari precedenti,
78scenari e relative risorse restano identici. Inventari alle posizioni effettive.

Controlli locali: contratti sorgente/lessicali/upgrade, codice studente conservato,
lingue/placeholder, mappe esistenti esatte, regressioni JS e patch reversibile.
PHP/Moodle/MariaDB non sono disponibili localmente: restano CI271 lint/PHPCS,
PHPUnit, Behat, Grunt, analisi, lifecycle e test server. OAuth/ricerca/anteprima reali,
eventi browser e telemetria studente attendibile richiedono HTTPS e account reale.
Microsoft resta indisponibile come sorgente tracciata. Nessuno ZIP plugin generato:
lo produce il manutentore dopo CI main accettata. Roadmap provider/App e accessibilità
manuale5.3 mantengono ordinamento e stato precedenti.

Quattro script Node ausiliari storici (backup_restore_static, privacy_static,
performance_static, deprecation_static) falliscono sia sulla270accettata sia sulla271:
attendono vecchie strutture sorgente o docs/struttura_tecnica.md. Restano invariati e
non sono risultati CI nativi. Passano tracker, sei gruppi sorgenti/sei compilati e adapter.

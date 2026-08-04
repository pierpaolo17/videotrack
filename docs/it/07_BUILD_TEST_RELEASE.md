# Build, test e release

## Ambiente

- Moodle 5.0+
- PHP 8.2+
- Node 22.x according to Moodle requirement
- Moodle Grunt toolchain installed in the Moodle root

## PHPUnit

```bash
cd /var/www/default-ssl/m45/moodle
php admin/tool/phpunit/cli/init.php
vendor/bin/phpunit --testsuite mod_videotrack_testsuite
```

Le deprecazioni PHPUnit 11 sui docblock `@covers` sono note: servono a soddisfare lo sniff Moodle `TestCaseCovers` finché moodle-cs non supporterà pienamente gli attributi nel caso d’uso del plugin.

## PHPCS

```bash
/root/.config/composer/vendor/bin/phpcs --standard=moodle-extra /var/www/default-ssl/m45/moodle/mod/videotrack > phpcs_extra.json
```

## AMD

```bash
cd /var/www/default-ssl/m45/moodle
ulimit -n 65535
export UV_THREADPOOL_SIZE=4
export NODE_OPTIONS="--max-old-space-size=4096"
node node_modules/grunt/bin/grunt amd --root=mod/videotrack --force > ../grunt_amd.txt 2>&1
```

## Patch

```bash
git apply --check videotrack-x.y.z.patch
patch -p1 --dry-run < videotrack-x.y.z.patch
```

La patch deve essere generata dalla root del plugin. Percorsi assoluti o temporanei dentro `/mnt/data` non sono accettabili.

## Checklist per release solo documentale

Per patch esclusivamente documentali, i controlli minimi sono:

```bash
php -l version.php
git apply --check <patchfile>
patch -p1 --dry-run < <patchfile>
```

Se cambiano gli inventari, rigenerare o verificare manualmente:

- inventario file rispetto al contenuto reale dello ZIP;
- inventario funzioni PHP rispetto ai file PHP distribuiti;
- inventario funzioni AMD rispetto ai file `amd/src`;
- inventario variabili come supporto di navigazione.

Una patch solo documentale non richiede `grunt amd`, PHPUnit o PHPCS, salvo modifiche a codice eseguibile. Se viene incrementato `version.php`, eseguire l'upgrade Moodle nell'ambiente target.

## Controlli di regressione integrazione Forum (1.5.0)

- creare e modificare un’istanza con la funzione disabilitata;
- configurare ciascun tipo Forum supportato (`general`, `qanda`, `blog`);
- verificare il comportamento di Forum nascosti e con restrizioni per docente e studente;
- verificare nessun gruppo, gruppi separati e appartenenza a più gruppi;
- verificare la gestione controllata di cutoff e soglie di pubblicazione;
- verificare che Annulla ritorni al frammento temporale del video;
- verificare che la discussione contenga solo testo confermato dallo studente e link di replay;
- ripristinare con e senza il Forum collegato e verificare la disabilitazione sicura quando manca la mappatura;
- ripetere i test player YouTube, Vimeo e HTML5 per tracking, seek, resume e replay.

## Controlli di regressione analytics per istanza (1.6.0)

- verificare che la sovrapposizione delle reazioni segnali il proprio limite di sicurezza senza crescita illimitata della memoria;

- verificare stati senza dati e con durata sconosciuta;
- verificare dataset sotto e sopra `analyticsminusers`;
- verificare intervalli e cluster di reazioni mascherati sotto soglia;
- confrontare copertura unica e tempo rivisto con segmenti sovrapposti;
- verificare filtro gruppo con gruppi consentiti e non disponibili;
- verificare descrizioni SVG accessibili e valori equivalenti nella tabella;
- confermare che le schede studente, cumulativa, CSV e ricalcolo completion non cambino;
- eseguire `analytics_test.php`, PHPCS Moodle + Extra e PHP lint. Questa fase non richiede build AMD.

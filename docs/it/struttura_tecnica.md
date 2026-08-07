# Struttura tecnica sintetica

- Pagine: `view.php`, `report.php`, `reports_course.php`, `reports_teacher.php`, `bookmarks.php`, `forum_post.php`, `presets.php`.
- Integrazione Moodle: `lib.php`, `mod_form.php`, `settings.php`, `db/*`, backup/restore e completamento.
- Dominio: `classes/local/*`; scritture AJAX: `classes/external/*`; eventi auditabili: `classes/event/*`.
- Browser canonico: `amd/src/*`; distribuzione generata: `amd/build/*`.
- Persistenza: sette tabelle XMLDB, File API e gradebook.
- Qualità: test PHPUnit, otto language pack, source map, privacy e documentazione numerata.

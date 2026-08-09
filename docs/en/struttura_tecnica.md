# Compact technical structure

- Entry pages: `view.php`, `report.php`, `reports_course.php`, `reports_teacher.php`, `bookmarks.php`, `forum_post.php`, `presets.php`.
- Moodle integration: `lib.php`, `mod_form.php`, `settings.php`, `db/*`, backup/restore and completion classes.
- Domain classes: `classes/local/*`; AJAX writes: `classes/external/*`; auditable events: `classes/event/*`.
- Canonical browser code: `amd/src/*`; generated distribution: `amd/build/*`.
- Persistence: seven XMLDB tables plus File API and gradebook.
- Quality: PHPUnit tests, eight language packs, source maps, privacy documentation and the numbered maintenance set.
- Retention: expired personal rows are deleted; derived state is rebuilt from retained trusted evidence and omitted from backup.

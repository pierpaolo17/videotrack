# mod_videotrack - Technical structure

**Documented version**: 1.6.8

This historical document has been replaced by the modular technical documentation introduced in release 1.4.248. The current source of truth is the numbered document set in this directory.

## Where to find information

- `00_INDEX.md`: general index.
- `01_DEVELOPER_GUIDE.md`: operational rules and patch workflow.
- `02_ARCHITECTURE.md`: architecture, AJAX services and database.
- `03_FILE_INVENTORY.md`: responsibility of every plugin file.
- `04_FUNCTION_INVENTORY.md`: complete PHP and AMD function inventory.
- `05_VARIABLE_INVENTORY.md`: static PHP and JavaScript variable inventory.
- `06_RUNTIME_FLOWS.md`: runtime flows for tracking, reactions, notes and replay.
- `07_BUILD_TEST_RELEASE.md`: build, test, PHPCS, PHPUnit and Grunt commands.
- `08_LESSONS_LEARNED.md`: lessons learned and maintenance rules.

The previous content has been replaced to avoid duplicated and obsolete information. Historical audit files with version suffixes remain available.


## Forum integration components (1.5.0)

`forum_post.php`, `classes/form/forum_post_form.php`, `classes/local/forum_bridge.php` and `amd/src/core/player/forum.js` implement the optional bridge. The bridge validates visibility, capabilities, groups and throttling twice and delegates creation to `mod_forum_external::add_discussion()`.

## Analytics components (1.6.0)

`classes/local/analytics.php` performs streaming, server-side aggregation over `videotrack_seg`. `classes/local/analytics_scope.php` identifies the same technical video across activities and capability-filters every instance; `report.php` applies permitted groups per activity, the configurable privacy threshold and accessible SVG rendering with an equivalent table. The cross-course filter is temporary and adds no aggregate database table or cache; query performance must be measured on real datasets before introducing persistence.

# Activity copies, CSV fields and completion maintenance

VideoTrack **1.7.274** (`2026112600`) preserves reusable activity settings,
clarifies CSV field access and retires the temporary historical-repair checkbox.

## Duplication and settings saves

Backup already contained playback speeds, CSV choices and the Forum relation.
The fixes address processing rather than adding duplicate backup fields:

- Fractional speed checkboxes use names such as `playbackspeed_0_5`. PHP changes
  dots in incoming variable names to underscores, so the former names did not
  survive browser POST parsing. Definition, defaults and normalisation now agree.
  Internal callers using older dotted aliases remain compatible.
- Stored scalar playback/control preferences survive a save without checkbox
  aliases. Unchanged inherited playback/control selections keep inheriting site defaults.
  CSV saves preserve disabled selections instead of deleting them.
  Empty CSV preferences still inherit site defaults; `none` remains explicit.
- Restore initially disables the optional Forum link and resolves it in
  `after_restore`, after all activity mappings are available. This includes
  configuration-only backups and a Forum located after VideoTrack in the course.
  A missing mapping may retain the existing compatible Forum only on the same
  site and in the same original/destination course. Other targets remain disabled
  with a warning. An unrelated same-numbered Forum is never selected by guesswork.

No schema migration or global settings rewrite is added. These corrections apply
to new copies and subsequent saves. Copies that already lost preferences need
manual correction from their intended original; their origin is not inferred.
Duplication continues to exclude learner viewing, reactions, notes and bookmarks.

## CSV availability and saved preferences

Populated user values and site-default selection do not themselves grant read
permission. Availability now distinguishes actual Moodle permissions from the
identity fields chosen for display in Moodle lists:

| Field category | Additional permitted access beyond the identity display list |
|---|---|
| Site administrator | Every supported standard field and custom field allowed by Moodle's administrator visibility. |
| Username, idnumber, institution, department | `moodle/user:viewalldetails` in the effective context, while preserving hidden-field restrictions. |
| City and country | Profile-detail permission and applicable hidden-field permission. |
| Email | The existing identity permission path or Moodle's course-email permission. |
| Phone/address fields | The existing authorised identity list, plus administrator access. |
| Custom profile fields | Moodle's field-visibility API and profile/identity permission; fully hidden fields require system permission. |

Fields that remain unavailable show a specific reason: identity configuration,
read permission, hidden-field permission or profile visibility. Custom visibility
uses the explicit course context instead of relying on the global course object.
No global Moodle identity setting or role capability is changed by the plugin.

A disabled checkbox keeps its saved preference on unrelated edits. This does not
make its data exportable: runtime field selection still filters by the exporting
account's effective permissions. Administrators can change those choices normally.
A newly forged checkbox cannot add an unavailable field. The existing bulk user
loader, CSV formula protection and report/learner-scope checks remain in place.

## Retiring temporary historical recovery

The report no longer renders or reads `recoverplayback`. Ordinary completion
recalculation remains available and uses retained, server-validated evidence.
Previously accepted `recovery269` rows remain valid; nothing is erased or rerun
automatically during installation. Normal recalculation does not append repairs.
The internal repair API remains for compatibility with existing administrative
code and its idempotency tests; it has no browser input path in this release.
No new recalculation is required just to install this update.

## Verification and remaining gates

The accepted baseline is the actual 273 ZIP, matching main `e3097257` and tree
`e413a886`: all six candidate/main jobs, native builds, both populated lifecycle
matrices, server 50/53 checks, strict validators and maintainer manual checks pass.
The 274 patch changes no AMD source, minified output or source map.

Eleven new PHPUnit methods exercise native duplication/course/partial restore,
PHP POST parsing plus QuickForm, actual CSV values, scoped permissions, hidden
fields, forged inputs and preference persistence. The recovery test also checks
that ordinary recalculation preserves accepted repair rows. Two new Behat scenarios
exercise real browser duplication plus an unrelated save and the remaining ordinary
recalculation form. Existing permission assertions remain in place.

Local source/inventory/language/schema/patch checks do not replace execution on
Moodle. PHP/PHPCS/PHPUnit and 274 native CI, lifecycle, server and manual tests are
pending until their actual reports are supplied. Future provider work still waits
for the separately planned HTTPS/Microsoft acceptance.

Primary references: [PHP incoming variable-name conversion](https://github.com/php/php-src/blob/PHP-8.3/main/php_variables.c),
[Moodle form submission](https://github.com/moodle/moodle/blob/MOODLE_500_STABLE/lib/formslib.php),
[Moodle user-detail permissions](https://github.com/moodle/moodle/blob/MOODLE_500_STABLE/user/lib.php),
[custom-profile visibility](https://github.com/moodle/moodle/blob/MOODLE_500_STABLE/user/profile/lib.php),
[deferred restore hook](https://github.com/moodle/moodle/blob/MOODLE_500_STABLE/backup/util/plan/restore_structure_step.class.php).

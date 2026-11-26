# Documentation audit

Current candidate: VideoTrack **1.7.274** (`2026112600`).

## Accepted baseline

The actual 273 ZIP SHA256 1f9f7fd2deeb01a7d374448e7fa80aabd33572229c9bee0cdb4c3d8e4327797f
contains 496 exact Git-exported files from main e3097257, tree e413a886. Candidate run
45837567111 and main run 45846097127 pass all six jobs: Unit 645/6663, Behat 82/1541
without internal retries, JS 38+harness, strict 10/0/0, exact 48 source/96 native-build
hashes and both populated lifecycle matrices. Six preservation hashes were rebuilt.
The observable PHP server survives each test run and exits only during intentional
cleanup. Earlier HTTP interruption causes remain unproven; this is not called a
root-cause fix merely because the new runs pass.

Actual server report SHA256 126163137969f410ba105dc1e1348d0a42d3a27ede7a70bfc836458aef9262c0
passes all eight requested outcomes: shared canon/lint 208 PHP, Unit 645/6663 on 50/53,
native Grunt on both without build changes. The 208 includes the repository-only
lifecycle helper; the installable baseline has 207 PHP. Both strict validators are
10/0/0. The maintainer confirms all manual duration/buttons/banner checks behave
as expected. Release 273 is accepted. The Grunt Browserslist data-age notice remains
nonblocking; static informational/advisory debt is not claimed zero.

## 274 scope and verification

See [copy/CSV/recalculation policy](44_ACTIVITY_COPY_CSV_MAINTENANCE.md). Forum links
are deferred until all mappings exist; same-course reuse requires the same site.
Fractional form names survive PHP POST parsing. Scalar and disabled CSV preferences
are retained without granting runtime data access. Profile/identity/hidden-field
permissions and per-field restriction labels are explicit. The historical repair
checkbox/request path is retired; ordinary recalculation and accepted rows remain.
No migration, historical-data rewrite, native grade/completion bypass, source-player
change or credential operation is added. Existing bad copies need manual correction.

Added 11 PHPUnit methods and 2 Behat scenarios; previous permission assertions remain.
The recovery test also checks accepted repair rows through ordinary recalculation.
No local PHP/Moodle runtime is available: PHP/PHPCS/PHPUnit and browser results for
274 are pending, not inferred from the previous release. Local checks cover token
structure, source/asset identity, languages, schema, inventories and patch mechanics.
All 48 AMD source/build/map sets are byte-identical to the accepted ZIP; no extra
native build artifact is needed before issuing this PHP-only candidate.

Counts: 530 repository/501 distributed files, 210 repository PHP/209 distributed,
1617 named PHP declarations (1610 distributed), 668 named AMD, 656 unit methods,
84 expanded Behat scenarios/1571 steps, 8 packs x 1080 keys, 48 AMD sets. Schema remains
7 tables/152 fields/22 indexes/22 foreign keys;9 AJAX methods. Current evidence must be
updated from actual 274 CI, both lifecycle matrices, server and manual reports before
release acceptance. HTTPS/Microsoft provider acceptance stays separate.

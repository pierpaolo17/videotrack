# Data and configuration inventory

This document records persistent fields and public configuration contracts. Local implementation variables remain documented by source type declarations, DocBlocks and JSDoc.

## XMLDB tables

### `videotrack`

Main table for videotrack instances

| Field | Type | Null/default | Comment |
|---|---|---|---|
| `id` | `int`(10) | NOT NULL |  |
| `course` | `int`(10) | NOT NULL; default `0` |  |
| `name` | `char`(255) | NOT NULL |  |
| `intro` | `text` | nullable |  |
| `introformat` | `int`(4) | NOT NULL; default `0` |  |
| `youtubeurl` | `text` | nullable |  |
| `videoid` | `char`(32) | nullable; default `` |  |
| `videosource` | `char`(20) | NOT NULL; default `youtube` | youtube | vimeo | upload |
| `videourl` | `text` | nullable | Vimeo URL or upload filename reference |
| `playbackspeeds` | `char`(100) | nullable; default `` | Comma-separated allowed speeds; empty = site default |
| `autoplay` | `int`(1) | NOT NULL; default `0` | Start video automatically |
| `loopenabled` | `int`(1) | NOT NULL; default `0` | Loop video when ended |
| `startmuted` | `int`(1) | NOT NULL; default `0` | Start video muted |
| `allowdownload` | `int`(1) | NOT NULL; default `0` | Show download control (upload source only) |
| `html5controls` | `char`(255) | nullable; default `` | Comma-separated list of HTML5 player controls; empty = site default |
| `playerwidth` | `int`(5) | NOT NULL; default `0` | Max player width in px; 0 = use site default |
| `rewindstep` | `int`(3) | NOT NULL; default `0` | Rewind seconds per click; 0 = use site default |
| `fastforwardstep` | `int`(3) | NOT NULL; default `0` | Fast-forward seconds per click; 0 = use site default |
| `captions` | `int`(1) | NOT NULL; default `0` | Enable captions/subtitles |
| `captionslang` | `char`(10) | nullable; default `` | Default caption language code (e.g. it, en) |
| `durationseconds` | `number`(10) | NOT NULL; default `0.000` |  |
| `showcontrols` | `int`(1) | NOT NULL; default `1` |  |
| `disablekeyboard` | `int`(1) | NOT NULL; default `0` |  |
| `showfullscreen` | `int`(1) | NOT NULL; default `1` |  |
| `allowseekforward` | `int`(1) | NOT NULL; default `1` |  |
| `allowseekbackward` | `int`(1) | NOT NULL; default `1` |  |
| `allowplaybackratechange` | `int`(1) | NOT NULL; default `1` |  |
| `resumeplayback` | `int`(1) | NOT NULL; default `0` | Resume playback from last saved position |
| `maxplaybackrate` | `int`(4) | NOT NULL; default `0` | Max playback rate in centesimal (0=no limit, 150=1.5x, 200=2x) |
| `blockedseekplaybackrate` | `int`(4) | NOT NULL; default `50` | Playback rate after blocked forward seek in centesimal (50=0.5x, 100=1x) |
| `showtranscript` | `int`(1) | NOT NULL; default `0` | Show the interactive VTT transcript panel for supported video sources |
| `showchapters` | `int`(1) | NOT NULL; default `0` | Show the VTT-based chapter navigation bar for supported video sources |
| `studentnotesenabled` | `int`(1) | NOT NULL; default `0` | Allow students to write personal timestamped notes while watching |
| `bookmarksenabled` | `int`(1) | NOT NULL; default `0` | Allow students to save private named bookmarks at watched timestamps |
| `integrityindicatorsenabled` | `int`(1) | NOT NULL; default `0` | Record privacy-safe diagnostic integrity signals |
| `pauseonfocusloss` | `int`(1) | NOT NULL; default `0` | Pause when the page is hidden; window-focus behaviour follows the site accessibility policy |
| `preventpictureinpicture` | `int`(1) | NOT NULL; default `0` | Best-effort prevention of Picture-in-Picture playback |
| `randomfocuspauses` | `int`(1) | NOT NULL; default `0` | Pause playback after a random active interval to prompt learner attention |
| `acknowledgementenabled` | `int`(1) | NOT NULL; default `0` | Require an explicit learner acknowledgement for the configured statement |
| `acknowledgementtext` | `text` | nullable | Teacher-authored acknowledgement statement |
| `acknowledgementformat` | `int`(4) | NOT NULL; default `1` | Text format for acknowledgement statement |
| `acknowledgementtiming` | `int`(1) | NOT NULL; default `0` | 0=confirmation at any time; 1=confirmation only after the final video second |
| `completionacknowledgement` | `int`(1) | NOT NULL; default `0` | Use current acknowledgement as a custom completion condition |
| `forumpostingenabled` | `int`(1) | NOT NULL; default `0` | Allow students to compose a forum discussion from the current video time |
| `linkedforumid` | `int`(10) | NOT NULL; default `0` | Linked forum instance id in the same course |
| `forumsubjecttemplate` | `text` | nullable | Forum discussion subject template with {timestamp} and {activity} placeholders |
| `csvdelimiter` | `char`(20) | NOT NULL; default `inherit` | CSV delimiter override: inherit, comma, semicolon, section, hash or pipe |
| `csvexportfields` | `text` | nullable | Comma-separated optional context and identity fields included in CSV exports |
| `countbyvideotime` | `int`(1) | NOT NULL; default `1` |  |
| `completionpercent` | `int`(3) | NOT NULL; default `0` |  |
| `reactionsenabled` | `int`(1) | NOT NULL; default `0` |  |
| `reactionsrequired` | `int`(1) | NOT NULL; default `0` |  |
| `minreactions` | `int`(10) | NOT NULL; default `0` |  |
| `requireallreactiontypes` | `int`(1) | NOT NULL; default `0` |  |
| `completionlogic` | `char`(10) | NOT NULL; default `and` |  |
| `clusterwindow` | `int`(3) | NOT NULL; default `30` |  |
| `showstudentreport` | `int`(1) | NOT NULL; default `1` |  |
| `showreactionnotice` | `int`(1) | NOT NULL; default `1` |  |
| `reactionnoticeformat` | `int`(4) | NOT NULL; default `1` |  |
| `reactionnotice` | `text` | nullable |  |
| `grade` | `int`(10) | NOT NULL; default `0` | 0=no grade, >0=max points, <0=scale id (negative) |
| `gradepass` | `number`(10) | NOT NULL; default `0.00000` | Minimum grade to pass (gradepass in gradebook) |
| `showgradeto` | `int`(1) | NOT NULL; default `0` | 1=show grade to student in view.php |
| `timemodified` | `int`(10) | NOT NULL; default `0` |  |
| `timecreated` | `int`(10) | NOT NULL; default `0` |  |

### `videotrack_seg`

Watched segments

| Field | Type | Null/default | Comment |
|---|---|---|---|
| `id` | `int`(10) | NOT NULL |  |
| `videotrackid` | `int`(10) | NOT NULL; default `0` |  |
| `courseid` | `int`(10) | NOT NULL; default `0` |  |
| `cmid` | `int`(10) | NOT NULL; default `0` |  |
| `userid` | `int`(10) | NOT NULL; default `0` |  |
| `videoid` | `char`(32) | NOT NULL |  |
| `sessionid` | `char`(64) | NOT NULL |  |
| `wallclockstart` | `int`(10) | NOT NULL; default `0` |  |
| `wallclockend` | `int`(10) | NOT NULL; default `0` |  |
| `videotimestart` | `number`(10) | NOT NULL; default `0.000` |  |
| `videotimeend` | `number`(10) | NOT NULL; default `0.000` |  |
| `playbackrate` | `number`(6) | NOT NULL; default `1.000` |  |
| `endreason` | `char`(32) | NOT NULL; default `unknown` |  |
| `timecreated` | `int`(10) | NOT NULL; default `0` |  |

### `videotrack_state`

Aggregated unique coverage per user and activity

| Field | Type | Null/default | Comment |
|---|---|---|---|
| `id` | `int`(10) | NOT NULL |  |
| `videotrackid` | `int`(10) | NOT NULL; default `0` |  |
| `courseid` | `int`(10) | NOT NULL; default `0` |  |
| `cmid` | `int`(10) | NOT NULL; default `0` |  |
| `userid` | `int`(10) | NOT NULL; default `0` |  |
| `videoid` | `char`(32) | NOT NULL |  |
| `lastposition` | `number`(10) | NOT NULL; default `0.000` |  |
| `durationseconds` | `number`(10) | NOT NULL; default `0.000` |  |
| `uniquecoveredseconds` | `number`(10) | NOT NULL; default `0.000` |  |
| `completionpercent` | `number`(6) | NOT NULL; default `0.00` |  |
| `intervaljson` | `text` | nullable |  |
| `iscompleted` | `int`(1) | NOT NULL; default `0` |  |
| `timemodified` | `int`(10) | NOT NULL; default `0` |  |
| `timecreated` | `int`(10) | NOT NULL; default `0` |  |

### `videotrack_integrity`

Privacy-safe diagnostic integrity signals

| Field | Type | Null/default | Comment |
|---|---|---|---|
| `id` | `int`(10) | NOT NULL |  |
| `videotrackid` | `int`(10) | NOT NULL; default `0` |  |
| `courseid` | `int`(10) | NOT NULL; default `0` |  |
| `cmid` | `int`(10) | NOT NULL; default `0` |  |
| `userid` | `int`(10) | NOT NULL; default `0` |  |
| `videoid` | `char`(32) | NOT NULL |  |
| `sessionid` | `char`(64) | NOT NULL |  |
| `eventtype` | `char`(32) | NOT NULL |  |
| `videotime` | `number`(10) | NOT NULL; default `0.000` |  |
| `timecreated` | `int`(10) | NOT NULL; default `0` |  |

### `videotrack_react`

Configured reactions per activity

| Field | Type | Null/default | Comment |
|---|---|---|---|
| `id` | `int`(10) | NOT NULL |  |
| `videotrackid` | `int`(10) | NOT NULL; default `0` |  |
| `reactionkey` | `char`(100) | NOT NULL |  |
| `label` | `char`(255) | NOT NULL |  |
| `description` | `text` | nullable |  |
| `icontype` | `char`(10) | NOT NULL; default `emoji` |  |
| `iconvalue` | `char`(255) | NOT NULL |  |
| `requiredforcompletion` | `int`(1) | NOT NULL; default `0` |  |
| `sortorder` | `int`(10) | NOT NULL; default `0` |  |
| `isdeleted` | `int`(1) | NOT NULL; default `0` |  |
| `timecreated` | `int`(10) | NOT NULL; default `0` |  |
| `timemodified` | `int`(10) | NOT NULL; default `0` |  |

### `videotrack_reactev`

Reaction click events

| Field | Type | Null/default | Comment |
|---|---|---|---|
| `id` | `int`(10) | NOT NULL |  |
| `videotrackid` | `int`(10) | NOT NULL; default `0` |  |
| `courseid` | `int`(10) | NOT NULL; default `0` |  |
| `cmid` | `int`(10) | NOT NULL; default `0` |  |
| `userid` | `int`(10) | NOT NULL; default `0` |  |
| `videoid` | `char`(32) | NOT NULL |  |
| `sessionid` | `char`(64) | NOT NULL |  |
| `reactionid` | `int`(10) | NOT NULL; default `0` |  |
| `reactionkey` | `char`(100) | NOT NULL |  |
| `reactionlabel` | `char`(255) | NOT NULL |  |
| `reactiondesc` | `text` | nullable |  |
| `notetext` | `text` | nullable | Private text for notes or bookmark labels |
| `notetype` | `char`(20) | NOT NULL | Empty=reaction; note=student note; bookmark=private bookmark |
| `videotime` | `number`(10) | NOT NULL; default `0.000` |  |
| `playbackrate` | `number`(6) | NOT NULL; default `1.000` |  |
| `isdeleted` | `int`(1) | NOT NULL; default `0` |  |
| `timecreated` | `int`(10) | NOT NULL; default `0` |  |
| `timemodified` | `int`(10) | NOT NULL; default `0` |  |

### `videotrack_acknowledge`

Explicit learner acknowledgements of versioned activity statements

| Field | Type | Null/default | Comment |
|---|---|---|---|
| `id` | `int`(10) | NOT NULL |  |
| `videotrackid` | `int`(10) | NOT NULL; default `0` |  |
| `courseid` | `int`(10) | NOT NULL; default `0` |  |
| `cmid` | `int`(10) | NOT NULL; default `0` |  |
| `userid` | `int`(10) | NOT NULL; default `0` |  |
| `statementhash` | `char`(64) | NOT NULL |  |
| `instanceversion` | `int`(10) | NOT NULL; default `0` |  |
| `viewedseconds` | `number`(10) | nullable | Unique covered seconds at confirmation time; null for legacy confirmations |
| `viewedpercent` | `number`(6) | nullable | Video coverage percentage at confirmation time; null for legacy confirmations |
| `timeconfirmed` | `int`(10) | NOT NULL; default `0` |  |

## Site settings

All keys are stored under `mod_videotrack`.

- `analyticsminusers`
- `bookmarkmaxlength`
- `bookmarksenabled`
- `csvdelimiter`
- `csvexportfields`
- `default_allowdownload`
- `default_allowplaybackratechange`
- `default_allowseekbackward`
- `default_allowseekforward`
- `default_autoplay`
- `default_captions`
- `default_captionslang`
- `default_clusterwindow`
- `default_completionpercent`
- `default_disablekeyboard`
- `default_loop`
- `default_showcontrols`
- `default_showfullscreen`
- `default_startmuted`
- `distractionfree`
- `fastforwardstep`
- `focuslossgraceseconds`
- `focuslosspolicy`
- `gd_missing_warning`
- `heading_accessibility`
- `heading_csvexport`
- `heading_defaults`
- `heading_html5controls`
- `heading_integrity`
- `heading_performance`
- `heading_player`
- `heading_playerbehavior`
- `heading_presets`
- `heading_privacy`
- `heartbeatinterval`
- `html5controls`
- `maxplaybackrate`
- `notemaxlength`
- `playbackspeeds`
- `playerwidth`
- `presets_link`
- `randompausemaxseconds`
- `randompauseminseconds`
- `reactionannouncementinterval`
- `reactionreadydebouncems`
- `reportclusterlimit`
- `reportnotespagesize`
- `resumeplayback`
- `retention_unlimited_warning`
- `retentionperioddays`
- `retentionunlimitedconfirmed`
- `rewindstep`
- `statuserrortimeoutms`
- `statusinfotimeoutms`
- `strictsessionvalidation`
- `studentnotesenabled`
- `validationfallbackdays`

## AJAX services

- `mod_videotrack_save_integrity_event` — authenticated write service with `mod/videotrack:view`.
- `mod_videotrack_save_segment` — authenticated write service with `mod/videotrack:view`.
- `mod_videotrack_save_reaction` — authenticated write service with `mod/videotrack:view`.
- `mod_videotrack_delete_reaction` — authenticated write service with `mod/videotrack:view`.
- `mod_videotrack_delete_note` — authenticated write service with `mod/videotrack:view`.
- `mod_videotrack_save_bookmark` — authenticated write service with `mod/videotrack:view`.
- `mod_videotrack_delete_bookmark` — authenticated write service with `mod/videotrack:view`.
- `mod_videotrack_save_note` — authenticated write service with `mod/videotrack:view`.

## Browser player configuration keys

The JSON script element created by `view.php` is the single server-to-player configuration contract.

- `cmid`
- `videoid`
- `videosource`
- `showcontrols`
- `disablekeyboard`
- `showfullscreen`
- `allowseekforward`
- `allowseekbackward`
- `allowplaybackratechange`
- `autoplay`
- `loop`
- `startmuted`
- `allowdownload`
- `playbackspeeds`
- `html5controls`
- `playerwidth`
- `rewindstep`
- `fastforwardstep`
- `captions`
- `captionslang`
- `vtturl`
- `showtranscript`
- `transcripttracks`
- `transcriptdefaultlanguage`
- `showchapters`
- `chapterurl`
- `chapterlegacymode`
- `posterurl`
- `chapterslabel`
- `chapterlabel`
- `chaptersunavailablelabel`
- `chaptersloadinglabel`
- `transcriptloadinglabel`
- `transcriptsearchlabel`
- `transcriptsearchplaceholder`
- `transcriptresultslabel`
- `transcriptlanguagelabel`
- `timedtextseekblockedlabel`
- `timedtextseekfailedlabel`
- `requiredpercent`
- `origin`
- `reactionsenabled`
- `studentnotesenabled`
- `bookmarksenabled`
- `integrityindicatorsenabled`
- `pauseonfocusloss`
- `preventpictureinpicture`
- `randomfocuspauses`
- `randompauseminseconds`
- `randompausemaxseconds`
- `focuslosspolicy`
- `focuslossgracems`
- `focuspausedlabel`
- `randompausedlabel`
- `pipblockedlabel`
- `integrityerrorlabel`
- `bookmarkmaxlength`
- `bookmarksmaxrendered`
- `bookmarkreplaylabel`
- `removebookmarklabel`
- `bookmarkerrorlabel`
- `bookmarksavedlabel`
- `bookmarkdeletedlabel`
- `bookmarkemptylabel`
- `bookmarktoolonglabel`
- `bookmarkslimitedlabel`
- `bookmarksnonelabel`
- `notespaneltitle`
- `noteshidelabel`
- `noteshowlabel`
- `replaylabel`
- `removelabel`
- `removenotelabel`
- `noteerrorlabel`
- `notesavedlabel`
- `notedeletedlabel`
- `noteplaybackrequiredlabel`
- `noteemptylabel`
- `notetoolonglabel`
- `studentnoteslimitedlabel`
- `notesmaxrendered`
- `charsremaininglabel`
- `notemaxlength`
- `dismisslabel`
- `statusdefaultlabel`
- `statuserrorlabel`
- `rewindlabel`
- `fastforwardlabel`
- `secondslabel`
- `reactionerrorlabel`
- `reactionunavailablelabel`
- `reactionsreadylabel`
- `reactionannouncementinterval`
- `reactionreadydebouncems`
- `statusinfotimeoutms`
- `statuserrortimeoutms`
- `autoblockedlabel`
- `vimeocspwarnlabel`
- `sdkerrorlabel`
- `transcriptunavailablelabel`
- `nofilelabel`
- `html5controlslabel`
- `html5playlabel`
- `html5pauselabel`
- `html5seeklabel`
- `html5volumelabel`
- `html5mutelabel`
- `html5unmutelabel`
- `html5speedlabel`
- `html5piplabel`
- `html5fullscreenlabel`
- `html5downloadlabel`
- `html5elapsedlabel`
- `resumelabel`
- `beaconurl`
- `replaystart`
- `replayend`
- `resumeposition`
- `maxplaybackrate`
- `blockedseekplaybackrate`
- `heartbeatinterval`
- `videourl`
- `intervaljson`
- `duration`
- `forumpostbuttonid`
- `forumpoststatusid`
- `forumposturl`
- `forumposterrorlabel`

## File areas

- uploaded video source; poster; caption VTT; searchable transcript tracks; chapter VTT; uploaded reaction icons.

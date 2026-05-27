<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * VideoTrack plugin file.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Admin page for managing site-wide reaction presets.
 *
 * Accessible from Administration > Plugins > Activity modules > Video track
 * via the "Manage reaction presets" link in settings.php.
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/mod/videotrack/presets.php'));
$PAGE->set_title(get_string('presets:pagetitle', 'mod_videotrack'));
$PAGE->set_heading(get_string('presets:pagetitle', 'mod_videotrack'));
$PAGE->set_pagelayout('admin');

// -------------------------------------------------------------------------
// Actions.
// -------------------------------------------------------------------------
$action   = optional_param('action', '', PARAM_ALPHA);
$presetkey = optional_param('presetkey', '', PARAM_ALPHANUMEXT);
$deleteaction = optional_param('deleteaction', '', PARAM_ALPHA);

if ($deleteaction === 'delete' && !empty($presetkey) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_sesskey();
    $presets = videotrack_get_all_presets();
    $presets = array_filter($presets, fn($p) => ($p['key'] ?? '') !== $presetkey);
    videotrack_save_presets(array_values($presets));
    redirect($PAGE->url, get_string('presets:deleted', 'mod_videotrack'), null,
        \core\output\notification::NOTIFY_SUCCESS);
}

// -------------------------------------------------------------------------
// Handle add / edit form submission.
// -------------------------------------------------------------------------
$editkey  = optional_param('editkey', '', PARAM_ALPHANUMEXT);
$isediting = ($action === 'edit' && !empty($editkey)) || ($action === 'add');

$formdata  = null;
$presets   = videotrack_get_all_presets();

if ($isediting && $_SERVER['REQUEST_METHOD'] === 'POST' && confirm_sesskey()) {
    $name      = required_param('preset_name', PARAM_TEXT);
    // In modalità edit la chiave è immutabile: si usa $editkey dal GET,
    // non il valore POST (readonly in HTML ma aggirabile lato client).
    $key       = ($action === 'edit') ? $editkey : required_param('preset_key', PARAM_ALPHANUMEXT);
    $labels    = optional_param_array('rlabel', [], PARAM_TEXT);
    $descs     = optional_param_array('rdesc', [], PARAM_TEXT);
    $icontypes = optional_param_array('ricontype', [], PARAM_ALPHA);
    $iconvals  = optional_param_array('riconval', [], PARAM_TEXT);
    $requireds = optional_param_array('rrequired', [], PARAM_INT);

    $reactions = [];
    foreach ($labels as $i => $label) {
        $label = trim($label);
        if ($label === '') {
            continue;
        }
        $reactions[] = [
            'label'               => $label,
            'description'         => trim($descs[$i] ?? ''),
            'icontype'            => in_array($icontypes[$i] ?? 'emoji', ['emoji', 'fa']) ? $icontypes[$i] : 'emoji',
            'iconvalue'           => trim($iconvals[$i] ?? ''),
            'requiredforcompletion' => empty($requireds[$i]) ? 0 : 1,
        ];
    }

    // Replace existing preset with same key or add new one.
    $found = false;
    foreach ($presets as &$p) {
        if (($p['key'] ?? '') === $key) {
            $p['name']      = $name;
            $p['reactions'] = $reactions;
            $found = true;
            break;
        }
    }
    unset($p);
    if (!$found) {
        $presets[] = ['key' => $key, 'name' => $name, 'reactions' => $reactions];
    }
    videotrack_save_presets($presets);
    redirect($PAGE->url, get_string('presets:saved', 'mod_videotrack'), null,
        \core\output\notification::NOTIFY_SUCCESS);
}

// Load preset being edited.
$editpreset = null;
if ($action === 'edit' && !empty($editkey)) {
    foreach ($presets as $p) {
        if (($p['key'] ?? '') === $editkey) {
            $editpreset = $p;
            break;
        }
    }
    if (!$editpreset) {
        redirect($PAGE->url, get_string('presets:notfound', 'mod_videotrack'), null,
            \core\output\notification::NOTIFY_WARNING);
    }
}

// -------------------------------------------------------------------------
// Output.
// -------------------------------------------------------------------------
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('presets:pagetitle', 'mod_videotrack'));
echo html_writer::tag('p', get_string('presets:intro', 'mod_videotrack'));

// Back link when editing.
if ($isediting) {
    echo html_writer::tag('p',
        html_writer::link($PAGE->url, '← ' . get_string('presets:backtolist', 'mod_videotrack'))
    );
}

// -------------------------------------------------------------------------
// List view.
// -------------------------------------------------------------------------
if (!$isediting) {
    if (!empty($presets)) {
        $table           = new html_table();
        $table->head     = [
            get_string('presets:col_name', 'mod_videotrack'),
            get_string('presets:col_key', 'mod_videotrack'),
            get_string('presets:col_reactions', 'mod_videotrack'),
            get_string('presets:col_actions', 'mod_videotrack'),
        ];
        $table->attributes['class'] = 'generaltable';

        foreach ($presets as $p) {
            $editurl = new moodle_url($PAGE->url, ['action' => 'edit', 'editkey' => $p['key']]);
            $deleteform = html_writer::start_tag('form', [
                'method' => 'post',
                'action' => $PAGE->url->out(false),
                'class' => 'd-inline videotrack-delete-preset-form',
            ]);
            $deleteform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
            $deleteform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'deleteaction', 'value' => 'delete']);
            $deleteform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'presetkey', 'value' => s($p['key'])]);
            $deleteform .= html_writer::tag('button', get_string('delete'), [
                'type' => 'submit',
                'class' => 'btn btn-link p-0 align-baseline text-danger',
                'data-confirm' => get_string('presets:confirmdelete', 'mod_videotrack'),
                'aria-label' => get_string('presets:deletearia', 'mod_videotrack', $p['name']),
            ]);
            $deleteform .= html_writer::end_tag('form');
            $actions = html_writer::link($editurl, get_string('edit')) . ' | ' . $deleteform;


            $reactionnames = implode(', ', array_column($p['reactions'] ?? [], 'label'));
            $table->data[] = [
                s($p['name']),
                s($p['key']),
                s($reactionnames ?: '—'),
                $actions,
            ];
        }
        echo html_writer::table($table);
    } else {
        echo html_writer::tag('p', get_string('presets:noneyet', 'mod_videotrack'),
            ['class' => 'alert alert-info']);
    }

    $addurl = new moodle_url($PAGE->url, ['action' => 'add']);
    echo html_writer::tag('p',
        $OUTPUT->single_button($addurl, get_string('presets:addpreset', 'mod_videotrack'), 'get')
    );
}

// -------------------------------------------------------------------------
// Add / Edit form.
// -------------------------------------------------------------------------
if ($isediting) {
    $formaction = new moodle_url($PAGE->url,
        $editpreset ? ['action' => 'edit', 'editkey' => $editpreset['key']] : ['action' => 'add']);

    $defaultreactions = $editpreset['reactions'] ?? [
        ['label' => '', 'description' => '', 'icontype' => 'emoji', 'iconvalue' => '', 'requiredforcompletion' => 0],
        ['label' => '', 'description' => '', 'icontype' => 'emoji', 'iconvalue' => '', 'requiredforcompletion' => 0],
        ['label' => '', 'description' => '', 'icontype' => 'emoji', 'iconvalue' => '', 'requiredforcompletion' => 0],
        ['label' => '', 'description' => '', 'icontype' => 'emoji', 'iconvalue' => '', 'requiredforcompletion' => 0],
    ];

    echo html_writer::start_tag('form', [
        'method' => 'post',
        'action' => $formaction->out(false),
    ]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

    echo html_writer::start_tag('fieldset');
    echo html_writer::tag('legend', get_string('presets:presetdetails', 'mod_videotrack'));

    // Preset name.
    echo html_writer::start_div('form-group row');
    echo html_writer::tag('label', get_string('presets:name', 'mod_videotrack'),
        ['for' => 'preset_name', 'class' => 'col-sm-3 col-form-label']);
    echo html_writer::start_div('col-sm-9');
    echo html_writer::empty_tag('input', [
        'type'  => 'text',
        'name'  => 'preset_name',
        'id'    => 'preset_name',
        'class' => 'form-control',
        'value' => s($editpreset['name'] ?? ''),
        'required' => 'required',
    ]);
    echo html_writer::end_div();
    echo html_writer::end_div();

    // Preset key.
    $keyreadonly = $editpreset ? ['readonly' => 'readonly'] : [];
    echo html_writer::start_div('form-group row');
    echo html_writer::tag('label', get_string('presets:key', 'mod_videotrack'),
        ['for' => 'preset_key', 'class' => 'col-sm-3 col-form-label']);
    echo html_writer::start_div('col-sm-9');
    echo html_writer::empty_tag('input', array_merge([
        'type'  => 'text',
        'name'  => 'preset_key',
        'id'    => 'preset_key',
        'class' => 'form-control',
        'value' => s($editpreset['key'] ?? ''),
        'pattern' => '[a-zA-Z0-9_]+',
        'required' => 'required',
    ], $keyreadonly));
    echo html_writer::tag('small', get_string('presets:key_help', 'mod_videotrack'),
        ['class' => 'form-text text-muted']);
    echo html_writer::end_div();
    echo html_writer::end_div();

    echo html_writer::end_tag('fieldset');

    // Reactions table.
    echo html_writer::tag('h4', get_string('presets:reactions', 'mod_videotrack'));
    echo html_writer::start_tag('table', ['class' => 'generaltable w-100']);
    echo html_writer::tag('caption', get_string('presets:reactionstablecaption', 'mod_videotrack'), ['class' => 'sr-only visually-hidden']);
    echo html_writer::start_tag('thead');
    echo html_writer::start_tag('tr');
    foreach ([
        get_string('reactionlabel', 'mod_videotrack'),
        get_string('reactiondescription', 'mod_videotrack'),
        get_string('reactionicontype', 'mod_videotrack'),
        get_string('reactioniconvalue', 'mod_videotrack'),
        get_string('reactionrequired', 'mod_videotrack'),
    ] as $th) {
        echo html_writer::tag('th', $th, ['scope' => 'col']);
    }
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');
    echo html_writer::start_tag('tbody');

    foreach ($defaultreactions as $i => $r) {
        $rownum = $i + 1;
        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', html_writer::empty_tag('input', [
            'type' => 'text', 'name' => 'rlabel[' . $i . ']',
            'class' => 'form-control form-control-sm', 'value' => s($r['label']),
            'aria-label' => get_string('presets:reactionlabelaria', 'mod_videotrack', $rownum),
        ]));
        echo html_writer::tag('td', html_writer::empty_tag('input', [
            'type' => 'text', 'name' => 'rdesc[' . $i . ']',
            'class' => 'form-control form-control-sm', 'value' => s($r['description'] ?? ''),
            'aria-label' => get_string('presets:reactiondescriptionaria', 'mod_videotrack', $rownum),
        ]));
        // Icon type select.
        $selecthtml = html_writer::start_tag('select', [
            'name' => 'ricontype[' . $i . ']',
            'class' => 'form-control form-control-sm',
            'aria-label' => get_string('presets:reactionicontypearia', 'mod_videotrack', $rownum),
        ]);
        foreach (['emoji' => get_string('icontype:emoji', 'mod_videotrack'),
                  'fa'    => get_string('icontype:fa', 'mod_videotrack')] as $val => $label) {
            $attrs = ['value' => $val];
            if (($r['icontype'] ?? 'emoji') === $val) {
                $attrs['selected'] = 'selected';
            }
            $selecthtml .= html_writer::tag('option', $label, $attrs);
        }
        $selecthtml .= html_writer::end_tag('select');
        echo html_writer::tag('td', $selecthtml);
        echo html_writer::tag('td', html_writer::empty_tag('input', [
            'type' => 'text', 'name' => 'riconval[' . $i . ']',
            'class' => 'form-control form-control-sm', 'value' => s($r['iconvalue'] ?? ''),
            'aria-label' => get_string('presets:reactioniconvaluearia', 'mod_videotrack', $rownum),
        ]));
        $checkedattr = !empty($r['requiredforcompletion']) ? ['checked' => 'checked'] : [];
        echo html_writer::tag('td',
            html_writer::empty_tag('input', array_merge(
                [
                    'type' => 'checkbox',
                    'name' => 'rrequired[' . $i . ']',
                    'value' => '1',
                    'aria-label' => get_string('presets:reactionrequiredaria', 'mod_videotrack', $rownum),
                ],
                $checkedattr
            ))
        );
        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');

    echo html_writer::tag('p', get_string('presets:reactions_help', 'mod_videotrack'),
        ['class' => 'text-muted small']);

    echo html_writer::start_div('mt-3');
    echo html_writer::empty_tag('input', [
        'type'  => 'submit',
        'class' => 'btn btn-primary',
        'value' => get_string('savechanges'),
    ]);
    echo ' ';
    echo html_writer::link($PAGE->url, get_string('cancel'), ['class' => 'btn btn-secondary']);
    echo html_writer::end_div();

    echo html_writer::end_tag('form');
}

$PAGE->requires->js_call_amd('mod_videotrack/presets', 'init', [[
    'confirmdelete' => get_string('presets:confirmdelete', 'mod_videotrack'),
    'confirmtitle' => get_string('confirm', 'moodle'),
    'deletelabel' => get_string('delete', 'moodle'),
    'cancellabel' => get_string('cancel', 'moodle'),
]]);

echo $OUTPUT->footer();

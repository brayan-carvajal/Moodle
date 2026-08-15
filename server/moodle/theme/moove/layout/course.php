<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/behat/lib.php');
require_once($CFG->dirroot . '/course/lib.php');

// Add block button in editing mode.
$addblockbutton = $OUTPUT->addblockbutton();

if (isloggedin()) {
    $courseindexopen = (get_user_preferences('drawer-open-index', true) == true);
    $blockdraweropen = (get_user_preferences('drawer-open-block') == true);
} else {
    $courseindexopen = false;
    $blockdraweropen = false;
}

if (defined('BEHAT_SITE_RUNNING') && get_user_preferences('behat_keep_drawer_closed') != 1) {
    $blockdraweropen = true;
}

$extraclasses = ['uses-drawers'];
if ($courseindexopen) {
    $extraclasses[] = 'drawer-open-index';
}

$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = (strpos($blockshtml, 'data-block=') !== false || !empty($addblockbutton));
if (!$hasblocks) {
    $blockdraweropen = false;
}

$contentblockshtml = '';
try {
    $contentblockshtml = $OUTPUT->blocks('content');
} catch (\Throwable $e) {
    $contentblockshtml = '';
}
$hascontentblocks = (strpos($contentblockshtml, 'data-block=') !== false);

$addblockbuttoncontent = '';
if ($PAGE->user_is_editing()) {
    $addblockbuttoncontent = $OUTPUT->addblockbutton('content');
}

$themesettings = new \theme_moove\util\settings();
if (!$themesettings->enablecourseindex) {
    $courseindex = '';
} else {
    $courseindex = core_course_drawer();
}

if (!$courseindex) {
    $courseindexopen = false;
}

$forceblockdraweropen = $OUTPUT->firstview_fakeblocks();

$secondarynavigation = false;
$overflow = '';
if ($PAGE->has_secondary_navigation() && $PAGE->user_is_editing()) {
    $secondary = $PAGE->secondarynav;

    if ($secondary->get_children_key_list()) {
        $tablistnav = $PAGE->has_tablist_secondary_navigation();
        $moremenu = new \core\navigation\output\more_menu($PAGE->secondarynav, 'nav-tabs', true, $tablistnav);
        $secondarynavigation = $moremenu->export_for_template($OUTPUT);
        $extraclasses[] = 'has-secondarynavigation';
    }

    $overflowdata = $PAGE->secondarynav->get_overflow_menu_data();
    if (!is_null($overflowdata)) {
        $overflow = $overflowdata->export_for_template($OUTPUT);
    }
}

// Add custom navigation items to primary navbar
$customitems = [
    [
        'text' => 'Estudiantes',
        'url' => '#',
        'children' => [
            [
                'text' => 'Reglamento estudiantil',
                'url' => 'https://uninavarra.edu.co/wp-content/uploads/2024/01/REGLAMENTO-ACADEMICO-Y-ESTUDIANTIL-con-modificaciones.pdf',
            ],
            [
                'text' => 'Mapa de uso',
                'url' => 'https://uninavarra.edu.co/wp-content/uploads/2024/01/Mapa-de-Uso-etR-2024.pdf',
            ],
            [
                'text' => 'Soporte',
                'url' => 'https://forms.gle/UKv5MRvc7djzPW8L7',
            ],
        ],
    ],
    [
        'text' => 'Biblioteca',
        'url' => 'https://uninavarra.edu.co/estudiantes/biblioteca/',
    ],
];

// Only show Kopere Dashboard if user is logged in
if ($USER->id != 0) {
    $customitems[] = [
        'text' => 'Kopere Dashboard',
        'url' => 'https://uninavarra.edu-labs.co/local/kopere_dashboard/view.php?classname=dashboard&method=start',
    ];
}

foreach ($customitems as $item) {
    $key = strtolower(str_replace(' ', '', $item['text']));
    $node = $PAGE->primarynav->add($item['text'], new \moodle_url($item['url']), \navigation_node::TYPE_CUSTOM, null, $key);
    
    if (isset($item['children']) && is_array($item['children'])) {
        foreach ($item['children'] as $child) {
            $childkey = strtolower(str_replace(' ', '', $child['text']));
            $node->add($child['text'], new \moodle_url($child['url']), \navigation_node::TYPE_CUSTOM, null, $childkey);
        }
    }
}

$primary = new core\navigation\output\primary($PAGE);
$renderer = $PAGE->get_renderer('core');
$primarymenu = $primary->export_for_template($renderer);

if (!empty($primarymenu['mobileprimarynav'])) {
    $primarymenu['mobileprimarynav'] = array_values(array_filter($primarymenu['mobileprimarynav'], function($item) {
        $url = $item['url'] ?? '';
        $text = $item['text'] ?? '';
        return $url !== '/my/' && stripos($text, 'Área personal') === false && stripos($text, 'myhome') === false && strpos($text, 'Página Principal') === false;
    }));
}

$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions() && !$PAGE->has_secondary_navigation();
$regionmainsettingsmenu = $buildregionmainsettings ? $OUTPUT->region_main_settings_menu() : false;

$header = $PAGE->activityheader;
$headercontent = $header->export_for_template($renderer);

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => \core\context\course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'contentblocks' => $contentblockshtml,
    'hascontentblocks' => $hascontentblocks,
    'contentblocks' => $contentblockshtml,
    'addblockbuttoncontent' => $addblockbuttoncontent,
    'bodyattributes' => $bodyattributes,
    'courseindexopen' => $courseindexopen,
    'blockdraweropen' => $blockdraweropen,
    'courseindex' => $courseindex,
    'primarymoremenu' => $primarymenu['moremenu'],
    'secondarymoremenu' => $secondarynavigation ?: false,
    'mobileprimarynav' => $primarymenu['mobileprimarynav'],
    'usermenu' => $primarymenu['user'],
    'langmenu' => $primarymenu['lang'],
    'forceblockdraweropen' => $forceblockdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'overflow' => $overflow,
    'headercontent' => $headercontent,
    'addblockbutton' => $addblockbutton,
];

$themesettings = new \theme_moove\util\settings();
$loggedin = isloggedin() && !isguestuser();
$templatecontext['loggedin'] = $loggedin;
$templatecontext = array_merge($templatecontext, $themesettings->footer());

echo $OUTPUT->render_from_template('theme_moove/course_page', $templatecontext);

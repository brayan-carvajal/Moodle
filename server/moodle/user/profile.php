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

/**
 * Public Profile -- a user's public profile page
 *
 * - each user can currently have their own page (cloned from system and then customised)
 * - users can add any blocks they want
 * - the administrators can define a default site public profile for users who have
 *   not created their own public profile
 *
 * This script implements the user's view of the public profile, and allows editing
 * of the public profile.
 *
 * @package    core_user
 * @copyright  2010 Remote-Learner.net
 * @author     Hubert Chathi <hubert@remote-learner.net>
 * @author     Olav Jordan <olav.jordan@remote-learner.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->libdir.'/filelib.php');

$userid         = optional_param('id', 0, PARAM_INT);
$edit           = optional_param('edit', null, PARAM_BOOL);    // Turn editing on and off.
$reset          = optional_param('reset', null, PARAM_BOOL);

// Even if the user didn't supply a userid, we treat page URL as if they did; this is needed so navigation works correctly.
$userid = $userid ?: $USER->id;
$PAGE->set_url('/user/profile.php', ['id' => $userid]);

if (!empty($CFG->forceloginforprofiles)) {
    require_login();
    if (isguestuser()) {
        $PAGE->set_context(context_system::instance());
        $PAGE->set_title(get_string('loginrequired'));
echo $OUTPUT->header();
        echo $OUTPUT->confirm(
            get_string('guestcantaccessprofiles', 'error'),
            get_login_url(),
            $CFG->wwwroot,
            [
                'headinglevel' => 1,
                'confirmtitle' => get_string('loginrequired'),
            ],
        );
        echo $OUTPUT->footer();
        die;
    }
} else if (!empty($CFG->forcelogin)) {
    require_login();
}

if ((!$user = $DB->get_record('user', array('id' => $userid))) || ($user->deleted)) {
    $PAGE->set_context(context_system::instance());
    $PAGE->set_title(get_string('user'));
    echo $OUTPUT->header();
    if (!$user) {
        echo $OUTPUT->notification(get_string('invaliduser', 'error'));
    } else {
        echo $OUTPUT->notification(get_string('userdeleted'));
    }
    echo $OUTPUT->footer();
    die;
}

$currentuser = ($user->id == $USER->id);
$context = $usercontext = context_user::instance($userid, MUST_EXIST);

if (!user_can_view_profile($user, null, $context)) {

    // Course managers can be browsed at site level. If not forceloginforprofiles, allow access (bug #4366).
    $struser = get_string('user');
$PAGE->set_context(context_system::instance());
$PAGE->set_title($struser);  // Do not leak the name.
$PAGE->set_heading($struser);
$PAGE->set_pagelayout('mypublic');
$PAGE->add_body_class('limitedwidth');
$PAGE->set_url('/user/profile.php', array('id' => $userid));
    $PAGE->navbar->add($struser);
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('usernotavailable', 'error'));
    echo $OUTPUT->footer();
    exit;
}

// Get the profile page.  Should always return something unless the database is broken.
if (!$currentpage = my_get_page($userid, MY_PAGE_PUBLIC)) {
    throw new \moodle_exception('mymoodlesetup');
}

$PAGE->set_context($context);
$PAGE->set_pagelayout('mypublic');
$PAGE->add_body_class('limitedwidth');
$PAGE->add_body_class('hideprofilepageheader');
$PAGE->set_pagetype('user-profile');

// Set up block editing capabilities.
if (isguestuser()) {     // Guests can never edit their profile.
    $USER->editing = $edit = 0;  // Just in case.
    $PAGE->set_blocks_editing_capability('moodle/my:configsyspages');  // unlikely :).
} else {
    if ($currentuser) {
        $PAGE->set_blocks_editing_capability('moodle/user:manageownblocks');
    } else {
        $PAGE->set_blocks_editing_capability('moodle/user:manageblocks');
    }
}

// Start setting up the page.
$strpublicprofile = get_string('publicprofile');

$PAGE->blocks->add_region('content');
$PAGE->set_subpage($currentpage->id);
$PAGE->set_title(fullname($user).": $strpublicprofile");
$PAGE->set_heading(fullname($user));

if (!$currentuser) {
    $PAGE->navigation->extend_for_user($user);
    if ($node = $PAGE->settingsnav->get('userviewingsettings'.$user->id)) {
        $node->forceopen = true;
    }
} else if ($node = $PAGE->settingsnav->get('dashboard', navigation_node::TYPE_CONTAINER)) {
    $node->forceopen = true;
}
if ($node = $PAGE->settingsnav->get('root')) {
    $node->forceopen = false;
}


// Toggle the editing state and switches.
if ($PAGE->user_allowed_editing()) {
    if ($reset !== null) {
        if (!is_null($userid)) {
            if (!$currentpage = my_reset_page($userid, MY_PAGE_PUBLIC, 'user-profile')) {
                throw new \moodle_exception('reseterror', 'my');
            }
            redirect(new moodle_url('/user/profile.php', array('id' => $userid)));
        }
    } else if ($edit !== null) {             // Editing state was specified.
        $USER->editing = $edit;       // Change editing state.
    } else {                          // Editing state is in session.
        if ($currentpage->userid) {   // It's a page we can edit, so load from session.
            if (!empty($USER->editing)) {
                $edit = 1;
            } else {
                $edit = 0;
            }
        } else {
            // For the page to display properly with the user context header the page blocks need to
            // be copied over to the user context.
            if (!$currentpage = my_copy_page($userid, MY_PAGE_PUBLIC, 'user-profile')) {
                throw new \moodle_exception('mymoodlesetup');
            }
            $PAGE->set_context($usercontext);
            $PAGE->set_subpage($currentpage->id);
            // It's a system page and they are not allowed to edit system pages.
            $USER->editing = $edit = 0;          // Disable editing completely, just to be safe.
        }
    }

    // Add button for editing page.
    $params = array('edit' => !$edit, 'id' => $userid);

    $resetbutton = '';
    $resetstring = get_string('resetpage', 'my');
    $reseturl = new moodle_url("$CFG->wwwroot/user/profile.php", array('edit' => 1, 'reset' => 1, 'id' => $userid));

    if (!$currentpage->userid) {
        // Viewing a system page -- let the user customise it.
        $editstring = get_string('updatemymoodleon');
        $params['edit'] = 1;
    } else if (empty($edit)) {
        $editstring = get_string('updatemymoodleon');
        // $resetbutton = $OUTPUT->single_button($reseturl, $resetstring);
    } else {
        $editstring = get_string('updatemymoodleoff');
        // $resetbutton = $OUTPUT->single_button($reseturl, $resetstring);
    }

    $url = new moodle_url("$CFG->wwwroot/user/profile.php", $params);
    $button = '';
    if (!$PAGE->theme->haseditswitch) {
        $button = $OUTPUT->single_button($url, $editstring);
    }
    $PAGE->set_button($resetbutton . $button);

} else {
    $USER->editing = $edit = 0;
}

// Trigger a user profile viewed event.
profile_view($user, $usercontext);

echo '<style>
body.hideprofilepageheader header#page-header > div.w-100 {display:none !important;}
body.hideprofilepageheader header#page-header {min-height:0; padding:0; margin:0;}
body.hideprofilepageheader #topofscroll.main-inner,
body.hideprofilepageheader #page-content,
body.hideprofilepageheader #region-main-box {
    padding:0;
    margin:0;
}
.profile-columns-wrapper {
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:16px;
    align-items:start;
    width:100%;
}
.profile-column {
    width:100%;
    min-width:0;
}
.profile-column > .card,
.profile-column > .profile_tree,
.profile-column > .profile_tree .node_category.card {
    width:100%;
    max-width:100%;
}
.profile-column > .profile_tree {
    display:flex;
    flex-direction:column;
    gap:16px;
}
.profile-column > .profile_tree .node_category.card {
    display:block;
    margin:0 !important;
    width:100%;
    max-width:100%;
}
.profile-column .card,
.profile-column .node_category.card {
    border:1px solid #e5e7eb;
    border-radius:0.5rem;
    box-shadow:none;
    width:100%;
    background:#fff;
    margin:0 !important;
}
.profile-column .card-body,
.profile-column .node_category.card .card-body {
    padding:24px;
    box-sizing:border-box;
}
.profile-user-card .userpicture img {
    width:110px;
    height:110px;
    object-fit:cover;
    border-radius:50%;
    border:1px solid #f2f3f7;
    margin-bottom:0.75rem;
}
.profile-user-card h3 {
    margin-bottom:0.75rem;
    font-size:1.15rem;
}
.profile-user-card .btn {
    display:block;
    width:100%;
    max-width:220px;
    margin:0.75rem auto 0;
}
@media (max-width: 767px) {
    .profile-columns-wrapper {
        grid-template-columns:1fr;
    }
    .profile-column > .profile_tree {
        gap:16px;
    }
}
</style>';

// TODO WORK OUT WHERE THE NAV BAR IS!
echo $OUTPUT->header();

$hiddenfields = [];
if (!has_capability('moodle/user:viewhiddendetails', $usercontext)) {
    $hiddenfields = array_flip(explode(',', $CFG->hiddenuserfields));
}
if ($user->description && !isset($hiddenfields['description'])) {
    echo '<div class="description">';
    if (!empty($CFG->profilesforenrolledusersonly) && !$currentuser &&
        !$DB->record_exists('role_assignments', array('userid' => $user->id))) {
        echo get_string('profilenotshown', 'moodle');
    } else {
        $user->description = file_rewrite_pluginfile_urls($user->description, 'pluginfile.php', $usercontext->id, 'user',
                                                          'profile', null);
        echo format_text($user->description, $user->descriptionformat);
    }
    echo '</div>';
}

// Render custom blocks.
$renderer = $PAGE->get_renderer('core_user', 'myprofile');
$tree = core_user\output\myprofile\manager::build_tree($user, $currentuser);
$profiletreehtml = $renderer->render($tree);

$middlegroups = [];
$rightgroups = [];
if (preg_match_all('/<section class="node_category[^>]*>.*?<\/section>/s', $profiletreehtml, $matches)) {
    foreach ($matches[0] as $sectionhtml) {
        if (preg_match('/<h3 class="lead">(.*?)<\/h3>/s', $sectionhtml, $titlematch)) {
            $title = trim($titlematch[1]);
            if (in_array($title, ['Detalles de usuario', 'Detalles del curso'])) {
                $middlegroups[] = $sectionhtml;
            } else {
                $rightgroups[] = $sectionhtml;
            }
        }
    }
}

echo '<div id="topofscroll" class="main-inner">';
echo '  <div id="page-content" class="d-print-block">';
echo '    <div id="region-main-box">';
echo '      <div class="profile-columns-wrapper">';

// Columna 1: foto y nombre del usuario.
echo '        <div class="profile-column">';
echo '          <div class="card card-profile profile-user-card">';
echo '            <div class="card-body">';
echo '              <div class="userpicture text-center mb-2">';
echo '                ' . $OUTPUT->user_picture($user, ['size' => 100, 'class' => 'userpicture']);
echo '              </div>';
echo '              <h3 class="text-center fw-bold mb-2">' . fullname($user) . '</h3>';
echo '              <div class="headerbuttons text-center mb-2"></div>';
echo '              <a href="' . (new moodle_url('/user/editadvanced.php', ['id' => $user->id, 'course' => !empty($course) ? $course->id : 1, 'returnto' => 'profile']))->out() . '" class="btn btn-sm btn-primary btn-block my-2"><i class="fa fa-edit"></i> Editar perfil</a>';
echo '              <div class="userinfo my-2"></div>';
echo '            </div>';
echo '          </div>';
echo '        </div>';

// Columna 2: detalles de usuario y del curso.
echo '        <div class="profile-column">';
echo '          <div class="profile_tree">';
foreach ($middlegroups as $grouphtml) {
    echo $grouphtml;
}
echo '          </div>';
echo '        </div>';

// Columna 3: misc, informes, accesos y app móvil.
echo '        <div class="profile-column">';
echo '          <div class="profile_tree">';
foreach ($rightgroups as $grouphtml) {
    echo $grouphtml;
}
echo '          </div>';
echo '        </div>';

echo '      </div>';
echo '    </div>';
echo '  </div>';
echo '</div>';

echo $OUTPUT->footer();

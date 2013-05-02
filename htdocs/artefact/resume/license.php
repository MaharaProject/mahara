<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', true);
define('MENUITEM', 'content/resume');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'resume');
define('SECTION_PAGE', 'index');
define('RESUME_SUBPAGE', 'licenses');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('resume', 'artefact.resume'));
require_once('pieforms/pieform.php');
require_once('license.php');
safe_require('artefact', 'resume');

if (!get_config('licensemetadata')) {
    redirect('/artefact/resume');
}

$personalinformation = null;
try {
    $personalinformation = artefact_instance_from_type('personalinformation');
}
catch (Exception $e) { }

$form = array(
    'name' => 'resumelicense',
    'plugintype' => 'artefact',
    'pluginname' => 'resume',
    'elements' => array(
        'license' => license_form_el_basic($personalinformation),
        'license_advanced' => license_form_el_advanced($personalinformation),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('save')
        ),
    ),
);
$form = pieform($form);

function resumelicense_submit(Pieform $form, $values) {
    global $personalinformation, $USER;
    $userid = $USER->get('id');

    if (empty($personalinformation)) {
        $personalinformation = new ArtefactTypePersonalinformation(0, array(
            'owner' => $userid,
            'title' => get_string('personalinformation', 'artefact.resume'),
        ));
    }
    if (get_config('licensemetadata')) {
        $personalinformation->set('license', $values['license']);
        $personalinformation->set('licensor', $values['licensor']);
        $personalinformation->set('licensorurl', $values['licensorurl']);
    }
    $personalinformation->commit();

    $result = array(
        'error'   => false,
        'message' => get_string('resumesaved', 'artefact.resume'),
        'goto'    => get_config('wwwroot') . 'artefact/resume/license.php',
    );
    if ($form->submitted_by_js()) {
        $SESSION->add_ok_msg($result['message']);
        $form->json_reply(PIEFORM_OK, $result, false);
    }
    $form->reply(PIEFORM_OK, $result);
}

$smarty = smarty(array('artefact/resume/js/simpleresumefield.js'));
$smarty->assign('licensesform', $form);
$smarty->assign('INLINEJAVASCRIPT', '$j(simple_resumefield_init);');
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('SUBPAGENAV', PluginArtefactResume::submenu_items());
$smarty->display('artefact:resume:licenses.tpl');

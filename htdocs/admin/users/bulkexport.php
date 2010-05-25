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
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');

define('TITLE', get_string('bulkexporttitle', 'admin'));

function bulkexport_submit(Pieform $form, $values) {
    global $SESSION;

    $usernames = array();

    // Read in the usernames explicitly specified
    foreach (split("\n", $values['usernames']) as $username) {
        $username = trim($username);
        if (!empty($username)) {
            $usernames[] = $username;
        }
    }

    if (empty($usernames) and !empty($values['authinstance'])) {
        // Export all users from the selected institution
        $rs = get_recordset_select('usr', 'authinstance = ? AND deleted = 0', array($values['authinstance']), '', 'username');
        while ($record = $rs->FetchRow()) {
            $usernames[] = $record['username'];
        }
    }

    $SESSION->set('exportdata', $usernames);

    $smarty = smarty();
    $smarty->assign('heading', '');
    $smarty->display('admin/users/bulkdownload.tpl');
    exit;
}

$authinstanceelement = array('type' => 'hidden', 'value' => '');

$authinstances = auth_get_auth_instances();
if (count($authinstances) > 0) {
    $options = array();

    foreach ($authinstances as $authinstance) {
        $options[$authinstance->id] = $authinstance->displayname. ': '.$authinstance->instancename;
    }
    $default = key($options);

    $authinstanceelement = array(
        'type' => 'select',
        'title' => get_string('institution'),
        'description' => get_string('bulkexportinstitution', 'admin'),
        'options' => $options,
        'defaultvalue' => $default
    );
}

$form = array(
    'name' => 'bulkexport',
    'elements' => array(
        'authinstance' => $authinstanceelement,
        'usernames' => array(
            'type' => 'textarea',
            'rows' => 25,
            'cols' => 50,
            'title' => get_string('bulkexportusernames', 'admin'),
            'description' => get_string('bulkexportusernamesdescription', 'admin'),
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('bulkexport', 'admin')
        )
    )
);

$form = pieform($form);

$smarty = smarty();
$smarty->assign('bulkexportform', $form);
$smarty->assign('bulkexportdescription', get_string('bulkexportdescription', 'admin'));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('admin/users/bulkexport.tpl');

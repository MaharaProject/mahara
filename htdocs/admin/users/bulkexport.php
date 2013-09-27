<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
    foreach (explode("\n", $values['usernames']) as $username) {
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

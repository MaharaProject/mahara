<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage core
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

// @todo Maybe later have a cron job to clean up access to views when the access expires
// @todo Currently you can add access with start date after end date, this should be restricted
// @todo Currently you can add multpile access that is exactly the same (e.g. 3x public with no dates)
//       This might need to be checked for. As it stands that just results in three rows in the database,
//       which are collapsed when access to the view is edited
define('INTERNAL', 1);
define('MENUITEM', 'view');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'editaccess');

require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('editaccess', 'view'));
require_once('pieforms/pieform.php');
require_once('pieforms/pieform/elements/calendar.php');
$smarty = smarty(array('tablerenderer'), pieform_element_calendar_get_headdata(pieform_element_calendar_configure(array())));

$viewid = param_integer('viewid');
$prefix = get_config('dbprefix');

if (!get_field('view', 'COUNT(*)', 'id', $viewid, 'owner', $USER->get('id'))) {
    $SESSION->add_error_msg(get_string('canteditdontown', 'view'));
    redirect('/view/');
}
$data = get_records_sql_array('SELECT va.accesstype AS type, va.startdate, va.stopdate
    FROM ' . $prefix . 'view_access va
    LEFT JOIN ' . $prefix . 'view v ON (va.view = v.id)
    WHERE v.id = ?
    AND v.owner = ?
    ORDER BY va.accesstype', array($viewid, $USER->get('id')));
if (!$data) {
    $data = array();
}
foreach ($data as &$item) {
    $item = (array)$item;
}

// Get access for users, groups and communities
$extradata = get_records_sql_array("
    SELECT 'user' AS type, usr AS id, 0 AS tutoronly, startdate, stopdate
        FROM {$prefix}view_access_usr
        WHERE view = ?
UNION
    SELECT 'group', grp, 0, startdate, stopdate
        FROM {$prefix}view_access_group
        WHERE view = ?
UNION
    SELECT 'community', community, tutoronly, startdate, stopdate
        FROM {$prefix}view_access_community
        WHERE view = ?", array($viewid, $viewid, $viewid));
if ($extradata) {
    foreach ($extradata as &$extraitem) {
        $extraitem = (array)$extraitem;
        $extraitem['tutoronly'] = (int)$extraitem['tutoronly'];
    }
    $data = array_merge($data, $extradata);
}


$form = array(
    'name' => 'editviewaccess',
    'elements' => array(
        'accesslist' => array(
            'type'         => 'viewacl',
            'defaultvalue' => $data
        ),
        'viewid' => array(
            'type' => 'hidden',
            'value' => $viewid
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('saveaccess'), get_string('cancel'))
        )
    )
);

function editviewaccess_cancel_submit() {
    redirect('/view/');
}

function editviewaccess_submit(Pieform $form, $values) {
    global $SESSION, $USER, $viewid, $data;

    // For users who are being removed from having access to this view, they
    // need to have the view and any attached artefacts removed from their
    // watchlist.
    $oldusers = array();
    foreach ($data as $item) {
        if ($item['type'] == 'user') {
            $oldusers[] = $item;
        }
    }

    $newusers = array();
    if ($values['accesslist']) {
        foreach ($values['accesslist'] as $item) {
            if ($item['type'] == 'user') {
                $newusers[] = $item;
            }
        }
    }

    $userstodelete = array();
    foreach ($oldusers as $olduser) {
        foreach ($newusers as $newuser) {
            if ($olduser['id'] == $newuser['id']) {
                continue(2);
            }
        }
        $userstodelete[] = $olduser;
    }

    if ($userstodelete) {
        $userids = array();
        foreach ($userstodelete as $user) {
            $userids[] = intval($user['id']);
        }
        $userids = implode(',', $userids);

        $prefix = get_config('dbprefix');
        execute_sql('DELETE FROM ' . $prefix . 'usr_watchlist_view
            WHERE view = ' . $viewid . '
            AND usr IN (' . $userids . ')');
        execute_sql('DELETE FROM ' . $prefix . 'usr_watchlist_artefact
            WHERE view = ' . $viewid . '
            AND usr IN(' . $userids . ')');
    }

    $beforeusers = activity_get_viewaccess_users($viewid, $USER->get('id'), 'viewaccess');

    // Procedure:
    // get list of current friends - this is available in global $data
    // compare with list of new friends
    // work out which friends are being removed
    // foreach friend
    //     // remove record from usr_watchlist_view where usr = ? and view = ?
    //     // remove records from usr_watchlist_artefact where usr = ? and view = ?
    // endforeach
    //
    db_begin();
    delete_records('view_access', 'view', $viewid);
    delete_records('view_access_usr', 'view', $viewid);
    delete_records('view_access_group', 'view', $viewid);
    delete_records('view_access_community', 'view', $viewid);
    $time = db_format_timestamp(time());

    // View access
    if ($values['accesslist']) {
        foreach ($values['accesslist'] as $item) {
            $accessrecord = new StdClass;
            $accessrecord->view = $viewid;
            $accessrecord->startdate = db_format_timestamp($item['startdate']);
            $accessrecord->stopdate  = db_format_timestamp($item['stopdate']);
            switch ($item['type']) {
                case 'public':
                case 'loggedin':
                case 'friends':
                    $accessrecord->accesstype = $item['type'];
                    insert_record('view_access', $accessrecord);
                    break;
                case 'user':
                    $accessrecord->usr = $item['id'];
                    insert_record('view_access_usr', $accessrecord);
                    break;
                case 'group':
                    $accessrecord->grp = $item['id'];
                    insert_record('view_access_group', $accessrecord);
                    break;
                case 'community':
                    $accessrecord->community = $item['id'];
                    $accessrecord->tutoronly = $item['tutoronly'];
                    insert_record('view_access_community', $accessrecord);
                    break;
            }
        }
    }

    $data = new StdClass;
    $data->view = $viewid;
    $data->owner = $USER->get('id');
    $data->oldusers = $beforeusers;
    activity_occurred('viewaccess', $data);
    handle_event('saveview', $viewid);

    db_commit();
    $SESSION->add_ok_msg(get_string('viewaccesseditedsuccessfully'));
    redirect('/view/');
}

$smarty->assign('titlestr', get_string('editaccess', 'view'));
$smarty->assign('form', pieform($form));
$smarty->display('view/create4.tpl');

?>

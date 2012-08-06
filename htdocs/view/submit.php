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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/views');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('view.php');
require_once('collection.php');
require_once('activity.php');
require_once(get_config('docroot') . 'artefact/lib.php');
$groupid = param_integer('group');
$returnto = param_variable('returnto', 'view');

$group = get_record_sql(
    'SELECT g.id, g.name, g.grouptype, g.urlid
       FROM {group_member} u
       INNER JOIN {group} g ON (u.group = g.id AND g.deleted = 0)
       WHERE u.member = ?
       AND g.id = ?
       AND g.submittableto = 1',
    array($USER->get('id'), $groupid)
);

if (!$group || !group_within_edit_window($group)) {
    throw new AccessDeniedException(get_string('cantsubmittogroup', 'view'));
}

if ($collectionid = param_integer('collection', null)) {
    $collection = new Collection($collectionid);
    if (!$collection || $collection->is_submitted() || ($collection->get('owner') !== $USER->get('id'))) {
        throw new AccessDeniedException(get_string('cantsubmitcollectiontogroup', 'view'));
    }
    $submissionname = $collection->get('name');
}
else {
    $view = new View(param_integer('id'));
    if (!$view || $view->is_submitted() || ($view->get('owner') !== $USER->get('id'))) {
        throw new AccessDeniedException(get_string('cantsubmitviewtogroup', 'view'));
    }
    $submissionname = $view->get('title');
}

define('TITLE', get_string('submitviewtogroup', 'view', $submissionname, $group->name));

$form = pieform(array(
    'name' => 'submitview',
    'renderer' => 'div',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . returnto(),
        )
    ),
));

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('message', get_string('submitconfirm', 'view', $submissionname, $group->name));
$smarty->assign('form', $form);
$smarty->display('view/submit.tpl');

function submitview_submit(Pieform $form, $values) {
    global $SESSION, $USER, $view, $collection, $group;

    if (!empty($collection)) {
        $collection->submit($group, $USER);
        $SESSION->add_ok_msg(get_string('collectionsubmitted', 'view'));
    }
    else if (!empty($view)) {
        $view->submit($group, $USER);
        $SESSION->add_ok_msg(get_string('viewsubmitted', 'view'));
    }

    redirect('/' . returnto());
}

function returnto() {
    global $view, $collection, $group, $returnto;
    // Deteremine the best place to return to
    if ($returnto === 'group') {
        $goto = group_homepage_url($group, false);
    }
    else if ($returnto === 'view') {
        if (!empty($collection)) {
            $goto = $collection->get_url(false);
        }
        else {
            $goto = $view->get_url(false);
        }
    }
    else {
        $goto = 'view/';
    }
    return $goto;
}

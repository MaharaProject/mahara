<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/views');

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'index');

require(dirname(dirname(__FILE__)) . '/init.php');
require('pieforms/pieform.php');
define('TITLE', get_string('myviews', 'view'));

$limit = param_integer('limit', 5);
$offset = param_integer('offset', 0);

$userid = $USER->get('id');

$count = count_records('view', 'owner', $userid);

/* Get $limit views from the view table, then get all these views'
   associated artefacts */

/* Do this in one query sometime */

$viewdata = get_records_sql_array('SELECT v.id,v.title,v.startdate,v.stopdate,v.description, g.id AS group, g.name
        FROM {view} v
        LEFT OUTER JOIN {group} g ON (v.submittedto = g.id AND g.deleted = 0)
        WHERE v.owner = ' . $userid . '
        ORDER BY v.title, v.id', '', $offset, $limit);

if ($viewdata) {
    $viewidlist = implode(', ', array_map(create_function('$a', 'return $a->id;'), $viewdata));
    $artefacts = get_records_sql_array('SELECT va.view, va.artefact, a.title, a.artefacttype, t.plugin
        FROM {view_artefact} va
        INNER JOIN {artefact} a ON va.artefact = a.id
        INNER JOIN {artefact_installed_type} t ON a.artefacttype = t.name
        WHERE va.view IN (' . $viewidlist . ')
        GROUP BY 1, 2, 3, 4, 5
        ORDER BY a.title, va.artefact', '');
    $accessgroups = get_records_sql_array('SELECT view, accesstype, id, name, startdate, stopdate
        FROM (
            SELECT view, \'group\' AS accesstype, g.id, g.name, startdate, stopdate
            FROM view_access_group vg
            INNER JOIN "group" g ON g.id = vg.group AND g.deleted = 0
            WHERE vg.tutoronly = 0
            UNION SELECT view, \'tutorgroup\' AS accesstype, g.id, g.name, startdate, stopdate
            FROM view_access_group vg
            INNER JOIN "group" g ON g.id = vg.group AND g.deleted = 0
            WHERE vg.tutoronly = 1
            UNION SELECT view, \'user\' AS accesstype, usr AS id, \'\' AS name, startdate, stopdate
            FROM view_access_usr vu
            UNION SELECT view, accesstype, 0 AS id, \'\' AS name, startdate, stopdate
            FROM view_access va
        ) AS a
        WHERE view in (' . $viewidlist . ')
        ORDER BY view, accesstype, name, id
    ', array());
}


$data = array();
if ($viewdata) {
    for ($i = 0; $i < count($viewdata); $i++) {
        $index[$viewdata[$i]->id] = $i;
        $data[$i]['id'] = $viewdata[$i]->id;
        $data[$i]['title'] = $viewdata[$i]->title;
        $data[$i]['description'] = $viewdata[$i]->description;
        if ($viewdata[$i]->name) {
            $data[$i]['submittedto'] = array('name' => $viewdata[$i]->name, 'id' => $viewdata[$i]->group);
        }
        $data[$i]['artefacts'] = array();
        $data[$i]['accessgroups'] = array();
        if ($viewdata[$i]->startdate && $viewdata[$i]->stopdate) {
            $data[$i]['access'] = get_string('accessbetweendates', 'view', format_date(strtotime($viewdata[$i]->startdate), 'strftimedate'),
                format_date(strtotime($viewdata[$i]->stopdate), 'strftimedate'));
        }
        else if ($viewdata[$i]->startdate) {
            $data[$i]['access'] = get_string('accessfromdate', 'view', format_date(strtotime($viewdata[$i]->startdate), 'strftimedate'));
        }
        else if ($viewdata[$i]->stopdate) {
            $data[$i]['access'] = get_string('accessuntildate', 'view', format_date(strtotime($viewdata[$i]->stopdate), 'strftimedate'));
        }
    }
    // Go through all the artefact records and put them in with the
    // views they belong to.
    if ($artefacts) {
        foreach ($artefacts as $artefactrec) {
            safe_require('artefact', $artefactrec->plugin);
            // Perhaps I shouldn't have to construct the entire
            // artefact object to render the name properly.
            $classname = generate_artefact_class_name($artefactrec->artefacttype);
            $artefactobj = new $classname(0, array('title' => $artefactrec->title));
            $artefactobj->set('dirty', false);
            $artname = $artefactobj->get_name();
            $data[$index[$artefactrec->view]]['artefacts'][] = array('id'    => $artefactrec->artefact,
                                                                     'title' => $artname);
        }
    }
    if ($accessgroups) {
        foreach ($accessgroups as $access) {
            $data[$index[$access->view]]['accessgroups'][] = array(
                'accesstype' => $access->accesstype, // friends, group, loggedin, public, tutorsgroup, user
                'id' => $access->id,
                'name' => $access->name,
                'startdate' => $access->startdate,
                'stopdate' => $access->stopdate
            );
        }
    }
}


/* Get a list of groups that the user belongs to which also have
   a tutor member.  This is the list of groups that the user is
   able to submit views to. */

if (!$tutorgroupdata = @get_records_sql_array('SELECT g.id, g.name
       FROM {group_member} u
       INNER JOIN {group} g ON (u.group = g.id AND g.deleted = 0)
       INNER JOIN {group_member} t ON t.group = g.id 
       WHERE u.member = ?
       AND t.tutor = 1
       AND t.member != ?
       GROUP BY g.id, g.name
       ORDER BY g.name', array($userid, $userid))) {
    $tutorgroupdata = array();
}
else {
	$options = array();
	foreach ($tutorgroupdata as $group) {
	    $options[$group->id] = $group->name;
	}
    $i = 0;
    foreach ($data as &$view) {
        if (empty($view['submittedto'])) {
            $view['submitto'] = pieform(array(
                'name' => 'submitto' . $i++,
                'method' => 'post',
                'renderer' => 'oneline',
                'autofocus' => false,
                'successcallback' => 'submitto_submit',
                'elements' => array(
                    'text1' => array(
                        'type' => 'html',
                        'value' => 'Submit this view to '
                    ),
                    'options' => array(
                        'type' => 'select',
                        'collapseifoneoption' => false,
                        'options' => $options,
                    ),
                    'text2' => array(
                        'type' => 'html',
                        'value' => ' for assessment',
                    ),
                    'submit' => array(
                        'type' => 'submit',
                        'value' => get_string('go')
                    ),
                    'view' => array(
                        'type' => 'hidden',
                        'value' => $view['id']
                    )
                ),
            ));
        }
        else {
            $view['submittedto'] = get_string('viewsubmittedtogroup', 'view', $viewdata[$i]->group, $viewdata[$i]->name);
        }
    }
}

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'view/myviews.php?',
    'count' => $count,
    'limit' => $limit,
    'offset' => $offset,
    'resultcounttextsingular' => get_string('view', 'view'),
    'resultcounttextplural' => get_string('views', 'view')
));

function submitto_submit(Pieform $form, $values) {
    redirect('/view/submit.php?id=' . $values['view'] . '&group=' . $values['options']);
}

$smarty = smarty();
$smarty->assign('views', $data);
$smarty->assign('pagination', $pagination['html']);
$smarty->display('view/index.tpl');
?>
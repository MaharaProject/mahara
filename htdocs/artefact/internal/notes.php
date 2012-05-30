<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2011 Catalyst IT Ltd and others; see:
 *                    http://wiki.mahara.org/Contributors
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
 * @subpackage artefact-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

define('INTERNAL', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'internal');
define('TITLE', get_string('Notes', 'artefact.internal'));

$offset  = param_integer('offset', 0);
$limit   = param_integer('limit', 10);
$baseurl = get_config('wwwroot') . 'artefact/internal/notes.php';
$params  = array();

if ($group = param_integer('group', null)) {
    define('MENUITEM', 'groups');
    define('GROUP', $group);
    require_once('group.php');
    if (!group_user_can_edit_views($group, $USER->get('id'))) {
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
    }
    $groupobj = group_current_group();
    $pageheading = get_string('notesfor', 'artefact.internal', $groupobj->name);
    $where = '"group" = ?';
    $values = array($group);
    $params['group'] = $group;
}
else if ($institution = param_alpha('institution', null)) {
    if ($institution == 'mahara') {
        define('ADMIN', 1);
        define('MENUITEM', 'configsite');
        $pageheading = get_string('Notes', 'artefact.internal');
    }
    else {
        define('INSTITUTIONALADMIN', 1);
        define('MENUITEM', 'manageinstitutions');
        require_once('institution.php');
        $institutionobj = new Institution($institution);
        $pageheading = get_string('notesfor', 'artefact.internal', $institutionobj->displayname);
    }
    if (!$USER->can_edit_institution($institution)) {
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
    }
    $where = 'institution = ?';
    $values = array($institution);
    $params['institution'] = $institution;
}
else {
    define('MENUITEM', 'content/notes');
    $pageheading = get_string('mynotes', 'artefact.internal');
    $where = 'owner = ?';
    $values = array($USER->get('id'));
}

if ($params) {
    $baseurl .= '?' . http_build_query($params);
}

$where .= ' AND artefacttype = ?';
$values[] = 'html';

$count  = count_records_select('artefact', $where, $values);

$data = get_records_select_assoc(
    'artefact', $where, $values,
    'title, id', '*', $offset, $limit
);

// Get blocks
if ($data) {
    $blocks = get_records_sql_assoc('
        SELECT
            bi.id AS block, bi.title AS blocktitle,
            va.artefact,
            va.view, v.title AS viewtitle,
            v.owner, u.firstname, u.lastname, u.preferredname,
            v.group, g.name AS groupname,
            v.institution, i.displayname AS institutionname
        FROM
            {block_instance} bi
            JOIN {view_artefact} va ON bi.id = va.block
            JOIN {view} v ON va.view = v.id
            LEFT OUTER JOIN {usr} u ON v.owner = u.id
            LEFT OUTER JOIN {group} g ON v.group = g.id
            LEFT OUTER JOIN {institution} i ON v.institution = i.name
        WHERE
            va.artefact IN (' . join(',', array_fill(0, count($data), '?')) . ')',
        array_keys($data)
    );
    if ($blocks) {
        foreach ($blocks as $b) {
            if (!isset($data[$b->artefact]->views)) {
                $data[$b->artefact]->views = array();
            }
            if (!isset($data[$b->artefact]->views[$b->view])) {
                $data[$b->artefact]->views[$b->view] = array(
                    'view' => $b->view,
                    'viewtitle' => $b->viewtitle
                );
                // Add the view owner's name if it's not the same as the note owner.  This will either
                // be a group artefact inside an individual's view, or it's an institution/site artefact.
                if ((!empty($params['group']) && $b->owner)
                    || (!empty($params['institution']) && $params['institution'] != $b->institution)) {
                    if ($b->owner) {
                        $ownername = display_default_name((object) array(
                            'firstname'     => $b->firstname,
                            'lastname'      => $b->lastname,
                            'preferredname' => $b->preferredname,
                        ));
                        $ownerurl  = get_config('wwwroot') . 'user/view.php?id=' . $b->owner;
                    }
                    else if ($b->group) {
                        $ownername = $b->groupname;
                        $ownerurl  = get_config('wwwroot') . 'group/view.php?id=' . $b->group;
                    }
                    else if ($b->institution == 'mahara') {
                        $ownername = get_config('sitename');
                    }
                    else {
                        $ownername = $b->institutionname;
                        $ownerurl  = get_config('wwwroot') . 'institution/index.php?institution=' . $b->institution;
                    }
                    $data[$b->artefact]->views[$b->view]['ownername'] = $ownername;
                    $data[$b->artefact]->views[$b->view]['ownerurl']  = $ownerurl;
                }
            }
            if (!isset($data[$b->artefact]->blocks)) {
                $data[$b->artefact]->blocks = array();
            }
            if (!isset($data[$b->artefact]->blocks[$b->block])) {
                $data[$b->artefact]->blocks[$b->block] = $b;
            }
        }
    }
    foreach ($data as $id => $n) {
        $n->deleteform = pieform(deletenote_form($id, $n));
    }
}

$pagination = build_pagination(array(
    'id'        => 'notes_pagination',
    'url'       => $baseurl,
    'datatable' => 'notes',
    'count'     => $count,
    'limit'     => $limit,
    'offset'    => $offset,
));

$js = '
$j(function() {
    $j(".notetitle").click(function(e) {
        e.preventDefault();
        $j("#" + this.id + "_desc").toggleClass("hidden");
    });
});';

$smarty = smarty();
$smarty->assign('PAGEHEADING', $pageheading);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign_by_ref('data', $data);
$smarty->assign('pagination', $pagination);
$smarty->display('artefact:internal:notes.tpl');

function deletenote_form($id, $notedata) {
    global $THEME;
    $form = array(
        'name'            => 'delete_' . $id,
        'successcallback' => 'deletenote_submit',
        'renderer'        => 'oneline',
        'class'           => 'oneline inline',
        'elements' => array(
            'delete' => array(
                'type'         => 'hidden',
                'value'        => $id,
            ),
            'submit' => array(
                'type'         => 'image',
                'src'          => $THEME->get_url('images/icon_close.gif'),
                'elementtitle' => get_string('delete'),
            ),
        ),
    );
    if (!empty($notedata->blocks)) {
        $form['elements']['submit']['confirm'] = get_string(
            'confirmdeletenote', 'artefact.internal',
            count($notedata->blocks), count($notedata->views)
        );
    }
    return $form;
}

function deletenote_submit(Pieform $form, array $values) {
    global $SESSION, $data, $baseurl;
    $id = $data[$values['delete']]->id;
    $note = new ArtefactTypeHtml($id);
    $note->delete();
    $SESSION->add_ok_msg(get_string('notedeleted', 'artefact.internal'));
    redirect($baseurl);
}

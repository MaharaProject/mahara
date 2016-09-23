<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'internal');
require_once('view.php');


$offset  = param_integer('offset', 0);
$limit   = param_integer('limit', 10);
$setlimit = param_boolean('setlimit', false);
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
            va.view, v.title AS viewtitle, v.owner, v.group, v.institution, v.ownerformat, v.urlid
        FROM
            {block_instance} bi
            JOIN {view_artefact} va ON bi.id = va.block
            JOIN {view} v ON va.view = v.id
        WHERE
            va.artefact IN (' . join(',', array_fill(0, count($data), '?')) . ')
        ORDER BY va.view, bi.title',
        array_keys($data)
    );
    if ($blocks) {
        $viewdata = array();
        foreach ($blocks as $b) {
            if (!isset($viewdata[$b->view])) {
                $viewdata[$b->view] = (object) array(
                    'id'          => $b->view,
                    'title'       => $b->viewtitle,
                    'owner'       => $b->owner,
                    'group'       => $b->group,
                    'institution' => $b->institution,
                    'ownerformat' => $b->ownerformat,
                    'urlid'       => $b->urlid,
                );
            }
        }
        View::get_extra_view_info($viewdata, false, false);

        foreach ($blocks as $b) {
            if (!isset($data[$b->artefact]->views)) {
                $data[$b->artefact]->views = array();
            }
            if (!isset($data[$b->artefact]->views[$b->view])) {
                $data[$b->artefact]->views[$b->view] = array(
                    'view' => $b->view,
                    'viewtitle' => $b->viewtitle,
                    'fullurl' => $viewdata[$b->view]['fullurl'],
                );
                // Add the view owner's name if it's not the same as the note owner.  This will either
                // be a group artefact inside an individual's view, or it's an institution/site artefact.
                if ((!empty($params['group']) && $b->owner)
                    || (!empty($params['institution']) && $params['institution'] != $b->institution)) {
                    if ($b->owner) {
                        $ownername = display_default_name($viewdata[$b->view]['user']);
                        $ownerurl  = profile_url($viewdata[$b->view]['user']);
                    }
                    else if ($b->group) {
                        $ownername = $viewdata[$b->view]['groupdata']['name'];
                        $ownerurl  = group_homepage_url($viewdata[$b->view]['groupdata']);
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
                $data[$b->artefact]->blocks[$b->block] = (array)$b;
                (!isset($data[$b->artefact]->views[$b->view]['extrablocks'])) ? $data[$b->artefact]->views[$b->view]['extrablocks'] = 0 : $data[$b->artefact]->views[$b->view]['extrablocks'] ++;
            }
            if (!isset($data[$b->artefact]->tags)) {
                $data[$b->artefact]->tags = ArtefactType::artefact_get_tags($b->artefact);
            }
        }
    }
    foreach ($data as $id => $n) {
        $n->deleteform = pieform(deletenote_form($id, $n));
    }
}

// Get the attached files.
$noteids = array();
if ($data) {
    $noteids = array_keys($data);
}
$files = ArtefactType::attachments_from_id_list($noteids);
if ($files) {
    safe_require('artefact', 'file');
    foreach ($files as $file) {
        $file->icon = call_static_method(generate_artefact_class_name($file->artefacttype), 'get_icon', array('id' => $file->attachment));
        $data[$file->artefact]->files[] = $file;
    }
}
// Add Attachments count for each Note
if ($data) {
    foreach ($data as $item) {
        $item->count = isset($item->files) ? count($item->files) : 0;
    }
}

$pagination = build_pagination(array(
    'id'        => 'notes_pagination',
    'url'       => $baseurl,
    'datatable' => 'notes',
    'count'     => $count,
    'limit'     => $limit,
    'offset'    => $offset,
    'jsonscript' => 'artefact/internal/notes.json.php',
    'setlimit' => true,
    'jumplinks' => 6,
    'numbersincludeprevnext' => 2,
));

$smarty = smarty_core();
$smarty->assign('data', $data);
$html = $smarty->fetch('artefact:internal:noteresults.tpl');

function deletenote_form($id, $notedata) {
    global $THEME;
    $form = array(
        'name'            => 'delete_' . $id,
        'successcallback' => 'deletenote_submit',
        'class' => 'form-as-button',
        'elements' => array(
            'delete' => array(
                'type'         => 'hidden',
                'value'        => $id,
            ),
            'submit' => array(
                'type' => 'button',
                'usebuttontag' => true,
                'class' => 'btn-default btn-xs last',
                'elementtitle' => get_string('delete'),
                'value' => '<span class="icon icon-trash text-danger icon-lg" role="presentation" aria-hidden="true"></span> ',
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

json_reply(false, array(
    'message' => null,
    'data' => array(
        'tablerows' => $html,
        'pagination' => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
        'count' => $count,
        'results' => $count . ' ' . ($count == 1 ? get_string('result') : get_string('results')),
        'offset' => $offset,
        'setlimit' => $setlimit,
    )
));

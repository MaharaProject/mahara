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

define('INTERNAL', 1);
define('MENUITEM', 'view');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'create4');

require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('createviewstep4', 'view'));
require_once('pieforms/pieform.php');
require_once('pieforms/pieform/elements/calendar.php');
$smarty = smarty(array('tablerenderer'), pieform_element_calendar_get_headdata(pieform_element_calendar_configure(array())));
$createid = param_integer('createid', null);
$data = $SESSION->get('create_' . $createid);


if (empty($data['artefacts'])) {
    $confirmmessage = get_string('reallyaddaccesstoemptyview');
    $backpage = get_config('wwwroot') . 'view/create3.php?createid=' . $createid;
    $js = <<<EOF
addLoadEvent(function() {
    connect('createview4_submit', 'onclick', function () {
        var accesslistrows = getElementsByTagAndClassName('tr', null, 'accesslistitems');
        if (accesslistrows.length > 0 && !confirm('{$confirmmessage}')) {
            replaceChildNodes('accesslistitems', []);
        }
    });
});
EOF;
    $smarty->assign('INLINEJAVASCRIPT', $js);
}

$form = array(
    'name' => 'createview4',
    'elements' => array(
        'accesslist' => array(
            'type'         => 'viewacl',
            'defaultvalue' => isset($data['accesslist']) ? $data['accesslist'] : null
        ),
        'submit' => array(
            'type' => 'cancelbackcreate',
            'value' => array(get_string('cancel'), get_string('back','view'), get_string('createview','view'))
        )
    )
);

function createview4_cancel_submit() {
    redirect('/view/');
}


function createview4_submit(Pieform $form, $values) {
    global $SESSION, $USER, $createid, $data;

    if (param_boolean('back')) {
        $data['accesslist'] = array_values((array)$values['accesslist']);
        $SESSION->set('create_' . $createid, $data);
        redirect('/view/create3.php?createid=' . $createid);
    }

    db_begin();
    $time = db_format_timestamp(time());

    $view = new StdClass;
    $view->title = $data['title'];
    $view->description = $data['description'];
    $view->owner = $USER->get('id');
    $view->ownerformat = $data['ownerformat'];
    $view->template = $data['template'];
    $view->startdate = db_format_timestamp($data['startdate']);
    $view->stopdate = db_format_timestamp($data['stopdate']);
    $view->ctime = $view->mtime = $view->atime = $time;
    $viewid = insert_record('view', $view, 'id', true);

    foreach ($data['tags'] as $tag) {
        insert_record('view_tag', (object)array('view' => $viewid, 'tag' => $tag));
    }

    foreach ($data['artefacts'] as $block => $blockdata) {
        if ($blockdata['type'] == 'label') {
            $viewcontent          = new StdClass;
            $viewcontent->view    = $viewid;
            $viewcontent->content = $blockdata['value'];
            $viewcontent->block   = $block;
            $viewcontent->ctime   = $time;
            insert_record('view_content', $viewcontent);
        }
        else if ($blockdata['type'] == 'artefact') {
            $blockdata['id'] = (array)$blockdata['id'];
            foreach ($blockdata['id'] as $id) {
                $viewartefact           = new StdClass;
                $viewartefact->view     = $viewid;
                $viewartefact->artefact = $id;
                $viewartefact->block    = $block;
                $viewartefact->ctime    = $time;
                $viewartefact->format   = $blockdata['format'];
                insert_record('view_artefact', $viewartefact);
            }
        }
        else {
            throw new UserException('Invalid block type');
        }
    }

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
                case 'community':
                    $accessrecord->community = $item['id'];
                    $accessrecord->tutoronly = $item['tutoronly'];
                    insert_record('view_access_community', $accessrecord);
                    break;
            }
        }
    }

    $data = new StdClass;
    $data->owner = $USER->get('id');
    $data->view = $viewid;
    activity_occurred('newview', $data);

    db_commit();

    handle_event('saveview', $viewid);

    $SESSION->add_ok_msg(get_string('viewcreatedsuccessfully', 'view'));
    redirect('/view/');
}

function createview4_cancel() {
    redirect('/view/');
}

$smarty->assign('titlestr', get_string('createviewstep4', 'view'));
$smarty->assign('form', pieform($form));
$smarty->display('view/create4.tpl');

?>

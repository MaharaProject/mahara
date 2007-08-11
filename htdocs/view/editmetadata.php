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
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/views');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'editmetadata');

require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('editmetadata', 'view'));
require_once('pieforms/pieform.php');
require_once('template.php');

$view_id = param_integer('viewid');

$formatstring = '%s (%s)';
$ownerformatoptions = array(
    FORMAT_NAME_FIRSTNAME => sprintf($formatstring, get_string('firstname'), $USER->get('firstname')),
    FORMAT_NAME_LASTNAME => sprintf($formatstring, get_string('lastname'), $USER->get('lastname')),
    FORMAT_NAME_FIRSTNAMELASTNAME => sprintf($formatstring, get_string('fullname'), full_name())
);

$preferredname = $USER->get('preferredname');
if ($preferredname !== '') {
    $ownerformatoptions[FORMAT_NAME_PREFERREDNAME] = sprintf($formatstring, get_string('preferredname'), $preferredname);
}
$studentid = (string)get_field('artefact', 'title', 'owner', $USER->get('id'), 'artefacttype', 'studentid');
if ($studentid !== '') {
    $ownerformatoptions[FORMAT_NAME_STUDENTID] = sprintf($formatstring, get_string('studentid'), $studentid);
}
$ownerformatoptions[FORMAT_NAME_DISPLAYNAME] = sprintf($formatstring, get_string('displayname'), display_name($USER));

$data = get_record(
    'view',
    'id', $view_id,
    'owner', $USER->get('id'),
    null,null,
    'id,title,description,owner,ownerformat,' . db_format_tsfield('startdate') . ',' . db_format_tsfield('stopdate')
);

if(!$data) {
    $SESSION->add_error_msg(get_string('canteditdontown', 'view'));
    redirect('/view/');
}

$createview1 = pieform(array(
    'name'     => 'createview1',
    'method'   => 'post',
    'autofocus' => 'title',
    'plugintype' => 'core',
    'pluginname' => 'view',
    'elements' => array(
        'viewid' => array(
            'type'  => 'hidden',
            'value' => $view_id,
        ),
        'title' => array(
            'type'         => 'text',
            'title'        => get_string('title','view'),
            'defaultvalue' => $data->title,
            'rules'        => array( 'required' => true ),
            'help '        => true,
        ),
        'startdate'        => array(
            'type'         => 'calendar',
            'title'        => get_string('startdate','view'),
            'defaultvalue' => $data->startdate,
            'caloptions'   => array(
                'dateStatusFunc' => 'startDateDisallowed',
                'onSelect'       => 'startSelected',
                'showsTime'      => true,
                'ifFormat'       => '%Y/%m/%d %H:%M'
            ),
            'help '        => true,
        ),
        'stopdate'  => array(
            'type'         => 'calendar',
            'title'        => get_string('stopdate','view'),
            'defaultvalue' => $data->stopdate,
            'caloptions'   => array(
                'dateStatusFunc' => 'stopDateDisallowed',
                'onSelect'       => 'stopSelected',
                'showsTime'      => true,
                'ifFormat'       => '%Y/%m/%d %H:%M'
            ),
            'help '        => true,
        ),
        'description' => array(
            'type'         => 'wysiwyg',
            'title'        => get_string('description','view'),
            'rows'         => 10,
            'cols'         => 70,
            'defaultvalue' => $data->description,
            'help '        => true,
        ),
        'tags'        => array(
            'type'         => 'tags',
            'title'        => get_string('tags'),
            'description'  => get_string('tagsdesc'),
            'defaultvalue' => get_column('view_tag', 'tag', 'view', $view_id),
            'help'         => true,
        ),
        'ownerformat' => array(
            'type'         => 'select',
            'title'        => get_string('ownerformat','view'),
            'description'  => get_string('ownerformatdescription','view'),
            'options'      => $ownerformatoptions,
            'defaultvalue' => $data->ownerformat,
            'rules'        => array('required' => true),
            'help '        => true,
        ),
        'submit'   => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('save'), get_string('cancel')),
        ),
    ),
));

function createview1_cancel_submit() {
    redirect('/view/');
}

function createview1_submit(Pieform $form, $values) {
    global $SESSION;
    global $view_id;

    $data = new StdClass;
    $data->title       = $values['title'];
    $data->description = $values['description'];
    $data->startdate   = db_format_timestamp($values['startdate']);
    $data->stopdate    = db_format_timestamp($values['stopdate']);
    $data->ownerformat = $values['ownerformat'];
    $data->mtime       = db_format_timestamp(time());

    db_begin();
    update_record('view', $data, (object)array( 'id' => $view_id ));
    delete_records('view_tag', 'view', $view_id);
    foreach ($values['tags'] as $tag) {
        insert_record('view_tag', (object)array( 'view' => $view_id, 'tag' => $tag));
    }

    db_commit();

    handle_event('saveview', $view_id);

    $SESSION->add_ok_msg(get_string('viewinformationsaved', 'view'));
    redirect('/view/');
}

$smarty = smarty();
$smarty->assign('createview1', $createview1);
$smarty->assign('INLINEJAVASCRIPT', <<<EOF
function startDateDisallowed(date) {
    var stopDate = $('createview1_stopdate').value;
    if (stopDate != '') {
        stopDate = stopDate.substr(0, 10).replace(/\//g, '-');
        stopDate = isoDate(stopDate);
        if (!stopDate) {
            stopDate = Date();
        }
        if (stopDate.getTime() < date.getTime()) {
            return true;
        }
    }
    
    return false;
}
function stopDateDisallowed(date) {
    var startDate = $('createview1_startdate').value;
    if (startDate != '') {
        startDate = startDate.substr(0, 10).replace(/\//g, '-');
        startDate = isoDate(startDate);
        if (!startDate) {
            startDate = Date();
        }
        if (startDate.getTime() > date.getTime()) {
            return true;
        }
    }
    
    return false;
}
function startSelected(calendar, date) {
    if (calendar.dateClicked) {
        var stopDate = $('createview1_stopdate').value;
        if (stopDate != '' && stopDateDisallowed(isoDate(stopDate))) {
            $('createview1_stopdate').value = date;
        }
        $('createview1_startdate').value = date;
        calendar.callCloseHandler();
    }
}
function stopSelected(calendar, date) {
    if (calendar.dateClicked) {
        var startDate = $('createview1_startdate').value.replace(/\//g, '-');
        if (startDate != '' && startDateDisallowed(isoDate(startDate))) {
            $('createview1_startdate').value = date;
        }
        $('createview1_stopdate').value = date;
        calendar.callCloseHandler();
    }
}

EOF
);
$smarty->assign('EDITMODE', true);
$smarty->display('view/create1.tpl');

?>

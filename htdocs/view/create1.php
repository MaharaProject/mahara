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
define('MENUITEM', 'myviews');
// define('SUBMENUITEM', 'mygroups');
require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('createviewstep1', 'view'));
require_once('pieforms/pieform.php');
require_once('template.php');

$createid = param_integer('createid', null);

if ($createid === null) {
    $createid = $SESSION->get('createid');
    if (empty($createid)) {
        $createid = 0;
    }
    
    $SESSION->set('createid', $createid + 1);
}


$data = $SESSION->get('create_' . $createid);

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

// @todo need a rule here that prevents stopdate being smaller than startdate
$createview1 = pieform(array(
    'name'     => 'createview1',
    'method'   => 'post',
    'autofocus' => 'title',
    'plugintype' => 'core',
    'pluginname' => 'view',
    'elements' => array(
        'createid' => array(
            'type'  => 'hidden',
            'value' => $createid,
        ),
        'title' => array(
            'type'         => 'text',
            'title'        => get_string('title','view'),
            'defaultvalue' => isset($data['title']) ? $data['title'] : null,
            'rules'        => array( 'required' => true ),
            'help'         => true,
        ),
        'startdate'        => array(
            'type'         => 'calendar',
            'title'        => get_string('startdate','view'),
            'defaultvalue' => isset($data['startdate']) ? $data['startdate'] : null,
            'caloptions'   => array(
                //'dateStatusFunc' => 'startDateDisallowed',
                //'onSelect'       => 'startSelected',
                'showsTime'      => true,
                'ifFormat'       => '%Y/%m/%d %H:%M'
            ),
            'help'         => true,
        ),
        'stopdate'  => array(
            'type'         => 'calendar',
            'title'        => get_string('stopdate','view'),
            'defaultvalue' => isset($data['stopdate']) ? $data['stopdate'] : null,
            'caloptions'   => array(
                //'dateStatusFunc' => 'stopDateDisallowed',
                //'onSelect'       => 'stopSelected',
                'showsTime'      => true,
                'ifFormat'       => '%Y/%m/%d %H:%M'
            ),
            'help'         => true,
        ),
        'description' => array(
            'type'         => 'wysiwyg',
            'title'        => get_string('description','view'),
            'rows'         => 10,
            'cols'         => 70,
            'defaultvalue' => isset($data['description']) ? $data['description'] : null,
            'help'         => true,
        ),
        'tags'        => array(
            'type'         => 'tags',
            'title'        => get_string('tags'),
            'description'  => get_string('tagsdesc'),
            'defaultvalue' => isset($data['tags']) ? $data['tags'] : null,
        ),
        'ownerformat' => array(
            'type'         => 'select',
            'title'        => get_string('ownerformat','view'),
            'description'  => get_string('ownerformatdescription','view'),
            'options'      => $ownerformatoptions,
            'defaultvalue' => isset($data['ownerformat']) ? $data['ownerformat'] : FORMAT_NAME_DISPLAYNAME,
            'rules'        => array('required' => true),
            'help'         => true,
        ),
        'submit'   => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('next','view'), get_string('cancel')),
        ),
    ),
));

function createview1_cancel_submit() {
    global $createid;
    global $SESSION;

    $SESSION->clear('create_' . $createid);

    redirect('/view/');
}

function createview1_submit(Pieform $form, $values) {
    global $SESSION;

    $data = $SESSION->get('create_' . $values['createid']);

    if (!is_array($data)) {
        $data = array();
    }

    $data['title']       = $values['title'];
    $data['description'] = $values['description'];
    $data['tags']        = $values['tags'];
    $data['startdate']   = $values['startdate'];
    $data['stopdate']    = $values['stopdate'];
    $data['ownerformat'] = $values['ownerformat'];

    $SESSION->set('create_' . $values['createid'], $data);

    redirect('/view/create2.php?createid=' . $values['createid']);
}

$smarty = smarty();
$smarty->assign('createview1', $createview1);
// NOTE: this javascript shows an idea of how you might make it so that you can't select invalid dates.
// However, it's broken (try selecting a start date and then an end date). Perhaps it can be improved
// later...
/*$smarty->assign('INLINEJAVASCRIPT', <<<EOF
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
);*/
$smarty->display('view/create1.tpl');

?>

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
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');

$createid = param_integer('createid', null);

if ($createid === null) {
    $createid = $SESSION->get('createid');
    if (empty($createid)) {
        $createid = 0;
    }
    
    $SESSION->set('createid', $createid + 1);
}

define('MENUITEM', 'myviews');
// define('SUBMENUITEM', 'mygroups');

$data = $SESSION->get('create_' . $createid);

$createview1 = pieform(array(
    'name'     => 'createview1',
    'method'   => 'post',
    'elements' => array(
        'createid' => array(
            'type'  => 'hidden',
            'value' => $createid,
        ),
        'title' => array(
            'type'         => 'text',
            'title'        => get_string('title'),
            'defaultvalue' => isset($data['title']) ? $data['title'] : null,
            'rules'        => array( 'required' => true ),
        ),
        'startdate'        => array(
            'type'         => 'date',
            'title'        => get_string('startdate'),
            'defaultvalue' => isset($data['startdate']) ? $data['startdate'] : null,
            'optional'     => true,
        ),
        'stopdate'  => array(
            'type'         => 'date',
            'title'        => get_string('stopdate'),
            'defaultvalue' => isset($data['stopdate']) ? $data['stopdate'] : null,
            'optional'     => true,
        ),
        'description' => array(
            'type'         => 'wysiwyg',
            'title'        => get_string('description'),
            'rows'         => 10,
            'cols'         => 80,
            'defaultvalue' => isset($data['description']) ? $data['description'] : null,
        ),
        'submit'   => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('next'), get_string('cancel')),
        ),
    ),
));

function createview1_cancel_submit() {
    global $createid;
    global $SESSION;

    $SESSION->clear('create_' . $createid);

    redirect(get_config('wwwroot') . 'view/');
}

function createview1_submit($values) {
    global $SESSION;

    $data = $SESSION->get('create_' . $values['createid']);

    if (!is_array($data)) {
        $data = array();
    }

    $data['title']       = $values['title'];
    $data['description'] = $values['description'];
    $data['startdate']   = $values['startdate'];
    $data['stopdate']    = $values['stopdate'];

    $SESSION->set('create_' . $values['createid'], $data);

    redirect(get_config('wwwroot') . 'view/create2.php?createid=' . $values['createid']);
}

$smarty = smarty();
$smarty->assign('createview1', $createview1);
$smarty->display('view/create1.tpl');

?>

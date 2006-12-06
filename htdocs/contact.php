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
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);

require('init.php');
require_once('pieforms/pieform.php');

if ($USER->is_logged_in()) {
    $name = display_name($USER->get('id'));
    $email = $USER->get('email');
}
else {
    $name = '';
    $email = '';
}

$contactform = pieform(array(
    'name'     => 'contactus',
    'method'   => 'post',
    'action'   => '',
    'ajaxpost' => true,
    'elements' => array(
        'name' => array(
            'type'  => 'text',
            'title' => get_string('name'),
            'defaultvalue' => $name,
            'rules' => array(
                'required'    => true
            ),
        ),
        'email' => array(
            'type'  => 'text',
            'title' => get_string('email'),
            'defaultvalue' => $email,
            'rules' => array(
                'required'    => true
            ),
        ),
        'subject' => array(
            'type'  => 'text',
            'title' => get_string('subject'),
            'defaultvalue' => '',
        ),
        'message' => array(
            'type'  => 'textarea',
            'rows'  => 10,
            'cols'  => 60,
            'title' => get_string('message'),
            'defaultvalue' => '',
            'rules' => array(
                'required'    => true
            ),
        ),
        'submit' => array(
            'type'  => 'submit',
            'value' => get_string('submitcontactinformation')
        ),
    )
));

function contactus_submit($values) {
    $data = new StdClass;
    $data->name    = $values['name'];
    $data->email   = $values['email'];
    $data->subject = $values['subject'];
    $data->message = $values['message'];
    require_once('activity.php');
    activity_occurred('contactus', $data);
    json_reply(false, get_string('contactinformationsent'));
}


$smarty = smarty();
$smarty->assign('page_content', $contactform);
$smarty->assign('site_menu', site_menu());
$smarty->assign('searchform', searchform());
$smarty->display('sitepage.tpl');

?>

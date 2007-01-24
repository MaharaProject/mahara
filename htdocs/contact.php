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
$contactus = get_string('contactus');
define('TITLE', $contactus);

if ($USER->is_logged_in()) {
    $userid = $USER->get('id');
    $name = display_name($userid);
    $email = $USER->get('email');
}
else {
    $userid = null;
    $name = '';
    $email = '';
}

$contactform = pieform(array(
    'name'     => 'contactus',
    'method'   => 'post',
    'action'   => '',
    'jsform' => true,
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
        'userid' => array(
            'type'  => 'hidden',
            'value' => $userid,
        ),
        'submit' => array(
            'type'  => 'submit',
            'value' => get_string('submitcontactinformation')
        ),
    )
));

function contactus_submit(Pieform $form, $values) {
    $data = new StdClass;
    $data->fromname    = $values['name'];
    $data->fromemail   = $values['email'];
    $data->subject     = $values['subject'];
    $data->message     = $values['message'];
    if ($values['userid']) {
        $data->userfrom = $values['userid'];
    }
    require_once('activity.php');
    activity_occurred('contactus', $data);
    $form->json_reply(PIEFORM_OK, get_string('contactinformationsent'));
}

$pagecontent = <<<EOF
<h2>$contactus</h2>

$contactform
EOF;

$smarty = smarty();
$smarty->assign('page_content', $pagecontent);
$smarty->assign('searchform', searchform());
$smarty->display('sitepage.tpl');

?>

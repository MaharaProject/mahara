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
define('PUBLIC', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'site');
define('SECTION_PAGE', 'forgotpass');

require('init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('forgotpassword'));

if (!session_id()) {
    session_start();
}

if (!empty($_SESSION['pwchangerequested'])) {
    unset($_SESSION['pwchangerequested']);
    die_info(get_string('pwchangerequestsent'));
}

if (isset($_GET['key'])) {
    if (!$pwrequest = get_record('usr_password_request', 'key', $_GET['key'])) {
        die_info(get_string('nosuchpasswordrequest'));
    }

    $form = array(
        'name' => 'forgotpasschange',
        'method' => 'post',
        'action' => '',
        'autofocus' => true,
        'elements' => array(
            'password1' => array(
                'type' => 'password',
                'title' => get_string('password'),
                'rules' => array(
                    'required' => true
                )
            ),
            'password2' => array(
                'type' => 'password',
                'title' => get_string('confirmpassword'),
                'rules' => array(
                    'required' => true
                )
            ),
            'user' => array(
                'type' => 'hidden',
                'value' => $pwrequest->usr
            ),
            'submit' => array(
                'type' => 'submit',
                'value' => get_string('change')
            )
        )
    );

    $smarty = smarty();
    $smarty->assign('forgotpasschange_form', pieform($form));
    $smarty->display('forgotpass.tpl');
    exit;
}

$form = array(
    'name'      => 'forgotpass',
    'method'    => 'post',
    'action'    => '',
    'autofocus' => true,
    'elements'  => array(
        'email' => array(
            'type' => 'text',
            'title' => get_string('emailaddress'),
            'description' => get_string('emailaddressdescription'),
            'rules' => array(
                'required' => true,
                'email' => true
            )
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('send')
        )
    )
);

function forgotpass_validate(Pieform $form, $values) {
    // The e-mail address cannot already be in the system
    if (!$form->get_error('email') && !($user = get_record('usr', 'email', $values['email']))) {
        $form->set_error('email', get_string('forgotpassnosuchemailaddress'));
        return;
    }

    if ($form->get_error('email')) {
        return;
    }

    $authobj = AuthFactory::create($user->authinstance);
    if (!method_exists($authobj, 'change_password')) {
        die_info(get_string('cantchangepassword'));
    }
}

function forgotpass_submit(Pieform $form, $values) {
    global $SESSION;

    try {
        if (!$user = get_record('usr', 'email', $values['email'])) {
            die_info(get_string('forgotpassnosuchemailaddress'));
        }


        $pwrequest = new StdClass;
        $pwrequest->usr = $user->id;
        $pwrequest->expiry = db_format_timestamp(time() + 86400);
        $pwrequest->key = get_random_key();
        $sitename = get_config('sitename');
        $fullname = display_name($user);
        email_user($user, null,
            get_string('forgotpassemailsubject', 'mahara', $sitename),
            get_string('forgotpassemailmessagetext', 'mahara', $fullname, $sitename, $pwrequest->key, $sitename, $pwrequest->key),
            get_string('forgotpassemailmessagehtml', 'mahara', $fullname, $sitename, $pwrequest->key, $pwrequest->key, $sitename, $pwrequest->key, $pwrequest->key));
        insert_record('usr_password_request', $pwrequest);
    }
    catch (SQLException $e) {
        die_info(get_string('forgotpassemailsendunsuccessful'));
    }
    catch (EmailException $e) {
        die_info(get_string('forgotpassemailsendunsuccessful'));
    }

    // Add a marker in the session to say that the user has registered
    $_SESSION['pwchangerequested'] = true;

    redirect('/forgotpass.php');
}

function forgotpasschange_validate(Pieform $form, $values) {
    $user = new User();
    $user->find_by_id($values['user']);
    password_validate($form, $values, $user);
}


// TODO:
//   password_validate to maharalib, use it in places specified, test with a drop/create run
//   support autofocus => (true|'id'), remove stuff doing autofocus from where it is, focus error fields
//   commit stuff
function forgotpasschange_submit(Pieform $form, $values) {
    global $SESSION, $USER;

    try {
        $user = new User();
        $user->find_by_id($values['user']);
    } catch (AuthUnknownUserException $e) {
        throw new Exception('Request to change the password for a user who does not exist');
    }

    $authobj = AuthFactory::create($user->authinstance);
    if ($password = $authobj->change_password($user, $values['password1'])) {

        // Remove the password request(s) for the user
        delete_records('usr_password_request', 'usr', $values['user']);

        $USER->reanimate($user->id, $user->authinstance);
        $SESSION->add_ok_msg(get_string('passwordchangedok'));
        redirect();
        exit;
    }

    throw new Exception('User "' . $user->username
        . ' tried to change their password, but the attempt failed');
}

$smarty = smarty();
$smarty->assign('forgotpass_form', pieform($form));
$smarty->display('forgotpass.tpl');

?>

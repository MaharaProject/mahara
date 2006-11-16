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
require('init.php');

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
                'value' => get_string('submit')
            )
        )
    );

    $smarty = smarty();
    $smarty->assign('forgotpasschange_form', form($form));
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
            'value' => get_string('submit')
        )
    )
);

function forgotpass_validate(Form $form, $values) {
    // The e-mail address cannot already be in the system
    if (!$form->get_error('email') && !($user = get_record('usr', 'email', $values['email']))) {
        $form->set_error('email', get_string('forgotpassnosuchemailaddress'));
        return;
    }
    
    $authtype = auth_get_authtype_for_institution($user->institution);
    safe_require('auth', $authtype, 'lib.php', 'require_once');
    $authclass = 'Auth' . ucfirst($authtype);
    if (!method_exists($authclass, 'change_password')) {
        die_info(get_string('cantchangepassword'));
    }
}

function forgotpass_submit($values) {
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

    redirect(get_config('wwwroot') . 'forgotpass.php');
}

function forgotpasschange_validate(Form $form, $values) {
    if (!$user = get_record('usr', 'id', $values['user'])) {
        throw new Exception('Request to change the password for a user who does not exist');
    }
    password_validate($form, $values, $user);
}


// TODO:
//   password_validate to maharalib, use it in places specified, test with a drop/create run
//   support autofocus => (true|'id'), remove stuff doing autofocus from where it is, focus error fields
//   commit stuff
function forgotpasschange_submit($values) {
    global $SESSION;

    if (!$user = get_record('usr', 'id', $values['user'])) {
        throw new Exception('Request to change the password for a user who does not exist');
    }

    $authtype  = auth_get_authtype_for_institution($user->institution);
    $authclass = 'Auth' . ucfirst($authtype);
    safe_require('auth', $authtype);
    log_debug($values);

    if ($password = call_static_method($authclass, 'change_password', $user->username, $values['password1'])) {
        $userrec = new StdClass;
        $userrec->password = $password;
        // @todo at the time of writing, update_record didn't support a null $where clause, even though
        // it's the default. That should be fixed, so this is easier
        $where = new StdClass;
        $where->id = $values['user'];
        update_record('usr', $userrec, $where);

        // Remove the password request(s) for the user
        delete_records('usr_password_request', 'usr', $values['user']);

        $SESSION->login($user);
        $SESSION->add_ok_msg(get_string('passwordchangedok'));
        redirect(get_config('wwwroot'));
        exit;
    }

    throw new Exception('User "' . $user->username . '@' . $user->institution
        . ' tried to change their password, but the attempt failed');
}

$smarty = smarty();
$smarty->assign('forgotpass_form', form($form));
$smarty->display('forgotpass.tpl');

?>

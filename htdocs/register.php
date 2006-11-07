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

$elements = array(
    'username' => array(
        'type' => 'text',
        'title' => get_string('username'),
        'description' => get_string('usernamedescription'),
        'rules' => array(
            'required' => true
        )
    ),
    'password1' => array(
        'type' => 'password',
        'title' => get_string('password'),
        'description' => get_string('passworddescription'),
        'rules' => array(
            'required' => true
        )
    ),
    'password2' => array(
        'type' => 'password',
        'title' => get_string('confirmpassword'),
        'description' => get_string('password2description'),
        'rules' => array(
            'required' => true
        )
    ),
    'firstname' => array(
        'type' => 'text',
        'title' => get_string('firstname'),
        'description' => get_string('firstnamedescription'),
        'rules' => array(
            'required' => true
        )
    ),
    'lastname' => array(
        'type' => 'text',
        'title' => get_string('lastname'),
        'description' => get_string('lastnamedescription'),
        'rules' => array(
            'required' => true
        )
    ),
    'email' => array(
        'type' => 'text',
        'title' => get_string('emailaddress'),
        'description' => get_string('emailaddressdescription'),
        'rules' => array(
            'required' => true,
            'email' => true
        )
    )
);

$institutions = get_records('institution', 'registerallowed', true);
if (count($institutions) > 1) {
    $options = array();
    foreach ($institutions as $institution) {
        $options[$institution->name] = $institution->displayname;
    }
    $elements['institution'] = array(
        'type' => 'select',
        'title' => get_string('institution'),
        'description' => get_string('institutiondescription'),
        'options' => $options
    );
}
else {
    $elements['institution'] = array(
        'type' => 'hidden',
        'value' => 'mahara'
    );
}

$elements['tandc'] = array(
    'type' => 'radio',
    'title' => get_string('iagreetothetermsandconditions'),
    'description' => get_string('youmustagreetothetermsandconditions'),
    'options' => array(
        'yes' => get_string('yes'),
        'no'  => get_string('no')
    )
);

$elements['submit'] = array(
    'type' => 'submit',
    'value' => get_string('register')
);

$form = array(
    'name' => 'register',
    'method' => 'post',
    'action' => '',
    'elements' => $elements
);

function register_validate(Form $form, $values) {
    global $SESSION;
    $SESSION->add_info_msg('validating...');
    
    $institution = $values['institution'];
    $authtype    = auth_get_authtype_for_institution($institution);
    $authclass   = 'Auth' . ucfirst(strtolower($authtype));
    log_debug($institution . ' ' . $authclass . ' ' . $authtype);
    safe_require('auth', $authtype, 'lib.php', 'require_once');

    if (!$form->get_error('username') && !call_static_method($authclass, 'is_username_valid', $values['username'])) {
        $form->set_error('username', get_string('usernameinvalidform', 'auth.' . auth_get_authtype_for_institution($institution)));
    }
}

function register_submit($values) {
    global $SESSION;
    $SESSION->add_ok_msg('w00t');
}

$smarty = smarty();
$smarty->assign('register_form', form($form));
$smarty->display('register.tpl');

?>

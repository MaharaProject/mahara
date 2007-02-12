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
define('MENUITEM', 'home');
require('init.php');
define('TITLE', get_string('home'));

// Check for whether the user is logged in, before processing the page. After
// this, we can guarantee whether the user is logged in or not for this page.
if (!$USER->is_logged_in()) {
    require_once('pieforms/pieform.php');
    $institutions = get_records_menu('institution', '', '', 'name, displayname');
    $defaultinstitution = get_cookie('institution');
    if (!$defaultinstitution) {
        $defaultinstitution = 'mahara';
    }
    $loginform = get_login_form_js(pieform(array(
        'name'       => 'login',
        'renderer'   => 'div',
        'submit'     => false,
        'plugintype' => 'auth',
        'pluginname' => 'internal',
        'elements'   => array(
            'login' => array(
                'type'   => 'fieldset',
                'legend' => get_string('login'),
                'elements' => array(
                    'login_username' => array(
                        'type'        => 'text',
                        'title'       => get_string('username') . ':',
                        'description' => get_string('usernamedescription'),
                        'rules' => array(
                            'required'    => true
                        )
                    ),
                    'login_password' => array(
                        'type'        => 'password',
                        'title'       => get_string('password') . ':',
                        'description' => get_string('passworddescription'),
                        'value'       => '',
                        'rules' => array(
                            'required'    => true
                        )
                    ),
                    'login_institution' => array(
                        'type' => 'select',
                        'title' => get_string('institution') . ':',
                        'defaultvalue' => $defaultinstitution,
                        'options' => $institutions,
                        'rules' => array(
                            'required' => true
                        ),
                        'ignore' => count($institutions) == 1
                    ),
                    'chelp' => array(
                        'value' =>  get_help_icon('core', 'login', null, null, null, 'loginbox'), 
                    ),
                )
            ),

            'submit' => array(
                'type'  => 'submit',
                'value' => get_string('login')
            ),
            'register' => array(
                'value' => '<div><a href="' . get_config('wwwroot') . 'register.php" tabindex="2">' . get_string('register') . '</a>'
                    . '<br><a href="' . get_config('wwwroot') . 'forgotpass.php" tabindex="2">' . get_string('passwordreminder') . '</a></div>'
            )
        )
    )));
    $pagename = 'loggedouthome';
}
else {
    $pagename = 'home';
}

$smarty = smarty();
if (!$USER->is_logged_in()) {
    $smarty->assign('login_form', $loginform);
}
$smarty->assign('page_content', get_site_page_content($pagename));
$smarty->display('index.tpl');

?>

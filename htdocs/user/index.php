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
define('MENUITEM', 'groups/myfriends');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'user');
define('SECTION_PAGE', 'friends');

require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('myfriends'));

// Generate a form for controlling the friendscontrol setting for users
require_once('pieforms/pieform.php');
$form = array(
    'name' => 'friendscontrol',
    'jsform'      => true,
    'plugintype'  => 'core',
    'pluginname'  => 'account',
    'autofocus'   => false,
    'elements' => array(
        'friendscontrol' => array(
            'type' => 'radio',
            'defaultvalue' => $USER->get_account_preference('friendscontrol'), 
            'separator' => HTML_BR,
            'options' => array(
                'nobody' => get_string('friendsnobody', 'account'),
                'auth'   => get_string('friendsauth', 'account'),
                'auto'   => get_string('friendsauto', 'account')
            ),
           'rules' => array(
                'required' => true
            ),
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('save')
        ),
    )
);

// Make a sideblock to put the friendscontrol block in
$sideblock = array(
    'name' => 'friendscontrol',
    'weight' => -5,
    'data' => pieform($form)
);
function friendscontrol_submit(Pieform $form, $values) {
    global $USER;
    $USER->set_account_preference('friendscontrol', $values['friendscontrol']);
    $form->json_reply(PIEFORM_OK, 'Updated friend control setting successfully');
}

$smarty = smarty(array('mahara', 'tablerenderer', 'friends'), array(), array(), array('sideblocks' => array($sideblock)));
$smarty->display('user/index.tpl');

?>

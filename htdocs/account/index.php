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
define('MENUITEM', 'account');
define('SUBMENUITEM', 'accountprefs');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('form.php');

// load up user preferences
$prefs = (object)($SESSION->get('accountprefs'));

$prefsform = array(
    'name'        => 'accountprefs',
    'method'      => 'post',
    'ajaxpost'    => true,
    'plugintype'  => 'core',
    'pluginname'  => 'account',
    'elements'    => array(
        'friendscontrol' => array(
            'type' => 'radio',
            'defaultvalue' => $prefs->friendscontrol, 
            'title'  => get_string('friendsdescr', 'account'),
            'separator' => HTML_BR,
            'options' => array(
                'nobody' => get_string('friendsnobody', 'account'),
                'auth'   => get_string('friendsauth', 'account'),
                'auto'   => get_string('friendsauto', 'account')
            ),
           'rules' => array(
                'required' => true
            ),
            'help' => true
        ),
        'wysiwyg' => array(
            'type' => 'radio',
            'defaultvalue' => $prefs->wysiwyg,
            'title' => get_string('wysiwygdescr', 'account'),
            'options' => array(
                1 => get_string('on', 'account'),
                0 => get_string('off', 'account'),
            ),
           'rules' => array(
                'required' => true
            )
        ),
        'messages' => array(
            'type' => 'radio',
            'defaultvalue' => $prefs->messages,
            'title' => get_string('messagesdescr', 'account'),
            'separator' => HTML_BR,
            'options' => array(
                'nobody' => get_string('messagesnobody', 'account'),
                'friends' => get_string('messagesfriends', 'account'),
                'allow' => get_string('messagesallow', 'account'),
            ),
           'rules' => array(
                'required' => true
            )
        ),
        'lang' => array(
            'type' => 'select',
            'defaultvalue' => $prefs->lang,
            'title' => get_string('language', 'account'),
            'options' => get_languages(),
            'rules' => array(
                'required' => true
            )
        ),                        
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('save'),
        ),
    ),
);


$smarty = smarty();
$smarty->assign('form', form($prefsform));
$smarty->display('account/index.tpl');

function accountprefs_submit($values) {
    global $SESSION;
    // use this as looping through values is not safe.
    $expectedprefs = expected_account_preferences(); 
    foreach (array_keys($expectedprefs) as $pref) {
        $SESSION->set_account_preference($pref, $values[$pref]);
    }
    json_reply(false, get_string('prefssaved', 'account'));
    exit;
}


?>

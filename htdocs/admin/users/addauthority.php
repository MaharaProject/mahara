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
 * @subpackage admin
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
define('ADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('institutions', 'admin'));
require_once('pieforms/pieform.php');
$smarty = smarty();

// CHECK FOR CANCEL BEFORE THE 'REQUIRED' PARAMS:
$cancel = param_boolean('c');

if ($cancel) {
    execute_javascript_and_close();
}

// NOT CANCELLING? OK - OTHER PARAMS THEN:
$institution = param_variable('i');
$authority   = param_variable('a');
$add         = param_boolean('add');
$edit        = param_boolean('edit');
$json        = param_boolean('j');
$instanceid  = param_variable('id', 0);

// IF WE'RE EDITING OR CREATING AN AUTHORITY:
if ($institution && $authority && ($add || ($edit && $instanceid))) {
    $authclassname = 'PluginAuth' . ucfirst(strtolower($authority));
    safe_require('auth', strtolower($authority));

    $has_config = call_static_method($authclassname, 'has_config');
    if (false == $has_config && $add) {
        $authinstance = new stdClass();

        // Get the auth instance with the highest priority number (which is
        // the instance with the lowest priority).
        // TODO: rethink 'priority' as a fieldname... it's backwards!!
        $lastinstance = get_records_array('auth_instance', 'institution', $institution, 'priority DESC', '*', '0', '1');

        if ($lastinstance == false) {
            $authinstance->priority = 0;
        } else {
            $authinstance->priority = $lastinstance[0]->priority + 1;
        }

        $authinstance->instancename = $authority;
        $authinstance->institution  = $institution;
        $authinstance->authname     = $authority;
        $authinstance->id           = insert_record('auth_instance', $authinstance, 'id', true);
        json_reply(false, array('id' => $authinstance->id, 'name' => ucfirst($authinstance->authname), 'authname' => $authinstance->authname));
        exit;
    }

    $form = call_static_method($authclassname, 'get_config_options', $institution, $instanceid);
    $form['name'] = 'auth_config';
    $form['plugintype'] = 'auth';
    $form['pluginname'] = strtolower($authority);

    $form['elements']['submit'] = array(
        'type' => 'submitcancel',
        'value' => array(get_string('submit'), get_string('cancel')),
        'goto'  => 'addauthority.php?c=1'
    );

    if ($add) {
        $smarty->assign('PAGETITLE', get_string('addauthority', 'auth'));
    } else {
        $smarty->assign('PAGETITLE', get_string('editauthority', 'auth'));
    }
    $smarty->assign('auth_imap_form', pieform($form));
    $smarty->display('admin/users/addauthority.tpl');
    exit;
}

function auth_config_submit(Pieform $form, $values) {
    $authority = $values['authname'];
    $authclassname = 'PluginAuth' . ucfirst(strtolower($authority));
    safe_require('auth', strtolower($authority));
    $values = call_static_method($authclassname, 'save_config_options', $values);
    if($values['create']) {
        execute_javascript_and_close('window.opener.addAuthority('.$values['instance'].', "'.addslashes($values['instancename']).'", "'.$values['authname'].'");');
    } else {
        execute_javascript_and_close();
    }
    exit;
}

// TODO: move to lib if people want this:
function execute_javascript_and_close($js='') {
    echo '<html>
    <head>
        <title>You may close this window</title>
        <script language="Javascript">
            function closeMe() { 
                '.$js.'
                window.close();
            }
        </script>
    </head>
    <body onLoad="closeMe();" style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; text-align: center;">This window should close automatically</body>'.
    "\n</html>";
    exit;
}

?>
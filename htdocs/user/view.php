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
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
require(dirname(dirname(__FILE__)).'/init.php');

$userid = param_integer('id','');

$profile = array();
if (!$user = get_record('usr', 'id', $userid)) {
    $name = get_string('usernotfound');
}
else {
    $name = display_name($user);
    safe_require('artefact', 'internal');
    $publicfields = call_static_method(generate_artefact_class_name('profile'),'get_public_fields');
    foreach (array_keys($publicfields) as $field) {
        $classname = generate_artefact_class_name($field);
        if ($field == 'email') {  // There may be multiple email records
            if ($emails = get_records_array('artefact_internal_profile_email', 'owner', $userid)) {
                foreach ($emails as $email) {
                    $fieldname = $email->principal ? 'principalemailaddress' : 'emailaddress';
                    $profile[] = array('name' => $fieldname, 'value' => $email->email);
                }
            }
        }
        else {
            $c = new $classname(0, array('owner' => $userid)); // email is different
            if ($value = $c->render(ARTEFACT_FORMAT_LISTITEM, null)) {
                $profile[] = array('name' => $field, 'value' => $value);
            }
        }
    }
}

$smarty = smarty();
$smarty->assign('searchform',searchform());
$smarty->assign('NAME',$name);
$smarty->assign('PROFILE',$profile);
$smarty->display('user/view.tpl');

?>

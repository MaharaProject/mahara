<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage artefact-internal
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'internal');
define('TITLE', get_string('emailactivation','artefact.internal'));

$email = param_variable('email');
$key   = param_variable('key');

$row = get_record('artefact_internal_profile_email', 'email', $email, 'key', $key, null,null,'owner,artefact,email,verified,' . db_format_tsfield('expiry'));

$smarty = smarty();

if ($row && $row->expiry > time()) {
    if ($row->artefact) {
        $artefact = new ArtefactTypeEmail($row->artefact);
    }
    else {
        $artefact = new ArtefactTypeEmail();
    }

    $artefact->set('owner', $row->owner);
    $artefact->set('title', $row->email);
    $artefact->commit();

    update_record(
        'artefact_internal_profile_email',
        (object)array(
            'verified' => 1,
            'key'      => null,
            'expiry'   => null,
            'artefact' => $artefact->get('id'),
        ),
        (object)array(
            'owner' => $row->owner,
            'email' => $row->email,
        )
    );

    set_cookie('validated_email', $row->email);
    $smarty->assign('message', get_string('emailactivationsucceeded', 'artefact.internal'));
}
else {
    $smarty->assign('message', get_string('emailactivationfailed', 'artefact.internal'));
}

$smarty->display('artefact:internal:validate.tpl');

?>

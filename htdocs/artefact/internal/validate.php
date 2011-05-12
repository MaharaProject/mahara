<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'internal');
define('SECTION_PAGE', 'validate');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'internal');
define('TITLE', get_string('emailactivation','artefact.internal'));

$email = param_variable('email');
$key   = param_variable('key');
$decline = param_boolean('decline');

$row = get_record('artefact_internal_profile_email', 'email', $email, 'key', $key, null,null,'owner,artefact,email,verified,' . db_format_tsfield('expiry'));

if ($row) {
    if ($decline) {
        delete_records_select('artefact_internal_profile_email', 'verified=0 AND key=? AND email=?', array($key, $email));
        $SESSION->add_ok_msg(get_string('emailactivationdeclined', 'artefact.internal'));
        redirect(get_config('wwwroot'));
    }
    if ($row->expiry > time()) {
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
        $SESSION->add_ok_msg(get_string('emailactivationsucceeded', 'artefact.internal'));
        redirect(get_config('wwwroot') . 'artefact/internal/index.php?fs=contact');
    }
    else {
        $message = get_string('verificationlinkexpired', 'artefact.internal');
    }
}
else if (get_record('artefact_internal_profile_email', 'email', $email, 'verified', 1)) {
    $message = get_string('emailalreadyactivated', 'artefact.internal');
}
else {
    $message = get_string('emailactivationfailed', 'artefact.internal');
}

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('message', $message);
$smarty->display('artefact:internal:validate.tpl');

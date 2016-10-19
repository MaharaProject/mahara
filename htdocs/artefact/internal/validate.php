<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'internal');
define('SECTION_PAGE', 'validate');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'internal');
define('TITLE', get_string('emailactivation','artefact.internal'));

$email = param_variable('email');
$key   = param_variable('key');
$decline = param_boolean('decline');

$row = get_record('artefact_internal_profile_email', 'email', $email, 'key', $key, null,null,'owner,artefact,email,verified,' . db_format_tsfield('expiry'));

if ($row) {
    if ($decline) {
        delete_records_select('artefact_internal_profile_email', 'verified = 0 AND "key" = ? AND email = ?', array($key, $email));
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
        // Update user's email if this email address is primary (principal == 1)
        if (record_exists('artefact_internal_profile_email', 'owner', $row->owner, 'email', $row->email, 'principal', 1)) {
            update_record(
                'usr',
                (object)array(
                    'email' => $row->email,
                ),
                (object)array(
                    'id' => $row->owner,
                )
            );
            redirect(get_config('wwwroot'));
        }
        else {
            redirect(get_config('wwwroot') . 'artefact/internal/index.php?fs=contact');
        }
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
$smarty->assign('message', $message);
$smarty->display('artefact:internal:validate.tpl');

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
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configsite/siteoptions');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'siteoptions');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
require_once('searchlib.php');
define('TITLE', get_string('siteoptions', 'admin'));

$langoptions = get_languages();
$themeoptions = get_themes();
$yesno = array(true  => get_string('yes'),
               false => get_string('no'));

$searchpluginoptions = get_search_plugins();

$siteoptionform = array(
    'name'       => 'siteoptions',
    'jsform'     => true,
    'renderer'   => 'table',
    'plugintype' => 'core',
    'pluginname' => 'admin',
    'jssuccesscallback' => 'checkReload',
    'elements'   => array(
        'sitename' => array(
            'type'         => 'text',
            'title'        => get_string('sitename', 'admin'),
            'description'  => get_string('sitenamedescription', 'admin'),
            'defaultvalue' => get_config('sitename'),
            'help'         => true,
        ),
        'lang' => array(
            'type'         => 'select',
            'title'        => get_string('language', 'admin'),
            'description'  => get_string('sitelanguagedescription', 'admin'),
            'defaultvalue' => get_config('lang'),
            'collapseifoneoption' => true,
            'options'      => $langoptions,
            'help'         => true,
        ),
        'country' => array(
            'type'         => 'select',
            'title'        => get_string('country', 'admin'),
            'description'  => get_string('sitecountrydescription', 'admin'),
            'defaultvalue' => 'nz',
            'options'      => getoptions_country(),
            'help'         => true,
        ),
        'theme' => array(
            'type'         => 'select',
            'title'        => get_string('theme'),
            'description'  => get_string('sitethemedescription', 'admin'),
            'defaultvalue' => get_config('theme'),
            'collapseifoneoption' => true,
            'options'      => $themeoptions,
            'help'         => true,
        ),
        'searchplugin' => array(
            'type'         => 'select',
            'title'        => get_string('searchplugin', 'admin'),
            'description'  => get_string('searchplugindescription', 'admin'),
            'defaultvalue' => get_config('searchplugin'),
            'collapseifoneoption' => true,
            'options'      => $searchpluginoptions,
            'help'         => true,
        ),
        'viruschecking' => array(
            'type'         => 'checkbox',
            'title'        => get_string('viruschecking', 'admin'),
            'description'  => get_string('viruscheckingdescription', 'admin'),
            'defaultvalue' => get_config('viruschecking'),
            'help'         => true,
        ),
        'pathtoclam' => array(
            'type'         => 'text',
            'title'        => get_string('pathtoclam', 'admin'),
            'description'  => get_string('pathtoclamdescription', 'admin'),
            'defaultvalue' => get_config('pathtoclam'),
            'help'         => true,
        ),
        'sessionlifetime' => array(
            'type'         => 'text',
            'size'         => 4,
            'title'        => get_string('sessionlifetime', 'admin'),
            'description'  => get_string('sessionlifetimedescription', 'admin'),
            'defaultvalue' => get_config('session_timeout') / 60,
            'rules'        => array('integer' => true, 'minvalue' => 1, 'maxvalue' => 10000000),
            'help'         => true,
        ),
        'allowpublicviews' => array(
            'type'         => 'select',
            'title'        => get_string('allowpublicviews', 'admin'),
            'description'  => get_string('allowpublicviewsdescription', 'admin'),
            'defaultvalue' => get_config('allowpublicviews'),
            'options'      => $yesno,
            'help'         => true,
        ),
        'allowpublicprofiles' => array(
            'type'         => 'select',
            'title'        => get_string('allowpublicprofiles', 'admin'),
            'description'  => get_string('allowpublicprofilesdescription', 'admin'),
            'defaultvalue' => get_config('allowpublicprofiles'),
            'options'      => $yesno,
            'help'         => true,
        ),
        'creategroups' => array(
            'type'         => 'select',
            'title'        => get_string('whocancreategroups', 'admin'),
            'description'  => get_string('whocancreategroupsdescription', 'admin'),
            'defaultvalue' => get_config('creategroups'),
            'options'      => array(
                'admins' => get_string('adminsonly', 'admin'),
                'staff'  => get_string('adminsandstaffonly', 'admin'),
                'all'    => get_string('Everyone', 'admin'),
            ),
        ),
        'createpublicgroups' => array(
            'type'         => 'select',
            'title'        => get_string('whocancreatepublicgroups', 'admin'),
            'description'  => get_string('whocancreatepublicgroupsdescription', 'admin'),
            'defaultvalue' => get_config('createpublicgroups'),
            'options'      => array(
                'admins' => get_string('adminsonly', 'admin'),
                'all' => get_string('Everyone', 'admin'),
            ),
            'help'         => true,
        ),
        'defaultaccountlifetime' => array(
            'type'         => 'expiry',
            'title'        => get_string('defaultaccountlifetime', 'admin'),
            'description'  => get_string('defaultaccountlifetimedescription', 'admin'),
            'defaultvalue' => get_config('defaultaccountlifetime'),
            'help'         => true,
        ),
        'defaultaccountinactiveexpire' => array(
            'type'         => 'expiry',
            'title'        => get_string('defaultaccountinactiveexpire', 'admin'),
            'description'  => get_string('defaultaccountinactiveexpiredescription', 'admin'),
            'defaultvalue' => get_config('defaultaccountinactiveexpire'),
            'help'         => true,
        ),
        'defaultaccountinactivewarn' => array(
            'type'         => 'expiry',
            'title'        => get_string('defaultaccountinactivewarn', 'admin'),
            'description'  => get_string('defaultaccountinactivewarndescription', 'admin'),
            'defaultvalue' => get_config('defaultaccountinactivewarn'),
            'help'         => true,
        ),
        'usersallowedmultipleinstitutions' => array(
            'type'         => 'checkbox',
            'title'        => get_string('usersallowedmultipleinstitutions', 'admin'),
            'description'  => get_string('usersallowedmultipleinstitutionsdescription', 'admin'),
            'defaultvalue' => get_config('usersallowedmultipleinstitutions'),
            'help'         => true,
        ),
        'registration_sendweeklyupdates' => array(
            'type'         => 'checkbox',
            'title'        => get_string('sendweeklyupdates', 'admin'),
            'description'  => get_string('sendweeklyupdatesdescription', 'admin'),
            'defaultvalue' => get_config('registration_sendweeklyupdates'),
            'help'         => true,
        ),
        'institutionexpirynotification' => array(
            'type'         => 'expiry',
            'title'        => get_string('institutionexpirynotification', 'admin'),
            'description'  => get_string('institutionexpirynotificationdescription', 'admin'),
            'defaultvalue' => is_null(get_config('institutionexpirynotification')) ? '2592000' : get_config('institutionexpirynotification'),
            'help'         => true,
        ),
        'institutionautosuspend' => array(
            'type'         => 'checkbox',
            'title'        => get_string('institutionautosuspend', 'admin'),
            'description'  => get_string('institutionautosuspenddescription', 'admin'),
            'defaultvalue' => get_config('institutionautosuspend'),
            'help'         => true,
        ),
        'captchaonregisterform' => array(
            'type'         => 'checkbox',
            'title'        => get_string('captchaonregisterform', 'admin'),
            'description'  => get_string('captchaonregisterformdescription', 'admin'),
            'defaultvalue' => get_config('captchaonregisterform'),
        ),
        'captchaoncontactform' => array(
            'type'         => 'checkbox',
            'title'        => get_string('captchaoncontactform', 'admin'),
            'description'  => get_string('captchaoncontactformdescription', 'admin'),
            'defaultvalue' => get_config('captchaoncontactform'),
        ),
        'showselfsearchsideblock' => array(
            'type'         => 'checkbox',
            'title'        => get_string('showselfsearchsideblock', 'admin'),
            'description'  => get_string('showselfsearchsideblockdescription', 'admin'),
            'defaultvalue' => get_config('showselfsearchsideblock'),
        ),
        'showtagssideblock' => array(
            'type'         => 'checkbox',
            'title'        => get_string('showtagssideblock', 'admin'),
            'description'  => get_string('showtagssideblockdescription', 'admin'),
            'defaultvalue' => get_config('showtagssideblock'),
        ),
        'tagssideblockmaxtags' => array(
            'type'         => 'text',
            'size'         => 4,
            'title'        => get_string('tagssideblockmaxtags', 'admin'),
            'description'  => get_string('tagssideblockmaxtagsdescription', 'admin'),
            'defaultvalue' => get_config('tagssideblockmaxtags'),
            'rules'        => array('integer' => true, 'minvalue' => 0, 'maxvalue' => 1000),
        ),
    )
);

$siteoptionform['elements']['submit'] = array(
    'type'  => 'submit',
    'value' => get_string('updatesiteoptions', 'admin')
);

$siteoptionform = pieform($siteoptionform);

function siteoptions_fail(Pieform $form, $field) {
    $form->reply(PIEFORM_ERR, array(
        'message' => get_string('setsiteoptionsfailed', 'admin', get_string($field, 'admin')),
        'goto'    => '/admin/site/options.php',
    ));
}

function siteoptions_submit(Pieform $form, $values) {
    $fields = array(
        'sitename','lang','theme', 'pathtoclam',
        'defaultaccountlifetime', 'defaultaccountinactiveexpire', 'defaultaccountinactivewarn',
        'allowpublicviews', 'allowpublicprofiles', 'creategroups', 'createpublicgroups', 'searchplugin',
        'registration_sendweeklyupdates', 'institutionexpirynotification', 'institutionautosuspend',
        'captchaonregisterform', 'captchaoncontactform', 'showselfsearchsideblock', 'showtagssideblock',
        'tagssideblockmaxtags'
    );
    $oldlanguage = get_config('lang');
    $oldtheme = get_config('theme');
    foreach ($fields as $field) {
        if (!set_config($field, $values[$field])) {
            siteoptions_fail($form, $field);
        }
    }
    if ($oldlanguage != $values['lang']) {
        safe_require('artefact', 'file');
        ArtefactTypeFolder::change_public_folder_name($oldlanguage, $values['lang']);
    }
    
    // submitted sessionlifetime is in minutes; db entry session_timeout is in seconds
    if (!set_config('session_timeout', $values['sessionlifetime'] * 60)) {
        siteoptions_fail($form, 'sessionlifetime');
    }
    // Submitted value is on/off; database entry should be 1/0
    foreach(array('viruschecking', 'usersallowedmultipleinstitutions') as $checkbox) {
        if (!set_config($checkbox, (int) ($values[$checkbox] == 'on'))) {
            siteoptions_fail($form, $checkbox);
        }
    }

    if ($values['viruschecking'] == 'on') {
        $pathtoclam = escapeshellcmd(trim(get_config('pathtoclam')));
        if (!$pathtoclam || !file_exists($pathtoclam) && !is_executable($pathtoclam)) {
            $form->reply(PIEFORM_ERR, array(
                'message' => get_string('clamlost', 'mahara', $pathtoclam),
                'goto'    => '/admin/site/options.php',
            ));
        }
    }

    $message = get_string('siteoptionsset', 'admin');
    if ($oldtheme != $values['theme']) {
        global $USER;
        $message .= '  ' . get_string('usersseenewthemeonlogin', 'admin');
        $USER->update_theme();
    }
    $form->reply(PIEFORM_OK, array('message' => $message, 'goto' => '/admin/site/options.php'));
}

$thispage = json_encode(get_config('wwwroot') . 'admin/site/options.php');
$smarty = smarty(array('adminsiteoptions'));
$smarty->assign('siteoptionform', $siteoptionform);
$smarty->assign('PAGEHEADING', hsc(get_string('siteoptions', 'admin')));
$smarty->display('admin/site/options.tpl');

?>

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
require_once('antispam.php');
define('TITLE', get_string('siteoptions', 'admin'));

$langoptions = get_languages();
$themeoptions = get_all_themes();
$yesno = array(true  => get_string('yes'),
               false => get_string('no'));

$searchpluginoptions = get_search_plugins();

$countries = getoptions_country();

$spamtraps = available_spam_traps();

$siteoptionform = array(
    'name'       => 'siteoptions',
    'jsform'     => true,
    'renderer'   => 'table',
    'plugintype' => 'core',
    'pluginname' => 'admin',
    'jssuccesscallback' => 'checkReload',
    'elements'   => array(
        'sitesettings' => array(
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => true,
            'legend'       => get_string('sitesettingslegend', 'admin'),
            'elements'     => array(
                'sitename' => array(
                    'type'         => 'text',
                    'title'        => get_string('sitename', 'admin'),
                    'description'  => get_string('sitenamedescription', 'admin'),
                    'defaultvalue' => get_config('sitename'),
                    'help'         => true,
                    'disabled'     => in_array('sitename', $OVERRIDDEN),
                ),
                'lang' => array(
                    'type'         => 'select',
                    'title'        => get_string('language', 'admin'),
                    'description'  => get_string('sitelanguagedescription', 'admin'),
                    'defaultvalue' => get_config('lang'),
                    'collapseifoneoption' => true,
                    'options'      => $langoptions,
                    'help'         => true,
                    'disabled'     => in_array('lang', $OVERRIDDEN),
                ),
                'country' => array(
                    'type'         => 'select',
                    'title'        => get_string('country', 'admin'),
                    'description'  => get_string('sitecountrydescription', 'admin'),
                    'defaultvalue' => get_config('country'),
                    'options'      => array('' => get_string('nocountryselected')) + $countries,
                    'help'         => true,
                    'disabled'     => in_array('country', $OVERRIDDEN),
                ),
                'theme' => array(
                    'type'         => 'select',
                    'title'        => get_string('theme'),
                    'description'  => get_string('sitethemedescription', 'admin'),
                    'defaultvalue' => get_config('theme'),
                    'collapseifoneoption' => true,
                    'options'      => $themeoptions,
                    'help'         => true,
                    'disabled'     => in_array('theme', $OVERRIDDEN),
                ),
                'homepageinfo' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('homepageinfo', 'admin'),
                    'description'  => get_string('homepageinfodescription', 'admin'),
                    'defaultvalue' => get_config('homepageinfo'),
                    'disabled'     => in_array('homepageinfo', $OVERRIDDEN),
                ),
                'registration_sendweeklyupdates' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('sendweeklyupdates', 'admin'),
                    'description'  => get_string('sendweeklyupdatesdescription', 'admin'),
                    'defaultvalue' => get_config('registration_sendweeklyupdates'),
                    'help'         => true,
                    'disabled'     => in_array('registration_sendweeklyupdates', $OVERRIDDEN),
                ),
            ),
        ),
        'usersettings' => array(
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => true,
            'legend'       => get_string('usersettingslegend', 'admin'),
            'elements'     => array(
                'userscanchooseviewthemes' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('userscanchooseviewthemes', 'admin'),
                    'description'  => get_string('userscanchooseviewthemesdescription', 'admin'),
                    'defaultvalue' => get_config('userscanchooseviewthemes'),
                    'disabled'     => in_array('userscanchooseviewthemes', $OVERRIDDEN),
                ),
                'remoteavatars' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('remoteavatars', 'admin'),
                    'description'  => get_string('remoteavatarsdescription', 'admin'),
                    'defaultvalue' => get_config('remoteavatars'),
                    'help'         => true,
                    'disabled'     => in_array('remoteavatars', $OVERRIDDEN),
                ),
                'userscanhiderealnames' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('userscanhiderealnames', 'admin'),
                    'description'  => get_string('userscanhiderealnamesdescription', 'admin'),
                    'defaultvalue' => get_config('userscanhiderealnames'),
                    'disabled'     => in_array('userscanhiderealnames', $OVERRIDDEN),
                ),
                'searchusernames' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('searchusernames', 'admin'),
                    'description'  => get_string('searchusernamesdescription', 'admin'),
                    'defaultvalue' => get_config('searchusernames'),
                    'disabled'     => in_array('searchusernames', $OVERRIDDEN),
                ),
                'anonymouscomments' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('anonymouscomments', 'admin'),
                    'description'  => get_string('anonymouscommentsdescription', 'admin'),
                    'defaultvalue' => get_config('anonymouscomments'),
                    'disabled'     => in_array('anonymouscomments', $OVERRIDDEN),
                ),
            ),
        ),
        'searchsettings' => array(
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => true,
            'legend'       => get_string('searchsettingslegend', 'admin'),
            'elements'     => array(
                'searchplugin' => array(
                    'type'         => 'select',
                    'title'        => get_string('searchplugin', 'admin'),
                    'description'  => get_string('searchplugindescription', 'admin'),
                    'defaultvalue' => get_config('searchplugin'),
                    'collapseifoneoption' => true,
                    'options'      => $searchpluginoptions,
                    'help'         => true,
                    'disabled'     => in_array('searchplugin', $OVERRIDDEN),
                ),
            ),
        ),
        'groupsettings' => array(
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => true,
            'legend'       => get_string('groupsettingslegend', 'admin'),
            'elements'     => array(
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
                'allowgroupcategories' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('allowgroupcategories', 'admin'),
                    'description'  => get_string('allowgroupcategoriesdescription', 'admin'),
                    'defaultvalue' => get_config('allowgroupcategories'),
                ),
            ),
        ),
        'institutionsettings' => array(
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => true,
            'legend'       => get_string('institutionsettingslegend', 'admin'),
            'elements'     => array(
                'usersallowedmultipleinstitutions' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('usersallowedmultipleinstitutions', 'admin'),
                    'description'  => get_string('usersallowedmultipleinstitutionsdescription', 'admin'),
                    'defaultvalue' => get_config('usersallowedmultipleinstitutions'),
                    'help'         => true,
                    'disabled'     => in_array('usersallowedmultipleinstitutions', $OVERRIDDEN),
                ),
                'institutionexpirynotification' => array(
                    'type'         => 'expiry',
                    'title'        => get_string('institutionexpirynotification', 'admin'),
                    'description'  => get_string('institutionexpirynotificationdescription', 'admin'),
                    'defaultvalue' => get_config('institutionexpirynotification'),
                    'help'         => true,
                    'disabled'     => in_array('institutionexpirynotification', $OVERRIDDEN),
                ),
                'institutionautosuspend' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('institutionautosuspend', 'admin'),
                    'description'  => get_string('institutionautosuspenddescription', 'admin'),
                    'defaultvalue' => get_config('institutionautosuspend'),
                    'help'         => true,
                    'disabled'     => in_array('institutionautosuspend', $OVERRIDDEN),
                ),
            ),
        ),
        'accountsettings' => array(
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => true,
            'legend'       => get_string('accountsettingslegend', 'admin'),
            'elements'     => array(
                'sessionlifetime' => array(
                    'type'         => 'text',
                    'size'         => 4,
                    'title'        => get_string('sessionlifetime', 'admin'),
                    'description'  => get_string('sessionlifetimedescription', 'admin'),
                    'defaultvalue' => get_config('session_timeout') / 60,
                    'rules'        => array('integer' => true, 'minvalue' => 1, 'maxvalue' => 10000000),
                    'help'         => true,
                    'disabled'     => in_array('session_timeout', $OVERRIDDEN),
                ),
                'defaultaccountlifetime' => array(
                    'type'         => 'expiry',
                    'title'        => get_string('defaultaccountlifetime', 'admin'),
                    'description'  => get_string('defaultaccountlifetimedescription', 'admin'),
                    'defaultvalue' => get_config('defaultaccountlifetime'),
                    'help'         => true,
                    'disabled'     => in_array('defaultaccountlifetime', $OVERRIDDEN),
                ),
                'defaultaccountinactiveexpire' => array(
                    'type'         => 'expiry',
                    'title'        => get_string('defaultaccountinactiveexpire', 'admin'),
                    'description'  => get_string('defaultaccountinactiveexpiredescription', 'admin'),
                    'defaultvalue' => get_config('defaultaccountinactiveexpire'),
                    'help'         => true,
                    'disabled'     => in_array('defaultaccountinactiveexpire', $OVERRIDDEN),
                ),
                'defaultaccountinactivewarn' => array(
                    'type'         => 'expiry',
                    'title'        => get_string('defaultaccountinactivewarn', 'admin'),
                    'description'  => get_string('defaultaccountinactivewarndescription', 'admin'),
                    'defaultvalue' => get_config('defaultaccountinactivewarn'),
                    'help'         => true,
                    'disabled'     => in_array('defaultaccountinactivewarn', $OVERRIDDEN),
                ),
            ),
        ),
        'securitysettings' => array(
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => true,
            'legend'       => get_string('securitysettingslegend', 'admin'),
            'elements'     => array(
                'viruschecking' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('viruschecking', 'admin'),
                    'description'  => get_string('viruscheckingdescription', 'admin'),
                    'defaultvalue' => get_config('viruschecking'),
                    'help'         => true,
                    'disabled'     => in_array('viruschecking', $OVERRIDDEN),
                ),
                'pathtoclam' => array(
                    'type'         => 'text',
                    'title'        => get_string('pathtoclam', 'admin'),
                    'description'  => get_string('pathtoclamdescription', 'admin'),
                    'defaultvalue' => get_config('pathtoclam'),
                    'help'         => true,
                    'disabled'     => in_array('pathtoclam', $OVERRIDDEN),
                ),
                'antispam' => array(
                    'type'         => 'select',
                    'title'        => get_string('antispam', 'admin'),
                    'description'  => get_string('antispamdescription', 'admin'),
                    'defaultvalue' => get_config('antispam'),
                    'options'      => $spamtraps,
                    'help'         => true,
                    'disabled'     => in_array('antispam', $OVERRIDDEN),
                ),
                'spamhaus' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('spamhaus', 'admin'),
                    'description'  => get_string('spamhausdescription', 'admin'),
                    'defaultvalue' => get_config('spamhaus'),
                    'help'         => true,
                    'disabled'     => in_array('spamhaus', $OVERRIDDEN),
                ),
                'surbl' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('surbl', 'admin'),
                    'description'  => get_string('surbldescription', 'admin'),
                    'defaultvalue' => get_config('surbl'),
                    'help'         => true,
                    'disabled'     => in_array('surbl', $OVERRIDDEN),
                ),
                'disableexternalresources' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('disableexternalresources', 'admin'),
                    'description'  => get_string('disableexternalresourcesdescription', 'admin'),
                    'defaultvalue' => get_config('disableexternalresources'),
                    'help'         => true,
                    'disabled'     => in_array('disableexternalresources', $OVERRIDDEN),
                ),
            ),
        ),
        # TODO: this should become "Network Settings" at some point
        'proxysettings' => array(
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => true,
            'legend'       => get_string('proxysettings', 'admin'),
            'elements'     => array(
                'proxyaddress' => array(
                   'type'          => 'text',
                   'title'         => get_string('proxyaddress', 'admin'),
                   'description'   => get_string('proxyaddressdescription', 'admin'),
                   'defaultvalue'  => get_config('proxyaddress'),
                ),
                'proxyauthmodel' => array(
                    'type'          => 'select',
                    'title'         => get_string('proxyauthmodel', 'admin'),
                    'description'   => get_string('proxyauthmodeldescription', 'admin'),
                    'defaultvalue'  => get_config('proxyauthmodel'),
                    'options'       => array(
                                        '' => get_string('none', 'admin'),
                                        'basic' => get_string('proxyauthmodelbasic', 'admin'),
                                    ),
                ),
                'proxyauthcredentials' => array(
                    'type'          => 'text',
                    'title'         => get_string('proxyauthcredentials', 'admin'),
                    'description'   => get_string('proxyauthcredentialsdescription', 'admin'),
                    'defaultvalue'  => get_config('proxyauthcredentials'),
                ),
            ),
        ),
        'emailsettings' => array(
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => true,
            'legend'       => get_string('emailsettings', 'admin'),
            'elements'     => array(
                'smtphosts' => array(
                   'type'           => 'text',
                   'title'          => get_string('emailsmtphosts', 'admin'),
                   'description'    => get_string('emailsmtphostsdescription', 'admin'),
                   'defaultvalue'   => get_config('smtphosts'),
                   'disabled'       => in_array('smtphosts', $OVERRIDDEN),
                   'help'           => true,
                ),
                'smtpport' => array(
                    'type'          => 'text',
                    'title'         => get_string('emailsmtpport', 'admin'),
                    'description'   => get_string('emailsmtpportdescription', 'admin'),
                    'defaultvalue'  => get_config('smtpport'),
                    'disabled'      => in_array('smtpport', $OVERRIDDEN),
                    'help'          => true,
                ),
                'smtpuser' => array(
                    'type'          => 'text',
                    'title'         => get_string('emailsmtpuser', 'admin'),
                    'description'   => get_string('emailsmtpuserdescription', 'admin'),
                    'defaultvalue'  => get_config('smtpuser'),
                    'disabled'      => in_array('smtpuser', $OVERRIDDEN),
                ),
                'smtppass' => array(
                    'type'          => 'text',
                    'title'         => get_string('emailsmtppass', 'admin'),
                    'defaultvalue'  => get_config('smtppass'),
                    'disabled'      => in_array('smtppass', $OVERRIDDEN),
                ),
                'smtpsecure' => array(
                    'type'          => 'select',
                    'title'         => get_string('emailsmtpsecure', 'admin'),
                    'description'   => get_string('emailsmtpsecuredescription', 'admin'),
                    'defaultvalue'  => get_config('smtpsecure'),
                    'disabled'      => in_array('smtpsecure', $OVERRIDDEN),
                    'options'       => array(
                        '' => get_string('none', 'admin'),
                        'ssl' => get_string('emailsmtpsecuressl', 'admin'),
                        'tls' => get_string('emailsmtpsecuretls', 'admin'),
                        ),
                    'help'          => true,
                ),
                'noreplyaddress' => array(
                    'type'          => 'text',
                    'title'         => get_string('emailnoreplyaddress', 'admin'),
                    'description'   => get_string('emailnoreplyaddressdescription', 'admin'),
                    'defaultvalue'  => get_config('noreplyaddress'),
                    'disabled'      => in_array('noreplyaddress', $OVERRIDDEN),
                    'help'          => true,
                ),
            ),
        ),
        'generalsettings' => array(
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => true,
            'legend'       => get_string('generalsettingslegend', 'admin'),
            'elements'     => array(
                'allowpublicviews' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('allowpublicviews', 'admin'),
                    'description'  => get_string('allowpublicviewsdescription', 'admin'),
                    'defaultvalue' => get_config('allowpublicviews'),
                    'help'         => true,
                    'disabled'     => in_array('allowpublicviews', $OVERRIDDEN),
                ),
                'allowpublicprofiles' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('allowpublicprofiles', 'admin'),
                    'description'  => get_string('allowpublicprofilesdescription', 'admin'),
                    'defaultvalue' => get_config('allowpublicprofiles'),
                    'help'         => true,
                    'disabled'     => in_array('allowpublicprofiles', $OVERRIDDEN),
                ),
                'showselfsearchsideblock' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('showselfsearchsideblock', 'admin'),
                    'description'  => get_string('showselfsearchsideblockdescription', 'admin'),
                    'defaultvalue' => get_config('showselfsearchsideblock'),
                    'disabled'     => in_array('showselfsearchsideblock', $OVERRIDDEN),
                ),
                'showtagssideblock' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('showtagssideblock', 'admin'),
                    'description'  => get_string('showtagssideblockdescription', 'admin'),
                    'defaultvalue' => get_config('showtagssideblock'),
                    'disabled'     => in_array('showtagssideblock', $OVERRIDDEN),
                ),
                'tagssideblockmaxtags' => array(
                    'type'         => 'text',
                    'size'         => 4,
                    'title'        => get_string('tagssideblockmaxtags', 'admin'),
                    'description'  => get_string('tagssideblockmaxtagsdescription', 'admin'),
                    'defaultvalue' => get_config('tagssideblockmaxtags'),
                    'rules'        => array('integer' => true, 'minvalue' => 0, 'maxvalue' => 1000),
                    'disabled'     => in_array('tagssideblockmaxtags', $OVERRIDDEN),
                ),
                'viewmicroheaders' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('smallviewheaders', 'admin'),
                    'description'  => get_string('smallviewheadersdescription', 'admin'),
                    'defaultvalue' => get_config('viewmicroheaders'),
                    'disabled'     => in_array('viewmicroheaders', $OVERRIDDEN),
                ),
                'showonlineuserssideblock' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('showonlineuserssideblock', 'admin'),
                    'description'  => get_string('showonlineuserssideblockdescription', 'admin'),
                    'defaultvalue' => get_config('showonlineuserssideblock'),
                    'disabled'     => in_array('showonlineuserssideblock', $OVERRIDDEN),
                ),
                'registerterms' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('registerterms', 'admin'),
                    'description'  => get_string('registertermsdescription', 'admin'),
                    'defaultvalue' => get_config('registerterms'),
                    'disabled'     => in_array('registerterms', $OVERRIDDEN),
                ),
                'allowmobileuploads' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('allowmobileuploads', 'admin'),
                    'description'  => get_string('allowmobileuploadsdescription', 'admin'),
                    'defaultvalue' => get_config('allowmobileuploads'),
                    'disabled'     => in_array('allowmobileuploads', $OVERRIDDEN),
                ),
                'wysiwyg' => array(
                    'type'         => 'select',
                    'title'        => get_string('wysiwyg', 'admin'),
                    'description'  => get_string('wysiwygdescription', 'admin'),
                    'defaultvalue' => get_config('wysiwyg'),
                    'options'      => array(
                                        '' => get_string('wysiwyguserdefined', 'admin'),
                                        'disable' => get_string('disable'),
                                        'enable' => get_string('enable'),
                                    ),
                    'help'         => true,
                ),
            ),
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
        'allowpublicviews', 'allowpublicprofiles',
        'registration_sendweeklyupdates', 'institutionexpirynotification', 'institutionautosuspend',
        'showselfsearchsideblock', 'searchusernames', 'showtagssideblock',
        'tagssideblockmaxtags', 'country', 'viewmicroheaders', 'userscanchooseviewthemes',
        'remoteavatars', 'userscanhiderealnames', 'antispam', 'spamhaus', 'surbl', 'anonymouscomments', 'disableexternalresources',
        'proxyaddress', 'proxyauthmodel', 'proxyauthcredentials', 'smtphosts', 'smtpport', 'smtpuser', 'smtppass', 'smtpsecure',
        'noreplyaddress', 'homepageinfo', 'showonlineuserssideblock', 'registerterms', 'allowmobileuploads',
        'creategroups', 'createpublicgroups', 'allowgroupcategories', 'wysiwyg',
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
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('admin/site/options.tpl');

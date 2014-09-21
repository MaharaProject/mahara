<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
require_once(get_config('libroot') . 'activity.php');
define('TITLE', get_string('siteoptions', 'admin'));

$langoptions = get_languages();
$themeoptions = get_all_themes();
unset($themeoptions['custom']); // Only available for institution configurable themes

$yesno = array(true  => get_string('yes'),
               false => get_string('no'));

$searchpluginoptions = get_search_plugins();

$countries = getoptions_country();

$notificationelements = get_notification_settings_elements(null, true);

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
                'dropdownmenu' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('dropdownmenu', 'admin'),
                    'description'  => get_string('dropdownmenudescription', 'admin'),
                    'defaultvalue' => get_config('dropdownmenu'),
                    'disabled'     => in_array('dropdownmenu', $OVERRIDDEN),
                ),
                'homepageinfo' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('homepageinfo1', 'admin'),
                    'description'  => get_string('homepageinfodescription1', 'admin'),
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
                'searchuserspublic' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('searchuserspublic', 'admin'),
                    'description'  => get_string('searchuserspublicdescription', 'admin'),
                    'defaultvalue' => get_config('searchuserspublic'),
                    'disabled'     => in_array('searchuserspublic', $OVERRIDDEN),
                ),
                'anonymouscomments' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('anonymouscomments', 'admin'),
                    'description'  => get_string('anonymouscommentsdescription', 'admin'),
                    'defaultvalue' => get_config('anonymouscomments'),
                    'disabled'     => in_array('anonymouscomments', $OVERRIDDEN),
                ),
                'loggedinprofileviewaccess' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('loggedinprofileviewaccess', 'admin'),
                    'description'  => get_string('loggedinprofileviewaccessdescription', 'admin'),
                    'defaultvalue' => get_config('loggedinprofileviewaccess'),
                    'disabled'     => in_array('loggedinprofileviewaccess', $OVERRIDDEN),
                    'help'         => true,
                ),
                'staffreports' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('staffuserreports', 'admin'),
                    'description'  => get_string('staffuserreportsdescription', 'admin'),
                    'defaultvalue' => get_config('staffreports'),
                    'disabled'     => in_array('staffreports', $OVERRIDDEN),
                ),
                'staffstats' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('staffuserstats', 'admin'),
                    'description'  => get_string('staffuserstatsdescription', 'admin'),
                    'defaultvalue' => get_config('staffstats'),
                    'disabled'     => in_array('staffstats', $OVERRIDDEN),
                ),
                'userscandisabledevicedetection' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('userscandisabledevicedetection', 'admin'),
                    'description'  => get_string('userscandisabledevicedetectiondescription', 'admin'),
                    'defaultvalue' => get_config('userscandisabledevicedetection'),
                    'disabled'     => in_array('userscandisabledevicedetection', $OVERRIDDEN),
                ),
                'masqueradingreasonrequired' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('masqueradingreasonrequired', 'admin'),
                    'description'  => get_string('masqueradingreasonrequireddescription1', 'admin'),
                    'defaultvalue' => get_config('masqueradingreasonrequired'),
                    'disabled'     => in_array('masqueradingreasonrequired', $OVERRIDDEN),
                ),
                'masqueradingnotified' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('masqueradingnotified', 'admin'),
                    'description'  => get_string('masqueradingnotifieddescription1', 'admin'),
                    'defaultvalue' => get_config('masqueradingnotified'),
                    'disabled'     => in_array('masqueradingnotified', $OVERRIDDEN),
                ),
                'showprogressbar' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('showprogressbar', 'admin'),
                    'description'  => get_string('showprogressbardescription', 'admin'),
                    'defaultvalue' => get_config('showprogressbar'),
                    'disabled'     => in_array('showprogressbar', $OVERRIDDEN),
                ),
                'exporttoqueue' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('exporttoqueue', 'admin'),
                    'description'  => get_string('exporttoqueuedescription1', 'admin'),
                    'defaultvalue' => get_config('exporttoqueue'),
                    'disabled'     => in_array('exporttoqueue', $OVERRIDDEN),
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
                    'disabled'     => in_array('creategroups', $OVERRIDDEN),
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
                    'disabled'     => in_array('createpublicgroups', $OVERRIDDEN),
                ),
                'allowgroupcategories' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('allowgroupcategories', 'admin'),
                    'description'  => get_string('allowgroupcategoriesdescription', 'admin'),
                    'defaultvalue' => get_config('allowgroupcategories'),
                    'disabled'     => in_array('allowgroupcategories', $OVERRIDDEN),
                ),
            ),
        ),
        'institutionsettings' => array(
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => true,
            'legend'       => get_string('institutionsettingslegend', 'admin'),
            'elements'     => array(
                'requireregistrationconfirm' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('requireregistrationconfirm', 'admin'),
                    'description'  => get_string('requireregistrationconfirmdescription', 'admin'),
                    'defaultvalue' => get_config('requireregistrationconfirm'),
                ),
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
                    'description'  => get_string('institutionexpirynotificationdescriptioninfo', 'admin'),
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
                'defaultregistrationexpirylifetime' => array(
                    'type'         => 'expiry',
                    'title'        => get_string('defaultregistrationexpirylifetime', 'admin'),
                    'description'  => get_string('defaultregistrationexpirylifetimedescription', 'admin'),
                    'defaultvalue' => get_config('defaultregistrationexpirylifetime'),
                    'help'         => false,
                    'disabled'     => in_array('defaultregistrationexpirylifetime', $OVERRIDDEN),
                ),
                'defaultaccountlifetime' => array(
                    'type'         => 'expiry',
                    'title'        => get_string('defaultaccountlifetime', 'admin'),
                    'description'  => get_string('defaultaccountlifetimedescription', 'admin'),
                    'defaultvalue' => get_config('defaultaccountlifetime'),
                    'help'         => true,
                    'disabled'     => in_array('defaultaccountlifetime', $OVERRIDDEN),
                ),
                'defaultaccountlifetimeupdate' => array(
                    'type'         => 'radio',
                    'title'        => get_string('defaultaccountlifetimeupdate', 'admin'),
                    'description'  => get_string('defaultaccountlifetimeupdatedescription', 'admin'),
                    'defaultvalue' => get_config('defaultaccountlifetimeupdate'),
                    'separator'    => '<br>',
                    'options'      => array(
                        'none'     => get_string('defaultaccountlifetimeupdatenone', 'admin'),
                        'some'     => get_string('defaultaccountlifetimeupdatesome', 'admin'),
                        'all'      => get_string('defaultaccountlifetimeupdateall', 'admin')
                    ),
                    'help'         => true,
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
                    'type' => 'html',
                    'title' => get_string('pathtoclam', 'admin'),
                    'description' => get_string('pathtoclamdescription', 'admin'),
                    'value' => (get_config('pathtoclam') ? get_config('pathtoclam') : get_string('pathtoclamnotset', 'admin')),
                    'help' => true,
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
                'recaptchaonregisterform' => array(
                    'type' => 'checkbox',
                    'title' => get_string('recaptchaonregisterform', 'admin'),
                    'description' => get_string('recaptchaonregisterformdesc', 'admin'),
                    'defaultvalue' => get_config('recaptchaonregisterform', 'admin'),
                    'help' => true,
                    'disabled' => in_array('recaptchaonregisterform', $OVERRIDDEN)
                ),
                'recaptchapublickey' => array(
                    'type' => 'text',
                    'title' => get_string('recaptchapublickey', 'admin'),
                    'description' => get_string('recaptchapublickeydesc', 'admin'),
                    'defaultvalue' => get_config('recaptchapublickey'),
                    'disabled' => in_array('recaptchapublickey', $OVERRIDDEN)
                ),
                'recaptchaprivatekey' => array(
                    'type' => 'text',
                    'title' => get_string('recaptchaprivatekey', 'admin'),
                    'description' => get_string('recaptchaprivatekeydesc', 'admin'),
                    'defaultvalue' => get_config('recaptchaprivatekey'),
                    'disabled' => in_array('recaptchaprivatekey', $OVERRIDDEN)
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
                   'disabled'      => in_array('proxyaddress', $OVERRIDDEN),
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
                    'disabled'      => in_array('proxyauthmodel', $OVERRIDDEN),
                ),
                'proxyauthcredentials' => array(
                    'type'          => 'text',
                    'title'         => get_string('proxyauthcredentials', 'admin'),
                    'description'   => get_string('proxyauthcredentialsdescription', 'admin'),
                    'defaultvalue'  => get_config('proxyauthcredentials'),
                    'disabled'      => in_array('proxyauthcredentials', $OVERRIDDEN),
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
        'notificationsettings' => array(
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => true,
            'legend'       => get_string('notificationsettings', 'admin'),
            'elements'     => array_merge(
                array(
                    'activitydescription' => array(
                        'type' => 'html',
                        'class' => 'description',
                        'value' => get_string('notificationsettingsdescription', 'admin'),
                    )
                ),
                $notificationelements),
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
                    'disabled'     => in_array('allowpublicprofiles', $OVERRIDDEN) || get_config('allowpublicviews'),
                ),
                'allowanonymouspages' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('allowanonymouspages', 'admin'),
                    'description'  => get_string('allowanonymouspagesdescription', 'admin'),
                    'defaultvalue' => get_config('allowanonymouspages'),
                    'help'         => true,
                    'disabled'     => in_array('allowanonymouspages', $OVERRIDDEN),
                ),
                'generatesitemap' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('generatesitemap', 'admin'),
                    'description'  => get_string('generatesitemapdescription', 'admin'),
                    'defaultvalue' => get_config('generatesitemap'),
                    'help'         => true,
                    'disabled'     => in_array('generatesitemap', $OVERRIDDEN) || !get_config('allowpublicviews'),
                ),
                'showselfsearchsideblock' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('showselfsearchsideblock', 'admin'),
                    'description'  => get_string('showselfsearchsideblockdescription1', 'admin'),
                    'defaultvalue' => get_config('showselfsearchsideblock'),
                    'disabled'     => in_array('showselfsearchsideblock', $OVERRIDDEN),
                ),
                'showtagssideblock' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('showtagssideblock', 'admin'),
                    'description'  => get_string('showtagssideblockdescription1', 'admin'),
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
                    'description'  => get_string('showonlineuserssideblockdescriptionmessage', 'admin'),
                    'defaultvalue' => get_config('showonlineuserssideblock'),
                    'disabled'     => in_array('showonlineuserssideblock', $OVERRIDDEN),
                ),
                'onlineuserssideblockmaxusers' => array(
                    'type'         => 'text',
                    'size'         => 4,
                    'title'        => get_string('onlineuserssideblockmaxusers', 'admin'),
                    'description'  => get_string('onlineuserssideblockmaxusersdescription', 'admin'),
                    'defaultvalue' => get_config('onlineuserssideblockmaxusers'),
                    'rules'        => array('integer' => true, 'minvalue' => 0, 'maxvalue' => 100),
                    'disabled'     => in_array('onlineuserssideblockmaxusers', $OVERRIDDEN),
                ),
                'registerterms' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('registerterms', 'admin'),
                    'description'  => get_string('registertermsdescription', 'admin'),
                    'defaultvalue' => get_config('registerterms'),
                    'disabled'     => in_array('registerterms', $OVERRIDDEN),
                ),
                'licensemetadata' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('licensemetadata', 'admin'),
                    'description'  => get_string('licensemetadatadescription', 'admin'),
                    'defaultvalue' => get_config('licensemetadata'),
                    'help'         => true,
                    'disabled'     => in_array('licensemetadata', $OVERRIDDEN),
                ),
                'licenseallowcustom' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('licenseallowcustom', 'admin'),
                    'description'  => get_string('licenseallowcustomdescription', 'admin'),
                    'defaultvalue' => get_config('licenseallowcustom'),
                    'disabled'     => in_array('licenseallowcustom', $OVERRIDDEN),
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
                    'disabled'     => in_array('wysiwyg', $OVERRIDDEN),
                ),
                'sitefilesaccess' => array(
                    'type'         => 'checkbox',
                    'title'        => get_string('sitefilesaccess', 'admin'),
                    'description'  => get_string('sitefilesaccessdescription', 'admin'),
                    'defaultvalue' => get_config('sitefilesaccess'),
                    'disabled'     => in_array('sitefilesaccess', $OVERRIDDEN),
                ),
                'watchlistnotification_delay' => array(
                    'type'         => 'text',
                    'title'        => get_string('watchlistdelaytitle', 'admin'),
                    'description'  => get_string('watchlistdelaydescription', 'admin'),
                    'defaultvalue' => get_config('watchlistnotification_delay'),
                    'disabled'     => in_array('watchlistnotification_delay', $OVERRIDDEN),
                ),
            ),
        ),
        'loggingsettings' => array(
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => true,
            'legend'       => get_string('loggingsettingslegend', 'admin'),
            'elements'     => array(
                'eventloglevel' => array(
                    'type'         => 'select',
                    'title'        => get_string('eventloglevel', 'admin'),
                    'description'  => get_string('eventlogleveldescription', 'admin'),
                    'defaultvalue' => get_config('eventloglevel'),
                    'options'      => array(
                        'none' => get_string('eventloglevelnone', 'admin'),
                        'masq' => get_string('eventloglevelmasq', 'admin'),
                        'all' => get_string('eventloglevelall', 'admin'),
                    ),
                    'help'         => true,
                    'disabled'     => in_array('eventloglevel', $OVERRIDDEN),
                ),
                'eventlogexpiry' => array(
                    'type'         => 'expiry',
                    'title'        => get_string('eventlogexpiry', 'admin'),
                    'description'  => get_string('eventlogexpirydescription', 'admin'),
                    'defaultvalue' => get_config('eventlogexpiry'),
                    'help'         => false,
                    'disabled'     => in_array('eventlogexpiry', $OVERRIDDEN),
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
        'sitename','lang','theme', 'dropdownmenu',
        'defaultaccountlifetime', 'defaultregistrationexpirylifetime', 'defaultaccountinactiveexpire', 'defaultaccountinactivewarn',
        'defaultaccountlifetimeupdate', 'allowpublicviews', 'allowpublicprofiles', 'allowanonymouspages', 'generatesitemap',
        'registration_sendweeklyupdates', 'institutionexpirynotification', 'institutionautosuspend', 'requireregistrationconfirm',
        'showselfsearchsideblock', 'searchusernames', 'searchplugin', 'showtagssideblock',
        'tagssideblockmaxtags', 'country', 'viewmicroheaders', 'userscanchooseviewthemes',
        'remoteavatars', 'userscanhiderealnames', 'antispam', 'spamhaus', 'surbl', 'anonymouscomments',
        'recaptchaonregisterform', 'recaptchapublickey', 'recaptchaprivatekey', 'loggedinprofileviewaccess', 'disableexternalresources',
        'proxyaddress', 'proxyauthmodel', 'proxyauthcredentials', 'smtphosts', 'smtpport', 'smtpuser', 'smtppass', 'smtpsecure',
        'noreplyaddress', 'homepageinfo', 'showprogressbar', 'showonlineuserssideblock', 'onlineuserssideblockmaxusers',
        'registerterms', 'licensemetadata', 'licenseallowcustom', 'allowmobileuploads', 'creategroups', 'createpublicgroups', 'allowgroupcategories', 'wysiwyg',
        'staffreports', 'staffstats', 'userscandisabledevicedetection', 'watchlistnotification_delay',
        'masqueradingreasonrequired', 'masqueradingnotified', 'searchuserspublic',
        'eventloglevel', 'eventlogexpiry', 'sitefilesaccess', 'exporttoqueue',
    );
    $count = 0;
    $where_sql = " WHERE admin = 0 AND id != 0";
    // if default account lifetime expiry has no end date
    if (empty($values['defaultaccountlifetime'])) {
        if ($values['defaultaccountlifetimeupdate'] == 'all') {
            // need to remove user expiry
            db_begin();
            $count = count_records_sql("SELECT COUNT(*) FROM {usr} $where_sql");
            execute_sql("UPDATE {usr} SET expiry = NULL $where_sql");
            db_commit();
        }
        else {
            // make the 'some' option the same as 'none' as it is meaningless to
            // update existing users without expiry date to having 'no end date'
            $values['defaultaccountlifetimeupdate'] = 'none';
        }
    }
    else {
        // fetch all the users that are not siteadmins
        $user_expiry = mktime(0, 0, 0, date('n'), date('j'), date('Y')) + (int)$values['defaultaccountlifetime'];
        if ($values['defaultaccountlifetimeupdate'] == 'some') {
            // and the user's expiry is not set
            $where_sql .= " AND expiry IS NULL";
            $count = count_records_sql("SELECT COUNT(*) FROM {usr} $where_sql");
            db_begin();
            execute_sql("UPDATE {usr} SET expiry = ? $where_sql", array(format_date($user_expiry)));
            db_commit();
        }
        else if ($values['defaultaccountlifetimeupdate'] == 'all') {
            // and the user's expiry is set
            db_begin();
            $count = count_records_sql("SELECT COUNT(*) FROM {usr} $where_sql");
            execute_sql("UPDATE {usr} SET expiry = ? $where_sql", array(format_date($user_expiry)));
            db_commit();
        }
    }
    // if public views are disabled, sitemap generation must also be disabled.
    if ($values['allowpublicviews'] == false) {
        $values['generatesitemap'] = false;
    }
    else {
        // Ensure allowpublicprofiles is set as well
        $values['allowpublicprofiles'] = 1;
    }

    $oldsearchplugin = get_config('searchplugin');
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

    save_notification_settings($values, null, true);

    // If they've changed the search plugin, give the new plugin a chance to initialize.
    if ($oldsearchplugin != $values['searchplugin']) {
        // Call the old search plugin's sitewide cleanup method
        safe_require('search', $oldsearchplugin);
        call_static_method(generate_class_name('search', $oldsearchplugin), 'cleanup_sitewide');
        // Call the new search plugin's sitewide initialize method
        safe_require('search', $values['searchplugin']);
        $initialize = call_static_method(generate_class_name('search', $values['searchplugin']), 'initialize_sitewide');
        if (!$initialize) {
            $form->reply(PIEFORM_ERR, array(
                'message' => get_string('searchconfigerror1', 'admin', $values['searchplugin']),
                'goto'    => '/admin/site/options.php',
            ));
        }
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
        if (!$pathtoclam ) {
            $form->reply(PIEFORM_ERR, array(
                'message' => get_string('clamnotset', 'mahara', $pathtoclam),
                'goto'    => '/admin/site/options.php',
            ));
        }
        else if (!file_exists($pathtoclam) && !is_executable($pathtoclam)) {
            $form->reply(PIEFORM_ERR, array(
                'message' => get_string('clamlost', 'mahara', $pathtoclam),
                'goto'    => '/admin/site/options.php',
            ));
        }
    }

    if (get_config('recaptchaonregisterform')
            && !(
                    get_config('recaptchapublickey')
                    && get_config('recaptchaprivatekey')
            )
    ) {
        $form->reply(
            PIEFORM_ERR,
            array(
                'message' => get_string('recaptchakeysmissing', 'admin'),
                'goto' => '/admin/site/options.php',
            )
        );
    }

    $message = get_string('siteoptionsset', 'admin');
    if ($oldtheme != $values['theme']) {
        global $USER;
        $message .= '  ' . get_string('usersseenewthemeonlogin', 'admin');
        $USER->reset_institutions();
    }
    if ($count) {
        $message .= ' ' . get_string('numberusersupdated','admin', $count);
    }
    $form->reply(PIEFORM_OK, array('message' => $message, 'goto' => '/admin/site/options.php'));
}

$js = <<<EOF
jQuery(document).ready(function() {
    var j = jQuery.noConflict();
    var overrideuseraccountlifetime = j('#siteoptions input[name=defaultaccountlifetimeupdate]');
    var defaultaccountlifetime = j('#siteoptions_defaultaccountlifetime_units');
    function overrideuseraccountlife(v) {
        // if default account lifetime is set to 'noenddate' disable the override options
        if (v == 'noenddate') {
            var value = j('#siteoptions input[name=defaultaccountlifetimeupdate]:checked').val();
            j('#siteoptions input[name=defaultaccountlifetimeupdate][value=some]').attr('disabled',true);
            j('#siteoptions input[name=defaultaccountlifetimeupdate][value=some]').attr('checked', false);
            if (value != 'some') {
                j('#siteoptions input[name=defaultaccountlifetimeupdate][value=' + value + ']').attr('checked', true);
            }
        }
        else {
            // enable the radio buttons
            overrideuseraccountlifetime.attr('disabled',false);
            if (!j('#siteoptions input[name=defaultaccountlifetimeupdate]:checked').val()) {
                overrideuseraccountlifetime.each(function() {
                    if (typeof j(this).attr('checked') === 'undefined' || j(this).attr('checked') === false) {
                        // if no radio button already selected then choose the 'only for new users' option by default
                        if (j(this).val() == 'none') {
                            j(this).attr('checked',true);
                        }
                    }
                });
            }
        }
    }
    // when default account lifetime changes rerun the override account lifetime checks
    defaultaccountlifetime.change(function() {
        overrideuseraccountlife(defaultaccountlifetime.val());
    });
    // initial setup
    overrideuseraccountlife(defaultaccountlifetime.val());
});
EOF;

$thispage = json_encode(get_config('wwwroot') . 'admin/site/options.php');
$smarty = smarty(array('adminsiteoptions'));
$smarty->assign('siteoptionform', $siteoptionform);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('admin/site/options.tpl');

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
require_once('searchlib.php');
require_once('antispam.php');
require_once(get_config('libroot') . 'activity.php');
define('TITLE', get_string('siteoptions', 'admin'));

$langoptions = get_languages();
$themeoptions = get_all_themes();
unset($themeoptions['custom']); // Only available for institution configurable themes

$searchpluginoptions = get_search_plugins();

$countries = getoptions_country();

$notificationelements = get_notification_settings_elements(null, true);

validate_theme(get_config('theme'));

$spamtraps = available_spam_traps();
$siteoptionform = array(
    'name'       => 'siteoptions',
    'jsform'     => true,
    'renderer'   => 'div',
    'plugintype' => 'core',
    'pluginname' => 'admin',
    'jssuccesscallback' => 'checkReload',
    'elements'   => array(
        'sitesettings' => array(
            'iconclass' => 'globe',
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
                    'type'         => 'switchbox',
                    'title'        => get_string('dropdownmenu', 'admin'),
                    'description'  => get_string('dropdownmenudescription2', 'admin'),
                    'defaultvalue' => get_config('dropdownmenu'),
                    'disabled'     => in_array('dropdownmenu', $OVERRIDDEN),
                ),
                'homepageinfo' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('homepageinfo1', 'admin'),
                    'description'  => get_string('homepageinfodescription3', 'admin'),
                    'defaultvalue' => get_config('homepageinfo'),
                    'disabled'     => in_array('homepageinfo', $OVERRIDDEN),
                ),
                'registration_sendweeklyupdates' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('sendweeklyupdates', 'admin'),
                    'description'  => get_string('sendweeklyupdatesdescription2', 'admin'),
                    'defaultvalue' => get_config('registration_sendweeklyupdates'),
                    'help'         => true,
                    'disabled'     => in_array('registration_sendweeklyupdates', $OVERRIDDEN),
                ),
            ),
        ),
        'usersettings' => array(
            'iconclass' => 'user',
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => true,
            'legend'       => get_string('usersettingslegend', 'admin'),
            'elements'     => array(
                'userscanchooseviewthemes' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('userscanchooseviewthemes', 'admin'),
                    'description'  => get_string('userscanchooseviewthemesdescription1', 'admin'),
                    'defaultvalue' => get_config('userscanchooseviewthemes'),
                    'disabled'     => in_array('userscanchooseviewthemes', $OVERRIDDEN),
                ),
                'remoteavatars' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('remoteavatars', 'admin'),
                    'description'  => get_string('remoteavatarsdescription1', 'admin'),
                    'defaultvalue' => get_config('remoteavatars'),
                    'help'         => true,
                    'disabled'     => in_array('remoteavatars', $OVERRIDDEN),
                ),
                'userscanhiderealnames' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('userscanhiderealnames', 'admin'),
                    'description'  => get_string('userscanhiderealnamesdescription1', 'admin'),
                    'defaultvalue' => get_config('userscanhiderealnames'),
                    'disabled'     => in_array('userscanhiderealnames', $OVERRIDDEN),
                ),
                'nousernames' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('nousernames', 'admin'),
                    'description'  => get_string('nousernamesdescription1', 'admin'),
                    'defaultvalue' => get_config('nousernames'),
                    'disabled'     => in_array('nousernames', $OVERRIDDEN),
                ),
                'searchuserspublic' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('searchuserspublic', 'admin'),
                    'description'  => get_string('searchuserspublicdescription', 'admin'),
                    'defaultvalue' => get_config('searchuserspublic'),
                    'disabled'     => in_array('searchuserspublic', $OVERRIDDEN),
                ),
                'anonymouscomments' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('anonymouscomments', 'admin'),
                    'description'  => get_string('anonymouscommentsdescription1', 'admin'),
                    'defaultvalue' => get_config('anonymouscomments'),
                    'disabled'     => in_array('anonymouscomments', $OVERRIDDEN),
                ),
                'loggedinprofileviewaccess' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('loggedinprofileviewaccess1', 'admin'),
                    'description'  => get_string('loggedinprofileviewaccessdescription1', 'admin'),
                    'defaultvalue' => get_config('loggedinprofileviewaccess'),
                    'disabled'     => in_array('loggedinprofileviewaccess', $OVERRIDDEN),
                    'help'         => true,
                ),
                'staffreports' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('staffuserreports', 'admin'),
                    'description'  => get_string('staffuserreportsdescription1', 'admin'),
                    'defaultvalue' => get_config('staffreports'),
                    'disabled'     => in_array('staffreports', $OVERRIDDEN),
                ),
                'staffstats' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('staffuserstats', 'admin'),
                    'description'  => get_string('staffuserstatsdescription1', 'admin'),
                    'defaultvalue' => get_config('staffstats'),
                    'disabled'     => in_array('staffstats', $OVERRIDDEN),
                ),
                'userscandisabledevicedetection' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('userscandisabledevicedetection', 'admin'),
                    'description'  => get_string('userscandisabledevicedetectiondescription1', 'admin'),
                    'defaultvalue' => get_config('userscandisabledevicedetection'),
                    'disabled'     => in_array('userscandisabledevicedetection', $OVERRIDDEN),
                ),
                'masqueradingreasonrequired' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('masqueradingreasonrequired', 'admin'),
                    'description'  => get_string('masqueradingreasonrequireddescription3', 'admin'),
                    'defaultvalue' => get_config('masqueradingreasonrequired'),
                    'disabled'     => in_array('masqueradingreasonrequired', $OVERRIDDEN),
                ),
                'masqueradingnotified' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('masqueradingnotified', 'admin'),
                    'description'  => get_string('masqueradingnotifielddescription', 'admin'),
                    'defaultvalue' => get_config('masqueradingnotified'),
                    'disabled'     => in_array('masqueradingnotified', $OVERRIDDEN),
                ),
                'showprogressbar' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('showprogressbar', 'admin'),
                    'description'  => get_string('showprogressbardescription1', 'admin'),
                    'defaultvalue' => get_config('showprogressbar'),
                    'disabled'     => in_array('showprogressbar', $OVERRIDDEN),
                ),
                'exporttoqueue' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('exporttoqueue', 'admin'),
                    'description'  => get_string('exporttoqueuedescription2', 'admin'),
                    'defaultvalue' => get_config('exporttoqueue'),
                    'disabled'     => in_array('exporttoqueue', $OVERRIDDEN),
                ),
                'defaultmultipleblogs' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('defaultmultipleblogs', 'admin'),
                    'description'  => get_string('defaultmultipleblogsdescription1', 'admin'),
                    'defaultvalue' => get_config('defaultmultipleblogs'),
                    'disabled'     => in_array('defaultmultipleblogs', $OVERRIDDEN),
                ),
            ),
        ),
        'searchsettings' => array(
            'iconclass' => 'search',
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
            'iconclass' => 'users',
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
                    'type'         => 'switchbox',
                    'title'        => get_string('allowgroupcategories', 'admin'),
                    'description'  => get_string('allowgroupcategoriesdescription1', 'admin'),
                    'defaultvalue' => get_config('allowgroupcategories'),
                    'disabled'     => in_array('allowgroupcategories', $OVERRIDDEN),
                ),
            ),
        ),
        'institutionsettings' => array(
            'iconclass' => 'university',
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => true,
            'legend'       => get_string('institutionsettingslegend', 'admin'),
            'elements'     => array(
                'requireregistrationconfirm' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('requireregistrationconfirm', 'admin'),
                    'description'  => get_string('requireregistrationconfirmdescription1', 'admin'),
                    'defaultvalue' => get_config('requireregistrationconfirm'),
                    'help'         => true,
                ),
                'usersallowedmultipleinstitutions' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('usersallowedmultipleinstitutions', 'admin'),
                    'description'  => get_string('usersallowedmultipleinstitutionsdescription1', 'admin'),
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
                    'class'        => 'double'
                ),
                'institutionautosuspend' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('institutionautosuspend', 'admin'),
                    'description'  => get_string('institutionautosuspenddescription1', 'admin'),
                    'defaultvalue' => get_config('institutionautosuspend'),
                    'help'         => true,
                    'disabled'     => in_array('institutionautosuspend', $OVERRIDDEN),
                ),
            ),
        ),
        'accountsettings' => array(
            'iconclass'=>'clock-o',
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
                    // Largest amount allowed is 30 days.
                    'rules'        => array('integer' => true, 'minvalue' => 1, 'maxvalue' => (30 * 24 * 60)),
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
                    'class'        => 'double'
                ),
                'defaultaccountlifetime' => array(
                    'type'         => 'expiry',
                    'title'        => get_string('defaultaccountlifetime', 'admin'),
                    'description'  => get_string('defaultaccountlifetimedescription', 'admin'),
                    'defaultvalue' => get_config('defaultaccountlifetime'),
                    'help'         => true,
                    'disabled'     => in_array('defaultaccountlifetime', $OVERRIDDEN),
                    'class'        => 'double'
                ),
                'defaultaccountlifetimeupdate' => array(
                    'type'         => 'radio',
                    'title'        => get_string('defaultaccountlifetimeupdate', 'admin'),
                    'description'  => get_string('defaultaccountlifetimeupdatedescription', 'admin'),
                    'defaultvalue' => get_config('defaultaccountlifetimeupdate'),
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
                    'class'        => 'double'
                ),
                'defaultaccountinactivewarn' => array(
                    'type'         => 'expiry',
                    'title'        => get_string('defaultaccountinactivewarn', 'admin'),
                    'description'  => get_string('defaultaccountinactivewarndescription', 'admin'),
                    'defaultvalue' => get_config('defaultaccountinactivewarn'),
                    'help'         => true,
                    'disabled'     => in_array('defaultaccountinactivewarn', $OVERRIDDEN),
                    'class'        => 'double'
                ),
            ),
        ),
        'securitysettings' => array(
            'iconclass'=>'lock',
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => true,
            'legend'       => get_string('securitysettingslegend', 'admin'),
            'elements'     => array(
                'viruschecking' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('viruschecking', 'admin'),
                    'description'  => get_string('viruscheckingdescription1', 'admin'),
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
                    'type'         => 'switchbox',
                    'title'        => get_string('spamhaus1', 'admin'),
                    'description'  => get_string('spamhausdescription1', 'admin'),
                    'defaultvalue' => get_config('spamhaus'),
                    'help'         => true,
                    'disabled'     => in_array('spamhaus', $OVERRIDDEN),
                ),
                'surbl' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('surbl1', 'admin'),
                    'description'  => get_string('surbldescription1', 'admin'),
                    'defaultvalue' => get_config('surbl'),
                    'help'         => true,
                    'disabled'     => in_array('surbl', $OVERRIDDEN),
                ),
                'disableexternalresources' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('disableexternalresources', 'admin'),
                    'description'  => get_string('disableexternalresourcesdescription1', 'admin'),
                    'defaultvalue' => get_config('disableexternalresources'),
                    'help'         => true,
                    'disabled'     => in_array('disableexternalresources', $OVERRIDDEN),
                ),
                'recaptchaonregisterform' => array(
                    'type' => 'switchbox',
                    'title' => get_string('recaptchaonregisterform', 'admin'),
                    'description' => get_string('recaptchaonregisterformdesc1', 'admin'),
                    'defaultvalue' => get_config('recaptchaonregisterform'),
                    'help' => true,
                    'disabled' => in_array('recaptchaonregisterform', $OVERRIDDEN)
                ),
                'recaptchapublickey' => array(
                    'type' => 'text',
                    'title' => get_string('recaptchapublickey1', 'admin'),
                    'description' => get_string('recaptchapublickeydesc1', 'admin'),
                    'defaultvalue' => get_config('recaptchapublickey'),
                    'disabled' => in_array('recaptchapublickey', $OVERRIDDEN)
                ),
                'recaptchaprivatekey' => array(
                    'type' => 'text',
                    'title' => get_string('recaptchaprivatekey1', 'admin'),
                    'description' => get_string('recaptchaprivatekeydesc1', 'admin'),
                    'defaultvalue' => get_config('recaptchaprivatekey'),
                    'disabled' => in_array('recaptchaprivatekey', $OVERRIDDEN)
                ),
            ),
        ),
        # TODO: this should become "Network Settings" at some point
        'proxysettings' => array(
            'iconclass'=>'exchange',
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
            'iconclass'=>'envelope',
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
            'iconclass'=>'bell',
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
            'iconclass'=>'cog',
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => true,
            'legend'       => get_string('generalsettingslegend', 'admin'),
            'elements'     => array(
                'allowpublicviews' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('allowpublicviews', 'admin'),
                    'description'  => get_string('allowpublicviewsdescription1', 'admin'),
                    'defaultvalue' => get_config('allowpublicviews'),
                    'help'         => true,
                    'disabled'     => in_array('allowpublicviews', $OVERRIDDEN),
                ),
                'allowpublicprofiles' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('allowpublicprofiles', 'admin'),
                    'description'  => get_string('allowpublicprofilesdescription1', 'admin'),
                    'defaultvalue' => get_config('allowpublicprofiles'),
                    'help'         => true,
                    'disabled'     => in_array('allowpublicprofiles', $OVERRIDDEN) || get_config('allowpublicviews'),
                ),
                'allowanonymouspages' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('allowanonymouspages', 'admin'),
                    'description'  => get_string('allowanonymouspagesdescription1', 'admin'),
                    'defaultvalue' => get_config('allowanonymouspages'),
                    'help'         => true,
                    'disabled'     => in_array('allowanonymouspages', $OVERRIDDEN),
                ),
                'generatesitemap' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('generatesitemap1', 'admin'),
                    'description'  => get_string('generatesitemapdescription', 'admin'),
                    'defaultvalue' => get_config('generatesitemap'),
                    'help'         => true,
                    'disabled'     => in_array('generatesitemap', $OVERRIDDEN) || !get_config('allowpublicviews'),
                ),
                'showselfsearchsideblock' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('showselfsearchsideblock1', 'admin'),
                    'description'  => get_string('showselfsearchsideblockdescription1', 'admin'),
                    'defaultvalue' => get_config('showselfsearchsideblock'),
                    'disabled'     => in_array('showselfsearchsideblock', $OVERRIDDEN),
                ),
                'showtagssideblock' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('showtagssideblock1', 'admin'),
                    'description'  => get_string('showtagssideblockdescription2', 'admin'),
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
                'showonlineuserssideblock' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('showonlineuserssideblock', 'admin'),
                    'description'  => get_string('showonlineuserssideblockdescriptionmessage1', 'admin'),
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
                    'type'         => 'switchbox',
                    'title'        => get_string('registerterms', 'admin'),
                    'description'  => get_string('registertermsdescription', 'admin'),
                    'defaultvalue' => get_config('registerterms'),
                    'disabled'     => in_array('registerterms', $OVERRIDDEN),
                ),
                'licensemetadata' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('licensemetadata', 'admin'),
                    'description'  => get_string('licensemetadatadescription', 'admin'),
                    'defaultvalue' => get_config('licensemetadata'),
                    'help'         => true,
                    'disabled'     => in_array('licensemetadata', $OVERRIDDEN),
                ),
                'licenseallowcustom' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('licenseallowcustom1', 'admin'),
                    'description'  => get_string('licenseallowcustomdescription', 'admin'),
                    'defaultvalue' => get_config('licenseallowcustom'),
                    'disabled'     => in_array('licenseallowcustom', $OVERRIDDEN),
                ),
                'allowmobileuploads' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('allowmobileuploads1', 'admin'),
                    'description'  => get_string('allowmobileuploadsdescription1', 'admin'),
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
                'mathjax' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('mathjax', 'admin'),
                    'description'  => get_string('mathjaxdescription', 'admin'),
                    'defaultvalue' => get_config('mathjax'),
                    'help'         => true,
                    'disabled'     => in_array('mathjax', $OVERRIDDEN),
                ),
                'sitefilesaccess' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('sitefilesaccess', 'admin'),
                    'description'  => get_string('sitefilesaccessdescription1', 'admin'),
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
            'iconclass'=>'exclamation-triangle',
            'class' => 'last',
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
                    'class'        => 'double'
                ),
            ),
        ),
    )
);

$siteoptionform['elements']['submit'] = array(
    'type'  => 'button',
    'usebuttontag' => true,
    'class' => 'btn-primary',
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
        'registration_sendweeklyupdates', 'mathjax', 'institutionexpirynotification', 'institutionautosuspend', 'requireregistrationconfirm',
        'showselfsearchsideblock', 'nousernames', 'searchplugin', 'showtagssideblock',
        'tagssideblockmaxtags', 'country', 'userscanchooseviewthemes',
        'remoteavatars', 'userscanhiderealnames', 'antispam', 'spamhaus', 'surbl', 'anonymouscomments',
        'recaptchaonregisterform', 'recaptchapublickey', 'recaptchaprivatekey', 'loggedinprofileviewaccess', 'disableexternalresources',
        'proxyaddress', 'proxyauthmodel', 'proxyauthcredentials', 'smtphosts', 'smtpport', 'smtpuser', 'smtppass', 'smtpsecure',
        'noreplyaddress', 'homepageinfo', 'showprogressbar', 'showonlineuserssideblock', 'onlineuserssideblockmaxusers',
        'registerterms', 'licensemetadata', 'licenseallowcustom', 'allowmobileuploads', 'creategroups', 'createpublicgroups', 'allowgroupcategories', 'wysiwyg',
        'staffreports', 'staffstats', 'userscandisabledevicedetection', 'watchlistnotification_delay',
        'masqueradingreasonrequired', 'masqueradingnotified', 'searchuserspublic',
        'eventloglevel', 'eventlogexpiry', 'sitefilesaccess', 'exporttoqueue', 'defaultmultipleblogs',
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
    // Call the new search plugin's can connect
    safe_require('search', $values['searchplugin']);
    $connect = call_static_method(generate_class_name('search', $values['searchplugin']), 'can_connect');
    if (!$connect) {
        $form->reply(PIEFORM_ERR, array(
            'message' => get_string('searchconfigerror1', 'admin', $values['searchplugin']),
            'goto'    => '/admin/site/options.php',
        ));
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
                'message' => get_string('recaptchakeysmissing1', 'admin'),
                'goto' => '/admin/site/options.php',
            )
        );
    }
    // Need to clear the cached menus in case site config changes effect them.
    clear_menu_cache();

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
setpageicon($smarty, 'icon-cogs');
$smarty->assign('siteoptionform', $siteoptionform);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('admin/site/options.tpl');

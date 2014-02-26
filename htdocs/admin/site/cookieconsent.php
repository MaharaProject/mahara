<?php
/**
 *
 * @package    mahara
 * @subpackge  admin
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2013 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configsite/cookieconsent');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'cookieconsent');

require(dirname(dirname(dirname(__FILE__))).'/init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('cookieconsent', 'cookieconsent'));
define('DEFAULTPAGE', 'home');


$enabled = get_config('cookieconsent_enabled');
$configdata = unserialize(get_config('cookieconsent_settings'));
$cookietypes = (!empty($configdata['cookietypes']) ? $configdata['cookietypes'] : array());

$form = pieform(array(
    'name'        => 'cookieconsent',
    'renderer'    => 'table',
    'plugintype'  => 'core',
    'pluginname'  => 'admin',
    'elements'    => array(
        'enabled' => array(
            'type' => 'checkbox',
            'title' => get_string('cookieconsentenable','cookieconsent'),
            'defaultvalue' => $enabled,
        ),
        'generaloptions' => array(
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => ($enabled ? false : true),
            'legend'       => get_string('generaloptions', 'cookieconsent'),
            'elements'     => array(
                'cookietypes' => array(
                    'type'  => 'checkboxes',
                    'title' => get_string('cookietypes','cookieconsent'),
                    'description' => get_string('cookietypesdesc','cookieconsent'),
                    'labelwidth' => 20,
                    'elements' => array(
                        'social' => array(
                            'title' => get_string('cookietypessocial','cookieconsent'),
                            'value' => 'social',
                            'defaultvalue' => (in_array('social', $cookietypes) ? 1 : 0),
                        ),
                        'analytics' => array(
                            'title' => get_string('cookietypesanalytics','cookieconsent'),
                            'value' => 'analytics',
                            'defaultvalue' => (in_array('analytics', $cookietypes) ? 1 : 0),
                        ),
                        'advertising' => array(
                            'title' => get_string('cookietypesadvertising','cookieconsent'),
                            'value' => 'advertising',
                            'defaultvalue' => (in_array('advertising', $cookietypes) ? 1 : 0),
                        ),
                        'necessary' => array(
                            'title' => get_string('cookietypesnecessary','cookieconsent'),
                            'value' => 'necessary',
                            'defaultvalue' => 1,
                            'disabled' => true,
                        ),
                    ),
                    'separator' => '<br />',
                ),
                'consentmode' => array(
                    'type'  => 'radio',
                    'title' => get_string('consentmode','cookieconsent'),
                    'description' => get_string('consentmodedesc1','cookieconsent') . '<br />'
                                   . get_string('consentmodedesc2','cookieconsent'),
                    'defaultvalue' => (!empty($configdata['consentmode']) ? hsc($configdata['consentmode']) : 'explicit'),
                    'options' => array(
                        'explicit'  => get_string('consentmodeexplicit','cookieconsent'),
                        'implicit' => get_string('consentmodeimplicit','cookieconsent'),
                    ),
                    'separator' => '<br />',
                ),
            ),
        ),
        'stylingoptions' => array(
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => ($enabled ? false : true),
            'legend'       => get_string('stylingoptions', 'cookieconsent'),
            'elements'     => array(
                'pluginstyle' => array(
                    'type'  => 'select',
                    'title' => get_string('pluginstyle','cookieconsent'),
                    'description' => get_string('pluginstyledesc','cookieconsent'),
                    'defaultvalue' => (!empty($configdata['pluginstyle']) ? hsc($configdata['pluginstyle']) : 'dark'),
                    'options' => array(
                        'dark'  => get_string('pluginstyledark','cookieconsent'),
                        'light' => get_string('pluginstylelight','cookieconsent'),
                    ),
                ),
                'bannerposition' => array(
                    'type'  => 'select',
                    'title' => get_string('bannerposition','cookieconsent'),
                    'description' => get_string('bannerpositiondesc','cookieconsent'),
                    'defaultvalue' => (!empty($configdata['bannerposition']) ? hsc($configdata['bannerposition']) : 'bottom'),
                    'options' => array(
                        'top'  => get_string('bannerpositiontop','cookieconsent'),
                        'push' => get_string('bannerpositionpush','cookieconsent'),
                        'bottom' => get_string('bannerpositionbottom','cookieconsent'),
                    ),
                ),
                'tabposition' => array(
                    'type'  => 'select',
                    'title' => get_string('tabposition','cookieconsent'),
                    'description' => get_string('tabpositiondesc','cookieconsent'),
                    'defaultvalue' => (!empty($configdata['tabposition']) ? hsc($configdata['tabposition']) : 'bottom-right'),
                    'options' => array(
                        'bottom-right'  => get_string('tabpositionbottomright','cookieconsent'),
                        'bottom-left' => get_string('tabpositionbottomleft','cookieconsent'),
                        'vertical-left' => get_string('tabpositionverticalleft','cookieconsent'),
                        'vertical-right' => get_string('tabpositionverticalright','cookieconsent'),
                    ),
                ),
                'hideprivacytab' => array(
                    'type'  => 'checkbox',
                    'title' => get_string('hideprivacytab','cookieconsent'),
                    'description' => get_string('hideprivacytabdesc','cookieconsent'),
                    'defaultvalue' => (!empty($configdata['hideprivacytab']) ? hsc($configdata['hideprivacytab']) : false),
                ),
            ),
        ),
        'featureoptions' => array(
            'type'         => 'fieldset',
            'collapsible'  => true,
            'collapsed'    => ($enabled ? false : true),
            'legend'       => get_string('featureoptions', 'cookieconsent'),
            'elements'     => array(
                'refreshonconsent' => array(
                    'type'  => 'checkbox',
                    'title' => get_string('pagerefresh','cookieconsent'),
                    'description' => get_string('pagerefreshdesc','cookieconsent'),
                    'defaultvalue' => (!empty($configdata['refreshonconsent']) ? hsc($configdata['refreshonconsent']) : false),
                ),
                'ignoredonottrack' => array(
                    'type'  => 'checkbox',
                    'title' => get_string('ignoredonottrack','cookieconsent'),
                    'description' => get_string('ignoredonottrackdesc','cookieconsent'),
                    'defaultvalue' => (!empty($configdata['ignoredonottrack']) ? hsc($configdata['ignoredonottrack']) : false),
                ),
                'usessl' => array(
                    'type'  => 'checkbox',
                    'title' => get_string('usessl','cookieconsent'),
                    'description' => get_string('usessldesc','cookieconsent'),
                    'defaultvalue' => (!empty($configdata['usessl']) ? hsc($configdata['usessl']) : false),
                ),
            ),
        ),
        'submit' => array(
            'type'  => 'submit',
            'value' => get_string('savechanges', 'admin')
        ),
    )
));


function cookieconsent_submit(Pieform $form, $values) {
    global $SESSION;
    // Save whether the Cookie Consent plugin is enabled
    $enabled = $values['enabled'];
    set_config('cookieconsent_enabled', $enabled);
    // Remove unused values and save Cookie Consent settings
    unset($values['enabled']);
    unset($values['submit']);
    unset($values['sesskey']);
    // Disabled checkbox isn't send through HTTP POST request, so
    $values['cookietypes'] = array_merge($values['cookietypes'], array('necessary'));
    // Save Cookie Consent plugin settings
    set_config('cookieconsent_settings', serialize($values));
    // Redirect to further installation instructions
    if ($enabled) {
        $SESSION->add_ok_msg(get_string('cookieconsentenabled', 'cookieconsent'));
        redirect(get_config('wwwroot') . 'admin/site/cookieconsent2.php');
    }
    else {
        $SESSION->add_ok_msg(get_string('cookieconsentdisabled', 'cookieconsent'));
        redirect(get_config('wwwroot') . 'admin/index.php');
    }
}

$js = <<<EOF
jQuery(document).ready(function() {
    var j = jQuery.noConflict();
    j('#cookieconsent input[name=enabled]').click(function() {
        if (this.checked) {
            // Expand collapsible fieldsets
            j('#cookieconsent fieldset').attr('class', 'pieform-fieldset collapsible');
            j('#cookieconsent_cookietypes').focus();
        }
        else {
            // Collapse collapsible fieldsets
            j('#cookieconsent fieldset').attr('class', 'pieform-fieldset collapsible collapsed');
        }
    });
});
EOF;


$smarty = smarty(array('expandable'));
$smarty->assign('form', $form);
$smarty->assign('introtext1', get_string('cookieconsentintro1', 'cookieconsent'));
$smarty->assign('introtext2', get_string('cookieconsentintro2', 'cookieconsent'));
$smarty->assign('introtext3', get_string('cookieconsentintro3', 'cookieconsent'));
$smarty->assign('introtext4', get_string('cookieconsentintro4', 'cookieconsent'));
$smarty->assign('introtext5', get_string('cookieconsentintro51', 'cookieconsent', '<a href="http://sitebeam.net/cookieconsent/" target="_blank">', '</a>'));
// Official EU languages
$smarty->assign('languages', array('BG','CS','DA','DE','EL','EN','ES','ET','FI','FR','HU','IT','LT','LV','MT','NL','PL','PT','RO','SK','SL','SV'));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('admin/site/cookieconsent.tpl');

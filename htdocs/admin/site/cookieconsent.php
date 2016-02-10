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
define('TITLE', get_string('cookieconsent', 'cookieconsent'));
define('DEFAULTPAGE', 'home');


$enabled = get_config('cookieconsent_enabled');
$configdata = unserialize(get_config('cookieconsent_settings'));
$cookietypes = (!empty($configdata['cookietypes']) ? $configdata['cookietypes'] : array());

$form = pieform(array(
    'class'        => 'collapsible-group',
    'name'        => 'cookieconsent',
    'renderer'    => 'div',
    'plugintype'  => 'core',
    'pluginname'  => 'admin',
    'elements'    => array(
        'enabled' => array(
            'type' => 'switchbox',
            'title' => get_string('cookieconsentenable','cookieconsent'),
            'defaultvalue' => $enabled,
        ),
        'submit' => array(
            'class' => 'btn-primary',
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
    if ($enabled) {
        $SESSION->add_ok_msg(get_string('cookieconsentenabled', 'cookieconsent'));
    }
    else {
        $SESSION->add_ok_msg(get_string('cookieconsentdisabled', 'cookieconsent'));
    }
    redirect(get_config('wwwroot') . 'admin/site/cookieconsent.php');
}


$smarty = smarty();
setpageicon($smarty, 'icon-shield');

$smarty->assign('form', $form);
$smarty->assign('introtext1', get_string('cookieconsentintro1', 'cookieconsent'));
$smarty->assign('introtext2', get_string('cookieconsentintro2', 'cookieconsent'));
$smarty->assign('introtext3', get_string('cookieconsentintro3', 'cookieconsent'));
$smarty->assign('introtext4', get_string('cookieconsentintro4', 'cookieconsent'));
$smarty->assign('introtext5', get_string('cookieconsentintro52', 'cookieconsent', '<a href="http://sitebeam.net/cookieconsent/">', '</a>'));
// Official EU languages
$smarty->assign('languages', array('BG','CS','DA','DE','EL','EN','ES','ET','FI','FR','HU','IT','LT','LV','MT','NL','PL','PT','RO','SK','SL','SV'));
$smarty->display('admin/site/cookieconsent.tpl');

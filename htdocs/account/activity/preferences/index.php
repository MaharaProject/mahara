<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'settings/notifications');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'account');
define('SECTION_PAGE', 'notifications');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
define('TITLE', get_string('notifications'));
require_once(get_config('libroot') . 'activity.php');

$elements = get_notification_settings_elements($USER);

$elements['submit'] = array(
    'type' => 'submit',
    'class' => 'btn-primary',
    'value' => get_string('save'),
);


$prefsform = pieform(array(
    'name'        => 'activityprefs',
    'class'       => 'form-notifications',
    'method'      => 'post',
    'jsform'      => true,
    'renderer'    => 'div',
    'plugintype'  => 'core',
    'pluginname'  => 'account',
    'elements'    => $elements,
));

$smarty = smarty();
$smarty->assign('pagedescription', get_string('prefsdescr', 'activity'));
$smarty->assign('form', $prefsform);
$smarty->assign('PAGEHEADING', get_config('dropdownmenu') ? get_string('settings') : TITLE);
$smarty->display('form.tpl');

function activityprefs_submit(Pieform $form, $values) {
    global $USER;

    save_notification_settings($values, $USER);

    $form->json_reply(PIEFORM_OK, get_string('prefssaved', 'account'));
}

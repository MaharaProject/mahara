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
define('MENUITEM', 'inbox');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'account');
define('SECTION_PAGE', 'activity');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

safe_require('module', 'multirecipientnotification');
if (PluginModuleMultirecipientnotification::is_active()) {
    redirect(get_config('wwwroot') . 'module/multirecipientnotification/inbox.php');
}

throw new ConfigSanityException(get_string('multirecipientnotificationnotenabled', 'error'));

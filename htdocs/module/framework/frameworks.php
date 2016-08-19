<?php
/**
 *
 * @package    mahara
 * @subpackage module-framework
 * @author     Catalyst IT Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configextensions/frameworks');
define('SECTION_PLUGINTYPE', 'module');
define('SECTION_PLUGINNAME', 'framework');
define('SECTION_PAGE', 'frameworks');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

define('TITLE', get_string('Framework', 'module.framework'));
safe_require('module', 'framework');
$frameworks = Framework::list_frameworks();

function framework_delete_submit(Pieform $form, $values) {
    global $SESSION;

    $framework = new Framework($values['framework']);
    if (!$framework->in_collections()) {
        $framework->delete();
        $SESSION->add_ok_msg(get_string('itemdeleted'));
    }
    else {
        $SESSION->add_error_msg(get_string('deletefailed', 'admin'));
    }

    redirect('/module/framework/frameworks.php');
}

$smarty = smarty();
setpageicon($smarty, 'icon-th');
$smarty->assign('frameworks', $frameworks);
$smarty->assign('wwwroot', get_config('wwwroot'));
$smarty->display('module:framework:frameworks.tpl');

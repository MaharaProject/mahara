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
define('MENUITEM', 'settings/privacy');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'account');
define('SECTION_PAGE', 'userprivacy');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('docroot') . 'lib/user.php');
define('TITLE', get_string('privacy', 'admin'));

if (!is_logged_in()) {
    throw new AccessDeniedException();
}

// Get all institutions of a user.
$userinstitutions = array_keys($USER->get('institutions'));
// Include the 'mahara' institution so that we may show the site privacy statement as well.
array_push($userinstitutions, 'mahara');

// Get all the latest privacy statement (institution and site) the user has agreed to.
$data = get_latest_privacy_versions($userinstitutions);

// JQuery logic for panel hide/show.
// Needed here because there are multiple dropdown panels on this page.
$js = <<< EOF
    function showPanel(el) {
        elementid = $(el).attr('id');
        $("#dropdown" + elementid).toggleClass("collapse");
    }
EOF;

$smarty = smarty();
setpageicon($smarty, 'icon-umbrella');

$smarty->assign('results', $data);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('account/userprivacy.tpl');

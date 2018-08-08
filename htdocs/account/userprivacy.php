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
define('TITLE', get_string('legal', 'admin'));

if (!is_logged_in()) {
    throw new AccessDeniedException();
}

$form = privacy_form(!get_config('institutionstrictprivacy'), !get_config('institutionstrictprivacy'));

// JQuery logic for panel hide/show submit button.
$js = <<< EOF
    $(function() {
        $(".state-label").on("click", function() {
            $(this).siblings( ".switch-inner" ).toggleClass("redraw-consent");
            showSubmitButton();
        });
    });
EOF;

$smarty = smarty(array('privacy'));
setpageicon($smarty, 'icon-umbrella');

$smarty->assign('form', $form);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('description', get_string('userprivacypagedescription1', 'admin'));
$smarty->display('account/userprivacy.tpl');

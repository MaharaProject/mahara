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

$form = privacy_form();

// JQuery logic for panel hide/show.
// Needed here because there are multiple dropdown panels on this page.
$js = <<< EOF
    $( document ).ready(function() {
        $(".state-label").click(function() {
            $(this).siblings( ".switch-inner" ).toggleClass("redraw-consent");
            showSubmitButton();
        });
    });
    function showSubmitButton() {
        if ($('body').find(".redraw-consent").length == 0) {
            $('#agreetoprivacy_submit_container').addClass('js-hidden');
            $('#agreetoprivacy_submit').addClass('js-hidden');
        }
        else {
            $('#agreetoprivacy_submit_container').removeClass('js-hidden');
            $('#agreetoprivacy_submit').removeClass('js-hidden');
        }
    }
EOF;

$smarty = smarty();
setpageicon($smarty, 'icon-umbrella');

$smarty->assign('form', $form);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('description', get_string('userprivacypagedescription', 'admin'));
$smarty->display('account/userprivacy.tpl');

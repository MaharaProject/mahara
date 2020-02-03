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

define('PUBLIC', 1);
define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'collection');
define('SECTION_PAGE', 'progress');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('collection.php');

$collectionid = param_integer('id');

$collection = new Collection($collectionid);

define('TITLE', $collection->get('name'));
$javascript = array('js/collection-navigation.js',
'js/jquery/jquery-mobile/jquery.mobile.custom.min.js',
'tinymce', 'module/framework/js/matrix.js',
'js/jquery/jquery-ui/js/jquery-ui.min.js');

$views = $collection->get('views');

// Get the first view from the collection
$firstview = $views['views'][0];
$view = new View($firstview->id);

if (!can_view_view($view->get('id'))) {
    throw new AccessDeniedException();
}
if (!$collection->has_progresscompletion()) {
    throw new AccessDeniedException();
}

// Set up theme
$viewtheme = $view->get('theme');
if ($viewtheme && $THEME->basename != $viewtheme) {
    $THEME = new Theme($viewtheme);
}
$headers[] = '<meta name="robots" content="noindex">';

$smarty = smarty(
    $javascript,
    $headers,
    array('View' => 'view',
        'Collection' => 'collection'
    ),
    array(
        'sidebars' => false,
    )
);

$smarty->assign('maintitle', get_string('portfoliocompletion', 'collection'));
// collection top navigation
if ($collection) {
    $shownav = $collection->get('navigation');
    if ($shownav) {
        $viewnav = $views['views'];
        if ($collection->get('framework')) {
            array_unshift($viewnav, $collection->collection_nav_framework_option());
        }
        array_unshift($viewnav, $collection->collection_nav_progresscompletion_option());
        $smarty->assign('collection', $viewnav);
    }
}

$smarty->assign('progresscompletion', true);

// progress bar
$collectionowner = new User();
$collectionowner->find_by_id($collection->get('owner'));
$smarty->assign('quotamessage', get_string('overallcompletion', 'collection', display_name($collectionowner)));
$smarty->assign('signoffpercentage', $collection->get_signed_off_percentage());

$smarty->display('collection/progresscompletion.tpl');

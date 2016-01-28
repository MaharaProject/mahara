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
define('MENUITEM', 'configextensions/filters');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('htmlfilters', 'admin'));

if ($filters = get_config('filters')) {
    $filters = unserialize($filters);
}
else {
    $filters = array();
}

$reloadform = pieform(array(
    'name'       => 'reloadfilters',
    'renderer'   => 'div',
    'plugintype' => 'core',
    'pluginname' => 'admin',
    'autofocus'  => false,
    'elements'   => array(
        'reload' => array(
            'type'         => 'button',
            'usebuttontag' => true,
            'class'        => 'btn-primary',
            'value'        => get_string('install', 'admin'),
        ),
    ),
));

function reloadfilters_submit(Pieform $form, $values) {
    global $SESSION;
    require_once(get_config('libroot') . 'upgrade.php');
    reload_html_filters();
    $SESSION->add_ok_msg(get_string('filtersinstalled', 'admin'));
    redirect(get_config('wwwroot') . 'admin/extensions/filter.php');
}

$smarty = smarty();
setpageicon($smarty, 'icon-puzzle-piece');

$smarty->assign('reloadform', $reloadform);
$smarty->assign('newfiltersdescription', get_string('newfiltersdescription', 'admin', get_config('libroot') . 'htmlpurifiercustom'));
$smarty->assign('filters', $filters);
$smarty->display('admin/extensions/filters.tpl');

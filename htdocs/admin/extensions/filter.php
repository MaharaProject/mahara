<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configextensions/filters');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('htmlfilters', 'admin'));

if ($filters = get_config('filters')) {
    $filters = unserialize($filters);
}
else {
    $filters = array();
}

$reloadform = pieform(array(
    'name'       => 'reloadfilters',
    'renderer'   => 'table',
    'plugintype' => 'core',
    'pluginname' => 'admin',
    'autofocus'  => false,
    'elements'   => array(
        'reload' => array(
            'type'         => 'submit',
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
$smarty->assign('reloadform', $reloadform);
$smarty->assign('newfiltersdescription', get_string('newfiltersdescription', 'admin', get_config('libroot') . 'htmlpurifiercustom'));
$smarty->assign('filters', $filters);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('admin/extensions/filters.tpl');

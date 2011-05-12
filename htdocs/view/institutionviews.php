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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);

define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'manageinstitutions/institutionviews');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'institution.php');
require_once('pieforms/pieform.php');

$institution = param_alpha('institution', false);

if ($institution == 'mahara') {
    redirect('/admin/site/views.php');
}

$s = institution_selector_for_page($institution,
                                   get_config('wwwroot') . 'view/institutionviews.php');

$institution = $s['institution'];

define('TITLE', get_string('institutionviews', 'view'));

if ($institution === false) {
    $smarty = smarty();
    $smarty->display('admin/users/noinstitutions.tpl');
    exit;
}

list($searchform, $data, $pagination) = View::views_by_owner(null, $institution);

$createviewform = pieform(create_view_form(null, $institution));

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('institutionselector', $s['institutionselector']);
$smarty->assign('INLINEJAVASCRIPT', $s['institutionselectorjs']);
$smarty->assign('views', $data->data);
$smarty->assign('institution', $institution);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('searchform', $searchform);
$smarty->assign('createviewform', $createviewform);
$smarty->display('view/index.tpl');

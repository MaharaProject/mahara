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
define('ADMIN', 1);
define('MENUITEM', 'configsite/siteviews');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'siteviews');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once('pieforms/pieform.php');

$limit   = param_integer('limit', 5);
$offset  = param_integer('offset', 0);

$title = get_string('siteviews', 'admin');
define('TITLE', $title);

$createviewform = pieform(create_view_form(null, 'mahara'));

$smarty = smarty();
$smarty->assign('PAGEHEADING', hsc(TITLE));

$data = View::get_myviews_data($limit, $offset, null, 'mahara');

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'admin/site/views.php',
    'count' => $data->count,
    'limit' => $limit,
    'offset' => $offset,
    'resultcounttextsingular' => get_string('view', 'view'),
    'resultcounttextplural' => get_string('views', 'view')
));

$smarty->assign('views', $data->data);
$smarty->assign('institution', 'mahara');
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('createviewform', $createviewform);

$smarty->display('view/index.tpl');

?>

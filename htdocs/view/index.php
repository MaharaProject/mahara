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
define('MENUITEM', 'myportfolio/views');

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'index');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once('pieforms/pieform.php');
require_once('group.php');
define('TITLE', get_string('myviews', 'view'));

$limit = param_integer('limit', 5);
$offset = param_integer('offset', 0);

$data = View::get_myviews_data($limit, $offset);

$userid = $USER->get('id');

/* Get a list of groups that the user belongs to which views can
    be sumitted. */
if (!$tutorgroupdata = group_get_user_course_groups()) {
    $tutorgroupdata = array();
}
else {
    $options = array();
    foreach ($data->data as &$view) {
        if (empty($view['submittedto'])) {
            $view['submitto'] = view_group_submission_form($view['id'], $tutorgroupdata);
        }
        if ($view['type'] == 'profile' && get_config('allowpublicprofiles')) {
            $view['togglepublic'] = togglepublic_form($view['id']);
        }
    }
}

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'view/',
    'count' => $data->count,
    'limit' => $limit,
    'offset' => $offset,
    'resultcounttextsingular' => get_string('view', 'view'),
    'resultcounttextplural' => get_string('views', 'view')
));

$createviewform = pieform(create_view_form());

$smarty = smarty();
$smarty->assign('views', $data->data);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('PAGEHEADING', hsc(get_string('myviews')));
$smarty->assign('createviewform', $createviewform);
$smarty->display('view/index.tpl');

?>

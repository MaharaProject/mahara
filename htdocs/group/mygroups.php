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
define('MENUITEM', 'groups/mygroups');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('mygroups'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'group');
define('SECTION_PAGE', 'mygroups');
require_once('group.php');
$filter = param_alpha('filter', 'all');
$offset = param_integer('offset', 'all');
$groupcategory = param_signed_integer('groupcategory', 0);

$groupsperpage = 10;
$offset = (int)($offset / $groupsperpage) * $groupsperpage;

$results = group_get_associated_groups($USER->get('id'), $filter, $groupsperpage, $offset, $groupcategory);
$elements = array();
$elements['options'] = array(
            'type' => 'select',
            'options' => array(
                'all'     => get_string('allmygroups', 'group'),
                'admin'   => get_string('groupsiown', 'group'),
                'member'  => get_string('groupsimin', 'group'),
                'invite'  => get_string('groupsiminvitedto', 'group'),
                'request' => get_string('groupsiwanttojoin', 'group')
            ),
            'defaultvalue' => $filter);
if (get_config('allowgroupcategories')
    && $groupcategories = get_records_menu('group_category','','','displayorder', 'id,title')
) {
    $options[0] = get_string('allcategories', 'group');
    $options[-1] = get_string('categoryunassigned', 'group');
    $options += $groupcategories;
    $elements['groupcategory'] = array(
                'type'         => 'select',
                'options'      => $options,
                'defaultvalue' => $groupcategory,
                'help'         => true);
}
$elements['submit'] = array(
            'type' => 'submit',
            'value' => get_string('filter'));
$form = pieform(array(
    'name'   => 'filter',
    'method' => 'post',
    'renderer' => 'oneline',
    'elements' => $elements
));

$params = array();
if ($filter != 'all') {
    $params['filter'] = $filter;
}
if ($groupcategory != 0) {
    $params['groupcategory'] = $groupcategory;
}

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'group/mygroups.php' . ($params ? ('?' . http_build_query($params)) : ''),
    'count' => $results['count'],
    'limit' => $groupsperpage,
    'offset' => $offset,
    'resultcounttextsingular' => get_string('group', 'group'),
    'resultcounttextplural' => get_string('groups', 'group'),
));

group_prepare_usergroups_for_display($results['groups'], 'mygroups');

$smarty = smarty();
$smarty->assign('groups', $results['groups']);
$smarty->assign('cancreate', group_can_create_groups());
$smarty->assign('form', $form);
$smarty->assign('filter', $filter);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('searchingforgroups', array('<a href="' . get_config('wwwroot') . 'group/find.php">', '</a>'));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('group/mygroups.tpl');

function filter_submit(Pieform $form, $values) {
    redirect('/group/mygroups.php?filter=' . $values['options']. (!empty($values['groupcategory']) ? '&groupcategory=' . intval($values['groupcategory']) : ''));
}

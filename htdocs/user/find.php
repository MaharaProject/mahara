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
define('MENUITEM', 'groups/findfriends');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('findfriends'));
require_once('searchlib.php');
safe_require('search', 'internal');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'user');
define('SECTION_PAGE', 'find');

if (param_variable('acceptfriend_submit', null)) {
    acceptfriend_form(param_integer('id'));
}
else if (param_variable('addfriend_submit', null)) {
    addfriend_form(param_integer('id'));
}

$query = param_variable('query', '');
$offset = param_integer('offset', 0);
$filter = param_alpha('filter', $USER->get('admin') ? 'all' : 'myinstitutions');
$limit  = 10;

$options = array('exclude' => $USER->get('id'));
if ($filter == 'myinstitutions' && $USER->get('institutions')) {
    $options['myinstitutions'] = true;
}
else {
    $filter = 'all';
}

$data = search_user($query, $limit, $offset, $options);
$data['query'] = $query;
if (!empty($options['myinstitutions'])) {
    $data['filter'] = $filter;
}

require_once(get_config('libroot').'group.php');
$admingroups = (bool) group_get_user_admintutor_groups();
build_userlist_html($data, 'find', $admingroups);

$searchform = array(
    'name' => 'search',
    'renderer' => 'oneline',
    'elements' => array(),
);

if ($USER->get('institutions')) {
    $searchform['elements']['filter'] = array(
        'type' => 'select',
        'options' => array(
            'all'            => get_string('Everyone', 'group'),
            'myinstitutions' => get_string('myinstitutions', 'group'),
        ),
        'defaultvalue' => $filter,
    );
}

$searchform['elements']['query'] = array(
    'type' => 'text',
    'defaultvalue' => $query,
);
$searchform['elements']['submit'] = array(
    'type' => 'submit',
    'value' => get_string('search'),
);

$searchform = pieform($searchform);

$js = <<< EOF
addLoadEvent(function () {
    p = {$data['pagination_js']}
    connect('search_submit', 'onclick', function (event) {
        replaceChildNodes('messages');
        var params = {'query': $('search_query').value, 'extradata':serializeJSON({'page':'find'})};
        if ($('search_filter')) {
            params.filter = $('search_filter').value;
        }
        p.sendQuery(params);
        event.stop();
    });
});
EOF;

$javascript = array('paginator');
if ($admingroups) {
    array_push($javascript, 'groupbox');
}
$smarty = smarty($javascript, array(), array('applychanges' => 'mahara', 'nogroups' => 'group'), array('sideblocks' => array(friends_control_sideblock('find'))));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('results', $data);
$smarty->assign('form', $searchform);
$smarty->display('user/find.tpl');

function search_submit(Pieform $form, $values) {
    redirect('/user/find.php' . (!empty($values['query']) ? '?query=' . urlencode($values['query']) : ''));
}

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

define('INTERNAL', 1);
define('MENUITEM', 'engage/people');
require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('people'));
require_once('searchlib.php');
safe_require('search', 'internal');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'user');
define('SECTION_PAGE', 'index');

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

$searchmode = 'find';

$options = array('exclude' => $USER->get('id'));

$data['query'] = $query;

if ($filter == 'myinstitutions' && $USER->get('institutions')) {
    $options['myinstitutions'] = true;
}
else {
    $filter = 'all';
}

if ($filter == 'current' || $filter == 'pending') {
    $searchmode = 'myfriends';
    $data = search_friend($filter, $limit, $offset, $query);
}
else {
    $data = search_user($query, $limit, $offset, $options);
}

require_once(get_config('libroot').'group.php');
$admingroups = (bool) group_get_user_admintutor_groups();
build_userlist_html($data, $searchmode, $admingroups, $filter, $query);

$elements = array();
$queryfield = array(
            'title' => get_string('search') . ': ',
            'hiddenlabel' => false,
            'type' => 'text',
            'class' => 'with-dropdown js-with-dropdown',
            'defaultvalue' => $query
);

$filterfield = array(
            'title' => get_string('filter') . ': ',
            'hiddenlabel' => false,
            'type' => 'select',
            'class' => 'dropdown-connect js-dropdown-connect',
            'options' => array(
                'all'   => get_string('Everyone', 'group'),
                'current' => get_string('Friends', 'group'),
                'pending' => get_string('friendrequests', 'group')
            ),
            'defaultvalue' => $filter
);

//Only offer myinstitutions if user is a member
if ($USER->get('institutions')) {
    unset($filterfield['options']);
    $filterfield['options'] = array(
        'all'   => get_string('Everyone', 'group'),
        'myinstitutions' => get_string('myinstitutions', 'group'),
        'current' => get_string('Friends', 'group'),
        'pending' => get_string('friendrequests', 'group')
    );
}

$elements['searchwithin'] = array(
    'type' => 'fieldset',
    'class' => 'dropdown-group js-dropdown-group',
    'elements' => array(
        'query' => $queryfield,
        'filter' => $filterfield
    )
);

$elements['searchfield'] = array(
    'type' => 'submit',
    'class' => 'btn-primary no-label',
    'value' => get_string('search')
);

$searchform = pieform(array(
    'name'   => 'search',
    'checkdirtychange' => false,
    'method' => 'post',
    'class' => 'form-inline with-heading',
    'elements' => $elements
    )
);

$javascript = array('paginator');
if ($admingroups) {
    array_push($javascript, 'groupbox');
}

$smarty = smarty($javascript, array(), array('applychanges' => 'mahara', 'nogroups' => 'group'), array('sideblocks' => array(friends_control_sideblock('find'))));
setpageicon($smarty, 'icon-user-plus');
$smarty->assign('results', $data);
$smarty->assign('count', $data['count']);
$smarty->assign('form', $searchform);
$smarty->display('user/index.tpl');

function search_submit(Pieform $form, $values) {
    $querystring = (isset($values['query']) && ($values['query'] != '')) ? '&query=' . urlencode($values['query']) : '';
    redirect('/user/index.php?filter=' . urlencode($values['filter']) . $querystring);

}

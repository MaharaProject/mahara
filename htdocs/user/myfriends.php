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
define('MENUITEM', 'groups/myfriends');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('searchlib.php');
define('TITLE', get_string('myfriends'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'user');
define('SECTION_PAGE', 'myfriends');

if (param_variable('acceptfriend_submit', null)) {
    acceptfriend_form(param_integer('id'));
}
else if (param_variable('addfriend_submit', null)) {
    addfriend_form(param_integer('id'));
}

$filter = param_alpha('filter', 'all');
$offset = param_integer('offset', 0);
$limit = 10;

$data = search_friend($filter, $limit, $offset);
$data['filter'] = $filter;

require_once('group.php');
$admingroups = (bool) group_get_user_admintutor_groups();
build_userlist_html($data, 'myfriends', $admingroups);

$filterform = pieform(array(
    'name' => 'filter',
    'checkdirtychange' => false,
    'renderer' => 'div',
    'class' => 'form-inline with-heading',
    'elements' => array(
        'inputgroup' => array(
            'type' => 'fieldset',
            'title' => get_string('filter'),
            'class' => 'input-group',
            'elements' => array(
                'filter' => array(
                    'type' => 'select',
                    'defaultvalue' => $filter,
                    'options' => array(
                        'all' => get_string('allfriends', 'group'),
                        'current' => get_string('currentfriends', 'group'),
                        'pending' => get_string('pendingfriends', 'group')
                    ),
                ),
                'submit' => array(
                    'type' => 'button',
                    'usebuttontag' => true,
                    'class' => 'btn-primary input-group-btn',
                    'value' => '<span class="icon icon-filter left" role="presentation" aria-hidden="true"></span> ' . get_string('filter')
                )
            ),
        ),
    )
));

$js = <<< EOF
addLoadEvent(function () {
    p = {$data['pagination_js']}
    connect('filter_submit', 'onclick', function (event) {
        replaceChildNodes('messages');
        var params = {'filter': $('filter_filter').value};
        p.sendQuery(params);
        event.stop();
    });
});
EOF;

if (!$data['count']) {
    if ($filter == 'pending') {
        $message = get_string('nobodyawaitsfriendapproval', 'group');
    }
    else {
        $message = get_string('trysearchingforfriends', 'group', '<a href="' . get_config('wwwroot') . 'user/find.php">', '</a>');
    }
}

$javascript = array('paginator');
if ($admingroups) {
    array_push($javascript, 'groupbox');
}
$smarty = smarty($javascript, array(), array('applychanges' => 'mahara', 'nogroups' => 'group'), array('sideblocks' => array(friends_control_sideblock())));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('results', $data);
$smarty->assign('form', $filterform);
if (isset($message)) {
    $smarty->assign('message', $message);
}
$smarty->display('user/myfriends.tpl');

function filter_submit(Pieform $form, $values) {
    redirect('/user/myfriends.php?filter=' . $values['filter']);
}

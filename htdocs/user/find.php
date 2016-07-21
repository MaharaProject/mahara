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
define('MENUITEM', 'groups/findfriends');
require(dirname(dirname(__FILE__)) . '/init.php');
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
    'checkdirtychange' => false,
    'renderer' => 'div',
    'class' => 'form-inline with-heading',
    'elements' => array(
        'inputgroup' => array(
            'type' => 'fieldset',
            'class' => 'input-group',
            'title' => get_string('search'),
            'elements' => array(
            ),
        ),
    ),
);

$searchform['elements']['inputgroup']['elements']['query'] = array(
    'type' => 'text',
    'title' => get_string('search'),
    'hiddenlabel' => true,
    'defaultvalue' => $query,
    'placeholder' => get_string('searchusers'),
);

$searchform['elements']['inputgroup']['elements']['submit'] = array(
    'type' => 'button',
    'usebuttontag' => true,
    'class' => 'btn-primary input-group-btn',
    'value' => get_string('search'),
);

// Add institution filter, and combine the search query field and the
// institution filter into one combined element via CSS
if ($USER->get('institutions')) {
    unset($searchform['elements']['inputgroup']['title']);
    $searchform['elements']['inputgroup']['class'] = 'dropdown-group js-dropdown-group';

    $searchform['elements']['inputgroup']['elements']['query']['title'] .= ': ';
    $searchform['elements']['inputgroup']['elements']['query']['hiddenlabel'] = false;
    $searchform['elements']['inputgroup']['elements']['query']['class'] = 'with-dropdown js-with-dropdown';

    // Move the submit button outside the inputgroup
    unset($searchform['elements']['inputgroup']['elements']['submit']);
    $searchform['elements']['submit'] = array(
        'type' => 'submit',
        'class' => 'btn-primary no-label',
        'value' => get_string('search')
    );

    // Insert the filter into the inputgroup, after the query element
    $searchform['elements']['inputgroup']['elements']['filter'] =     array(
        'title' => get_string('filter') . ': ',
        'hiddenlabel' => false,
        'type' => 'select',
        'class' => 'dropdown-connect js-dropdown-connect',
        'options' => array(
            'all'            => get_string('Everyone', 'group'),
            'myinstitutions' => get_string('myinstitutions', 'group'),
        ),
        'defaultvalue' => $filter,
    );
}

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
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('results', $data);
$smarty->assign('form', $searchform);
$smarty->display('user/find.tpl');

function search_submit(Pieform $form, $values) {
    redirect('/user/find.php' . ((isset($values['query']) && ($values['query'] != '')) ? '?query=' . urlencode($values['query']) : ''));
}

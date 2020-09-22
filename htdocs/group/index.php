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
define('MENUITEM', 'engage/index');
require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('groups'));
require_once('group.php');
require_once('searchlib.php');

$labelfilter = param_variable('labelfilter', null);
$labelfilterremove = param_integer('remove', 0);
if ($labelfilter) {
    $entitiesAttr = array('/\&quot;/' => '"',
                          '/\&amp;/' => '&',
                          '/\&lt;/' => '<',
                          '/\&gt;/' => '>',
                          '/\&\#39;/' => "'",
                          '/\&\#039;/' => "'",
                          '/\&\#x27;/' => "'"
                          );
    $patterns = array_keys($entitiesAttr);
    $replace = array_values($entitiesAttr);
    $labelfilter = preg_replace($patterns, $replace, $labelfilter);
    $userlabels = (array)json_decode(get_account_preference($USER->get('id'), 'grouplabels'));
    $last = count($userlabels);
    $flipped = array_flip($userlabels);
    if ($labelfilterremove) {
        unset($flipped[$labelfilter]);
    }
    else if (!isset($flipped[$labelfilter])) {
        $flipped[$labelfilter] = $last;
    }

    $userlabels = array_flip($flipped);
    natcasesort($userlabels);
    set_account_preference($USER->get('id'), 'grouplabels', json_encode(array_values($userlabels)));
}
$filter = param_alpha('filter', 'allmy');
if ($filter == 'all' && $labelfilter !== null) {
     // when selecting a label in 'All groups' search view switch to 'All my groups'
    $filter = 'allmy';
}
$offset = param_integer('offset', 0);
$groupcategory = param_signed_integer('groupcategory', 0);
$groupsperpage = 10;
$query = param_variable('query', '');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'group');
define('SECTION_PAGE', 'index');

// Create the "add label form " now if it's been submitted
if (param_exists('pieform_grouplabel')) {
    pieform(group_label_form(param_integer('groupid')));
}

$activegrouplabels = (array)json_decode(get_account_preference($USER->get('id'), 'grouplabels'));

/* $searchmode will switch between 2 search funcs (with different queries)
*  $searchmode = 'find' uses search_group
*  $searchmode = 'mygroups' uses group_get_associated_groups
*/
$searchmode = 'find';

$showactivegrouplabels = false;
// check that the filter is valid, if not default to 'all'
if (in_array($filter, array('allmy', 'member', 'admin', 'invite', 'notmember', 'canjoin'))) {
    if ($filter == 'allmy' || $filter == 'admin' || $filter == 'member' || $filter == 'invite') {
        $searchmode = 'mygroups';
    }
    if ($filter == 'allmy' || $filter == 'member' || $filter == 'admin') {
        $showactivegrouplabels = true;
    }
    $type = $filter;
}
else { // all or some other text
    $filter = 'all';
    $type = 'all';
}

$elements = array();
$queryfield = array(
    'title' => get_string('search') . ': ',
    'hiddenlabel' => false,
    'type' => 'text',
    'class' => 'with-dropdown js-with-dropdown',
    'defaultvalue' => $query
);
$filteroptions = array(
    'allmy'     => get_string('allmygroups', 'group'),
    'member'    => get_string('groupsimin', 'group'),
    'admin'     => get_string('groupsiown', 'group'),
    'invite'    => get_string('groupsiminvitedto', 'group'),
    'canjoin'   => get_string('groupsicanjoin', 'group'),
    'notmember' => get_string('groupsnotin', 'group'),
    'all'       => get_string('allgroups', 'group')
);
$is_admin = $USER->get('admin') || $USER->is_institutional_admin() || $USER->get('staff') || $USER->is_institutional_staff();
if (is_isolated() && get_config('owngroupsonly') && !$is_admin) {
    $filteroptions = array(
        'allmy'     => get_string('allmygroups', 'group'),
        'member'    => get_string('groupsimin', 'group'),
        'admin'     => get_string('groupsiown', 'group'),
        'invite'    => get_string('groupsiminvitedto', 'group'),
        'canjoin'   => get_string('groupsicanjoin', 'group')
    );
}
$filterfield = array(
    'title' => get_string('filter') . ': ',
    'hiddenlabel' => false,
    'type' => 'select',
    'class' => 'dropdown-connect js-dropdown-connect',
    'options' => $filteroptions,
    'defaultvalue' => $filter
);

$elements['searchwithin'] = array(
    'type' => 'fieldset',
    'class' => 'dropdown-group js-dropdown-group',
    'elements' => array(
        'query' => $queryfield,
        'filter' => $filterfield
    )
);


if (get_config('allowgroupcategories')
    && $groupcategories = get_records_menu('group_category','','','displayorder', 'id,title')
) {
    $options[0] = get_string('allcategories', 'group');
    $options[-1] = get_string('notcategorised', 'group');
    $options += $groupcategories;

    $groupcategoryfield = array(
        'title'        => get_string('groupcategory', 'group'),
        'hiddenlabel'  => false,
        'type'         => 'select',
        'options'      => $options,
        'defaultvalue' => $groupcategory,
        'class'        => 'input-small'
    );

    $searchfield = array(
        'type' => 'submit',
        'class' => 'btn-secondary input-group-append no-label button',
        'value' => get_string('search')
    );

    $elements['formgroupcategory'] = array(
        'type' => 'fieldset',
        'class' => 'input-group',
        'elements' => array(
            'groupcategory' => $groupcategoryfield,
            'search' => $searchfield
        )
    );

}
else {

    $elements['searchfield'] = array(
        'type' => 'submit',
        'class' => 'btn-secondary no-label',
        'value' => get_string('search')
    );

}


$searchform = pieform(array(
    'name'   => 'search',
    'checkdirtychange' => false,
    'method' => 'post',
    'class' => 'form-inline with-heading',
    'elements' => $elements
    )
);

$groups = array();
if ($searchmode == 'mygroups') {
    $results = group_get_associated_groups($USER->get('id'), $type, $groupsperpage, $offset, $groupcategory, $query);
    $groups['data'] = isset($results['groups']) ? $results['groups'] : array();
    $groups['count'] = isset($results['count']) ? $results['count'] : 0;
}
else {
    if (is_isolated() && !($USER->get('admin') || $USER->get('staff'))) {
        $groups = search_group($query, $groupsperpage, $offset, $type, $groupcategory, $USER->get('institutions'));
    }
    else {
        $groups = search_group($query, $groupsperpage, $offset, $type, $groupcategory);
    }
}

// gets more data about the groups found by search_group
// including type if the user is associated with the group in some way
if ($searchmode == 'find') {
    if ($groups['data']) {
        $groups['data'] = group_get_extended_data($groups['data']);
    }
}

group_prepare_usergroups_for_display($groups['data']);

$params = array();
$params['filter'] = $filter;

if ($groupcategory != 0) {
    $params['groupcategory'] = $groupcategory;
}
if ($query) {
    $params['query'] = $query;
}
$paramsurl = get_config('wwwroot') . 'group/index.php' . ($params ? ('?' . http_build_query($params)) : '');
$pagination = build_pagination(array(
    'url' => $paramsurl,
    'count' => $groups['count'],
    'limit' => $groupsperpage,
    'offset' => $offset,
    'datatable' => 'findgroups',
    'jsonscript' => 'group/index.json.php',
    'setlimit' => true,
    'jumplinks' => 6,
    'numbersincludeprevnext' => 2,
    'resultcounttextsingular' => get_string('group', 'group'),
    'resultcounttextplural' => get_string('groups', 'group'),
));

function search_submit(Pieform $form, $values) {
    redirect('/group/index.php?filter=' . $values['filter'] . ((isset($values['query']) && ($values['query'] != '')) ? '&query=' . urlencode($values['query']) : '') . (!empty($values['groupcategory']) ? '&groupcategory=' . intval($values['groupcategory']) : ''));
}

$labelfilter = array(
    'name' => 'dummy',
    'checkdirtychange' => false,
    'elements' => array (
        'grouplabelfilter' => array(
            'type'          => 'autocomplete',
            'title'         => get_string('filterbygrouplabel', 'group'),
            'ajaxurl'       => get_config('wwwroot') . 'group/addlabel.json.php',
            'multiple'      => true,
            'initfunction'  => 'translate_landingpage_to_tags',
            'ajaxextraparams' => array(),
            'extraparams' => array('tags' => false),
            'defaultvalue'  => $activegrouplabels,
            'mininputlength' => 2,
        )
    ),
);
$labelfilterform = pieform($labelfilter);

$smarty = smarty(array('paginator'));
setpageicon($smarty, 'icon-comments');
$smarty->assign('groups', $groups['data']);
$smarty->assign('paramsurl', $paramsurl);
$smarty->assign('activegrouplabels', $showactivegrouplabels ? $labelfilterform : false);
$smarty->assign('cancreate', group_can_create_groups());
$html = $smarty->fetch('group/mygroupresults.tpl');
$smarty->assign('groupresults', $html);
$smarty->assign('form', $searchform);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('pagination_js', $pagination['javascript']);
$smarty->display('group/index.tpl');

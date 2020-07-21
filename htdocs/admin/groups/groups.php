<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'managegroups/groups');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

define('TITLE', get_string('administergroups', 'admin'));

require_once('group.php');
require_once('searchlib.php');

$query = param_variable('query', '');
$offset = param_integer('offset', 0);
$limit = param_integer('limit', 0);
$limit = user_preferred_limit($limit, 'itemsperpage');
$institution = param_alphanum('institution', null);
$groupcategory = param_signed_integer('groupcategory', 0);


// Build the institution select field that sits behind the search field
$inst_select = array();
$institution = !$institution ? 'all' : $institution;
$institutions = get_records_array('institution', '', '', 'displayname');
$inst_select['all'] = get_string('Allinstitutions');
if (is_array($institutions)) {
    foreach ($institutions as $inst) {
        $inst_select[$inst->name] = $inst->displayname;
    }
}

$inst_options = array();
$inst_options['all'] = get_string('Allinstitutions');

foreach ($institutions as $inst) {
    $inst_options[$inst->name] = $inst->displayname;
}
$count = 0;
$data = build_grouplist_html($query, $limit, $offset, $count, $institution, $groupcategory);

$elements = array();

//dropdown with search
$queryfield = array(
    'title' => get_string('search') . ': ',
    'hiddenlabel' => false,
    'type'  => 'text',
    'defaultvalue' => $query,
    'class' => 'with-dropdown js-with-dropdown',
);

$filterfield = array(
    'title' => get_string('Institution', 'admin'),
    'hiddenlabel' => false,
    'type' => 'select',
    'class' => 'dropdown-connect js-dropdown-connect',
    'options' => $inst_options,
    'defaultvalue' => $institution
);

$elements['searchwithin'] = array(
    'type' => 'fieldset',
    'class' => 'dropdown-group js-dropdown-group',
    'elements' => array(
        'query' => $queryfield,
        'filter' => $filterfield
    )
);

$options = array();
$options[0] = get_string('allcategories', 'group');
$options[-1] = get_string('notcategorised', 'group');

if ($groupcategories = get_records_menu('group_category','','','displayorder', 'id,title')) {
    $options += $groupcategories;
}

$groupcategoryfield = array(
    'title'        => get_string('groupcategory', 'group'). ': ',
    'hiddenlabel'  => false,
    'type'         => 'select',
    'options'      => $options,
    'defaultvalue' => $groupcategory,
    'class'        => 'input-small'
);

$searchfield = array(
    'type'  => 'submit',
    'usebuttontag' => true,
    'class' => 'btn-secondary input-group-append no-label button',
    'value' => get_string('search'),
);

$elements['formgroupcategory'] = array(
    'type' => 'fieldset',
    'class' => 'form input-group',
    'elements' => array(
        'groupcategory' => $groupcategoryfield
    )
);

$elements['search'] = $searchfield;

$searchform = pieform(array(
    'name'   => 'search',
    'renderer' => 'div',
    'method' => 'post',
    'class' => 'form-inline with-heading dropdown admin-user-search',
    'autofocus' => false,
    'elements' => $elements
)
);

$js = <<< EOF
jQuery(function() {
  p = {$data['pagination_js']}
  jQuery('#search_submit').on('click', function(event) {
    jQuery('#messages').empty();
    var params = {'query': jQuery('#search_query').val(),
                  'institution': jQuery('#search_institution').val()};
    p.sendQuery(params);
    event.preventDefault();
  });
});
EOF;

$smarty = smarty(array('paginator'));
setpageicon($smarty, 'icon-users-cog');

$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('searchform', $searchform);
$smarty->assign('results', $data);
$smarty->display('admin/groups/groups.tpl');

function search_submit(Pieform $form, $values) {
    $search = (isset($values['query']) && $values['query'] != '') ? urlencode($values['query']) : null;
    $institution = (isset($values['filter']) && $values['filter'] != '') ? urlencode($values['filter']) : null;
    $groupcategory = (!empty($values['groupcategory']) ? '&groupcategory=' . intval($values['groupcategory']) : '' );
    $query = '?search=1&query=' . $search . '&institution=' . $institution . '&groupcategory' . $groupcategory;
    redirect(get_config('wwwroot') . 'admin/groups/groups.php' . $query);
}

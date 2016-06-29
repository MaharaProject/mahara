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
$count = 0;
$data = build_grouplist_html($query, $limit, $offset, $count, $institution);

$searchform = pieform(array(
    'name'   => 'search',
    'renderer' => 'div',
    'class' => 'form-inline with-heading dropdown admin-user-search',
    'autofocus' => false,
    'elements' => array(
        'inputgroup' => array(
            'type'  => 'fieldset',
            'class' => 'dropdown-group js-dropdown-group',
            'elements'     => array(
                'query' => array(
                    'type'  => 'text',
                    'defaultvalue' => $query,
                    'class' => 'with-dropdown js-with-dropdown',
                    'title' => get_string('search') . ': ',
                ),
                'institution' => array(
                    'type'         => 'select',
                    'title'        => get_string('Institution', 'admin'),
                    'defaultvalue' => $institution,
                    'options'      => $inst_select,
                    'class'        => 'dropdown-connect js-dropdown-connect',
                ),
            ),
        ),
        'submit' => array(
            'type'  => 'button',
            'usebuttontag' => true,
            'class' => 'btn-search btn btn-primary admin-groups',
            'value' => get_string('search'),
        )
    ),
));

$js = <<< EOF
addLoadEvent(function () {
p = {$data['pagination_js']}
connect('search_submit', 'onclick', function (event) {
    replaceChildNodes('messages');
    var params = {'query': $('search_query').value,
                  'institution': $('search_institution').value};
    p.sendQuery(params);
    event.stop();
    });
});
EOF;

$smarty = smarty(array('paginator'));
setpageicon($smarty, 'icon-users');

$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('searchform', $searchform);
$smarty->assign('results', $data);
$smarty->display('admin/groups/groups.tpl');

function search_submit(Pieform $form, $values) {
    $search = (isset($values['query']) && $values['query'] != '') ? 'query=' . urlencode($values['query']) : null;
    $institution = (isset($values['institution']) && $values['institution'] != '') ? urlencode($values['institution']) : null;
    $query = '?search=1&query=' . $search . '&institution=' . $institution;
    redirect(get_config('wwwroot') . 'admin/groups/groups.php' . $query);
}

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
define('INSTITUTIONALSTAFF', 1);
define('MENUITEM', 'configusers/usersearch');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('usersearch', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'usersearch');
require_once('searchlib.php');

$search = (object) array(
    'query'          => trim(param_variable('query', '')),
    'f'              => param_alpha('f', null), // first initial
    'l'              => param_alpha('l', null), // last initial
    'sortby'         => param_alpha('sortby', 'firstname'),
    'sortdir'        => param_alpha('sortdir', 'asc'),
    'loggedin'       => param_alpha('loggedin', 'any'),
    'loggedindate'   => param_variable('loggedindate', strftime(get_string('strftimedatetimeshort'))),
    'duplicateemail' => param_boolean('duplicateemail', false),
);

$offset  = param_integer('offset', 0);
$limit   = param_integer('limit', 10);

if ($USER->get('admin') || $USER->get('staff')) {
    $institutions = get_records_array('institution', '', '', 'displayname');
    $search->institution = param_alphanum('institution', 'all');
}
else {
    $institutionnames = array_keys(array_merge($USER->get('admininstitutions'), $USER->get('staffinstitutions')));
    $institutions = get_records_select_array(
        'institution',
        'name IN (' . join(',', array_fill(0, count($institutionnames), '?')) . ')',
        $institutionnames,
        'displayname'
    );
}

$loggedintypes = array();
$loggedintypes[] = array('name' => 'any', 'string' => get_string('anyuser', 'admin'));
$loggedintypes[] = array('name' => 'ever', 'string' => get_string('usershaveloggedin', 'admin'));
$loggedintypes[] = array('name' => 'never', 'string' => get_string('usershaveneverloggedin', 'admin'));
$loggedintypes[] = array('name' => 'since', 'string' => get_string('usershaveloggedinsince', 'admin'));
$loggedintypes[] = array('name' => 'notsince', 'string' => get_string('usershavenotloggedinsince', 'admin'));

$calendar = array(
    'name' => 'loggedindate',
    'id' => 'loggedindate',
    'tabindex' => false,
    'type' => 'calendar',
    'title' => get_string('date'),
    'imagefile' => $THEME->get_image_url('btn_calendar'),
    'defaultvalue' => strtotime($search->loggedindate),
    'caloptions'   => array(
        'showsTime'      => true,
        'ifFormat'       => get_string('strftimedatetimeshort'),
    ),
);
$calendarform = new Pieform(array(
    'name' => 'loggedinform',
    'elements' => array(
        'loggedindate' => $calendar,
    ),
));
$calendarform->include_plugin('element', 'calendar');
$loggedindate = pieform_element_calendar($calendarform, $calendar);

list($html, $columns, $pagination, $search) = build_admin_user_search_results($search, $offset, $limit);

$js = <<<EOF
addLoadEvent(function() {
    var p = {$pagination['javascript']}

    new UserSearch(p);
})
EOF;

$smarty = smarty(array('adminusersearch', 'paginator'), array(), array('ascending' => 'mahara', 'descending' => 'mahara'));
$smarty->assign('search', $search);
$smarty->assign('limit', $limit);
$smarty->assign('loggedintypes', $loggedintypes);
$smarty->assign('loggedindate', $loggedindate);
$smarty->assign('alphabet', explode(',', get_string('alphabet')));
$smarty->assign('institutions', $institutions);
$smarty->assign('results', $html);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('columns', $columns);
$smarty->assign('searchurl', $search['url']);
$smarty->assign('sortby', $search['sortby']);
$smarty->assign('sortdir', $search['sortdir']);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('admin/users/search.tpl');

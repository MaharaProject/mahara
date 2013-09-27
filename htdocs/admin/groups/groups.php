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
$limit = 10;

$data = build_grouplist_html($query, $limit, $offset);

$searchform = pieform(array(
    'name'   => 'search',
    'renderer' => 'oneline',
    'elements' => array(
        'query' => array(
            'type' => 'text',
            'defaultvalue' => $query,
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('search'),
        ),
    ),
));

$js = <<< EOF
addLoadEvent(function () {
p = {$data['pagination_js']}
connect('search_submit', 'onclick', function (event) {
    replaceChildNodes('messages');
    var params = {'query': $('search_query').value};
    p.sendQuery(params);
    event.stop();
    });
});
EOF;

$smarty = smarty(array('paginator'));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('searchform', $searchform);
$smarty->assign('results', $data);
$smarty->display('admin/groups/groups.tpl');

function search_submit(Pieform $form, $values) {
    redirect(get_config('wwwroot') . 'admin/groups/groups.php' . ((isset($values['query']) && ($values['query'] != '')) ? '?query=' . urlencode($values['query']) : ''));
}

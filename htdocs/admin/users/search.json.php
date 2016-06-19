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
define('JSON', 1);
define('INSTITUTIONALSTAFF', 1);
define('SECTION_PAGE', 'usersearch');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('searchlib.php');

$params = new StdClass;
$params->query          = trim(param_variable('query', ''));
$params->institution    = param_alphanum('institution', null);
$params->f              = param_alpha('f', null);
$params->l              = param_alpha('l', null);
$params->sortby         = param_alpha('sortby', 'firstname');
$params->sortdir        = param_alpha('sortdir', 'asc');
$params->loggedin       = param_alpha('loggedin', 'any');
$params->loggedindate   = param_variable('loggedindate', null);
$params->duplicateemail = param_boolean('duplicateemail', false);

$offset  = param_integer('offset', 0);
$limit   = param_integer('limit', 10);

list($html, $columns, $pagination, $search) = build_admin_user_search_results($params, $offset, $limit);

json_reply(false, array(
    'message' => null,
    'data' => array(
        'tablerows' => $html,
        'pagination' => $pagination['html'],
        'pagination_js' => $pagination['javascript']
    )
));

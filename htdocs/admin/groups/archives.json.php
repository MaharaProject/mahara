<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
define('INSTITUTIONALADMIN', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('searchlib.php');

$search_params = [
    'type' => param_exists('current') ? 'current' : 'archived',
    'query' => trim(param_variable('query', '')),
    'institution' => trim(param_alphanum('institution', null)),
    'sortby' => param_alpha('sortby', 'firstname'),
    'sortdir' => param_alpha('sortdir', 'asc'),
];

$offset = param_integer('offset', 0);
$limit = param_integer('limit', 10);

list($html, $columns, $pagination, $search) = build_admin_archived_submissions_results($search_params, $offset, $limit);

json_reply(false, array(
    'message' => null,
    'data' => array(
        'tablerows' => $html,
        'pagination' => $pagination['html'],
        'pagination_js' => $pagination['javascript']
    )
));

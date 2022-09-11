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
define('PUBLIC', 1);
define('JSON', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once(get_config('libroot') . 'searchlib.php');
require_once(get_config('libroot') . 'group.php');
require_once(get_config('docroot') . 'artefact/lib.php');
require_once(get_config('docroot') . 'artefact/file/lib.php');
require_once(get_config('libroot') . 'group.php');
require_once(get_config('docroot') . 'search/elasticsearch7/lib/PluginSearchElasticsearch7.php');

global $USER;
if (!get_config('publicsearchallowed') && !$USER->is_logged_in()) {
    throw new AccessDeniedException();
}

$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);
$filter = param_alpha('filter', 'all');
$type   = param_variable('type', '');
$mainfacetterm   = param_alpha('mainfacetterm', null);
$options = array();
$options['secfacetterm'] = param_alpha('secfacetterm', null);
$options['owner'] = param_alpha('owner', null);
$options['sort'] = param_alphanumext('sort', null);
$options['license'] = param_variable('license', '');

$tagsonly = param_boolean('tagsonly');
if ($tagsonly === true) {
    $options['tagsonly'] = true;
}

$query = param_variable('query', '');
if (!empty($query)) {
    $query = clean_str_replace($query, ' ', array("'", '@', '&', '*', '|'));
}

$data = PluginSearchElasticsearch7::search_all($query, $limit, $offset, $options, $mainfacetterm);
$data['query'] = $query;
$data['limit'] = $limit;

// License
if (get_config('licensemetadata')) {
    $data['license_on'] = true;
    $license_options = array();
    $licenses = get_records_assoc('artefact_license', '', '', 'displayname');
    foreach ($licenses as $l) {
        $license_options[$l->name] = $l->displayname;
    }
    $data['license_options'] = $license_options;
}

PluginSearchElasticsearch7::build_results_html($data);

unset($data['data']);
// Until json_reply is annotated correctly
json_reply(false, array('data' => $data));

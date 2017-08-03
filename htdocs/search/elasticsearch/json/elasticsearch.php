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
define('PUBLIC', 1);
define('JSON', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once(get_config('libroot') . 'searchlib.php');
require_once(get_config('libroot') . 'group.php');
require_once(get_config('docroot') . 'artefact/lib.php');
require_once(get_config('docroot') . 'artefact/file/lib.php');
require_once(get_config('libroot') . 'group.php');
require_once(get_config('docroot') . 'search/elasticsearch/lib.php');

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

$tagsonly = param_boolean('tagsonly', false);
if ($tagsonly === true) {
    $options['tagsonly'] = true;
}

try {
    $query = param_variable('query');
}
catch (ParameterException $e) {
    json_reply('missingparameter','Missing parameter \'query\'');
}

$query = PluginSearchElasticsearch::clean_query($query);
$data = PluginSearchElasticsearch::search_all($query, $limit, $offset, $options, $mainfacetterm);
$data['query'] = $query;
$data['limit'] = $limit;

// License
if (get_config('licensemetadata')) {
    $data['license_on'] = true;
    $license_options = array();
    $licenses = get_records_assoc('artefact_license', null, null, 'displayname');
    foreach ($licenses as $l) {
        $license_options[$l->name] = $l->displayname;
    }
    $data['license_options'] = $license_options;
}

PluginSearchElasticsearch::build_results_html($data);

unset($data['data']);
json_reply(false, array('data' => $data));

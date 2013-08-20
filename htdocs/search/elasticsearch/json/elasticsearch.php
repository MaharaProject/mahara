<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once(get_config('libroot') . 'pieforms/pieform.php');
require_once(get_config('libroot') . 'searchlib.php');
require_once(get_config('libroot') . 'group.php');
require_once(get_config('docroot') . 'artefact/lib.php');
require_once(get_config('docroot') . 'artefact/file/lib.php');
require_once(get_config('libroot') . 'group.php');
require_once(get_config('docroot') . 'search/elasticsearch/lib.php');

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

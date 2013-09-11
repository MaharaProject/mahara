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

//require_once(get_config('docroot') . 'lib/Elastica/lib/Elastica/Client.php');

define('INTERNAL', 1);
define('MENUITEM', '');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'pieforms/pieform.php');
require_once(get_config('libroot') . 'searchlib.php');
// to get the license select list
require_once(get_config('libroot') . 'license.php');
// required to generate the thumbnail for artefact images
require_once(get_config('docroot') . 'artefact/lib.php');
require_once(get_config('docroot') . 'artefact/file/lib.php');
require_once(get_config('libroot') . 'group.php');

define('TITLE', get_string('pagetitle', 'search.elasticsearch'));

safe_require('search', 'elasticsearch');
define('SECTION_PLUGINTYPE', 'search');
define('SECTION_PLUGINNAME', 'elasticsearch');
define('SECTION_PAGE', 'elasticsearch');

global $USER;
$options = array();

$query = param_variable('query', '');
$mainfacetterm = param_alpha('mainfacetterm', null);
$options['secfacetterm'] = param_alpha('secfacetterm', '');
$options['owner'] = param_alpha('owner', '');
$options['tagsonly'] = param_boolean('tagsonly', false);
$options['sort'] = param_alphanumext('sort', null);
$options['license'] = param_variable('license', '');

$offset = param_integer('offset', 0);
$filter = param_alpha('filter', $USER->get('admin') ? 'all' : 'myinstitutions');
$limit  = param_integer('limit', 10);

$filter = 'all';

$query = PluginSearchElasticsearch::clean_query($query);
$data = PluginSearchElasticsearch::search_all($query, $limit, $offset, $options, $mainfacetterm, $USER);

$data['query'] = $query;

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

$searchform = array(
    'name' => 'search',
    'renderer' => 'oneline',
    'checkdirtychange' => false,
    'elements' => array(),
);

$searchform['elements']['query'] = array(
    'type' => 'text',
    'defaultvalue' => $query,
);
$searchform['elements']['submit'] = array(
    'type' => 'submit',
    'value' => get_string('search'),
);
$searchform['elements']['tagsonly'] = array(
    'type'         => 'checkbox',
    'value'        => (isset($options['tagsonly']) && $options['tagsonly'] == true) ? true : false,
    'posthtml'     => get_string('tagsonly', 'search.elasticsearch'),
);

$searchform = pieform($searchform);

$js = <<< EOF
addLoadEvent(function () {
    p = {$data['pagination_js']}
    connect('search_submit', 'onclick', function (event) {
        replaceChildNodes('messages');
        var params = {'query': $('search_query').value, 'tagsonly': $('search_tagsonly').checked, 'limit': $('setlimitselect').value, 'extradata':serializeJSON({'page':'index'})};
        p.sendQuery(params);
        event.stop();
    });
});
EOF;

$javascript = array('paginator');

$smarty = smarty($javascript, array(), array('applychanges' => 'mahara', 'nogroups' => 'group'), array('sideblocks' => array()));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('results', $data);
$smarty->assign('form', $searchform);
$smarty->display('Search:elasticsearch:search_layout.tpl');
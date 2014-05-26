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

//require_once(get_config('docroot') . 'lib/Elastica/lib/Elastica/Client.php');

define('INTERNAL', 1);
define('MENUITEM', '');
define('PUBLIC', 1);
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
if (!get_config('publicsearchallowed') && !$USER->is_logged_in()) {
    throw new AccessDeniedException();
}

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
    'title'        => get_string('pagetitle', 'search.elasticsearch'),
    'hiddenlabel'  => true,
);
$searchform['elements']['submit'] = array(
    'type' => 'submit',
    'value' => get_string('search'),
);
$searchform['elements']['tagsonly'] = array(
    'type'         => 'checkbox',
    'value'        => (isset($options['tagsonly']) && $options['tagsonly'] == true) ? true : false,
    'title'        => get_string('tagsonly', 'search.elasticsearch'),
);

$searchform = pieform($searchform);

$js = <<< EOF
addLoadEvent(function () {
    var firstpage = false;

    function SearchPager() {
        var self = this;
        paginatorProxy.addObserver(self);
        connect(self, 'pagechanged', function() {
            if (firstpage) {
                firstpage = false;
                addElementClass('totalresultsdisplay', 'hidefocus');
                setNodeAttribute('totalresultsdisplay', 'tabindex', -1);
                $('totalresultsdisplay').focus();
            }
            else {
                getFirstElementByTagAndClassName('a', null, $('universalsearchresults')).focus();
            }
        });
    }
    var searchPager = new SearchPager();

    p = {$data['pagination_js']}
    connect('search_submit', 'onclick', function (event) {
        firstpage = true;
        replaceChildNodes('messages');
        var params = {'query': $('search_query').value, 'tagsonly': $('search_tagsonly').checked, 'limit': $('setlimitselect').value, 'extradata':serializeJSON({'page':'index'})};
        p.sendQuery(params);
        event.stop();
    });
});
EOF;

if (!empty($query)) {
    $js .= <<< EOF
addLoadEvent(function() {
    addElementClass('totalresultsdisplay', 'hidefocus');
    setNodeAttribute('totalresultsdisplay', 'tabindex', -1);
    $('totalresultsdisplay').focus();
});
EOF;
}

$javascript = array('paginator');

$smarty = smarty($javascript, array(), array('applychanges' => 'mahara', 'nogroups' => 'group'), array('sideblocks' => array()));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('results', $data);
$smarty->assign('form', $searchform);
$smarty->display('Search:elasticsearch:search_layout.tpl');

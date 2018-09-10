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

$searchoptions = array(
    'all' => get_string('All'),
    'tagsonly' => get_string('tagsonly', 'view'),
);

if (isset($options['tagsonly']) && $options['tagsonly'] == true) {
    $selectvalue = 'tagsonly';
}
else {
    $selectvalue = 'all';
}

$searchform = array(
    'name' => 'search',
    'renderer' => 'div',
    'checkdirtychange' => false,
    'class' => 'form-inline with-heading elasticsearch-form',
    'elements' => array(
        'searchwithin' => array (
            'type' => 'fieldset',
            'class' => 'dropdown-group js-dropdown-group',
            'elements' => array(
                'query' => array(
                    'type' => 'text',
                    'title' => get_string('pagetitle', 'search.elasticsearch') . ': ',
                    'class' => 'with-dropdown js-with-dropdown',
                    'defaultvalue' => $query,
                ),
                'tagsonly' => array(
                    'title' => get_string('searchwithin'). ': ',
                    'class' => 'dropdown-connect js-dropdown-connect searchviews-type',
                    'type' => 'select',
                    'options' => $searchoptions,
                    'value' => $selectvalue,
                ),
                'submit' => array(
                    'type' => 'submit',
                    'class' => 'btn-primary no-label input-group',
                    'value' => get_string('search'),
                )
            )
        ),
    ),
);

$searchform = pieform($searchform);

$js = <<< EOF
jQuery(function ($) {
    var firstpage = false;

    function SearchPager() {
        var self = this;
        paginatorProxy.addObserver(self);
        $(self).on('pagechanged', function() {
            if (firstpage) {
                firstpage = false;
                $('#totalresultsdisplay').addClass('hidefocus')
                  .prop('tabindex', -1)
                  .trigger("focus");
            }
            else {
                $('#universalsearchresults a:first').trigger("focus");
            }
        });
    }
    var searchPager = new SearchPager();

    p = {$data['pagination_js']}
    $('#search_submit').on('click', function (event) {
        firstpage = true;
        $('#messages').empty();

        if ($('#search_tagsonly').val() === 'tagsonly') {
            var tagsonly = true;
        }
        else {
            var tagsonly = false;
        }

        var params = {'query': $('#search_query').val(), 'tagsonly': tagsonly, 'mainfacetterm': null,
                      'offset': 0, 'limit': $('#setlimitselect').val(), 'extradata':JSON.stringify({'page':'index'})};
        p.sendQuery(params);
        event.preventDefault();
    });
});
EOF;

if (!empty($query)) {
    $js .= <<< EOF
jQuery(function($) {
    $('#totalresultsdisplay').addClass('hidefocus')
        .prop('tabindex', -1)
        .trigger("focus");
});
EOF;
}

$javascript = array('paginator');

$smarty = smarty($javascript, array(), array('applychanges' => 'mahara', 'nogroups' => 'group'));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('results', $data);
$smarty->assign('form', $searchform);
$smarty->display('Search:elasticsearch:search_layout.tpl');

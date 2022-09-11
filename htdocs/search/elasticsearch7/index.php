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
define('MENUITEM', '');
define('PUBLIC', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'searchlib.php');
// To get the license select list.
require_once(get_config('libroot') . 'license.php');
// Required to generate the thumbnail for artefact images.
require_once(get_config('docroot') . 'artefact/lib.php');
require_once(get_config('docroot') . 'artefact/file/lib.php');
require_once(get_config('libroot') . 'group.php');

define('TITLE', get_string('pagetitle', 'search.elasticsearch7'));

safe_require('search', 'elasticsearch7');
define('SECTION_PLUGINTYPE', 'search');
define('SECTION_PLUGINNAME', 'elasticsearch7');
define('SECTION_PAGE', 'elasticsearch7');

global $USER;
if (!get_config('publicsearchallowed') && !$USER->is_logged_in()) {
    throw new AccessDeniedException();
}

$options = array();
$search_class = 'PluginSearchElasticsearch7';

$query = param_variable('query', '');
$mainfacetterm = param_alpha('mainfacetterm', null);
$options['secfacetterm'] = param_alpha('secfacetterm', '');
$options['owner'] = param_alpha('owner', '');
$options['tagsonly'] = param_boolean('tagsonly');
$options['sort'] = param_alphanumext('sort', null);
$options['license'] = param_variable('license', '');

$offset = param_integer('offset', 0);
$filter = param_alpha('filter', $USER->get('admin') ? 'all' : 'myinstitutions');
$limit  = param_integer('limit', 10);

$query = clean_str_replace($query, ' ', array("'", '@', '&', '*', '|'));
$data = PluginSearchElasticsearch7::search_all($query, $limit, $offset, $options, $mainfacetterm);

$data['query'] = $query;

// License.
// @TODO: License is not currently being filtered.
if (get_config('licensemetadata') && false) {
    $data['license_on'] = true;
    $license_options = array();
    $licenses = get_records_assoc('artefact_license', '', '', 'displayname');
    foreach ($licenses as $l) {
        $license_options[$l->name] = $l->displayname;
    }
    $data['license_options'] = $license_options;
}

PluginSearchElasticsearch7::build_results_html($data);

$searchoptions = array(
    'all' => get_string('All'),
    'tagsonly' => get_string('tagsonly', 'view'),
);

if ($options['tagsonly'] == true) {
    $selectvalue = 'tagsonly';
}
else {
    $selectvalue = 'all';
}

$searchform = array(
    'name' => 'search',
    'renderer' => 'div',
    'checkdirtychange' => false,
    'class' => 'form-inline with-heading elasticsearch7-form',
    'elements' => array(
        'searchwithin' => array (
            'type' => 'fieldset',
            'class' => 'dropdown-group js-dropdown-group',
            'elements' => array(
                'query' => array(
                    'type' => 'text',
                    'title' => get_string('pagetitle', 'search.elasticsearch7') . ': ',
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
                    'class' => 'btn-secondary no-label input-group',
                    'value' => get_string('search'),
                )
            )
        ),
    ),
);

// If the current search class can tweak the searchform, do that now.
if (method_exists($search_class, 'tweak_searchform')) {
    $search_class::tweak_searchform($searchform);
}

$searchform = pieform($searchform);
$pagination_js = '';
if (array_key_exists('pagination_js', $data)) {
    $pagination_js = $data['pagination_js'];
}

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
                $('#universalsearchresults a').first().trigger("focus");
            }
        });
    }
    var searchPager = new SearchPager();

    p = {$pagination_js}
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

// If the current search class can tweak the javascript being loaded, do that
// now.
if (method_exists($search_class, 'tweak_searchform_js')) {
    $search_class::tweak_searchform_js($javascript);
}

$smarty = smarty($javascript, array(), array('applychanges' => 'mahara', 'nogroups' => 'group'));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('results', $data);
$smarty->assign('form', $searchform);
$smarty->display('search:elasticsearch7:search_layout.tpl');

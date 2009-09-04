<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio');
require('init.php');
require('searchlib.php');
define('TITLE', get_string('mytags'));


$tagsort = param_alpha('ts', null) != 'freq' ? 'alpha' : 'freq';
$tags = get_my_tags(null, false, $tagsort);
$tagsstr = json_encode($tags);

$tag = param_variable('tag', null);

$js = '';

$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);
$sort   = param_alpha('sort', 'name');
$owner = (object) array('type' => 'user', 'id' => $USER->get('id'));
$data = get_portfolio_items_by_tag($tag, $owner, $limit, $offset, $sort);
build_portfolio_search_html($data);

$hidepagination = $tag ? '' : "addElementClass('results_pagination', 'hidden');";
$js = <<<EOF
var p = null;
var mytags_container = null;
var inittags = {$tagsstr};
var mytags = {};

function sortTagAlpha(a, b) {
    var aid = getNodeAttribute(a, 'id');
    var bid = getNodeAttribute(b, 'id');
    return aid < bid ? -1 : (aid > bid ? 1 : 0);
}

function sortTagFreq(a, b) {
    var aid = getNodeAttribute(a, 'id');
    var bid = getNodeAttribute(b, 'id');
    return mytags[bid] - mytags[aid];
}

var sort_functions = {'alpha': sortTagAlpha, 'freq': sortTagFreq};

function rewriteTagSortLink(elem) {
    connect(elem, 'onclick', function(e) {
        e.stop();
        var href = getNodeAttribute(this, 'href');
        var params = parseQueryString(href.substring(href.indexOf('?')+1, href.length));
        var elems = getElementsByTagAndClassName('a', 'tag', mytags_container);
        elems.sort(sort_functions[params.ts]);
        replaceChildNodes(mytags_container, elems);

        forEach(getElementsByTagAndClassName('a', 'current-tab'), function(selected) {
            removeElementClass(selected, 'current-tab');
        });
        addElementClass(this, 'current-tab');

        return false;
    });
}

function rewriteTagLink(elem) {
    disconnectAll(elem);
    connect(elem, 'onclick', function(e) {
        e.stop();
        var href = getNodeAttribute(this, 'href');
        var params = parseQueryString(href.substring(href.indexOf('?')+1, href.length));
        sendjsonrequest(config.wwwroot + 'json/tagsearch.php', params, 'POST', function(data) {
            p.updateResults(data);

            // Update tag links in the My Tags list:
            forEach(getElementsByTagAndClassName('a', 'selected', mytags_container), function(selected) {
                removeElementClass(selected, 'selected');
            });

            // Mark the selected tag in the My Tags list:
            addElementClass('tag:' + params.tag, 'selected');

            // Replace the tag in the Search Results heading
            var heading_tag = getFirstElementByTagAndClassName('a', 'tag', 'results_heading');
            if (heading_tag) {
                heading_tag.href = href;
                heading_tag.innerHTML = data.data.tagdisplay;
            }

            removeElementClass('results_container', 'hidden');

            // Rewrite tag links in the results list:
            forEach(getElementsByTagAndClassName('a', 'tag', 'results'), rewriteTagLink);

            // Rewrite Sort By links above the Search results:
            forEach(getElementsByTagAndClassName('a', null, 'results_sort'), function (a) {
                var href = getNodeAttribute(a, 'href');
                var params = parseQueryString(href.substring(href.indexOf('?')+1, href.length));
                params.tag = data.data.tag;
                setNodeAttribute(a, 'href', config.wwwroot + 'tags.php?' + queryString(params));
                if (hasElementClass(a, 'selected') && data.data.sort != params.sort) {
                    removeElementClass(a, 'selected');
                }
                else if (!hasElementClass(a, 'selected') && data.data.sort == params.sort) {
                    addElementClass(a, 'selected');
                }
                rewriteTagLink(a);
            });
        });
        return false;
    });
}

addLoadEvent(function() {
    forEach(inittags, function(t) {
        mytags['tag:' + t.tag] = t.count;
    });
    forEach(getElementsByTagAndClassName('a', 'tag-sort'), rewriteTagSortLink);

    mytags_container = getFirstElementByTagAndClassName(null, 'mytags', 'main-column-container');
    p = {$data->pagination_js}
    forEach(getElementsByTagAndClassName('a', 'tag', mytags_container), rewriteTagLink);
    forEach(getElementsByTagAndClassName('a', 'tag', 'sb-mytags'), rewriteTagLink);
    forEach(getElementsByTagAndClassName('a', 'tag', 'results'), rewriteTagLink);
    forEach(getElementsByTagAndClassName('a', null, 'results_sort'), rewriteTagLink);
    {$hidepagination}
});
EOF;

$tagsortoptions = array();
foreach (array('alpha', 'freq') as $option) {
    $tagsortoptions[$option] = $option == $tagsort;
}

$smarty = smarty(array('paginator'));
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('tags', $tags);
$smarty->assign('tagsortoptions', $tagsortoptions);
$smarty->assign('tag', $tag);
$smarty->assign_by_ref('results', $data);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('tags.tpl');
?>

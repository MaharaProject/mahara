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
define('MENUITEM', 'myportfolio');
require('init.php');
require_once('searchlib.php');
define('TITLE', get_string('mytags'));


$tagsort = param_alpha('ts', null) != 'freq' ? 'alpha' : 'freq';
$tags = get_my_tags(null, false, $tagsort);

$tag    = param_variable('tag', null);

$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);
$sort   = param_alpha('sort', 'name');
$type   = param_alpha('type', null);
$owner  = (object) array('type' => 'user', 'id' => $USER->get('id'));

$data = get_portfolio_items_by_tag($tag, $owner, $limit, $offset, $sort, $type);
build_portfolio_search_html($data);

$str = array();
foreach (array('tags', 'tag', 'sort', 'type') as $v) {
    $str[$v] = json_encode($$v);
}

$js = <<<EOF
var p = null;
var mytags_container = null;
var inittags = {$str['tags']};
var mytags = {};

var params = {
    'tag': {$str['tag']},
    'sort': {$str['sort']},
    'type': {$str['type']}
};

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

        // FF needs spaces in between each element for wrapping
        replaceChildNodes(mytags_container, []);
        forEach(elems, function (a) {
            appendChildNodes(mytags_container, a, ' ');
        });

        forEach(getElementsByTagAndClassName('a', 'current-tab'), function(selected) {
            removeElementClass(selected, 'current-tab');
        });
        addElementClass(this, 'current-tab');

        return false;
    });
}

function rewriteTagLink(elem, keep, replace) {
    disconnectAll(elem);
    connect(elem, 'onclick', function(e) {
        e.stop();
        var href = getNodeAttribute(this, 'href');
        var hrefparams = parseQueryString(href.substring(href.indexOf('?')+1, href.length));
        var reqparams = {};
        reqparams[replace] = hrefparams[replace];
        for (var i = 0; i < keep.length; i++) {
            if (params[keep[i]]) {
                reqparams[keep[i]] = params[keep[i]];
            }
        }

        sendjsonrequest(config.wwwroot + 'json/tagsearch.php', reqparams, 'POST', function(data) {
            p.updateResults(data);

            if (data.data.tag != params.tag) {

                // Update tag links in the My Tags list:
                forEach(getElementsByTagAndClassName('a', 'selected', mytags_container), function(selected) {
                    removeElementClass(selected, 'selected');
                });

                // Mark the selected tag in the My Tags list:
                if (data.data.tag) {
                    addElementClass('tag:' + data.data.tagurl, 'selected');
                }

                // Replace the tag in the Search Results heading
                var heading_tag = getFirstElementByTagAndClassName('a', 'tag', 'results_heading');
                if (heading_tag) {
                    setNodeAttribute(heading_tag, 'href', href);
                    heading_tag.innerHTML = data.data.tagdisplay;
                }
                var edit_tag_link = getFirstElementByTagAndClassName('a', 'edit-tag', 'results_container');
                if (edit_tag_link) {
                    if (data.data.tag) {
                        setNodeAttribute(edit_tag_link, 'href', config.wwwroot + 'edittags.php?tag=' + data.data.tagurl);
                        removeElementClass(edit_tag_link, 'hidden');
                    }
                    else {
                        addElementClass(edit_tag_link, 'hidden');
                    }
                }

                if (data.data.tag) {
                    params.tag = data.data.tag;
                }
            }

            // Rewrite tag links in the results list:
            forEach(getElementsByTagAndClassName('a', 'tag', 'results'), function (elem) {rewriteTagLink(elem, [], 'tag')});

            // Change selected Sort By links above the Search results:
            if (data.data.sort != params.sort) {
                forEach(getElementsByTagAndClassName('a', null, 'results_sort'), function (a) {
                    var href = getNodeAttribute(a, 'href');
                    var hrefparams = parseQueryString(href.substring(href.indexOf('?')+1, href.length));
                    if (hasElementClass(a, 'selected') && data.data.sort != hrefparams.sort) {
                        removeElementClass(a, 'selected');
                    }
                    else if (!hasElementClass(a, 'selected') && data.data.sort == hrefparams.sort) {
                        addElementClass(a, 'selected');
                    }
                });
                params.sort = data.data.sort;
            }

            // Change selected Filter By links above the Search results:
            if (data.data.type != params.type) {
                forEach(getElementsByTagAndClassName('a', null, 'results_filter'), function (a) {
                    var href = getNodeAttribute(a, 'href');
                    var hrefparams = parseQueryString(href.substring(href.indexOf('?')+1, href.length));
                    if (hasElementClass(a, 'selected') && data.data.type != hrefparams.type) {
                        removeElementClass(a, 'selected');
                    }
                    else if (!hasElementClass(a, 'selected') && data.data.type == hrefparams.type) {
                        addElementClass(a, 'selected');
                    }
                });
                params.type = data.data.type;
            }

        });
        return false;
    });
}

addLoadEvent(function() {
    forEach(inittags, function(t) {
        mytags['tag:' + t.tagurl] = t.count;
    });
    forEach(getElementsByTagAndClassName('a', 'tag-sort'), rewriteTagSortLink);

    mytags_container = getFirstElementByTagAndClassName(null, 'mytags', 'main-column-container');
    p = {$data->pagination_js}
    forEach(getElementsByTagAndClassName('a', 'tag', mytags_container), function (elem) {rewriteTagLink(elem, [], 'tag')});
    forEach(getElementsByTagAndClassName('a', 'tag', 'sb-tags'), function (elem) {rewriteTagLink(elem, [], 'tag')});
    forEach(getElementsByTagAndClassName('a', 'tag', 'results'), function (elem) {rewriteTagLink(elem, [], 'tag')});
    forEach(getElementsByTagAndClassName('a', null, 'results_sort'), function (elem) {rewriteTagLink(elem, ['tag', 'type'], 'sort')});
    forEach(getElementsByTagAndClassName('a', null, 'results_filter'), function (elem) {rewriteTagLink(elem, ['tag', 'sort'], 'type')});
});
EOF;

$tagsortoptions = array();
foreach (array('alpha', 'freq') as $option) {
    $tagsortoptions[$option] = $option == $tagsort;
}

$smarty = smarty(array('paginator'));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('tags', $tags);
$smarty->assign('tagsortoptions', $tagsortoptions);
$smarty->assign('tag', $tag);
$smarty->assign_by_ref('results', $data);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('tags.tpl');

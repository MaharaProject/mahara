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

$tags = get_my_tags();
$tag = param_variable('tag', null);

$js = '';

$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);
$owner = (object) array('type' => 'user', 'id' => $USER->get('id'));
$data = get_portfolio_items_by_tag($tag, $owner, $limit, $offset);
build_portfolio_search_html($data);

$hidepagination = $tag ? '' : "addElementClass('results_pagination', 'hidden');";
$js = <<<EOF
var p = null;
var mytags_container = null;
function rewriteTagLink(elem) {
    disconnectAll(elem);
    connect(elem, 'onclick', function(e) {
        e.stop();
        var href = getNodeAttribute(this, 'href');
        var params = parseQueryString(href.substring(href.indexOf('?')+1, href.length));
        sendjsonrequest(config.wwwroot + 'json/tagsearch.php', params, 'POST', function(data) {
            p.updateResults(data);
            forEach(getElementsByTagAndClassName('a', 'selected', mytags_container), function(selected) {
                removeElementClass(selected, 'selected');
            });
            addElementClass('tag:' + params.tag, 'selected');
            var heading_tag = getFirstElementByTagAndClassName('a', 'tag', 'results_heading');
            if (heading_tag) {
                heading_tag.href = href;
                heading_tag.innerHTML = data.data.tagdisplay;
            }
            if (hasElementClass('results', 'hidden')) {
                removeElementClass('results', 'hidden');
                removeElementClass('results_heading', 'hidden');
                removeElementClass('results_pagination', 'hidden');
            }
            forEach(getElementsByTagAndClassName('a', 'tag', 'results'), rewriteTagLink);
        });
        return false;
    });
};
addLoadEvent(function() {
    mytags_container = getFirstElementByTagAndClassName(null, 'mytags', 'main-column-container');
    p = {$data->pagination_js}
    forEach(getElementsByTagAndClassName('a', 'tag', mytags_container), rewriteTagLink);
    forEach(getElementsByTagAndClassName('a', 'tag', 'sb-mytags'), rewriteTagLink);
    forEach(getElementsByTagAndClassName('a', 'tag', 'results'), rewriteTagLink);
    {$hidepagination}
});
EOF;

$smarty = smarty(array('paginator'));
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('tags', $tags);
$smarty->assign('tag', $tag);
$smarty->assign_by_ref('results', $data);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('tags.tpl');
?>

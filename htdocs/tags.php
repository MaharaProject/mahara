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
if ($tags && is_null($tag)) {
    $tag = $tags[0]->tag;
}

if ($tag) {
    $limit  = param_integer('limit', 10);
    $offset = param_integer('offset', 0);
    $owner = (object) array('type' => 'user', 'id' => $USER->get('id'));
    $data = get_portfolio_items_by_tag($tag, $owner, $limit, $offset);
    build_portfolio_search_html($data);
    $pagerjs = $data->pagination_js;
}
else {
    $pagerjs = 'var results_pager = new Paginator("portfoliosearch_pagination", "results", "json\\/tagsearch.php", null);';
}

$js .= <<<EOF
addLoadEvent(function() {
    {$pagerjs}
    forEach(getElementsByTagAndClassName('a', 'tag', 'main-column-container'), function(elem) {
        disconnectAll(elem);
        connect(elem, 'onclick', function(e) {
            e.stop();
            var href = getNodeAttribute(this, 'href');
            var params = parseQueryString(href.substring(href.indexOf('?')+1, href.length));
            sendjsonrequest(config.wwwroot + 'json/tagsearch.php', params, 'POST', function(data) {
                results_pager.updateResults(data);
                forEach(getElementsByTagAndClassName('a', 'selected', 'main-column-container'), function(selected) {
                    removeElementClass(selected, 'selected');
                });
                addElementClass(elem, 'selected');
            });
            return false;
        });
    });
});
EOF;

$smarty = smarty(array('paginator'));
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('tags', $tags);
if (!is_null($tag) && isset($data)) {
    $smarty->assign('tag', $tag);
    $smarty->assign_by_ref('results', $data);
}
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('tags.tpl');
?>

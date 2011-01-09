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
define('MENUITEM', 'groups/views');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('sharedviews', 'view'));

$query  = param_variable('query', null);
$tag    = param_variable('tag', null);
$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);

$searchoptions = array(
    'titleanddescription' => get_string('titleanddescription', 'view'),
    'tagsonly' => get_string('tagsonly', 'view'),
);
if (!empty($tag)) {
    $searchtype = 'tagsonly';
    $searchdefault = $tag;
    $querystring = '?tag=' . urlencode($tag);
    $query = null;
}
else {
    $searchtype = 'titleanddescription';
    $searchdefault = $query;
    $querystring = empty($query) ? '' : ('?query=' . urlencode($query));
}

$searchform = pieform(array(
    'name' => 'search',
    'renderer' => 'oneline',
    'elements' => array(
        'query' => array(
            'type' => 'text',
            'title' => get_string('search') . ': ',
            'defaultvalue' => $searchdefault,
        ),
        'type' => array(
            'type'         => 'select',
            'options'      => $searchoptions,
            'defaultvalue' => $searchtype,
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('search')
        )
    )
));

$data = View::shared_to_user($query, $tag, $limit, $offset);

$pagination = build_pagination(array(
    'id' => 'sharedviews_pagination',
    'url' => get_config('wwwroot') . 'view/sharedviews.php' . $querystring,
    'jsonscript' => '/json/sharedviews.php',
    'datatable' => 'sharedviewlist',
    'count' => $data->count,
    'limit' => $limit,
    'offset' => $offset,
));

$smarty = smarty(array('paginator'));
$smarty->assign('views', $data->data);
$smarty->assign('searchform', $searchform);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('INLINEJAVASCRIPT', 'addLoadEvent(function() {' . $pagination['javascript'] . '});');
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('view/sharedviews.tpl');
exit;

function search_submit(Pieform $form, $values) {
    $goto = '/view/sharedviews.php';
    if (!empty($values['query'])) {
        $querystring = $values['type'] == 'tagsonly' ? '?tag=' : '?query=';
        $querystring .= urlencode($values['query']);
        $goto .= $querystring;
    }
    redirect($goto);
}

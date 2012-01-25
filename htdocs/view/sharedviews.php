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
define('MENUITEM', 'groups/sharedviews');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('sharedviews', 'view'));

$query  = param_variable('query', null);
$tag    = param_variable('tag', null);
$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);

$queryparams = array();

$searchoptions = array(
    'titleanddescription' => get_string('titleanddescription', 'view'),
    'tagsonly' => get_string('tagsonly', 'view'),
);
if (!empty($tag)) {
    $searchtype = 'tagsonly';
    $searchdefault = $tag;
    $queryparams['tag'] = $tag;
    $query = null;
}
else {
    $searchtype = 'titleanddescription';
    $searchdefault = $query;
    if (!empty($query)) {
        $queryparams['query'] = $query;
    }
}

$sortoptions = array(
    'lastchanged' => get_string('lastupdateorcomment'),
    'mtime'       => get_string('lastupdate'),
    'ownername'   => get_string('Owner', 'view'),
    'title'       => get_string('Title'),
);

if (!in_array($sort = param_alpha('sort', 'lastchanged'), array_keys($sortoptions))) {
    $sort = 'lastchanged';
}
if ($sort !== 'lastchanged') {
    $queryparams['sort'] = $sort;
}
$sortdir = ($sort == 'lastchanged' || $sort == 'mtime') ? 'desc' : 'asc';

$searchform = pieform(array(
    'name' => 'search',
    'dieaftersubmit' => false,
    'elements' => array(
        'query' => array(
            'type' => 'text',
            'title' => get_string('Query') . ': ',
            'defaultvalue' => $searchdefault,
        ),
        'type' => array(
            'type'         => 'select',
            'title'        => get_string('searchwithin') . ': ',
            'options'      => $searchoptions,
            'defaultvalue' => $searchtype,
        ),
        'sort' => array(
            'type'         => 'select',
            'title'        => get_string('sortresultsby') . ' ',
            'options'      => $sortoptions,
            'defaultvalue' => $sort,
        ),
        'search' => array(
            'type' => 'submit',
            'value' => get_string('search')
        ),
    )
));

$data = View::shared_to_user($query, $tag, $limit, $offset, $sort, $sortdir);

$pagination = build_pagination(array(
    'id' => 'sharedviews_pagination',
    'url' => get_config('wwwroot') . 'view/sharedviews.php' . (empty($queryparams) ? '' : ('?' . http_build_query($queryparams))),
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
    // Convert (query,type) parameters from form to (query,tag)
    global $queryparams, $tag, $query;

    if (isset($queryparams['query'])) {
        unset($queryparams['query']);
        $query = null;
    }

    if (isset($queryparams['tag'])) {
        unset($queryparams['tag']);
        $tag = null;
    }

    if (!empty($values['query'])) {
        if ($values['type'] == 'tagsonly') {
            $queryparams['tag'] = $tag = $values['query'];
        }
        else {
            $queryparams['query'] = $query = $values['query'];
        }
    }
}

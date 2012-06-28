<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2012 Catalyst IT Ltd and others; see:
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
 * @author     Melissa Draper <melissa@catalyst.net.nz>, Catalyst IT Ltd
 *
 */

define('INTERNAL', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('view.php');
require_once('group.php');
safe_require('artefact', 'comment');
define('TITLE', get_string('report', 'group'));
define('MENUITEM', 'groups/report');
define('GROUP', param_integer('group'));

$wwwroot = get_config('wwwroot');
$needsubdomain = get_config('cleanurlusersubdomains');

$setlimit = true;
$limit = param_integer('limit', 10);
$offset = param_integer('offset', 0);
$sort = param_variable('sort', 'title');
$direction = param_variable('direction', 'asc');
$group = group_current_group();
$role = group_user_access($group->id);
if (!group_role_can_access_report($group, $role)) {
    throw new AccessDeniedException();
}

$sharedviews = View::get_sharedviews_data(0, null, $group->id);
$sharedviewscount = $sharedviews->count;
$sharedviews = $sharedviews->data;
foreach ($sharedviews as &$data) {

    if (isset($data['group'])) {
        $sharedviews[$id]['groupname'] = get_field('group', 'name', 'id', $data['group']);
    }

    $view = new View($data['id']);
    $comments = ArtefactTypeComment::get_comments(0, 0, null, $view);

    $extcommenters = 0;
    $membercommenters = 0;
    $extcomments = 0;
    $membercomments = 0;
    $commenters = array();
    foreach ($comments->data as $c) {
        if (empty($c->author)) {
            if (!isset($commenters[$c->authorname])) {
                $commenters[$c->authorname] = array();
            }
            $commenters[$c->authorname]['commenter'] = $c->authorname;
            $commenters[$c->authorname]['count'] = (isset($commenters[$c->authorname]['count']) ? $commenters[$c->authorname]['count'] + 1 : 1);
            if ($commenters[$c->authorname]['count'] == 1) {
                $extcommenters++;
            }
            $extcomments++;
        }
        else {
            if (!isset($commenters[$c->author->id])) {
                $commenters[$c->author->id] = array();
            }
            $commenters[$c->author->id]['commenter'] = (int) $c->author->id;
            $commenters[$c->author->id]['member'] = group_user_access($group->id, $c->author->id);
            $commenters[$c->author->id]['count'] = (isset($commenters[$c->author->id]['count']) ? $commenters[$c->author->id]['count'] + 1 : 1);
            if (empty($commenters[$c->author->id]['member'])) {
                if ($commenters[$c->author->id]['count'] == 1) {
                    $extcommenters++;
                }
                $extcomments++;
            }
            else {
                if ($commenters[$c->author->id]['count'] == 1) {
                    $membercommenters++;
                }
                $membercomments++;
            }
        }
    }

    sorttablebycolumn($commenters, 'count', 'desc');
    $data['mcommenters'] = $membercommenters;
    $data['ecommenters'] = $extcommenters;
    $data['mcomments'] = $membercomments;
    $data['ecomments'] = $extcomments;
    $data['comments'] = $commenters;
    $data['baseurl'] = $needsubdomain ? $view->get_url(true) : ($wwwroot . $view->get_url(false));
}

if (in_array($sort, array('title', 'sharedby', 'mcomments', 'ecomments'))) {
    sorttablebycolumn($sharedviews, $sort, $direction);
}
$sharedviews = array_slice($sharedviews, $offset, $limit);

list($searchform, $groupviews, $unusedpagination) = View::views_by_owner($group->id);
$groupviews = $groupviews->data;
$groupviewscount = count($groupviews);

foreach ($groupviews as &$data) {
    $view = new View($data['id']);
    $comments = ArtefactTypeComment::get_comments(0, 0, null, $view);

    $extcommenters = 0;
    $membercommenters = 0;
    $extcomments = 0;
    $membercomments = 0;
    $commenters = array();
    foreach ($comments->data as $c) {
        if (empty($c->author)) {
            if (!isset($commenters[$c->authorname])) {
                $commenters[$c->authorname] = array();
            }
            $commenters[$c->authorname]['commenter'] = $c->authorname;
            $commenters[$c->authorname]['count'] = (isset($commenters[$c->authorname]['count']) ? $commenters[$c->authorname]['count'] + 1 : 1);
            if ($commenters[$c->authorname]['count'] == 1) {
                $extcommenters++;
            }
            $extcomments++;
        }
        else {
            if (!isset($commenters[$c->author->id])) {
                $commenters[$c->author->id] = array();
            }
            $commenters[$c->author->id]['commenter'] = (int) $c->author->id;
            $commenters[$c->author->id]['member'] = group_user_access($group->id, $c->author->id);
            $commenters[$c->author->id]['count'] = (isset($commenters[$c->author->id]['count']) ? $commenters[$c->author->id]['count'] + 1 : 1);
            if (empty($commenters[$c->author->id]['member'])) {
                if ($commenters[$c->author->id]['count'] == 1) {
                    $extcommenters++;
                }
                $extcomments++;
            }
            else {
                if ($commenters[$c->author->id]['count'] == 1) {
                    $membercommenters++;
                }
                $membercomments++;
            }
        }
    }

    $data['id'] = (int) $data['id'];
    $data['mcommenters'] = $membercommenters;
    $data['ecommenters'] = $extcommenters;
    $data['mcomments'] = $membercomments;
    $data['ecomments'] = $extcomments;
    $data['comments'] = $commenters;
    $data['title'] = $data['displaytitle'];
}

if (in_array($sort, array('title', 'mcomments', 'ecomments'))) {
    sorttablebycolumn($groupviews, $sort, $direction);
}
$groupviews = array_slice($groupviews, $offset, $limit);

$pagination = build_pagination(array(
    'url'    => get_config('wwwroot') . 'group/report.php?group=' . $group->id,
    'count'  => max($sharedviewscount, $groupviewscount),
    'limit'  => $limit,
    'setlimit' => $setlimit,
    'offset' => $offset,
    'jumplinks' => 6,
    'numbersincludeprevnext' => 2,
));

$js = <<< EOF
addLoadEvent(function () {
    p = {$pagination['javascript']}
});
EOF;

$smarty = smarty(array('paginator'));
$smarty->assign('baseurl', get_config('wwwroot') . 'group/report.php?group=' . $group->id);
$smarty->assign('heading', $group->name);
$smarty->assign('sharedviews', $sharedviews);
$smarty->assign('groupviews', $groupviews);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('gvcount', $groupviewscount);
$smarty->assign('svcount', $sharedviewscount);
$smarty->assign('sort', $sort);
$smarty->assign('direction', $direction);
$smarty->display('group/report.tpl');

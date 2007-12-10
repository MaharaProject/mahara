<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage artefact-blog
 * @author     Alastair Pharo <alastair@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

//
// NOTE: this script is used for rendering a blog in the view interface.
// It is envisioned later that the blog blocktype will be modified to not use 
// javascript to render a blog, and thus this script can be removed.
//
define('INTERNAL', 1);
define('JSON', 1);
define('PUBLIC', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'blog');

$id      = param_integer('id');
$limit   = param_integer('limit', ArtefactTypeBlog::pagination);
$offset  = param_integer('offset', 0);
$options = json_decode(param_variable('options'));
$viewid  = isset($options->viewid) ? $options->viewid : null;

if ($viewid) {
   if (!can_view_view($viewid)) {
        throw new AccessDeniedException();
   }
}
else {
    if (!$USER->is_logged_in()) {
        throw new AccessDeniedException();
    }
    if (!$viewid && get_field('artefact', 'owner', 'id', $id) != $USER->get('id')) {
        throw new AccessDeniedException();
    }
}

($postids = get_records_sql_array("
 SELECT a.id
 FROM {artefact} a
  LEFT OUTER JOIN {artefact_blog_blogpost} bp
   ON a.id = bp.blogpost
 WHERE a.parent = ?
  AND bp.published = 1
 ORDER BY a.ctime DESC
 LIMIT ? OFFSET ?;", array($id, $limit, $offset)))
    || ($postids = array());

$data = array();
foreach($postids as $postid) {
    $blogpost = new ArtefactTypeBlogPost($postid->id);
    $data[] = array(
        'id' => $postid->id,
        'content' => $blogpost->render_self((array) $options)
    );
}

$count = (int)get_field_sql("
 SELECT COUNT(*)
 FROM {artefact} a
  LEFT OUTER JOIN {artefact_blog_blogpost} bp
   ON a.id = bp.blogpost
 WHERE a.parent = ?
  AND bp.published = 1", array($id));

if (!$count) {
    $count = 1;
    $data = array(
        array(
            'content' => get_string('noresults', 'artefact.blog')
        )
    );
}

json_reply(false, array(
    'count' => $count,
    'limit' => $limit,
    'offset' => $offset,
    'data' => $data
));

?>

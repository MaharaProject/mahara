<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-blog
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'blog');
require_once(get_config('docroot') . 'blocktype/lib.php');
require_once(get_config('docroot') . 'artefact/blog/blocktype/blog/lib.php');

$offset = param_integer('offset', 0);

if ($blockid = param_integer('block', null)) {
    $bi = new BlockInstance($blockid);
    if (!can_view_view($bi->get('view'))) {
        json_reply(true, get_string('accessdenied', 'error'));
    }
    $configdata = $bi->get('configdata');
    $limit  = isset($configdata['count']) ? $configdata['count'] : 5;
    $configdata['countcomments'] = true;
    $configdata['viewid'] = $bi->get('view');
    $posts = ArtefactTypeBlogpost::get_posts($configdata['artefactid'], $limit, $offset, $configdata);
    $template = 'artefact:blog:viewposts.tpl';
    $baseurl = $bi->get_view()->get_url();
    $baseurl .= (strpos($baseurl, '?') === false ? '?' : '&') . 'block=' . $blockid;
    $pagination = array(
        'baseurl' => $baseurl,
        'id' => 'blogpost_pagination_' . $blockid,
        'datatable' => 'postlist_' . $blockid,
        'jsonscript' => 'artefact/blog/posts.json.php',
    );
    ArtefactTypeBlogpost::render_posts($posts, $template, $configdata, $pagination);
}
else {
    // No block, we're just rendering the blog by itself.
    $limit  = param_integer('limit', ArtefactTypeBlog::pagination);
    $blogid = param_integer('artefact');
    $viewid = param_integer('view');
    if (!can_view_view($viewid)) {
        json_reply(true, get_string('accessdenied', 'error'));
    }
    $options = array(
        'viewid' => $viewid,
        'countcomments' => true,
    );
    $posts = ArtefactTypeBlogpost::get_posts($blogid, $limit, $offset, $options);

    $template = 'artefact:blog:viewposts.tpl';
    $baseurl = get_config('wwwroot') . 'artefact/artefact.php?artefact=' . $blogid . '&view=' . $viewid;
    $pagination = array(
        'baseurl' => $baseurl,
        'id' => 'blogpost_pagination',
        'datatable' => 'postlist',
        'jsonscript' => 'artefact/blog/posts.json.php',
    );

    ArtefactTypeBlogpost::render_posts($posts, $template, $options, $pagination);
}


json_reply(false, array('data' => $posts));

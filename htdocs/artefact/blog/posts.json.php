<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-blog
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
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
$posts = array();

if ($blockid = param_integer('block', null)) {
    $bi = new BlockInstance($blockid);
    if (!can_view_view($bi->get('view'))) {
        json_reply(true, get_string('accessdenied', 'error'));
    }
    $configdata = $bi->get('configdata');
    $limit  = isset($configdata['count']) ? $configdata['count'] : 5;
    $configdata['countcomments'] = true;
    $configdata['versioning'] = false;
    $configdata['viewid'] = $bi->get('view');
    $configdata['blockid'] = $blockid;
    $posts = ArtefactTypeBlogPost::get_posts($configdata['artefactid'], $limit, $offset, $configdata);
    $template = 'artefact:blog:viewposts.tpl';
    $baseurl = $bi->get_view()->get_url();
    $baseurl .= (strpos($baseurl, '?') === false ? '?' : '&') . 'block=' . $blockid;
    $pagination = array(
        'baseurl' => $baseurl,
        'id' => 'blogpost_pagination_' . $blockid,
        'datatable' => 'postlist_' . $blockid,
        'jsonscript' => 'artefact/blog/posts.json.php',
    );
    ArtefactTypeBlogPost::render_posts($posts, $template, $configdata, $pagination);
}

json_reply(false, array('data' => $posts));

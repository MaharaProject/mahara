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
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'blog');

$blogs = (object) array(
    'offset' => param_integer('offset', 0),
    'limit'  => param_integer('limit', 10),
    'institution' => param_alpha('institution', null),
    'group'  => param_integer('group', null),
);

list($blogs->count, $blogs->data) = ArtefactTypeBlog::get_blog_list($blogs->limit, $blogs->offset, $blogs->institution, $blogs->group);
ArtefactTypeBlog::build_blog_list_html($blogs);

json_reply(false, array('data' => $blogs));

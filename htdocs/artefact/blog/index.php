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
define('MENUITEM', 'content/blogs');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'blog');
define('SECTION_PAGE', 'index');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'blog');

if (!$USER->get_account_preference('multipleblogs')) {
    redirect(get_config('wwwroot') . 'artefact/blog/view/index.php');
}

define('TITLE', get_string('blogs','artefact.blog'));

if ($delete = param_integer('delete', 0)) {
    ArtefactTypeBlog::delete_form($delete);
}

$blogs = (object) array(
    'offset' => param_integer('offset', 0),
    'limit'  => param_integer('limit', 10),
);

list($blogs->count, $blogs->data) = ArtefactTypeBlog::get_blog_list($blogs->limit, $blogs->offset);

ArtefactTypeBlog::build_blog_list_html($blogs);

$smarty = smarty(array('paginator'));
$smarty->assign_by_ref('blogs', $blogs);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', 'addLoadEvent(function() {' . $blogs->pagination_js . '});');
$smarty->display('artefact:blog:index.tpl');

function delete_blog_submit(Pieform $form, $values) {
    global $SESSION;
    $blog = new ArtefactTypeBlog($values['delete']);
    $blog->check_permission();
    if ($blog->get('locked')) {
        $SESSION->add_error_msg(get_string('submittedforassessment', 'view'));
    }
    else {
        $blog->delete();
        $SESSION->add_ok_msg(get_string('blogdeleted', 'artefact.blog'));
    }
    redirect('/artefact/blog/index.php');
}

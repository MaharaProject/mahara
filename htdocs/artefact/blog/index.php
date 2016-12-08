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
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'blog');
define('SECTION_PAGE', 'index');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'blog');

if ($delete = param_integer('delete', 0)) {
    ArtefactTypeBlog::delete_form($delete);
}

$blogs = (object) array(
    'offset' => param_integer('offset', 0),
    'limit'  => param_integer('limit', 10),
    'institution' => null,
    'group' => null,
    'data' => false,
    'pagination_js' => false,
);

if ($groupid = param_alphanum('group', null)) {
    define('SUBSECTIONHEADING', get_string('Blogs','artefact.blog'));
}

$institutionname = $groupid = null;
if ($institution = param_alphanum('institution', null)) {
    define('TITLE', get_string('Blogs','artefact.blog'));
    if ($institution == 'mahara') {
        $institutionname = $institution;
        if (!($USER->get('admin'))) {
            throw new AccessDeniedException();
        }
        $institutiontitle = get_string('siteblogs', 'artefact.blog');
    }
    else {
        $s = institution_selector_for_page($institution, get_config('wwwroot')  . 'artefact/blog/index.php');

        // Special case: institution=1 means "any institution".
        // If it comes back with a different institution than that,
        // then reload so we know the institution by name.
        // (This makes pagination a lot easier!)
        if ($institution == 1 && $s['institution'] != 1) {
            if (empty($s['institution'])) {
                // we have no institutions yet (only 'mahara')
                $s['institution'] = 'mahara';
            }
            redirect($CFG->wwwroot . 'artefact/blog/index.php?institution=' . $s['institution']);
        }

        $institutionname = $s['institution'];
        if (!($USER->get('admin') || $USER->is_institutional_admin())) {
            throw new AccessDeniedException();
        }
        $institutiontitle = get_string('institutionblogs', 'artefact.blog');
    }
    $blogs->institution = $institutionname;
}
else if ($groupid = param_alphanum('group', null)) {
    $blogs->group = $groupid;
    $group = get_record('group', 'id', $groupid, 'deleted', 0);
    define('TITLE', $group->name);
}
else {
    define('TITLE', get_string('Blogs','artefact.blog'));
}

PluginArtefactBlog::set_blog_nav($institution, $institutionname, $groupid);

list($blogs->count, $blogs->data) = ArtefactTypeBlog::get_blog_list($blogs->limit, $blogs->offset, $blogs->institution, $blogs->group);

if (empty($blogs->institution) && empty($blogs->group)) {
    if (!$USER->get_account_preference('multipleblogs')) {
        // Check to see if the user has multiple blogs anyway
        $records = get_records_select_array('artefact', "artefacttype = 'blog' AND \"owner\" = ?", array($USER->get('id')), 'id ASC');
        if ($records && count($records) > 1) {
            set_account_preference($USER->get('id'), 'multipleblogs', 1);
            $USER->set_account_preference('multipleblogs', 1);
        }
        else {
            $extra = !empty($institution) ? '?institution=' . $institution : '';
            $extra = !empty($group) ? '?group=' . $group : '';
            redirect(get_config('wwwroot') . 'artefact/blog/view/index.php' . $extra);
        }
    }
}

ArtefactTypeBlog::build_blog_list_html($blogs);

$smarty = smarty(array('paginator'));
$smarty->assign('blogs', $blogs);
$smarty->assign('institutionname', $institutionname);
$smarty->assign('group', $groupid);
$js = '';
if ($blogs->pagination_js) {
    $js .= 'addLoadEvent(function() {' . $blogs->pagination_js . '});';
}
if (!empty($institutionname) && ($institutionname != 'mahara')) {
    $smarty->assign('institution', $institutionname);
    $smarty->assign('institutionselector', $s['institutionselector']);
    $js .= $s['institutionselectorjs'];
}
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('artefact:blog:index.tpl');

function delete_blog_submit(Pieform $form, $values) {
    global $SESSION;
    require_once('embeddedimage.php');
    $blog = new ArtefactTypeBlog($values['delete']);
    $blog->check_permission();
    $institution = $blog->get('institution');
    $group = $blog->get('group');
    if ($blog->get('locked')) {
        $SESSION->add_error_msg(get_string('submittedforassessment', 'view'));
    }
    else {
        $blog->delete();
        $SESSION->add_ok_msg(get_string('blogdeleted', 'artefact.blog'));
    }
    if ($institution) {
        redirect('/artefact/blog/index.php?institution=' . $institution);
    }
    else if ($group) {
        redirect('/artefact/blog/index.php?group=' . $group);
    }
    redirect('/artefact/blog/index.php');
}

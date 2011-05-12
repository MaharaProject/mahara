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
 * @subpackage artefact-blog
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'content/blogs');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'blog');
define('SECTION_PAGE', 'view');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
define('TITLE', get_string('viewblog','artefact.blog'));
safe_require('artefact', 'blog');
require_once(get_config('libroot') . 'pieforms/pieform.php');

if ($publish = param_integer('publish', null)) {
    ArtefactTypeBlogpost::publish_form($publish);
}
if ($delete = param_integer('delete', null)) {
    ArtefactTypeBlogpost::delete_form($delete);
}

$id = param_integer('id', null);
if (is_null($id)) {
    if (!$records = get_records_select_array(
            'artefact',
            "artefacttype = 'blog' AND \"owner\" = ?",
            array($USER->get('id')),
            'id ASC'
        )) {
        throw new ParameterException();
    }
    $id = $records[0]->id;
    $blog = new ArtefactTypeBlog($id, $records[0]);
}
else {
    $blog = new ArtefactTypeBlog($id);
}
$blog->check_permission();

$limit = param_integer('limit', 5);
$offset = param_integer('offset', 0);

$posts = ArtefactTypeBlogPost::get_posts($id, $limit, $offset);
$template = 'artefact:blog:posts.tpl';
$pagination = array(
    'baseurl'    => get_config('wwwroot') . 'artefact/blog/view/index.php?id=' . $id,
    'id'         => 'blogpost_pagination',
    'jsonscript' => 'artefact/blog/view/index.json.php',
    'datatable'  => 'postlist',
);
ArtefactTypeBlogPost::render_posts($posts, $template, array(), $pagination);

$strpublished = json_encode(get_string('published', 'artefact.blog'));
$js = <<<EOF
function publish_success(form, data) {
    removeElement('publish_' + data.id);
    $('poststatus' + data.id).innerHTML = {$strpublished};
}
function delete_success(form, data) {
    addElementClass('postdetails_' + data.id, 'hidden');
    if ($('postfiles_' + data.id)) {
        addElementClass('postfiles_' + data.id, 'hidden');
    }
    addElementClass('postdescription_' + data.id, 'hidden');
    addElementClass('posttitle_' + data.id, 'hidden');
}
EOF;

$smarty = smarty(array('paginator'));
$smarty->assign('PAGEHEADING', $blog->get('title'));
$smarty->assign('INLINEJAVASCRIPT', $js);

if (!$USER->get_account_preference('multipleblogs')
    && count_records('artefact', 'artefacttype', 'blog', 'owner', $USER->get('id')) == 1) {
    $smarty->assign('enablemultipleblogstext', 1);
}

$smarty->assign_by_ref('blog', $blog);
$smarty->assign_by_ref('posts', $posts);
$smarty->display('artefact:blog:view.tpl');
exit;

function publish_submit(Pieform $form, $values) {
    $blogpost = new ArtefactTypeBlogPost((int) $values['publish']);
    $blogpost->check_permission();
    $blogpost->publish();
    $form->reply(PIEFORM_OK, array(
        'message' => get_string('blogpostpublished', 'artefact.blog'),
        'goto' => get_config('wwwroot') . 'artefact/blog/view/?id=' . $blogpost->get('parent'),
        'id' => $values['publish'],
    ));
}

function delete_submit(Pieform $form, $values) {
    $blogpost = new ArtefactTypeBlogPost((int) $values['delete']);
    $blogpost->check_permission();
    if ($blogpost->get('locked')) {
        $form->reply(PIEFORM_ERR, get_string('submittedforassessment', 'view'));
    }
    $blogpost->delete();
    $form->reply(PIEFORM_OK, array(
        'message' => get_string('blogpostdeleted', 'artefact.blog'),
        'goto' => get_config('wwwroot') . 'artefact/blog/view/?id=' . $blogpost->get('parent'),
        'id' => $values['delete'],
    ));
}

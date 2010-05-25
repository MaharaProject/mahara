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
 * @subpackage artefact-comment
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('editcomment', 'artefact.comment'));
safe_require('artefact', 'comment');

$id = param_integer('id');
$viewid = param_integer('view');
$comment = new ArtefactTypeComment($id);

if ($USER->get('id') != $comment->get('author')) {
    throw new AccessDeniedException(get_string('canteditnotauthor', 'artefact.comment'));
}

$onview = $comment->get('onview');
if ($onview && $onview != $viewid) {
    throw new NotFoundException(get_string('commentnotinview', 'artefact.comment', $id, $viewid));
}

$maxage = (int) get_config_plugin('artefact', 'comment', 'commenteditabletime');
$editableafter = time() - 60 * $maxage;

$goto = $comment->get_view_url($viewid, false);

if ($comment->get('ctime') < $editableafter) {
    $SESSION->add_error_msg(get_string('cantedittooold', 'artefact.comment', $maxage));
    redirect($goto);
}

$lastcomment = ArtefactTypeComment::last_public_comment($viewid, $comment->get('onartefact'));

if (!$comment->get('private') && $id != $lastcomment->id) {
    $SESSION->add_error_msg(get_string('cantedithasreplies', 'artefact.comment'));
    redirect($goto);
}

$form = pieform(array(
    'name'            => 'edit_comment',
    'method'          => 'post',
    'plugintype'      => 'artefact',
    'pluginname'      => 'comment',
    'elements'        => array(
        'message' => array(
            'type'         => 'wysiwyg',
            'title'        => get_string('message'),
            'rows'         => 5,
            'cols'         => 80,
            'defaultvalue' => $comment->get('description'),
        ),
        'ispublic' => array(
            'type'  => 'checkbox',
            'title' => get_string('makepublic', 'artefact.comment'),
            'defaultvalue' => !$comment->get('private'),
        ),
        'submit' => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('save'), get_string('cancel')),
            'goto'  => $goto,
        ),
    )
));

function edit_comment_submit(Pieform $form, $values) {
    global $viewid, $comment, $SESSION, $goto;

    db_begin();

    $comment->set('description', $values['message']);
    $comment->set('private', 1 - (int) $values['ispublic']);
    $comment->commit();

    require_once('activity.php');
    $data = (object) array(
        'commentid' => $comment->get('id'),
        'viewid'    => $viewid,
    );

    activity_occurred('feedback', $data, 'artefact', 'comment');

    db_commit();

    $SESSION->add_ok_msg(get_string('commentupdated', 'artefact.comment'));
    redirect($goto);
}

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('strdescription', get_string('editcommentdescription', 'artefact.comment', $maxage));
$smarty->assign('form', $form);
$smarty->display('artefact:comment:edit.tpl');

?>

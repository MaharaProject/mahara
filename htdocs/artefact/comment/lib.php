<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginArtefactComment extends PluginArtefact {

    public static function get_artefact_types() {
        return array(
            'comment',
        );
    }

    public static function get_block_types() {
        return array();
    }

    public static function get_plugin_name() {
        return 'comment';
    }

    public static function menu_items() {
        return array();
    }

    public static function get_event_subscriptions() {
        return array();
    }

    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            foreach(ArtefactTypeComment::deleted_types() as $type) {
                insert_record('artefact_comment_deletedby', (object)array('name' => $type));
            }
        }
    }
}

class ArtefactTypeComment extends ArtefactType {

    protected $onview;
    protected $onartefact;
    protected $private;
    protected $deletedby;

    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);

        if ($this->id && ($extra = get_record('artefact_comment_comment', 'artefact', $this->id))) {
            foreach($extra as $name => $value) {
                if (property_exists($this, $name)) {
                    $this->{$name} = $value;
                }
            }
        }
    }

    public function commit() {
        if (empty($this->dirty)) {
            return;
        }

        $new = empty($this->id);

        db_begin();

        parent::commit();

        $data = (object)array(
            'artefact'    => $this->get('id'),
            'onview'      => $this->get('onview'),
            'onartefact'  => $this->get('onartefact'),
            'private'     => $this->get('private'),
            'deletedby'   => $this->get('deletedby'),
        );

        if ($new) {
            insert_record('artefact_comment_comment', $data);
        }
        else {
            update_record('artefact_comment_comment', $data, 'artefact');
        }

        db_commit();
        $this->dirty = false;
    }

    public static function is_singular() {
        return false;
    }

    public static function get_icon($options=null) {
        global $THEME;
        return $THEME->get_url('images/comment.gif', false, 'artefact/comment');
    }

    public function delete() {
        if (empty($this->id)) {
            return;
        }
        db_begin();
        $this->detach();
        parent::delete();
        db_commit();
    }

    public static function bulk_delete($artefactids) {
        if (empty($artefactids)) {
            return;
        }

        $idstr = join(',', $artefactids);

        db_begin();
        delete_records_select('artefact_comment_comment', 'artefact IN (' . $idstr . ')');
        parent::bulk_delete($artefactids);
        db_commit();
    }

    public static function delete_view_comments($viewid) {
        $ids = get_column('artefact_comment_comment', 'artefact', 'onview', $viewid);
        self::bulk_delete($ids);
    }

    public static function get_links($id) {
        return array(
            '_default' => get_config('wwwroot') . 'artefact/comment/view.php?id=' . $this->get('id'),
        );
    }

    public function can_have_attachments() {
        return true;
    }

    /* public function to_stdclass($options) {
        return (object) array(
            'id'          => $this->id,
            'name'        => $this->title,
            'description' => $this->description,
            'created'     => $this->ctime,
        );
    }

    public function render_self($options) {
        $smarty = smarty_core();
        $smarty->assign('comment', $this->to_stdclass($options));
        return array('html' => $smarty->fetch('artefact:comment:comment.tpl'), 'javascript' => null);
    }*/

    public static function deleted_types() {
        return array('author', 'owner', 'admin');
    }

    public static function get_comments($limit=10, $offset=0, $lastpage=false, &$view=null, &$artefact=null) {
        global $USER;
        $userid = $USER->get('id');
        $viewid = $view->get('id');
        if (!empty($artefact)) {
            $canedit = $USER->can_edit_artefact($artefact);
            $isowner = $userid && $userid == $artefact->get('owner');
            $artefactid = $artefact->get('id');
        }
        else {
            $canedit = $USER->can_edit_view($view);
            $isowner = $userid && $userid == $view->get('owner');
            $artefactid = null;
        }

        $result = (object) array(
            'limit'    => $limit,
            'offset'   => $offset,
            'lastpage' => $lastpage,
            'view'     => $viewid,
            'artefact' => $artefactid,
            'canedit'  => $canedit,
            'isowner'  => $isowner,
            'data'     => array(),
        );

        if (!empty($artefactid)) {
            $where = 'c.onartefact = ' . $artefactid;
        }
        else {
            $where = 'c.onview = ' . $viewid;
        }
        if (!$canedit) {
            $where .= ' AND (c.private = 0 OR a.author = ' . (int) $userid . ')';
        }

        $result->count = count_records_sql('
            SELECT COUNT(*)
            FROM {artefact} a JOIN {artefact_comment_comment} c ON a.id = c.artefact
            WHERE ' . $where);

        if ($result->count > 0) {
            if ($lastpage) { // Ignore $offset and just get the last page of feedback
                $offset = (ceil($result->count / $limit) - 1) * $limit;
            }

            $comments = get_records_sql_assoc('
                SELECT
                    a.id, a.author, a.authorname, a.ctime, a.description, c.private, c.deletedby
                FROM {artefact} a JOIN {artefact_comment_comment} c ON a.id = c.artefact
                WHERE ' . $where . '
                ORDER BY a.ctime', array(), $offset, $limit);

            $files = ArtefactType::attachments_from_id_list(array_keys($comments));

            if ($files) {
                safe_require('artefact', 'file');
                foreach ($files as &$file) {
                    $comments[$file->artefact]->attachments[] = $file;
                }
            }

            $result->data = array_values($comments);
        }

        self::build_html($result);
        return $result;
    }

    public static function build_html(&$data) {
        foreach ($data->data as &$item) {
            $item->date = format_date(strtotime($item->ctime), 'strftimedatetime');
            if (!empty($item->attachments)) {
                if ($data->isowner) {
                    $item->attachmessage = get_string(
                        'feedbackattachmessage',
                        'artefact.comment',
                        get_string('feedbackattachdirname', 'artefact.comment')
                    );
                }
                foreach ($item->attachments as &$a) {
                    $a->attachid    = $a->attachment;
                    $a->attachtitle = $a->title;
                    $a->attachsize  = display_size($a->size);
                }
            }
            if ($item->private) {
                $item->pubmessage = get_string('thisfeedbackisprivate', 'artefact.comment');
            }
            else if (!$item->private && $data->canedit) {
                $item->pubmessage = get_string('thisfeedbackispublic', 'artefact.comment');
                $item->makeprivateform = pieform(self::make_private_form($item->id));
            }

        }

        $extradata = array('view' => $data->view);
        $data->jsonscript = 'artefact/comment/comments.json.php';

        if (!empty($data->artefact)) {
            $data->baseurl = get_config('wwwroot') . 'view/artefact.php?view=' . $data->view . '&artefact=' . $data->artefact;
            $extradata['artefact'] = $data->artefact;
        }
        else {
            $data->baseurl = get_config('wwwroot') . 'view/view.php?id=' . $data->view;
        }

        $smarty = smarty_core();
        $smarty->assign_by_ref('data', $data->data);
        $smarty->assign('canedit', $data->canedit);
        $smarty->assign('baseurl', $data->baseurl);
        $data->tablerows = $smarty->fetch('artefact:comment:commentlist.tpl');
        $pagination = build_pagination(array(
            'id' => 'feedback_pagination',
            'class' => 'center',
            'url' => $data->baseurl,
            'jsonscript' => $data->jsonscript,
            'datatable' => 'feedbacktable',
            'count' => $data->count,
            'limit' => $data->limit,
            'offset' => $data->offset,
            'lastpage' => $data->lastpage,
            'resultcounttextsingular' => get_string('comment', 'artefact.comment'),
            'resultcounttextplural' => get_string('comments', 'artefact.comment'),
            'extradata' => $extradata,
        ));
        $data->pagination = $pagination['html'];
        $data->pagination_js = $pagination['javascript'];
    }

    public static function add_comment_form() {
        global $USER;
        $form = array(
            'name'            => 'add_feedback_form',
            'method'          => 'post',
            'class'           => 'js-hidden',
            'plugintype'      => 'artefact',
            'pluginname'      => 'comment',
            'jsform'          => true,
            'autofocus'       => false,
            'elements'        => array(),
            'jssuccesscallback' => 'addFeedbackSuccess',
        );
        if (!$USER->is_logged_in()) {
            $form['elements']['authorname'] = array(
                'type'  => 'text',
                'title' => get_string('name'),
                'rules' => array(
                    'required' => true,
                ),
            );
        }
        $form['elements']['message'] = array(
            'type'  => 'wysiwyg',
            'title' => get_string('message'),
            'rows'  => 5,
            'cols'  => 80,
        );
        $form['elements']['ispublic'] = array(
            'type'  => 'checkbox',
            'title' => get_string('makepublic', 'artefact.comment'),
        );
        if ($USER->is_logged_in()) {
            $form['elements']['attachments'] = array(
                'type'         => 'files',
                'title'        => get_string('attachfile', 'artefact.comment'),
                'defaultvalue' => array(),
            );
        }
        $form['elements']['submit'] = array(
            'type'  => 'submitcancel',
            'value' => array(get_string('placefeedback', 'artefact.comment'), get_string('cancel')),
        );
        return $form;
    }

    public static function make_private_form($id) {
        return array(
            'name'            => 'make_private',
            'renderer'        => 'oneline',
            'class'           => 'makeprivate',
            'elements'        => array(
                'comment'  => array('type' => 'hidden', 'value' => $id),
                'submit'   => array(
                    'type' => 'submit',
                    'name' => 'make_private_submit',
                    'value' => get_string('makeprivate', 'artefact.comment'),
                ),
            ),
        );
    }
}

function make_private_submit(Pieform $form, $values) {
    global $SESSION, $view;
    $viewid = $view->get('id');
    $comment = new ArtefactTypeComment((int) $values['comment']);
    $comment->set('private', 1);
    $comment->commit();
    $SESSION->add_ok_msg(get_string('feedbackchangedtoprivate', 'artefact.comment'));
    if ($artefact = $comment->get('onartefact')) {
        redirect(get_config('wwwroot') . 'view/artefact.php?view=' . $viewid . '&artefact=' . $artefact);
    }
    redirect(get_config('wwwroot') . 'view/view.php?id=' . $viewid);
}

function add_feedback_form_validate(Pieform $form, $values) {
    global $USER, $view;
    if (!$USER->is_logged_in()) {
        $token = get_cookie('viewaccess:'.$view->get('id'));
        if (!$token || get_view_from_token($token) != $view->get('id')) {
            $form->set_error('message', get_string('placefeedbacknotallowed', 'artefact.comment'));
        }
    }
}

function add_feedback_form_submit(Pieform $form, $values) {
    global $view, $artefact, $USER;
    $data = (object) array(
        'title'       => get_string('Comment', 'artefact.comment'),
        'description' => $values['message'],
        'private'     => 1 - (int) $values['ispublic'],
    );

    if ($artefact) {
        $data->onartefact  = $artefact->get('id');
        $data->owner       = $artefact->get('owner');
        $data->group       = $artefact->get('group');
        $data->institution = $artefact->get('institution');
    }
    else {
        $data->onview      = $view->get('id');
        $data->owner       = $view->get('owner');
        $data->group       = $view->get('group');
        $data->institution = $view->get('institution');
    }

    if ($author = $USER->get('id')) {
        $data->author = $author;
    }
    else {
        $data->authorname = $values['authorname'];
    }

    db_begin();

    $comment = new ArtefactTypeComment(0, $data);
    $comment->commit();

    if (!empty($values['attachments']) && is_array($values['attachments']) && !empty($data->author)) {

        require_once(get_config('libroot') . 'uploadmanager.php');
        safe_require('artefact', 'file');

        $ownerlang = empty($data->owner) ? get_config('lang') : get_user_language($data->owner);
        $folderid = ArtefactTypeFolder::get_folder_id(
            get_string_from_language($ownerlang, 'feedbackattachdirname', 'artefact.comment'),
            get_string_from_language($ownerlang, 'feedbackattachdirdesc', 'artefact.comment'),
            null, true, $data->owner, $data->group, $data->institution
        );

        $attachment = (object) array(
            'owner'         => $data->owner,
            'group'         => $data->group,
            'institution'   => $data->institution,
            'author'        => $data->author,
            'allowcomments' => 0,
            'parent'        => $folderid,
            'description'   => get_string_from_language(
                $ownerlang,
                'feedbackonviewbyuser',
                'artefact.comment',
                $view->get('title'),
                display_name($USER)
            ),
        );

        foreach ($values['attachments'] as $filesindex) {

            $um = new upload_manager($filesindex);
            if ($error = $um->preprocess_file()) {
                throw new UploadException($error);
            }

            $attachment->title = ArtefactTypeFileBase::get_new_file_title(
                $um->file['name'],
                $folderid,
                $data->owner,
                $data->group,
                $data->institution
            );
            $attachment->size         = $um->file['size'];
            $attachment->filetype     = $um->file['type'];
            $attachment->oldextension = $um->original_filename_extension();

            try {
                $fileid = ArtefactTypeFile::save_uploaded_file($filesindex, $attachment);
            }
            catch (QuotaExceededException $e) {}

            $comment->attach($fileid);
        }
    }

    require_once('activity.php');
    $data->message = html2text($data->description);
    $data->view    = $view->get('id');
    activity_occurred('feedback', $data);

    db_commit();

    if ($artefact) {
        $goto = get_config('wwwroot') . 'view/artefact.php?artefact=' . $artefact->get('id') . '&view='.$view->get('id');
        $newlist = ArtefactTypeComment::get_comments(10, 0, true, $view, $artefact);
    }
    else {
        $goto = get_config('wwwroot') . 'view/view.php?id='.$view->get('id');
        $newlist = ArtefactTypeComment::get_comments(10, 0, true, $view);
    }
    $form->reply(PIEFORM_OK, array(
        'message' => get_string('feedbacksubmitted', 'artefact.comment'),
        'goto' => $goto,
        'data' => $newlist,
    ));
}

function add_feedback_form_cancel_submit(Pieform $form) {
    global $view;
    $form->reply(PIEFORM_OK, array(
        'goto' => '/view/view.php?id=' . $view->get('id'),
    ));
}

?>

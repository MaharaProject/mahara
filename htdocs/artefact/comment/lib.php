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

require_once('activity.php');

define('MIN_RATING', 1);
define('MAX_RATING', 5);

function valid_rating($ratingstr) {
    if (empty($ratingstr)) {
        return null;
    }

    $rating = intval($ratingstr);
    if ($rating < MIN_RATING) {
        return null;
    }

    return max(MIN_RATING, min(MAX_RATING, $rating));
}

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

    public static function get_activity_types() {
        return array(
            (object)array(
                'name' => 'feedback',
                'admin' => 0,
                'delay' => 0,
            )
        );
    }

    public static function can_be_disabled() {
        return false;
    }

    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            set_config_plugin('artefact', 'comment', 'commenteditabletime', 10);
            foreach(ArtefactTypeComment::deleted_types() as $type) {
                insert_record('artefact_comment_deletedby', (object)array('name' => $type));
            }
        }
    }

    public static function view_export_extra_artefacts($viewids) {
        $artefacts = array();
        if (!$artefacts = get_column_sql("
            SELECT artefact
            FROM {artefact_comment_comment}
            WHERE deletedby IS NULL AND onview IN (" . join(',', array_map('intval', $viewids)) . ')', array())) {
            return array();
        }
        if ($attachments = get_column_sql('
            SELECT attachment
            FROM {artefact_attachment}
            WHERE artefact IN (' . join(',', $artefacts). ')')) {
            $artefacts = array_merge($artefacts, $attachments);
        }
        return $artefacts;
    }

    public static function artefact_export_extra_artefacts($artefactids) {
        if (!$artefacts = get_column_sql("
            SELECT artefact
            FROM {artefact_comment_comment}
            WHERE deletedby IS NULL AND onartefact IN (" . join(',', $artefactids) . ')', array())) {
            return array();
        }
        if ($attachments = get_column_sql('
            SELECT attachment
            FROM {artefact_attachment}
            WHERE artefact IN (' . join(',', $artefacts). ')')) {
            $artefacts = array_merge($artefacts, $attachments);
        }
        return $artefacts;
    }

    public static function get_cron() {
        return array(
            (object)array(
                'callfunction' => 'clean_feedback_notifications',
                'minute'       => '35',
                'hour'         => '22',
            ),
        );
    }

    public static function clean_feedback_notifications() {
        safe_require('notification', 'internal');
        PluginNotificationInternal::clean_notifications(array('feedback'));
    }
}

class ArtefactTypeComment extends ArtefactType {

    protected $onview;
    protected $onartefact;
    protected $private;
    protected $deletedby;
    protected $requestpublic;
    protected $rating;

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
            'artefact'      => $this->get('id'),
            'onview'        => $this->get('onview'),
            'onartefact'    => $this->get('onartefact'),
            'private'       => $this->get('private'),
            'deletedby'     => $this->get('deletedby'),
            'requestpublic' => $this->get('requestpublic'),
            'rating'        => $this->get('rating'),
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
        delete_records('artefact_comment_comment', 'artefact', $this->id);
        parent::delete();
        db_commit();
    }

    public static function bulk_delete($artefactids) {
        if (empty($artefactids)) {
            return;
        }

        $idstr = join(',', array_map('intval', $artefactids));

        db_begin();
        delete_records_select('artefact_comment_comment', 'artefact IN (' . $idstr . ')');
        parent::bulk_delete($artefactids);
        db_commit();
    }

    public static function delete_view_comments($viewid) {
        $ids = get_column('artefact_comment_comment', 'artefact', 'onview', $viewid);
        self::bulk_delete($ids);
    }

    public static function delete_comments_onartefacts($artefactids) {
        $idstr = join(',', array_map('intval', $artefactids));
        $commentids = get_column_sql("SELECT artefact FROM {artefact_comment_comment} WHERE onartefact IN ($idstr)");
        self::bulk_delete($commentids);
    }

    public static function get_links($id) {
        return array(
            '_default' => get_config('wwwroot') . 'artefact/comment/view.php?id=' . $id,
        );
    }

    public function can_have_attachments() {
        return true;
    }

    public static function deleted_types() {
        return array('author', 'owner', 'admin');
    }

    public static function get_comments($limit=10, $offset=0, $showcomment=null, &$view=null, &$artefact=null) {
        global $USER;
        $userid = $USER->get('id');
        $viewid = $view->get('id');
        if (!empty($artefact)) {
            $canedit = $USER->can_edit_artefact($artefact);
            $owner = $artefact->get('owner');
            $isowner = $userid && $userid == $owner;
            $artefactid = $artefact->get('id');
        }
        else {
            $canedit = $USER->can_edit_view($view);
            $owner = $view->get('owner');
            $isowner = $userid && $userid == $owner;
            $artefactid = null;
        }

        $result = (object) array(
            'limit'    => $limit,
            'offset'   => $offset,
            'view'     => $viewid,
            'artefact' => $artefactid,
            'canedit'  => $canedit,
            'owner'    => $owner,
            'isowner'  => $isowner,
            'data'     => array(),
        );

        if (!empty($artefactid)) {
            $where = 'c.onartefact = ' . (int)$artefactid;
        }
        else {
            $where = 'c.onview = ' . (int)$viewid;
        }
        if (!$canedit) {
            $where .= ' AND (c.private = 0 OR a.author = ' . (int) $userid . ')';
        }

        $result->count = count_records_sql('
            SELECT COUNT(*)
            FROM {artefact} a JOIN {artefact_comment_comment} c ON a.id = c.artefact
            WHERE ' . $where);

        if ($result->count > 0) {
            if ($showcomment == 'last') { // Ignore $offset and just get the last page of feedback
                $result->forceoffset = $offset = (ceil($result->count / $limit) - 1) * $limit;
            }
            else if (is_numeric($showcomment)) {
                // Ignore $offset and get the page that has the comment
                // with id $showcomment on it.
                // Fetch everything up to $showcomment to get its rank
                // This will get ugly if there are 1000s of comments
                $ids = get_column_sql('
                SELECT a.id
                FROM {artefact} a JOIN {artefact_comment_comment} c ON a.id = c.artefact
                WHERE ' . $where . ' AND a.id <= ?
                ORDER BY a.ctime', array($showcomment));
                $last = end($ids);
                if ($last == $showcomment) {
                    $rank = key($ids);
                    $result->forceoffset = $offset = ((ceil($rank / $limit) - 1) * $limit);
                    $result->showcomment = $showcomment;
                }
            }

            $comments = get_records_sql_assoc('
                SELECT
                    a.id, a.author, a.authorname, a.ctime, a.description,
                    c.private, c.deletedby, c.requestpublic, c.rating,
                    u.username, u.firstname, u.lastname, u.preferredname, u.email, u.staff, u.admin,
                    u.deleted, u.profileicon
                FROM {artefact} a
                    INNER JOIN {artefact_comment_comment} c ON a.id = c.artefact
                    LEFT JOIN {usr} u ON a.author = u.id
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

    public static function count_comments(&$viewids=null, &$artefactids=null) {
        if (!empty($viewids)) {
            return get_records_sql_assoc('
                SELECT c.onview, COUNT(c.artefact) AS comments
                FROM {artefact_comment_comment} c
                WHERE c.onview IN (' . join(',', array_map('intval', $viewids)) . ') AND c.deletedby IS NULL
                GROUP BY c.onview',
                array()
            );
        }
        if (!empty($artefactids)) {
            return get_records_sql_assoc('
                SELECT c.onartefact, COUNT(c.artefact) AS comments
                FROM {artefact_comment_comment} c
                WHERE c.onartefact IN (' . join(',', array_map('intval', $artefactids)) . ') AND c.deletedby IS NULL
                GROUP BY c.onartefact',
                array()
            );
        }
    }

    public static function last_public_comment($view=null, $artefact=null) {
        if (!empty($artefact)) {
            $where = 'c.onartefact = ?';
            $values = array($artefact);
        }
        else {
            $where = 'c.onview = ?';
            $values = array($view);
        }
        $newest = get_records_sql_array('
            SELECT a.id, a.ctime
            FROM {artefact} a INNER JOIN {artefact_comment_comment} c ON a.id = c.artefact
            WHERE c.private = 0 AND ' . $where . '
            ORDER BY a.ctime DESC', $values, 0, 1
        );
        return $newest[0];
    }

    public static function deleted_messages() {
        return array(
            'author' => 'commentremovedbyauthor',
            'owner'  => 'commentremovedbyowner',
            'admin'  => 'commentremovedbyadmin',
        );
    }

    public static function build_html(&$data) {
        global $USER, $THEME;
        $candelete = $data->canedit || $USER->get('admin');
        $deletedmessage = array();
        foreach (self::deleted_messages() as $k => $v) {
            $deletedmessage[$k] = get_string($v, 'artefact.comment');
        }
        $authors = array();
        $lastcomment = self::last_public_comment($data->view, $data->artefact);
        $editableafter = time() - 60 * get_config_plugin('artefact', 'comment', 'commenteditabletime');
        foreach ($data->data as &$item) {
            $item->ts = strtotime($item->ctime);
            $item->date = format_date($item->ts, 'strftimedatetime');
            $item->isauthor = $item->author && $item->author == $USER->get('id');
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
                $item->pubmessage = get_string('thiscommentisprivate', 'artefact.comment');
            }

            if (isset($data->showcomment) && $data->showcomment == $item->id) {
                $item->highlight = 1;
            }

            if ($item->deletedby) {
                $item->deletedmessage = $deletedmessage[$item->deletedby];
            }
            else if ($candelete || $item->isauthor) {
                $item->deleteform = pieform(self::delete_comment_form($item->id));
            }

            // Comment authors can edit recent comments if they're private or if no one has replied yet.
            if (!$item->deletedby && $item->isauthor
                && ($item->private || $item->id == $lastcomment->id) && $item->ts > $editableafter) {
                $item->canedit = 1;
            }

            // Form to make private comment public, or request that a
            // private comment be made public
            if (!$item->deletedby && $item->private && $item->author && $data->owner
                && ($item->isauthor || $data->isowner)) {
                if (empty($item->requestpublic)
                    || $item->isauthor && $item->requestpublic == 'owner'
                    || $data->isowner && $item->requestpublic == 'author') {
                    $item->makepublicform = pieform(self::make_public_form($item->id));
                }
                else if ($item->isauthor && $item->requestpublic == 'author'
                         || $data->isowner && $item->requestpublic == 'owner') {
                    $item->makepublicrequested = 1;
                }
            }

            if ($item->author) {
                if (isset($authors[$item->author])) {
                    $item->author = $authors[$item->author];
                }
                else {
                    $item->author = $authors[$item->author] = (object) array(
                        'id'            => $item->author,
                        'username'      => $item->username,
                        'firstname'     => $item->firstname,
                        'lastname'      => $item->lastname,
                        'preferredname' => $item->preferredname,
                        'email'         => $item->email,
                        'staff'         => $item->staff,
                        'admin'         => $item->admin,
                        'deleted'       => $item->deleted,
                        'profileicon'   => $item->profileicon,
                    );
                }
            }

            if (get_config_plugin('artefact', 'comment', 'commentratings') and $item->rating) {
                $item->rating = valid_rating($item->rating);
                $item->ratingimage = '';
                for ($i = MIN_RATING; $i <= MAX_RATING; $i++) {
                    $checked = '';
                    if ($i === $item->rating) {
                        $checked = 'checked="checked"';
                    }
                    $item->ratingimage .= '<input name="star'.$item->id.'" type="radio" class="star" '.$checked.' disabled="disabled"/>';
                }
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
        $smarty->assign('viewid', $data->view);
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
            'forceoffset' => isset($data->forceoffset) ? $data->forceoffset : null,
            'resultcounttextsingular' => get_string('comment', 'artefact.comment'),
            'resultcounttextplural' => get_string('comments', 'artefact.comment'),
            'extradata' => $extradata,
        ));
        $data->pagination = $pagination['html'];
        $data->pagination_js = $pagination['javascript'];
    }

    public function render_self() {
        return clean_html($this->get('description'));
    }

    public static function add_comment_form($defaultprivate=false, $moderate=false) {
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
            $form['spam'] = array(
                'secret'       => get_config('formsecret'),
                'mintime'      => 1,
                'hash'         => array('authorname', 'message', 'ispublic', 'message', 'submit'),
            );
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
            'rules' => array('maxlength' => 8192),
        );
        if (get_config_plugin('artefact', 'comment', 'commentratings')) {
            $form['elements']['rating'] = array(
                'type'  => 'radio',
                'title' => get_string('rating', 'artefact.comment'),
                'options' => array('1' => '', '2' => '', '3' => '', '4' => '', '5' => ''),
                'class' => 'star',
            );
        }
        $form['elements']['ispublic'] = array(
            'type'  => 'checkbox',
            'title' => get_string('makepublic', 'artefact.comment'),
            'defaultvalue' => !$defaultprivate,
        );
        if ($moderate) {
            $form['elements']['ispublic']['description'] = get_string('approvalrequired', 'artefact.comment');
            $form['elements']['moderate'] = array(
                'type'  => 'hidden',
                'value' => true,
            );
        }
        if ($USER->is_logged_in()) {
            $form['elements']['attachments'] = array(
                'type'         => 'files',
                'title'        => get_string('attachfile', 'artefact.comment'),
                'defaultvalue' => array(),
                'maxfilesize'  => get_max_upload_size(false),
            );
        }
        $form['elements']['submit'] = array(
            'type'  => 'submitcancel',
            'value' => array(get_string('placefeedback', 'artefact.comment'), get_string('cancel')),
        );
        return $form;
    }

    public static function make_public_form($id) {
        return array(
            'name'            => 'make_public',
            'renderer'        => 'oneline',
            'elements'        => array(
                'comment'  => array('type' => 'hidden', 'value' => $id),
                'submit'   => array(
                    'type'  => 'submit',
                    'class' => 'quiet',
                    'name'  => 'make_public_submit',
                    'value' => get_string('makepublic', 'artefact.comment'),
                ),
            ),
        );
    }

    public static function delete_comment_form($id) {
    	global $THEME;
        return array(
            'name'     => 'delete_comment',
            'renderer' => 'oneline',
            'elements' => array(
                'comment' => array('type' => 'hidden', 'value' => $id),
                'submit'  => array(
                    'type'  => 'image',
                    'src' => $THEME->get_url('images/icon_close.gif'),
                    'value' => get_string('delete'),
                    'elementtitle' => get_string('delete'),
                    'confirm' => get_string('reallydeletethiscomment', 'artefact.comment'),
                    'name'  => 'delete_comment_submit',
                ),
            ),
        );
    }

    public function exportable() {
        return empty($this->deletedby);
    }

    public function get_view_url($viewid, $showcomment=true) {
        if ($artefact = $this->get('onartefact')) {
            $url = get_config('wwwroot') . 'view/artefact.php?view=' . $viewid . '&artefact=' . $artefact;
        }
        else {
            $url = get_config('wwwroot') . 'view/view.php?id=' . $viewid;
        }
        if ($showcomment) {
            $url .= '&showcomment=' . $this->get('id');
        }
        return $url;
    }

    // Check whether the logged-in user can see a comment within the
    // context of a given view.  Does not check whether the user can
    // view the view.
    public function viewable_in($viewid) {
        global $USER;
        if ($this->get('deletedby')) {
            return false;
        }

        if ($USER->is_logged_in()) {
            if ($USER->can_view_artefact($this)) {
                return true;
            }
            if ($this->get('author') == $USER->get('id')) {
                return true;
            }
        }

        if ($this->get('private')) {
            return false;
        }

        if ($onview = $this->get('onview')) {
            return $onview == $viewid;
        }

        if ($onartefact = $this->get('onartefact')) {
            return artefact_in_view($onartefact, $viewid);
        }

        return false;
    }

    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        $elements =  array(
            'commentratings' => array(
                'type'  => 'checkbox',
                'title' => get_string('commentratings', 'artefact.comment'),
                'defaultvalue' => get_config_plugin('artefact', 'comment', 'commentratings'),
                'help'  => true,
            ),
        );
        return array(
            'name'     => 'commentconfig',
            'elements' => $elements,
            'renderer' => 'table'
        );
    }

    public static function save_config_options($values) {
        foreach (array('commentratings') as $settingname) {
            set_config_plugin('artefact', 'comment', $settingname, $values[$settingname]);
        }
    }
}

/* To make private comments public, both the author and the owner must agree. */
function make_public_validate(Pieform $form, $values) {
    global $USER;
    $comment = new ArtefactTypeComment((int) $values['comment']);

    $author    = $comment->get('author');
    $owner     = $comment->get('owner');
    $requester = $USER->get('id');

    if (!$author || !$owner || !$requester || ($requester != $owner && $requester != $author)) {
        $form->set_error('comment', get_string('makepublicnotallowed', 'artefact.comment'));
    }
}

function make_public_submit(Pieform $form, $values) {
    global $SESSION, $USER, $view;

    $comment = new ArtefactTypeComment((int) $values['comment']);

    $url = $comment->get_view_url($view->get('id'));

    $author    = $comment->get('author');
    $owner     = $comment->get('owner');
    $requester = $USER->get('id');

    if (($author == $owner && $requester == $owner)
        || ($requester == $owner  && $comment->get('requestpublic') == 'author')
        || ($requester == $author && $comment->get('requestpublic') == 'owner')) {
        $comment->set('private', 0);
        $comment->set('requestpublic', null);
        $comment->commit();
        $SESSION->add_ok_msg(get_string('commentmadepublic', 'artefact.comment'));
        redirect($url);
    }

    $subject = 'makepublicrequestsubject';
    if ($requester == $owner) {
        $comment->set('requestpublic', 'owner');
        $message = 'makepublicrequestbyownermessage';
        $arg = display_name($owner, $author);
        $userid = $author;
        $sessionmessage = get_string('makepublicrequestsent', 'artefact.comment', display_name($author));
    }
    else if ($requester == $author) {
        $comment->set('requestpublic', 'author');
        $message = 'makepublicrequestbyauthormessage';
        $arg = display_name($author, $owner);
        $userid = $owner;
        $sessionmessage = get_string('makepublicrequestsent', 'artefact.comment', display_name($owner));
    }
    else {
        redirect($url); // Freak out?
    }

    db_begin();
    $comment->commit();

    $data = (object) array(
        'subject'   => false,
        'message'   => false,
        'strings'   => (object) array(
            'subject' => (object) array(
                'key'     => $subject,
                'section' => 'artefact.comment',
                'args'    => array(),
            ),
            'message' => (object) array(
                'key'     => $message,
                'section' => 'artefact.comment',
                'args'    => array(hsc($arg)),
            ),
            'urltext' => (object) array(
                'key'     => 'Comment',
                'section' => 'artefact.comment',
            ),
        ),
        'users'     => array($userid),
        'url'       => $url,
    );
    activity_occurred('maharamessage', $data);
    db_commit();

    $SESSION->add_ok_msg($sessionmessage);
    redirect($url);
}

function delete_comment_submit(Pieform $form, $values) {
    global $SESSION, $USER, $view;

    $comment = new ArtefactTypeComment((int) $values['comment']);

    if ($USER->get('id') == $comment->get('author')) {
        $deletedby = 'author';
    }
    else if ($USER->can_edit_view($view)) {
        $deletedby = 'owner';
    }
    else if ($USER->get('admin')) {
        $deletedby = 'admin';
    }

    $viewid = $view->get('id');
    if ($artefact = $comment->get('onartefact')) {
        $url = get_config('wwwroot') . 'view/artefact.php?view=' . $viewid . '&artefact=' . $artefact;
    }
    else {
        $url = get_config('wwwroot') . 'view/view.php?id=' . $viewid;
    }

    db_begin();

    $comment->set('deletedby', $deletedby);
    $comment->commit();

    if ($deletedby != 'author') {
        // Notify author
        if ($artefact) {
            $title = get_field('artefact', 'title', 'id', $artefact);
        }
        else {
            $title = get_field('view', 'title', 'id', $comment->get('onview'));
        }
        $title = hsc($title);
        $data = (object) array(
            'subject'   => false,
            'message'   => false,
            'strings'   => (object) array(
                'subject' => (object) array(
                    'key'     => 'commentdeletednotificationsubject',
                    'section' => 'artefact.comment',
                    'args'    => array($title),
                ),
                'message' => (object) array(
                    'key'     => 'commentdeletedauthornotification',
                    'section' => 'artefact.comment',
                    'args'    => array($title, html2text($comment->get('description'))),
                ),
                'urltext' => (object) array(
                    'key'     => $artefact ? 'artefact' : 'view',
                ),
            ),
            'users'     => array($comment->get('author')),
            'url'       => $url,
        );
        activity_occurred('maharamessage', $data);
    }
    if ($deletedby != 'owner' && $comment->get('owner') != $USER->get('id')) {
        // Notify owner
        $data = (object) array(
            'commentid' => $comment->get('id'),
            'viewid'    => $view->get('id'),
        );
        activity_occurred('feedback', $data, 'artefact', 'comment');
    }

    db_commit();

    $SESSION->add_ok_msg(get_string('commentremoved', 'artefact.comment'));
    redirect($url);
}

function add_feedback_form_validate(Pieform $form, $values) {
    if ($form->get_property('spam')) {
        require_once(get_config('libroot') . 'antispam.php');
        $spamtrap = new_spam_trap(array(
            array(
                'type' => 'body',
                'value' => $values['message'],
            ),
        ));

        if ($form->spam_error() || $spamtrap->is_spam()) {
            $msg = get_string('formerror');
            $emailcontact = get_config('emailcontact');
            if (!empty($emailcontact)) {
                $msg .= ' ' . get_string('formerroremail', 'mahara', $emailcontact, $emailcontact);
            }
            $form->set_error('message', $msg);
        }
    }
    if (empty($values['attachments']) && empty($values['message'])) {
        $form->set_error('message', get_string('messageempty', 'artefact.comment'));
    }
}

function add_feedback_form_submit(Pieform $form, $values) {
    global $view, $artefact, $USER;
    $data = (object) array(
        'title'       => get_string('Comment', 'artefact.comment'),
        'description' => $values['message'],
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

    if (isset($values['moderate']) && $values['ispublic'] && !$USER->can_edit_view($view)) {
        $data->private = 1;
        $data->requestpublic = 'author';
    }
    else {
        $data->private = (int) !$values['ispublic'];
    }

    if (isset($values['rating'])) {
        $data->rating = valid_rating($values['rating']);
    }

    $comment = new ArtefactTypeComment(0, $data);

    db_begin();

    $comment->commit();

    $goto = $comment->get_view_url($view->get('id'));

    if (isset($data->requestpublic) && $data->requestpublic === 'author' && $data->owner) {
        $arg = $author ? display_name($USER, null, true) : $data->authorname;
        $moderatemsg = (object) array(
            'subject'   => false,
            'message'   => false,
            'strings'   => (object) array(
                'subject' => (object) array(
                    'key'     => 'makepublicrequestsubject',
                    'section' => 'artefact.comment',
                    'args'    => array(),
                ),
                'message' => (object) array(
                    'key'     => 'makepublicrequestbyauthormessage',
                    'section' => 'artefact.comment',
                    'args'    => array(hsc($arg)),
                ),
                'urltext' => (object) array(
                    'key'     => 'Comment',
                    'section' => 'artefact.comment',
                ),
            ),
            'users'     => array($data->owner),
            'url'       => $goto,
        );
    }

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

            $originalname = $_FILES[$filesindex]['name'];
            $attachment->title = ArtefactTypeFileBase::get_new_file_title(
                $originalname,
                $folderid,
                $data->owner,
                $data->group,
                $data->institution
            );

            try {
                $fileid = ArtefactTypeFile::save_uploaded_file($filesindex, $attachment);
            }
            catch (QuotaExceededException $e) {
                if ($data->owner == $USER->get('id')) {
                    $form->reply(PIEFORM_ERR, array('message' => $e->getMessage()));
                }
                redirect($goto);
            }
            catch (UploadException $e) {
                $form->reply(PIEFORM_ERR, array('message' => $e->getMessage()));
                redirect($goto);
            }

            $comment->attach($fileid);
        }
    }

    require_once('activity.php');
    $data = (object) array(
        'commentid' => $comment->get('id'),
        'viewid'    => $view->get('id')
    );
    activity_occurred('feedback', $data, 'artefact', 'comment');

    if (isset($moderatemsg)) {
        activity_occurred('maharamessage', $moderatemsg);
    }

    db_commit();

    $newlist = ArtefactTypeComment::get_comments(10, 0, 'last', $view, $artefact);

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

class ActivityTypeArtefactCommentFeedback extends ActivityTypePlugin {

    protected $viewid;
    protected $commentid;

    /**
     * @param array $data Parameters:
     *                    - viewid (int)
     *                    - commentid (int)
     */
    public function __construct($data, $cron=false) {
        parent::__construct($data, $cron);

        $comment = new ArtefactTypeComment($this->commentid);

        $this->overridemessagecontents = true;

        if ($onartefact = $comment->get('onartefact')) { // feedback on artefact
            $userid = null;
            require_once(get_config('docroot') . 'artefact/lib.php');
            $artefactinstance = artefact_instance_from_id($onartefact);
            if ($artefactinstance->feedback_notify_owner()) {
                $userid = $artefactinstance->get('owner');
            }
            if (empty($this->url)) {
                $this->url = get_config('wwwroot') . 'view/artefact.php?artefact='
                    . $onartefact . '&view=' . $this->viewid;
            }
        }
        else { // feedback on view.
            $onview = $comment->get('onview');
            if (!$viewrecord = get_record('view', 'id', $onview)) {
                throw new ViewNotFoundException(get_string('viewnotfound', 'error', $onview));
            }
            $userid = $viewrecord->owner;
            if (empty($this->url)) {
                $this->url = get_config('wwwroot') . 'view/view.php?id=' . $onview;
            }
        }
        if (empty($userid)) {
            return;
        }

        $this->users = activity_get_users($this->get_id(), array($userid));
        $title = $onartefact ? $artefactinstance->get('title') : $viewrecord->title;
        $this->urltext = $title;
        $body = $comment->get('description');
        $posttime = strftime(get_string('strftimedaydatetime'), $comment->get('ctime'));
        $user = $this->users[0];
        $lang = (empty($user->lang) || $user->lang == 'default') ? get_config('lang') : $user->lang;

        // Internal
        $this->message = strip_tags(str_shorten_html($body, 200, true));

        // Comment deleted notification
        if ($deletedby = $comment->get('deletedby')) {
            $this->strings = (object) array(
                'subject' => (object) array(
                    'key'     => 'commentdeletednotificationsubject',
                    'section' => 'artefact.comment',
                    'args'    => array($title),
                ),
            );
            $deletedmessage = ArtefactTypeComment::deleted_messages();
            $removedbyline = get_string_from_language($lang, $deletedmessage[$deletedby], 'artefact.comment');
            $this->message = $removedbyline . ":\n" . $this->message;

            // Email
            $this->users[0]->htmlmessage = get_string_from_language(
                $lang, 'feedbackdeletedhtml', 'artefact.comment',
                hsc($title), $removedbyline, clean_html($body), $this->url, hsc($title)
            );
            $this->users[0]->emailmessage = get_string_from_language(
                $lang, 'feedbackdeletedtext', 'artefact.comment',
                $title, $removedbyline, trim(html2text($body)), $title, $this->url
            );
            return;
        }

        $this->strings = (object) array(
            'subject' => (object) array(
                'key'     => 'newfeedbacknotificationsubject',
                'section' => 'artefact.comment',
                'args'    => array($title),
            ),
        );

        $this->url .= '&showcomment=' . $comment->get('id');

        // Email
        $author = $comment->get('author');
        $authorname = empty($author) ? $comment->get('authorname') : display_name($author, $user);

        $this->users[0]->htmlmessage = get_string_from_language(
            $lang, 'feedbacknotificationhtml', 'artefact.comment',
            hsc($authorname), hsc($title), $posttime, clean_html($body), $this->url
        );
        $this->users[0]->emailmessage = get_string_from_language(
            $lang, 'feedbacknotificationtext', 'artefact.comment',
            $authorname, $title, $posttime, trim(html2text($body)), $this->url
        );
    }

    public function get_plugintype(){
        return 'artefact';
    }

    public function get_pluginname(){
        return 'comment';
    }

    public function get_required_parameters() {
        return array('commentid', 'viewid');
    }
}

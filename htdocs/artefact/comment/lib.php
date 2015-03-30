<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-comment
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

require_once('activity.php');
require_once('license.php');

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
                'allownonemethod' => 1,
                'defaultmethod' => 'email',
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

    public static function progressbar_link($artefacttype) {
        switch ($artefacttype) {
            case 'feedback':
                return 'view/sharedviews.php';
                break;
        }
    }

    public static function progressbar_additional_items() {
        return array(
            (object)array(
                    'name' => 'feedback',
                    'title' => get_string('placefeedback', 'artefact.comment'),
                    'plugin' => 'comment',
                    'active' => true,
                    'iscountable' => true,
                    'is_metaartefact' => true,
            )
        );
    }

    public static function progressbar_metaartefact_count($name) {
        global $USER;
        $meta = new stdClass();
        $meta->artefacttype = $name;
        $meta->completed = 0;
        switch ($name) {
            case 'feedback':
                $sql = "SELECT COUNT(*) AS completed
                         FROM {artefact}
                       WHERE artefacttype='comment'
                         AND owner <> ? AND author = ?";
                $count = get_records_sql_array($sql, array($USER->get('id'), $USER->get('id')));
                $meta->completed = $count[0]->completed;
                break;
            default:
                return false;
        }
        return $meta;
    }
}

class ArtefactTypeComment extends ArtefactType {

    protected $onview;
    protected $onartefact;
    protected $private;
    protected $deletedby;
    protected $requestpublic;
    protected $rating;
    protected $lastcontentupdate;

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

    public static function is_allowed_in_progressbar() {
        return false;
    }


    /**
     * For comments, the artefact.mtime property is displayed to users, as the "Update on" date,
     * if it is later than the artefact's creation time. The purpose of this is for transparency
     * in communication, so that people will know that a later comment may be in response to one
     * that no longer exists.
     *
     * So, we don't want the publishing
     * @see ArtefactType::set()
     */
    public function set($field, $value) {
        if (($field == 'title' || $field == 'description') && $this->{$field} != $value) {
            $this->lastcontentupdate = $this->mtime;
        }
        return parent::set($field, $value);
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
        if ($this->get('lastcontentupdate')) {
            $data->lastcontentupdate = db_format_timestamp($this->get('lastcontentupdate'));
        }

        if ($new) {
            insert_record('artefact_comment_comment', $data);
        }
        else {
            update_record('artefact_comment_comment', $data, 'artefact');
        }

        if (!$this->get('private')) {
            if ($this->get('onview')) {
                set_field('view', 'mtime', db_format_timestamp(time()), 'id', $this->get('onview'));
            }
            else if ($this->get('onartefact')) {
                execute_sql("UPDATE {view} SET mtime = ? WHERE id IN (SELECT va.view FROM {view_artefact} va WHERE va.artefact = ?)", array(db_format_timestamp(time()), $this->get('onartefact')));
            }
        }
        db_commit();
        $this->dirty = false;
    }

    public static function is_singular() {
        return false;
    }

    public static function get_icon($options=null) {
        global $THEME;
        return $THEME->get_image_url('comment', 'artefact/comment');
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

    /**
     * Generates default data object required for displaying comments on the page.
     * The is called before populating with specific data to send to get_comments() as
     * an easy way to add variables to get passed to get_comments.
     *
     * int $limit              The number of comments to display (set to
     *                         0 for disabling pagination and showing all comments)
     * int $offset             The offset of comments used for pagination
     * int|string $showcomment Optionally show page with particular comment
     *                         on it or the last page. $offset will be ignored.
     *                         Specify either comment_id or 'last' respectively.
     *                         Set to null to use $offset for pagination.
     * object $view            The view object
     * object $artefact        Optional artefact object
     * bool   $export          Determines if comments are fetched for html export purposes
     * bool   $onview          Optional - is viewing artefact comments on view page so don't show edit buttons
     * string $sort            Optional - the sort order of the comments. Valid options are 'earliest' and 'latest'.
     * @return object $options Default comments data object
     */
    public static function get_comment_options() {
        $options = new stdClass();
        $options->limit = 10;
        $options->offset = 0;
        $options->showcomment = null;
        $options->view = null;
        $options->artefact = null;
        $options->export = false;
        $options->onview = false;
        $sortorder = get_user_institution_comment_sort_order();
        $options->sort = (!empty($sortorder)) ? $sortorder : 'earliest';
        return $options;
    }

    /**
     * Generates the data object required for displaying comments on the page.
     *
     * @param   object  $options  Object of comment options
     *                            - defaults can be retrieved from get_comment_options()
     * @return  object $result    Comment data object
     */
    public static function get_comments($options) {
        global $USER;
        // set the object's key/val pairs as variables
        foreach ($options as $key => $option) {
            $$key = $option;
        }
        $userid = $USER->get('id');
        $viewid = $view->get('id');
        if (!empty($artefact)) {
            $canedit = $USER->can_edit_artefact($artefact);
            $owner = $artefact->get('owner');
            $isowner = $userid && $userid == $owner;
            $artefactid = $artefact->get('id');
        }
        else {
            $canedit = $USER->can_moderate_view($view);
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
            'export'   => $export,
            'sort'     => $sort,
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
            // If pagination is in use, see if we want to get a page with particular comment
            if ($limit) {
                if ($showcomment == 'last') {
                    // If we have limit (pagination is used) ignore $offset and just get the last page of feedback.
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
                        // Add 1 because array index starts from 0 and therefore key value is offset by 1.
                        $rank = key($ids) + 1;
                        $result->forceoffset = $offset = ((ceil($rank / $limit) - 1) * $limit);
                        $result->showcomment = $showcomment;
                    }
                }
            }

            $sortorder = (!empty($sort) && $sort == 'latest') ? 'a.ctime DESC' : 'a.ctime ASC';
            $comments = get_records_sql_assoc('
                SELECT
                    a.id, a.author, a.authorname, a.ctime, a.mtime, a.description, a.group,
                    c.private, c.deletedby, c.requestpublic, c.rating, c.lastcontentupdate,
                    u.username, u.firstname, u.lastname, u.preferredname, u.email, u.staff, u.admin,
                    u.deleted, u.profileicon, u.urlid
                FROM {artefact} a
                    INNER JOIN {artefact_comment_comment} c ON a.id = c.artefact
                    LEFT JOIN {usr} u ON a.author = u.id
                WHERE ' . $where . '
                ORDER BY ' . $sortorder, array(), $offset, $limit);

            $files = ArtefactType::attachments_from_id_list(array_keys($comments));

            if ($files) {
                safe_require('artefact', 'file');
                foreach ($files as &$file) {
                    $comments[$file->artefact]->attachments[] = $file;
                }
            }

            $result->data = array_values($comments);
        }

        // check to see if the feedback is to be displayed in a block instance
        // or the base of the page
        $result->position = 'base';
        $blocks = get_records_array('block_instance', 'view', $viewid);
        if (!empty($blocks)) {
            foreach ($blocks as $block) {
                if ($block->blocktype == 'comment') {
                    $result->position = 'blockinstance';
                    break;
                }
            }
        }

        self::build_html($result, $onview);
        return $result;
    }

    public static function count_comments($viewids=null, $artefactids=null) {
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

    /**
     * Fetching the comments for an artefact to display on a view
     *
     * @param   ArtefactType  $artfact  The artefact to display comments for
     * @param   object  $view     The view on which the artefact appears
     * @param   int     $blockid  The id of the block instance that connects the artefact to the view
     * @param   bool    $html     Whether to return the information rendered as html or not
     * @param   bool    $editing  Whether we are view edit mode or not
     *
     * @return  array   $commentcount, $comments   The count of comments and either the comments
     *                                             or the html to render them.
     */
    public function get_artefact_comments_for_view(ArtefactType $artefact, $view, $blockid, $html = true, $editing = false) {
        if (!is_object($artefact) || !is_object($view)) {
            throw new MaharaException('we do not have the right information to display the comments');
        }

        $commentoptions = ArtefactTypeComment::get_comment_options();
        $commentoptions->limit = 0;
        $commentoptions->view = $view;
        $commentoptions->artefact = $artefact;
        $commentoptions->onview = true;
        $comments = ArtefactTypeComment::get_comments($commentoptions);
        $commentcount = isset($comments->count) ? $comments->count : 0;

        // If there are no comments, and comments are not allowed, don't display anything.
        if ($commentcount == 0 && !$artefact->get('allowcomments')) {
            return array(0, '');
        }

        $artefacturl = get_config('wwwroot') . 'artefact/artefact.php?view=' . $view->get('id') . '&artefact=' . $artefact->get('id');
        if ($html) {
            $smarty = smarty_core();
            $smarty->assign('artefacturl', $artefacturl);
            $smarty->assign('blockid', $blockid);
            $smarty->assign('commentcount', $commentcount);
            $smarty->assign('comments', $comments);
            $smarty->assign('editing', $editing);
            $smarty->assign('allowcomments', $artefact->get('allowcomments'));
            $render = $smarty->fetch('artefact/artefactcommentsview.tpl');
            return array($commentcount, $render);
        }
        else {
            return array($commentcount, $comments);
        }
    }

    public static function deleted_messages() {
        return array(
            'author' => 'commentremovedbyauthor',
            'owner'  => 'commentremovedbyowner',
            'admin'  => 'commentremovedbyadmin',
        );
    }

    public static function build_html(&$data, $onview) {
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
            if ($item->ts < strtotime($item->lastcontentupdate)) {
                $item->updated = format_date(strtotime($item->lastcontentupdate), 'strftimedatetime');
            }
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
            $is_export_preview = param_integer('export',0);
            if ($item->deletedby) {
                $item->deletedmessage = $deletedmessage[$item->deletedby];
            }
            else if (($candelete || $item->isauthor) && !$is_export_preview) {
                $item->deleteform = pieform(self::delete_comment_form($item->id));
            }

            // Comment authors can edit recent comments if they're private or if no one has replied yet.
            if (!$item->deletedby && $item->isauthor && !$is_export_preview
                && ($item->private || $item->id == $lastcomment->id) && $item->ts > $editableafter) {
                $item->canedit = 1;
            }

            // Form to make private comment public, or request that a
            // private comment be made public
            if (!$item->deletedby && $item->private && $item->author && $data->owner
                && ($item->isauthor || $data->isowner)) {
                if ((empty($item->requestpublic) && $data->isowner)
                    || $item->isauthor && $item->requestpublic == 'owner'
                    || $data->isowner && $item->requestpublic == 'author') {
                    if (!$is_export_preview) {
                        $item->makepublicform = pieform(self::make_public_form($item->id));
                    }
                }
                else if ($item->isauthor && $item->requestpublic == 'author'
                         || $data->isowner && $item->requestpublic == 'owner') {
                    $item->makepublicrequested = 1;
                }
            }
            else if (!$item->deletedby && $item->private && !$item->author
                && $data->owner && $data->isowner && $item->requestpublic == 'author' && !$is_export_preview) {
                $item->makepublicform = pieform(self::make_public_form($item->id));
            }
            else if (!$item->deletedby && $item->private && !$data->owner
                && $item->group && $item->requestpublic == 'author') {
                // no owner as comment is on a group view / artefact
                if ($item->isauthor) {
                    $item->makepublicrequested = 1;
                }
                else {
                    if (($data->artefact && $data->canedit) || ($data->view && $data->canedit) && !$is_export_preview) {
                        $item->makepublicform = pieform(self::make_public_form($item->id));
                    }
                    else {
                        $item->makepublicrequested = 1;
                    }
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
                        'profileurl'    => profile_url($item->author),
                    );
                }
            }

            if (get_config_plugin('artefact', 'comment', 'commentratings') and $item->rating) {
                $item->ratingdata = (object) array(
                    'value' => valid_rating($item->rating),
                    'min_rating' => MIN_RATING,
                    'max_rating' => MAX_RATING,
                    'export' => $data->export,
                );
            }
        }

        $extradata = array('view' => $data->view);
        $data->jsonscript = 'artefact/comment/comments.json.php';

        if (!empty($data->artefact)) {
            $data->baseurl = get_config('wwwroot') . 'artefact/artefact.php?view=' . $data->view . '&artefact=' . $data->artefact;
            $extradata['artefact'] = $data->artefact;
        }
        else {
            $data->baseurl = get_config('wwwroot') . 'view/view.php?id=' . $data->view;
        }

        $smarty = smarty_core();
        $smarty->assign_by_ref('data', $data->data);
        $smarty->assign('canedit', $data->canedit);
        $smarty->assign('viewid', $data->view);
        $smarty->assign('position', $data->position);
        $smarty->assign('baseurl', $data->baseurl);
        $smarty->assign('onview', $onview);
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
            'type'  => 'switchbox',
            'title' => get_string('makepublic', 'artefact.comment'),
            'defaultvalue' => !$defaultprivate,
        );
        if (get_config('licensemetadata')) {
            $form['elements']['license'] = license_form_el_basic(null);
            $form['elements']['licensing_advanced'] = license_form_el_advanced(null);
        }
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
                    'src' => $THEME->get_image_url('btn_deleteremove'),
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

    public function get_view_url($viewid, $showcomment=true, $full=true) {
        if ($artefact = $this->get('onartefact')) {
            $url = 'artefact/artefact.php?view=' . $viewid . '&artefact=' . $artefact;
        }
        else {
            $url = 'view/view.php?id=' . $viewid;
        }
        if ($showcomment) {
            $url .= '&showcomment=' . $this->get('id');
        }
        if ($full) {
            $url = get_config('wwwroot') . $url;
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
                'type'  => 'switchbox',
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

    public static function save_config_options($form, $values) {
        foreach (array('commentratings') as $settingname) {
            set_config_plugin('artefact', 'comment', $settingname, $values[$settingname]);
        }
    }

    /**
     * Fetch all users that are currently watching the view the comment is being added to,
     * in case of comment on artefact it will be the view the artefact sits on,
     * so we can use this array to email affected parties.
     *
     * @param   int    $posterid   If set, the poster's id is ignored from resulting array
     * @param   int    $ownerid    If set, we make sure the owner is emailed even if they don't have a comment
     * @return  array  $users      An array of user objects of the users that have comments on this view/artefact
     */
    public function get_comments_users($posterid = null, $ownerid = null) {
        $ontype = ($onview = $this->get('onview')) ? 'onview' : 'onartefact';
        $values = array();
        $sql = "SELECT DISTINCT u.* FROM {usr} u";
        if ($ontype == 'onview') {
            $sql .= " JOIN {usr_watchlist_view} uwv ON uwv.view = ? AND uwv.usr = u.id";
            $values[] = $this->get($ontype);
        }
        else if ($ontype == 'onartefact') {
            $sql .= " JOIN {view_artefact} va ON va.artefact = ?
                      JOIN {usr_watchlist_view} uwv ON uwv.view = va.view AND uwv.usr = u.id";
            $values[] = $this->get($ontype);
        }
        if ($posterid) {
            $sql .= " WHERE u.id != ?";
            $values[] = $posterid;
        }
        if (!empty($ownerid) && $ownerid != $posterid) {
            $sql .= " UNION SELECT * FROM {usr} WHERE id = ?";
            $values[] = $ownerid;
        }

        $users = get_records_sql_assoc($sql, $values);
        return $users;
    }
}

/* To make private comments public, both the author and the owner must agree. */
function make_public_validate(Pieform $form, $values) {
    global $USER;
    $comment = new ArtefactTypeComment((int) $values['comment']);

    $author    = $comment->get('author');
    $owner     = $comment->get('owner');
    $requester = $USER->get('id');
    $group     = $comment->get('group');

    if (!$owner && !$group) {
        $form->set_error('comment', get_string('makepublicnotallowed', 'artefact.comment'));
    }
    else if (!$owner && $group) {
        if ($requester) {
            $allowed = false;
            // check to see if the requester is a group admin
            $group_admins = group_get_admin_ids($group);
            if (array_search($requester,$group_admins) === false) {
                $form->set_error('comment', get_string('makepublicnotallowed', 'artefact.comment'));
            }
        }
        else {
            $form->set_error('comment', get_string('makepublicnotallowed', 'artefact.comment'));
        }
    }
    else if (!$owner || !$requester || ($requester != $owner && $requester != $author)) {
        $form->set_error('comment', get_string('makepublicnotallowed', 'artefact.comment'));
    }
}

function make_public_submit(Pieform $form, $values) {
    global $SESSION, $USER, $view;

    $comment = new ArtefactTypeComment((int) $values['comment']);

    $relativeurl = $comment->get_view_url($view->get('id'), true, false);
    $url = get_config('wwwroot') . $relativeurl;

    $author    = $comment->get('author');
    $owner     = $comment->get('owner');
    $groupid   = $comment->get('group');
    $group_admins = array();
    if ($groupid) {
        $group_admins = group_get_admin_ids($groupid);
    }
    $requester = $USER->get('id');

    if (($author == $owner && $requester == $owner)
        || ($requester == $owner  && $comment->get('requestpublic') == 'author')
        || (array_search($requester,$group_admins) !== false && $comment->get('requestpublic') == 'author')
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
    else if (array_search($requester,$group_admins) !== false) {
        $comment->set('requestpublic', 'owner');
        $message = 'makepublicrequestbyownermessage';
        $arg = display_name($requester, $author);
        $userid = $author;
        $sessionmessage = get_string('makepublicrequestsent', 'artefact.comment', display_name($author));
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
        'url'       => $relativeurl,
    );
    activity_occurred('maharamessage', $data);
    db_commit();

    $SESSION->add_ok_msg($sessionmessage);
    redirect($url);
}

function delete_comment_submit(Pieform $form, $values) {
    global $SESSION, $USER, $view;
    require_once('embeddedimage.php');

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
        $url = 'artefact/artefact.php?view=' . $viewid . '&artefact=' . $artefact;
    }
    else {
        $url = $view->get_url(false);
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

    EmbeddedImage::delete_embedded_images('comment', $comment->get('id'));
    db_commit();

    $SESSION->add_ok_msg(get_string('commentremoved', 'artefact.comment'));
    redirect(get_config('wwwroot') . $url);
}

function add_feedback_form_validate(Pieform $form, $values) {
    require_once(get_config('libroot') . 'antispam.php');
    if ($form->get_property('spam')) {
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
    $result = probation_validate_content($values['message']);
    if ($result !== true) {
        $form->set_error('message', get_string('newuserscantpostlinksorimages'));
    }
}

function add_feedback_form_submit(Pieform $form, $values) {
    global $view, $artefact, $USER;
    require_once('embeddedimage.php');
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

    $owner = $data->owner;
    $author = null;
    if ($author = $USER->get('id')) {
        $anonymous = false;
        $data->author = $author;
    }
    else {
        $anonymous = true;
        $data->authorname = $values['authorname'];
    }

    if (isset($values['moderate']) && $values['ispublic'] && !$USER->can_edit_view($view)) {
        $data->private = 1;
        $data->requestpublic = 'author';
        $moderated = true;
    }
    else {
        $data->private = (int) !$values['ispublic'];
        $moderated = false;
    }
    $private = $data->private;

    if (get_config('licensemetadata')) {
        $data->license       = $values['license'];
        $data->licensor      = $values['licensor'];
        $data->licensorurl   = $values['licensorurl'];
    }

    if (isset($values['rating'])) {
        $data->rating = valid_rating($values['rating']);
    }

    $comment = new ArtefactTypeComment(0, $data);

    db_begin();

    $comment->commit();

    $newdescription = EmbeddedImage::prepare_embedded_images($values['message'], 'comment', $comment->get('id'), $data->group);

    if ($newdescription !== $values['message']) {
        $updatedcomment = new stdClass();
        $updatedcomment->id = $comment->get('id');
        $updatedcomment->description = $newdescription;
        update_record('artefact', $updatedcomment, 'id');
    }

    $url = $comment->get_view_url($view->get('id'), true, false);
    $goto = get_config('wwwroot') . $url;

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
            'url'       => $url,
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


    // We want to add the user placing the comment to the watchlist so they
    // can get notified about future comments to the page.
    // @TODO Add a site/institution preference to override this.
    if (!get_field('usr_watchlist_view', 'ctime', 'usr', $author, 'view', $view->get('id'))) {
        insert_record('usr_watchlist_view', (object) array('usr' => $author,
                                                           'view' => $view->get('id'),
                                                           'ctime' => db_format_timestamp(time())));
    }
    if (!$private) {
        // We want to alert all interested parties that a new public comment was added
        if ($users = $comment->get_comments_users($author, $owner)) {
            $data->users = $users;
        }
    }

    activity_occurred('feedback', $data, 'artefact', 'comment');

    if (isset($moderatemsg)) {
        activity_occurred('maharamessage', $moderatemsg);
    }

    db_commit();

    $commentoptions = ArtefactTypeComment::get_comment_options();
    $commentoptions->showcomment = 'last';
    $commentoptions->view = $view;
    $commentoptions->artefact = $artefact;
    $newlist = ArtefactTypeComment::get_comments($commentoptions);

    // If you're anonymous and your message is moderated or private, then you won't
    // be able to tell what happened to it. So we'll provide some more explanation in
    // the feedback message.
    if ($anonymous && $moderated) {
        $message = get_string('feedbacksubmittedmoderatedanon', 'artefact.comment');
    }
    else if ($anonymous && $private) {
        $message = get_string('feedbacksubmittedprivateanon', 'artefact.comment');
    }
    else {
        $message = get_string('feedbacksubmitted', 'artefact.comment');
    }

    $form->reply(PIEFORM_OK, array(
        'message' => $message,
        'goto' => $goto,
        'data' => $newlist,
    ));
}

function add_feedback_form_cancel_submit(Pieform $form) {
    global $view;
    $form->reply(PIEFORM_CANCEL, array(
        'location' => $view->get_url(true),
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
                $groupid = $artefactinstance->get('group');
                $institutionid = $artefactinstance->get('institution');
            }
            if (empty($this->url)) {
                $this->url = 'artefact/artefact.php?artefact=' . $onartefact . '&view=' . $this->viewid;
            }
        }
        else { // feedback on view.
            $onview = $comment->get('onview');
            if (!$viewrecord = get_record('view', 'id', $onview)) {
                throw new ViewNotFoundException(get_string('viewnotfound', 'error', $onview));
            }
            $userid = $viewrecord->owner;
            $groupid = $viewrecord->group;
            $institutionid =  $viewrecord->institution;
            if (empty($this->url)) {
                $this->url = 'view/view.php?id=' . $onview;
            }
        }
        // Now fetch the users that will need to get notified about this event
        // depending on whether the page has an owner, group, or institution id set.
        if (!empty($userid)) {
            $users = $this->users;
            if (empty($users)) {
                $this->users = activity_get_users($this->get_id(), array($userid));
            }
            else {
                $this->users = array_values($users);
            }
        }
        else if (!empty($groupid)) {
            require_once(get_config('docroot') . 'lib/group.php');
            $this->users = get_records_sql_array("SELECT u.* from {usr} u, {group_member} m, {group} g
                                                       WHERE g.id = m.group AND m.member = u.id AND m.group = ?
                                                       AND (g.feedbacknotify = " . GROUP_ROLES_ALL . "
                                                           OR (g.feedbacknotify = " . GROUP_ROLES_NONMEMBER . " AND (m.role = 'tutor' OR m.role = 'admin'))
                                                           OR (g.feedbacknotify = " . GROUP_ROLES_ADMIN . " AND m.role = 'admin')
                                                       )", array($groupid));
        }
        else if (!empty($institutionid)) {
            require_once(get_config('libroot') .'institution.php');
            $institution = new Institution($institutionid);
            $admins = $institution->institution_and_site_admins();
            $this->users = get_records_sql_array("SELECT * FROM {usr} WHERE id IN (" . implode(',', $admins) . ")", array());
        }

        if (empty($this->users)) {
            // no one to notify - possibe if group 'feedbacknotify' is set to 0
            return;
        }

        $title = $onartefact ? $artefactinstance->get('title') : $viewrecord->title;
        $this->urltext = $title;
        $body = $comment->get('description');
        $posttime = strftime(get_string('strftimedaydatetime'), $comment->get('ctime'));

        // Internal
        $this->message = strip_tags(str_shorten_html($body, 200, true));
        // Seen as things like emaildigest base the message on $this->message
        // we need to set the language for the $removedbyline here based on first user.
        $user = $this->users[0];
        $lang = (empty($user->lang) || $user->lang == 'default') ? get_config('lang') : $user->lang;

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

            foreach ($this->users as $key => $user) {
                if (empty($user->lang) || $user->lang == 'default') {
                    // check to see if we need to show institution language
                    $instlang = get_user_institution_language($user->id);
                    $lang = (empty($instlang) || $instlang == 'default') ? get_config('lang') : $instlang;
                }
                else {
                    $lang = $user->lang;
                }
                // For email we can send the message in the user's preferred language
                $removedbyline = get_string_from_language($lang, $deletedmessage[$deletedby], 'artefact.comment');
                $this->users[$key]->htmlmessage = get_string_from_language(
                    $lang, 'feedbackdeletedhtml', 'artefact.comment',
                    hsc($title), $removedbyline, clean_html($body), get_config('wwwroot') . $this->url, hsc($title)
                );
                $this->users[$key]->emailmessage = get_string_from_language(
                    $lang, 'feedbackdeletedtext', 'artefact.comment',
                    $title, $removedbyline, trim(html2text($body)), $title, get_config('wwwroot') . $this->url
                );
            }
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
        foreach ($this->users as $key => $user) {
            $authorname = empty($author) ? $comment->get('authorname') : display_name($author, $user);
            if (empty($user->lang) || $user->lang == 'default') {
                // check to see if we need to show institution language
                $instlang = get_user_institution_language($user->id);
                $lang = (empty($instlang) || $instlang == 'default') ? get_config('lang') : $instlang;
            }
            else {
                $lang = $user->lang;
            }
            $this->users[$key]->htmlmessage = get_string_from_language(
                $lang, 'feedbacknotificationhtml', 'artefact.comment',
                hsc($authorname), hsc($title), $posttime, clean_html($body), get_config('wwwroot') . $this->url
            );
            $this->users[$key]->emailmessage = get_string_from_language(
                $lang, 'feedbacknotificationtext', 'artefact.comment',
                $authorname, $title, $posttime, trim(html2text(htmlspecialchars($body))), get_config('wwwroot') . $this->url
            );
        }
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

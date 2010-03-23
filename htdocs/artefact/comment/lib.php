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

    public static function get_comments($limit, $offset, $lastpage, &$view=null, &$artefact=null) {

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
                $offset = (ceil($count / $limit) - 1) * $limit;
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
            $item->date    = format_date(strtotime($item->ctime), 'strftimedatetime');
            if (!empty($item->attachments)) {
                if ($data->isowner) {
                    // @todo: move strings to comment artefact
                    // @todo: files attached to comments are no longer always 'assessment' files,
                    // so change the string.
                    $item->attachmessage = get_string(
                        'feedbackattachmessage',
                        'view',
                        get_string('feedbackattachdirname', 'view')
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
                $item->makeprivateform = pieform(make_private_form($item->id));
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

}

function make_private_form($id) {
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

?>

<?php

/**
 *
 * @package    mahara
 * @subpackage artefact-checkpoint-feedback
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

require_once('activity.php');
require_once('license.php');

class PluginArtefactCheckpoint extends PluginArtefact {

    public static function is_active() {
        return get_field('artefact_installed', 'active', 'name', 'checkpoint');
    }

    public static function get_artefact_types() {
        return array(
            'checkpointfeedback',
        );
    }

    public static function get_block_types() {
        return array();
    }

    public static function get_plugin_name() {
        return 'checkpoint';
    }

    /**
     * Fetch the human readable name for the plugin
     *
     * @return string
     */
    public static function get_plugin_display_name() {
        return get_string('pluginname', 'artefact.checkpoint');
    }

    public static function menu_items() {
        return array();
    }

    public static function get_event_subscriptions() {
        return array();
    }

    public static function get_activity_types() {
        // These events are handled by this artefact.
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
        return true;
    }

    /**
     * Called post install and after every upgrade to the artefact.
     * @param string $prevversion the previously installed version of this artefact.
     */
    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            // If elasticsearch is installed, update the artefacttypesmap field to include checkpoint
            $sql = "SELECT value FROM {search_config} WHERE plugin='elasticsearch' AND field='artefacttypesmap'";
            if ($result = get_field_sql($sql, array())) {
                $elasticsearchartefacttypesmap = explode("\n", $result);
                // add checkpoint field.
                $elasticsearchartefacttypesmap[] = "checkpoint|Checkpoint|Text";
                // Now save the data including the new checkpoint field.
                set_config_plugin('search', 'elasticsearch', 'artefacttypesmap', implode("\n", $elasticsearchartefacttypesmap));
            }

            // Now install the blocktype checkpoint only if Mahara was previously installed.
            // Otherwise, the Mahara installer will install everything.
            if (get_config('installed')) {
                if ($upgrade = check_upgrades('blocktype.checkpoint/checkpoint')) {
                    return upgrade_plugin($upgrade);
                }
            }
        }
        return true;
    }

    public static function view_export_extra_artefacts($viewids) {
        $artefacts = array();
        return $artefacts;
    }

    public static function get_cron() {
        return array(
            (object)array(
                'callfunction' => 'clean_checkpoint_notifications',
                'minute'       => '55',
                'hour'         => '22',
            ),
        );
    }

    public static function clean_checkpoint_notifications() {
        safe_require('notification', 'internal');
        PluginNotificationInternal::clean_notifications(array('checkpoint'));
    }

    public static function progressbar_link($artefacttype) {
        return 'group/index.php';
    }

    public static function progressbar_additional_items() {
        return array(
            (object)array(
                'name' => 'checkpointfeedback',
                'title' => get_string('checkpointfeedback', 'artefact.checkpoint'),
                'plugin' => 'checkpoint',
                'active' => get_field('blocktype_installed', 'active', 'name', 'checkpoint', 'artefactplugin', 'checkpoint'),
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
            case 'checkpointfeedback':
                $sql = "SELECT COUNT(*) AS completed
                        FROM {artefact} a
                        JOIN {artefact_checkpoint_feedback} ap ON ap.feedback = a.id
                        WHERE a.artefacttype = 'checkpointfeedback'
                        AND ap.author = ?";
                $meta->completed = count_records_sql($sql, array($USER->get('id')));
                break;
            default:
                return false;
        }
        return $meta;
    }
}

class ArtefactTypeCheckpointfeedback extends ArtefactType {

    protected $feedback;      // artefact id of the feedback artefact.
    protected $block;         // block id of the block this checkpoint feedback is linked to.
    protected $author;        // author id of the user who added this checkpoint feedback.
    protected $view;          // view id of the view this checkpoint feedback is linked to.
    protected $activity;      // activity id of the activity this checkpoint feedback is linked to.
    protected $view_obj;      // the view object based on the $view id.
    protected $deletedby;     // what type of person deleted the comment
    protected $private;       // Whether this assessment has been published by the user.
    // 0 = can be seen by author, page owner, manager (published)
    // 1 = can only be seen by author (draft)

    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);
        global $view;

        $activity = get_record('view_activity', 'view', $view->get('id'));
        if ($activity) {
            $this->activity = $activity->id;
            $this->view = $activity->view;
        }

        if ($this->id) {
            $existing_feedback = get_record('artefact_checkpoint_feedback', 'feedback', $this->id);
            if ($existing_feedback) {
                foreach ($existing_feedback as $name => $value) {
                    if (property_exists($this, $name)) {
                        $this->{$name} = $value;
                    }
                }
            }
        }
    }

    public static function is_singular() {
        return false;
    }

    public static function get_icon($options = null) {
        global $THEME;
        return false;
    }

    public static function get_links($id) {
        $artefact = new ArtefactTypeCheckpointfeedback($id);
        require_once(get_config('libroot') . 'view.php');
        $v = new View($artefact->get('view'));
        return array(
            '_default' => $v->get_url(),
        );
    }

    public function get_view_url($viewid, $showcomment = true, $full = true, $editing = false) {
        $url = 'view/view.php?id=' . $viewid;
        if ($editing) {
            $url = 'view/blocks.php?id=' . $viewid;
        }
        if ($showcomment) {
            $url .= '&showfeedback=' . $this->get('id');
        }
        if ($full) {
            $url = get_config('wwwroot') . $url;
        }
        return $url;
    }

    /**
     * @return View the view object this checkpoint block is in
     */

    public function get_view() {
        if (empty($this->view_obj)) {
            require_once('view.php');
            $this->view_obj = new View($this->get('view'));
        }
        return $this->view_obj;
    }

    public function commit() {
        if (empty($this->dirty)) {
            return;
        }

        $new = empty($this->id);

        db_begin();

        parent::commit();

        $data = (object)array(
            'feedback'   => $this->get('id'),
            'block'      => $this->get('block'),
            'author'     => $this->get('author'),
            'activity'   => $this->get('activity'),
            'deletedby'  => $this->get('deletedby'),
        );

        if ($new) {
            insert_record('artefact_checkpoint_feedback', $data);
        }
        else {
            update_record('artefact_checkpoint_feedback', $data, 'feedback');
        }

        if ($this->get('view')) {
            set_field('view', 'mtime', db_format_timestamp(time()), 'id', $this->get('view'));
        }
        db_commit();
        $this->dirty = false;
    }

    public function delete() {
        if (empty($this->id)) {
            return;
        }
        db_begin();
        $this->detach();

        // Remove any embedded images for this checkpoint feedback.
        require_once('embeddedimage.php');
        EmbeddedImage::remove_embedded_images('feedback', $this->id);
        delete_records('artefact_checkpoint_feedback', 'feedback', $this->id);
        parent::delete();
        db_commit();
    }

    public static function bulk_delete($artefactids, $log = false) {
        if (empty($artefactids)) {
            return;
        }

        $idstr = join(',', array_map('intval', $artefactids));

        db_begin();
        delete_records_select('artefact_checkpoint_feedback', 'feedback IN (' . $idstr . ')');
        delete_records_select('artefact_file_embedded', 'resourcetype = ? AND resourceid IN (' . $idstr . ')', array('feedback'));
        parent::bulk_delete($artefactids);
        db_commit();
    }

    public static function delete_view_feedback($viewid) {
        $ids = get_column('artefact_checkpoint_feedback', 'feedback', 'view', $viewid);
        self::bulk_delete($ids);
    }

    public function can_have_attachments() {
        return true;
    }

    public static function deleted_types() {
        return array('author', 'owner', 'admin');
    }

    /**
     * Generates default data object required for displaying checkpoint feedback on the page.
     * The is called before populating with specific data to send to get_assessments() as
     * an easy way to add variables to get passed to get_assessments.
     *
     * int $limit              The number of comments to display (set to
     *                         0 for disabling pagination and showing all comments)
     * int $offset             The offset of comments used for pagination
     * int|string showfeedback Optionally show page with particular comment
     *                         on it or the last page. $offset will be ignored.
     *                         Specify either comment_id or 'last' respectively.
     *                         Set to null to use $offset for pagination.
     * object $view            The view object
     * int $block              Optional The block id that the assessment is linked to.
     * bool   $export          Determines if comments are fetched for html export purposes
     * bool   $onview          Optional - is viewing assessment on view page so don't show edit buttons
     * string $sort            Optional - the sort order of the comments. Valid options are 'earliest' and 'latest'.
     * @return object $options Default checkpoint feedback data object
     */
    public static function get_checkpoint_feedback_options() {
        $options = new stdClass();
        $options->limit = 10;
        $options->offset = 0;
        $options->showfeedback = null;
        $options->view = null;
        $options->block = null;
        $options->export = false;
        $options->onview = false;
        $sortorder = get_user_institution_comment_sort_order();
        $options->sort = (!empty($sortorder)) ? $sortorder : 'earliest';
        return $options;
    }

    /**
     * Get checkpoint activity id for view
     *
     * @return int|false
     */
    public static function get_checkpoint_activity_id() {
        global $view;

        $view_activity = get_record('view_activity', 'view', $view->get('id'));
        if ($view_activity) {
            return $view_activity->id;
        }
        return false;
    }

    /**
     * Generates the data object required for displaying assessments on the page.
     *
     * @param   object  $options  Object of checkpoint feedback options
     *                            - defaults can be retrieved from get_checkpoint_feedback_options()
     * @param   object  $versioning Object with data for the timeline view versions
     * @param   PluginExport    $exporter object used when exporting the portfolios
     * @return  object $result    Assessment data object
     */
    public static function get_checkpoint_feedback($options, $versioning = null, $exporter = null) {
        global $USER;

        $allowedoptions = self::get_checkpoint_feedback_options();

        // vars get populated from $options
        $limit = null;
        $offset = null;
        $block = null;
        $export = null;
        $sort = null;
        $showfeedback = null;
        $view = (object) null;

        // set the object's key/val pairs as variables
        foreach ($options as $key => $option) {
            if (property_exists($allowedoptions, $key));
            $$key = $option;
        }
        $userid = $USER->get('id');
        $viewid = $view->get('id');
        $activityid = ArtefactTypeCheckpointfeedback::get_checkpoint_activity_id();

        $canedit = true; // all group members can add a comment to provide feedback
        $owner = $view->get('owner');
        $isowner = $userid && $userid == $owner;

        $result = (object) array(
            'limit'    => $limit,
            'offset'   => $offset,
            'view'     => $viewid,
            'block'    => $block,
            'canedit'  => $canedit,
            'owner'    => $owner,
            'isowner'  => $isowner,
            'export'   => $export,
            'sort'     => $sort,
            'data'     => array(),
        );


        if ($versioning) {
            $result->data = self::get_checkpoint_feedback_data_for_versioning($versioning, $viewid);
            $result->count = sizeof($result->data);
        }
        else {
            $where = 'cf.activity = ? ';

            // select assessments that are published
            // or select assessments where the user is the author, published or not

            $values = array($activityid, $block);

            $result->count = count_records_sql(
                '
                    SELECT COUNT(*)
                    FROM
                    {artefact} a
                    JOIN {artefact_checkpoint_feedback} cf
                    ON a.id = cf.feedback
                    LEFT JOIN {artefact} p
                    ON a.parent = p.id
                    WHERE ' . $where .
                '
                    AND cf.block = ?',
                $values
            );

            if ($result->count > 0) {

                // Figure out sortorder
                $orderby = 'a.ctime ' . ($sort == 'latest' ? 'DESC' : 'ASC');

                // If pagination is in use, see if we want to get a page with particular assessment
                if ($limit) {
                    if ($showfeedback == 'last') {
                        // If we have limit (pagination is used) ignore $offset and just get the last page of comments.
                        $result->forceoffset = $offset = (ceil($result->count / $limit) - 1) * $limit;
                    }
                    else if (is_numeric($showfeedback)) {
                        // Ignore $offset and get the page that has the assessment
                        // with id $showfeedback on it.
                        // Fetch everything and figure out which page $showfeedback is in.
                        // This will get ugly if there are 1000s of assessments
                        $ids = get_column_sql(
                            '
                                SELECT a.id
                                FROM {artefact} a JOIN {artefact_checkpoint_feedback} cf ON a.id = cf.feedback
                                LEFT JOIN {artefact} p ON a.parent = p.id
                                WHERE ' . $where . '
                                AND cf.block = ?
                                ORDER BY ' . $orderby,
                            $values
                        );
                        $found = false;
                        foreach ($ids as $k => $v) {
                            if ($v == $showfeedback) {
                                $found = $k;
                                break;
                            }
                        }
                        if ($found !== false) {
                            // Add 1 because array index starts from 0 and therefore key value is offset by 1.
                            $rank = $found + 1;
                            $result->forceoffset = $offset = ((ceil($rank / $limit) - 1) * $limit);
                            $result->showfeedback = $showfeedback;
                        }
                    }
                }

                $assessments = get_records_sql_assoc(
                    '
                    SELECT
                    a.id, a.title, a.author, a.authorname, a.ctime, a.mtime, a.description, a.group, a.path,
                    cf.activity, cf.block, va.view,
                    u.username, u.firstname, u.lastname, u.preferredname, u.email, u.staff, u.admin,
                    u.deleted, u.profileicon, u.urlid, p.id AS parent, p.author AS parentauthor
                    FROM {artefact} a
                    INNER JOIN {artefact_checkpoint_feedback} cf ON a.id = cf.feedback
                    INNER JOIN {view_activity} va ON cf.activity = va.id
                    LEFT JOIN {artefact} p
                    ON a.parent = p.id
                    LEFT JOIN {usr} u ON a.author = u.id
                    WHERE ' . $where .
                    '
                    AND cf.block = ?
                    ORDER BY ' . $orderby,
                    $values,
                    $offset,
                    $limit
                );
                if ($assessments) {
                    $result->data = array_values($assessments);
                }
            }
        }

        $result->position = 'blockinstance';
        self::build_html($result, $versioning, $exporter);
        return $result;
    }

    private static function get_checkpoint_feedback_data_for_versioning($versioning, $viewid) {
        //   global $USER;
        //   foreach ($versioning->blocks as $blockversion) {
        //     //find the assessment block
        //     if ($blockversion->blocktype == 'checkpoint') {
        //       $existing_artefacts = array();
        //       if (isset($blockversion->configdata->existing_artefacts)) {
        //         $existing_artefacts = $blockversion->configdata->existing_artefacts;
        //       }
        //       // populate the version with data to display
        //       $assessmentsversion = array();
        //       foreach ($existing_artefacts as &$assessment) {
        //         // select assessments that are published
        //         // or select assessments where the user is the author, published or not
        //         if ($assessment->author == $USER->get('id') || !$assessment->private) {
        //           $assessment->view = $viewid;
        //           $assessment->block = $blockversion->originalblockid;

        //           if (!$assessment->private && !$assessment->author) {
        //             // the assessment has been imported and is not link to an author
        //             $assessment->authorname = get_string('importedassessment', 'artefact.checkpoint');
        //           }
        //           else {
        //             $user = new User();
        //             $user->find_by_id($assessment->author);
        //             $assessment->username = $user->get('username');
        //             $assessment->firstname = $user->get('firstname');
        //             $assessment->lastname = $user->get('lastname');
        //             $assessment->preferredname = $user->get('preferredname');
        //             $assessment->email = $user->get('email');
        //             $assessment->admin = $user->get('admin');
        //             $assessment->staff = $user->get('staff');
        //             $assessment->deleted = $user->get('deleted');
        //             $assessment->profileicon = $user->get('profileicon');
        //           }
        //           $assessmentsversion[] = $assessment;
        //         }
        //       }
        //       return $assessmentsversion;
        //     }
        //   }
        //   return 0;
    }

    // public static function is_signed_off(View $view) {
    //   if (!$view->get('owner')) {
    //     return false;
    //   }
    //   return (bool)get_field_sql("SELECT signoff FROM {view_signoff_verify} WHERE view = ? LIMIT 1", array($view->get('id')));
    // }

    // /**
    //  * Checks if the verify options is enabled for the page
    //  * @param $view the view object of the view to verify
    //  */
    // public static function is_verify_enabled(View $view) {
    //   $configdata = get_field_sql("SELECT configdata FROM {block_instance} WHERE view = ? AND blocktype = ? LIMIT 1", array($view->get('id'), 'signoff'));
    //   if ($configdata) {
    //     $configdata = unserialize($configdata);
    //     if (isset($configdata['verify'])) {
    //       return $configdata['verify'];
    //     }
    //   }
    //   return false;
    // }

    // public static function is_verified(View $view) {
    //   if (!$view->get('owner')) {
    //     return false;
    //   }
    //   return (bool)get_field_sql("SELECT verified FROM {view_signoff_verify} WHERE view = ? LIMIT 1", array($view->get('id')));
    // }

    public static function count_feedback($viewids = null) {
        if (!empty($viewids)) {
            return get_records_sql_assoc(
                '
                  SELECT cf.activity, COUNT(cf.feedback) AS feedback
                  FROM {artefact_checkpoint_feedback} cf
                  JOIN {view_activity} va ON va.id = cf.activity
                  WHERE va.view IN (' . join(',', array_map('intval', $viewids)) . ')
                  GROUP BY cf.activity',
                array()
            );
        }
    }

    /**
     * Get the last comment for the particular block
     *
     * @param $viewid integer ID of the view
     * @param $blockid integer ID of the block
     * @return array
     */
    public static function last_feedback($viewid, $blockid) {
        if ($newest = get_records_sql_array("
            SELECT a.id, a.ctime
            FROM {artefact} a
            INNER JOIN {artefact_checkpoint_feedback} cf ON a.id = cf.feedback
            INNER JOIN {view_activity} va ON va.id = cf.activity
            WHERE va.view = ? AND cf.block = ?
            ORDER BY a.ctime DESC", array($viewid, $blockid), 0, 1)) {
            return $newest[0];
        }
        return array();
    }

    public static function build_html(&$data, $versioning = null, $exporter = null) {
        global $USER, $THEME, $view;

        $deletedmessage = array();
        $authors = array();
        $lastcomment = self::last_feedback($data->view, $data->block);
        $editableafter = time() - 60 * get_config_plugin('artefact', 'comment', 'commenteditabletime');
        $admintutorids = $view->get('group') ? group_get_member_ids($view->get('group'), array('admin', 'tutor')) : [];
        foreach ($data->data as $key => &$item) {
            $deletedby = get_field('artefact_checkpoint_feedback', 'deletedby', 'feedback', $item->id);
            if (intval($deletedby)) {
                $item->deletedmessage = !empty($deletedby) ? get_string('commentremovedbyuser', 'artefact.checkpoint', get_config('wwwroot') . 'user/view.php?id=' . $deletedby, display_name(get_user_for_display($deletedby), $USER)) : null;
            }
            else {
                $item->deletedmessage = !empty($deletedby) ? get_string('commentremovedby' . $deletedby, 'artefact.comment') : null;
            }
            $candelete = in_array($USER->get('id'), $admintutorids);
            $candelete = $candelete && ($item->id == $lastcomment->id || empty($deletedby));
            $item->ts = strtotime($item->ctime);
            $timelapse = format_timelapse($item->ts);
            $item->date = ($timelapse) ? $timelapse : format_date($item->ts, 'strftimedatetime');
            if ($item->ts < strtotime($item->mtime)) {
                $timelapseupdated = format_timelapse(strtotime($item->mtime));
                $item->updated = ($timelapseupdated) ? $timelapseupdated : format_date(strtotime($item->mtime), 'strftimedatetime');
            }
            $item->isauthor = $item->author && $item->author == $USER->get('id');

            if (isset($data->showfeedback) && $data->showfeedback == $item->id) {
                $item->highlight = 1;
            }
            $is_export_preview = param_integer('export', 0);

            // Feedback authors can edit recent feedback if they're within editable timeframe.
            if (
                $data->canedit &&
                ($item->isauthor && !$is_export_preview && $item->ts > $editableafter && $item->id == $lastcomment->id
                )
            ) {
                $item->canedit = 1;
            }
            else {
                $item->canedit = 0;
            }

            if (!$versioning) {
                $submittedcheck = get_record_sql(
                    'SELECT v.* FROM {view} v WHERE v.id = ?',
                    array($data->view),
                    ERROR_MULTIPLE
                );

                if ($candelete && !$is_export_preview
                    && $submittedcheck->submittedstatus == View::UNSUBMITTED
                ) {
                    $item->deleteform = pieform(
                        self::delete_checkpoint_feedback_form($item->id, $item->view, $item->block)
                    );
                }
                if ($item->canedit && $submittedcheck->submittedstatus == View::UNSUBMITTED) {
                    $smarty = smarty_core();
                    $smarty->assign('id', $item->id);
                    $smarty->assign('block', $item->block);
                    $smarty->assign('title', $item->title);
                    $item->editlink = $smarty->fetch('artefact:checkpoint:editlink.tpl');
                }
            }
            if ($exporter) {
                // Don't export the author of the assessment
                $item->author = null;
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
        }

        $extradata = array('block' => $data->block);
        $data->jsonscript = 'artefact/checkpoint/checkpoint.json.php';
        $data->baseurl = get_config('wwwroot') . 'view/view.php?id=' . $data->view;

        $smarty = smarty_core();
        $smarty->assign('data', $data->data);
        $smarty->assign('canedit', $data->canedit);
        $smarty->assign('position', $data->position);
        $smarty->assign('viewid', $data->view);
        $smarty->assign('baseurl', $data->baseurl);

        $data->tablerows = $smarty->fetch('artefact:checkpoint:feedbacklist.tpl');

        $pagination = build_pagination(array(
            'id' => 'checkpoint_pagination_' . $data->block,
            'class' => 'center',
            'url' => $data->baseurl,
            'jsonscript' => $data->jsonscript,
            'datatable' => 'checkpointfeedbacktable' . $data->block,
            'count' => $data->count,
            'limit' => $data->limit,
            'offset' => $data->offset,
            'forceoffset' => isset($data->forceoffset) ? $data->forceoffset : null,
            'resultcounttext' => get_string('nfeedback', 'artefact.checkpoint', $data->count),
            'extradata' => $extradata,
        ));
        $data->pagination = $pagination['html'];
        $data->pagination_js = $pagination['javascript'];
        $data->html = $data->tablerows . "\n" . $data->pagination . "\n";
        if ($data->pagination_js) {
            $data->html .= '<script type="application/javascript">' . "\n";
            $data->html .= ' jQuery(function () {' . "\n";
            $data->html .= '   checkpointpaginator' . $data->block . ' = ' . $data->pagination_js . "\n";
            $data->html .= ' });' . "\n";
            $data->html .= '</script>' . "\n";
        }
    }

    public function render_self($options) {
        return clean_html($this->get('description'));
    }

    public static function get_checkpoint_achievement_form($block_id = 0) {
        global $USER;

        $checkpoint_block = new BlockInstance($block_id);
        $block_view = new View($checkpoint_block->get('view'));
        $can_select_achievement = View::check_can_edit_activity_page_info($block_view->get('group'), true);
        $achievement_levels = [];
        $options = [];

        if ($checkpoint_block) {
            $activity = ArtefactTypeCheckpointfeedback::get_checkpoint_activity_id();
            $achievement_levels = get_records_array('view_activity_achievement_levels', 'activity', $activity, 'type');
        }

        foreach ($achievement_levels as $level) {
            $options[$level->type] =  get_string('level_cap', 'artefact.checkpoint') . ' ' . $level->type;
        }

        $form = array(
            'name'            => 'achievement_form_block_' . $block_id,
            'method'          => 'post',
            'plugintype'      => 'artefact',
            'pluginname'      => 'checkpoint',
            'jsform'          => true,
            'autofocus'       => false,
            'elements'        => array(),
            'jssuccesscallback' => 'selectLevelSuccess',
            'jserrorcallback'   => 'selectLevelError',
        );

        if ($can_select_achievement) {
            $form['elements']['achievement_levels'] = array(
                'type' => 'select',
                'title' => get_string('achievementlevel', 'artefact.checkpoint'),
                'collapseifoneoption' => false,
                'options' => $options,
                'defaultvalue' => key($options),
            );

            $form['elements']['submit'] = array(
                'type' => 'submit',
                'name' => 'level_submit',
                'value' => get_string('save'),
                'class' => 'btn-primary',
            );
        }
        else {
            $form['elements']['achievement_levels'] = array(
                'type' => 'html',
                'title' => get_string('achievementlevel', 'artefact.checkpoint'),
                'value' =>
                '
                <span class="icon-stack" style="vertical-align: centre;">
                    <i class="icon-solid icon-minus icon-stack-1x"></i>
                    &nbsp;&nbsp;&nbsp;
                </span>
                '
            );
        }

        $form['elements']['block'] = array(
            'type' => 'hidden',
            'value' => $block_id,
        );

        return $form;
    }

    public static function add_checkpoint_feedback_form($blockid = 0, $id = 0, $editing = false) {
        global $USER;
        $form = array(
            'name'            => 'add_checkpoint_feedback_form_' . $blockid,
            'method'          => 'post',
            'plugintype'      => 'artefact',
            'pluginname'      => 'checkpoint',
            'jsform'          => true,
            'autofocus'       => false,
            'elements'        => array(),
            'successcallback'   => 'add_checkpoint_feedback_form_submit',
            'validatecallback'  => 'add_checkpoint_feedback_form_validate',
            'jssuccesscallback' => 'addCheckpointFeedbackSuccess',
            'jserrorcallback'   => 'addCheckpointFeedbackError',
        );
        $form['elements']['message'] = array(
            'type'  => 'wysiwyg',
            'title' => get_string('Feedback', 'artefact.checkpoint'),
            'class' => 'hide-label',
            'rows'  => 5,
            'cols'  => 80,
            'rules' => array('maxlength' => 1000000),
        );
        $form['elements']['block'] = array(
            'type' => 'hidden',
            'value' => $blockid,
        );
        $form['elements']['editing'] = array(
            'type' => 'hidden',
            'value' => $editing,
        );
        $form['elements']['feedback'] = array(
            'type' => 'text',
            'defaultvalue' => $id,
            'class' => 'hidden',
        );
        $form['elements']['submit'] = array(
            'type' => 'multisubmit',
            'name' => 'checkpoint_comment',
            'options' => array(
                'submit' => 'save',
                'cancel' => 'cancel'
            ),
            'primarychoice' => 'save',
            'classes' => array(
                'submit' => 'btn-secondary',
                'cancel' => 'btn-secondary submitcancel'
            ),
            'value' => array(
                'submit' => get_string('save', 'artefact.checkpoint'),
                'cancel' => get_string('cancel')
            )
        );
        return $form;
    }

    public static function delete_checkpoint_feedback_form($id, $viewid, $blockid, $editing = false) {
        global $THEME;
        return array(
            'name'     => 'delete_checkpoint_feedback_' . $id,
            'method' => 'post',
            'autofocus'         => false,
            'renderer'          => 'oneline',
            'plugintype'        => 'artefact',
            'pluginname'        => 'checkpoint',
            'class'             => 'form-as-button float-start assessbtn last',
            'jsform'            => true,
            'successcallback'   => 'delete_checkpoint_feedback_submit',
            'jssuccesscallback' => 'modifyCheckpointFeedbackSuccess',
            'elements' => array(
                'feedback' => array('type' => 'hidden', 'value' => $id),
                'view' => array('type' => 'hidden', 'value' => $viewid),
                'block' => array('type' => 'hidden', 'value' => $blockid),
                'editing' => array('type' => 'hidden', 'value' => $editing),
                'submit'  => array(
                    'type'  => 'button',
                    'usebuttontag' => true,
                    'class' => 'btn-secondary btn-sm',
                    'value' => '<span class="icon icon-trash-alt text-danger" role="presentation" aria-hidden="true"></span> <span class="visually-hidden">' . get_string('delete') . '</span>',
                    'confirm' => get_string('reallydeletethisfeedback', 'artefact.checkpoint'),
                    'name'  => 'delete_checkpoint_feedback_submit',
                ),
            ),
        );
    }

    public function exportable() {
        return true;
    }

    public static function has_config() {
        return false;
    }
}

function delete_checkpoint_feedback_submit(Pieform $form, $values) {
    global $SESSION, $USER;

    $checkpoint_feedback = new ArtefactTypeCheckpointfeedback((int) $values['feedback']);
    $view = $checkpoint_feedback->get_view();
    $viewid = $values['view'];
    $blockid = $checkpoint_feedback->get('block');
    $url = !empty($values['editing']) ? 'view/blocks.php?id=' . $viewid : $view->get_url(false);
    $goto = get_config('wwwroot') . $url;
    // If this page is being marked, make assessments un-deletable until released
    // unless it is the last assessment still with in the editable timeframe
    $editableafter = time() - 60 * get_config_plugin('artefact', 'comment', 'commenteditabletime');
    $lastcomment = $checkpoint_feedback::last_feedback($viewid, $blockid);
    $is_last_comment = $lastcomment && $checkpoint_feedback->get('id') == $lastcomment->id;

    $view = new View($values['view']);
    $admintutorids = $view->get('group') ? group_get_member_ids($view->get('group'), array('admin', 'tutor')) : [];
    if (($is_last_comment && $checkpoint_feedback->get('mtime') > $editableafter)
        || in_array($USER->get('id'), $admintutorids)
    ) {
        $candelete = 1;
    }
    else {
        $candelete = 0;
    }

    if (
        // $view->get('submittedstatus') == View::UNSUBMITTED  ||
        $candelete
    ) {
        db_begin();
        $author = $checkpoint_feedback->get('author');
        $owner = $view->get('owner');
        $sendto = array();
        if ($owner != $USER->get('id')) {
            $sendto[] = $owner;
        }
        if ($author != $USER->get('id')) {
            $sendto[] = $author;
        }
        if (!empty($sendto)) {
            // Notify author
            $title = $view->get('title');
            $title = hsc($title);
            $data = (object) array(
                'subject'   => false,
                'message'   => false,
                'strings'   => (object) array(
                    'subject' => (object) array(
                        'key'     => 'deletednotificationsubject',
                        'section' => 'artefact.checkpoint',
                        'args'    => array($title),
                    ),
                    'message' => (object) array(
                        'key'     => 'deletedauthornotification1',
                        'section' => 'artefact.checkpoint',
                        'args'    => array(
                            $title, html2text($checkpoint_feedback->get('description'))
                        ),
                    ),
                    'urltext' => (object) array(
                        'key'     => 'view',
                    ),
                ),
                'users'     => $sendto,
                'url'       => $url,
            );
            activity_occurred('maharamessage', $data);
        }

        if ($is_last_comment) {
            $checkpoint_feedback->delete();
        }
        else {
            $deletedby = '';
            if ($USER->get('id') == $checkpoint_feedback->get('author')) {
                $deletedby = 'author';
            }
            else if ($USER->can_edit_view($view)) {
                $deletedby = $USER->get('id');
            }
            else if ($USER->get('admin')) {
                $deletedby = 'admin';
            }
            $checkpoint_feedback->set('deletedby', $deletedby);
            $checkpoint_feedback->commit();
        }
        db_commit();

        $form->reply(PIEFORM_OK, array(
            'message' => get_string('feedbackremoved', 'artefact.checkpoint'),
            'goto' => $goto,
        ));
    }
    else {
        $form->reply(PIEFORM_ERR, get_string('assessmentremovedfailed', 'artefact.checkpoint'));
    }
}

function add_checkpoint_feedback_form_validate(Pieform $form, $values) {
    global $USER, $view;
    if (empty($values['attachments']) && empty($values['message'])) {
        $form->set_error('message', get_string('messageempty', 'artefact.checkpoint'));
    }
}

function add_checkpoint_feedback_form_submit(Pieform $form, $values) {
    global $view, $USER;
    require_once('embeddedimage.php');

    if ($values['feedback'] == 0) {
        // new one
        $data = (object) array(
            'title'       => get_string('Feedback', 'artefact.checkpoint'),
            'description' => $values['message'],
        );
        $data->view        = $view->get('id');
        $data->owner       = $view->get('owner');
        $data->group       = $view->get('group');
        $data->institution = $view->get('institution');

        $data->author = $USER->get('id');
        $data->block = $values['block'];
        $checkpoint_feedback = new ArtefactTypeCheckpointfeedback(0, $data);
    }
    else {
        $checkpoint_feedback = new ArtefactTypeCheckpointfeedback($values['feedback']);
        $checkpoint_feedback->set('description', $values['message']);
    }

    db_begin();

    $checkpoint_feedback->commit();

    $newdescription = EmbeddedImage::prepare_embedded_images($values['message'], 'checkpoint_feedback', $checkpoint_feedback->get('id'));

    if ($newdescription !== $values['message']) {
        $updated = new stdClass();
        $updated->id = $checkpoint_feedback->get('id');
        $updated->description = $newdescription;
        update_record('artefact', $updated, 'id');
    }

    $url = $checkpoint_feedback->get_view_url($view->get('id'), true, false, $values['editing']);

    $goto = get_config('wwwroot') . $url;

    // Notify group
    $data = (object) array(
        'checkpointid' => $checkpoint_feedback->get('id'),
        'viewid'    => $view->get('id'),
    );
    activity_occurred('feedback', $data, 'artefact', 'checkpoint');

    db_commit();

    $options = ArtefactTypeCheckpointfeedback::get_checkpoint_feedback_options();
    $options->showfeedback = $checkpoint_feedback->get('id');
    $options->view = $view;
    $options->block = $values['block'];
    $newlist = ArtefactTypeCheckpointfeedback::get_checkpoint_feedback($options);

    $message = get_string('feedbacksubmitted', 'artefact.checkpoint');

    $form->reply(PIEFORM_OK, array(
        'message' => $message,
        'goto' => $goto,
        'data' => $newlist,
    ));
}

function add_checkpoint_feedback_form_cancel_submit(Pieform $form) {
    global $view;
    $form->reply(PIEFORM_CANCEL, array(
        'location' => $view->get_url(true),
    ));
}

class ActivityTypeArtefactCheckpointfeedback extends ActivityTypePlugin {

    protected $viewid;
    protected $checkpointid;

    /**
     * @param array $data Parameters:
     *                    - viewid (int)
     *                    - checkpointid (int)
     */
    public function __construct($data, $cron = false) {
        parent::__construct($data, $cron);

        $checkpoint_feedback = new ArtefactTypeCheckpointfeedback($this->checkpointid);

        $this->overridemessagecontents = true;

        $onview = $checkpoint_feedback->get('view');
        if (!$viewrecord = get_record('view', 'id', $onview)) {
            throw new ViewNotFoundException(get_string('viewnotfound', 'error', $onview));
        }
        $userid = $viewrecord->owner;
        $groupid = $viewrecord->group;
        if (empty($this->url)) {
            $this->url = 'view/view.php?id=' . $onview;
        }

        // Now fetch the users that will need to get notified about this event
        $this->users = array();
        if (!empty($userid)) {
            $this->users = activity_get_users($this->get_id(), array($userid));
        }
        else if (!empty($groupid)) {
            require_once(get_config('docroot') . 'lib/group.php');
            $this->users = get_records_sql_assoc("SELECT u.id, u.username, u.firstname, u.lastname, u.preferredname, u.email
                                                  from {usr} u, {group_member} m, {group} g
                                                  WHERE g.id = m.group AND m.member = u.id AND m.group = ?
                                                  AND (g.feedbacknotify = " . GROUP_ROLES_ALL . "
                                                   OR (g.feedbacknotify = " . GROUP_ROLES_NONMEMBER . " AND (m.role = 'tutor' OR m.role = 'admin'))
                                                   OR (g.feedbacknotify = " . GROUP_ROLES_ADMIN . " AND m.role = 'admin')
                                                  )", array($groupid));
        }

        if (empty($this->users)) {
            // no one to notify - possible if group 'feedbacknotify' is set to 0
            return;
        }

        $title = $viewrecord->title;
        $this->urltext = $title;
        $body = $checkpoint_feedback->get('description');
        $posttime = strftime(get_string('strftimedaydatetime'), $checkpoint_feedback->get('ctime'));

        // Internal
        $this->message = strip_tags(str_shorten_html($body, 200, true));
        // Seeing as things like emaildigest base the message on $this->message
        // we need to set the language for the $removedbyline here based on first user.
        $user = reset($this->users);
        $lang = (empty($user->lang) || $user->lang == 'default') ? get_config('lang') : $user->lang;

        $this->strings = (object) array(
            'subject' => (object) array(
                'key'     => 'newfeedbacknotificationsubject',
                'section' => 'artefact.checkpoint',
                'args'    => array($title),
            ),
        );

        $this->url .= '&showfeedback=' . $checkpoint_feedback->get('id');

        // Email
        $author = $checkpoint_feedback->get('author');
        if ($author) {
            $this->fromuser = $author;
            // We don't need to send an email to the inbox of the author of the feedback as we send one to their outbox
            if (isset($this->users[$author])) {
                unset($this->users[$author]);
            }
        }
        foreach ($this->users as $key => $user) {
            $authorname = display_name($author, $user);
            if (empty($user->lang) || $user->lang == 'default') {
                // check to see if we need to show institution language
                $instlang = get_user_institution_language($user->id);
                $lang = (empty($instlang) || $instlang == 'default') ? get_config('lang') : $instlang;
            }
            else {
                $lang = $user->lang;
            }
            $this->users[$key]->htmlmessage = get_string_from_language(
                $lang,
                'feedbacknotificationhtml',
                'artefact.checkpoint',
                hsc($authorname),
                hsc($title),
                $posttime,
                clean_html($body),
                get_config('wwwroot') . $this->url
            );
            $this->users[$key]->emailmessage = get_string_from_language(
                $lang,
                'feedbacknotificationtext1',
                'artefact.checkpoint',
                $authorname,
                $title,
                $posttime,
                trim(html2text(htmlspecialchars($body))),
                get_config('wwwroot') . $this->url
            );
        }
    }

    public function get_plugintype() {
        return 'artefact';
    }

    public function get_pluginname() {
        return 'checkpoint';
    }

    public function get_required_parameters() {
        return array('checkpointid', 'viewid');
    }
}

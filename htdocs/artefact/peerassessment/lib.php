<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-peer-assessment
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

require_once('activity.php');
require_once('license.php');

class PluginArtefactPeerassessment extends PluginArtefact {

    public static function is_active() {
        return get_field('artefact_installed', 'active', 'name', 'peerassessment');
    }

    public static function get_artefact_types() {
        return array(
            'peerassessment',
        );
    }

    public static function get_block_types() {
        return array();
    }

    public static function get_plugin_name() {
        return 'peerassessment';
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
                'name' => 'assessmentfeedback',
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
            // If elasticsearch is installed, update the artefacttypesmap field to include peerassessment
            $sql = "SELECT value FROM {search_config} WHERE plugin='elasticsearch' AND field='artefacttypesmap'";
            if ($result = get_field_sql($sql, array())) {
                $elasticsearchartefacttypesmap = explode("\n", $result);
                // add peerassessment field.
                $elasticsearchartefacttypesmap[] = "peerassessment|Peerassessment|Text";
                // Now save the data including the new peer assessment field.
                set_config_plugin('search', 'elasticsearch', 'artefacttypesmap', implode("\n", $elasticsearchartefacttypesmap));
            }

            // Now install the blocktype peer assessment only if Mahara was previously installed.
            // Otherwise, the Mahara installer will install everything.
            if (get_config('installed')) {
                if ($upgrade = check_upgrades('blocktype.peerassessment/peerassessment')) {
                    upgrade_plugin($upgrade);
                }
            }
        }
    }

    public static function view_export_extra_artefacts($viewids) {
        $artefacts = array();
        if (!$artefacts = get_column_sql("
            SELECT assessment
            FROM {artefact_peer_assessment}
            WHERE view IN (" . join(',', array_map('intval', $viewids)) . ')', array())) {
            return array();
        }
        if ($attachments = get_column_sql('
            SELECT attachment
            FROM {artefact_attachment}
            WHERE artefact IN (' . join(',', $artefacts). ')')) {
            $artefacts = array_merge($artefacts, $attachments);
        }
        if ($embeds = get_column_sql("
            SELECT afe.fileid
            FROM {artefact_file_embedded} afe
            JOIN {artefact_peer_assessment} apa ON apa.assessment = afe.resourceid
            WHERE afe.resourcetype IN (?)
            AND apa.view IN (" . join(',', array_map('intval', $viewids)) . ")
            UNION
            SELECT afe.fileid
            FROM {artefact_file_embedded} afe
            JOIN {artefact_peer_assessment} apa ON apa.block = afe.resourceid
            WHERE afe.resourcetype in(?)
            AND apa.view IN (" . join(',', array_map('intval', $viewids)) . ")"
            , array('assessment', 'peerinstruction'))) {
            $artefacts = array_merge($artefacts, $embeds);
        }

        return $artefacts;
    }

    public static function get_cron() {
        return array(
            (object)array(
                'callfunction' => 'clean_peerassessment_notifications',
                'minute'       => '55',
                'hour'         => '22',
            ),
        );
    }

    public static function clean_peerassessment_notifications() {
        safe_require('notification', 'internal');
        PluginNotificationInternal::clean_notifications(array('peerassessment'));
    }

    public static function progressbar_link($artefacttype) {
        switch ($artefacttype) {
            case 'peerassessment':
                return 'view/index.php';
                break;
            case 'verify':
                return 'view/index.php';
                break;
        }
    }

    public static function progressbar_additional_items() {
        return array(
            (object)array(
                'name' => 'peerassessment',
                'title' => get_string('placeassessment', 'artefact.peerassessment'),
                'plugin' => 'peerassessment',
                'active' => get_field('blocktype_installed', 'active', 'name', 'peerassessment', 'artefactplugin', 'peerassessment'),
                'iscountable' => true,
                'is_metaartefact' => true,
            ),
            (object)array(
                'name' => 'verify',
                'title' => get_string('verifyassessment', 'artefact.peerassessment'),
                'plugin' => 'peerassessment',
                'active' => get_field('blocktype_installed', 'active', 'name', 'signoff', 'artefactplugin', 'peerassessment'),
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
            case 'peerassessment':
                $sql = "SELECT COUNT(*) AS completed
                        FROM {artefact} a
                        JOIN {artefact_peer_assessment} ap ON ap.assessment = a.id
                        WHERE a.artefacttype = 'peerassessment'
                        AND a.owner <> ?
                        AND ap.usr = ?";
                $meta->completed = count_records_sql($sql, array($USER->get('id'), $USER->get('id')));
                break;
            case 'verify':
                $sql = "SELECT COUNT(*) AS completed
                        FROM {view} v
                        JOIN {view_signoff_verify} vsv ON vsv.view = v.id
                        WHERE v.owner <> ?
                        AND vsv.verifier = ?";
                $meta->completed = count_records_sql($sql, array($USER->get('id'), $USER->get('id')));
                break;
            default:
                return false;
        }
        return $meta;
    }
}

class ArtefactTypePeerassessment extends ArtefactType {

    protected $assessment;    // artefact id of the peer assessment artefact.
    protected $block;         // block id of the block this peer assessment is linked to.
    protected $usr;           // usr id of the user who added this peer assessment.
    protected $view;          // view id of the view this peer assessment is linked to.
    protected $view_obj;      // the view object based on the $view id.
    protected $private;       // Whether this assessment has been published by the user.
                              // 0 = can be seen by author, page owner, manager (published)
                              // 1 = can only be seen by author (draft)

    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);

        if ($this->id && ($extra = get_record('artefact_peer_assessment', 'assessment', $this->id))) {
            foreach($extra as $name => $value) {
                if (property_exists($this, $name)) {
                    $this->{$name} = $value;
                }
            }
        }
    }

    public static function is_singular() {
        return false;
    }

    public static function get_icon($options=null) {
        global $THEME;
        return false;
    }

    public static function get_links($id) {
        $v = $this->get_view();
        return array(
            '_default' => $v->get_url(),
        );
    }

    public function get_view_url($viewid, $showcomment=true, $full=true) {
        $url = 'view/view.php?id=' . $viewid;
        if ($showcomment) {
            $url .= '&showcomment=' . $this->get('id');
        }
        if ($full) {
            $url = get_config('wwwroot') . $url;
        }
        return $url;
    }

    /**
     * @return View the view object this peer assessment block is in
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
            'assessment'    => $this->get('id'),
            'block'         => $this->get('block'),
            'usr'           => $this->get('usr'),
            'view'          => $this->get('view'),
            'private'       => ($this->get('private') ? 1 : 0),
        );

        if ($new) {
            insert_record('artefact_peer_assessment', $data);
        }
        else {
            update_record('artefact_peer_assessment', $data, 'assessment');
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

        // Remove any embedded images for this peer assessment.
        require_once('embeddedimage.php');
        EmbeddedImage::remove_embedded_images('assessment', $this->id);
        delete_records('artefact_peer_assessment', 'assessment', $this->id);
        parent::delete();
        db_commit();
    }

    public static function bulk_delete($artefactids, $log=false) {
        if (empty($artefactids)) {
            return;
        }

        $idstr = join(',', array_map('intval', $artefactids));

        db_begin();
        delete_records_select('artefact_peer_assessment', 'assessment IN (' . $idstr . ')');
        delete_records_select('artefact_file_embedded', 'resourcetype = ? AND resourceid IN (' . $idstr . ')', array('assessment'));
        parent::bulk_delete($artefactids);
        db_commit();
    }

    public static function delete_view_assessments($viewid) {
        $ids = get_column('artefact_peer_assessment', 'assessment', 'view', $viewid);
        self::bulk_delete($ids);
    }

    public function can_have_attachments() {
        return true;
    }

    public static function deleted_types() {
        return array('author', 'owner', 'admin');
    }

    /**
     * Generates default data object required for displaying peer assessments on the page.
     * The is called before populating with specific data to send to get_assessments() as
     * an easy way to add variables to get passed to get_assessments.
     *
     * int $limit              The number of comments to display (set to
     *                         0 for disabling pagination and showing all comments)
     * int $offset             The offset of comments used for pagination
     * int|string $showcomment Optionally show page with particular comment
     *                         on it or the last page. $offset will be ignored.
     *                         Specify either comment_id or 'last' respectively.
     *                         Set to null to use $offset for pagination.
     * object $view            The view object
     * int $block              Optional The block id that the assessment is linked to.
     * bool   $export          Determines if comments are fetched for html export purposes
     * bool   $onview          Optional - is viewing assessment on view page so don't show edit buttons
     * string $sort            Optional - the sort order of the comments. Valid options are 'earliest' and 'latest'.
     * @return object $options Default peer assessments data object
     */
    public static function get_assessment_options() {
        $options = new stdClass();
        $options->limit = 10;
        $options->offset = 0;
        $options->showcomment = null;
        $options->view = null;
        $options->block = null;
        $options->export = false;
        $options->onview = false;
        $sortorder = get_user_institution_comment_sort_order();
        $options->sort = (!empty($sortorder)) ? $sortorder : 'earliest';
        return $options;
    }

    /**
     * Generates the data object required for displaying assessments on the page.
     *
     * @param   object  $options  Object of assessment options
     *                            - defaults can be retrieved from get_assessment_options()
     * @return  object $result    Assessment data object
     */
    public static function get_assessments($options, $versioning=null) {
        global $USER;
        $allowedoptions = self::get_assessment_options();
        // set the object's key/val pairs as variables

        foreach ($options as $key => $option) {
            if (array_key_exists($key, $allowedoptions));
            $$key = $option;
        }
        $userid = $USER->get('id');
        $viewid = $view->get('id');
        $canedit = ($USER->can_peer_assess($view) && !self::is_signed_off($view));
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
            $result->data = self::get_assessment_data_for_versioning($versioning, $viewid);
            $result->count = sizeof($result->data);
        }
        else {
            $where = 'pa.view = ? ';

            // select assessments that are published
            // or select assessments where the user is the author, published or not
            $where.= 'AND ( (pa.private = 0) ';
            $where.= '    OR (a.author = ?))';

            $values = array($viewid, $userid, $block);

            $result->count = count_records_sql('
                SELECT COUNT(*)
                FROM
                    {artefact} a
                    JOIN {artefact_peer_assessment} pa
                        ON a.id = pa.assessment
                    LEFT JOIN {artefact} p
                        ON a.parent = p.id
                WHERE ' . $where . '
                AND pa.block = ?', $values);

            if ($result->count > 0) {

                // Figure out sortorder
                $orderby = 'a.ctime ' . ($sort == 'latest' ? 'DESC' : 'ASC');

                // If pagination is in use, see if we want to get a page with particular assessment
                if ($limit) {
                    if ($showcomment == 'last') {
                        // If we have limit (pagination is used) ignore $offset and just get the last page of comments.
                        $result->forceoffset = $offset = (ceil($result->count / $limit) - 1) * $limit;
                    }
                    else if (is_numeric($showcomment)) {
                        // Ignore $offset and get the page that has the assessment
                        // with id $showcomment on it.
                        // Fetch everything and figure out which page $showcomment is in.
                        // This will get ugly if there are 1000s of assessments
                        $ids = get_column_sql('
                                SELECT a.id
                                FROM {artefact} a JOIN {artefact_peer_assessment} pa ON a.id = pa.assessment
                                LEFT JOIN {artefact} p ON a.parent = p.id
                                WHERE ' . $where . '
                                AND pa.block = ?
                                ORDER BY ' . $orderby,
                                $values
                        );
                        $found = false;
                        foreach ($ids as $k => $v) {
                            if ($v == $showcomment) {
                                $found = $k;
                                break;
                            }
                        }
                        if ($found !== false) {
                            // Add 1 because array index starts from 0 and therefore key value is offset by 1.
                            $rank = $found + 1;
                            $result->forceoffset = $offset = ((ceil($rank / $limit) - 1) * $limit);
                            $result->showcomment = $showcomment;
                        }
                    }
                }

                $assessments = get_records_sql_assoc('
                    SELECT
                        a.id, a.title, a.author, a.authorname, a.ctime, a.mtime, a.description, a.group, a.path,
                        pa.private, pa.view, pa.block,
                        u.username, u.firstname, u.lastname, u.preferredname, u.email, u.staff, u.admin,
                        u.deleted, u.profileicon, u.urlid, p.id AS parent, p.author AS parentauthor
                    FROM {artefact} a
                        INNER JOIN {artefact_peer_assessment} pa ON a.id = pa.assessment
                        LEFT JOIN {artefact} p
                            ON a.parent = p.id
                        LEFT JOIN {usr} u ON a.author = u.id
                    WHERE ' . $where . '
                    AND pa.block = ?
                    ORDER BY ' . $orderby, $values, $offset, $limit);
                $result->data = array_values($assessments);
            }
        }

        $result->position = 'blockinstance';
        self::build_html($result, $versioning);
        return $result;
    }

    private static function get_assessment_data_for_versioning($versioning, $viewid) {
          global $USER;
          foreach ($versioning->blocks as $blockversion) {
              //find the assessment block
              if ($blockversion->blocktype == 'peerassessment') {
                  $existing_artefacts= array();
                  if (isset($blockversion->configdata->existing_artefacts)) {
                      $existing_artefacts = $blockversion->configdata->existing_artefacts;
                  }
                  // populate the version with data to display
                  $assessmentsversion = array();
                  foreach ($existing_artefacts as &$assessment) {
                      // select assessments that are published
                      // or select assessments where the user is the author, published or not
                      if ($assessment->author == $USER->get('id') || !$assessment->private ) {
                          $assessment->view = $viewid;
                          $assessment->block = $blockversion->originalblockid;

                          $user = new User();
                          $user->find_by_id($assessment->author);
                          $assessment->username = $user->get('username');
                          $assessment->firstname = $user->get('firstname');
                          $assessment->lastname = $user->get('lastname');
                          $assessment->preferredname = $user->get('preferredname');
                          $assessment->email = $user->get('email');
                          $assessment->admin = $user->get('admin');
                          $assessment->staff = $user->get('staff');
                          $assessment->deleted = $user->get('deleted');
                          $assessment->profileicon = $user->get('profileicon');

                          $assessmentsversion[] = $assessment;
                      }
                  }
                  return $assessmentsversion;
              }
          }
          return 0;
    }

    public static function is_signable(View $view) {
        global $USER;

        $signable = false;
        if ($view->get('owner')) {
            $signable = ($view->get('owner') == $USER->get('id')) ? true : false;
        }
        return $signable;
    }

    public static function is_signed_off(View $view) {
        if (!$view->get('owner')) {
            return false;
        }
        return (bool)get_field_sql("SELECT signoff FROM {view_signoff_verify} WHERE view = ? LIMIT 1", array($view->get('id')));
    }

    public static function is_verifiable(View $view) {
        global $USER;

        if (!$view->get('owner')) {
            return false;
        }
        $verifiable = get_field_sql("SELECT va.usr FROM {view_access} va
                                     JOIN {usr_roles} ur ON ur.role = va.role
                                     WHERE ur.see_block_content = ?
                                     AND va.view = ? AND va.usr = ?
                                     LIMIT 1", array(1, $view->get('id'), $USER->get('id')));
        return (bool)$verifiable;
    }

    public static function is_verified(View $view) {
        if (!$view->get('owner')) {
            return false;
        }
        return (bool)get_field_sql("SELECT verified FROM {view_signoff_verify} WHERE view = ? LIMIT 1", array($view->get('id')));
    }

    public static function count_assessments($viewids=null) {
        if (!empty($viewids)) {
            return get_records_sql_assoc('
                SELECT pa.view, COUNT(pa.assessment) AS assessments
                FROM {artefact_peer_assessment} pa
                WHERE pa.view IN (' . join(',', array_map('intval', $viewids)) . ')
                GROUP BY pa.view',
                array()
            );
        }
    }

    public static function last_public_assessment($view=null) {
        $newest = get_records_sql_array('
            SELECT a.id, a.ctime
            FROM {artefact} a
            INNER JOIN {artefact_peer_assessment} pa ON a.id = pa.assessment
            WHERE pa.private = 0 AND pa.view = ?
            ORDER BY a.ctime DESC', array($view), 0, 1
        );
        return $newest[0];
    }

    public static function build_html(&$data, $versioning=null) {
        global $USER, $THEME;

        $deletedmessage = array();
        $authors = array();
        $lastcomment = self::last_public_assessment($data->view);
        $editableafter = time() - 60 * get_config_plugin('artefact', 'comment', 'commenteditabletime');
        $signedoff = null;
        foreach ($data->data as &$item) {
            $candelete = ($data->canedit && ($item->author == $USER->get('id'))) || $USER->get('admin');
            if ($signedoff === null) {
                $view = new View($item->view);
                $signedoff = self::is_signed_off($view);
            }
            $item->ts = strtotime($item->ctime);
            $timelapse = format_timelapse($item->ts);
            $item->date = ($timelapse) ? $timelapse : format_date($item->ts, 'strftimedatetime');
            if ($item->ts < strtotime($item->mtime)) {
                $timelapseupdated = format_timelapse(strtotime($item->mtime));
                $item->updated = ($timelapseupdated) ? $timelapseupdated : format_date(strtotime($item->mtime), 'strftimedatetime');
            }
            $item->isauthor = $item->author && $item->author == $USER->get('id');

            if ($item->private) {
                $item->pubmessage = get_string('thisassessmentisprivate', 'artefact.peerassessment');
            }

            if (isset($data->showcomment) && $data->showcomment == $item->id) {
                $item->highlight = 1;
            }
            $is_export_preview = param_integer('export', 0);

            // Peer assessment authors can edit recent assessments if they're private or if within editable timeframe.
            if ($data->canedit &&
                ($item->isauthor && !$is_export_preview &&
                 ($item->private || $item->ts > $editableafter)
                )
              ) {
                $item->canedit = 1;
            }
            else {
                $item->canedit = 0;
            }

            if (!$versioning) {
                $submittedcheck = get_record_sql('SELECT v.* FROM {view} v WHERE v.id = ?', array($data->view), ERROR_MULTIPLE);
                if (($candelete || ($item->isauthor && !$signedoff && !$is_export_preview)) && $submittedcheck->submittedstatus == View::UNSUBMITTED) {
                    $item->deleteform = pieform(self::delete_assessment_form($item->id, $item->view, $item->block));
                }
                if ($item->canedit && $submittedcheck->submittedstatus == View::UNSUBMITTED) {
                    $smarty = smarty_core();
                    $smarty->assign('id', $item->id);
                    $smarty->assign('block', $item->block);
                    $smarty->assign('title', $item->title);
                    $item->editlink = $smarty->fetch('artefact:peerassessment:editlink.tpl');
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
        }

        $extradata = array('block' => $data->block);
        $data->jsonscript = 'artefact/peerassessment/peerassessment.json.php';
        $data->baseurl = get_config('wwwroot') . 'view/view.php?id=' . $data->view;

        $smarty = smarty_core();
        $smarty->assign('data', $data->data);
        $smarty->assign('canedit', $data->canedit);
        $smarty->assign('position', $data->position);
        $smarty->assign('viewid', $data->view);
        $smarty->assign('baseurl', $data->baseurl);

        $data->tablerows = $smarty->fetch('artefact:peerassessment:assessmentlist.tpl');

        $pagination = build_pagination(array(
            'id' => 'peerassessment_pagination_' . $data->block,
            'class' => 'center',
            'url' => $data->baseurl,
            'jsonscript' => $data->jsonscript,
            'datatable' => 'assessmentfeedbacktable' . $data->block,
            'count' => $data->count,
            'limit' => $data->limit,
            'offset' => $data->offset,
            'forceoffset' => isset($data->forceoffset) ? $data->forceoffset : null,
            'resultcounttextsingular' => get_string('assessment', 'artefact.peerassessment'),
            'resultcounttextplural' => get_string('assessments', 'artefact.peerassessment'),
            'extradata' => $extradata,
        ));
        $data->pagination = $pagination['html'];
        $data->pagination_js = $pagination['javascript'];
        $data->html = $data->tablerows . "\n" . $data->pagination . "\n";
        if ($data->pagination_js) {
            $data->html .= '<script type="application/javascript">' . "\n";
            $data->html .= ' jQuery(function () {' . "\n";
            $data->html .= '   assessmentpaginator' . $data->block . ' = ' . $data->pagination_js . "\n";
            $data->html .= ' });' . "\n";
            $data->html .= '</script>' . "\n";
        }
    }

    public function render_self($options) {
        return clean_html($this->get('description'));
    }

    public static function add_assessment_form($defaultprivate=false, $blockid=0, $id=0) {
        global $USER;
        $form = array(
            'name'            => 'add_assessment_form_' . $blockid,
            'method'          => 'post',
            'plugintype'      => 'artefact',
            'pluginname'      => 'peerassessment',
            'jsform'          => true,
            'autofocus'       => false,
            'elements'        => array(),
            'successcallback'   => 'add_assessment_form_submit',
            'validatecallback'  => 'add_assessment_form_validate',
            'jssuccesscallback' => 'addPeerassessmentSuccess',
            'jserrorcallback'   => 'addPeerassessmentError',
        );
        $form['elements']['message'] = array(
            'type'  => 'wysiwyg',
            'title' => get_string('Assessment', 'artefact.peerassessment'),
            'class' => 'hide-label',
            'rows'  => 5,
            'cols'  => 80,
            'rules' => array('maxlength' => 1000000),
        );
        $form['elements']['block'] = array(
            'type' => 'hidden',
            'value' => $blockid,
        );
        $form['elements']['assessment'] = array(
            'type' => 'text',
            'defaultvalue' => $id,
            'class' => 'hidden',
        );
        $form['elements']['submit'] = array(
            'type' => 'multisubmit',
            'name' => 'peerassessment',
            'options' => array('draftsubmit' => 'draft',
                               'submit' => 'publish',
                               'cancel' => 'cancel'),
            'primarychoice' => 'publish',
            'classes' => array('draftsubmit' => 'btn-secondary',
                               'submit' => 'btn-secondary',
                               'cancel' => 'btn-secondary submitcancel'),
            'value' => array('draftsubmit' => get_string('draft', 'blocktype.peerassessment/peerassessment'),
                             'submit' => get_string('publish', 'blocktype.peerassessment/peerassessment'),
                             'cancel' => get_string('cancel'))
        );
        $form['elements']['helpnotes'] = array(
            'type' => 'html',
            'value' => get_string('savepublishhelp', 'blocktype.peerassessment/peerassessment'),
        );
        return $form;
    }

    public static function delete_assessment_form($id, $viewid, $blockid) {
        global $THEME;
        return array(
            'name'     => 'delete_assessment_' . $id,
            'method' => 'post',
            'autofocus'         => false,
            'renderer'          => 'oneline',
            'plugintype'        => 'artefact',
            'pluginname'        => 'peerassessment',
            'class'             => 'form-as-button pull-left assessbtn last',
            'jsform'            => true,
            'successcallback'   => 'delete_assessment_submit',
            'jssuccesscallback' => 'modifyPeerassessmentSuccess',
            'elements' => array(
                'assessment' => array('type' => 'hidden', 'value' => $id),
                'view' => array('type' => 'hidden', 'value' => $viewid),
                'block' => array('type' => 'hidden', 'value' => $blockid),
                'submit'  => array(
                    'type'  => 'button',
                    'usebuttontag' => true,
                    'class' => 'btn-secondary btn-sm',
                    'value' => '<span class="icon icon-trash icon-lg text-danger" role="presentation" aria-hidden="true"></span> <span class="sr-only">' . get_string('delete') . '</span>',
                    'confirm' => get_string('reallydeletethisassessment', 'artefact.peerassessment'),
                    'name'  => 'delete_assessment_submit',
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

function delete_assessment_submit(Pieform $form, $values) {
    global $SESSION, $USER;

    $assessment = new ArtefactTypePeerassessment((int) $values['assessment']);
    $view = $assessment->get_view();
    $viewid = $view->get('id');
    $blockid = $assessment->get('block');
    $url = $view->get_url(false);
    $goto = get_config('wwwroot') . $url;
    // If this page is being marked, make assessments un-deletable until released
    // unless it is the last assessment still with in the editable timeframe
    $editableafter = time() - 60 * get_config_plugin('artefact', 'comment', 'commenteditabletime');
    $lastcomment = $assessment::last_public_assessment($viewid);
    if (($lastcomment === null || $assessment->get('private') || $assessment->get('id') == $lastcomment->id) && $assessment->get('mtime') > $editableafter) {
        $candelete = 1;
    }
    else {
        $candelete = 0;
    }

    if ($view->get('submittedstatus') == View::UNSUBMITTED || $candelete) {
        db_begin();
        $author = $assessment->get('author');
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
                        'section' => 'artefact.peerassessment',
                        'args'    => array($title),
                    ),
                    'message' => (object) array(
                        'key'     => 'deletedauthornotification1',
                        'section' => 'artefact.peerassessment',
                        'args'    => array(display_name($author), $title, html2text($assessment->get('description'))),
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

        $assessment->delete();
        db_commit();

        $assessmentoptions = ArtefactTypePeerassessment::get_assessment_options();
        $assessmentoptions->showcomment = $assessment->get('id');
        $assessmentoptions->view = $view;
        $assessmentoptions->block = $blockid;
        $newlist = ArtefactTypePeerassessment::get_assessments($assessmentoptions);
        $form->reply(PIEFORM_OK, array(
            'message' => get_string('assessmentremoved', 'artefact.peerassessment'),
            'goto' => $goto,
            'data' => $newlist,
        ));
    }
    else {
        $form->reply(PIEFORM_ERR, get_string('assessmentremovedfailed', 'artefact.peerassessment'));
    }
}

function add_assessment_form_validate(Pieform $form, $values) {
    global $USER, $view;
    if (empty($values['attachments']) && empty($values['message'])) {
        $form->set_error('message', get_string('messageempty', 'artefact.peerassessment'));
    }
}

function add_assessment_form_submit(Pieform $form, $values) {
    global $view, $USER;
    require_once('embeddedimage.php');

    if (empty($values['assessment'])) {
        // new one
        $data = (object) array(
            'title'       => get_string('Assessment', 'artefact.peerassessment'),
            'description' => $values['message'],
        );
        $data->view        = $view->get('id');
        $data->owner       = $view->get('owner');
        $data->group       = $view->get('group');
        $data->institution = $view->get('institution');

        $data->author = $USER->get('id');
        $data->usr = $USER->get('id');
        $data->block = $values['block'];
        $isprivate = ($values['peerassessment'] == 'draft') ? 1 : 0;
        $data->private = $isprivate;
        $assessment = new ArtefactTypePeerassessment(0, $data);
    }
    else {
        $assessment = new ArtefactTypePeerassessment($values['assessment']);
        $assessment->set('description', $values['message']);
        $isprivate = ($values['peerassessment'] == 'draft') ? 1 : 0;
        $assessment->set('private', $isprivate);
    }

    db_begin();

    $assessment->commit();

    $newdescription = EmbeddedImage::prepare_embedded_images($values['message'], 'assessment', $assessment->get('id'));

    if ($newdescription !== $values['message']) {
        $updated = new stdClass();
        $updated->id = $assessment->get('id');
        $updated->description = $newdescription;
        update_record('artefact', $updated, 'id');
    }

    $url = $assessment->get_view_url($view->get('id'), true, false);
    $goto = get_config('wwwroot') . $url;

    // If peer assessment is published we send a notification to page owner
    if (!$isprivate && $view->get('owner') != $USER->get('id')) {
        // Notify owner
        $data = (object) array(
            'assessmentid' => $assessment->get('id'),
            'viewid'    => $view->get('id'),
        );
        activity_occurred('assessmentfeedback', $data, 'artefact', 'peerassessment');
    }

    db_commit();

    $assessmentoptions = ArtefactTypePeerassessment::get_assessment_options();
    $assessmentoptions->showcomment = $assessment->get('id');
    $assessmentoptions->view = $view;
    $assessmentoptions->block = $values['block'];
    $newlist = ArtefactTypePeerassessment::get_assessments($assessmentoptions);

    $message = get_string('assessmentsubmitted', 'artefact.peerassessment');

    $form->reply(PIEFORM_OK, array(
        'message' => $message,
        'goto' => $goto,
        'data' => $newlist,
    ));
}

function add_assessment_form_cancel_submit(Pieform $form) {
    global $view;
    $form->reply(PIEFORM_CANCEL, array(
        'location' => $view->get_url(true),
    ));
}

class ActivityTypeArtefactPeerassessmentAssessmentfeedback extends ActivityTypePlugin {

    protected $viewid;
    protected $assessmentid;

    /**
     * @param array $data Parameters:
     *                    - viewid (int)
     *                    - assessmentid (int)
     */
    public function __construct($data, $cron=false) {
        parent::__construct($data, $cron);

        $assessment = new ArtefactTypePeerassessment($this->assessmentid);

        $this->overridemessagecontents = true;

        $onview = $assessment->get('view');
        if (!$viewrecord = get_record('view', 'id', $onview)) {
            throw new ViewNotFoundException(get_string('viewnotfound', 'error', $onview));
        }
        $userid = $viewrecord->owner;
        if (empty($this->url)) {
            $this->url = 'view/view.php?id=' . $onview;
        }

        // Now fetch the users that will need to get notified about this event
        $this->users = array();
        if (!empty($userid)) {
            $this->users = activity_get_users($this->get_id(), array($userid));
        }

        if (empty($this->users)) {
            // no one to notify - possibe if group 'feedbacknotify' is set to 0
            return;
        }

        $title = $viewrecord->title;
        $this->urltext = $title;
        $body = $assessment->get('description');
        $posttime = strftime(get_string('strftimedaydatetime'), $assessment->get('ctime'));

        // Internal
        $this->message = strip_tags(str_shorten_html($body, 200, true));
        // Seeing as things like emaildigest base the message on $this->message
        // we need to set the language for the $removedbyline here based on first user.
        $user = reset($this->users);
        $lang = (empty($user->lang) || $user->lang == 'default') ? get_config('lang') : $user->lang;

        $this->strings = (object) array(
            'subject' => (object) array(
                'key'     => 'newassessmentnotificationsubject',
                'section' => 'artefact.peerassessment',
                'args'    => array($title),
            ),
        );

        $this->url .= '&showcomment=' . $assessment->get('id');

        // Email
        $author = $assessment->get('usr');
        if ($author) {
            $this->fromuser = $author;
            // We don't need to send an email to the inbox of the author of the assessment as we send one to their outbox
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
                $lang, 'feedbacknotificationhtml', 'artefact.peerassessment',
                hsc($authorname), hsc($title), $posttime, clean_html($body), get_config('wwwroot') . $this->url
            );
            $this->users[$key]->emailmessage = get_string_from_language(
                $lang, 'feedbacknotificationtext1', 'artefact.peerassessment',
                $authorname, $title, $posttime, trim(html2text(htmlspecialchars($body))), get_config('wwwroot') . $this->url
            );
        }
    }

    public function get_plugintype() {
        return 'artefact';
    }

    public function get_pluginname() {
        return 'peerassessment';
    }

    public function get_required_parameters() {
        return array('assessmentid', 'viewid');
    }
}

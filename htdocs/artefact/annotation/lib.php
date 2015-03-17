<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-annotation
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
/**
 * Developement Notes:
 * We are creating this as an artefact to prepare for future changes:
 * 1. It can 'stand-alone' without a link to a view or an artefact.
 * 2. It can be linked to a particular artefact that is already on a view.
 * 3. It can be linked to an artefact that is not on a view (i.e. a file, a plan, etc.).
 *
 * Currently, an annotation can:
 * 1. Appear on a view multiple times.
 * 2. It is not linked to any particular artefact on the view.
 *    In the future, we will select an artefact on that view to link it to.
 * 3. It will be linked to the view it appears on (i.e. artefact.id = view_artefact.artefact)
 * 4. It does not have any attached files.
 * 5. It does not have any ratings.
 *
 * The database tables:
 * artefact_annotation - links an annotation to an artefact and/or view.
 * artefact_annotation_feedback - holds extra information about the annotationfeedback.
 * artefact_annotation_deletedby - holds static data
 */

defined('INTERNAL') || die();

require_once('activity.php');
require_once('license.php');
require_once('pieforms/pieform.php');

class PluginArtefactAnnotation extends PluginArtefact {

    public static function get_artefact_types() {
        return array(
            'annotation',
            'annotationfeedback',
        );
    }

    public static function get_block_types() {
        return array();
    }

    public static function get_plugin_name() {
        return 'annotation';
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
                'name' => 'annotationfeedback',
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

    /**
     * Called post install and after every upgrade to the artefact.
     * @param string $prevversion the previously installed version of this artefact.
     */
    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            // Since this is the first time, we need to
            // create the default settings and the static
            // table data for aretfact_annotation_deletedby.
            set_config_plugin('artefact', 'annotation', 'commenteditabletime', 10);
            foreach(ArtefactTypeAnnotationfeedback::deleted_by_types() as $index => $type) {
                insert_record('artefact_annotation_deletedby', (object)array('id' => ((int)$index + 1), 'name' => $type));
            }

            // If elasticsearch is installed, update the artefacttypesmap field to include
            // annotation and annotationfeedback.
            $sql = "SELECT value FROM {search_config} WHERE plugin='elasticsearch' AND field='artefacttypesmap'";
            if ($result = get_field_sql($sql, array())) {
                $elasticsearchartefacttypesmap = explode("\n", $result);
                // add annotation and annotationfeedback fields.
                $elasticsearchartefacttypesmap[] = "annotation|Annotation|Text";
                $elasticsearchartefacttypesmap[] = "annotationfeedback|Annotation|Text";
                // Now save the data including the new annotation fields.
                set_config_plugin('search', 'elasticsearch', 'artefacttypesmap', implode("\n", $elasticsearchartefacttypesmap));
            }

            // Now install the blocktype annotation only if Mahara was previously installed.
            // Otherwise, the Mahara installer will install everything.
            if (get_config('installed')) {
                if ($upgrade = check_upgrades('blocktype.annotation/annotation')) {
                    upgrade_plugin($upgrade);
                }
            }
        }
    }

    public static function view_export_extra_artefacts($viewids) {
        $artefacts = array();
        if (!$artefacts = get_column_sql("
            SELECT af.artefact
            FROM {artefact_annotation} an
            INNER JOIN {artefact_annotation_feedback} af ON an.annotation = af.onannotation
            WHERE af.deletedby IS NULL
            AND   an.view IN (" . join(',', array_map('intval', $viewids)) . ')', array())) {
            return array();
        }
        return $artefacts;
    }

    public static function artefact_export_extra_artefacts($artefactids) {
        if (!$artefacts = get_column_sql("
            SELECT af.artefact
            FROM {artefact_annotation} an
            INNER JOIN {artefact_annotation_feedback} af ON an.annotation = af.onannotation
            WHERE af.deletedby IS NULL
            AND   an.artefact IN (" . join(',', $artefactids) . ')', array())) {
            return array();
        }
        return $artefacts;
    }

    public static function get_cron() {
        return array(
            (object)array(
                'callfunction' => 'clean_annotationfeedback_notifications',
                'minute'       => '35',
                'hour'         => '22',
            ),
        );
    }

    public static function clean_annotationfeedback_notifications() {
        safe_require('notification', 'internal');
        PluginNotificationInternal::clean_notifications(array('annotationfeedback'));
    }

    public static function progressbar_link($artefacttype) {
        switch ($artefacttype) {
            case 'annotation':
                return 'view/sharedviews.php';
                break;
        }
    }

    public static function progressbar_additional_items() {
        return array(
            (object)array(
                'name' => 'annotation',
                'title' => get_string('placeannotationfeedback', 'artefact.annotation'),
                'plugin' => 'annotation',
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
            case 'annotation':
                $sql = "SELECT COUNT(*) AS completed
                        FROM {artefact}
                        WHERE artefacttype='annotation'
                        AND owner <> ?
                        AND author = ?";
                $count = get_records_sql_array($sql, array($USER->get('id'), $USER->get('id')));
                $meta->completed = $count[0]->completed;
                break;
            default:
                return false;
        }
        return $meta;
    }
}

class ArtefactTypeAnnotation extends ArtefactType {

    protected $annotation;  // artefactid of the annotation artefact.
    protected $artefact;    // artefactid of the artefact this annotation is linked to.
    protected $view;        // viewid of the view this annotation is linked to.

    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);

        if ($this->id && ($extra = get_record('artefact_annotation', 'annotation', $this->id))) {
            foreach($extra as $name => $value) {
                if (property_exists($this, $name)) {
                    $this->{$name} = $value;
                }
            }
        }
    }

    /**
     * For annotations, the artefact.mtime property is displayed to users, as the "Update on" date,
     * if it is later than the artefact's creation time. The purpose of this is for transparency
     * in communication, so that people will know that a later comment may be in response to one
     * that no longer exists.
     *
     * @see ArtefactType::set()
     */
    public function set($field, $value) {
        if (($field == 'title' || $field == 'description') && $this->{$field} != $value) {
            $this->lastcontentupdate = $this->mtime;
        }
        return parent::set($field, $value);
    }

    public static function is_singular() {
        return false;
    }

    public static function get_icon($options=null) {
        global $THEME;
        return $THEME->get_image_url('annotation', 'artefact/annotation');
    }

    public static function get_links($id) {
        return array();
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

    public function commit() {
        if (empty($this->dirty)) {
            return;
        }

        $new = empty($this->id);

        db_begin();

        parent::commit();

        $data = (object)array(
            'annotation'    => $this->get('id'),
            'view'          => $this->get('view'),
            'artefact'      => $this->get('artefact'),
        );

        if ($new) {
            insert_record('artefact_annotation', $data);
        }
        else {
            update_record('artefact_annotation', $data, 'annotation');
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

        // Delete all annotation feedback linked to this annotation.
        $sql = "SELECT af.id
                    FROM {artefact} a
                    INNER JOIN {artefact_annotation} an ON a.id = an.annotation
                    INNER JOIN {artefact_annotation_feedback} f ON an.annotation = f.onannotation
                    INNER JOIN {artefact} af ON f.artefact = af.id
                    WHERE a.id = ?
                    ORDER BY af.id DESC";
        $annotationfeedbackids = get_column_sql($sql, array($this->id));
        foreach ($annotationfeedbackids as $id) {
            $feedback = new ArtefactTypeAnnotationfeedback($id);
            $feedback->delete();
        }

        // Delete any embedded images for this annotation.
        // Don't use EmbeddedImage::delete_embedded_images() - it deletes by
        // the fileid. We need to delete by the resourceid.
        delete_records('artefact_file_embedded', 'resourceid', $this->id);
        delete_records('artefact_annotation', 'annotation', $this->id);
        parent::delete();
        db_commit();
    }

}

class ArtefactTypeAnnotationfeedback extends ArtefactType {

    protected $artefact; // The artefact id for the annotationfeedback record.
    protected $onannotation;  // The artefact id of the annotation that this annotationfeedback relates to.
    protected $private;
    protected $deletedby;
    protected $requestpublic;
    protected $lastcontentupdate;

    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);

        if ($this->id && ($extra = get_record('artefact_annotation_feedback', 'artefact', $this->id))) {
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
     * For annotations, the artefact.mtime property is displayed to users, as the "Update on" date,
     * if it is later than the artefact's creation time. The purpose of this is for transparency
     * in communication, so that people will know that a later feedback may be in response to one
     * that no longer exists.
     *
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

        // Now fix up the text in case there were any embedded images.
        // Do this after saving because we may not have an artefactid yet.
        // This will add a record to the artefact_file_embedded table
        // for each file that is embedded in the feedback text.
        require_once('embeddedimage.php');
        $newtext = EmbeddedImage::prepare_embedded_images($this->get('description'), 'annotationfeedback', $this->id);
        if ($newtext !== false && $newtext !== $this->get('description')) {
            $updatedartefact = new stdClass();
            $updatedartefact->id = $this->get('id');
            $updatedartefact->description = $newtext;
            update_record('artefact', $updatedartefact, 'id');
        }

        $data = (object)array(
            'artefact'      => $this->get('id'),
            'onannotation'  => $this->get('onannotation'),
            'private'       => $this->get('private'),
            'deletedby'     => $this->get('deletedby'),
            'requestpublic' => $this->get('requestpublic'),
        );
        if ($this->get('lastcontentupdate')) {
            $data->lastcontentupdate = db_format_timestamp($this->get('lastcontentupdate'));
        }

        if ($new) {
            insert_record('artefact_annotation_feedback', $data);
        }
        else {
            update_record('artefact_annotation_feedback', $data, 'artefact');
        }

        // Get the block instance that contains this artefact
        // so we can add to the view any artefacts containted in the feedback text
        // as well as the feedback itself.
        $sql = "SELECT bi.*
                FROM {block_instance} bi
                INNER JOIN {view_artefact} va ON va.view = bi.view
                WHERE bi.blocktype = 'annotation'
                AND va.artefact = ?";
        if ($blocks = get_records_sql_array($sql, array($this->get('onannotation')))) {
            require_once(get_config('docroot') . 'blocktype/lib.php');
            foreach ($blocks as $bi) {
                $block = new BlockInstance($bi->id);
                $blockconfig = $block->get('configdata');
                if (isset($blockconfig['artefactid']) && $blockconfig['artefactid'] == $this->get('onannotation')) {
                    // Currently, all annotations can only exist on views.
                    // But, put the check anyway.
                    if ($block->get('view')) {
                        // We found the annotation we're inputting feedback for.
                        // Rebuild the block's list and break out of the loop.
                        $block->rebuild_artefact_list();

//                         Otherwise, we can do this but any images that were deleted while editing will still exist.
//                         if (count_records_select('view_artefact', "view = {$block->get('view')} AND block = {$block->get('id')} AND artefact = {$this->get('id')}") == 0) {
//                             // Insert the feedback record in the view_artefact table.
//                             $va = new StdClass;
//                             $va->view = $block->get('view');
//                             $va->block = $block->get('id');
//                             // this is the feedback id that was just inserted/updated.
//                             $va->artefact = $this->get('id');
//                             insert_record('view_artefact', $va);
//                         }
//
//                         // Get any artefacts (i.e. images) that may have been embedded
//                         // in the feedback text.
//                         $feedbackartefacts = artefact_get_references_in_html($this->get('description'));
//                         if (count($feedbackartefacts) > 0) {
//
//                             // Get list of allowed artefacts.
//                             // Please note that images owned by other users that are place on feedback
//                             // will not be part of the view_artefact because the owner of the
//                             // annotation does not own the image being placed on the feedback.
//                             // Therefore, when exported as Leap2A, these images will not come through.
//                             require_once('view.php');
//                             $searchdata = array(
//                                             'extraselect'          => array(array('fieldname' => 'id', 'type' => 'int', 'values' => $feedbackartefacts)),
//                                             'userartefactsallowed' => true,  // If this is a group view, the user can add personally owned artefacts
//                             );
//                             $view = $block->get_view();
//                             list($allowedfeedbackartefacts, $count) = View::get_artefactchooser_artefacts(
//                                     $searchdata,
//                                     $view->get('owner'),
//                                     $view->get('group'),
//                                     $view->get('institution'),
//                                     true
//                             );
//                             foreach ($feedbackartefacts as $id) {
//                                 $va = new StdClass;
//                                 $va->view = $block->get('view');
//                                 $va->block = $block->get('id');
//                                 if (isset($allowedfeedbackartefacts[$id]) || isset($old[$id])) {
//                                     // only insert artefacts that the view can actually own
//                                     // and which are not already in the view_artefact table.
//                                     $va->artefact = $id;
//                                     if (count_records_select('view_artefact', "view = {$block->get('view')} AND block = {$block->get('id')} AND artefact = {$id}") == 0) {
//                                         insert_record('view_artefact', $va);
//                                     }
//                                 }
//                             }
//                         }
                    }

                    break;
                }
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
        return $THEME->get_image_url('annotation', 'artefact/annotation');
    }

    public function delete() {
        if (empty($this->id)) {
            return;
        }
        db_begin();
        $this->detach();
        // Don't use EmbeddedImage::delete_embedded_images() - it deletes by
        // the fileid. We need to delete by the resourceid.
        delete_records('artefact_file_embedded', 'resourceid', $this->id);
        delete_records('artefact_annotation_feedback', 'artefact', $this->id);
        parent::delete();
        db_commit();
    }

    /**
     * Delete annotationfeedback records in the artefact table and the artefat_annotation_feedback table.
     *
     * @param array $artefactids is a list of artefactids representing the annotationfeedback artefacts.
     */
    public static function bulk_delete($artefactids) {
        if (empty($artefactids)) {
            return;
        }

        $idstr = join(',', array_map('intval', $artefactids));

        db_begin();
        delete_records_select('artefact_annotation_feedback', 'artefact IN (' . $idstr . ')');
        parent::bulk_delete($artefactids);
        db_commit();
    }

    /**
     * Delete annotationfeedback records in the artefact table and the artefat_annotation_feedback table
     * that are on a particular view.
     *
     * @param array $viewid is the id of the view that the annotation is linked to.
     */
    public static function delete_annotation_feedback_onview($viewid) {
        // Get a list of the annotationfeedback ids that are on the viewid.
        $sql = "SELECT f.artefact
                FROM {artefact_annotation} a
                INNER JOIN {artefact_annotation_feedback} f on a.annotation = f.onannotation
                WHERE a.view = ?";

        $artefacts = get_records_sql_assoc($sql, array($viewid));
        $ids = array_keys($artefacts);
        self::bulk_delete($ids);
    }

    /**
     * Delete annotationfeedback records in the artefact table and the artefat_annotation_feedback table
     * that are on a particular artefact.
     *
     * @param array $artefactid is the id of the artefact that the annotation is linked to.
     */
    public static function delete_annotation_feedback_onartefact($artefactid) {
        // Get a list of the annotationfeedback ids that are on the artefactid.
        $sql = "SELECT f.artefact
                FROM {artefact_annotation} a
                INNER JOIN {artefact_annotation_feedback} f on a.annotation = f.onannotation
                WHERE a.artefact = ?";

        $artefacts = get_records_sql_assoc($sql, array($artefactid));
        $ids = array_keys($artefacts);
        self::bulk_delete($ids);
    }

    /**
     * Delete annotationfeedback records in the artefact table and the artefat_annotation_feedback table
     * that are for a particular annotation.
     *
     * @param array $artefactid is the id of the artefact that the annotation is linked to.
     */
    public static function delete_annotation_feedback_onannotation($artefactid) {
        $ids = get_column_sql("SELECT artefact FROM {artefact_annotation_feedback} WHERE onannotation IN ($artefactid)");
        self::bulk_delete($ids);
    }

    public static function get_links($id) {
        return array();
    }

    /**
     * Return an array of the user types that can delete a message.
     *
     * @return array
     */
    public static function deleted_by_types() {
        // An annotation can be deleted by these three types of users.
        return array('author', 'owner', 'admin');
    }

    /**
     * Return an array of the user types that can delete a message
     * with the corresponding string description.
     *
     * @return array
     */
    public static function deleted_by_types_description() {
        return array(
            'author' => 'commentremovedbyauthor',
            'owner'  => 'commentremovedbyowner',
            'admin'  => 'commentremovedbyadmin',
        );
    }

    /**
     * Generates default data object required for displaying annotations on the page.
     * The is called before populating with specific data to send to get_annotation_feedback() as
     * an easy way to add variables to get passed to get_annotation_feedback.
     *
     * int $limit              The number of comments to display (set to
     *                         0 for disabling pagination and showing all comments)
     * int $offset             The offset of comments used for pagination
     * int|string $showcomment Optionally show page with particular comment
     *                         on it or the last page. $offset will be ignored.
     *                         Specify either comment_id or 'last' respectively.
     *                         Set to null to use $offset for pagination.
     * int $annotation         The artefactid of the annotation.
     * int $view               Optional The view id that the annotation is linked to.
     * int $artefact           Optional artefact id that the annotation is linked to.
     * bool   $export          Determines if comments are fetched for html export purposes
     * bool   $onview          Optional - is viewing artefact annotations on view page so don't show edit buttons
     * string $sort            Optional - the sort order of the comments. Valid options are 'earliest' and 'latest'.
     * @return object $options Default annotations data object
     */
    public static function get_annotation_feedback_options() {
        $options = new stdClass();
        $options->limit = 10;
        $options->offset = 0;
        $options->showcomment = null;

        $options->annotation = '';      // artefactid for annotation.
        $options->artefact = '';        // artefactid that the annotation is linked to.
        $options->view = '';            // viewid that the annotation is linked to.
        $options->block = '';           // blockid that the annotation lives in.

        $options->export = false;
        $sortorder = get_user_institution_comment_sort_order();
        $options->sort = (!empty($sortorder)) ? $sortorder : 'earliest';
        return $options;
    }

    /**
     * Generates the data object required for displaying annotations on the page.
     *
     * @param   object  $options  Object of annotation options
     *                            - defaults can be retrieved from get_annotation_feedback_options()
     * @return  object $result    Annotation data object
     */
    public static function get_annotation_feedback($options) {
        global $USER;
        // set the object's key/val pairs as variables
        foreach ($options as $key => $option) {
            $$key = $option;
        }
        $userid = $USER->get('id');

        $canedit = false;
        if (!empty($artefact)) {
            // This is the artefact that the annotation is linked to.
            $artefactobj = artefact_instance_from_id($artefact);
            $canedit = $USER->can_edit_artefact($artefactobj);
            $owner = $artefactobj->get('owner');
            $isowner = $userid && $userid == $owner;
            $view = null;
        }
        else if (!empty($view)) {
            // This is the view that the annotation is linked to.
            $viewobj = new View($view);
            $canedit = $USER->can_moderate_view($viewobj);
            $owner = $viewobj->get('owner');
            $isowner = $userid && $userid == $owner;
            $artefact = null;
        }

        $result = (object) array(
            'limit'      => $limit,
            'offset'     => $offset,
            'annotation' => $annotation,
            'view'       => $view,
            'artefact'   => $artefact,
            'block'      => $block,
            'canedit'    => $canedit,
            'owner'      => $owner,
            'isowner'    => $isowner,
            'export'     => $export,
            'sort'       => $sort,
            'data'       => array(),
        );

        $wherearray = array();
        $wherearray[] = 'a.id = ' . (int) $annotation;

        // if artefact and view are not set, this annotation is not linked to anything.
        if (!empty($artefact)) {
            $wherearray[] = 'an.artefact = ' . (int) $artefact;
        }
        else if (!empty($view)) {
            $wherearray[] = 'an.view = ' . (int) $view;
        }
        else {
            // Something is wrong.  Don't show anything.
            $wherearray[] = '1 = 2';
        }
        if (!$canedit) {
            $wherearray[] = '(f.private = 0 OR af.author = ' . (int) $userid . ')';
        }
        $where = implode(' AND ', $wherearray);

        $sql = 'SELECT COUNT(*)
                FROM {artefact} a
                INNER JOIN {artefact_annotation} an ON a.id = an.annotation
                INNER JOIN {artefact_annotation_feedback} f ON an.annotation = f.onannotation
                INNER JOIN {artefact} af ON f.artefact = af.id
                INNER JOIN {usr} u ON a.author = u.id
                LEFT JOIN {usr} uf ON af.author = uf.id
                LEFT JOIN {usr_institution} uif ON uf.id = uif.usr
                WHERE ' . $where;
        $result->count = count_records_sql($sql);

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
                    // This will get ugly if there are 1000s of feedback.
                    $ids = get_column_sql('
                        SELECT f.artefact
                        FROM {artefact} a
                        INNER JOIN {artefact_annotation} an ON a.id = an.annotation
                        INNER JOIN {artefact_annotation_feedback} f ON an.annotation = f.onannotation
                        INNER JOIN {artefact} af ON f.artefact = af.id
                        INNER JOIN {usr} u ON a.author = u.id
                        LEFT JOIN {usr} uf ON af.author = uf.id
                        LEFT JOIN {usr_institution} uif ON uf.id = uif.usr
                        WHERE ' . $where . '
                        AND f.artefact <= ?
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

            $sortorder = (!empty($sort) && $sort == 'latest') ? 'af.ctime DESC' : 'af.ctime ASC';
            $sql = 'SELECT
                        af.id, af.author, af.authorname, af.ctime, af.mtime, af.description, af.group,
                        f.private, f.deletedby, f.requestpublic, f.lastcontentupdate,
                        uf.username, uf.firstname, uf.lastname, uf.preferredname, uf.email, uf.staff, uf.admin,
                        uf.deleted, uf.profileicon, uf.urlid,
                        uif.staff as feedbackinstitutionstaff, uif.admin as feedbackinstitutionadmin
                    FROM {artefact} a
                    INNER JOIN {artefact_annotation} an ON a.id = an.annotation
                    INNER JOIN {artefact_annotation_feedback} f ON an.annotation = f.onannotation
                    INNER JOIN {artefact} af ON f.artefact = af.id
                    INNER JOIN {usr} u ON a.author = u.id
                    LEFT JOIN {usr} uf ON af.author = uf.id
                    LEFT JOIN {usr_institution} uif ON uf.id = uif.usr
                    WHERE ' . $where . '
                    ORDER BY ' . $sortorder;
            $annotationfeedback = get_records_sql_assoc($sql, array(), $offset, $limit);
            $result->data = array_values($annotationfeedback);
        }

        $result->position = 'blockinstance';

        self::build_html($result, $view);
        return $result;
    }

    public static function build_html(&$data, $onview) {
        global $USER, $THEME;

        $candelete = $data->canedit || $USER->get('admin');
        $deletedmessage = array();
        foreach (ArtefactTypeAnnotationfeedback::deleted_by_types_description() as $k => $v) {
            $deletedmessage[$k] = get_string($v, 'artefact.annotation');
        }
        $authors = array();
        $lastcomment = self::last_public_annotation_feedback($data->annotation, $data->view, $data->artefact);
        $editableafter = time() - 60 * get_config_plugin('artefact', 'annotation', 'commenteditabletime');
        foreach ($data->data as &$item) {
            $isadminfeedback = $item->admin == 1 || $item->staff == 1 || $item->feedbackinstitutionadmin == 1 || $item->feedbackinstitutionstaff == 1;
            $item->ts = strtotime($item->ctime);
            $item->date = format_date($item->ts, 'strftimedatetime');
            if ($item->ts < strtotime($item->lastcontentupdate)) {
                $item->updated = format_date(strtotime($item->lastcontentupdate), 'strftimedatetime');
            }
            $item->isauthor = $item->author && $item->author == $USER->get('id');
            if ($item->private) {
                $item->pubmessage = get_string('annotationfeedbackisprivate', 'artefact.annotation');
            }

            if (isset($data->showcomment) && $data->showcomment == $item->id) {
                $item->highlight = 1;
            }
            $is_export_preview = param_integer('export', 0);
            if ($item->deletedby) {
                $item->deletedmessage = $deletedmessage[$item->deletedby];
            }
            else if (($candelete || $item->isauthor) && !$is_export_preview && !$isadminfeedback) {
                // If the auther was admin/staff and not the owner of the annotation,
                // the feedback can't be deleted.
                $item->deleteform = pieform(self::delete_annotation_feedback_form($data->annotation, $data->view, $data->artefact, $data->block, $item->id));
            }

            // Comment authors can edit recent comments if they're private or if no one has replied yet.
            if (!$item->deletedby && $item->isauthor && !$is_export_preview
                && ($item->private || $item->id == $lastcomment->id) && $item->ts > $editableafter) {
                $item->canedit = 1;
            }

            // Form to make private comment public, or request that a
            // private comment be made public.
            if (!$item->deletedby && $item->private && $item->author && $data->owner
                && ($item->isauthor || $data->isowner)) {
                if ((empty($item->requestpublic) && $data->isowner)
                    || $item->isauthor && $item->requestpublic == 'owner'
                    || $data->isowner && $item->requestpublic == 'author') {
                    if (!$is_export_preview) {
                        $item->makepublicform = pieform(self::make_annotation_feedback_public_form($data->annotation, $data->view, $data->artefact, $data->block, $item->id));
                    }
                }
                else if ($item->isauthor && $item->requestpublic == 'author'
                         || $data->isowner && $item->requestpublic == 'owner') {
                    $item->makepublicrequested = 1;
                }
            }
            else if (!$item->deletedby && $item->private && !$item->author
                && $data->owner && $data->isowner && $item->requestpublic == 'author' && !$is_export_preview) {
                $item->makepublicform = pieform(self::make_annotation_feedback_public_form($data->annotation, $data->view, $data->artefact, $data->block, $item->id));
            }
            else if (!$item->deletedby && $item->private && !$data->owner
                && $item->group && $item->requestpublic == 'author') {
                // no owner as comment is on a group view / artefact
                if ($item->isauthor) {
                    $item->makepublicrequested = 1;
                }
                else {
                    if (($data->artefact && $data->canedit) || ($data->view && $data->canedit) && !$is_export_preview) {
                        $item->makepublicform = pieform(self::make_annotation_feedback_public_form($data->annotation, $data->view, $data->artefact, $data->block, $item->id));
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
        }

        $extradata = array(
                'annotation' => $data->annotation,
                'view'       => $data->view,
                'artefact'   => (!empty($data->artefact) ? $data->artefact : ''),
                'blockid'    => $data->block,
            );
        $data->jsonscript = 'artefact/annotation/annotations.json.php';

        $data->baseurl = get_config('wwwroot') . 'artefact/artefact.php?' .
            'artefact=' . $data->annotation .
            '&view=' . $data->view .
            (isset($data->block) ? '&block=' . $data->block : '');

        $smarty = smarty_core();
        $smarty->assign_by_ref('data', $data->data);
        $smarty->assign('canedit', $data->canedit);
        $smarty->assign('viewid', $data->view);
        $smarty->assign('position', $data->position);
        $smarty->assign('baseurl', $data->baseurl);
        $data->tablerows = $smarty->fetch('artefact:annotation:annotationlist.tpl');
        $pagination = build_pagination(array(
            'id' => 'annotationfeedback_pagination_' . $data->block,
            'class' => 'center',
            'url' => $data->baseurl,
            'jsonscript' => $data->jsonscript,
            'datatable' => 'annotationfeedbacktable_' . $data->block,
            'count' => $data->count,
            'limit' => $data->limit,
            'offset' => $data->offset,
            'forceoffset' => isset($data->forceoffset) ? $data->forceoffset : null,
            'resultcounttextsingular' => get_string('annotation', 'artefact.annotation'),
            'resultcounttextplural' => get_string('annotations', 'artefact.annotation'),
            'extradata' => $extradata,
        ));
        $data->pagination = $pagination['html'];
        $data->pagination_js = $pagination['javascript'];
    }

    public static function last_public_annotation_feedback($annotation, $onview=null, $onartefact=null) {
        if (!empty($onartefact)) {
            $where = 'an.artefact = ?';
            $values = array($onartefact);
        }
        else if (!empty($onview)) {
            $where = 'an.view = ?';
            $values = array($onview);
        }
        $newest = get_records_sql_array('
            SELECT af.id, af.ctime
            FROM {artefact} a
            INNER JOIN {artefact_annotation} an ON a.id = an.annotation
            INNER JOIN {artefact_annotation_feedback} f ON an.annotation = f.onannotation
            INNER JOIN {artefact} af ON f.artefact = af.id
            WHERE f.private = 0
            AND   a.id = ' . (int) $annotation . '
            AND   ' . $where . '
            ORDER BY af.ctime DESC', $values, 0, 1
        );
        return $newest[0];
    }

    /**
     * Fetching the annotations for an artefact to display on a view
     *
     * @param   object  $annotationartefact  The annotation artefact to display feedbacks for.
     * @param   object  $view     The view on which the annotation artefact is linked to.
     * @param   int     $blockid  The id of the block instance that connects the artefact to the view
     * @param   int     @annotationscountonview The number annotations alread on the view. If one is already
     *                            on there, don't add the add_annotation_feedback_form as it's already been
     *                            created.
     * @param   bool    $html     Whether to return the information rendered as html or not
     * @param   bool    $editing  Whether we are view edit mode or not
     */
    public function get_annotation_feedback_for_view($annotationartefact, $view, $blockid, $html = true, $editing = false) {
        global $USER;
        if (!is_object($annotationartefact) || !is_object($view)) {
            throw new MaharaException(get_string('annotationinformationerror', 'artefact.annotation'));
        }

        // If there is annotation feedback, retrieve it so it can be displayed -
        // even if no annotation feedback is turned on.
        $options = ArtefactTypeAnnotationfeedback::get_annotation_feedback_options();
        // Don't paginate when the annotation is on a view.  It gets mixed up
        // with the pagination of feedback on a view.
        $options->limit = 0;
        $options->view = $view->get('id');
        $options->annotation = $annotationartefact->get('id');
        $options->block = $blockid;
        $annotationfeedback = ArtefactTypeAnnotationfeedback::get_annotation_feedback($options);
        $annotationfeedbackcount = isset($annotationfeedback->count) ? $annotationfeedback->count : 0;

        if ($html) {
            // Return the rendered form.
            $smarty = smarty_core();
            if ($annotationartefact->get('allowcomments') && !$editing) {
                $addannotationfeedbackform = pieform(ArtefactTypeAnnotationfeedback::add_annotation_feedback_form(false, $annotationartefact->get('approvecomments'), $annotationartefact, $view, null, $blockid));
                $smarty->assign('addannotationfeedbackform', $addannotationfeedbackform);
            }
            else {
                // The user has switched off annotation feedback. Don't create the add annotation feedback form.
                $smarty->assign('addannotationfeedbackform', null);
            }
            $smarty->assign('blockid', $blockid);
            $smarty->assign('annotationfeedbackcount', $annotationfeedbackcount);
            $smarty->assign('annotationfeedback', $annotationfeedback);
            $smarty->assign('editing', $editing);
            $smarty->assign('allowfeedback', $USER->is_logged_in() && $annotationartefact->get('allowcomments'));
            $render = $smarty->fetch('artefact:annotation:annotationfeedbackview.tpl');
            return array($annotationfeedbackcount, $render);
        }
        else {
            // Return the array of raw data unrendered.
            return array($annotationfeedbackcount, $annotationfeedback);
        }
    }

    /**
     * Get the total number of annotation feedback inserted so far for a particular
     * annotation on a: view and/or on an artefact.
     * @param int $annotation
     * @param array $viewids list of viewids that the annotation lives on.
     * @param array $artefactids list of artefactids that the annotation is linked to.
     * @param boolean $bystafforadmin TRUE - retrieve the count of feedback input by users who are staff and/or admin.
     *                                FALSE - retrieve the count of feedback input by users who are NOT staff or admin.
     *                                NULL - retrieve the count of feedback input by users irrespecitve of the staff/admin flags.
     * @return array
     */
    public static function count_annotation_feedback($annotation, $viewids=null, $artefactids=null, $bystafforadmin = null) {
        $userwherearray = array();
        $instwherearray = array();
        $userwhere = '';
        $instwhere = '';
        if ($bystafforadmin === true) {
            $userwherearray[] = '(u.staff = 1 OR u.admin = 1)';
            $instwherearray[] = '(ui.staff = 1 OR ui.admin = 1)';
        }
        else if ($bystafforadmin === false) {
            $userwherearray[] = '(u.staff = 0 OR u.staff = 0)';
            $instwherearray[] = '(ui.staff = 0 OR ui.admin = 0)';
        }
        if (count($userwherearray) > 0) {
            $userwhere = ' AND ' . implode(' AND ', $userwherearray);
            $instwhere =  ' AND ' . implode(' AND ', $instwherearray);
        }
        if (!empty($viewids)) {
            // Get the count of feedback on a view for a particular annotation.
            $sql = 'SELECT an.view, COUNT(f.artefact) AS total
                    FROM {artefact} a
                    INNER JOIN {artefact_annotation} an ON a.id = an.annotation
                    INNER JOIN {artefact_annotation_feedback} f ON an.annotation = f.onannotation
                    INNER JOIN {artefact} af ON af.id = f.artefact
                    INNER JOIN {usr} u ON af.author = u.id
                    LEFT JOIN {usr_institution} ui ON u.id = ui.usr
                        ' . $instwhere . '
                    WHERE an.annotation = ?
                    AND an.view IN (' . join(',', array_map('intval', $viewids)) . ')
                    AND f.deletedby IS NULL
                    ' . $userwhere . '
                    GROUP BY an.view';
            return get_records_sql_assoc($sql, array((int) $annotation));
        }
        if (!empty($artefactids)) {
            // Get the count of feedback on an artefact for a particular annotation.
            $sql = 'SELECT an.artefact, COUNT(c.artefact) AS total
                    FROM {artefact} a
                    INNER JOIN {artefact_annotation} an ON a.id = an.annotation
                    INNER JOIN {artefact_annotation_feedback} f ON an.annotation = f.onannotation
                    INNER JOIN {artefact} af ON af.id = f.artefact
                    INNER JOIN {usr} u ON af.author = u.id
                    LEFT JOIN {usr_institution} ui ON u.id = ui.usr
                        ' . $instwhere . '
                    WHERE an.annotation = ?
                    AND an.artefact IN (' . join(',', array_map('intval', $artefactids)) . ')
                    AND f.deletedby IS NULL
                    ' . $userwhere . '
                    GROUP BY an.artefact';
            return get_records_sql_assoc($sql, array((int) $annotation));
        }
    }

    public function render_self() {
        return clean_html($this->get('description'));
    }

    /**
     * Create a form so the user can enter feedback for an annotation that is linked to
     * a view or an artefact.
     * @param boolean $defaultprivate set the private setting. Default is false.
     * @param boolean $moderate if moderating feedback. Default is false.
     * @param object $annotation the annotation artefact object.
     * @param object $view the view object that the annotation is linked to.
     * @param object $artefact the artefact object that the annotation is linked to.
     * @return multitype:string multitype:NULL string
     */
    public static function add_annotation_feedback_form($defaultprivate=false, $moderate=false, $annotation, $view, $artefact, $blockid) {
        global $USER;
        $form = array(
            'name'              => 'add_annotation_feedback_form_' . $blockid,
            'method'            => 'post',
            'class'             => 'js-hidden hidden add_annotation_feedback_form',
            'plugintype'        => 'artefact',
            'pluginname'        => 'annotation',
            'jsform'            => true,
            'autofocus'         => false,
            'elements'          => array(),
            'jssuccesscallback' => 'addAnnotationFeedbackSuccess',
            'successcallback'   => 'add_annotation_feedback_form_submit',
            'validatecallback'  => 'add_annotation_feedback_form_validate',
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
            'title' => get_string('Annotationfeedback', 'artefact.annotation'),
            'rows'  => 5,
            'cols'  => 80,
            'rules' => array('maxlength' => 8192),
        );
        $form['elements']['ispublic'] = array(
            'type'  => 'checkbox',
            'title' => get_string('makepublic', 'artefact.annotation'),
            'defaultvalue' => !$defaultprivate,
        );

        // What is this annotation feedback linked to? Store it in hidden fields.
        $form['elements']['viewid'] = array(
            'type'  => 'hidden',
            'value' => (isset($view) ? $view->get('id') : null),
        );
        $form['elements']['artefactid'] = array(
            'type'  => 'hidden',
            'value' => (isset($artefact) ? $artefact->get('id') : null),
        );
        // Save the artefactid of the annotation.
        $form['elements']['annotationid'] = array(
            'type'  => 'hidden',
            'value' => $annotation->get('id'),
        );
        $form['elements']['blockid'] = array(
            'type'  => 'hidden',
            'value' => $blockid,
        );

        if ($moderate) {
            $form['elements']['ispublic']['description'] = get_string('approvalrequired', 'artefact.annotation');
            $form['elements']['moderate'] = array(
                'type'  => 'hidden',
                'value' => true,
            );
        }
        $form['elements']['submit'] = array(
            'type'  => 'submitcancel',
            'value' => array(get_string('placeannotationfeedback', 'artefact.annotation'), get_string('cancel')),
            'goto' => '/' . $view->get_url(false),
        );
        return $form;
    }

    public static function make_annotation_feedback_public_form($annotationid, $viewid, $artefactid, $blockid, $id) {
        return array(
            'name'              => 'make_annotation_feedback_public_' . $id,
            'method'            => 'post',
            'autofocus'         => false,
            'renderer'          => 'oneline',
            'plugintype'        => 'artefact',
            'pluginname'        => 'annotation',
            'jsform'            => true,
            'successcallback'   => 'make_annotation_feedback_public_submit',
            'validatecallback'  => 'make_annotation_feedback_public_validate',
            'jssuccesscallback' => 'modifyAnnotationFeedbackSuccess',
            'elements'        => array(
                'annotationfeedbackid' => array('type' => 'hidden', 'value' => $id),
                'annotationid' => array('type' => 'hidden', 'value' => $annotationid),
                'viewid' => array('type' => 'hidden', 'value' => $viewid),
                'artefactid' => array('type' => 'hidden', 'value' => $artefactid),
                'blockid' => array('type' => 'hidden', 'value' => $blockid),
                'submit'   => array(
                    'type'  => 'submit',
                    'class' => 'quiet',
                    'name'  => 'make_annotation_feedback_public_submit',
                    'value' => get_string('makepublic', 'artefact.annotation'),
                ),
            ),
        );
    }

    public static function delete_annotation_feedback_form($annotationid, $viewid, $artefactid, $blockid, $id) {
        global $THEME;
        return array(
            'name'              => 'delete_annotation_feedback_' . $id,
            'method'            => 'post',
            'autofocus'         => false,
            'renderer'          => 'oneline',
            'plugintype'        => 'artefact',
            'pluginname'        => 'annotation',
            'jsform'            => true,
            'successcallback'   => 'delete_annotation_feedback_submit',
            'jssuccesscallback' => 'modifyAnnotationFeedbackSuccess',
            'elements' => array(
                'annotationfeedbackid' => array('type' => 'hidden', 'value' => $id),
                'annotationid' => array('type' => 'hidden', 'value' => $annotationid),
                'viewid' => array('type' => 'hidden', 'value' => $viewid),
                'artefactid' => array('type' => 'hidden', 'value' => $artefactid),
                'blockid' => array('type' => 'hidden', 'value' => $blockid),
                'submit'  => array(
                    'type'  => 'image',
                    'src' => $THEME->get_image_url('btn_deleteremove'),
                    'value' => get_string('delete'),
                    'elementtitle' => get_string('delete'),
                    'confirm' => get_string('reallydeletethisannotationfeedback', 'artefact.annotation'),
                    'name'  => 'delete_annotation_feedback_submit',
                ),
            ),
        );
    }

    public function exportable() {
        return empty($this->deletedby);
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
}

/**
 * To make private annotations public, both the author and the owner must agree.
 * @param Pieform $form
 * @param array $values data entered on pieform.
 */
function make_annotation_feedback_public_validate(Pieform $form, $values) {
    global $USER;
    $annotationfeedback = new ArtefactTypeAnnotationFeedback((int) $values['annotationfeedbackid']);

    if (!empty($values['viewid']) && !can_view_view($values['viewid'])) {
        // The user does not access to this view.
        $form->set_error('annotationfeedbackid', get_string('noaccesstoview', 'view'));
    }

    $author    = $annotationfeedback->get('author');
    $owner     = $annotationfeedback->get('owner');
    $requester = $USER->get('id');
    $group     = $annotationfeedback->get('group');

    if (!$owner && !$group) {
        $form->set_error('annotationfeedbackid', get_string('makepublicnotallowed', 'artefact.annotation'));
    }
    else if (!$owner && $group) {
        if ($requester) {
            $allowed = false;
            // check to see if the requester is a group admin
            $group_admins = group_get_admin_ids($group);
            if (array_search($requester,$group_admins) === false) {
                $form->set_error('annotationfeedbackid', get_string('makepublicnotallowed', 'artefact.annotation'));
            }
        }
        else {
            $form->set_error('annotationfeedbackid', get_string('makepublicnotallowed', 'artefact.annotation'));
        }
    }
    else if (!$owner || !$requester || ($requester != $owner && $requester != $author)) {
        $form->set_error('annotationfeedbackid', get_string('makepublicnotallowed', 'artefact.annotation'));
    }
}

function make_annotation_feedback_public_submit(Pieform $form, $values) {
    global $USER;

    $annotationfeedback = new ArtefactTypeAnnotationFeedback((int) $values['annotationfeedbackid']);
    $annotationid = $annotationfeedback->get('onannotation');
    $annotation = new ArtefactTypeAnnotation((int) $annotationid);

    $viewid = $values['viewid'];
    $view = new View($viewid);

    $relativeurl = $annotation->get_view_url($viewid, true, false);
    $url = get_config('wwwroot') . $relativeurl;

    $author    = $annotationfeedback->get('author');
    $owner     = $annotationfeedback->get('owner');
    $groupid   = $annotationfeedback->get('group');
    $group_admins = array();
    if ($groupid) {
        $group_admins = group_get_admin_ids($groupid);
    }
    $requester = $USER->get('id');

    if (($author == $owner && $requester == $owner)
        || ($requester == $owner  && $annotationfeedback->get('requestpublic') == 'author')
        || (array_search($requester,$group_admins) !== false && $annotationfeedback->get('requestpublic') == 'author')
        || ($requester == $author && $annotationfeedback->get('requestpublic') == 'owner')) {
        $annotationfeedback->set('private', 0);
        $annotationfeedback->set('requestpublic', null);
        $annotationfeedback->commit();

        $form->reply(PIEFORM_OK, array(
            'message' => get_string('annotationfeedbackmadepublic', 'artefact.annotation'),
            'goto'    => $url,
        ));
    }

    $subject = 'makepublicrequestsubject';
    if ($requester == $owner) {
        $annotationfeedback->set('requestpublic', 'owner');
        $message = 'makepublicrequestbyownermessage';
        $arg = display_name($owner, $author);
        $userid = $author;
        $sessionmessage = get_string('makepublicrequestsent', 'artefact.annotation', display_name($author));
    }
    else if ($requester == $author) {
        $annotationfeedback->set('requestpublic', 'author');
        $message = 'makepublicrequestbyauthormessage';
        $arg = display_name($author, $owner);
        $userid = $owner;
        $sessionmessage = get_string('makepublicrequestsent', 'artefact.annotation', display_name($owner));
    }
    else if (array_search($requester,$group_admins) !== false) {
        $annotationfeedback->set('requestpublic', 'owner');
        $message = 'makepublicrequestbyownermessage';
        $arg = display_name($requester, $author);
        $userid = $author;
        $sessionmessage = get_string('makepublicrequestsent', 'artefact.annotation', display_name($author));
    }
    else {
        // Something is wrong. Go back to the view.
        redirect($url);
    }

    db_begin();
    $annotationfeedback->commit();

    $data = (object) array(
        'subject'   => false,
        'message'   => false,
        'strings'   => (object) array(
            'subject' => (object) array(
                'key'     => $subject,
                'section' => 'artefact.annotation',
                'args'    => array(),
            ),
            'message' => (object) array(
                'key'     => $message,
                'section' => 'artefact.annotation',
                'args'    => array(hsc($arg)),
            ),
            'urltext' => (object) array(
                'key'     => 'Annotation',
                'section' => 'artefact.annotation',
            ),
        ),
        'users'     => array($userid),
        'url'       => $relativeurl,
    );
    activity_occurred('maharamessage', $data);
    db_commit();

    $options = ArtefactTypeAnnotationfeedback::get_annotation_feedback_options();
    $options->showcomment = $annotationfeedback->get('id');
    $options->artefact = $values['artefactid'];
    $options->view = $viewid;
    $options->annotation = $annotationid;
    $options->block = $values['blockid'];
    $newlist = ArtefactTypeAnnotationfeedback::get_annotation_feedback($options);

    $form->reply(PIEFORM_OK, array(
        'message' => $sessionmessage,
        'goto'    => $url,
        'data'    => $newlist,
    ));

}

function delete_annotation_feedback_submit(Pieform $form, $values) {
    global $USER;

    $annotationfeedback = new ArtefactTypeAnnotationfeedback((int) $values['annotationfeedbackid']);
    $view = new View($values['viewid']);
    $annotationid = $annotationfeedback->get('onannotation');
    $annotation = new ArtefactTypeAnnotation((int) $annotationid);

    if ($USER->get('id') == $annotationfeedback->get('author')) {
        $deletedby = 'author';
    }
    else if ($USER->can_edit_view($view)) {
        $deletedby = 'owner';
    }
    else if ($USER->get('admin')) {
        $deletedby = 'admin';
    }

    $viewid = $view->get('id');
    if ($artefactid = $annotation->get('artefact')) {
        $url = 'artefact/artefact.php?view=' . $viewid . '&artefact=' . $artefactid;
    }
    else {
        $url = $view->get_url(false);
    }

    db_begin();

    $annotationfeedback->set('deletedby', $deletedby);
    $annotationfeedback->commit();

    if ($deletedby != 'author') {
        // Notify author
        if ($artefactid) {
            $title = get_field('artefact', 'title', 'id', $artefactid);
        }
        else {
            $title = get_field('view', 'title', 'id', $viewid);
        }
        $title = hsc($title);
        $data = (object) array(
            'subject'   => false,
            'message'   => false,
            'strings'   => (object) array(
                'subject' => (object) array(
                    'key'     => 'annotationfeedbackdeletednotificationsubject',
                    'section' => 'artefact.annotation',
                    'args'    => array($title),
                ),
                'message' => (object) array(
                    'key'     => 'annotationfeedbackdeletedauthornotification',
                    'section' => 'artefact.annotation',
                    'args'    => array($title, html2text($annotationfeedback->get('description'))),
                ),
                'urltext' => (object) array(
                    'key'     => $artefactid ? 'artefact' : 'view',
                ),
            ),
            'users'     => array($annotationfeedback->get('author')),
            'url'       => $url,
        );
        activity_occurred('maharamessage', $data);
    }
    if ($deletedby != 'owner' && $annotationfeedback->get('owner') != $USER->get('id')) {
        // Notify owner
        $data = (object) array(
            'annotationfeedbackid' => $annotationfeedback->get('id'),
            'annotationid'         => $annotationid,
            'viewid'               => $viewid,
            'artefactid'           => $artefactid,
        );
        activity_occurred('annotationfeedback', $data, 'artefact', 'annotation');
    }

    db_commit();

    if (param_exists('offset')) {
        $options = ArtefactTypeAnnotationfeedback::get_annotation_feedback_options();
        $options->showcomment = $annotationfeedback->get('id');
        $options->artefact = $artefactid;
        $options->view = $viewid;
        $options->annotation = $annotationid;
        $options->block = $values['blockid'];
        $newlist = ArtefactTypeAnnotationfeedback::get_annotation_feedback($options);
    }
    else {
        $newlist = null;
    }

    $form->reply(PIEFORM_OK, array(
        'message' => get_string('annotationfeedbackremoved', 'artefact.annotation'),
        'goto'    => get_config('wwwroot') . $url,
        'data'    => $newlist,
    ));

}

function add_annotation_feedback_form_validate(Pieform $form, $values) {
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
    // Make sure that the user has not manipulated the ids.
    if (empty($values['artefactid']) && empty($values['viewid'])) {
        // One of them must have data.
        $form->set_error('message', get_string('invalidannotationfeedbacklinkerror', 'artefact.annotation'));
    }
    if (empty($values['annotationid'])) {
        $form->set_error('message', get_string('annotationinformationerror', 'artefact.annotation'));
    }
    if (!empty($values['viewid']) && !can_view_view($values['viewid'])) {
        // The user does not access to this view.
        $form->set_error('message', get_string('noaccesstoview', 'view'));
    }
    if (!empty($values['viewid']) &&
        !artefact_in_view($values['annotationid'], $values['viewid'])) {
        // The annotation is not on the view.
        $form->set_error('message', get_string('accessdenied', 'error'));
    }
    if (!empty($values['artefactid']) && !empty($values['viewid']) &&
        !artefact_in_view($values['artefactid'], $values['viewid'])) {
        // The artefact is not on the view.
        $form->set_error('message', get_string('accessdenied', 'error'));
    }

    if (empty($values['message'])) {
        $form->set_error('message', get_string('annotationfeedbackempty', 'artefact.annotation'));
    }

    $result = probation_validate_content($values['message']);
    if ($result !== true) {
        $form->set_error('message', get_string('newuserscantpostlinksorimages'));
    }
}

function add_annotation_feedback_form_submit(Pieform $form, $values) {
    global $USER;
    $data = (object) array(
        'title'        => get_string('Annotation', 'artefact.annotation'),
        'description'  => $values['message'],
        'onannotation' => $values['annotationid'],
    );

    // hidden fields.
    $artefactid = $values['artefactid'];
    $viewid = $values['viewid'];
    $blockid = $values['blockid'];

    if ($artefactid) {
        $artefact = artefact_instance_from_id($artefactid);
        $data->artefact    = $artefactid;
        $data->owner       = $artefact->get('owner');
        $data->group       = $artefact->get('group');
        $data->institution = $artefact->get('institution');
    }
    else if ($viewid) {
        $view = new View($viewid);
        $data->view        = $viewid;
        $data->owner       = $view->get('owner');
        $data->group       = $view->get('group');
        $data->institution = $view->get('institution');
    }

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

    $annotationfeedback = new ArtefactTypeAnnotationfeedback(0, $data);
    $annotation = new ArtefactTypeAnnotation($values['annotationid']);

    db_begin();

    $annotationfeedback->commit();

    $url = $annotation->get_view_url($view->get('id'), true, false);
    $goto = get_config('wwwroot') . $url;

    if (isset($data->requestpublic) && $data->requestpublic === 'author' && $data->owner) {
        $arg = $author ? display_name($USER, null, true) : $data->authorname;
        $moderatemsg = (object) array(
            'subject'   => false,
            'message'   => false,
            'strings'   => (object) array(
                'subject' => (object) array(
                    'key'     => 'makepublicrequestsubject',
                    'section' => 'artefact.annotation',
                    'args'    => array(),
                ),
                'message' => (object) array(
                    'key'     => 'makepublicrequestbyauthormessage',
                    'section' => 'artefact.annotation',
                    'args'    => array(hsc($arg)),
                ),
                'urltext' => (object) array(
                    'key'     => 'Annotation',
                    'section' => 'artefact.annotation',
                ),
            ),
            'users'     => array($data->owner),
            'url'       => $url,
        );
    }

    require_once('activity.php');
    $data = (object) array(
        'annotationfeedbackid' => $annotationfeedback->get('id'),
        'annotationid'         => $values['annotationid'],
        'viewid'               => $viewid,
        'artefactid'           => $artefactid,
    );
    activity_occurred('annotationfeedback', $data, 'artefact', 'annotation');

    if (isset($moderatemsg)) {
        activity_occurred('maharamessage', $moderatemsg);
    }

    db_commit();

    if (param_exists('offset')) {
        $options = ArtefactTypeAnnotationfeedback::get_annotation_feedback_options();
        $options->showcomment = 'last';
        $options->artefact = $artefactid;
        $options->view = $viewid;
        $options->annotation = $values['annotationid'];
        $options->block = $blockid;
        $newlist = ArtefactTypeAnnotationfeedback::get_annotation_feedback($options);
    }
    else {
        $newlist = null;
    }

    // If you're anonymous and your message is moderated or private, then you won't
    // be able to tell what happened to it. So we'll provide some more explanation in
    // the feedback message.
    if ($anonymous && $moderated) {
        $message = get_string('annotationfeedbacksubmittedmoderatedanon', 'artefact.annotation');
    }
    else if ($anonymous && $private) {
        $message = get_string('annotationfeedbacksubmittedprivateanon', 'artefact.annotation');
    }
    else {
        $message = get_string('annotationfeedbacksubmitted', 'artefact.annotation');
    }

    $form->reply(PIEFORM_OK, array(
        'message' => $message,
        'goto'    => $goto,
        'data'    => $newlist,
    ));
}

/**
 * Class to handle the annotationfeedback event for annotations.
 *
 */
class ActivityTypeArtefactAnnotationAnnotationfeedback extends ActivityTypePlugin {

    protected $annotationfeedbackid;
    protected $annotationid;
    protected $viewid;
    protected $artefactid;

    /**
     * @param array $data Parameters:
     *                    - viewid (int)
     *                    - annotationid (int)
     */
    public function __construct($data, $cron=false) {
        parent::__construct($data, $cron);

        $annotation = new ArtefactTypeAnnotation($this->annotationid);
        $annotationfeedback = new ArtefactTypeAnnotationfeedback($this->annotationfeedbackid);

        $this->overridemessagecontents = true;

        if ($onartefact = $annotation->get('artefact')) {
            // Feedback on artefact.
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
        else if ($onview = $annotation->get('view')) {
            // Feedback on view.
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
        else {
            // Something is wrong.
            throw new ViewNotFoundException(get_string('invalidannotationfeedbacklinkerror', 'artefact.annotation'));
        }

        // Now fetch the users that will need to get notified about this event
        // depending on whether the page has an owner, group, or institution id set.
        if (!empty($userid)) {
            $this->users = activity_get_users($this->get_id(), array($userid));
        }
        else if (!empty($groupid)) {
            require_once(get_config('docroot') . 'lib/group.php');
            $sql = "SELECT u.*
                    FROM {usr} u, {group_member} m, {group} g
                    WHERE g.id = m.group
                    AND m.member = u.id
                    AND m.group = ?
                    AND (g.feedbacknotify = " . GROUP_ROLES_ALL . "
                         OR (g.feedbacknotify = " . GROUP_ROLES_NONMEMBER . " AND (m.role = 'tutor' OR m.role = 'admin'))
                         OR (g.feedbacknotify = " . GROUP_ROLES_ADMIN . " AND m.role = 'admin')
                        )";
            $this->users = get_records_sql_array($sql, array($groupid));
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
        $body = $annotationfeedback->get('description');
        $posttime = strftime(get_string('strftimedaydatetime'), $annotationfeedback->get('ctime'));

        // Internal
        $this->message = strip_tags(str_shorten_html($body, 200, true));
        // Seen as things like emaildigest base the message on $this->message
        // we need to set the language for the $removedbyline here based on first user.
        $user = $this->users[0];
        $lang = (empty($user->lang) || $user->lang == 'default') ? get_config('lang') : $user->lang;

        // Comment deleted notification
        if ($deletedby = $annotationfeedback->get('deletedby')) {
            $this->strings = (object) array(
                'subject' => (object) array(
                    'key'     => 'annotationfeedbackdeletednotificationsubject',
                    'section' => 'artefact.annotation',
                    'args'    => array($title),
                ),
            );
            $deletedmessage = ArtefactTypeAnnotationfeedback::deleted_by_types_description();
            $removedbyline = get_string_from_language($lang, $deletedmessage[$deletedby], 'artefact.annotation');
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
                $removedbyline = get_string_from_language($lang, $deletedmessage[$deletedby], 'artefact.annotation');
                $this->users[$key]->htmlmessage = get_string_from_language(
                    $lang, 'annotationfeedbackdeletedhtml', 'artefact.annotation',
                    hsc($title), $removedbyline, clean_html($body), get_config('wwwroot') . $this->url, hsc($title)
                );
                $this->users[$key]->emailmessage = get_string_from_language(
                    $lang, 'annotationfeedbackdeletedtext', 'artefact.annotation',
                    $title, $removedbyline, trim(html2text(htmlspecialchars($body))), $title, get_config('wwwroot') . $this->url
                );
            }
            return;
        }

        $this->strings = (object) array(
            'subject' => (object) array(
                'key'     => 'newannotationfeedbacknotificationsubject',
                'section' => 'artefact.annotation',
                'args'    => array($title),
            ),
        );

        $this->url .= '&showcomment=' . $annotationfeedback->get('id');

        // Email
        $author = $annotationfeedback->get('author');
        foreach ($this->users as $key => $user) {
            $authorname = empty($author) ? $annotationfeedback->get('authorname') : display_name($author, $user);
            if (empty($user->lang) || $user->lang == 'default') {
                // check to see if we need to show institution language
                $instlang = get_user_institution_language($user->id);
                $lang = (empty($instlang) || $instlang == 'default') ? get_config('lang') : $instlang;
            }
            else {
                $lang = $user->lang;
            }
            $this->users[$key]->htmlmessage = get_string_from_language(
                $lang, 'annotationfeedbacknotificationhtml', 'artefact.annotation',
                hsc($authorname), hsc($title), $posttime, clean_html($body), get_config('wwwroot') . $this->url
            );
            $this->users[$key]->emailmessage = get_string_from_language(
                $lang, 'annotationfeedbacknotificationtext', 'artefact.annotation',
                $authorname, $title, $posttime, trim(html2text(htmlspecialchars($body))), get_config('wwwroot') . $this->url
            );
        }
    }

    public function get_plugintype(){
        return 'artefact';
    }

    public function get_pluginname(){
        return 'annotation';
    }

    public function get_required_parameters() {
        return array('annotationfeedbackid', 'annotationid');
    }
}

<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginArtefactResume extends PluginArtefact {

    public static function get_artefact_types() {
        return array(
            'coverletter',
            'contactinformation',
            'personalinformation',
            'employmenthistory',
            'educationhistory',
            'certification',
            'book',
            'membership',
            'interest',
            'personalgoal',
            'academicgoal',
            'careergoal',
            'personalskill',
            'academicskill',
            'workskill'
        );
    }

    public static function get_block_types() {
        return array();
    }

    public static function get_plugin_name() {
        return 'resume';
    }

    public static function is_active() {
        return get_field('artefact_installed', 'active', 'name', 'resume');
    }

    public static function menu_items() {
        return array(
            'content/resume' => array(
                'path' => 'content/resume',
                'title' => get_string('resume', 'artefact.resume'),
                'url' => 'artefact/resume/index.php',
                'weight' => 50,
            ),
        );
    }

    public static function get_artefact_type_content_types() {
        return array(
            'coverletter'   => array('text'),
            'interest'      => array('text'),
            'personalgoal'  => array('text'),
            'academicgoal'  => array('text'),
            'careergoal'    => array('text'),
            'personalskill' => array('text'),
            'academicskill' => array('text'),
            'workskill'     => array('text'),
        );
    }

    public static function submenu_items() {
        $tabs = array(
            'index' => array(
                'page'  => 'index',
                'url'   => 'artefact/resume',
                'title' => get_string('introduction', 'artefact.resume'),
            ),
            'employment' => array(
                'page'  => 'employment',
                'url'   => 'artefact/resume/employment.php',
                'title' => get_string('educationandemployment', 'artefact.resume'),
            ),
            'achievements' => array(
                'page'  => 'achievements',
                'url'   => 'artefact/resume/achievements.php',
                'title' => get_string('achievements', 'artefact.resume'),
            ),
            'goalsandskills' => array(
                'page'  => 'goalsandskills',
                'url'   => 'artefact/resume/goalsandskills.php',
                'title' => get_string('goalsandskills', 'artefact.resume'),
            ),
            'interests' => array(
                'page'  => 'interests',
                'url'   => 'artefact/resume/interests.php',
                'title' => get_string('interests', 'artefact.resume'),
            ),
            'license' => array(
                'page'  => 'license',
                'url'   => 'artefact/resume/license.php',
                'title' => get_string('license', 'artefact.resume'),
            ),
        );
        if (!get_config('licensemetadata')) {
            unset($tabs['license']);
        }
        if (defined('RESUME_SUBPAGE') && isset($tabs[RESUME_SUBPAGE])) {
            $tabs[RESUME_SUBPAGE]['selected'] = true;
        }
        return $tabs;
    }

    public static function composite_tabs() {
        return array(
            'educationhistory'  => 'employment',
            'employmenthistory' => 'employment',
            'certification'     => 'achievements',
            'book'              => 'achievements',
            'membership'        => 'achievements',
        );
    }

    public static function artefact_export_extra_artefacts($artefactids) {
        if (!$artefacts = get_column_sql("
            SELECT artefact
            FROM {artefact_attachment}
            WHERE artefact IN (" . join(',', $artefactids) . ')', array())) {
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

    public static function progressbar_link($artefacttype) {
        switch ($artefacttype) {
            case 'coverletter':
            case 'personalinformation':
                return 'artefact/resume/index.php';
                break;
            case 'educationhistory':
            case 'employmenthistory':
                return 'artefact/resume/employment.php';
                break;
            case 'certification':
            case 'book':
            case 'membership':
                return 'artefact/resume/achievements.php';
                break;
            case 'personalgoal':
            case 'academicgoal':
            case 'careergoal':
            case 'personalskill':
            case 'academicskill':
            case 'workskill':
                return 'artefact/resume/goalsandskills.php';
                break;
            case 'interest':
                return 'artefact/resume/interests.php';
                break;
            default:
                return '';
        }
    }
}

class ArtefactTypeResume extends ArtefactType {

    public static function get_icon($options=null) {}

    public function __construct($id=0, $data=array()) {
        if (empty($id)) {
            $data['title'] = get_string($this->get_artefact_type(), 'artefact.resume');
        }
        parent::__construct($id, $data);
    }

    public static function is_singular() {
        return false;
    }

    public static function format_child_data($artefact, $pluginname) {
        $a = new StdClass;
        $a->id         = $artefact->id;
        $a->isartefact = true;
        $a->title      = '';
        $a->text       = get_string($artefact->artefacttype, 'artefact.resume'); // $artefact->title;
        $a->container  = (bool) $artefact->container;
        $a->parent     = $artefact->id;
        return $a;
    }

    public static function get_links($id) {
        // @todo Catalyst IT Ltd
    }

    /**
     * Default render method for resume fields - show their description
     */
    public function render_self($options) {
        return array('html' => clean_html($this->description));
    }

    /**
     * Overrides the default commit to make sure that any 'entireresume' blocks
     * in views the user have know about this artefact - but only if necessary.
     * Goals and skills are not in the entireresume block
     *
     * @param boolean $updateresumeblocks Whether to update any resume blockinstances
     */
    public function commit() {
        parent::commit();

        if ($blockinstances = get_records_sql_array('
            SELECT id, "view", configdata
            FROM {block_instance}
            WHERE blocktype = \'entireresume\'
            AND "view" IN (
                SELECT id
                FROM {view}
                WHERE "owner" = ?)', array($this->owner))) {
            foreach ($blockinstances as $blockinstance) {
                $whereobject = (object)array(
                    'view' => $blockinstance->view,
                    'artefact' => $this->get('id'),
                    'block' => $blockinstance->id,
                );
                ensure_record_exists('view_artefact', $whereobject, $whereobject);
            }
        }
    }

    public function get_license_artefact() {
        if ($this->get_artefact_type() == 'personalinformation')
            return $this;

        $pi = get_record('artefact',
                         'artefacttype', 'personalinformation',
                         'owner', $this->owner);
        if (!$pi)
            return null;

        require_once(get_config('docroot') . 'artefact/lib.php');
        return artefact_instance_from_id($pi->id);
    }


    public function render_license($options, &$smarty) {
        if (!empty($options['details']) and get_config('licensemetadata')) {
            $smarty->assign('license', render_license($this->get_license_artefact()));
        }
        else {
            $smarty->assign('license', false);
        }
    }

    /**
     * Render the import entry request for resume fields
     */
    public static function render_import_entry_request($entry_content) {
        return clean_html($entry_content['description']);
    }
}

class ArtefactTypeCoverletter extends ArtefactTypeResume {

    public static function is_singular() {
        return true;
    }

    public function __construct($id=0, $data=array()) {
        if (empty($id)) {
            $data['title'] = get_string($this->get_artefact_type(), 'artefact.resume');
        }
        parent::__construct($id, $data);
    }

}

class ArtefactTypeInterest extends ArtefactTypeResume {

    public static function is_singular() {
        return true;
    }

}

class ArtefactTypeContactinformation extends ArtefactTypeResume {

    public function render_self($options) {
        $smarty = smarty_core();
        $fields = ArtefactTypeContactinformation::get_profile_fields();
        foreach ($fields as $f) {
            try {
                $$f = artefact_instance_from_type($f, $this->get('owner'));
                $rendered = $$f->render_self(array());
                $smarty->assign($f, $rendered['html']);
                $smarty->assign('hascontent', true);
            }
            catch (Exception $e) { }
        }

        $this->render_license($options, $smarty);

        return array('html' => $smarty->fetch('artefact:resume:fragments/contactinformation.tpl'));
    }

    public static function is_singular() {
        return true;
    }

    public static function setup_new($userid) {
        try {
            return artefact_instance_from_type('contactinformation', $userid);
        } catch (ArtefactNotFoundException $e) {
            $artefact = new ArtefactTypeContactinformation(null, array(
                'owner' => $userid,
                'title' => get_string('contactinformation', 'artefact.resume')
            ));
            $artefact->commit();
        }
        return $artefact;
    }

    public static function get_profile_fields() {
        static $fields = array(
            'address',
            'town',
            'city',
            'country',
            'faxnumber',
            'businessnumber',
            'homenumber',
            'mobilenumber'
        );
        return $fields;
    }

    public static function is_allowed_in_progressbar() {
        return false;
    }
}

class ArtefactTypePersonalinformation extends ArtefactTypeResume {

    protected $composites;

    public function __construct($id=0, $data=null) {
        if (empty($id)) {
            $data['title'] = get_string('personalinformation', 'artefact.resume');
        }
        parent::__construct($id, $data);
        $this->composites = ArtefactTypePersonalinformation::get_composite_fields();
        if (!empty($id)) {
            $this->composites = (array)get_record('artefact_resume_personal_information', 'artefact', $id,
                null, null, null, null, '*, ' . db_format_tsfield('dateofbirth'));
        }
    }

    public function set_composite($field, $value) {
        if (!array_key_exists($field, $this->composites)) {
            throw new InvalidArgumentException("Tried to set a non existant composite, $field");
        }
        if ($this->composites[$field] == $value) {
            return true;
        }
        // only set it to dirty if it's changed
        $this->dirty = true;
        $this->mtime = time();
        $this->composites[$field] = $value;
    }

    public function get_composite($field) {
        return $this->composites[$field];
    }

    public function commit() {
        if (empty($this->dirty)) {
            return true;
        }

        db_begin();

        $data = new StdClass;
        $have_composites = false;
        foreach ($this->composites as $field => $value) {
            if ($field != 'artefact' && !empty($value)) {
                $have_composites = true;
            }
            if ($field == 'dateofbirth' && !empty($value)) {
                $value = db_format_timestamp($value);
            }
            if ($field == 'gender' && $value=='') {
                $value = null;
            }
            $data->{$field} = $value;
        }
        if (!$have_composites) {
            if (!empty($this->id)) {
                // need to delete empty personal information
                $this->delete();
            }
            db_commit();
            return true;
        }
        $inserting = empty($this->id);
        parent::commit();
        $data->artefact = $this->id;
        if ($inserting) {
            insert_record('artefact_resume_personal_information', $data);
        }
        else {
            update_record('artefact_resume_personal_information', $data, 'artefact');
        }

        db_commit();
    }

    public static function get_composite_fields() {
        static $composites = array(
            'dateofbirth' => null,
            'placeofbirth' => null,
            'citizenship' => null,
            'visastatus' => null,
            'gender' => null,
            'maritalstatus' => null,
        );
        return $composites;
    }

    public static function is_singular() {
        return true;
    }

    public static function render_fields(ArtefactTypePersonalInformation $a=null, $options=array(), $values=null) {
        $smarty = smarty_core();
        $fields = array();
        foreach (array_keys(ArtefactTypePersonalInformation::get_composite_fields()) as $field) {
            if ($values && isset($values[$field])) {
                $value = $values[$field];
                // TODO: Make this be a call to a subclass instead of a hard-coded listing
                // of special behaviors for particular fields
                if ($field == 'dateofbirth') {
                    if (empty($value)) {
                        $value = '';
                    }
                    else {
                        $value = strtotime($value);
                    }
                }
            }
            else if ($a) {
                $value = $a->get_composite($field);
            }
            else {
                continue;
            }
            if ($field == 'gender' && !empty($value)) {
                $value = get_string($value, 'artefact.resume');
            }
            if ($field == 'dateofbirth' && !empty($value)) {
                $value = format_date($value+3600, 'strftimedate');
            }
            $fields[get_string($field, 'artefact.resume')] = $value;
        }
        $smarty->assign('fields', $fields);
        if ($a) {
            $a->render_license($options, $smarty);
        }
        return $smarty->fetch('artefact:resume:fragments/personalinformation.tpl');
    }

    public function render_self($options) {
        return array('html' => self::render_fields($this, $options), 'javascript' => '');
    }

    public static function render_import_entry_request($entry_content) {
        return self::render_fields(null, array(), $entry_content);
    }

    public function delete() {
        db_begin();

        delete_records('artefact_resume_personal_information', 'artefact', $this->id);
        parent::delete();

        db_commit();
    }

    public static function bulk_delete($artefactids) {
        if (empty($artefactids)) {
            return;
        }

        $idstr = join(',', array_map('intval', $artefactids));

        db_begin();
        delete_records_select('artefact_resume_personal_information', 'artefact IN (' . $idstr . ')');
        parent::bulk_delete($artefactids);
        db_commit();
    }

    /**
     * returns duplicated artefacts which have the same values of the following fields:
     *  - owner
     *  - type
     *  - content which has:
     *      - dateofbirth
     *      - placeofbirth
     *      - citizenship
     *      - visastatus
     *      - gender
     *      - maritalstatus
     *
     * @param array $values
     */
    public static function get_duplicated_artefacts(array $values) {
        $fields = array('dateofbirth', 'placeofbirth', 'citizenship', 'visastatus', 'gender', 'maritalstatus');
        $where = array();
        $wherevalues = array($values['owner'], $values['type']);
        $wherestr = 'WHERE a.owner = ? AND a.artefacttype = ?';
        $contentvalues = $values['content'];
        foreach ($fields as $f) {
            if (!isset($contentvalues[$f])) {
                $wherestr .= ' AND ar.' . $f . ' IS NULL';
            }
            if (!empty($contentvalues[$f])) {
                $where[] = "ar.$f = ?";
                $wherevalues[] = $contentvalues[$f];
            }
        }
        $wherestr .= (!empty($where) ? ' AND ' . join(' AND ', $where) : '');
        return get_column_sql('
            SELECT DISTINCT a.id
            FROM {artefact} AS a
            INNER JOIN {artefact_resume_personal_information} AS ar
            ON a.id = ar.artefact
            ' . $wherestr, $wherevalues
        );
    }
}

/**
 * Helper interface to hold ArtefactTypeResumeComposite's abstract static methods
 */
interface IArtefactTypeResumeComposite {
    /**
    * This function should return a snippet of javascript
    * to be plugged into a table renderer instantiation
    * it comprises the cell function definition
    */
    public static function get_tablerenderer_js();

    public static function get_tablerenderer_title_js_string();

    public static function get_tablerenderer_body_js_string();

    /**
    * This function should return an array suitable to
    * put into the 'elements' part of a pieform array
    * to generate a form to add an instance
    */
    public static function get_addform_elements();
}

abstract class ArtefactTypeResumeComposite extends ArtefactTypeResume implements IArtefactTypeResumeComposite {

    public static function is_singular() {
        return true;
    }

    public function can_have_attachments() {
        return true;
    }

    public static function get_composite_artefact_types() {
        return array(
            'employmenthistory',
            'educationhistory',
            'certification',
            'book',
            'membership'
        );
    }

    public static function get_tablerenderer_extra_js_string() {
        return '';
    }

    public static function get_tablerenderer_attachments_js_string(){
        return '';
    }

    /**
     * Can be overridden to format data retrieved from artefact tables for
     * display of the resume artefact by render_self
     */
    public static function format_render_self_data($data) {
        return $data;
    }

    /**
    * This function processes the form for the composite
    * @throws Exception
    */
    public static function process_compositeform(Pieform $form, $values) {
        global $USER;
        $error = self::ensure_composite_value($values, $values['compositetype'], $USER->get('id'));
        if (is_array($error)) {
            $form->reply(PIEFORM_ERR, array('message' => $error['message']));
            if (isset($error['goto'])) {
                redirect($error['goto']);
            }
        }
    }

    /**
     * Ensures that the given value for the given composite is present
     * TODO: expand on these docs.
     * @param unknown_type $values
     * @param unknown_type $compositetype
     * @param unknown_type $owner
     * @return int If successful, the ID of the composite artefact
     * @throws SystemException
     */
    public static function ensure_composite_value($values, $compositetype, $owner) {
        global $USER;
        if (!in_array($compositetype, self::get_composite_artefact_types())) {
            throw new SystemException("ensure_composite_value called with invalid composite type");
        }
        try {
            $a = artefact_instance_from_type($compositetype, $owner);
            $a->set('mtime', time());
        }
        catch (Exception $e) {
            $classname = generate_artefact_class_name($compositetype);
            $a = new $classname(0, array(
                'owner' => $owner,
                'title' => get_string($compositetype, 'artefact.resume'),
                )
            );
        }

        $a->commit();

        $values['artefact'] = $a->get('id');

        $table = 'artefact_resume_' . $compositetype;
        if (!empty($values['id'])) {
            $itemid = $values['id'];
            update_record($table, (object)$values, 'id');
        }
        else {
            if (isset($values['displayorder'])) {
                $values['displayorder'] = intval($values['displayorder']);
            }
            else {
                $max = get_field($table, 'MAX(displayorder)', 'artefact', $values['artefact']);
                $values['displayorder'] = is_numeric($max) ? $max + 1 : 0;
            }
            $itemid = insert_record($table, (object)$values, 'id', true);
        }

        // If there are any attachments, attach them to your Resume...
        if ($compositetype == 'educationhistory' || $compositetype == 'employmenthistory') {
            $goto = get_config('wwwroot') . 'artefact/resume/employment.php';
        }
        else {
            $goto = get_config('wwwroot') . 'artefact/resume/achievements.php';
        }

        // Attachments via 'files' pieform element
        // This happens when adding new resume composite...
        if (array_key_exists('attachments', $values)) {
            require_once(get_config('libroot') . 'uploadmanager.php');
            safe_require('artefact', 'file');

            $folderid = null;
            $attachment = (object) array(
                'owner'         => $owner,
                'group'         => null, // Group
                'institution'   => null, // Institution
                'author'        => $owner,
                'allowcomments' => 0,
                'parent'        => $folderid,
                'description'   => null,
            );

            foreach ($values['attachments'] as $filesindex) {
                $originalname = $_FILES[$filesindex]['name'];
                $attachment->title = ArtefactTypeFileBase::get_new_file_title(
                    $originalname,
                    $folderid,
                    $owner,
                    null, // Group
                    null  // Institution
                );

                try {
                    $fileid = ArtefactTypeFile::save_uploaded_file($filesindex, $attachment);
                }
                catch (QuotaExceededException $e) {
                    return array('message'=>$e->getMessage(), 'goto'=>$goto);
                }
                catch (UploadException $e) {
                    return array('message'=>$e->getMessage(), 'goto'=>$goto);
                }

                $a->attach($fileid, $itemid);
            }
        }

        // Attachments via 'filebrowser' pieform element
        // This happens when editing resume composite...
        if (array_key_exists('filebrowser', $values)) {
            $old = $a->attachment_id_list_with_item($itemid);
            $new = is_array($values['filebrowser']) ? $values['filebrowser'] : array();
            // only allow the attaching of files that exist and are editable by user
            foreach ($new as $key => $fileid) {
                $file = artefact_instance_from_id($fileid);
                if (!($file instanceof ArtefactTypeFile) || !$USER->can_publish_artefact($file)) {
                    unset($new[$key]);
                }
            }
            if (!empty($new) || !empty($old)) {
                foreach ($old as $o) {
                    if (!in_array($o, $new)) {
                        try {
                            $a->detach($o, $itemid);
                        }
                        catch (ArtefactNotFoundException $e) {}
                    }
                }
                $is_error = false;
                foreach ($new as $n) {
                    if (!in_array($n, $old)) {
                        // check the new item is not already attached to the
                        // artefact under a different $itemid
                        if (record_exists('artefact_attachment', 'artefact', $a->get('id'), 'attachment', $n)) {
                            $artefactfile = artefact_instance_from_id($n);
                            $is_error[] = $artefactfile->get('title');
                        }
                        else {
                            try {
                                $a->attach($n, $itemid);
                            }
                            catch (ArtefactNotFoundException $e) {}
                        }
                    }
                }
                if (!empty($is_error)) {
                    if (sizeof($is_error) > 1) {
                        $error = get_string('duplicateattachments', 'artefact.resume', implode('\', \'', $is_error));
                    }
                    else {
                        $error = get_string('duplicateattachment', 'artefact.resume', implode(', ', $is_error));
                    }
                    return array('message'=>$error);
                }
            }
        }
        return $a->id;
    }

    public function delete() {
        $table = $this->get_other_table_name();
        db_begin();

        delete_records($table, 'artefact', $this->id);
        parent::delete();

        db_commit();
    }

    public static function bulk_delete_composite($artefactids, $compositetype) {
        $table = 'artefact_resume_' . $compositetype;
        if (empty($artefactids)) {
            return;
        }

        $idstr = join(',', array_map('intval', $artefactids));

        db_begin();
        delete_records_select($table, 'artefact IN (' . $idstr . ')');
        parent::bulk_delete($artefactids);
        db_commit();
    }

    /**
    * Takes a pieform that's been set up by all the
    * subclass get_addform_elements functions
    * and puts the default values in (and hidden id field)
    * ready to be an edit form
    *
    * @param $form pieform structure (before calling pieform() on it
    * passed by _reference_
    */
    public static function populate_form(&$form, $id, $type) {
        if (!$composite = get_record('artefact_resume_' . $type, 'id', $id)) {
            throw new InvalidArgumentException("Couldn't find composite of type $type with id $id");
        }
        $datetypes = array('date', 'startdate', 'enddate');
        foreach ($form['elements'] as $k => $element) {
            if ($k == 'submit' || $k == 'submitform' ||$k == 'compositetype') {
                continue;
            }
            if (isset($composite->{$k})) {
                $form['elements'][$k]['defaultvalue'] = $composite->{$k};
            }
        }
        $form['elements']['id'] = array(
            'type' => 'hidden',
            'value' => $id,
        );
        $form['elements']['artefact'] = array(
            'type' => 'hidden',
            'value' => $composite->artefact,
        );
    }


    /**
    * call the parent constructor
    * and then load up the stuff from the supporting table
    */
    public function __construct($id=0, $data=array()) {
        if (empty($id)) {
            $data['container'] = 0;
            $data['title'] = get_string($this->get_artefact_type(), 'artefact.resume');
        }
        parent::__construct($id, $data);
    }

    /**
    * returns the name of the supporting table
    */
    public function get_other_table_name() {
        return 'artefact_resume_' . $this->get_artefact_type();
    }

    public function render_self($options) {
        global $USER;
        $suffix = '_' . substr(md5(microtime()), 0, 4);
        $attachmessage = get_string('fileattachmessage', 'artefact.resume',
                         get_string('fileattachdirname', 'artefact.resume'));
        $smarty = smarty_core();
        $smarty->assign('user', $USER->get('id'));
        $smarty->assign('hidetitle', true);
        $smarty->assign('suffix', $suffix);
        $smarty->assign('attachmessage', $attachmessage);
        $type = $this->get('artefacttype');
        $othertable = 'artefact_resume_' . $type;
        $owner = $USER->get('id');

        $sql = 'SELECT ar.*, a.owner
            FROM {artefact} a
            JOIN {' . $othertable . '} ar ON ar.artefact = a.id
            WHERE a.owner = ? AND a.artefacttype = ?
            ORDER BY ar.displayorder';

        if (!empty($options['viewid'])) {
            require_once('view.php');
            $smarty->assign('viewid', $options['viewid']);
            $v = new View($options['viewid']);
            $owner = $v->get('owner');
        }

        if (!$data = get_records_sql_array($sql, array($owner, $type))) {
            $data = array();
        }

        // Give the artefact type a chance to format the data how it sees fit
        $data = call_static_method(generate_artefact_class_name($type), 'format_render_self_data', $data);

        // Add artefact attachments it there are any
        $datawithattachments = array();
        foreach ($data as $record) {
            // Cannot use $this->get_attachments() as it would return
            // all the attachments for specified resume composite.
            // Instead we want only attachments for single item of the
            // specified resume composite...
            $sql = 'SELECT a.title, a.id, af.size
                    FROM {artefact} a
                    JOIN {artefact_file_files} af ON af.artefact = a.id
                    JOIN {artefact_attachment} at ON at.attachment = a.id
                    WHERE at.artefact = ? AND at.item = ?
                    ORDER BY a.title';
            $attachments = get_records_sql_array($sql, array($record->artefact, $record->id));
            if ($attachments) {
                foreach ($attachments as &$attachment) {
                    $f = artefact_instance_from_id($attachment->id);
                    $attachment->size = $f->describe_size();
                    $attachment->iconpath = $f->get_icon(array('id' => $attachment->id, 'viewid' => isset($options['viewid']) ? $options['viewid'] : 0));
                    $attachment->viewpath = get_config('wwwroot') . 'artefact/artefact.php?artefact=' . $attachment->id . '&view=' . (isset($options['viewid']) ? $options['viewid'] : 0);
                    $attachment->downloadpath = get_config('wwwroot') . 'artefact/file/download.php?file=' . $attachment->id;
                    $attachment->description = $f->description;
                }
            }
            $record->attachments = $attachments;
            if (!is_array($attachments)) {
                $record->clipcount = 0;
            }
            else {
                $record->clipcount = count($attachments);
            }
            $datawithattachments[] = $record;
        }

        $smarty->assign('rows', $datawithattachments);
        $this->render_license($options, $smarty);

        $content = array(
            'html'         => $smarty->fetch('artefact:resume:fragments/' . $type . '.tpl'),
            'javascript'   => $this->get_showhide_composite_js()
        );
        return $content;
    }

    public static function render_import_entry_request($entry_content, $renderfields) {
        $smarty = smarty_core();
        $fields = array();
        foreach ($renderfields as $field) {
            $fields[get_string($field, 'artefact.resume')] = isset($entry_content[$field]) ? $entry_content[$field] : '';
        }
        $smarty->assign('fields', $fields);
        return $smarty->fetch('artefact:resume:import/resumecompositefields.tpl');
    }

    public static function get_js(array $compositetypes) {
        $js = self::get_common_js();
        foreach ($compositetypes as $compositetype) {
            $js .= call_static_method(
                generate_artefact_class_name($compositetype),
                'get_artefacttype_js',
                $compositetype
            );
        }
        return $js;
    }

    public static function get_common_js() {
        $cancelstr = json_encode(get_string('cancel'));
        $addstr = json_encode(get_string('add'));
        $confirmdelstr = get_string('compositedeleteconfirm', 'artefact.resume');
        $js = <<<EOF
var tableRenderers = {};

function toggleCompositeForm(type) {
    var elem = \$j('#' + type + 'form');
    if (elem.hasClass('hidden')) {
        elem.removeClass('hidden');
        elem.find(':input').first().focus();
        \$j('#add' + type + 'button').html({$cancelstr});
    }
    else {
        \$j('#add' + type + 'button').html({$addstr});
        elem.addClass('hidden');
    }
}

function compositeSaveCallback(form, data) {
    key = form.id.substr(3);
    tableRenderers[key].doupdate();
    toggleCompositeForm(key);
    // Can't reset() the form here, because its values are what were just submitted,
    // thanks to pieforms
    forEach(form.elements, function(element) {
        if (hasElementClass(element, 'text') || hasElementClass(element, 'textarea')) {
            element.value = '';
        }
    });
    formSuccess(form, data);
}

function deleteComposite(type, id, artefact) {
    if (confirm('{$confirmdelstr}')) {
        sendjsonrequest('compositedelete.json.php',
            {'id': id, 'artefact': artefact},
            'GET',
            function(data) {
                tableRenderers[type].doupdate();
            },
            function() {
                // @todo error
            }
        );
    }
    return false;
}

function moveComposite(type, id, artefact, direction) {
    sendjsonrequest('compositemove.json.php',
        {'id': id, 'artefact': artefact, 'direction':direction},
        'GET',
        function(data) {
            tableRenderers[type].doupdate();
        },
        function() {
            // @todo error
        }
    );
    return false;
}
EOF;
        $js .= self::get_showhide_composite_js();
        return $js;
    }

    static function get_tablerenderer_title_js($titlestring, $extrastring, $bodystring, $attachstring) {
        return "
                function (r, d) {
                    if (!{$bodystring} && !{$attachstring}) {
                        return TD(null, STRONG(null, {$titlestring}), DIV(null, {$extrastring}));
                    }
                    var link = A({'class': 'toggle textonly', 'href': ''}, {$titlestring});
                    connect(link, 'onclick', function (e) {
                        e.stop();
                        return showhideComposite(r, {$bodystring}, {$attachstring});
                    });
                    var extra = DIV(null, {$extrastring});
                    return TD({'id': 'composite-' + r.artefact + '-' + r.id}, DIV({'class': 'expandable-head'}, link, extra));
                },
                ";
    }

    static function get_showhide_composite_js() {
        return "
            function showhideComposite(r, content, attachments) {
                // get the reference for the title we just clicked on
                var titleTD = $('composite-' + r.artefact + '-' + r.id);
                var bodyNode = $('composite-body-' + r.artefact +  '-' + r.id);
                if (bodyNode) {
                    if (hasElementClass(bodyNode, 'hidden')) {
                        removeElementClass(bodyNode, 'hidden');
                    }
                    else {
                        addElementClass(bodyNode, 'hidden');
                    }
                    return false;
                }
                if (attachments) {
                    var newNode = DIV({'id': 'composite-body-' + r.artefact + '-' + r.id},
                        DIV({'class':'compositedesc'}, content), attachments);
                }
                else {
                    var newNode = DIV({'id': 'composite-body-' + r.artefact + '-' + r.id},
                        DIV({'class':'compositedesc'}, content));
                }
                insertSiblingNodesAfter(getFirstElementByTagAndClassName(null, 'expandable-head', titleTD), newNode);
                setupExpanders(jQuery(newNode));
            }
        ";
    }

    static function get_artefacttype_js($compositetype) {
        global $THEME;
        $titlestring = call_static_method(generate_artefact_class_name($compositetype), 'get_tablerenderer_title_js_string');
        $editstr = json_encode(get_string('edit'));
        $delstr = json_encode(get_string('delete'));
        $editjsstr = json_encode(get_string('editspecific', 'mahara', '%s')) . ".replace('%s', {$titlestring})";
        $deljsstr = json_encode(get_string('deletespecific', 'mahara', '%s')) . ".replace('%s', {$titlestring})";

        $imagemoveblockup   = json_encode($THEME->get_image_url('btn_moveup'));
        $imagemoveblockdown = json_encode($THEME->get_image_url('btn_movedown'));
        $upstr = get_string('moveup', 'artefact.resume');
        $downstr = get_string('movedown', 'artefact.resume');

        $js = call_static_method(generate_artefact_class_name($compositetype), 'get_composite_js');

        $js .= <<<EOF
tableRenderers.{$compositetype} = new TableRenderer(
    '{$compositetype}list',
    'composite.json.php',
    [
EOF;

        $js .= <<<EOF

        function (r, d) {
            var buttons = [];
            if (r._rownumber > 1) {
                var up = A({'href': ''}, IMG({'src': {$imagemoveblockup}, 'alt':'{$upstr}'}));
                connect(up, 'onclick', function (e) {
                    e.stop();
                    return moveComposite(d.type, r.id, r.artefact, 'up');
                });
                buttons.push(up);
            }
            if (!r._last) {
                var down = A({'href': '', 'class':'movedown'}, IMG({'src': {$imagemoveblockdown}, 'alt':'{$downstr}'}));
                connect(down, 'onclick', function (e) {
                    e.stop();
                    return moveComposite(d.type, r.id, r.artefact, 'down');
                });
                buttons.push(' ');
                buttons.push(down);
            }
            return TD({'class':'movebuttons'}, buttons);
        },
EOF;

        $js .= call_static_method(generate_artefact_class_name($compositetype), 'get_tablerenderer_js');

        $js .= <<<EOF
        function (r, d) {
            var editlink = A({'href': 'editcomposite.php?id=' + r.id + '&artefact=' + r.artefact, 'title': {$editstr}}, IMG({'src': config.theme['images/btn_edit.png'], 'alt':{$editjsstr}}));
            var dellink = A({'href': '', 'title': {$delstr}}, IMG({'src': config.theme['images/btn_deleteremove.png'], 'alt': {$deljsstr}}));
            connect(dellink, 'onclick', function (e) {
                e.stop();
                return deleteComposite(d.type, r.id, r.artefact);
            });
            return TD({'class':'btns2'}, null, editlink, ' ', dellink);
        }
    ]
);

tableRenderers.{$compositetype}.type = '{$compositetype}';
tableRenderers.{$compositetype}.statevars.push('type');
tableRenderers.{$compositetype}.emptycontent = '';
tableRenderers.{$compositetype}.updateOnLoad();

EOF;
        return $js;
    }

    static function get_composite_js() {
        $attachmentsstr = json_encode(get_string('Attachments', 'artefact.resume'));
        $downloadstr = json_encode(get_string('Download', 'artefact.file'));
        return <<<EOF
function formatSize(size) {
    size = parseInt(size, 10);
    if (size < 1024) {
        return size <= 0 ? '0' : size.toFixed(1).replace(/\.0$/, '') + 'b';
    }
    if (size < 1048576) {
        return (size / 1024).toFixed(1).replace(/\.0$/, '') + 'K';
    }
    return (size / 1048576).toFixed(1).replace(/\.0$/, '') + 'M';
}
function listAttachments(attachments) {
    if (attachments.length > 0) {
        var togglelink = A({'class': 'toggle', 'href': '#'}, {$attachmentsstr});
        var thead = THEAD({'class': 'expandable-head'}, TR(null, TH(null, togglelink)));
        var tbody = TBODY({'class': 'expandable-body'});
        for (var i=0; i < attachments.length; i++) {
            var item = attachments[i];
            var href = self.config.wwwroot + 'artefact/file/download.php?file=' + attachments[i].id;
            var link = A({'href': href}, {$downloadstr});
            appendChildNodes(tbody, TR(null, TD(null, item.title + ' (' + formatSize(item.size) + ') - ', STRONG(null, link))));
        }
        return TABLE({'class': 'cb attachments fullwidth'}, thead, tbody);
    }
    else {
        // No attachments
        return '';
    }
}
EOF;
    }

    static function get_forms(array $compositetypes) {
        require_once(get_config('libroot') . 'pieforms/pieform.php');
        $compositeforms = array();
        foreach ($compositetypes as $compositetype) {
            $elements = call_static_method(generate_artefact_class_name($compositetype), 'get_addform_elements');
            $elements['submit'] = array(
                'type' => 'submit',
                'value' => get_string('save'),
            );
            $elements['compositetype'] = array(
                'type' => 'hidden',
                'value' => $compositetype,
            );
            $cform = array(
                'name' => 'add' . $compositetype,
                'plugintype' => 'artefact',
                'pluginname' => 'resume',
                'elements' => $elements,
                'jsform' => true,
                'successcallback' => 'compositeform_submit',
                'jssuccesscallback' => 'compositeSaveCallback',
            );
            $compositeforms[$compositetype] = pieform($cform);
        }
        return $compositeforms;
    }

}

class ArtefactTypeEmploymenthistory extends ArtefactTypeResumeComposite {

    protected $startdate;
    protected $enddate;
    protected $employer;

    public static function get_tablerenderer_js() {
        return ArtefactTypeResumeComposite::get_tablerenderer_title_js(
                    self::get_tablerenderer_title_js_string(),
                    self::get_tablerenderer_date_js_string(),
                    self::get_tablerenderer_body_js_string(),
                    self::get_tablerenderer_attachments_js_string()
                ) . ",
                function (r, d) {
                    return TD({'style':'text-align:center'}, r.clipcount);
                },
        ";
    }

    public static function get_tablerenderer_title_js_string() {
        return " r.jobtitle + ': ' + r.employer";
    }

    public static function get_tablerenderer_date_js_string() {
        return " r.startdate + (r.enddate ? ' - ' + r.enddate : '')";
    }

    public static function get_tablerenderer_body_js_string() {
        return " r.positiondescription";
    }

    public static function get_tablerenderer_attachments_js_string() {
        return " listAttachments(r.attachments)";
    }

    public static function get_addform_elements() {
        return array(
            'startdate' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('startdate', 'artefact.resume'),
                'size' => 20,
                'help' => true,
            ),
            'enddate' => array(
                'type' => 'text',
                'title' => get_string('enddate', 'artefact.resume'),
                'size' => 20,
            ),
            'employer' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('employer', 'artefact.resume'),
                'size' => 50,
            ),
            'employeraddress' => array(
                'type' => 'text',
                'title' => get_string('employeraddress', 'artefact.resume'),
                'size' => 50,
            ),
            'jobtitle' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('jobtitle', 'artefact.resume'),
                'size' => 50,
            ),
            'positiondescription' => array(
                'type' => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'title' =>  get_string('positiondescription', 'artefact.resume'),
            ),
            'attachments' => array(
                'type'         => 'files',
                'title'        => get_string('attachfile', 'artefact.resume'),
                'defaultvalue' => array(),
                'maxfilesize'  => get_max_upload_size(false),
            ),
        );
    }

    public static function bulk_delete($artefactids) {
        ArtefactTypeResumeComposite::bulk_delete_composite($artefactids, 'employmenthistory');
    }

    /**
     * returns the employmenthistory artefacts which have the same values of the following fields:
     *  - owner
     *  - type
     *  - content which has
     *      - startdate
     *      - enddate
     *      - employer
     *      - jobtitle
     *      - positiondescription
     *
     * @param array $values
     */
    public static function get_duplicated_artefacts(array $values) {
        $fields = array('startdate', 'enddate', 'employer', 'jobtitle', 'positiondescription');
        $where = array();
        $wherevalues = array($values['owner'], $values['type']);
        $contentvalues = $values['content'];
        foreach ($fields as $f) {
            if (!isset($contentvalues[$f])) {
                return array();
            }
            if (!empty($contentvalues[$f])) {
                $where[] = "ar.$f = ?";
                $wherevalues[] = $contentvalues[$f];
            }
        }
        $wherestr = 'WHERE a.owner = ? AND a.artefacttype = ?' . (!empty($where) ? ' AND ' . join(' AND ', $where) : '');
        return get_column_sql('
            SELECT DISTINCT a.id
            FROM {artefact} AS a
            INNER JOIN {artefact_resume_employmenthistory} AS ar
            ON a.id = ar.artefact
            ' . $wherestr, $wherevalues
        );
    }

    public static function render_import_entry_request($entry_content) {
        return parent::render_import_entry_request($entry_content, array_keys(self::get_addform_elements()));
    }

}

class ArtefactTypeEducationhistory extends ArtefactTypeResumeComposite {

    protected $startdate;
    protected $enddate;
    protected $qualtype;
    protected $institution;

    public static function get_tablerenderer_js() {

        return ArtefactTypeResumeComposite::get_tablerenderer_title_js(
                    self::get_tablerenderer_title_js_string(),
                    self::get_tablerenderer_date_js_string(),
                    self::get_tablerenderer_body_js_string(),
                    self::get_tablerenderer_attachments_js_string()
                ) . ",
                function (r, d) {
                    return TD({'style':'text-align:center'}, r.clipcount);
                },
        ";
    }

    public static function get_tablerenderer_title_js_string() {
        return " formatQualification(r.qualname, r.qualtype, r.institution)";
    }

    public static function get_tablerenderer_date_js_string() {
        return " r.startdate + (r.enddate ? ' - ' + r.enddate : '')";
    }

    public static function format_render_self_data($data) {
        $at = get_string('at');
        foreach ($data as &$row) {
            $row->qualification = '';
            if (strlen($row->qualname) && strlen($row->qualtype)) {
                $row->qualification = $row->qualname. ' (' . $row->qualtype . ') ' . $at . ' ';
            }
            else if (strlen($row->qualtype)) {
                $row->qualification = $row->qualtype . ' ' . $at . ' ';
            }
            else if (strlen($row->qualname)) {
                $row->qualification = $row->qualname . ' ' . $at . ' ';
            }
            $row->qualification .= $row->institution;
        }
        return $data;
    }

    public static function get_tablerenderer_body_js_string() {
        return " r.qualdescription";
    }

    public static function get_tablerenderer_attachments_js_string() {
        return " listAttachments(r.attachments)";
    }

    public static function get_addform_elements() {
        global $USER;
        return array(
            'startdate' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('startdate', 'artefact.resume'),
                'size' => 20,
                'help' => true,
            ),
            'enddate' => array(
                'type' => 'text',
                'title' => get_string('enddate', 'artefact.resume'),
                'size' => 20,
            ),
            'institution' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('institution', 'artefact.resume'),
                'size' => 50,
            ),
            'institutionaddress' => array(
                'type' => 'text',
                'title' => get_string('institutionaddress', 'artefact.resume'),
                'size' => 50,
            ),
            'qualtype' => array(
                'type' => 'text',
                'title' => get_string('qualtype', 'artefact.resume'),
                'size' => 50,
            ),
            'qualname' => array(
                'type' => 'text',
                'title' => get_string('qualname', 'artefact.resume'),
                'size' => 50,
            ),
            'qualdescription' => array(
                'type' => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'title' => get_string('qualdescription', 'artefact.resume'),
            ),
            'attachments' => array(
                'type'         => 'files',
                'title'        => get_string('attachfile', 'artefact.resume'),
                'defaultvalue' => array(),
                'maxfilesize'  => get_max_upload_size(false),
            ),
            'attachments' => array(
                'type'         => 'files',
                'title'        => get_string('attachfile', 'artefact.resume'),
                'defaultvalue' => array(),
                'maxfilesize'  => get_max_upload_size(false),
            ),
        );
    }

    static function get_composite_js() {
        $at = get_string('at');
        return <<<EOF
function formatQualification(name, type, institution) {
    var qual = '';
    if (name && type) {
        qual = name + ' (' + type + ') {$at} ';
    }
    else if (type) {
        qual = type + ' {$at} ';
    }
    else if (name) {
        qual = name + ' {$at} ';
    }
    qual += institution;
    return qual;
}
EOF;
    }

    public static function bulk_delete($artefactids) {
        ArtefactTypeResumeComposite::bulk_delete_composite($artefactids, 'educationhistory');
    }

    /**
     * returns the artefacts which have the same values of the following fields:
     *  - owner
     *  - type == 'educationhistory'
     *  - content, which has
     *      - startdate
     *      - enddate
     *      - institution
     *      - qualtype
     *      - qualname
     *
     * @param array $values
     */
    public static function get_duplicated_artefacts(array $values) {
        $fields = array('startdate', 'enddate', 'institution', 'qualtype', 'qualname');
        $where = array();
        $wherevalues = array($values['owner'], $values['type']);
        $contentvalues = $values['content'];
        foreach ($fields as $f) {
            if (!isset($contentvalues[$f])) {
                return array();
            }
            if (!empty($contentvalues[$f])) {
                $where[] = "ar.$f = ?";
                $wherevalues[] = $contentvalues[$f];
            }
        }
        $wherestr = 'WHERE a.owner = ? AND a.artefacttype = ?' . (!empty($where) ? ' AND ' . join(' AND ', $where) : '');
        return get_column_sql('
            SELECT DISTINCT a.id
            FROM {artefact} AS a
                INNER JOIN {artefact_resume_educationhistory} AS ar
                    ON a.id = ar.artefact
            ' . $wherestr, $wherevalues
        );
    }

    public static function render_import_entry_request($entry_content) {
        return parent::render_import_entry_request($entry_content, array_keys(self::get_addform_elements()));
    }

}

class ArtefactTypeCertification extends ArtefactTypeResumeComposite {

    protected $date;

    public static function get_tablerenderer_js() {
        return ArtefactTypeResumeComposite::get_tablerenderer_title_js(
                    self::get_tablerenderer_title_js_string(),
                    self::get_tablerenderer_date_js_string(),
                    self::get_tablerenderer_body_js_string(),
                    self::get_tablerenderer_attachments_js_string()
                ) . ",
                function (r, d) {
                    return TD({'style':'text-align:center'}, r.clipcount);
                },
        ";
    }

    public static function get_tablerenderer_title_js_string() {
        return "r.title";
    }

    public static function get_tablerenderer_date_js_string() {
        return " r.date";
    }

    public static function get_tablerenderer_body_js_string() {
        return "r.description";
    }

    public static function get_tablerenderer_attachments_js_string() {
        return " listAttachments(r.attachments)";
    }

    public static function get_addform_elements() {
        return array(
            'date' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('date', 'artefact.resume'),
                'size' => 20,
                'help' => true,
            ),
            'title' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('title', 'artefact.resume'),
                'size' => 20,
            ),
            'description' => array(
                'type' => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'title' => get_string('description'),
            ),
            'attachments' => array(
                'type'         => 'files',
                'title'        => get_string('attachfile', 'artefact.resume'),
                'defaultvalue' => array(),
                'maxfilesize'  => get_max_upload_size(false),
            ),
        );
    }

    public static function bulk_delete($artefactids) {
        ArtefactTypeResumeComposite::bulk_delete_composite($artefactids, 'certification');
    }

    /**
     * returns certificate artefacts which have the same values of the following fields:
     *  - owner
     *  - type
     *  - content which has:
     *      - date
     *      - title
     *      - description
     *
     * @param array $values
     */
    public static function get_duplicated_artefacts(array $values) {
        $fields = array('date', 'title', 'description');
        $where = array();
        $wherevalues = array($values['owner'], $values['type']);
        $contentvalues = $values['content'];
        foreach ($fields as $f) {
            if (!isset($contentvalues[$f])) {
                return array();
            }
            if (!empty($contentvalues[$f])) {
                $where[] = "ar.$f = ?";
                $wherevalues[] = $contentvalues[$f];
            }
        }
        $wherestr = 'WHERE a.owner = ? AND a.artefacttype = ?' . (!empty($where) ? ' AND ' . join(' AND ', $where) : '');
        return get_column_sql('
            SELECT DISTINCT a.id
            FROM {artefact} AS a
            INNER JOIN {artefact_resume_certification} AS ar
            ON a.id = ar.artefact
            ' . $wherestr, $wherevalues
        );
    }

    public static function render_import_entry_request($entry_content) {
        return parent::render_import_entry_request($entry_content, array_keys(self::get_addform_elements()));
    }

}

class ArtefactTypeBook extends ArtefactTypeResumeComposite {

    protected $date;
    protected $contribution;

    public static function get_tablerenderer_js() {
        return ArtefactTypeResumeComposite::get_tablerenderer_title_js(
                    self::get_tablerenderer_title_js_string(),
                    self::get_tablerenderer_date_js_string(),
                    self::get_tablerenderer_body_js_string(),
                    self::get_tablerenderer_attachments_js_string()
                ) . ",
                function (r, d) {
                    return TD({'style':'text-align:center'}, r.clipcount);
                },
        ";
    }

    public static function get_tablerenderer_title_js_string() {
        return "r.title + ' (' + r.contribution + ')'";
    }

    public static function get_tablerenderer_date_js_string() {
        return " r.date";
    }

    public static function get_tablerenderer_body_js_string() {
        return "DIV(r.description, DIV({'id':'composite-book-url'}, A({'href':r.url, 'target':'_blank'}, r.url)))";
    }

    public static function get_tablerenderer_attachments_js_string() {
        return " listAttachments(r.attachments)";
    }

    public static function get_addform_elements() {
        return array(
            'date' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('date', 'artefact.resume'),
                'help' => true,
                'size' => 20,
            ),
            'title' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('title', 'artefact.resume'),
                'size' => 50,
            ),
            'contribution' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('contribution', 'artefact.resume'),
                'size' => 50,
            ),
            'description' => array(
                'type' => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'title' => get_string('detailsofyourcontribution', 'artefact.resume'),
            ),
            'attachments' => array(
                'type'         => 'files',
                'title'        => get_string('attachfile', 'artefact.resume'),
                'defaultvalue' => array(),
                'maxfilesize'  => get_max_upload_size(false),
            ),
            'url' => array(
                'type' => 'text',
                'title' => get_string('bookurl', 'artefact.resume'),
                'size' => 70,
                'help' => true,
            ),
        );
    }

    public static function bulk_delete($artefactids) {
        ArtefactTypeResumeComposite::bulk_delete_composite($artefactids, 'book');
    }
    /**
     * returns the book artefacts which have the same values of the following fields:
     *  - owner
     *  - type
     *  - content which has:
     *      - date
     *      - title
     *      - contribution
     *
     * @param array $values
     */
    public static function get_duplicated_artefacts(array $values) {
        $fields = array('date', 'title', 'contribution');
        $where = array();
        $wherevalues = array($values['owner'], $values['type']);
        $contentvalues = $values['content'];
        foreach ($fields as $f) {
            if (!isset($contentvalues[$f])) {
                return array();
            }
            if (!empty($contentvalues[$f])) {
                $where[] = "ar.$f = ?";
                $wherevalues[] = $contentvalues[$f];
            }
        }
        $wherestr = 'WHERE a.owner = ? AND a.artefacttype = ?' . (!empty($where) ? ' AND ' . join(' AND ', $where) : '');
        return get_column_sql('
            SELECT DISTINCT a.id
            FROM {artefact} AS a
            INNER JOIN {artefact_resume_book} AS ar
            ON a.id = ar.artefact
            ' . $wherestr, $wherevalues
        );
    }

    public static function render_import_entry_request($entry_content) {
        return parent::render_import_entry_request($entry_content, array_keys(self::get_addform_elements()));
    }

}

class ArtefactTypeMembership extends ArtefactTypeResumeComposite {

    protected $startdate;
    protected $enddate;

    public static function get_tablerenderer_js() {
        return ArtefactTypeResumeComposite::get_tablerenderer_title_js(
                    self::get_tablerenderer_title_js_string(),
                    self::get_tablerenderer_date_js_string(),
                    self::get_tablerenderer_body_js_string(),
                    self::get_tablerenderer_attachments_js_string()
                ) . ",
                function (r, d) {
                    return TD({'style':'text-align:center'}, r.clipcount);
                },
        ";
    }

    public static function get_tablerenderer_title_js_string() {
        return "r.title";
    }

    public static function get_tablerenderer_date_js_string() {
        return " r.startdate + (r.enddate ? ' - ' + r.enddate : '')";
    }

    public static function get_tablerenderer_body_js_string() {
        return "r.description";
    }

    public static function get_tablerenderer_attachments_js_string() {
        return " listAttachments(r.attachments)";
    }

    public static function get_addform_elements() {
        return array(
            'startdate' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('startdate', 'artefact.resume'),
                'help' => true,
                'size' => 20,
            ),
            'enddate' => array(
                'type' => 'text',
                'title' => get_string('enddate', 'artefact.resume'),
                'size' => 20,
            ),
            'title' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('title', 'artefact.resume'),
                'size' => 50,
            ),
            'description' => array(
                'type' => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'title' => get_string('description', 'artefact.resume'),
            ),
            'attachments' => array(
                'type'         => 'files',
                'title'        => get_string('attachfile', 'artefact.resume'),
                'defaultvalue' => array(),
                'maxfilesize'  => get_max_upload_size(false),
            ),
        );
    }

    public static function bulk_delete($artefactids) {
        ArtefactTypeResumeComposite::bulk_delete_composite($artefactids, 'membership');
    }

    /**
     * returns membership artefacts which have the same values of the following fields:
     *  - owner
     *  - type
     *  - content which has:
     *      - startdate
     *      - enddate
     *      - title
     *      - description
     *
     * @param array $values
     */
    public static function get_duplicated_artefacts(array $values) {
        $fields = array('startdate', 'enddate', 'title', 'description');
        $where = array();
        $wherevalues = array($values['owner'], $values['type']);
        $contentvalues = $values['content'];
        foreach ($fields as $f) {
            if (!isset($contentvalues[$f])) {
                return array();
            }
            if (!empty($contentvalues[$f])) {
                $where[] = "ar.$f = ?";
                $wherevalues[] = $contentvalues[$f];
            }
        }
        $wherestr = 'WHERE a.owner = ? AND a.artefacttype = ?' . (!empty($where) ? ' AND ' . join(' AND ', $where) : '');
        return get_column_sql('
            SELECT DISTINCT a.id
            FROM {artefact} AS a
            INNER JOIN {artefact_resume_membership} AS ar
            ON a.id = ar.artefact
            ' . $wherestr, $wherevalues
        );
    }

    public static function render_import_entry_request($entry_content) {
        return parent::render_import_entry_request($entry_content, array_keys(self::get_addform_elements()));
    }

}

class ArtefactTypeResumeGoalAndSkill extends ArtefactTypeResume {

    public static function is_singular() {
        return true;
    }

    public function can_have_attachments() {
        return true;
    }

    public function render_self($options) {
        global $USER;
        $smarty = smarty_core();
        $smarty->assign('description', $this->get('description'));

        $attachments = $this->get_attachments();
        if ($attachments) {
            foreach ($attachments as &$attachment) {
                $f = artefact_instance_from_id($attachment->id);
                $attachment->size = $f->describe_size();
                $attachment->iconpath = $f->get_icon(array('id' => $attachment->id, 'viewid' => isset($options['viewid']) ? $options['viewid'] : 0));
                $attachment->viewpath = get_config('wwwroot') . 'artefact/artefact.php?artefact=' . $attachment->id . '&view=' . (isset($options['viewid']) ? $options['viewid'] : 0);
                $attachment->downloadpath = get_config('wwwroot') . 'artefact/file/download.php?file=' . $attachment->id;
                $attachment->description = $f->description;
            }
            $smarty->assign('attachments', $attachments);
            $smarty->assign('count', count($attachments));
        }

        $result = array(
            'html' => $smarty->fetch('artefact:resume:fragments/goalsandskills.tpl')
        );
        return $result;
    }

    public function get_goals_and_skills($type='') {
        global $USER;
        switch ($type) {
            case 'goals':
                $artefacts = array('personalgoal', 'academicgoal', 'careergoal');
                break;
            case 'skills':
                $artefacts = array('personalskill', 'academicskill', 'workskill');
                break;
            default:
                $artefacts = array('personalgoal', 'academicgoal', 'careergoal',
                                   'personalskill', 'academicskill', 'workskill');
        }

        $data = array();
        foreach ($artefacts as $artefact) {
            $record = get_record('artefact', 'artefacttype', $artefact, 'owner', $USER->get('id'));
            if ($record) {
                $record->exists = 1;
                // Add attachments
                $files = ArtefactType::attachments_from_id_list(array($record->id));
                if ($files) {
                    safe_require('artefact', 'file');
                    foreach ($files as &$file) {
                        $file->icon = call_static_method(generate_artefact_class_name($file->artefacttype), 'get_icon', array('id' => $file->attachment));
                        $record->files[] = $file;
                    }
                    $record->count = count($files);
                }
                else {
                    $record->count = 0;
                }
            }
            else {
                $record = new stdClass();
                $record->artefacttype = $artefact;
                $record->exists = 0;
                $record->count = 0;
            }
            $data[] = $record;
        }
        return $data;
    }

}

class ArtefactTypePersonalgoal extends ArtefactTypeResumeGoalAndSkill { }
class ArtefactTypeAcademicgoal extends ArtefactTypeResumeGoalAndSkill { }
class ArtefactTypeCareergoal extends ArtefactTypeResumeGoalAndSkill { }
class ArtefactTypePersonalskill extends ArtefactTypeResumeGoalAndSkill { }
class ArtefactTypeAcademicskill extends ArtefactTypeResumeGoalAndSkill { }
class ArtefactTypeWorkskill extends ArtefactTypeResumeGoalAndSkill { }


function book_validate(Pieform $form, $values) {
    // Check if string enter by user is valid URL
    if (array_key_exists('url', $values) && !empty($values['url'])) {
        if (filter_var($values['url'], FILTER_VALIDATE_URL) === false) {
            $form->set_error('url', get_string('notvalidurl', 'artefact.resume'));
        }
    }
}

function addbook_validate(Pieform $form, $values) {
    // Check if string enter by user is valid URL
    if (array_key_exists('url', $values) && !empty($values['url'])) {
        if (filter_var($values['url'], FILTER_VALIDATE_URL) === false) {
            $form->set_error('url', get_string('notvalidurl', 'artefact.resume'));
        }
    }
}

function compositeform_submit(Pieform $form, $values) {
    try {
        call_static_method(generate_artefact_class_name($values['compositetype']),
            'process_compositeform', $form, $values);
    }
    catch (Exception $e) {
        $form->json_reply(PIEFORM_ERR, $e->getMessage());
    }
    $form->json_reply(PIEFORM_OK, get_string('compositesaved', 'artefact.resume'));
}

function compositeformedit_submit(Pieform $form, $values) {
    global $SESSION;

    $tabs = PluginArtefactResume::composite_tabs();
    $goto = get_config('wwwroot') . 'artefact/resume/';
    if (isset($tabs[$values['compositetype']])) {
        $goto .= $tabs[$values['compositetype']] . '.php';
    }
    else {
        $goto .= 'index.php';
    }

    try {
        call_static_method(generate_artefact_class_name($values['compositetype']),
            'process_compositeform', $form, $values);
    }
    catch (Exception $e) {
        $SESSION->add_error_msg(get_string('compositesavefailed', 'artefact.resume'));
        redirect($goto);
    }

    $result = array(
        'error'   => false,
        'message' => get_string('compositesaved', 'artefact.resume'),
        'goto'    => $goto,
    );
    if ($form->submitted_by_js()) {
        // Redirect back to the resume composite page from within the iframe
        $SESSION->add_ok_msg($result['message']);
        $form->json_reply(PIEFORM_OK, $result, false);
    }
    $form->reply(PIEFORM_OK, $result);
}

function simple_resumefield_form($defaults, $goto, $options = array()) {
    safe_require('artefact', 'file');
    global $USER, $simple_resume_artefacts, $simple_resume_types;
    $simple_resume_artefacts = array();
    $simple_resume_types = array_keys($defaults);

    $form = array(
        'name'              => 'resumefieldform',
        'plugintype'        => 'artefact',
        'pluginname'        => 'resume',
        'jsform'            => true,
        'successcallback'   => 'simple_resumefield_submit',
        'jssuccesscallback' => 'simple_resumefield_success',
        'jserrorcallback'   => 'simple_resumefield_error',
        'configdirs'        => array(get_config('libroot') . 'form/', get_config('docroot') . 'artefact/file/form/'),
        'elements'          => array(),
    );

    foreach ($simple_resume_types as $t) {
        try {
            $simple_resume_artefacts[$t] = artefact_instance_from_type($t);
            $content = $simple_resume_artefacts[$t]->get('description');
        }
        catch (Exception $e) {
            $content = $defaults[$t]['default'];
        }

        if (!empty($options['editortitle'])) {
            $editortitle = $options['editortitle'];
        }
        else {
            $editortitle = get_string('description', 'artefact.resume');
        }

        $fieldset = $t . 'fs';
        $form['elements'][$fieldset] = array(
            'type' => 'fieldset',
            'legend' => get_string($t, 'artefact.resume'),
            'elements' => array(
                $t => array(
                    'type'  => 'wysiwyg',
                    'class' => 'js-hidden',
                    'title' => $editortitle,
                    'hiddenlabel' => true,
                    'rows'  => 20,
                    'cols'  => 65,
                    'defaultvalue' => $content,
                    'rules' => array('maxlength' => 65536),
                ),
                $t . 'display' => array(
                    'type' => 'html',
                    'class' => 'nojs-hidden-block',
                    'value' => $content,
                ),
                $t . 'submit' => array(
                    'type' => 'submitcancel',
                    'class' => 'js-hidden',
                    'value' => array(get_string('save'), get_string('cancel')),
                    'goto' => get_config('wwwroot') . $goto,
                ),
                $t . 'edit' => array(
                    'type' => 'button',
                    'class' => 'nojs-hidden-block openedit',
                    'value' => get_string('edit'),
                ),
            ),
        );
        if (!empty($defaults[$t]['fshelp'])) {
            $form['elements'][$fieldset]['help'] = true;
        }
    }

    $form['elements']['goto'] = array(
        'type'  => 'hidden',
        'value' => $goto,
    );

    return $form;
}

function simple_resumefield_submit(Pieform $form, $values) {
    global $simple_resume_types, $simple_resume_artefacts, $USER;
    require_once('embeddedimage.php');
    $owner = $USER->get('id');

    if (isset($values['coverletter'])) {
        $newcoverletter = EmbeddedImage::prepare_embedded_images($values['coverletter'], 'resumecoverletter', $USER->get('id'));
        $values['coverletter'] = $newcoverletter;
    }
    else if (isset($values['interest'])) {
        $newinterest = EmbeddedImage::prepare_embedded_images($values['interest'], 'resumeinterest', $USER->get('id'));
        $values['interest'] = $newinterest;
    }

    foreach ($simple_resume_types as $t) {
        if (isset($values[$t . 'submit']) && isset($values[$t])) {
            if (!isset($simple_resume_artefacts[$t])) {
                $classname = generate_artefact_class_name($t);
                $simple_resume_artefacts[$t] = new $classname(0, array(
                    'owner' => $USER->get('id'),
                    'title' => get_string($t),
                ));
            }
            $simple_resume_artefacts[$t]->set('description', $values[$t]);
            $simple_resume_artefacts[$t]->commit();

            $data = array(
                'message' => get_string('goalandskillsaved', 'artefact.resume'),
                'update'  => $t,
                'content' => clean_html($values[$t]),
                'goto'    => get_config('wwwroot') . $values['goto'],
            );
            $form->reply(PIEFORM_OK, $data);
        }
    }

    $form->reply(PIEFORM_OK, array('goto' => get_config('wwwroot') . $values['goto']));
}

function add_resume_attachment($attachmentid) {
    global $artefact;
    if ($artefact) {
        $artefact->attach($attachmentid);
    }
}

function delete_resume_attachment($attachmentid) {
    global $artefact;
    if ($artefact) {
        $artefact->detach($attachmentid);
    }
}

<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * module plugin class. Used for registering the plugin and functions.
 */
class PluginModuleFramework extends PluginModule {
    /**
     * Is the plugin activated or not?
     *
     * @return boolean true, if the plugin is activated, otherwise false
     */
    public static function is_active() {
        $active = false;
        if (get_field('module_installed', 'active', 'name', 'framework')) {
            $active = true;
        }
        return $active;
    }

    /**
     * API-Function get the Plugin ShortName
     *
     * @return string ShortName of the plugin
     */
    public static function get_plugin_name() {
        return 'smartevidence';
    }

    public static function postinst($prevversion) {
        if ($prevversion < 2016071400) {
            // Add foreign key to the collection.framework table on install
            log_debug('Add a fireign key on collection.framework to framework.id');
            $table = new XMLDBTable('collection');
            $field = new XMLDBField('framework');
            if (field_exists($table, $field)) {
                $key = new XMLDBKey('frameworkfk');
                $key->setAttributes(XMLDB_KEY_FOREIGN, array('framework'), 'framework', array('id'));
                add_key($table, $key);
            }
            // Add in any smart evidence framework data to the framework tables
            // based on any existing .matrix files in the matricies directory
            $matricesdir = get_config('docroot') . 'module/framework/matrices/';
            $files = glob($matricesdir . '*.matrix');
            foreach ($files as $file) {
                self::add_matrix_to_db($file);
            }
            require_once('file.php');
            if (!@rmdirr($matricesdir)) {
                log_warn(get_string('manuallyremovematrices', 'module.framework', $matricesdir), true, false);
            }
        }
    }

    public function matrix_is_valid_json($filename) {

        $ok = array('error' => false);
        $matrix = file_get_contents($filename);
        if (!$matrix) {
            $ok['error'] = true;
            $ok['message'] = get_string('invalidfilename', 'admin', $filename);
        }
        else {
            $content = json_decode($matrix);
            if (is_null($content)) {
                $ok['error'] = true;
                $ok['message'] = get_string('invalidjson', 'module.framework');
            }
            else {
                if (empty($content->framework) || empty($content->framework->name)) {
                    $ok['error'] = true;
                    $ok['message'] = get_string('jsonmissingvars', 'module.framework');
                }
                else {
                    $ok['content'] = $content;
                }
            }
        }
        return $ok;
    }

    private function add_matrix_to_db($filename) {
        if (substr_count($filename, '/') == 0) {
            $filename = get_config('docroot') . 'module/framework/matrices/' . $filename;
        }
        $ok = self::matrix_is_valid_json($filename);
        if ($ok['error']) {
            return false;
        }
        else {
            safe_require('module', 'framework');
            $framework = new Framework(null, $ok['content']->framework);
            $framework->commit();
        }
    }

    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        $elements = array(
            'matrix' => array(
                'type' => 'file',
                'title' => get_string('matrixfile', 'module.framework'),
                'description' => get_string('matrixfiledesc', 'module.framework'),
                'accept' => '.matrix',
                'rules' => array(
                    'required' => true
                )
            ),
        );

        return array(
            'elements' => $elements,
        );
    }

    public static function validate_config_options(Pieform $form, $values) {
        require_once('uploadmanager.php');
        $um = new upload_manager('matrix');
        if ($error = $um->preprocess_file()) {
            $form->set_error('matrix', $error);
        }
        if (!$um->optionalandnotsupplied) {
            $reqext = ".matrix";
            $fileext = substr($values['matrix']['name'], (-1 * strlen($reqext)));
            if ($fileext !== $reqext) {
                $form->set_error('matrix', get_string('notvalidmatrixfile', 'module.framework'));
            }
        }

        $matrixfile = self::matrix_is_valid_json($um->file['tmp_name']);
        if ($matrixfile['error']) {
            $form->set_error('matrix', $matrixfile['message']);
        }
    }

    public static function save_config_options(Pieform $form, $values) {
        self::add_matrix_to_db($values['matrix']['tmp_name']);
    }
}

/**
 * module class
 */
class Framework {

    private $id;
    private $name;
    private $institution;
    private $description;
    private $selfassess;
    private $standards;

    const EVIDENCE_BEGUN = 0;
    const EVIDENCE_INCOMPLETE = 1;
    const EVIDENCE_PARTIALCOMPLETE = 2;
    const EVIDENCE_COMPLETED = 3;

    public function __construct($id=0, $data=null) {

        if (!empty($id)) {
            $tempdata = get_record('framework', 'id', $id);
            if (empty($tempdata)) {
                throw new FrameworkNotFoundException("Framework with id $id not found");
            }
            if (!empty($data)) {
                $data = array_merge((array)$tempdata, $data);
            }
            else {
                $data = $tempdata; // use what the database has
            }
            $this->id = $id;
        }

        if (empty($data)) {
            $data = array();
        }
        foreach ((array)$data as $field => $value) {
            if (property_exists($this, $field)) {
                if (empty($id) && $field === 'standards') {
                    $value = array('standards' => $value,
                                   'count' => count($value));
                }
                if ($field == 'selfassess') {
                    $value = (int) $value;
                }
                $this->{$field} = $value;
            }
        }
    }

    public function get($field) {
        if (!property_exists($this, $field)) {
            throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
        }
        if ($field == 'standards') {
            return $this->standards(false);
        }
        if ($field == 'standardsoptions') {
            return $this->standards();
        }
        if ($field == 'collections') {
            return $this->collections();
        }
        return $this->{$field};
    }

    public function set($field, $value) {
        if (property_exists($this, $field)) {
            $this->{$field} = $value;
            $this->mtime = time();
            return true;
        }
        throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
    }

    /**
     * Deletes a Framework
     */
    public function delete() {
        $standards = get_column('framework_standard', 'id', 'framework', $this->id);

        db_begin();
        delete_records('framework_evidence', 'framework', $this->id);
        delete_records_sql('DELETE FROM {framework_standard_element} WHERE standard IN (' . join(',', array_map('intval', $standards)) . ')');
        delete_records('framework_standard', 'framework', $this->id);
        delete_records('framework', 'id', $this->id);

        db_commit();
    }

    /**
     * This method updates the contents of the
     * - framework table
     * - framework_standard table (optional based on $this->standards data)
     * - framework_standard_element table (optional based on $this->standards data)
     */
    public function commit() {

        $fordb = new StdClass;
        foreach (get_object_vars($this) as $k => $v) {
            $fordb->{$k} = $v;
        }

        db_begin();

        // if id is not empty we are editing an existing framework
        if (!empty($this->id)) {
            update_record('framework', $fordb, 'id');
        }
        else {
            $id = insert_record('framework', $fordb, 'id', true);
            if ($id) {
                $this->set('id', $id);
            }
        }
        // update standards
        $standardsvars = array('shortname','name','description','priority');
        if (isset($this->standards) && is_array($this->standards)) {
            foreach ($this->standards['standards'] as $standard) {
                $sfordb = new StdClass;
                $sfordb->framework = $this->id;
                $sfordb->mtime = db_format_timestamp(time());
                foreach ($standardsvars as $v) {
                    $sfordb->{$v} = isset($standard->{$v}) ? $standard->{$v} : null;
                }
                if (!empty($standard->id)) {
                    $sfordb->id = $standard->id;
                    update_record('framework_standard', $sfordb, 'id');
                }
                else {
                    $sfordb->ctime = db_format_timestamp(time());
                    $sid = insert_record('framework_standard', $sfordb, 'id', true);
                    // From .matrix file reading
                    if (isset($standard->standardelement) && is_array($standard->standardelement)) {
                        $standard->options = $standard->standardelement;
                    }
                    if ($sid && isset($standard->options) && is_array($standard->options)) {
                        $prevoption = 0;
                        foreach ($standard->options as $option) {
                            $sofordb = new StdClass;
                            $sofordb->standard = $sid;
                            $sofordb->mtime = db_format_timestamp(time());
                            foreach ($standardsvars as $ov) {
                                $sofordb->{$ov} = isset($option->{$ov}) ? $option->{$ov} : null;
                            }
                            if (!empty($option->id)) {
                                $sofordb->id = $option->id;
                                $prevoption = $option->id;
                                update_record('framework_standard', $sofordb, 'id');
                            }
                            else {
                                $sofordb->ctime = db_format_timestamp(time());
                                $sofordb->parent = ($option->parent && $prevoption) ? $prevoption : null;
                                $prevoption = insert_record('framework_standard_element', $sofordb, 'id', true);
                            }
                        }
                    }
                }
            }
        }

        db_commit();
    }

    /**
     * Returns array of standards in the current framework
     *
     * @param boolean $options  Set to false if you only want the standards without substandard options
     * @return array standards
     */
    public function standards($options = true) {

        if (!isset($this->standards)) {

            $sql = "SELECT id, shortname, name, description, priority, ctime, mtime
                     FROM {framework_standard}
                     WHERE framework = ?
                    ORDER BY priority, shortname, name, ctime";

            $result = get_records_sql_assoc($sql, array($this->get('id')));

            if (!empty($result)) {
                if ($options) {
                    // get all options relating to the standards
                    $sql = "SELECT id, standard, shortname, name, description, priority, parent, ctime, mtime
                             FROM {framework_standard_element}
                             WHERE standard IN (" . join(',', array_map('intval', array_keys($result))) . ")
                             ORDER BY priority, shortname, name, ctime";

                    $optresult = get_records_sql_assoc($sql, array());
                    if ($optresult) {
                        foreach ($optresult as $opt) {
                            if (!isset($result[$opt->standard]->options)) {
                                $result[$opt->standard]->options = array();
                            }
                            $result[$opt->standard]->options[] = $opt;
                        }
                    }
                }
                $standards = array(
                    'standards' => array_values($result),
                    'count'     => count($result),
                );

                $this->standards = $standards;
            }
            else {
                $this->standards = array();
            }

        }

        return $this->standards;
    }

    /**
     * Check that the framework is being used by a collection
     *
     * @return boolean
     */
    public function in_collections() {
        if (empty($this->collections)) {
            return false;
        }
        return true;
    }

    /**
     * Get collections that use the framework
     *
     * @return object $collections
     */
    public function collections() {
        require_once('collection.php');
        if (!isset($this->collections)) {
            $collections = array();
            $ids = get_column('collection', 'id', 'framework', $this->id);
            foreach ($ids as $id) {
                $collection = new Collection($id);
                $collections[] = $collection;
            }
            $this->collections = $collections;
        }

        return $this->collections;
    }

    /**
     * Get ids of collections that use the framework
     *
     * @return array $ids
     */
    public function get_collectionids() {
        $ids = array();
        $data = $this->collections();

        if (!empty($data)) {
            foreach ($data as $c) {
                $ids[] = $c->id;
            }
        }

        return $ids;
    }

    /**
     * Return the current state as part of array of all states
     *
     * @param string $state Current state
     *
     * @return array All states with current active
     */
    public static function get_state_array($state) {
        return array(
            'begun' => ((int) $state === Self::EVIDENCE_BEGUN ? 1 : 0),
            'incomplete' => ((int) $state === Self::EVIDENCE_INCOMPLETE ? 1 : 0),
            'partialcomplete' => ((int) $state === Self::EVIDENCE_PARTIALCOMPLETE ? 1 : 0),
            'completed' => ((int) $state === Self::EVIDENCE_COMPLETED ? 1 : 0),
        );
    }

    /**
     * Get available frameworks based on institution
     *
     * @param string  $institution  If set to 'any' all results returned
     * @param boolean $shared       Return frameworks that can be viewed by all institutions
     *
     * @return frameworks
     */
    public function get_frameworks($institution = 'any', $shared = false) {
        global $USER;

        $sql = "SELECT * FROM {framework}";
        $values = array();
        if ($institution != 'any') {
            // Only get the frameworks available to this institution
            $placeholders = '?';
            $values[] = $institution;
            if ($shared) {
                // Include frameworks with institution set to 'all'
                $placeholders .= ',?';
                $values[] = 'all';
            }
            $sql .= " WHERE institution IN (" . $placeholders . ")";
        }
        $sql .= " ORDER BY name";
        $frameworks = get_records_sql_array($sql, $values);
        return $frameworks;
    }

    /**
     * Add/update an annotation block on a view via the framework matrix page.
     * This hooks into using the annotation block's config form.
     *
     * @param int $collectionid
     * @param int $annotationid
     *
     * @return evidence(s)
     */
    public function get_evidence($collectionid, $annotationid = false) {
        if ($viewids = get_column('collection_view', 'view', 'collection', $collectionid)) {
            $evidence = get_records_sql_array('SELECT * FROM {framework_evidence} WHERE framework = ? AND view IN (' . join(',', $viewids) . ')', array($this->id));
            if (!empty($annotationid) && $evidence) {
                foreach ($evidence as $e) {
                    if ($e->annotation === $annotationid) {
                        return $e;
                    }
                }
                return false;
            }
            return $evidence;
        }
        return false;
    }

    public static function annotation_config_form($data) {
        require_once(get_config('docroot') . 'blocktype/lib.php');
        if (empty($data->annotation)) {
            // Get the title for the option
            $title = get_field('framework_standard_element', 'shortname', 'id', $data->option);

            // Find out how many blocks already exist for the view.
            $maxorder = get_field_sql(
                'SELECT MAX("order") FROM {block_instance} WHERE "view"=? AND "row"=? AND "column"=?',
                array($data->view, 1, 1)
            );
            // Create the block at the end of the cell.
            $annotation = new BlockInstance(0, array(
                'blocktype'  => 'annotation',
                'title'      => (get_string('Annotation', 'artefact.annotation') . ': ' . $title),
                'view'       => $data->view,
                'row'        => 1,
                'column'     => 1,
                'order'      => $maxorder + 1,
            ));
            $annotation->commit();
            $new = true;
        }
        else {
            $annotation = new BlockInstance($data->annotation);
            $new = false;
        }
        $title = $annotation->get_title();
        $annotation->option = $data->option;
        $annotation->frommatrix = true;
        list($content, $js, $css) = array_values($annotation->build_configure_form($new));

        $return = array(
            'content' => $content,
            'js' => $js,
            'css' => $css,
            'title' => $title,
            'isnew' => $new
        );
        return $return;
    }

    /**
     * Save evidence
     * @param string $id          Framework_evidence id
     * @param string $framework   Framework id                  }
     * @param string $element     Framework_standard_element id }  A unique grouping
     * @param string $view        View id                       }
     * @param string $annotation  Annotation block id (not artefact id)
     * @param string $state       See constants in this class
     * @param string $reviewer    The user marking the evidence as completed
     */
    public static function save_evidence($id = null, $framework = null, $element = null, $view = null, $annotation = null, $state = Self::EVIDENCE_BEGUN, $reviewer = null) {
        // need to check we have at least one indicator of uniqueness
        $uniqueness = false;
        if (!empty($id)) {
            $uniqueness = true;
        }
        else if (!empty($framework) && !empty($element) && !empty($view)) {
            $uniqueness = true;
        }

        if (!$uniqueness) {
            throw new SQLException('No unique identifier supplied');
        }

        $fordb = array('mtime' => db_format_timestamp(time()),
                       'annotation' => $annotation,
                       'state' => $state);
        if ($id) {
            // update row
            if (!empty($element)) {
                $fordb['element'] = $element;
            }
            $fordb['reviewer'] = ((int) $state === Self::EVIDENCE_COMPLETED) ? $reviewer : null;
            update_record('framework_evidence', (object) $fordb, (object) array('id' => $id));
        }
        else {
            // insert
            $fordb['view'] = $view;
            $fordb['element'] = $element;
            $fordb['framework'] = $framework;
            $fordb['ctime'] = db_format_timestamp(time());
            $id = insert_record('framework_evidence', (object) $fordb, 'id', true);
        }
        return $id;
    }

    /**
     * Save evidence when adding block to page on block edit view
     *
     * @param string $blockid  Block id
     * @param string $element  The framework_standard_element id
     */
    public static function save_evidence_in_block($blockid, $element) {
        $evidence = get_record('framework_evidence', 'annotation', $blockid);
        $id = !empty($evidence) ? $evidence->id : null;

        if (!$id) {
            // We need to find the view/framework info via the blockid
            if ($records = get_records_sql_array("SELECT bi.view, c.framework FROM {block_instance} bi
                                                 JOIN {collection_view} cv ON cv.view = bi.view
                                                 JOIN {collection} c ON c.id = cv.collection
                                                 WHERE bi.id = ?", array($blockid))) {
                $record = $records[0];
                try {
                    $id = self::save_evidence(null, $record->framework, $element, $record->view, $blockid);
                    return $id;
                }
                catch (SQLException $e) {
                    // An error occured like an existing annotation block exist for this view/standard option
                    return false;
                }
            }
            else {
                // block not on a page that is in a collection that is using a framework
                return false;
            }
        }
        else {
            try {
                $id = self::save_evidence($id, null, $element, null, $blockid);
                return $id;
            }
            catch (SQLException $e) {
                // An error occured like an existing annotation block exist for this view/standard option
                return false;
            }
        }
    }

    /**
     * Add/update an annotation status form on the framework matrix page.
     * This uses a feedback style config form with some extra bits.
     */
    public function annotation_feedback_form($data) {
        global $USER;

        require_once(get_config('docroot') . 'blocktype/lib.php');
        $annotation = new BlockInstance($data->annotation);
        $configdata = $annotation->get('configdata');
        if (empty($configdata['artefactid'])) {
            return false;
        }

        safe_require('artefact', 'file');
        $artefactid = $configdata['artefactid'];
        $artefact = $annotation->get_artefact_instance($artefactid);
        $view = $annotation->get_view();
        $text = $artefact->get('description');
        $collection = $view->get('collection');
        $evidence = get_record('framework_evidence', 'annotation', $annotation->get('id'));
        $defaultval = $evidence->state;

        if (!is_object($collection) || !$collection->get('framework')) {
            return false;
        }

        $options = self::allow_assessment($view->get('owner'), true, $evidence->framework);
        if (!$options) {
            $choices = Self::get_choices();
            $assessment = array(
                'type' => 'html',
                'title' => get_string('assessment', 'module.framework'),
                'value' => $choices[$defaultval],
                'class' => 'top-line',
            );
        }
        else {
            if (!array_key_exists($defaultval, $options)) {
                $defaultval = null;
            }
            $assessment = array(
                'type' => 'select',
                'title' => get_string('assessment', 'module.framework'),
                'options' => $options,
                'defaultvalue' => $defaultval,
                'width' => '280px',
                'class' => 'top-line',
            );
        }

        $form = array(
            'name' => 'annotationfeedback',
            'jsform' => true,
            'renderer' => 'div',
            'plugintype' => 'module',
            'pluginname' => 'framework',
            'jssuccesscallback' => 'updateAnnotation',
            'elements'   => array(
                'annotation' => array(
                    'type' => 'html',
                    'value' => $text,
                ),
            ),
        );
        if ($options || (!$options && $view->get('owner') == $USER->get('id'))) {
            $form['elements']['annotationdiv'] = array(
                'type' => 'html',
                'value' => '<div class="modal-header modal-section">' . get_string("assessment", "module.framework") . '</div>',
            );
            $form['elements']['assessment'] = $assessment;
        }
        if ($options) {
            $form['elements']['submitcancel'] = array(
                'type' => 'submitcancel',
                'class' => 'btn-default',
                'value' => array(get_string('save'), get_string('cancel')),
                'goto' => get_string('docroot') . 'module/framework/matrix.php?id=' . $collection->get('id'),
            );
        }
        $content = pieform($form);
        list($feedbackcount, $annotationfeedback) = ArtefactTypeAnnotationfeedback::get_annotation_feedback_for_matrix($artefact, $view, $annotation->get('id'));
        $content .= $annotationfeedback;

        $return = array(
            'content' => $content,
            'js' => 'function updateAnnotation(form, data) { formSuccess(form, data); }',
            'css' => '',
            'title' => $annotation->get_title(),
        );
        return $return;
    }

    /**
     * Check to see if a user can add an annotation via the matrix page. Currently only view owner
     *
     * @param string $viewid    The view the matrix point is associated with
     *
     * @return bool
     */
    public static function allow_annotation($viewid) {
        global $USER;

        if (empty($viewid) || !is_numeric($viewid)) {
            return false;
        }

        require_once(get_config('libroot') . 'view.php');
        $view = new View($viewid);
        $collection = $view->get('collection');
        if (!is_object($collection)) {
            return false;
        }
        $framework = $collection->get('framework');
        if (empty($framework)) {
            return false;
        }

        $userid = $USER->get('id');
        if ($USER->get('id') == $view->get('owner')) {
            // Is owner
            return true;
        }
        return false;
    }

    /**
     * Check to see if a user can set the assessment status for a piece of evidence.
     * If $options is set to true return the valid options for a select dropdown
     *
     * @param string $ownerid   The owner of the smart evidence annotation
     * @param bool   $options   Whether to return the valid options (on true) or just true/false
     * @param string $framework ID of the framework
     *
     * @return mixed either bool or array for select dropdown
     */
    public static function allow_assessment($ownerid, $options = false, $framework = null) {
        global $USER;

        if (empty($ownerid) || !is_numeric($ownerid)) {
            return false;
        }

        $owner = new User();
        $owner->find_by_id($ownerid);
        $ownerinstitutions = array_keys($owner->get('institutions'));
        $institution = (!empty($ownerinstitutions)) ? $ownerinstitutions[0] : 'mahara';
        $isowner = ($owner->get('id') === $USER->get('id'));
        $isadminofowner = $selfcomplete = false;

        if ($USER->get('admin') || $USER->get('staff')) {
            $isadminofowner = true;
        }
        else if ($institution != 'mahara' && ($USER->is_institutional_admin($institution) || $USER->is_institutional_staff($institution))) {
            $isadminofowner = true;
        }

        require_once(get_config('libroot') . 'institution.php');
        $institution = new Institution($institution);
        // Check that smart evidence self assessment is enabled for the framework
        if ($framework) {
            $framework = new Framework($framework);
            if ($framework->selfassess) {
                $selfcomplete = true;
            }
        }

        if ($isowner || $isadminofowner) {
            if ($options) {
                $reply = Self::get_choices();
                if (($isowner && $selfcomplete === false) ||
                    ($isadminofowner && $selfcomplete === true)) {
                    unset($reply[1]);
                    unset($reply[2]);
                    unset($reply[3]);
                }
                return $reply;
            }
            else {
                return true;
            }
        }
        return false;
    }

    public static function get_choices() {
        return array(
            Self::EVIDENCE_BEGUN => get_string('begun','module.framework'),
            Self::EVIDENCE_INCOMPLETE => get_string('incomplete','module.framework'),
            Self::EVIDENCE_PARTIALCOMPLETE => get_string('partialcomplete','module.framework'),
            Self::EVIDENCE_COMPLETED => get_string('completed','module.framework'),
        );
    }
}

class FrameworkNotFoundException extends NotFoundException {}

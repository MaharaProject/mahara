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
        return 'framework';
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
            $files = glob(get_config('docroot') . 'module/framework/matrices/*.matrix');
            foreach ($files as $file) {
                self::add_matrix_to_db($file);
            }
        }
    }

    private function add_matrix_to_db($filename) {
        if (substr_count($filename, '/') == 0) {
            $filename = get_config('docroot') . 'module/framework/matrices/' . $filename;
        }

        $matrix = file_get_contents($filename);
        $content = json_decode($matrix);
        if (!$content || empty($content->framework) || empty($content->framework->name)) {
            log_warn($file . ' is not a valid JSON file');
            return false;
        }
        else {
            safe_require('module', 'framework');
            $framework = new Framework(null, $content->framework);
            $framework->commit();
        }
    }

    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        $elements = array(
            'matrix' => array(
                'type'  => 'text',
                'size' => 50,
                'title' => get_string('matrixname', 'module.framework'),
                'rules' => array(
                    'required' => true,
                ),
                'description' => get_string('matrixnamedesc', 'module.framework'),
            )
        );

        return array(
            'elements' => $elements,
        );
    }

    public static function validate_config_options(Pieform $form, $values) {
        if (empty($values['matrix']) ||
            preg_match("/\.matrix$/", $values['matrix']) === 0 ||
            !file_exists(get_config('docroot') . 'module/framework/matrices/' . $values['matrix'])
        ) {
            $form->set_error('matrix', get_string('errorbadmatrixname', 'module.framework'));
        }
    }

    public static function save_config_options(Pieform $form, $values) {
        self::add_matrix_to_db($values['matrix']);
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
            'begun' => ((int) $state === Self::EVIDENCE_BEGUN),
            'incomplete' => ((int) $state === Self::EVIDENCE_INCOMPLETE),
            'partialcomplete' => ((int) $state === Self::EVIDENCE_PARTIALCOMPLETE),
            'completed' => ((int) $state === Self::EVIDENCE_COMPLETED),
        );
    }

    /**
     * Get the evidence state for the framework
     *
     * @param int $userid
     *
     * @return outcomes
     */
    public function get_evidence() {
        $outcomes = get_records_array('framework_evidence', 'framework', $this->id);
        return $outcomes;
    }

}

class FrameworkNotFoundException extends NotFoundException {}

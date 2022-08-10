<?php
/**
 *
 * @package    Mahara
 * @subpackage module-submissions
 * @author     Alexander Del Ponte <delponte@uni-bremen.de>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information, please see the README file distributed with this software.
 *
 */

namespace Submissions\Models;

use InvalidArgumentException;

abstract class AbstractModel {

    protected $id;                                  // Mandatory for every Mahara DB-Table

    protected static $dbTable;                      // Mahara DB-Table
    protected static $propertiesToDBFields = [];    // Associative array 'property name' => 'dbfield name'
    protected static $internalDbFieldsToProperties = [];    // Associative array 'dbfield name of model internal dbtable' => 'property name'
    protected static $externalDbFieldsToProperties = [];    // Associative array 'dbfield name of model foreign dbtable' => 'property name'
    protected static $allDbFieldsToProperties = [];    // Merged associative array of both internal and external dbfields to properties arrays
    protected static $dateProperties = [];          // Array with date properties (db handling in read and commit)

    protected $dirty;

    /**
     * @return array
     */
    public static function getAllDbFieldsToProperties() {
        if (empty(static::$allDbFieldsToProperties)) {
            static::$allDbFieldsToProperties = array_merge(static::$internalDbFieldsToProperties, static::$externalDbFieldsToProperties);
        }
        return static::$allDbFieldsToProperties;
    }

    /**
     * @param string $property
     * @return false|int|string
     * @throws \SystemException
     */
    protected static function getDbFieldForProperty($property) {
        $dbField = array_search($property, static::getAllDbFieldsToProperties(), true);
        if ($dbField) {
            return $dbField;
        }
        else {
            throw new \SystemException("Property $property is not defined for DBField in class " . get_called_class());
        }
    }

    /**
     * @param string $dbField
     * @return string
     * @throws \SystemException
     */
    protected static function getPropertyForDbField($dbField) {
        if (array_key_exists($dbField, static::getAllDbFieldsToProperties())) {
            return static::$allDbFieldsToProperties[$dbField];
        }
        else {
            throw new \SystemException("DBField $dbField has no defined Property in class " . get_called_class());
        }
    }

    /**
     * @param string $property
     * @return mixed
     */
    public function get($property) {
        if (!property_exists($this, $property)) {
            throw new InvalidArgumentException("Property $property wasn't found in class " . get_class($this));
        }
        return $this->$property;
    }

    /**
     * @param string $property
     * @param mixed $value
     */
    public function set($property, $value) {
        if (property_exists($this, $property)) {
            if ($this->$property != $value) {
                // Only set it to dirty if it's changed.
                $this->dirty = true;
            }
            $this->$property = $value;
        }
        else {
            throw new InvalidArgumentException("Property $property wasn't found in class " . get_class($this));
        }
    }

    /**
     * AbstractModel constructor.
     * @param int|null $id
     * @param \stdClass|null $data
     * @throws \SystemException
     */
    function __construct($id = null, \stdClass $data = null) {
        if (empty($this::$allDbFieldsToProperties)) {
            $this::$allDbFieldsToProperties = array_merge($this::$internalDbFieldsToProperties, $this::$externalDbFieldsToProperties);
        }

        if ($id) {
            $this->read($id);
        }
        else if ($data) {
            $this->setPropertiesFromRecord($data);
        }

        $this->dirty = false;
    }

    /**
     * @param \stdClass $data
     */
    protected function setPropertiesFromRecord(\stdClass $data) {
        foreach ($this::$allDbFieldsToProperties as $dbField => $property) {
            if (property_exists($data, $dbField)) {
                if (in_array($property, $this::$dateProperties)) {
                    $this->$property = strtotime($data->$dbField);
                }
                else {
                    $this->$property = $data->$dbField;
                }
            }
        }
    }

    /**
     * @return \stdClass
     */
    protected function createRecordFromProperties() {
        $data = new \stdClass();

        foreach ($this::$internalDbFieldsToProperties as $dbField => $property) {
            if (in_array($property, $this::$dateProperties)) {
                $data->$dbField = db_format_timestamp($this->$property);
            }
            else {
                $data->$dbField = $this->$property;
            }
        }
        return $data;
    }

    /**
     * @return bool
     * @throws \SQLException
     */
    public function commit() {
        $new = empty($this->id);
        $data = $this->createRecordFromProperties();
        $success = true;

        db_begin();

        if ($new) {
            $newId = insert_record($this::$dbTable, $data, 'id', true);
            if ($newId) {
                $this->id = $newId;
            }
            else {
                $success = false;
            }
        }
        else if ($this->dirty) {
            $success = update_record($this::$dbTable, $data, 'id', 'id', true);
        }

        db_commit();
        $this->dirty = false;

        return $success;
    }

    /**
     * @param int|null $id
     * @throws \SystemException
     */
    public function read($id = null) {

        if (is_null($id) && $this->id) {
            $id = $this->id;
        }
        else if (is_null($id)) {
            throw new \SystemException('Missing id to be read from db.');
        }
        $data = get_record($this::$dbTable, 'id', $id);

        if (!$data) {
            throw new \SystemException('Record not found.', $id);
        }

        $this->setPropertiesFromRecord($data);
        $this->dirty = false;
    }

    /**
     * @throws \SQLException
     */
    public function delete() {
        if (empty($this->id)) {
            return;
        }

        db_begin();
        delete_records($this::$dbTable, 'id', $this->id);
        db_commit();
    }
}
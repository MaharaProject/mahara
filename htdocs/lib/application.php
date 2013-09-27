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

class Application {

    const   UNINITIALIZED           = 0;
    const   INITIALIZED             = 1;
    const   PERSISTENT              = 2;

    private $initialized            = self::UNINITIALIZED;
    private $changed                = false;
    private $oldname;
    private $members = array('name' => '',
                             'displayname' => '',
                             'xmlrpcserverurl' => '',
                             'ssolandurl' => ''
                             );

    public function __construct($result = null) {
        if (null == $result) {
            return;
        }
        $this->populate($result);
        $this->initialized = self::PERSISTENT;
    }

    protected function populate($result) {
        $values = get_object_vars($result);
        foreach ($values as $key => $value) {
            $this->__set($key, $value);
        }
        $this->oldname = $result->name;
    }

    public static function findByName($name) {
        $result = get_record('application', 'name', $name);

        if (false == $result) {
            throw new ParamOutOfRangeException(addslashes($name) .' is not an application.');
        }
        return new Application($result);
    }

    public function __set($name, $value) {
        if (!array_key_exists($name, $this->members)) {
            throw new ParamOutOfRangeException(addslashes($name) .' is not a member of Application.');
        }
        if ($value != $this->members[$name]) {
            $this->members[$name] = $value;
            $this->changed = true;
        }
        if (!empty($this->members['name']) && !empty($this->members['xmlrpcserverurl']) && !empty($this->members['ssolandurl'])) {
            $this->initialized = self::INITIALIZED;
        }
        return $this;
    }

    public function __get($name) {
        if (array_key_exists($name, $this->members)) {
            return $this->members[$name];
        }
        return null;
    }

    public function commit() {
        if ($this->initialized == self::UNINITIALIZED) {
            return false;
        }

        if (false == $this->changed) return true;

        if (empty($this->members['displayname'])) {
            $this->members['displayname'] = $this->members['name'];
        }

        if (false == $this->changed) return true;
        $application = new stdClass();
        $application->name             = $this->members['name'];
        $application->displayname      = $this->members['displayname'];
        $application->xmlrpcserverurl  = $this->members['xmlrpcserverurl'];

        if ($this->initialized == self::INITIALIZED) {
            $this->initialized = self::PERSISTENT;
            return insert_record('application',$application);
        } elseif ($this->initialized == self::PERSISTENT) {
            return update_record('application', $application, array('name' => $application->oldname) );
        }
        return false;
    }

}

<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage auth-internal
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

// TODO : lib

defined('INTERNAL') || die();
global $CFG;
require($CFG->docroot .'/include/eLearning/hostset.php');

class Institution {

    const   UNINITIALIZED  = 0;
    const   INITIALIZED    = 1;
    const   PERSISTENT     = 2;

    protected $initialized = self::UNINITIALIZED;
    protected $hostset;
    protected $members = array(
        'name' => '',
        'displayname' => '',
        'registerallowed' => 1,
        'updateuserinfoonlogin' => 0,
        'defaultaccountlifetime' => null,
        'defaultaccountinactiveexpire' => null,
        'defaultaccountinactivewarn' => 0
        ); 

    function __construct($name = null) {
        if (is_null($name)) {
            return $this;
        }

        if ($this->findByName($name)) {
            throw new ParamOutOfRangeException('No such institution');
        }
    }

    function __get($name) {
        if ($name == 'hosts') {
            if (count($this->hosts) == 0 && $this->ready) {
                $this->getHostData();
            }
        }
        if (array_key_exists($name, $this->members)) {
            return $this->members[$name];
        }
        return null;
    }

    public function __set($name, $value) {
        if (!is_string($name) | !array_key_exists($name, $this->members)) {
            throw new ParamOutOfRangeException();
        }
        if ($name == 'name') {
            if (!is_string($value) || empty($value) || strlen($value) > 255) {
                throw new ParamOutOfRangeException("'name' should be a string between 1 and 255 characters in length");
            }
        } elseif ($name == 'displayname') {
            if (!is_string($value) || empty($value) || strlen($value) > 255) {
                throw new ParamOutOfRangeException("'displayname' ($value) should be a string between 1 and 255 characters in length");
            }
        } elseif ($name == 'registerallowed') {
            if (!is_numeric($value) || $value < 0 || $value > 1) {
                throw new ParamOutOfRangeException("'registerallowed' should be zero or one");
            }
        } elseif ($name == 'updateuserinfoonlogin') {
            if (!is_numeric($value) || $value < 0 || $value > 1) {
                throw new ParamOutOfRangeException("'updateuserinfoonlogin' should be zero or one");
            }
        } elseif ($name == 'defaultaccountlifetime') {
            if (!empty($value) && (!is_numeric($value) || $value < 0 || $value > 9999999999)) {
                throw new ParamOutOfRangeException("'defaultaccountlifetime' should be a number between 1 and 9,999,999,999");
            }
        } elseif ($name == 'defaultaccountinactiveexpire') {
            if (!empty($value) && (!is_string($value) || empty($value) || strlen($value) > 255)) {
                throw new ParamOutOfRangeException("'defaultaccountinactiveexpire' should be a number between 1 and 9,999,999,999");
            }
        } elseif ($name == 'defaultaccountinactivewarn') {
            if (!empty($value) && strlen($value) > 255) {
                throw new ParamOutOfRangeException("'defaultaccountinactivewarn' should be a number between 1 and 9,999,999,999");
            }
        }
        $this->members[$name] = $value;
    }

    function findByName($name) {
        global $CFG;
        if (!is_string($name) || strlen($name) < 1 || strlen($name) > 255) {
            throw new ParamOutOfRangeException("'name' must be a string.");
        }

        $result = get_record('institution', 'name', $name);

        if (false == $result) {
            return false;
        }

        $this->initialized = self::PERSISTENT;
        $this->populate($result);

        return $this;
    }

    function findByWwwroot($wwwroot) {
        // TODO : remove CFG
        global $CFG;
        if (!is_string($wwwroot) || strlen($wwwroot) < 1 || strlen($wwwroot) > 255) {
            throw new SystemException();
        }

        $this->hostset = new HostSet();
        $this->hostset->findByWwwroot($wwwroot);
        if (false == $a_host = $this->hostset->current()) {
            return false;
        }
        $institution = $a_host->institution;

        $result = get_record('institution', 'name', $institution);
        if (false == $result) {
            throw new SystemException('Invalid Institution name');
        }

        $this->initialized = self::PERSISTENT;
        $this->populate($result);
    }

    function getHostData() {
        $this->hostset = new HostSet();
        $this->hostset->findByInstitution($this->name);
    }

    function initialise($name, $displayname) {
        if (empty($name) || !is_string($name)) {
            return false;
        }

        $this->name = $name;
        if (empty($displayname) || !is_string($displayname)) {
            return false;
        }

        $this->displayname = $displayname;
        $this->initialized = max(self::INITIALIZED, $this->initialized);
        return true;
    }

    function verifyReady() {
        if (empty($this->members['name']) || !is_string($this->members['name'])) {
            return false;
        }
        if (empty($this->members['displayname']) || !is_string($this->members['displayname'])) {
            return false;
        }
        $this->initialized = max(self::INITIALIZED, $this->initialized);
        return true;
    }

    function commit() {
        if (!$this->verifyReady()) {
            throw new Exception();
        }

        $record = new stdClass();
        $record->name                         = $this->name;
        $record->displayname                  = $this->displayname;
        $record->updateuserinfoonlogin        = $this->updateuserinfoonlogin;
        $record->defaultaccountlifetime       = $this->defaultaccountlifetime;
        $record->defaultaccountinactiveexpire = $this->defaultaccountinactiveexpire;
        $record->defaultaccountinactivewarn   = $this->defaultaccountinactivewarn;

        if ($this->initialized == self::INITIALIZED) {
            return insert_record('institution', $record);
        } elseif ($this->initialized == self::PERSISTENT) {
            return update_record('institution', $record, array('name' => $this->name));
        }
        // Shouldn't happen but who noes?
        return false;
    }

    function findByHostset(HostSet $hostset) {
        global $CFG;

        // get the first host record:
        $host = reset($hostset);

        $result = get_record('institution', 'name', $host->institution);
        if (false == $result) {
            throw new SystemException('No institution for hostset '.addslashes($host->institution));
        }
        $this->populate($result);
    }

    protected function populate($result) {
        $this->name                         = $result->name;
        $this->displayname                  = $result->displayname;
        $this->registerallowed              = $result->registerallowed;
        $this->updateuserinfoonlogin        = $result->updateuserinfoonlogin;
        $this->defaultaccountlifetime       = $result->defaultaccountlifetime;
        $this->defaultaccountinactiveexpire = $result->defaultaccountinactiveexpire;
        $this->defaultaccountinactivewarn   = $result->defaultaccountinactivewarn;
        $this->verifyReady();
    }

}
?>

<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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

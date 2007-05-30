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
 * @author     Donal McMullan <donal@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();
global $CFG;

class Host {

    protected $wwwroot                      = '';
    protected $displayname                  = '';
    protected $ipaddress                    = '';
    protected $deleted                      = 0;
    protected $publickey                    = '';
    protected $publickeyexpires             = 0;
    protected $portno                       = 80;
    protected $lastconnecttime              = 0;
    protected $application                  = '';
    protected $theyssoin                    = 0;
    protected $wessoout                     = 0;
    protected $institution                  = '';

    function __construct($resultset = null) {
        if (is_null($resultset)) {
            return $this;
        }

        $this->populate($resultset);
    }

    function __get($name) {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
        return;
    }

    function __set($name, $value) {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
        return;
    }

    function findByWwwroot($wwwroot) {
        global $CFG;
        if (!is_string($name) || strlen($name) < 1 || strlen($name) > 255) {
            throw new SystemException();
        }

        $result = get_record('host', 'wwwroot', $wwwroot);

        if (false == $result) {
            throw new SystemException('Unknown wwwroot: '.addslashes($wwwroot));
        }

        $this->populate($result);
    }

    function commit($result) {
        $this->wwwroot              = $result->wwwroot;
        $this->name                 = $result->name;
        $this->ipaddress            = $result->ipaddress;
        $this->deleted              = $result->deleted;
        $this->publickey            = $result->publickey;
        $this->publickeyexpires     = $result->publickeyexpires;
        $this->portno               = $result->portno;
        $this->lastconnecttime      = $result->lastconnecttime;
        $this->application          = $result->application;
        $this->theyssoin            = $result->theyssoin;
        $this->wessoout             = $result->wessoout;
        $this->institution          = $result->institution;
    }

    protected function populate($result) {
        $this->wwwroot              = $result->wwwroot;
        $this->name                 = $result->name;
        $this->ipaddress            = $result->ipaddress;
        $this->deleted              = $result->deleted;
        $this->publickey            = $result->publickey;
        $this->publickeyexpires     = $result->publickeyexpires;
        $this->portno               = $result->portno;
        $this->lastconnecttime      = $result->lastconnecttime;
        $this->application          = $result->application;
        $this->theyssoin            = $result->theyssoin;
        $this->wessoout             = $result->wessoout;
        $this->institution          = $result->institution;
    }

}
?>

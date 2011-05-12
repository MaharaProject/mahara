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

defined('INTERNAL') || die();
require_once(get_config('libroot') . 'application.php');

class ApplicationSet implements Iterator {

    protected $resultset                  = array();

    function __construct() {
        $this->resultset   = get_records_assoc('application');
    }

    /////////////////////////////////////////////////////////
    // Iterator stuff
    public function rewind() {
        reset($this->resultset);
    }

    public function current() {
        if (false === current($this->resultset)) {
            return false;
        }

        if (!is_a(current($this->resultset), 'Application')) {
            $key     =     key($this->resultset);
            $current = current($this->resultset);

            $this->resultset[$key] = new Application($current);
        }
        return current($this->resultset);
    }

    public function key() {
        return key($this->resultset);
    }

    public function next() {
        next($this->resultset);
        return $this->current();
    }

    public function valid() {
        return $this->current() !== false;
    }

    // End of iterator stuff
    /////////////////////////////////////////////////////////

}

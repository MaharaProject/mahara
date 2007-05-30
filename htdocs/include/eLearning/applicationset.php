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
require_once($CFG->docroot .'/include/eLearning/application.php');

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
?>

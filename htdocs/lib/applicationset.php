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

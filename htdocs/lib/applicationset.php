<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();
require_once(get_config('libroot') . 'application.php');

/**
 * This class is used to represent an iterator for a set of applications.
 *
 * @todo Code coverage. No phpunit or behat tests cover this.
 */
class ApplicationSet implements Iterator {

    protected $resultset                  = array();

    function __construct() {
        $this->resultset   = get_records_assoc('application');
    }

    /////////////////////////////////////////////////////////
    // Iterator stuff
    public function rewind(): void {
        reset($this->resultset);
    }

    public function current(): mixed {
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

    public function key(): mixed {
        return key($this->resultset);
    }

    public function next(): void {
        next($this->resultset);
    }

    public function valid(): bool {
        return $this->current() !== false;
    }

    // End of iterator stuff
    /////////////////////////////////////////////////////////

}

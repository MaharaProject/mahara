<?php
/**
 *
 * @package    mahara
 * @subpackage antispam
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * Base class for spam traps. Defines no evaluation schemes, so its
 * is_spam() method will always return false.
 */
class NoneSpamTrap {

    public function __construct($fields) {
        $this->fields = $fields;
    }

    public function is_spam($threshold=3) {
        // if no spam score threshold is defined, never call something spam
        if (empty($threshold)) {
            return false;
        }
        $score = 0;
        foreach ($this->fields as $field) {
            $method = 'evaluate_' . $field['type'];
            if (method_exists($this, $method)) {
                $score += $this->$method($field['value']);
            }
        }
        if ($score > $threshold) {
            return true;
        }
        return false;
    }

    public function has_blacklisted_urls($string) {
        return false;
    }
}

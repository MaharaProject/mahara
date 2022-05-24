<?php
/**
 * Trait that adds read-only slave connection capability
 * @package    mahara
 * @subpackage    module_beacon
 * @author     Kevin Pham <kevinpham@catalyst-au.net>
 * @copyright  Catalyst IT, 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace module_beacon\question;
defined('INTERNAL') || die();
trait sql_question_trait {

    /**
     * Returns a list of words to not allow when executing a sql query
     *
     * @return array
     */
    private function blocked_words_list() {
        return [
            'ALTER',
            'CREATE',
            'DELETE',
            'DROP',
            'GRANT',
            'INSERT',
            'INTO',
            'TRUNCATE',
            'UPDATE'
        ];
    }

    /**
     * Given a string, return whether or not the contents includes one of the
     * bad words that should not be executed in a query.
     *
     * @return bool
     */
    private function contains_blocked_word($string) {
        return preg_match('/\b('.implode('|', $this->blocked_words_list()).')\b/i', $string);
    }
}

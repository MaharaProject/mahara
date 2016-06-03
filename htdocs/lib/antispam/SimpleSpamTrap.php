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
require_once('NoneSpamTrap.php');

/**
 * SimpleSpamTrap implements basic checks of name, email,
 * subject, and body, but does not perform any checks that
 * require an internet connection.
 */
class SimpleSpamTrap extends NoneSpamTrap {

    protected function email_form($email) {
        // pieforms does some email validation, but it's somewhat imperfect.
        // it allows multiple @ characters, for example
        if (preg_match("/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i", $email)) {
            return true;
        }
        return false;
    }

    protected function valid_email($email) {
        return $this->email_form($email);
    }

    protected function get_urls($text) {
        preg_match_all('#(?:https?|ftp)://[^\s\'"<>()]+#S', $text, $urls);
        return $urls[0];
    }

    protected function evaluate_name($name) {
        $score = 0;
        // a name should not contain a newline character
        if (strpos($name, '\n')) {
            $score += 2;
        }
        // a name should not be a valid email
        if ($this->email_form($name)) {
            $score += 2;
        }
        return $score;
    }

    protected function evaluate_email($email) {
        $score = 0;
        if (!$this->valid_email($email)) {
            $score += 10;
        }
        return $score;
    }

    protected function evaluate_subject($subject) {
        $score = 0;
        $url_score = 0.5;
        // subject should not have a newline character
        if (strpos($subject, '\n')) {
            $score += 2;
        }
        // subject should not be a valid email
        if ($this->email_form($subject)) {
            $score += 2;
        }
        $score += $url_score * count($this->get_urls($subject));
        return $score;
    }

    protected function evaluate_body($body) {
        $score = 0;
        $url_score = 0.3;
        // each url adds url_score to the total score
        $score += $url_score * count($this->get_urls($body));
        return $score;
    }

}

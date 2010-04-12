<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2010 Catalyst IT Ltd and others; see:
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
 * @subpackage antispam
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2010 Catalyst IT Ltd http://catalyst.net.nz
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
        if (eregi("^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$", $email)) {
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

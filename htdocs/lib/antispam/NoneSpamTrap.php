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

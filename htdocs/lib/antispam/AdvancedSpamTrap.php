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
require_once('SimpleSpamTrap.php');

/**
 * AdvancedSpamTrap implements stronger checks of email,
 * subject, and body, by determining whether or not the email
 * actually exists, and checking any URLs against internet
 * blacklists. It should only be used if an internet connection
 * is available.
 */
class AdvancedSpamTrap extends SimpleSpamTrap {

    protected function valid_email($email) {
        if (!parent::valid_email($email)) {
            return false;
        }
        list($local, $domain) = explode('@', $email);

        // TODO: we could connect to the mailserver and see if the
        // email address exists. This will detect the case where a
        // nonexistant email at a valid domain is used.

        // If an MX record is not found, mail goes to the A or AAAA record
        return checkdnsrr($domain, 'MX') or checkdnsrr($domain, 'A') or checkdnsrr($domain, 'AAAA');
    }

    protected function blacklisted_url($url) {
        $blacklists = array(
            'black.uribl.com',
        );
        if (get_config('spamhaus')) {
            $blacklists[] = 'dbl.spamhaus.org';
        }
        if (get_config('surbl')) {
            $blacklists[] = 'multi.surbl.com';
        }
        // extract the hostname from the url
        preg_match('#://([^\s\'"<>()\.]*\.)*([^\s\'"<>()]+\.[a-zA-Z]+)/?#', $url, $match);
        $domain = $match[2];
        foreach ($blacklists as $bl) {
            if (checkdnsrr($domain . '.' . $bl, 'A')) {
                return $domain;
            }
        }
        return false;
    }

    protected function evaluate_body($body) {
        $score = parent::evaluate_body($body);
        $urls = $this->get_urls($body);
        foreach ($urls as $url) {
            if ($this->blacklisted_url($url)) {
                $score += 5;
            }
        }
        return $score;
    }

    // Call this when you want to know if there's a url with a
    // blacklisted domain in some text but you don't care how many
    // non-blacklisted ones there are.
    public function has_blacklisted_urls($string) {
        foreach ($this->get_urls($string) as $url) {
            if ($domain = $this->blacklisted_url($url)) {
                return $domain;
            }
        }
        return false;
    }
}

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
            $blacklists[] = 'multi.surbl.org';
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

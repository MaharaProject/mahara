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

define('PROBATION_MAX_POINTS', 10);

function available_spam_traps() {
    $results = array();
    $handle = opendir(get_config('docroot') . 'lib/antispam');
    while ($file = readdir($handle)) {
        preg_match("/(.+)SpamTrap\.php/", $file, $name);
        if ($name) {
            $results[strtolower($name[1])] = get_string($name[1], 'admin');
        }
    }

    // Addition of new traps will involve updating this ordering array.
    $order = array(1,3,2);
    array_multisort($order, $results);

    return $results;
}

function new_spam_trap($fields) {
    $spamclass = ucfirst(get_config('antispam')) . 'SpamTrap';
    require_once('antispam/' . $spamclass . '.php');
    return new $spamclass($fields);
}

function get_first_blacklisted_domain($text) {
    $spamtrap = new_spam_trap(array());
    if ($baddomain = $spamtrap->has_blacklisted_urls($text)) {
        return $baddomain;
    }
}

// windows has no checkdnsrr until PHP 5.3
if (!function_exists('checkdnsrr')) {
    function checkdnsrr($host, $type='MX') {
        if (empty($host)) {
            return false;
        }
        exec('nslookup -type=' . $type . ' ' . escapeshellcmd($host), $output);
        foreach ($output as $line) {
            if (preg_match('/^' . $host . '/', $line)) {
                return true;
            }
        }
        return false;
    }
}


/**
 * Check whether a user is on probation.
 * @param int/object $user
 * @return boolean TRUE if the user is on probation, FALSE if the user is not on probation
 */
function is_probationary_user($user = null) {
    global $USER;

    // Check whether a new user threshold is in place or not.
    if (!is_using_probation()) {
        return false;
    }

    // Get the user's information
    if (!($user instanceof User)) {
        if ($user == null) {
            $user = $USER;
        }
        else {
            $userobj = new User();
            $userobj->find_by_id($user);
            $user = $userobj;
        }
    }

    // Admins and staff get a free pass
    if ($user->get('admin') || $user->get('staff') || $user->is_institutional_admin() || $user->is_institutional_staff()) {
        return false;
    }

    // We actually store new user points in reverse. When your account is created, you get $newuserthreshold points, and
    // we decrease those when you do something good, and when it hits 0 you're no longer a new user
    // we also want to treat anonymous users as always in probation.
    $userid = $user->get('id');
    $userspoints = get_field('usr', 'probation', 'id', $userid);
    if (empty($userid) || $userspoints > 0) {
        return true;
    }
    else {
        return false;
    }
}


/**
 * Activity that "vouches" for a new user to indicate that they're a real person, should call this
 *
 * @param int $vouchforthisuserid The userid of the person being vouched for
 * @param int $vouchinguserid The userid of the person doing the vouching
 * @return boolean TRUE if we can vouch for the person, FALSE if not
 */
function vouch_for_probationary_user($probationaryuserid, $vouchinguserid = null, $points = 1) {
    global $USER;

    // Check whether we're even using this system.
    if (!is_using_probation()) {
        return true;
    }

    if ($vouchinguserid == null) {
        $vouchinguserid = $USER->get('id');
    }

    // A new user can't vouch for another new user
    if (is_probationary_user($vouchinguserid)) {
        return false;
    }

    $voucheepoints = get_field('usr', 'probation', 'id', $probationaryuserid);
    if ($voucheepoints > 0) {
        set_field('usr', 'probation', max(0, $voucheepoints - $points), 'id', $probationaryuserid);
    }

    return true;
}

/**
 * Indicates whether we're using a probation threshold
 * @return boolean
 */
function is_using_probation() {
    return (boolean) (get_config('probationenabled') && get_config('probationstartingpoints'));
}

/**
 * Check for external links and images being posted by a probationary user
 * @param string $text
 * @return BOOLEAN true if the text is okay, false if not
 */
function probation_validate_content($text) {
    if (!is_using_probation()) {
        return true;
    }
    if (!has_external_links_or_images($text)) {
        return true;
    }
    if (is_probationary_user()) {
        return false;
    }
    return true;
}

function has_external_links_or_images($text) {
    // Check to see whether the post contains any content forbidden to new users
    // (We do this first, in order to avoid any unnecessary hits to the DB
    return (boolean) preg_match('#(://)|(<a\b)#i', $text);
}

/**
 * For creating a drop-down menu to set a user's probation points.
 * @return array Suitable for use in a pieform select element's "options" attribute
 */
function probation_form_options() {
    $options = array();
    $options[0] = get_string('probationzeropoints', 'admin');
    for ($i = 1; $i <= PROBATION_MAX_POINTS; $i++ ) {
        $options[$i] = get_string('probationxpoints', 'admin', $i);
    }
    return $options;
}

/**
 * Ensures that a number is in the valid range of probation points (from 0 to PROBATION_MAX_POINTS).
 * It's used primarily in cleaning & validating user input when setting user probation points.
 *
 * @param int $points The number of probation points supplied from the UI
 * @return int A legal number of probation points
 */
function ensure_valid_probation_points($points) {
    if ($points < 0) {
        return 0;
    }
    else if ($points > PROBATION_MAX_POINTS) {
        return PROBATION_MAX_POINTS;
    }
    else {
        return (int) $points;
    }
}
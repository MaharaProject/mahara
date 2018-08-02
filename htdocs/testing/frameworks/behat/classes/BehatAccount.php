<?php
/**
 * @package    mahara
 * @subpackage test/behat
 * @author     Son Nguyen, Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  portions from mahara Behat, 2013 David Monllaó
 *
 */
require_once(__DIR__ . '/BehatBase.php');

use Behat\Behat\Context\Step\Given as Given,
    Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;

/**
 * Account steps definitions.
 *
 */
class BehatAccount extends BehatBase {

    /**
     * Sets the specified account settings to the current user.
     * A table with | Setting label | value | is expected.
     *
     * @Given /^I set the following account settings values:$/
     * @param TableNode $table
     */
    public function i_set_account_settings(TableNode $table) {
        global $USER;

        $prefs = array();
        foreach ($table->getHash() as $accountpref) {
            $prefs[$accountpref['field']] = $accountpref['value'];
        }

        // Validate the settings
        if (isset($prefs['urlid']) && get_config('cleanurls') && $prefs['urlid'] != $USER->get('urlid')) {
            if (strlen($prefs['urlid']) < 3) {
                throw new Exception("Invalid urlid: " . get_string('rule.minlength.minlength', 'pieforms', 3));
            }
            else if (record_exists('usr', 'urlid', $prefs['urlid'])) {
                throw new Exception("Invalid urlid: " . get_string('urlalreadytaken', 'account'));
            }
        }

        // Update user's account settings
        db_begin();
        // use this as looping through values is not safe.
        $expectedprefs = expected_account_preferences();
        if (isset($prefs['maildisabled']) && $prefs['maildisabled'] == 0 && get_account_preference($USER->get('id'), 'maildisabled') == 1) {
            // Reset the sent and bounce counts otherwise mail will be disabled
            // on the next send attempt
            $u = new stdClass();
            $u->email = $USER->get('email');
            $u->id = $USER->get('id');
            update_bounce_count($u,true);
            update_send_count($u,true);
        }

        // Remember the user's language & theme prefs, so we can reload the page if they change them
        $oldlang = $USER->get_account_preference('lang');
        $oldtheme = $USER->get_account_preference('theme');
        $oldgroupsideblockmaxgroups = $USER->get_account_preference('groupsideblockmaxgroups');
        $oldgroupsideblocksortby = $USER->get_account_preference('groupsideblocksortby');

        // Set user account preferences
        foreach ($expectedprefs as $eprefkey => $epref) {
            if (isset($prefs[$eprefkey]) && $prefs[$eprefkey] !== get_account_preference($USER->get('id'), $eprefkey)) {
                $USER->set_account_preference($eprefkey, $prefs[$eprefkey]);
            }
        }

        db_commit();

    }
}

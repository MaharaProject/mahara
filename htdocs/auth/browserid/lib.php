<?php
/**
 *
 * @package    mahara
 * @subpackage auth-browserid
 * @author     Francois Marier <francois@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();
require_once(get_config('docroot') . 'auth/lib.php');
require_once(get_config('docroot') . 'lib/institution.php');

class AuthBrowserid extends Auth {

    public function __construct($id = null) {
        if (!empty($id)) {
            return $this->init($id);
        }
        $this->ready = true;
        return true;
    }

    public function can_auto_create_users() {
        return false;
    }
}

class PluginAuthBrowserid extends PluginAuth {

    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        // Find out how many active users there are, with which instances,
        // in which institutions.
        $instances = get_records_sql_array(
            'SELECT
                i.displayname as displayname,
                i.name as name,
                (
                    SELECT COUNT(*)
                    FROM {usr} u
                    WHERE
                        u.authinstance = ai.id
                        AND deleted = 0
                ) AS numusers
            FROM
                {auth_instance} ai
                INNER JOIN {institution} i
                    ON ai.institution = i.name
            WHERE
                ai.authname=\'browserid\'
            ORDER BY
                i.displayname
            '
        );

        $elements = array();
        $elements['helptext'] = array(
            'type' => 'html',
            'value' => get_string('deprecatedmsg1', 'auth.browserid')
        );

        if ($instances) {
            $smarty = smarty_core();
            $smarty->assign('instances', $instances);
            $tablehtml = $smarty->fetch('auth:browserid:statustable.tpl');
            $elements['statustable'] = array(
                'type' => 'html',
                'value' => $tablehtml
            );

            $elements['migrate'] = array(
                'type' => 'switchbox',
                'title' => get_string('migratetitle', 'auth.browserid'),
                'description' => get_string('migratedesc1', 'auth.browserid'),
                'defaultvalue' => false,
                'help' => true,
            );
        }
        else {
            $elements['noaction'] = array(
                'type' => 'html',
                'value' => get_string('nobrowseridinstances', 'auth.browserid')
            );
        }

        $form = array(
            'elements' => $elements
        );
        if ($instances) {
            $form['elements']['js'] = array(
                'type' => 'html',
                'value' => <<<HTML
<script type="text/javascript">
if (typeof auth_browserid_reload_page === "undefined") {
    var auth_browserid_reload_page = function() {
        window.location.reload(true);
    }
}
</script>
HTML
            );
            $form['jssuccesscallback'] = 'auth_browserid_reload_page';
        }
        return $form;
    }

    public static function save_config_options(Pieform $form, $values) {
        if (!empty($values['migrate'])) {
            $instances = get_records_array('auth_instance', 'authname', 'browserid', 'id');
            foreach ($instances as $authinst) {
                // Are there any users with this auth instance?
                if (record_exists('usr', 'authinstance', $authinst->id)) {

                    // Find the internal auth instance for this institution
                    $internal = get_field('auth_instance', 'id', 'authname', 'internal', 'institution', $authinst->institution);
                    if (!$internal) {
                        // Institution has no internal auth instance. Create one.
                        $todb = new stdClass();
                        $todb->instancename = 'internal';
                        $todb->authname = 'internal';
                        $todb->institution = $authinst->institution;
                        $todb->priority = $authinst->priority;
                        $internal = insert_record('auth_instance', $todb, 'id', true);
                    }

                    // Set the password & salt for Persona users to "*", which means "no password set"
                    update_record(
                        'usr',
                        (object)array(
                            'password' => '*',
                            'salt' => '*'
                        ),
                        array(
                            'authinstance' => $authinst->id
                        )
                    );
                    set_field('usr', 'authinstance', $internal, 'authinstance', $authinst->id);
                }

                // Delete the Persona auth instance
                delete_records('auth_remote_user', 'authinstance', $authinst->id);
                delete_records('auth_instance_config', 'instance', $authinst->id);
                delete_records('auth_instance', 'id', $authinst->id);
                // Make it no longer be the parent authority to any auth instances
                delete_records('auth_instance_config', 'field', 'parent', 'value', $authinst->id);
            }
            set_field('auth_installed', 'active', 0, 'name', 'browserid');
        }
    }

    public static function has_instance_config() {
        return false;
    }

    /**
     * Implement the function is_usable()
     *
     * @return boolean true if the BrowserID verifier is usable, false otherwise
     */
    public static function is_usable() {
        return false;
    }

    public static function postinst($fromversion) {
        // Always deactivate this plugin, if it has been activated somehow.
        set_field('auth_installed', 'active', 0, 'name', 'browserid');
    }

    public static function can_be_disabled() {
        return true;
    }

    public static function is_deprecated() {
        return true;
    }
}

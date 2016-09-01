<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();
require_once(get_config('docroot') . 'auth/internal/lib.php');

$path = get_config('docroot') . 'webservice/libs/zend';
set_include_path($path . PATH_SEPARATOR . get_include_path());

require_once(get_config('docroot') . '/webservice/lib.php');
require_once(get_config('docroot') . 'api/xmlrpc/lib.php');

/**
 * The webservice authentication method, which authenticates users against the
 * Mahara database, but ensures that these users can only be used for webservices
 */
class AuthWebservice extends AuthInternal {

    public function __construct($id = null) {
        $this->has_instance_config = false;
        $this->type       = 'webservice';
        if (!empty($id)) {
            return $this->init($id);
        }
        return true;
    }

    /**
     * Attempt to authenticate user
     *
     * @param object $user     As returned from the usr table
     * @param string $password The password being used for authentication
     * @return bool            True/False based on whether the user
     *                         authenticated successfully
     * @throws AuthUnknownUserException If the user does not exist
     */
    public function authenticate_user_account($user, $password, $from='elsewhere') {
        // deny from anywhere other than a webservice context
        if ($from != 'webservice') {
            return false;
        }
        $this->must_be_ready();
        return $this->validate_password($password, $user->password, $user->salt);
    }

    /**
     * Given a password that the user has sent, the password we have for them
     * and the salt we have, see if the password they sent is correct.
     *
     * @param string $theysent The password the user sent
     * @param string $wehave   The password we have in the database for them
     * @param string $salt     The salt we have.
     */
    protected function validate_password($theysent, $wehave, $salt) {
        $this->must_be_ready();
        $validate = parent::validate_password($theysent, $wehave, $salt);
        return (!empty($validate)) ? true : false;
    }
}

/**
 * Plugin configuration class
 */
class PluginAuthWebservice extends PluginAuth {

    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        redirect('/webservice/admin/index.php');
    }

    public static function has_instance_config() {
        return false;
    }

    public static function get_instance_config_options($institution, $instance = 0) {
        return array();
    }

    public static function admin_menu_items() {

        if (!is_plugin_active('webservice', 'auth')) {
            return array();
        }

        $map = array(
            'webservices' => array(
                'path'   => 'webservices',
                'url'    => 'webservice/admin/index.php',
                'title'  => get_string('webservice', 'auth.webservice'),
                'weight' => 70,
                'accesskey' => 'w',
            ),
            'webservices/config' => array(
                'path'   => 'webservices/config',
                'url'    => 'webservice/admin/index.php',
                'title'  => get_string('webservicesconfig', 'auth.webservice'),
                'weight' => 10,
            ),
            'webservices/oauthconfig' => array(
                'path'   => 'webservices/oauthconfig',
                'url'    => 'webservice/admin/oauthv1sregister.php',
                'title'  => get_string('oauth', 'auth.webservice'),
                'weight' => 40,
            ),
            'webservices/logs' => array(
                'path'   => 'webservices/logs',
                'url'    => 'webservice/admin/webservicelogs.php',
                'title'  => get_string('webservicelogsnav', 'auth.webservice'),
                'weight' => 50,
            ),
            'webservices/testclient' => array(
                'path'   => 'webservices/testclient',
                'url'    => 'webservice/testclient.php',
                'title'  => get_string('testclientnav', 'auth.webservice'),
                'weight' => 60,
            ),
            'webservices/apps' => array(
                'path'   =>  'webservices/apps',
                'url'    => 'webservice/apptokens.php',
                'title'  => get_string('apptokens', 'auth.webservice'),
                'weight' => 20,
            ),
            'webservices/connections' => array(
                'path'   =>  'webservices/connections',
                'url'    => 'webservice/admin/connections.php',
                'title'  => get_string('connections', 'auth.webservice'),
                'weight' => 30,
            ),
        );

        if (defined('MENUITEM') && isset($map[MENUITEM])) {
            $map[MENUITEM]['selected'] = true;
        }

        return $map;
    }

    /*
    * cron cleanup service for web service logs
    * set this to go daily at 5 past 1
    */
    public static function get_cron() {
        return array(
            (object)array(
                    'callfunction' => 'clean_webservice_logs',
                    'hour'         => '01',
                    'minute'       => '05',
            ),
        );
    }

    /**
     * The web services cron callback
     * clean out the old records that are N seconds old
     */
    public static function clean_webservice_logs() {
        $LOG_AGE = 8 * 24 * 60 * 60; // 8 days
        delete_records_select('external_services_logs', 'timelogged < ?', array(time() - $LOG_AGE));
    }

    public static function postinst($prevversion) {

        if ($prevversion == 0) {
        // force the upgrade to get the intial services loaded
            external_reload_webservices();
            // Install a cron job to clean webservices logs
            if (!get_record('cron', 'callfunction', 'webservice_clean_webservice_logs')) {
                $cron = new StdClass;
                $cron->callfunction = 'webservice_clean_webservice_logs';
                $cron->minute       = '5';
                $cron->hour         = '01';
                $cron->day          = '*';
                $cron->month        = '*';
                $cron->dayofweek    = '*';
                insert_record('cron', $cron);
            }

            // activate webservices
            foreach (array('soap', 'xmlrpc', 'rest', 'oauth') as $proto) {
                set_config('webservice_provider_' . $proto.'_enabled', 1);
            }
        }
    }
}

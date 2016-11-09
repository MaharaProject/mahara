<?php
/**
 *
 * @package    mahara
 * @subpackage auth-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();
require_once(get_config('docroot') . 'auth/lib.php');

/**
 * The internal authentication method, which authenticates users against the
 * Mahara database.
 */
class AuthImap extends Auth {

    public function __construct($id = null) {
        $this->type                         = 'imap';
        $this->has_instance_config                   = true;

        $this->config['host']               = '';
        $this->config['port']               = '143';
        $this->config['protocol']           = '/imap';
        $this->config['domainname']  = '';
        $this->config['changepasswordurl']  = '';
        $this->config['weautocreateusers']  = '';

        if (!empty($id)) {
            return $this->init($id);
        }
        return true;
    }

    public function init($id = null) {

        $this->ready = parent::init($id);

        // Check that required fields are set
        if ( empty($this->config['host']) ||
             empty($this->config['port']) ||
             empty($this->config['protocol']) ) {
            $this->ready = false;
        }

        return $this->ready;

    }

    /**
     * Attempt to authenticate user
     *
     * @param string $username The username to authenticate with
     * @param string $password The password being used for authentication
     * @return bool            True/False based on whether the user
     *                         authenticated successfully
     * @throws AuthUnknownUserException If the user does not exist
     */
    public function authenticate_user_account($user, $password) {
        $this->must_be_ready();

        if (! function_exists('imap_open')) {
            throw new ConfigException('IMAP is not available in your PHP environment. Check that it is properly installed');
        }

        $connectionstring = '{'. $this->config['host'] .':'. $this->config['port'] . $this->config['protocol'] .'}';
        try {

            if (isset($this->config['domainname']) && $this->config['domainname'] != '') {
                // Check whether the end of the user's email address matches the specified domain name
                $domainpart = '@' . strtolower($this->config['domainname']);
                if (strtolower(substr($user->username, -1 * strlen($domainpart))) != $domainpart) {
                    return false;
                }
            }

            $connection = imap_open($connectionstring, $user->username, $password, OP_HALFOPEN);
            if ($connection) {
                imap_close($connection);
                return true;
            }
        } catch (Exception $e) {
            throw new ConfigException('Unable to connect to server with connection string: '.$connectionstring);
        }

        return false;  // No match
    }

    /**
     * Whether to auto-create users. (The only information that IMAP exports is the user's
     * email address; but this is no different from Persona/Browserid.)
     *
     * @return bool
     */
    public function can_auto_create_users() {
        return (bool)$this->config['weautocreateusers'];
    }


    /**
     * Get basic user info to create new users
     * Needed if can_auto_create_users comes back true
     *
     * @param string $username The username to look up information for
     * @return array           The information for the user
     * @throws AuthUnknownUserException If the user is unknown to the
     *                                  authentication method
     */
    public function get_user_info($username) {
        $userinfo = array('email' => $username);
        return (object)$userinfo;
    }

}

/**
 * Plugin configuration class
 */
class PluginAuthImap extends PluginAuth {

    private static $default_config = array('host'=>'', 'port'=>'143', 'protocol'=>'/imap', 'domainname'=>'', 'changepasswordurl'=>'', 'weautocreateusers'=>'', 'active' => 1);

    public static function has_config() {
        return false;
    }

    public static function get_config_options() {
        return array();
    }

    public static function has_instance_config() {
        return true;
    }

    public static function is_usable() {
        return extension_loaded('imap');
    }

    public static function get_instance_config_options($institution, $instance = 0) {
        // TODO: put these strings in a lang file
        $options['/imap'] = 'IMAP';
        $options['/imap/ssl'] = 'IMAP/SSL';
        $options['/imap/ssl/novalidate-cert'] = 'IMAP/SSL (self-signed certificate)';
        $options['/imap/tls'] = 'IMAP/TLS';

        if ($instance > 0) {
            $current        = get_records_array('auth_instance',        'id',       $instance, 'priority ASC');
            if ($current == false) {
                throw new SystemException('Could not find data for auth instance '.$instance);
            }
            $default = $current[0];
            $current_config = get_records_menu('auth_instance_config', 'instance', $instance, '', 'field, value');

            if ($current_config == false) {
                $current_config = array();
            }

            foreach (self::$default_config as $key => $value) {
                if (array_key_exists($key, $current_config)) {
                    self::$default_config[$key] = $current_config[$key];
                }
            }
            self::$default_config['active'] = $default->active;
        } else {
            $default = new stdClass();
            $default->instancename = '';
        }

        $elements['instancename'] = array(
            'type' => 'text',
            'title' => get_string('authname','auth'),
            'rules' => array(
                'required' => true
            ),
            'defaultvalue' => $default->instancename
        );

        $elements['active'] = array(
            'type'  => 'switchbox',
            'title' => get_string('active', 'auth'),
            'defaultvalue' => (int) self::$default_config['active'],
        );

        $elements['instance'] = array(
            'type' => 'hidden',
            'value' => $instance
        );

        $elements['institution'] = array(
            'type' => 'hidden',
            'value' => $institution
        );

        $elements['authname'] = array(
            'type' => 'hidden',
            'value' => 'imap'
        );

        $elements['host'] = array(
            'type' => 'text',
            'title' => get_string('host', 'auth'),
            'rules' => array(
                'required' => true
            ),
            'defaultvalue' => self::$default_config['host']
        );

        $elements['port'] = array(
            'type' => 'text',
            'title' => get_string('port', 'auth'),
            'rules' => array(
                'required' => true,
                'integer' => true
            ),
            'defaultvalue' => self::$default_config['port']
        );

        $elements['protocol'] = array(
            'type' => 'select',
            'title' => get_string('protocol', 'auth'),
            'options' => $options,
            'rules' => array(
                'required' => true
            ),
            'defaultvalue' => self::$default_config['protocol']
        );

        $elements['domainname'] = array(
            'type' => 'text',
            'title' => get_string('domainname', 'auth.imap'),
            'rules' => array(
                'required' => false
            ),
            'defaultvalue' => self::$default_config['domainname'],
            'help'  => true,
        );

        $elements['changepasswordurl'] = array(
            'type' => 'text',
            'title' => get_string('changepasswordurl', 'auth'),
            'rules' => array(
                'required' => false
            ),
            'defaultvalue' => self::$default_config['changepasswordurl']
        );

        $elements['weautocreateusers'] = array(
            'type'  => 'checkbox',
            'title' => get_string('weautocreateusers', 'auth'),
            'defaultvalue' => self::$default_config['weautocreateusers'],
            'help'  => true,
        );

        return array(
            'elements' => $elements,
            'renderer' => 'div'
        );
    }

    public static function save_instance_config_options($values, Pieform $form) {

        $authinstance = new stdClass();

        if ($values['instance'] > 0) {
            $values['create'] = false;
            $current = get_records_assoc('auth_instance_config', 'instance', $values['instance'], '', 'field, value');
            $authinstance->id = $values['instance'];
        } else {
            $values['create'] = true;

            // Get the auth instance with the highest priority number (which is
            // the instance with the lowest priority).
            // TODO: rethink 'priority' as a fieldname... it's backwards!!
            $lastinstance = get_records_array('auth_instance', 'institution', $values['institution'], 'priority DESC', '*', '0', '1');

            if ($lastinstance == false) {
                $authinstance->priority = 0;
            } else {
                $authinstance->priority = $lastinstance[0]->priority + 1;
            }
        }

        $authinstance->instancename = $values['instancename'];
        $authinstance->institution  = $values['institution'];
        $authinstance->authname     = $values['authname'];
        $authinstance->active       = $values['active'];

        if ($values['create']) {
            $values['instance'] = insert_record('auth_instance', $authinstance, 'id', true);
        } else {
            update_record('auth_instance', $authinstance, array('id' => $values['instance']));
        }

        if (empty($current)) {
            $current = array();
        }

        self::$default_config =   array('host'              => $values['host'],
                                        'port'              => $values['port'],
                                        'protocol'          => $values['protocol'],
                                        'domainname'        => $values['domainname'],
                                        'changepasswordurl' => $values['changepasswordurl'],
                                        'weautocreateusers' => $values['weautocreateusers'],
                                        );

        foreach(self::$default_config as $field => $value) {
            $record = new stdClass();
            $record->instance = $values['instance'];
            $record->field    = $field;
            $record->value    = $value;

            if ($values['create'] || !array_key_exists($field, $current)) {
                insert_record('auth_instance_config', $record);
            } else {
                update_record('auth_instance_config', $record, array('instance' => $values['instance'], 'field' => $field));
            }
        }

        return $values;
    }
}

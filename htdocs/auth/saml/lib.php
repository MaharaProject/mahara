<?php
/**
 *
 * @package    mahara
 * @subpackage auth-saml
 * @author     Piers Harding <piers@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) portions from Moodle, (C) Martin Dougiamas http://dougiamas.com
 */

defined('INTERNAL') || die();
require_once(get_config('docroot') . 'auth/lib.php');

/**
 * Authenticates users with SAML 2.0
 */
class AuthSaml extends Auth {

    public function __construct($id = null) {
        $this->type = 'saml';
        $this->has_instance_config = true;

        $this->config['simplesamlphplib'] = get_config_plugin('auth', 'saml', 'simplesamlphplib');
        $this->config['simplesamlphpconfig'] = get_config_plugin('auth', 'saml', 'simplesamlphpconfig');
        $this->config['user_attribute'] = '';
        $this->config['weautocreateusers'] = 1;
        $this->config['firstnamefield' ] = '';
        $this->config['surnamefield'] = '';
        $this->config['emailfield'] = '';
        $this->config['institutionattribute'] = '';
        $this->config['institutionregex'] = 0;
        $this->config['institutionvalue'] = '';
        $this->config['updateuserinfoonlogin'] = 1;
        $this->config['remoteuser'] = true;
        $this->config['loginlink'] = false;
        $this->instanceid = $id;

        if (!empty($id)) {
            return $this->init($id);
        }
        return true;
    }

    public function init($id = null) {
        $this->ready = parent::init($id);

        // Check that required fields are set
        if ( empty($this->config['user_attribute']) ||
             empty($this->config['institutionattribute'])
              ) {
            $this->ready = false;
        }

        return $this->ready;
    }


    /**
     * We can autocreate users if the admin has said we can
     * in weautocreateusers
     */
    public function can_auto_create_users() {
        return (bool)$this->config['weautocreateusers'];
    }


    /**
     * Grab a delegate object for auth stuff
     */
    public function request_user_authorise($attributes) {
        global $USER, $SESSION;
        $this->must_be_ready();

        if (empty($attributes) or !array_key_exists($this->config['user_attribute'], $attributes)
                               or !array_key_exists($this->config['institutionattribute'], $attributes)) {
            throw new AccessDeniedException();
        }

        $remoteuser      = $attributes[$this->config['user_attribute']][0];
        $firstname       = isset($attributes[$this->config['firstnamefield']][0]) ? $attributes[$this->config['firstnamefield']][0] : null;
        $lastname        = isset($attributes[$this->config['surnamefield']][0]) ? $attributes[$this->config['surnamefield']][0] : null;
        $email           = isset($attributes[$this->config['emailfield']][0]) ? $attributes[$this->config['emailfield']][0] : null;
        $institutionname = $this->institution;

        $create = false;
        $update = false;

        // Retrieve a $user object. If that fails, create a blank one.
        try {
            $isremote = $this->config['remoteuser'] ? true : false;
            $user = new User;
            if (get_config('usersuniquebyusername')) {
                // When turned on, this setting means that it doesn't matter
                // which other application the user SSOs from, they will be
                // given the same account in Mahara.
                //
                // This setting is one that has security implications unless
                // only turned on by people who know what they're doing. In
                // particular, every system linked to Mahara should be making
                // sure that same username == same person.  This happens for
                // example if two Moodles are using the same LDAP server for
                // authentication.
                //
                // If this setting is on, it must NOT be possible to self
                // register on the site for ANY institution - otherwise users
                // could simply pick usernames of people's accounts they wished
                // to steal.
                if ($institutions = get_column('institution', 'name', 'registerallowed', '1')) {
                    log_warn("usersuniquebyusername is turned on but registration is allowed for an institution. "
                        . "No institution can have registration allowed for it, for security reasons.\n"
                        . "The following institutions have registration enabled:\n  " . join("\n  ", $institutions));
                    throw new AccessDeniedException();
                }

                if (!get_config('usersallowedmultipleinstitutions')) {
                    log_warn("usersuniquebyusername is turned on but usersallowedmultipleinstitutions is off. "
                        . "This makes no sense, as users will then change institution every time they log in from "
                        . "somewhere else. Please turn this setting on in Site Options");
                    throw new AccessDeniedException();
                }
            }
            else {
                if (!$isremote){
                    log_warn("usersuniquebyusername is turned off but remoteuser has not been set on for this institution: $institutionname. "
                        . "This is a security risk as users from different institutions with different IdPs can hijack "
                        . "each others accounts.  Fix this in the institution level auth/saml settings.");
                    throw new AccessDeniedException();
                }
            }
            if ($isremote) {
                $user->find_by_instanceid_username($this->instanceid, $remoteuser, $isremote);
            }
            else {
                $user->find_by_username($remoteuser);
            }

            if ($user->get('suspendedcusr')) {
                die_info(get_string('accountsuspended', 'mahara', strftime(get_string('strftimedaydate'), $user->get('suspendedctime')), $user->get('suspendedreason')));
            }

            if ('1' == $this->config['updateuserinfoonlogin']) {
                $update = true;
            }
        } catch (AuthUnknownUserException $e) {
            if (!empty($this->config['weautocreateusers'])) {
                $institution = new Institution($this->institution);
                if ($institution->isFull()) {
                    $institution->send_admin_institution_is_full_message();
                    throw new XmlrpcClientException('SSO attempt from ' . $institution->displayname . ' failed - institution is full');
                }
                $user = new User;
                $create = true;
            }
            else {
                log_debug("User authorisation request from SAML failed - "
                    . "remote user '$remoteuser' is unknown to us and auto creation of users is turned off");
                return false;
            }
        }

        /*******************************************/

        if ($create) {

            $user->passwordchange     = 1;
            $user->active             = 1;
            $user->deleted            = 0;

            $user->expiry             = null;
            $user->expirymailsent     = 0;
            $user->lastlogin          = time();

            $user->firstname          = $firstname;
            $user->lastname           = $lastname;
            $user->email              = $email;

            // must have these values
            if (empty($firstname) || empty($lastname) || empty($email)) {
                throw new AccessDeniedException(get_string('errormissinguserattributes1', 'auth.saml', get_config('sitename')));
            }

            $user->authinstance       = empty($this->config['parent']) ? $this->instanceid : $this->parent;

            db_begin();
            $user->username           = get_new_username($remoteuser);

            $user->id = create_user($user, array(), $institutionname, $this, $remoteuser);

            /*
             * We need to convert the object to a stdclass with its own
             * custom method because it uses overloaders in its implementation
             * and its properties wouldn't be visible to a simple cast operation
             * like (array)$user
             */
            $userobj = $user->to_stdclass();
            $userarray = (array)$userobj;
            db_commit();

            // Now we have fired the create event, we need to re-get the data
            // for this user
            $user = new User;
            $user->find_by_id($userobj->id);

            if (get_config('usersuniquebyusername')) {
                // Add them to the institution they have SSOed in by
                $user->join_institution($institutionname);
            }

        } elseif ($update) {
            if (! empty($firstname)) {
                set_profile_field($user->id, 'firstname', $firstname);
                $user->firstname = $firstname;
            }
            if (! empty($lastname)) {
                set_profile_field($user->id, 'lastname', $lastname);
                $user->lastname = $lastname;
            }
            if (! empty($email)) {
                set_profile_field($user->id, 'email', $email);
                $user->email = $email;
            }
            $user->lastlastlogin      = $user->lastlogin;
            $user->lastlogin          = time();
        }
        $user->commit();


        /*******************************************/

        // We know who our user is now. Bring em back to life.
        $result = $USER->reanimate($user->id, $this->instanceid);
        log_debug("remote user '$remoteuser' is now reanimated as '{$USER->username}' ");
        $SESSION->set('authinstance', $this->instanceid);

        return true;
    }

    // ensure that a user is logged out of mahara and the SAML 2.0 IdP
    public function logout() {
        global $CFG, $USER, $SESSION;

        // logout of mahara
        $USER->logout();

        // tidy up the session for retries
        $SESSION->set('messages', array());
        $SESSION->set('wantsurl', null);

        // redirect for logout of SAML 2.0 IdP
        redirect($CFG->wwwroot.'/auth/saml/index.php?logout=1');
    }

    public function after_auth_setup_page_hook() {
        return;
    }

    public function needs_remote_username() {
        return $this->config['remoteuser'] || parent::needs_remote_username();
    }
}

/**
 * Plugin configuration class
 */
class PluginAuthSaml extends PluginAuth {

    private static $default_config = array(
//        'idpidentity'           => '',
        'simplesamlphplib'      => '',
        'simplesamlphpconfig'   => '',
        'user_attribute'        => '',
        'weautocreateusers'     => 0,
        'firstnamefield'        => '',
        'surnamefield'          => '',
        'emailfield'            => '',
        'updateuserinfoonlogin' => 1,
        'institution'           => '',
        'institutionattribute'  => '',
        'institutionvalue'      => '',
        'institutionregex'      => 0,
        'remoteuser'            => 1,
        'loginlink'             => 0,
            );

    public static function can_be_disabled() {
        return true;
    }

    public static function has_config() {
        return true;
    }

    public static function get_config_options() {

        $elements = array(
            'authname' => array(
                'type'  => 'hidden',
                'value' => 'saml',
            ),
            'authglobalconfig' => array(
                'type'  => 'hidden',
                'value' => 'saml',
            ),
            'simplesamlphplib' => array(
                'type'  => 'text',
                'size' => 50,
                'title' => get_string('simplesamlphplib', 'auth.saml'),
                'rules' => array(
                    'required' => true,
                ),
                'defaultvalue' => get_config_plugin('auth', 'saml', 'simplesamlphplib'),
                'help'  => true,
            ),
            'simplesamlphpconfig' => array(
                'type'  => 'text',
                'size' => 50,
                'title' => get_string('simplesamlphpconfig', 'auth.saml'),
                'rules' => array(
                    'required' => true,
                ),
                'defaultvalue' => get_config_plugin('auth', 'saml', 'simplesamlphpconfig'),
                'help'  => true,
            ),
        );

        return array(
            'elements' => $elements,
            'renderer' => 'table'
        );
    }

    public static function has_instance_config() {
        return true;
    }

    public static function is_usable() {
        // would be good to be able to detect SimpleSAMLPHP libraries
        return true;
    }

    public static function get_instance_config_options($institution, $instance = 0) {

        if ($instance > 0) {
            $default = get_record('auth_instance', 'id', $instance);
            if ($default == false) {
                throw new SystemException('Could not find data for auth instance ' . $instance);
            }
            $current_config = get_records_menu('auth_instance_config', 'instance', $instance, '', 'field, value');

            if ($current_config == false) {
                $current_config = array();
            }

            foreach (self::$default_config as $key => $value) {
                if (array_key_exists($key, $current_config)) {
                    self::$default_config[$key] = $current_config[$key];
                }
            }
            if(empty(self::$default_config['institutionvalue'])) {
                self::$default_config['institutionvalue'] = $institution;
            }
        } else {
            $default = new stdClass();
            $default->instancename = '';
        }

        $elements = array(
            'instance' => array(
                'type'  => 'hidden',
                'value' => $instance,
            ),
            'instancename' => array(
                'type'  => 'hidden',
                'value' => 'SAML',
            ),
            'institution' => array(
                'type'  => 'hidden',
                'value' => $institution,
            ),
            'authname' => array(
                'type'  => 'hidden',
                'value' => 'saml',
            ),
            'institutionattribute' => array(
                'type'  => 'text',
                'title' => get_string('institutionattribute', 'auth.saml', $institution),
                'rules' => array(
                    'required' => true,
                ),
                'defaultvalue' => self::$default_config['institutionattribute'],
                'help' => true,
            ),
            'institutionvalue' => array(
                'type'  => 'text',
                'title' => get_string('institutionvalue', 'auth.saml'),
                'rules' => array(
                'required' => true,
                ),
                'defaultvalue' => self::$default_config['institutionvalue'],
                'help' => true,
            ),
            'institutionregex' => array(
                'type'  => 'checkbox',
                'title' => get_string('institutionregex', 'auth.saml'),
                'defaultvalue' => self::$default_config['institutionregex'],
                'help' => true,
            ),
            'user_attribute' => array(
                'type'  => 'text',
                'title' => get_string('userattribute', 'auth.saml'),
                'rules' => array(
                    'required' => true,
                ),
                'defaultvalue' => self::$default_config['user_attribute'],
                'help' => true,
            ),
            'remoteuser' => array(
                'type'  => 'checkbox',
                'title' => get_string('remoteuser', 'auth.saml'),
                'defaultvalue' => self::$default_config['remoteuser'],
                'help'  => true,
            ),
            'loginlink' => array(
                'type'  => 'checkbox',
                'title' => get_string('loginlink', 'auth.saml'),
                'defaultvalue' => self::$default_config['loginlink'],
                'disabled' => (self::$default_config['remoteuser'] ? false : true),
                'help'  => true,
            ),
            'updateuserinfoonlogin' => array(
                'type'  => 'checkbox',
                'title' => get_string('updateuserinfoonlogin', 'auth.saml'),
                'defaultvalue' => self::$default_config['updateuserinfoonlogin'],
                'help'  => true,
            ),
            'weautocreateusers' => array(
                'type'  => 'checkbox',
                'title' => get_string('weautocreateusers', 'auth.saml'),
                'defaultvalue' => self::$default_config['weautocreateusers'],
                'help'  => true,
            ),
            'firstnamefield' => array(
                'type'  => 'text',
                'title' => get_string('samlfieldforfirstname', 'auth.saml'),
                'defaultvalue' => self::$default_config['firstnamefield'],
                'help'  => true,
            ),
            'surnamefield' => array(
                'type'  => 'text',
                'title' => get_string('samlfieldforsurname', 'auth.saml'),
                'defaultvalue' => self::$default_config['surnamefield'],
                'help'  => true,
            ),
            'emailfield' => array(
                'type'  => 'text',
                'title' => get_string('samlfieldforemail', 'auth.saml'),
                'defaultvalue' => self::$default_config['emailfield'],
                'help' => true,
            ),
        );

        return array(
            'elements' => $elements,
            'renderer' => 'table'
        );
    }

    public static function validate_config_options(Pieform $form, $values) {
        // SimpleSAMLPHP lib directory must have right things
        if (!file_exists($values['simplesamlphplib'] . '/lib/_autoload.php')) {
            $form->set_error('simplesamlphplib', get_string('errorbadlib', 'auth.saml', $values['simplesamlphplib']));
        }
        // SimpleSAMLPHP config directory must shape up
        if (!file_exists($values['simplesamlphpconfig'] . '/config.php')) {
            $form->set_error('simplesamlphpconfig', get_string('errorbadconfig', 'auth.saml', $values['simplesamlphpconfig']));
        }
    }

    public static function validate_instance_config_options($values, $form) {

        // only allow remoteuser to be unset if usersuniquebyusername is NOT set
        if (!get_config('usersuniquebyusername') && !$values['remoteuser']) {
            $form->set_error('remoteuser', get_string('errorremoteuser', 'auth.saml'));
        }
        if ($values['weautocreateusers'] && $values['remoteuser']) {
            $form->set_error('weautocreateusers', get_string('errorbadcombo', 'auth.saml'));
        }

        // Autocreation cannot be enabled unless no institutions have registration enabled.
        // This seems like a weird rule, but consider the following:
        // - weautocreateusers = 1 requires remoteuser = 0 (from the test immediately above this one)
        // - remoteuser = 0 requires usersuniquebyusername = 1 (from the test above that)
        // - usersuniquebyusername = 1 requires registerallowed = 0 on all institutions
        //   (for security reasons - see the comments in the request_user_authorise function above).
        // So weautocreateusers = 1 requires registerallowed = 0 on all institutions, and we might
        // as well display an error to that effect right away, without forcing the user to enable
        // usersuniquebyusername.
        if (($institutions = get_column('institution', 'name', 'registerallowed', '1')) && ($values['weautocreateusers'])) {
            $form->set_error('weautocreateusers', get_string('errorregistrationenabledwithautocreate', 'auth.saml'));
        }
        $dup = get_records_sql_array('SELECT COUNT(instance) AS instance FROM {auth_instance_config}
                                          WHERE ((field = \'institutionattribute\' AND value = ?) OR
                                                 (field = \'institutionvalue\' AND value = ?)) AND
                                                 instance IN (SELECT id FROM {auth_instance} WHERE authname = \'saml\' AND id != ?)
                                          GROUP BY instance
                                          ORDER BY instance',
                                      array($values['institutionattribute'], $values['institutionvalue'], $values['instance']));
        if (is_array($dup)) {
            foreach ($dup as $instance) {
                if ($instance->instance >= 2) {
                    // we already have an authinstance with these same values
                    $form->set_error('institutionattribute', get_string('errorbadinstitutioncombo', 'auth.saml'));
                    break;
                }
            }
        }
    }

    public static function save_config_options($form, $values) {
        $configs = array('simplesamlphplib', 'simplesamlphpconfig');
        foreach ($configs as $config) {
            set_config_plugin('auth', 'saml', $config, $values[$config]);
        }
    }

    public static function save_instance_config_options($values, $form) {

        $authinstance = new stdClass();

        if ($values['instance'] > 0) {
            $values['create'] = false;
            $current = get_records_assoc('auth_instance_config', 'instance', $values['instance'], '', 'field, value');
            $authinstance->id = $values['instance'];
        }
        else {
            $values['create'] = true;
            $lastinstance = get_records_array('auth_instance', 'institution', $values['institution'], 'priority DESC', '*', '0', '1');

            if ($lastinstance == false) {
                $authinstance->priority = 0;
            }
            else {
                $authinstance->priority = $lastinstance[0]->priority + 1;
            }
        }

        $authinstance->institution  = $values['institution'];
        $authinstance->authname     = $values['authname'];
        $authinstance->instancename = $values['authname'];

        if ($values['create']) {
            $values['instance'] = insert_record('auth_instance', $authinstance, 'id', true);
        }
        else {
            update_record('auth_instance', $authinstance, array('id' => $values['instance']));
        }

        if (empty($current)) {
            $current = array();
        }

        self::$default_config = array(
            'user_attribute' => $values['user_attribute'],
            'weautocreateusers' => $values['weautocreateusers'],
            'loginlink' => $values['loginlink'],
            'remoteuser' => $values['remoteuser'],
            'firstnamefield' => $values['firstnamefield'],
            'surnamefield' => $values['surnamefield'],
            'emailfield' => $values['emailfield'],
            'updateuserinfoonlogin' => $values['updateuserinfoonlogin'],
            'institutionattribute' => $values['institutionattribute'],
            'institutionvalue' => $values['institutionvalue'],
            'institutionregex' => $values['institutionregex'],
        );

        foreach(self::$default_config as $field => $value) {
            $record = new stdClass();
            $record->instance = $values['instance'];
            $record->field    = $field;
            $record->value    = $value;

            if ($values['create'] || !array_key_exists($field, $current)) {
                insert_record('auth_instance_config', $record);
            }
            else {
                update_record('auth_instance_config', $record, array('instance' => $values['instance'], 'field' => $field));
            }
        }

        $configs = array('simplesamlphplib', 'simplesamlphpconfig');
        foreach ($configs as $config) {
            self::$default_config[$config] = get_config_plugin('auth', 'saml', $config);
        }
        return $values;
    }

    /**
     * Add "SSO Login" link below the normal login form.
     */
    public static function login_form_elements() {
        $elements = array(
            'loginsaml' => array(
                'value' => '<div class="login-externallink"><a class="btn" href="' . get_config('wwwroot') . 'auth/saml/index.php">' . get_string('login', 'auth.saml') . '</a></div>'
            )
        );
        return $elements;
    }

    public static function need_basic_login_form() {
        return false;
    }
}

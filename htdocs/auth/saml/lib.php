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

    public static function get_metadata_path() {
        check_dir_exists(get_config('dataroot') . 'metadata/');
        return get_config('dataroot') . 'metadata/';
    }

    public static function get_certificate_path() {
        check_dir_exists(get_config('dataroot') . 'certificate/');
        return get_config('dataroot') . 'certificate/';
    }

    public function __construct($id = null) {
        $this->type = 'saml';
        $this->has_instance_config = true;

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
        $this->config['institutionidp'] = '';
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
            $user->username           = get_new_username($remoteuser, 40);

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

    private static function create_certificates($numberofdays = 3650) {
        global $CFG;
        // Get the details of the first site admin and use it for setting up the certificate
        $userid = get_record_sql('SELECT id FROM {usr} WHERE admin = 1 AND deleted = 0 ORDER BY id LIMIT 1', array());
        $id = $userid->id;
        $user = new User;
        $user->find_by_id($id);

        $country = get_profile_field($id, 'country');
        $town = get_profile_field($id, 'town');
        $city = get_profile_field($id, 'city');
        $industry = get_profile_field($id, 'industry');
        $occupation = get_profile_field($id, 'occupation');

        $dn = array(
            'commonName' => ($user->get('username') ? $user->get('username') : 'Mahara'),
            'countryName' => ($country ? strtoupper($country) : 'NZ'),
            'localityName' => ($town ? $town : 'Wellington'),
            'emailAddress' => ($user->get('email') ? $user->get('email') : $CFG->noreplyaddress),
            'organizationName' => ($industry ? $industry : get_config('sitename')),
            'stateOrProvinceName' => ($city ? $city : 'Wellington'),
            'organizationalUnitName' => ($occupation ? $occupation : 'Mahara'),
        );

        $privkeypass = get_config('sitename');
        $privkey = openssl_pkey_new();
        $csr     = openssl_csr_new($dn, $privkey);
        $sscert  = openssl_csr_sign($csr, null, $privkey, $numberofdays);
        openssl_x509_export($sscert, $publickey);
        openssl_pkey_export($privkey, $privatekey, $privkeypass);

        // Write Private Key and Certificate files to disk.
        // If there was a generation error with either explode.
        if (empty($privatekey)) {
            throw new Exception(get_string('nullprivatecert', 'auth.saml'), 1);
        }
        if (empty($publickey)) {
            throw new Exception(get_string('nullpubliccert', 'auth.saml'), 1);
        }

        if ( !file_put_contents(AuthSaml::get_certificate_path() . 'server.pem', $privatekey) ) {
            throw new Exception(get_string('nullprivatecert', 'auth.saml'), 1);
        }
        if ( !file_put_contents(AuthSaml::get_certificate_path() . 'server.crt', $publickey) ) {
            throw new Exception(get_string('nullpubliccert', 'auth.saml'), 1);
        }
    }

    public static function get_config_options() {

        $spentityid = get_config_plugin('auth', 'saml', 'spentityid');
        if (empty($spentityid)) {
            $spentityid = $_SERVER['HTTP_HOST'] . '/mahara';
        }

        // first time - create it
        if (!file_exists(AuthSaml::get_certificate_path() . 'server.crt')) {
            error_log("auth/saml: Creating the certificate for the first time");
            self::create_certificates();
        }
        $cert = file_get_contents(AuthSaml::get_certificate_path() . 'server.crt');
        $pem = file_get_contents(AuthSaml::get_certificate_path() . 'server.pem');
        if (empty($cert) || empty($pem)) {
            // bad cert - get rid of it
            unlink(AuthSaml::get_certificate_path() . 'server.crt');
            unlink(AuthSaml::get_certificate_path() . 'server.pem');
        }
        else {
            $privatekey = openssl_pkey_get_private($pem);
            $publickey  = openssl_pkey_get_public($cert);
            $data = openssl_pkey_get_details($publickey);
            // Load data from the current certificate.
            $data = openssl_x509_parse($cert);
        }

        // Calculate date expirey interval.
        $date1 = date("Y-m-d\TH:i:s\Z", str_replace ('Z', '', $data['validFrom_time_t']));
        $date2 = date("Y-m-d\TH:i:s\Z", str_replace ('Z', '', $data['validTo_time_t']));
        $datetime1 = new DateTime($date1);
        $datetime2 = new DateTime($date2);
        $interval = $datetime1->diff($datetime2);
        $expirydays = $interval->format('%a');

        $elements = array(
            'authname' => array(
                'type'  => 'hidden',
                'value' => 'saml',
            ),
            'authglobalconfig' => array(
                'type'  => 'hidden',
                'value' => 'saml',
            ),
            'spentityid' => array(
                'type'  => 'text',
                'size' => 50,
                'title' => get_string('spentityid', 'auth.saml'),
                'rules' => array(
                    'required' => true,
                ),
                'defaultvalue' => $spentityid,
                'help'  => true,
            ),
            'makereallysure' => array(
                'type'         => 'html',
                'value'        => "<script>jQuery('document').ready(function() {     jQuery('#pluginconfig_save').on('click', function() {
                return confirm('" . get_string('reallyreallysure1', 'auth.saml') . "');
            });});</script>",
            ),
            'certificate' => array(
                                'type' => 'fieldset',
                                'legend' => get_string('certificate1', 'auth.saml'),
                                'elements' =>  array(
                                                'protos_help' =>  array(
                                                'type' => 'html',
                                                'value' => '<div><p>' . get_string('manage_certificate1', 'auth.saml', get_config('wwwroot') . 'auth/saml/sp/metadata.php?output=xhtml') . '</p></div>',
                                                ),

                                                'pubkey' => array(
                                                    'type'         => 'html',
                                                    'value'        => '<h3 class="title">' . get_string('publickey','admin') . '</h3>' .
                                                      '<pre style="font-size: 0.7em; white-space: pre;">' . $cert . '</pre>'
                                                ),
                                                'sha1fingerprint' => array(
                                                    'type'         => 'html',
                                                    'value'        => '<div><p>' . get_string('sha1fingerprint', 'auth.webservice', auth_saml_openssl_x509_fingerprint($cert, "sha1")) . '</p></div>',
                                                ),
                                                'md5fingerprint' => array(
                                                    'type'         => 'html',
                                                    'value'        => '<div><p>' . get_string('md5fingerprint', 'auth.webservice', auth_saml_openssl_x509_fingerprint($cert, "md5")) . '</p></div>',
                                                ),
                                                'expires' => array(
                                                    'type'         => 'html',
                                                    'value'        => '<div><p>' . get_string('publickeyexpireson', 'auth.webservice',
                                                    format_date($data['validTo_time_t']) . " (" . $expirydays . " days)") . '</p></div>'
                                                ),
                                            ),
                                'collapsible' => false,
                                'collapsed'   => false,
                                'name' => 'activate_webservices_networking',
                            ),
        );

        // check extensions are loaded
        $libchecks = '';
        // Make sure mcrypt exists
        if (!extension_loaded('mcrypt')) {
            $libchecks .= '<li>' . get_string('errornomcrypt', 'auth.saml') . '</li>';
        }
        // Make sure the simplesamlphp files have been installed via 'make ssphp'
        if (!file_exists(get_config('docroot') .'auth/saml/extlib/simplesamlphp/vendor/autoload.php')) {
            $libchecks .= '<li>' . get_string('errorbadlib', 'auth.saml', get_config('docroot') .'auth/saml/extlib/simplesamlphp/vendor/autoload.php') . '</li>';
        }
        // Make sure we can use 'memcache' with simplesamlphp as 'phpsession' doesn't work correctly in many situations
        $memcacheservers_config = get_config('memcacheservers');
        if (empty($memcacheservers_config) && !extension_loaded('memcache')) {
            $libchecks .= '<li>' . get_string('errornomemcache', 'auth.saml') . '</li>';
        }
        if (!empty($libchecks)) {
            $libcheckstr = '<div class="alert alert-danger"><ul class="unstyled">' . $libchecks . '</ul></div>';
            $elements = array_merge(array('libchecks' => array(
                                                'type' => 'html',
                                                'value' => $libcheckstr,
                                     )), $elements);
        }

        return array(
            'elements' => $elements,
        );
    }

    public static function validate_config_options(Pieform $form, $values) {
        if (empty($values['spentityid'])) {
            $form->set_error('spentityid', get_string('errorbadspentityid', 'auth.saml', $values['spentityid']));
        }
    }

    public static function save_config_options(Pieform $form, $values) {
        delete_records('auth_config', 'plugin', 'saml');
        $configs = array('spentityid');
        foreach ($configs as $config) {
            set_config_plugin('auth', 'saml', $config, $values[$config]);
        }

        // generate new certificates
        error_log("auth/saml: Creating new certificates");
        self::create_certificates();

    }

    public static function has_instance_config() {
        return true;
    }

    public static function is_usable() {
        // would be good to be able to detect SimpleSAMLPHP libraries
        return true;
    }

    public static function get_instance_config_options($institution, $instance = 0) {
        if (!class_exists('SimpleSAML_XHTML_IdPDisco')) {
            global $SESSION;
            $SESSION->add_error_msg(get_string('errorssphpsetup', 'auth.saml'));
            redirect(get_config('wwwroot') . 'admin/users/institutions.php?i=' . $institution);
        }

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

        // lookup the institution metadata
        $entityid = "";
        self::$default_config['institutionidp'] = "";
        if (file_exists(AuthSaml::get_metadata_path() . $institution . '.xml')) {
            $rawxml = file_get_contents(AuthSaml::get_metadata_path() . $institution . '.xml');
            if (empty($rawxml)) {
                // bad metadata - get rid of it
                unlink(AuthSaml::get_metadata_path() . $institution . '.xml');
            }
            else {
                $xml = new SimpleXMLElement($rawxml);
                $xml->registerXPathNamespace('md',   'urn:oasis:names:tc:SAML:2.0:metadata');
                $xml->registerXPathNamespace('mdui', 'urn:oasis:names:tc:SAML:metadata:ui');
                // Find all IDPSSODescriptor elements and then work back up to the entityID.
                $idps = $xml->xpath('//md:EntityDescriptor[//md:IDPSSODescriptor]');
                if ($idps && isset($idps[0])) {
                    $entityid = (string)$idps[0]->attributes('', true)->entityID[0];
                    self::$default_config['institutionidp'] = $rawxml;
                }
                else {
                    // bad metadata - get rid of it
                    unlink(AuthSaml::get_metadata_path() . $institution . '.xml');
                }
            }
        }

        $idp_title = get_string('institutionidp', 'auth.saml', $institution);
        if ($entityid) {
            $idp_title .= " (" . $entityid . ")";
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
            'institutionidp' => array(
                'type'  => 'textarea',
                'title' => $idp_title,
                'rows' => 10,
                'cols' => 80,
                'defaultvalue' => self::$default_config['institutionidp'],
                'help' => true,
                'class' => 'under-label',
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
                'type'         => 'switchbox',
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
                'type'         => 'switchbox',
                'title' => get_string('remoteuser', 'auth.saml'),
                'defaultvalue' => self::$default_config['remoteuser'],
                'help'  => true,
            ),
            'loginlink' => array(
                'type'         => 'switchbox',
                'title' => get_string('loginlink', 'auth.saml'),
                'defaultvalue' => self::$default_config['loginlink'],
                'disabled' => (self::$default_config['remoteuser'] ? false : true),
                'help'  => true,
            ),
            'updateuserinfoonlogin' => array(
                'type'         => 'switchbox',
                'title' => get_string('updateuserinfoonlogin', 'auth.saml'),
                'defaultvalue' => self::$default_config['updateuserinfoonlogin'],
                'help'  => true,
            ),
            'weautocreateusers' => array(
                'type'         => 'switchbox',
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
            'renderer' => 'div'
        );
    }

    public static function validate_instance_config_options($values, Pieform $form) {

        // only allow remoteuser to be unset if usersuniquebyusername is NOT set
        if (!get_config('usersuniquebyusername') && !$values['remoteuser']) {
            $form->set_error('remoteuser', get_string('errorremoteuser1', 'auth.saml'));
        }

        if (!empty($values['institutionidp'])) {
            try {
                $xml = new SimpleXMLElement($values['institutionidp']);
                $xml->registerXPathNamespace('md',   'urn:oasis:names:tc:SAML:2.0:metadata');
                $xml->registerXPathNamespace('mdui', 'urn:oasis:names:tc:SAML:metadata:ui');
                // Find all IDPSSODescriptor elements and then work back up to the entityID.
                $idps = $xml->xpath('//md:EntityDescriptor[//md:IDPSSODescriptor]');
                if ($idps && isset($idps[0])) {
                    $entityid = (string)$idps[0]->attributes('', true)->entityID[0];
                }
                else {
                    throw new Exception("Could not find entityId", 1);
                }

                // has this IdP already been configured?
                $institutions = get_records_sql_array(
                    'SELECT aic.value AS idpentityid,
                            ai.institution AS institution
                    FROM {auth_instance_config} as aic
                    JOIN {auth_instance} AS ai
                    ON aic.instance = ai.id
                      WHERE field = \'institutionidpentityid\' AND value = ? AND
                            ai.institution <> ?
                      ORDER BY instance',
                  array($entityid, $values['institution']));
                $i = 'Unknown';
                if (is_array($institutions)) {
                    $i = $institutions[0]->institution;
                    $form->set_error('institutionidp', get_string('errorduplicateidp1', 'auth.saml', $entityid, $i));
                }

            }
            catch (Exception $e) {
                $form->set_error('institutionidp', get_string('errorbadmetadata', 'auth.saml'));
            }
        }

        // If we're using Mahara usernames (usr.username) instead of remote usernames
        // (auth_remote_user.remoteusername), then autocreation cannot be enabled if any
        // institutions have registration enabled.
        //
        // This is because a user self-registering with another institution might pick
        // a username that matches the username from this SAML service, allowing them
        // to highjack someone else's account.
        //
        // (see the comments in the request_user_authorise function above).
        if ((!$values['remoteuser']) && ($values['weautocreateusers']) && ($institutions = get_column('institution', 'name', 'registerallowed', '1'))) {
            $form->set_error('weautocreateusers', get_string('errorregistrationenabledwithautocreate1', 'auth.saml'));
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

    public static function save_instance_config_options($values, Pieform $form) {

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

        // grab the entityId
        if (!empty($values['institutionidp'])) {
            $xml = new SimpleXMLElement($values['institutionidp']);
            $xml->registerXPathNamespace('md',   'urn:oasis:names:tc:SAML:2.0:metadata');
            $xml->registerXPathNamespace('mdui', 'urn:oasis:names:tc:SAML:metadata:ui');
            // Find all IDPSSODescriptor elements and then work back up to the entityID.
            $idps = $xml->xpath('//md:EntityDescriptor[//md:IDPSSODescriptor]');
            $entityid = (string)$idps[0]->attributes('', true)->entityID[0];
        }
        else {
            // cleanup old one if exists
            $entityid = "";
            if (file_exists(AuthSaml::get_metadata_path() . $values['institution'] . '.xml')) {
                // bad metadata - get rid of it
                unlink(AuthSaml::get_metadata_path() . $values['institution'] . '.xml');
            }
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
            'institutionidpentityid' => $entityid,
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

        // save the institution config
        if (!empty($values['institutionidp'])) {
            file_put_contents(AuthSaml::get_metadata_path() . $values['institution'] . '.xml', $values['institutionidp']);
        }

        return $values;
    }

    /**
     * Add "SSO Login" link below the normal login form.
     */
    public static function login_form_elements() {
        $url = get_config('wwwroot') . 'auth/saml/index.php';
        if (isset($_GET['login'])) {
            // We're on the transient login page. Redirect back to original page once we're done.
            $url .= '?wantsurl=' . urlencode(get_full_script_path());
        }
        $elements = array(
            'loginsaml' => array(
                'value' => '<div class="login-externallink"><a class="btn btn-primary btn-xs" href="' . $url . '">' . get_string('login', 'auth.saml') . '</a></div>'
            )
        );
        return $elements;
    }

    public static function need_basic_login_form() {
        return false;
    }
}

/**
 * Work around for missing function in 5.5 - is in 5.6
 */
function auth_saml_openssl_x509_fingerprint($cert, $hash) {
   $cert = preg_replace('#-.*-|\r|\n#', '', $cert);
   $bin = base64_decode($cert);
   return hash($hash, $bin);
}

if (file_exists(get_config('docroot') . 'auth/saml/extlib/simplesamlphp/lib/SimpleSAML/XHTML/IdPDisco.php')) {
    require_once(get_config('docroot') . 'auth/saml/extlib/simplesamlphp/lib/SimpleSAML/XHTML/IdPDisco.php');

    class PluginAuthSaml_IdPDisco extends SimpleSAML_XHTML_IdPDisco
    {

        /**
         * Initializes this discovery service.
         *
         * The constructor does the parsing of the request. If this is an invalid request, it will throw an exception.
         *
         * @param array  $metadataSets Array with metadata sets we find remote entities in.
         * @param string $instance The name of this instance of the discovery service.
         *
         * @throws Exception If the request is invalid.
         */
        public function __construct(array $metadataSets, $instance) {
            assert('is_string($instance)');

            // initialize standard classes
            $this->config = SimpleSAML_Configuration::getInstance();
            $this->metadata = SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
            $this->session = SimpleSAML_Session::getSessionFromRequest();
            $this->instance = $instance;
            $this->metadataSets = $metadataSets;
            $this->isPassive = false;
        }

        public function getTheIdPs() {
            $idpList = $this->getIdPList();
            $idpList = $this->filterList($idpList);
            $preferredIdP = $this->getRecommendedIdP();
            return array('list' => $idpList, 'preferred' => $preferredIdP);
        }
    }
}

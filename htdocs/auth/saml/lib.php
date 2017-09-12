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

    public static function prepare_metadata_path($idp) {
        $path = self::get_metadata_path() . preg_replace('/[\/:\.]/', '_', $idp) . '.xml';
        return $path;
    }

    /**
    * Loads and merges in a file with an attribute map.
    *
    * @param string $filepath  Path of attribute map file.
    * @param array $mapping Array where the attributes from the file should be added
    */
    private static function custom_loadmapfile($filepath, $mapping = array()) {
        if (!is_readable($filepath)) {
            throw new Exception(get_string('attributemapfilenotfound', 'auth.saml', $filepath));
        }
        $attributemap = NULL;
        include($filepath);
        if (!is_array($attributemap)) {
            throw new Exception(get_string('attributemapfilenotamap', 'auth.saml', $filepath));
        }

        $mapping = array_merge_recursive($mapping, $attributemap);
        return $mapping;
    }

/*
* Loads all mappings in the files into an array with 'class' => 'core:AttributeMap'
*
* @param filepaths array Paths to files that contain a mapping array
*/
    public static function get_attributemappings($filepaths= array()) {

          $configparameter = array(
              'class' => 'core:AttributeMap',
          );

          $attributemap = array();
          foreach ($filepaths as $key => $filepath) {
                //get the $attributemap array in the file
                $attributemap = self::custom_loadmapfile($filepath, $attributemap);
          }

          return array_merge($attributemap, $configparameter);
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
        $this->config['studentidfield'] = '';
        $this->config['institutionattribute'] = '';
        $this->config['institutionregex'] = 0;
        $this->config['institutionvalue'] = '';
        $this->config['updateuserinfoonlogin'] = 1;
        $this->config['remoteuser'] = true;
        $this->config['loginlink'] = false;
        $this->config['institutionidp'] = '';
        $this->config['institutionidpentityid'] = '';
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
        $studentid       = isset($attributes[$this->config['studentidfield']][0]) ? $attributes[$this->config['studentidfield']][0] : null;
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
            if ($isremote && !empty($email) && $this->config['loginlink']) {
                $user->find_by_email_address($email);
            }
            else if ($isremote) {
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
            $user->studentid          = $studentid;

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
            if (! empty($studentid)) {
                set_profile_field($user->id, 'studentid', $studentid);
                $user->studentid = $studentid;
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
        'user_attribute'         => '',
        'weautocreateusers'      => 0,
        'firstnamefield'         => '',
        'surnamefield'           => '',
        'emailfield'             => '',
        'studentidfield'         => '',
        'updateuserinfoonlogin'  => 1,
        'institution'            => '',
        'institutionattribute'   => '',
        'institutionvalue'       => '',
        'institutionregex'       => 0,
        'remoteuser'             => 1,
        'loginlink'              => 0,
        'institutionidpentityid' => '',
        'active'                 => 1
    );

    public static function can_be_disabled() {
        return true;
    }

    public static function is_active() {
        return get_field('auth_installed', 'active', 'name', 'saml');
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
            'commonName' => ($user->get('username') ? substr($user->get('username'), 0, 64) : 'Mahara'),
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

    /*
     * Return an array of signature algorithms in a form suitable for feeding into a dropdown form
     */
    public static function get_valid_saml_signature_algorithms() {
        $return = array();
        $return['http://www.w3.org/2001/04/xmldsig-more#rsa-sha256'] = get_string('sha256', 'auth.saml');
        $return['http://www.w3.org/2001/04/xmldsig-more#rsa-sha384'] = get_string('sha384', 'auth.saml');
        $return['http://www.w3.org/2001/04/xmldsig-more#rsa-sha512'] = get_string('sha512', 'auth.saml');
        $return['http://www.w3.org/2000/09/xmldsig#rsa-sha1'] = get_string('sha1', 'auth.saml');

        return $return;
    }

    /*
     * Return a sensible default signature algorithm for simplesamlphp config
     */
    public static function get_default_saml_signature_algorithm() {
        //Sha1 is deprecated so we default to something more sensible
        return 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256';
    }

    /*
     * Check if a given value is a valid signature algorithm for configuration
     * in simplesamlphp
     */
    public static function is_valid_saml_signature_algorithm($value) {
        $valids = self::get_valid_saml_signature_algorithms();
        return array_key_exists($value, $valids);
    }

    /*
     * Get the configured signature algorithm, falling back to the default if
     * no valid value can be found or no value is set
     */
    public static function get_config_saml_signature_algorithm() {
        $signaturealgo = get_config_plugin('auth', 'saml', 'sigalgo');
        if (empty($signaturealgo) || !self::is_valid_saml_signature_algorithm($signaturealgo)) {
                $signaturealgo = self::get_default_saml_signature_algorithm();
        }

        return $signaturealgo;
    }

    public static function get_config_options() {

        $spentityid = get_config_plugin('auth', 'saml', 'spentityid');
        if (empty($spentityid)) {
            $spentityid = $_SERVER['HTTP_HOST'] . '/mahara';
        }

        $signaturealgo = self::get_config_saml_signature_algorithm();
        $possiblealgos = self::get_valid_saml_signature_algorithms();

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
            'sigalgo' => array(
                'type' => 'select',
                'title' => get_string('sigalgo', 'auth.saml'),
                'options' => $possiblealgos,
                'defaultvalue' => $signaturealgo,
                'help' => true,
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
            $libchecks .= '<li>' . get_string_php_version('errornomcrypt', 'auth.saml') . '</li>';
        }
        // Make sure the simplesamlphp files have been installed via 'make ssphp'
        if (!self::is_simplesamlphp_installed()) {
            $libchecks .= '<li>' . get_string('errorbadlib', 'auth.saml', get_config('docroot') .'auth/saml/extlib/simplesamlphp/vendor/autoload.php') . '</li>';
        }
        // Make sure we can use 'memcache' with simplesamlphp as 'phpsession' doesn't work correctly in many situations
        if (!self::is_memcache_configured()) {
            $libchecks .= '<li>' . get_string_php_version('errornomemcache', 'auth.saml') . '</li>';
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
        $configs = array('spentityid', 'sigalgo');
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
        if (!self::is_simplesamlphp_installed()) {
            return false;
        }

        if (empty(get_config('ssphpsessionhandler'))) {
            return self::is_memcache_configured();
        }

        return true;
    }

    public static function is_simplesamlphp_installed() {
        return file_exists(get_config('docroot') . 'auth/saml/extlib/simplesamlphp/vendor/autoload.php');
    }

    public static function init_simplesamlphp() {
        if (!self::is_simplesamlphp_installed()) {
            throw new AuthInstanceException(get_string('errorbadlib', 'auth.saml', get_config('docroot') . 'auth/saml/extlib/simplesamlphp/vendor/autoload.php'));
        }

        require_once(get_config('docroot') . 'auth/saml/extlib/simplesamlphp/vendor/autoload.php');
        require_once(get_config('docroot') . 'auth/saml/extlib/_autoload.php');

        SimpleSAML_Configuration::init(get_config('docroot') . 'auth/saml/config');
    }

    public static function is_memcache_configured() {
        $is_configured = false;

        if (extension_loaded('memcache')) {
            foreach (self::get_memcache_servers() as $server) {
                $memcache = new Memcache;

                if (!empty($server['hostname']) && !empty($server['port'])) {
                    if ($memcache->connect($server['hostname'], $server['port'])) {
                        $is_configured = true;
                        break;
                    }
                }
            }
        }

        return $is_configured;
    }

    public static function get_memcache_servers() {
        $memcache_servers = array();

        $servers = get_config('memcacheservers');

        if (empty($servers)) {
            $servers = 'localhost';
        }

        $servers = explode(',', $servers);

        foreach ($servers as $server) {
            $url = parse_url($server);
            $host = !empty($url['host']) ? $url['host'] : $url['path'];
            $port = !empty($url['port']) ? $url['port'] : 11211;

            $memcache_servers[] = array('hostname' => $host, 'port' => $port);
        }

        return $memcache_servers;
    }

    public static function get_idps($xml) {
        $xml = new SimpleXMLElement($xml);
        $xml->registerXPathNamespace('md',   'urn:oasis:names:tc:SAML:2.0:metadata');
        $xml->registerXPathNamespace('mdui', 'urn:oasis:names:tc:SAML:metadata:ui');
        // Find all IDPSSODescriptor elements and then work back up to the entityID.
        $idps = $xml->xpath('//md:EntityDescriptor[//md:IDPSSODescriptor]');
        $entityid = null;
        if ($idps && isset($idps[0])) {
            $entityid = (string)$idps[0]->attributes('', true)->entityID[0];
        }
        return array($entityid, $idps);
    }

    public static function get_disco_list($lang = null, $entityidps = array()) {
        if (empty($lang)) {
            $lang = current_language();
        }
        PluginAuthSaml::init_simplesamlphp();
        $discoHandler = new PluginAuthSaml_IdPDisco(array('saml20-idp-remote', 'shib13-idp-remote'), 'saml');
        $disco = $discoHandler->getTheIdPs();
        if (count($disco['list']) > 0) {
            $lang = explode('.', $lang);
            $lang = strtolower(array_shift($lang));
            foreach($disco['list'] as $idp) {
                $idpname = (isset($idp['name'][$lang])) ? $idp['name'][$lang] : $idp['entityid'];
                $entityidps[$idp['entityid']] = $idpname;
            }
            return $entityidps;
        }
        return false;
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
            if (empty(self::$default_config['institutionvalue'])) {
                self::$default_config['institutionvalue'] = $institution;
            }
            self::$default_config['active'] = $default->active;
        }
        else {
            $default = new stdClass();
            $default->instancename = '';
            $default->active = 1;
        }

        // lookup the institution metadata
        $entityid = "";
        self::$default_config['institutionidp'] = "";
        if (!empty(self::$default_config['institutionidpentityid'])) {
            $idpfile = AuthSaml::prepare_metadata_path(self::$default_config['institutionidpentityid']);
            if (file_exists($idpfile)) {
                $rawxml = file_get_contents($idpfile);
                if (empty($rawxml)) {
                    // bad metadata - get rid of it
                    unlink($idpfile);
                }
                else {
                    list ($entityid, $idps) = self::get_idps($rawxml);
                    if ($entityid) {
                        self::$default_config['institutionidp'] = $rawxml;
                    }
                    else {
                        // bad metadata - get rid of it
                        unlink($idpfile);
                    }
                }
            }
        }

        $idp_title = get_string('institutionidp', 'auth.saml', $institution);
        if ($entityid) {
            $idp_title .= " (" . $entityid . ")";
        }
        $entityidps = array();
        $entityidp_hiddenlabel = true;
        // Fetch the idp info via disco
        $discolist = self::get_disco_list();
        if ($discolist) {
            $entityidps += $discolist;
            $entityidp_hiddenlabel = false;
        }
        asort($entityidps);
        // add the 'New' option to the top of the list
        $entityidps = array('new' => get_string('newidpentity', 'auth.saml')) + $entityidps;

        $idpselectjs = <<< EOF
<script type="application/javascript">
jQuery('document').ready(function($) {

    function update_idp_label(idp) {
        var idplabel = $('label[for="auth_config_institutionidp"]').html();
        // remove the idp entity from string
        if (idplabel.lastIndexOf('(') != -1) {
            idplabel = idplabel.substring(0, idplabel.lastIndexOf('('));
        }
        // add in new one
        if (idp) {
            idplabel = idplabel.trim() + ' (' + idp + ')';
        }
        $('label[for="auth_config_institutionidp"]').html(idplabel);
    }

    function update_idp_info(idp) {

        if (idp == 'new') {
            // clear the metadata box
            $('#auth_config_institutionidp').val('');
            update_idp_label(false);
        }
        else {
            // fetch the metadata info and update the textarea
            idpsafe = idp.replace(/[\/:\.]/g, '_'); // change dots to underscores as that is how we save file
            sendjsonrequest(config.wwwroot + 'auth/saml/idpmetadata.json.php', {'idp': idpsafe}, 'POST', function (data) {
                if (!data.error) {
                    $('#auth_config_institutionidp').val(data.data.metadata);
                }
            });
            update_idp_label(idp);
        }
    }

    // On change
    $('#auth_config_institutionidpentityid').on('change', function() {
        update_idp_info($(this).val());
    });
    // On load
    update_idp_info($('#auth_config_institutionidpentityid').val());
});
</script>
EOF;

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
            'active' => array(
                'type'  => 'switchbox',
                'title' => get_string('active', 'auth'),
                'defaultvalue' => (int) self::$default_config['active'],
            ),
            'institutionidpentityid' => array(
                'type'  => 'select',
                'title' => get_string('institutionidpentity', 'auth.saml'),
                'options' => $entityidps,
                'defaultvalue' => ($entityid ? $entityid : 'new'),
                'hiddenlabel' => $entityidp_hiddenlabel,
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
            'idpselectjs' => array(
                'type'         => 'html',
                'value'        => $idpselectjs,
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
            'studentidfield' => array(
                'type' => 'text',
                'title' => get_string('samlfieldforstudentid', 'auth.saml'),
                'defaultvalue' => self::$default_config['studentidfield'],
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
                list ($entityid, $idps) = self::get_idps($values['institutionidp']);
                if (!$entityid) {
                    throw new Exception("Could not find entityId", 1);
                }
            }
            catch (Exception $e) {
                $form->set_error('institutionidp', get_string('errorbadmetadata', 'auth.saml'));
            }
        }
        else {
            $form->set_error('institutionidpentityid', get_string('errormissingmetadata', 'auth.saml'));
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

        // If enabled "We auto-create users" check that all required fields for that are set.
        if ($values['weautocreateusers']) {
            $required= array('firstnamefield', 'surnamefield', 'emailfield');
            foreach ($required as $required_field) {
                if (empty($values[$required_field])) {
                    $form->set_error($required_field, get_string('errorextrarequiredfield', 'auth.saml'));
                }
            }
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
        global $SESSION;

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
        $authinstance->active       = (int) $values['active'];
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

        // grab the entityId from the metadata
        list ($entityid, $idps) = self::get_idps($values['institutionidp']);

        $changedxml = false;
        if ($values['institutionidpentityid'] != 'new') {
            $existingidpfile = AuthSaml::prepare_metadata_path($values['institutionidpentityid']);
            if (file_exists($existingidpfile)) {
                $rawxml = file_get_contents($existingidpfile);
                if ($rawxml != $values['institutionidp']) {
                    $changedxml = true;
                    // find out which institutions are using it
                    $duplicates = get_records_sql_array("
                        SELECT COUNT(aic.instance) AS instances
                        FROM {auth_instance_config} aic
                        JOIN {auth_instance} ai ON (ai.authname = 'saml' AND ai.id = aic.instance)
                        WHERE aic.field = 'institutionidpentityid' AND aic.value = ? AND aic.instance != ?",
                        array($values['institutionidpentityid'], $values['instance']));
                    if ($duplicates[0]->instances > 0) {
                        $SESSION->add_ok_msg(get_string('idpentityupdatedduplicates', 'auth.saml', $duplicates[0]->instances));
                    }
                    else {
                        $SESSION->add_ok_msg(get_string('idpentityupdated', 'auth.saml'));
                    }
                }
                else {
                    $SESSION->add_ok_msg(get_string('idpentityadded', 'auth.saml'));
                }
            }
            else {
                // existing idpfile not found so just save it
                $changedxml = true;
            }
        }
        else {
           $values['institutionidpentityid'] = $entityid;
           $changedxml = true;
           $SESSION->add_ok_msg(get_string('idpentityadded', 'auth.saml'));
        }

        self::$default_config = array(
            'user_attribute' => $values['user_attribute'],
            'weautocreateusers' => $values['weautocreateusers'],
            'loginlink' => $values['loginlink'],
            'remoteuser' => $values['remoteuser'],
            'firstnamefield' => $values['firstnamefield'],
            'surnamefield' => $values['surnamefield'],
            'emailfield' => $values['emailfield'],
            'studentidfield' => $values['studentidfield'],
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
        if ($changedxml) {
            $idpfile = AuthSaml::prepare_metadata_path($values['institutionidpentityid']);
            file_put_contents($idpfile, $values['institutionidp']);
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

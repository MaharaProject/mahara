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
        $this->has_instance_config = true;
        $this->type = 'browserid';

        $this->config['weautocreateusers'] = 0;
        if (!empty($id)) {
            return $this->init($id);
        }
        return true;
    }

    public function init($id) {
        $this->ready = parent::init($id);
        return $this->ready;
    }

    public function authenticate_user_account($user, $password) {
        // Authentication is done elsewhere in Javascript
        return false;
    }

    public function can_auto_create_users() {
        // The normal user auto creation process doesn't work for this backend
        return false;
    }

    public function create_new_user($email) {
        if (!$this->config['weautocreateusers']) {
            return null;
        }

        if (record_exists('artefact_internal_profile_email', 'email', $email)) {
            throw new AccountAutoCreationException(get_string('emailalreadyclaimed', 'auth.browserid', $email));
        }

        if (record_exists('usr', 'username', $email)) {
            throw new AccountAutoCreationException(get_string('emailclaimedasusername', 'auth.browserid', $email));
        }

        // Personal details are currently not provided by the Persona API.
        $user = new stdClass();
        $user->username = $email;
        $user->firstname = '';
        $user->lastname = '';
        $user->email = $email;

        // no need for a password on Persona accounts
        $user->password = '';
        $user->passwordchange = 0;
        $user->authinstance = $this->instanceid;

        // Set default values to activate this user
        $user->deleted = 0;
        $user->expiry = null;
        $user->suspendedcusr = null;

        $user->id = create_user($user, array(), $this->institution);

        return $user;
    }
}

class PluginAuthBrowserid extends PluginAuth {

    const BROWSERID_VERIFIER_URL = 'https://verifier.login.persona.org/verify';

    private static $default_config = array(
        'weautocreateusers' => 0,
    );

    public static function has_config() {
        return false;
    }

    public static function get_config_options() {
        return array();
    }

    public static function has_instance_config() {
        return true;
    }
    /**
     * Implement the function is_usable()
     *
     * @return boolean true if the BrowserID verifier is usable, false otherwise
     */
    public static function is_usable() {
        if ( extension_loaded('curl')) {
            return self::is_available();
        }
        return false;
    }

    public static function get_instance_config_options($institution, $instance = 0) {

        if ($instance > 0) {
            $current_config = get_records_menu('auth_instance_config', 'instance', $instance, '', 'field, value');

            if ($current_config == false) {
                $current_config = array();
            }

            foreach (self::$default_config as $key => $value) {
                if (array_key_exists($key, $current_config)) {
                    self::$default_config[$key] = $current_config[$key];
                }
            }
        }

        $elements = array(
            'instance' => array(
                'type'  => 'hidden',
                'value' => $instance,
            ),
            'institution' => array(
                'type'  => 'hidden',
                'value' => $institution,
            ),
            'authname' => array(
                'type'  => 'hidden',
                'value' => 'browserid',
            ),
            'instancename' => array(
                'type'  => 'hidden',
                'value' => 'Persona',
            ),
            'authname' => array(
                'type'  => 'hidden',
                'value' => 'browserid',
            ),
            'weautocreateusers' => array(
                'type'         => 'checkbox',
                'title'        => get_string('weautocreateusers', 'auth'),
                'defaultvalue' => self::$default_config['weautocreateusers'],
                'help'         => true
            ),
        );

        return array(
            'elements' => $elements,
            'renderer' => 'table'
        );
    }

    /**
     * Function to check a BrowserID verifier status
     * @return boolean true if the verifier is available, false otherwise
     */
    public static function is_available(){
        // Send a test assertion to the verification service
        $request = array(
                CURLOPT_URL        => self::BROWSERID_VERIFIER_URL,
                CURLOPT_POST       => 1,
                CURLOPT_POSTFIELDS => 'request=1'
        );
        $response = mahara_http_request($request);
        if (!empty($response->data)) {
            $jsondata = json_decode($response->data);
            return !empty($jsondata);
        }
        return false;
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
        $authinstance->instancename = $values['instancename'];

        if ($values['create']) {
            $values['instance'] = insert_record('auth_instance', $authinstance, 'id', true);
        }
        else {
            update_record('auth_instance', $authinstance, array('id' => $values['instance']));
        }

        if (empty($current)) {
            $current = array();
        }

        self::$default_config = array('weautocreateusers' => $values['weautocreateusers']);

        foreach(self::$default_config as $field => $value) {
            $record = new stdClass();
            $record->instance = $values['instance'];
            $record->field = $field;
            $record->value = $value;

            if ($values['create'] || !array_key_exists($field, $current)) {
                insert_record('auth_instance_config', $record);
            }
            else {
                update_record('auth_instance_config', $record, array('instance' => $values['instance'], 'field' => $field));
            }
        }

        return $values;
    }

    /**
     * Add a Persona link/button.
     */
    public static function login_form_elements() {
        return array(
            'loginbrowserid' => array(
                'value' => '<div class="login-externallink"><a class="persona-button" href="javascript:window.browserid_login()"><span>' . get_string('login', 'auth.browserid') . '</span></a></div>'
            )
        );
    }

    /**
     * Load all of the Javascript needed to retrieve Personas from
     * the browser.
     */
    public static function login_form_js() {
        global $HEADDATA, $SESSION;
        $HEADDATA[] = '<script src="https://login.persona.org/include.js" type="text/javascript"></script>';
        $wwwroot = get_config('wwwroot');
        $returnurl = hsc(get_relative_script_path());
        // We can't use $USER->get('sesskey') because there is no $USER object yet.
        $sesskey = get_random_key();
        $SESSION->set('browseridsesskey', $sesskey);

        return <<< EOF
<form id="browserid-form" action="{$wwwroot}auth/browserid/login.php" method="post">
<input id="browserid-assertion" type="hidden" name="assertion" value="">
<input id="browserid-returnurl" type="hidden" name="returnurl" value="{$returnurl}">
<input id="browserid-sesskey" type="hidden" name="sesskey" value="{$sesskey}">
<input style="display: none" type="submit">
</form>

<script type="text/javascript">
function browserid_login() {
    navigator.id.get(function(assertion) {
        if (assertion) {
            document.getElementById('browserid-assertion').setAttribute('value', assertion);
            document.getElementById('browserid-form').submit();
        }
   });
}
</script>
EOF;
    }

    public static function need_basic_login_form() {
        return false;
    }
}

class BrowserIDUser extends LiveUser {
    public function login($email) {
        // This will do one of 3 things
        // 1 - If a user has an account, log them in
        // 2 - If a user doesn't have an account, and there is an auth method (which also has weautocreate), create acc and login
        // 3 - If a user doesn't have an account, and there is more than one auth method, show a registration page
        $sql = "SELECT
                    a.id, i.name AS institutionname
                FROM
                    {auth_instance} a
                JOIN
                    {institution} i ON a.institution = i.name
                WHERE
                    a.authname = 'browserid' AND
                    i.suspended = 0";
        $authinstances = get_records_sql_array($sql, null);
        if (!$authinstances) {
            throw new ConfigException(get_string('browseridnotenabled', 'auth.browserid'));
        }

        $autocreate = array(); // Remember the authinstances that are happy to create users

        foreach ($authinstances as $authinstance) {
            $auth = AuthFactory::create($authinstance->id);

            $institutionjoin = '';
            $institutionwhere = '';
            $sqlvalues = array($email);
            if ($authinstance->institutionname != 'mahara') {
                // Make sure that user is in the right institution
                $institutionjoin = 'JOIN {usr_institution} ui ON ui.usr = u.id';
                $institutionwhere = 'AND ui.institution = ?';
                $sqlvalues[] = $authinstance->institutionname;
            }

            $sql = "SELECT
                        u.*,
                        " . db_format_tsfield('u.expiry', 'expiry') . ",
                        " . db_format_tsfield('u.lastlogin', 'lastlogin') . ",
                        " . db_format_tsfield('u.lastlastlogin', 'lastlastlogin') . ",
                        " . db_format_tsfield('u.lastaccess', 'lastaccess') . ",
                        " . db_format_tsfield('u.suspendedctime', 'suspendedctime') . ",
                        " . db_format_tsfield('u.ctime', 'ctime') . "
                    FROM
                        {usr} u
                    JOIN
                        {artefact_internal_profile_email} a ON a.owner = u.id
                    $institutionjoin
                    WHERE
                        a.verified = 1 AND
                        a.email = ?
                    $institutionwhere";
            $user = get_record_sql($sql, $sqlvalues);
            if (!$user) {
                if ($auth->weautocreateusers) {
                    if ($authinstance->institutionname == 'mahara') {
                        array_unshift($autocreate, $auth); // Try "No Instititution" first when creating users below
                    }
                    else {
                        $autocreate[] = $auth;
                    }
                }
                continue; // skip to the next auth_instance
            }

            if (is_site_closed($user->admin)) {
                return false;
            }
            ensure_user_account_is_active($user);

            $this->authenticate($user, $auth->instanceid);
            return true;
        }

        foreach ($autocreate as $auth) {
            if (!$user = $auth->create_new_user($email)) {
                continue;
            }
            $this->authenticate($user, $auth->instanceid);
            return;
        }

        // Autocreation failed; try registration.
        list($form, $registerconfirm) = auth_generate_registration_form('register', 'browserid', '/register.php');
        if (!$form) {
            throw new AuthUnknownUserException(get_string('emailnotfound', 'auth.browserid', $email));
        }
        if (record_exists('usr', 'email', $email)
                || record_exists('artefact_internal_profile_email', 'email', $email)) {
            throw new AuthUnknownUserException(get_string('emailalreadytaken', 'auth.internal', $email));
        }
        $form['elements']['email'] = array(
            'type' => 'hidden',
            'value' => $email
        );
        $form['elements']['authtype'] = array(
            'type' => 'hidden',
            'value' => 'browserid'
        );
        list($formhtml, $js) = auth_generate_registration_form_js($form, $registerconfirm);

        $registerdescription = get_string('registerwelcome');
        if ($registerterms = get_config('registerterms')) {
            $registerdescription .= ' ' . get_string('registeragreeterms');
        }
        $registerdescription .= ' ' . get_string('registerprivacy');

        $smarty = smarty();
        $smarty->assign('register_form', $formhtml);
        $smarty->assign('registerdescription', $registerdescription);
        if ($registerterms) {
            $smarty->assign('termsandconditions', get_site_page_content('termsandconditions'));
        }
        $smarty->assign('PAGEHEADING', get_string('register', 'auth.browserid'));
        $smarty->assign('INLINEJAVASCRIPT', $js);
        $smarty->display('register.tpl');
        die;
    }
}

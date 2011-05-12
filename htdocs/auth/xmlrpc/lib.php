<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage auth-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();
require_once(get_config('docroot') . 'auth/lib.php');
require_once(get_config('libroot') . 'peer.php');
require_once(get_config('libroot') . 'applicationset.php');
require_once(get_config('docroot') . 'api/xmlrpc/lib.php');

/**
 * The XMLRPC authentication method, which authenticates users against the
 * ID Provider's XMLRPC service. This is special - it doesn't extend Auth, it's
 * not static, and it doesn't implement the expected methods. It doesn't replace
 * the user's existing Auth type, whatever that might be; it supplements it.
 */
class AuthXmlrpc extends Auth {

    /**findByWwwroot
     * Get the party started with an optional id
     * TODO: appraise
     * @param int $id   The auth instance id
     */
    public function __construct($id = null) {

        $this->has_instance_config = true;
        $this->type                            = 'xmlrpc';

        $this->config['wwwroot']               = '';
        $this->config['wwwroot_orig']          = '';
        $this->config['shortname']             = '';
        $this->config['name']                  = '';
        $this->config['portno']                = 80;
        $this->config['xmlrpcserverurl']       = '';
        $this->config['changepasswordurl']     = '';
        $this->config['updateuserinfoonlogin'] = 0;
        $this->config['weautocreateusers']     = 0;
        $this->config['theyautocreateusers']   = 0;
        $this->config['wessoout']              = 0;
        $this->config['theyssoin']             = 0;
        $this->config['weimportcontent']       = 0;
        $this->config['parent']                = null;
        $this->config['authloginmsg']          = '';
        if (!empty($id)) {
            return $this->init($id);
        }
        return true;
    }

    /**
     * Get config variables
     */
    public function init($id = null) {
        $this->ready = parent::init($id);
        return $this->ready;
    }

    public function __get($name) {
        if (array_key_exists($name, $this->config)) {
            return $this->config[$name];
        }
        if (isset($this->$name)) {
            return $this->$name;
        }
    }

    /**
     * The keepalive_client function is tricky to implement in Mahara. Moodle 
     * accomplishes this simply, because that application already updates the user 
     * table once for every page view.
     * I think that we *really* don't want to do that with Mahara. There are heaps of
     * ways that we could implement this that are not very portable, but for now, it's
     * best if we leave this on the todo pile. If it becomes crucially important for a 
     * stakeholder, we can provide some implementation of it.
     */
    public static function keepalive_client() {}
    public static function keepalive_server() {}

    /**
     * Grab a delegate object for auth stuff
     */
    public function request_user_authorise($token, $remotewwwroot) {
        global $USER, $SESSION;
        $this->must_be_ready();
        $peer = get_peer($remotewwwroot);

        if ($peer->deleted != 0 || $this->config['theyssoin'] != 1) {
            throw new XmlrpcClientException('We don\'t accept SSO connections from ' . $peer->name);
        }

        $client = new Client();
        $client->set_method('auth/mnet/auth.php/user_authorise')
               ->add_param($token)
               ->add_param(sha1($_SERVER['HTTP_USER_AGENT']))
               ->send($remotewwwroot);

        $remoteuser = (object)$client->response;

        if (empty($remoteuser) or !property_exists($remoteuser, 'username')) {
            // Caught by land.php
            throw new AccessDeniedException();
        }

        $create = false;
        $update = false;
        if ('1' == $this->config['updateuserinfoonlogin']) {
            $update = true;
        }

        // Retrieve a $user object. If that fails, create a blank one.
        try {
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

                $user->find_by_username($remoteuser->username);
            }
            else {
                $user->find_by_instanceid_username($this->instanceid, $remoteuser->username, true);
            }

            if ($user->get('suspendedcusr')) {
                die_info(get_string('accountsuspended', 'mahara', strftime(get_string('strftimedaydate'), $user->get('suspendedctime')), $user->get('suspendedreason')));
            }

        } catch (AuthUnknownUserException $e) {
            if (!empty($this->config['weautocreateusers'])) {
                $institution = new Institution($this->institution);
                if ($institution->isFull()) {
                    throw new XmlrpcClientException('SSO attempt from ' . $institution->displayname . ' failed - institution is full');
                }
                $user = new User;
                $create = true;
            }
            else {
                log_debug("User authorisation request from $remotewwwroot failed - "
                    . "remote user '$remoteuser->username' is unknown to us and auto creation of users is turned off");
                return false;
            }
        }

        /*******************************************/

        if ($create) {

            $user->passwordchange     = 1;
            $user->active             = 1;
            $user->deleted            = 0;

            //TODO: import institution's expiry?:
            //$institution = new Institution($peer->institution);
            $user->expiry             = null;
            $user->expirymailsent     = 0;
            $user->lastlogin          = time();
    
            $user->firstname          = $remoteuser->firstname;
            $user->lastname           = $remoteuser->lastname;
            $user->email              = $remoteuser->email;
            $imported = array('firstname', 'lastname', 'email');

            //TODO: import institution's per-user-quota?:
            //$user->quota              = $userrecord->quota;
            $user->authinstance       = empty($this->config['parent']) ? $this->instanceid : $this->parent;

            db_begin();
            $user->username           = get_new_username($remoteuser->username);

            $user->id = create_user($user, array(), $this->institution, $this, $remoteuser->username);

            $locked = $this->import_user_settings($user, $remoteuser);
            $locked = array_merge($imported, $locked);

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

        } elseif ($update) {
            $imported = array('firstname', 'lastname', 'email');
            foreach ($imported as $field) {
                if ($user->$field != $remoteuser->$field) {
                    $user->$field = $remoteuser->$field;
                    set_profile_field($user->id, $field, $user->$field);
                }
            }

            if (isset($remoteuser->idnumber)) {
                if ($user->studentid != $remoteuser->idnumber) {
                    $user->studentid = $remoteuser->idnumber;
                    set_profile_field($user->id, 'studentid', $user->studentid);
                }
                $imported[] = 'studentid';
            }

            $locked = $this->import_user_settings($user, $remoteuser);
            $locked = array_merge($imported, $locked);

            $user->lastlastlogin      = $user->lastlogin;
            $user->lastlogin          = time();

            //TODO: import institution's per-user-quota?:
            //$user->quota              = $userrecord->quota;
            $user->commit();
        }

        if (get_config('usersuniquebyusername')) {
            // Add them to the institution they have SSOed in by
            $user->join_institution($peer->institution);
        }


        // See if we need to create/update a profile Icon image
        if ($create || $update) {

            $client->set_method('auth/mnet/auth.php/fetch_user_image')
                   ->add_param($remoteuser->username)
                   ->send($remotewwwroot);

            $imageobject = (object)$client->response;

            $u = preg_replace('/[^A-Za-z0-9 ]/', '', $user->username);
            $filename = get_config('dataroot') . 'temp/mpi_' . intval($this->instanceid) . '_' . $u;

            if (array_key_exists('f1', $client->response)) {
                $imagecontents = base64_decode($client->response['f1']);
                if (file_put_contents($filename, $imagecontents)) {
                    $imageexists = false;
                    $icons       = false;

                    if ($update) {
                        $newchecksum = sha1_file($filename);
                        $icons = get_records_select_array('artefact', 'artefacttype = \'profileicon\' AND owner = ? ', array($user->id), '', 'id');
                        if (false != $icons) {
                            foreach ($icons as $icon) {
                                $iconfile = get_config('dataroot') . 'artefact/file/profileicons/originals/' . ($icon->id % 256) . '/'.$icon->id;
                                $checksum = sha1_file($iconfile);
                                if ($newchecksum == $checksum) {
                                    $imageexists = true;
                                    unlink($filename);
                                    break;
                                }
                            }
                        }
                    }

                    if (false == $imageexists) {
                        $filesize = filesize($filename);
                        if (!$user->quota_allowed($filesize)) {
                            $error = get_string('profileiconuploadexceedsquota', 'artefact.file', get_config('wwwroot'));
                        }

                        require_once('file.php');
                        $imagesize = getimagesize($filename);
                        if (!$imagesize || !is_image_type($imagesize[2])) {
                            $error = get_string('filenotimage');
                        }

                        $mime   = $imagesize['mime'];
                        $width  = $imagesize[0];
                        $height = $imagesize[1];
                        $imagemaxwidth  = get_config('imagemaxwidth');
                        $imagemaxheight = get_config('imagemaxheight');
                        if ($width > $imagemaxwidth || $height > $imagemaxheight) {
                            $error = get_string('profileiconimagetoobig', 'artefact.file', $width, $height, $imagemaxwidth, $imagemaxheight);
                        }

                        try {
                            $user->quota_add($filesize);
                        }
                        catch (QuotaException $qe) {
                            $error =  get_string('profileiconuploadexceedsquota', 'artefact.file', get_config('wwwroot'));
                        }

                        require_once(get_config('docroot') .'/artefact/lib.php');
                        require_once(get_config('docroot') .'/artefact/file/lib.php');

                        // Entry in artefact table
                        $artefact = new ArtefactTypeProfileIcon();
                        $artefact->set('owner', $user->id);
                        $artefact->set('title', 'Profile Icon');
                        $artefact->set('note', 'Profile Icon');
                        $artefact->set('size', $filesize);
                        $artefact->set('filetype', $mime);
                        $artefact->set('width', $width);
                        $artefact->set('height', $height);
                        $artefact->commit();

                        $id = $artefact->get('id');

                        // Move the file into the correct place.
                        $directory = get_config('dataroot') . 'artefact/file/profileicons/originals/' . ($id % 256) . '/';
                        check_dir_exists($directory);
                        rename($filename, $directory . $id);
                        if ($create || empty($icons)) {
                            $user->profileicon = $id;
                        }
                    }

                    $user->commit();
                }
                else {
                    log_warn(get_string('cantcreatetempprofileiconfile', 'artefact.file', $filename));
                }
            }
            if ($update) {
                $locked[] = 'profileicon';
            }
        }

        /*******************************************/

        // We know who our user is now. Bring her back to life.
        $USER->reanimate($user->id, $this->instanceid);

        // Set session variables to let the application know this session was 
        // initiated by MNET. Don't forget that users could initiate their 
        // sessions without MNET sometimes, which is why this data is stored in 
        // the session object.
        $SESSION->set('mnetuser', $user->id);
        $SESSION->set('authinstance', $this->instanceid);
        $SESSION->set('mnetuserfrom', $_SERVER['HTTP_REFERER']);

        if ($update && isset($locked)) {
            $SESSION->set('lockedfields', $locked);
        }

        return true;
    }

    /**
     * Given a username, returns whether the user exists in the usr table
     *
     * @param string $username The username to attempt to identify
     * @return bool            Whether the username exists
     */
    public function user_exists($username) {
        $this->must_be_ready();
        $userrecord = false;

        // The user is likely to be associated with the parent instance
        if (is_numeric($this->config['parent']) && $this->config['parent'] > 0) {
            $_instanceid = $this->config['parent'];
            $userrecord = record_exists_select('usr', 'LOWER(username) = ? and authinstance = ?', array(strtolower($username), $_instanceid));
        }

        if (empty($userrecord)) {
            $_instanceid = $this->instanceid;
            $userrecord = record_exists_select('usr', 'LOWER(username) = ? and authinstance = ?', array(strtolower($username), $_instanceid));
        }

        if ($userrecord != false) {
            return $userrecord;
        }
        throw new AuthUnknownUserException("\"$username\" is not known to Auth");
    }

    /**
     * In practice, I don't think this method needs to return an accurate 
     * answer for this, because XMLRPC authentication doesn't use the standard 
     * authentication mechanisms, instead relying on land.php to handle 
     * everything.
     */
    public function can_auto_create_users() {
        return (bool)$this->config['weautocreateusers'];
    }

    /**
     * Given a user and their remote user record, attempt to populate some of 
     * the user's profile fields and account settings from the remote data.
     *
     * This does not change the first name, last name or e-mail fields, as these are 
     * dealt with differently depending on whether we are creating the user 
     * record or updating it.
     *
     * This method attempts to set:
     *
     * * City
     * * Country
     * * Language
     * * Introduction
     * * WYSIWYG editor setting
     *
     * @param User $user
     * @param stdClass $remoteuser
     */
    private function import_user_settings($user, $remoteuser) {
        $imported = array();

        // City
        if (!empty($remoteuser->city)) {
            if (get_profile_field($user->id, 'town') != $remoteuser->city) {
                set_profile_field($user->id, 'town', $remoteuser->city);
            }
            $imported[] = 'town';
        }

        // Country
        if (!empty($remoteuser->country)) {
            $validcountries = array_keys(getoptions_country());
            $newcountry = strtolower($remoteuser->country);
            if (in_array($newcountry, $validcountries)) {
                set_profile_field($user->id, 'country', $newcountry);
            }
            $imported[] = 'country';
        }

        // Language
        if (!empty($remoteuser->lang)) {
            $validlanguages = array_keys(get_languages());
            $newlanguage = str_replace('_', '.', strtolower($remoteuser->lang));
            if (in_array($newlanguage, $validlanguages)) {
                set_account_preference($user->id, 'lang', $newlanguage);
                $user->set_account_preference('lang', $newlanguage);
            }
        }

        // Description
        if (isset($remoteuser->description)) {
            if (get_profile_field($user->id, 'introduction') != $remoteuser->description) {
                set_profile_field($user->id, 'introduction', $remoteuser->description);
            }
            $imported[] = 'introduction';
        }

        // HTML Editor setting
        if (isset($remoteuser->htmleditor)) {
            $htmleditor = ($remoteuser->htmleditor) ? 1 : 0;
            if ($htmleditor != get_account_preference($user->id, 'wysiwyg')) {
                set_account_preference($user->id, 'wysiwyg', $htmleditor);
                $user->set_account_preference('wysiwyg', $htmleditor);
            }
        }

        return $imported;
    }

    public function kill_parent($username) {
        require_once(get_config('docroot') . 'api/xmlrpc/client.php');

        // For some people, the call to kill_children fails (when the remote 
        // site is a Moodle). We still haven't worked out why that is, but it's 
        // not a problem on the Mahara site
        try {
            $client = new Client();
            $client->set_method('auth/mnet/auth.php/kill_children')
                   ->add_param($username)
                   ->add_param(sha1($_SERVER['HTTP_USER_AGENT']))
                   ->send($this->wwwroot);
        }
        catch (XmlrpcClientException $e) {
            log_debug("XMLRPC error occured while calling MNET method kill_children on $this->wwwroot");
            log_debug("This means that single-signout probably didn't work properly, but the problem "
                . "is at the remote application");
            log_debug("If the remote application is Moodle, you are likely a victim of "
                . "http://tracker.moodle.org/browse/MDL-16872 - try applying the attached patch to fix the issue");
            log_debug("Exception message follows:");
            log_debug($e->getMessage());
        }
    }

    /**
     * Overrides the default logout mechanism to do proper single singout
     */
    public function logout() {
        global $USER, $SESSION;

        if (get_config('usersuniquebyusername')) {
            // The auth_remote_user will have a row for the institution in 
            // which the user SSOed into first. However, they could have 
            // been coming from somewhere else this time, which is why we 
            // can't use auth_remote_user for the lookup. Their username 
            // won't change for their Mahara account anyway, so just grab 
            // it out of the usr table.
            $remoteusername = get_field('usr', 'username', 'id', $USER->get('id'));
        }
        else {
            // Check the auth_remote_user table for what the remote 
            // application thinks the username is
            $remoteusername = get_field('auth_remote_user', 'remoteusername', 'localusr', $USER->get('id'), 'authinstance', $this->instanceid);
            if (!$remoteusername && $this->parent) {
                $remoteusername = get_field('auth_remote_user', 'remoteusername', 'localusr', $USER->get('id'), 'authinstance', $this->parent);
            }
        }

        $USER->logout();
        // Unset locked fields
        $SESSION->clear('lockedfields');

        if (isset($_GET['logout'])) {
            // Explicit logout request
            $this->kill_parent($remoteusername);
            redirect($this->wwwroot);
        }
        elseif (!$this->parent) {
            $this->kill_parent($remoteusername);
            // Redirect back to their IDP if they don't have a parent auth method set 
            // (aka: they can't log in at Mahara's log in form)
            $peer = get_peer($this->wwwroot);
            // TODO: This should be stored in the application config table
            $jumpurl = str_replace('land', 'jump', $peer->application->ssolandurl);
            redirect($this->wwwroot . $jumpurl . '?hostwwwroot=' . dropslash(get_config('wwwroot')) . '&wantsurl=' . urlencode($_SERVER['REQUEST_URI']));
        }

        // Anything else is a session timeout

        $SESSION->set('mnetuser', null);
    }

}

/**
 * Plugin configuration class
 */
class PluginAuthXmlrpc extends PluginAuth {

    private static $default_config = array(
        'instancename'          => '',
        'wwwroot'               => '',
        'wwwroot_orig'          => '',
        'name'                  => '',
        'appname'               => '',
        'portno'                => 80,
        'updateuserinfoonlogin' => 0,
        'weautocreateusers'     => 0,
        'theyautocreateusers'   => 0,
        'wessoout'              => 0,
        'theyssoin'             => 0,
        'weimportcontent'       => 0,
        'parent'                => null,
        'authloginmsg'          => ''
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

    public static function is_usable() {
        return extension_loaded('xmlrpc') && extension_loaded('openssl') && extension_loaded('curl');
    }

    public static function get_instance_config_options($institution, $instance = 0) {

        $peer = new Peer();

        // TODO : switch to getrecord
        // Get a list of applications and make a dropdown from it
        $applicationset = new ApplicationSet();
        $apparray = array();
        foreach ($applicationset as $app) {
            $apparray[$app->name] = $app->displayname;
        }

        /**
         * A parent authority for XML-RPC is the data-source that a remote XML-RPC service
         * communicates with to authenticate a user, for example, the XML-RPC server that 
         * we connect to might be authorising users against an LDAP store. If this is the 
         * case, and we know of the LDAP store, and our users are able to log on to our 
         * system and be authenticated directly against the LDAP store, then we honor that 
         * association.
         * 
         * In this way, the unique relationship is between the username and the authority,
         * not the username and the institution. This allows an institution to have a user
         * 'donal' on server 'LDAP-1' and a different user 'donal' on server 'LDAP-2'.
         * 
         * Get a list of auth instances for this institution, and eliminate those that 
         * would not be valid parents (as they themselves require a parent). These are 
         * eliminated only to provide a saner interface to the admin user. In theory, it's
         * ok to chain authorities.
         */ 
        $instances = auth_get_auth_instances_for_institution($institution);
        $options = array('None');
        if (is_array($instances)) {
            foreach($instances as $someinstance) {
                if ($someinstance->requires_parent == 1 || $someinstance->authname == 'none') {
                    continue;
                }
                $options[$someinstance->id] = $someinstance->instancename;
            }
        }

        // Get the current data (if any exists) for this auth instance
        if ($instance > 0) {
            $default = get_record('auth_instance', 'id', $instance);
            if ($default == false) {
                throw new SystemException(get_string('nodataforinstance', 'auth').$instance);
            }
            $current_config = get_records_menu('auth_instance_config', 'instance', $instance, '', 'field, value');

            if ($current_config == false) {
                throw new SystemException('No config data for instance: '.$instance);
            }

            foreach (self::$default_config as $key => $value) {
                if (array_key_exists($key, $current_config)) {
                    self::$default_config[$key] = $current_config[$key];

                    // We can use the wwwroot to create a Peer object
                    if ('wwwroot' == $key) {
                        $peer->findByWwwroot($current_config[$key]);
                        self::$default_config['wwwroot_orig'] = $current_config[$key];
                    }
                } elseif (property_exists($default, $key)) {
                    self::$default_config[$key] = $default->{$key};
                }
            }
        } else {
            $max_priority = get_field('auth_instance', 'MAX(priority)', 'institution', $institution);
            self::$default_config['priority'] = ++$max_priority;
        }

        if (empty($peer->application->name)) {
            self::$default_config['appname'] = key(current($applicationset));
        } else {
            self::$default_config['appname'] = $peer->application->name;
        }

        $elements['instancename'] = array(
            'type' => 'text',
            'title' => get_string('authname','auth'),
            'rules' => array(
                'required' => true
            ),
            'defaultvalue' => self::$default_config['instancename'],
            'help'   => true
        );

        $elements['instance'] = array(
            'type' => 'hidden',
            'value' => $instance
        );

        $elements['institution'] = array(
            'type' => 'hidden',
            'value' => $institution
        );

        $elements['deleted'] = array(
            'type' => 'hidden',
            'value' => $peer->deleted
        );

        $elements['authname'] = array(
            'type' => 'hidden',
            'value' => 'xmlrpc'
        );

        $elements['wwwroot'] = array(
            'type' => 'text',
            'title' => get_string('wwwroot', 'auth'),
            'rules' => array(
                'required' => true
            ),
            'defaultvalue' => self::$default_config['wwwroot'],
            'help'   => true
        );

        $elements['wwwroot_orig'] = array(
            'type' => 'hidden',
            'value' => self::$default_config['wwwroot_orig']
        );

        $elements['oldwwwroot'] = array(
            'type' => 'hidden',
            'value' => 'xmlrpc'
        );

        if ($instance) {
            $elements['publickey'] = array(
                'type' => 'textarea',
                'title' => get_string('publickey', 'admin'),
                'defaultvalue' => get_field('host', 'publickey', 'wwwroot', self::$default_config['wwwroot']),
                'rules' => array(
                    'required' => true,
                ),
                'rows' => 15,
                'cols' => 70,
            );

            $elements['publickeyexpires']= array(
                'type' => 'html',
                'title' => get_string('publickeyexpires', 'admin'),
                'value' => format_date(get_field('host', 'publickeyexpires', 'wwwroot', self::$default_config['wwwroot'])),
            );
        }

        $elements['name'] = array(
            'type' => 'text',
            'title' => get_string('name', 'auth'),
            'rules' => array(
                'required' => true
            ),
            'defaultvalue' => $peer->name,
            'help'   => true
        );

        /**
         * empty($peer->appname) would ALWAYS return true, because the property doesn't really
         * exist. When we try to get $peer->appname, we're actually calling the peer class's
         * __get overloader. Unfortunately, the 'empty' function seems to just check for the
         * existence of the property - it doesn't call the overloader. Bug or feature?
         */
	     
        $tmpappname = $peer->appname;

        $elements['appname'] = array(
            'type'                => 'select',
            'title'               => get_string('application','auth'),
            'collapseifoneoption' => true,
            'multiple'            => false,
            'options'             => $apparray,
            'defaultvalue'        => empty($tmpappname)? 'moodle' : $tmpappname,
            'help'                => true
        );

        $elements['portno'] = array(
            'type' => 'text',
            'title' => get_string('port', 'auth'),
            'rules' => array(
                'required' => true,
                'integer'  => true
            ),
            'defaultvalue' => $peer->portno,
            'size'   => 4,
            'help'   => true
        );

        $elements['parent'] = array(
            'type'                => 'select',
            'title'               => get_string('parent','auth'),
            'collapseifoneoption' => false,
            'options'             => $options,
            'defaultvalue'        => self::$default_config['parent'],
            'help'   => true
        );

        $elements['authloginmsg'] = array(
            'type'         => 'wysiwyg',
            'rows'         => 10,
            'cols'         => 70,
            'title'        => '',
            'description'  => get_string('authloginmsg', 'auth'),
            'defaultvalue' => self::$default_config['authloginmsg'],
            'help'         => true,
        );

        $elements['ssodirection'] = array(
            'type'         => 'select',
            'title'        => get_string('ssodirection', 'auth'),
            'options'      => array(0 => '--', 'theyssoin' => get_string('theyssoin', 'auth'), 'wessoout' => get_string('wessoout', 'auth')),
            'defaultvalue' => self::$default_config['wessoout'] ? 'wessoout' : 'theyssoin',
            'help'   => true,
        );

        $elements['updateuserinfoonlogin'] = array(
            'type'         => 'checkbox',
            'title'        => get_string('updateuserinfoonlogin', 'auth'),
            'defaultvalue' => self::$default_config['updateuserinfoonlogin'],
            'help'   => true
        );

        $elements['weautocreateusers'] = array(
            'type'         => 'checkbox',
            'title'        => get_string('weautocreateusers', 'auth'),
            'defaultvalue' => self::$default_config['weautocreateusers'],
            'help'   => true
        );

        $elements['theyautocreateusers'] = array(
            'type'         => 'checkbox',
            'title'        => get_string('theyautocreateusers', 'auth'),
            'defaultvalue' => self::$default_config['theyautocreateusers'],
            'help'   => true
        );

        $elements['weimportcontent'] = array(
            'type'         => 'checkbox',
            'title'        => get_string('weimportcontent', 'auth'),
            'defaultvalue' => self::$default_config['weimportcontent'],
            'help'         => true,
        );

        return array(
            'elements' => $elements,
            'renderer' => 'table'
        );
    }

    public static function validate_config_options($values, $form) {
        if (false === strpos($values['wwwroot'], '://')) {
            $values['wwwroot'] = 'http://' . $values['wwwroot'];
        }

        $authinstance = new stdClass();
        $peer = new Peer();

        if (false == $peer->findByWwwroot($values['wwwroot'])) {
            try {
                $peer->bootstrap($values['wwwroot'], null, $values['appname'], $values['institution']);
            } catch (RemoteServerException $e) {
                log_debug($e->getMessage());
                $form->set_error('wwwroot',get_string('cantretrievekey', 'auth') . '<br>' . $e->getMessage());
            } catch (ParamOutOfRangeException $e) {
                log_debug($e->getMessage());
                $form->set_error('wwwroot',get_string('cantretrievekey', 'auth') . '<br>' . $e->getMessage());
            }
        }
        else if ($values['institution'] != $peer->institution) {
            if (get_records_sql_array("
                SELECT ai.*, aic.*
                FROM {auth_instance} ai JOIN {auth_instance_config} aic ON ai.id = aic.instance
                WHERE aic.field = 'wwwroot' AND aic.value = ? AND ai.institution = ?", array($values['wwwroot'], $peer->institution))) {
                $form->set_error('wwwroot',get_string('hostwwwrootinuse', 'auth', hsc(get_field('institution', 'displayname', 'name', $peer->institution))));
            }
        }

        if (isset($values['publickey'])) {
            try {
                new PublicKey($values['publickey'], $peer->wwwroot);
            }
            catch (CryptException $e) {
                $form->set_error('publickey', $e->getMessage());
            }
        }

        //TODO: test values and set appropriate errors on form
    }

    public static function save_config_options($values, $form) {
        if (false === strpos($values['wwwroot'], '://')) {
            $values['wwwroot'] = 'http://' . $values['wwwroot'];
        }

        db_begin();
        $authinstance = new stdClass();
        $peer = new Peer();

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
 
        if (false == $peer->findByWwwroot($values['wwwroot'])) {
            try {
                $peer->bootstrap($values['wwwroot'], null, $values['appname'], $values['institution']);
            } catch (RemoteServerException $e) {
                $form->set_error('wwwroot',get_string('cantretrievekey', 'auth'));
                throw new RemoteServerException($e->getMessage(), $e->getCode());
            }
        }

        $peer->wwwroot              = preg_replace("|\/+$|", "", $values['wwwroot']);
        $peer->name                 = $values['name'];
        $peer->deleted              = $values['deleted'];
        $peer->portno               = $values['portno'];
        $peer->appname              = $values['appname'];
        $peer->institution          = $values['institution'];
        if (isset($values['publickey'])) {
            $peer->publickey            = new PublicKey($values['publickey'], $peer->wwwroot);
            $peer->publickeyexpires     = $peer->publickey->expires;
        }

        /**
         * The following properties are not user-updatable
        $peer->lastconnecttime      = $values['lastconnecttime'];
         */

        $peer->commit();
        
        $authinstance->instancename = $values['instancename'];
        $authinstance->institution  = $values['institution'];
        $authinstance->authname     = $values['authname'];

        if ($values['create']) {
            $values['instance'] = insert_record('auth_instance', $authinstance, 'id', true);
        } else {
            update_record('auth_instance', $authinstance, array('id' => $values['instance']));
        }

        if (empty($current)) {
            $current = array();
        }

        self::$default_config = array(  'wwwroot'               => $values['wwwroot'],
                                        'parent'                => $values['parent'],
                                        'authloginmsg'          => $values['authloginmsg'],
                                        'wessoout'              => 0,
                                        'theyssoin'             => 0,
                                        'theyautocreateusers'   => 0,
                                        'weautocreateusers'     => 0,
                                        'updateuserinfoonlogin' => 0,
                                        'weimportcontent'       => 0,
                                        );

        if ($values['ssodirection'] == 'wessoout') {
            self::$default_config['wessoout']              = 1;
            self::$default_config['theyautocreateusers']   = $values['theyautocreateusers'];
        }
        else if ($values['ssodirection'] == 'theyssoin') {
            self::$default_config['theyssoin']             = 1;
            self::$default_config['updateuserinfoonlogin'] = $values['updateuserinfoonlogin'];
            self::$default_config['weautocreateusers']     = $values['weautocreateusers'];
            self::$default_config['weimportcontent']       = $values['weimportcontent'];
        }

        foreach(self::$default_config as $field => $value) {
            $record = new stdClass();
            $record->instance = $values['instance'];
            $record->field    = $field;
            $record->value    = $value;

            if ($field == 'wwwroot') {
                $record->value    = dropslash($value);
            }

            if (empty($value)) {
                delete_records('auth_instance_config', 'field', $field, 'instance', $values['instance']);
            } elseif ($values['create'] || !array_key_exists($field, $current)) {
                insert_record('auth_instance_config', $record);
            } else {
                update_record('auth_instance_config', $record, array('instance' => $values['instance'], 'field' => $field));
            }
        }

        db_commit();
        return $values;
    }

    public static function get_jump_url_prefix($hostwwwroot, $hostapp) {
        return $hostwwwroot . '/' . ($hostapp == 'moodle' ? 'auth/mnet/jump.php' : 'auth/xmlrpc/jump.php')
            . '?hostwwwroot=' . substr(get_config('wwwroot'), 0, -1) . '&wantsurl=';
    }

}

/**
 * Lifted from Moodle.
 *
 * Inline function to modify a url string so that mnet users are requested to
 * log in at their mnet identity provider (if they are not already logged in)
 * before ultimately being directed to the original url.
 *
 * uses global IDPJUMPURL - the url which user should initially be directed to
 * @param array $url array with 3 elements
 *     0 - context the url was taken from, possibly just the url, possibly href="url"
 *     1 - the destination url
 *     2 - the destination url, without the wwwroot part
 * @return string the url the remote user should be supplied with.
 */
function localurl_to_jumpurl($url) {
    global $IDPJUMPURL;
    $localpart='';
    $urlparts = parse_url($url[2]);
    if ($urlparts) {
        if (isset($urlparts['path'])) {
            $localpart .= $urlparts['path'];
        }
        if (isset($urlparts['query'])) {
            $localpart .= '?'.$urlparts['query'];
        }
        if (isset($urlparts['fragment'])) {
            $localpart .= '#'.$urlparts['fragment'];
        }
    }
    $indirecturl = $IDPJUMPURL . urlencode($localpart);
    //If we matched on more than just a url (ie an html link), return the url to an href format
    if ($url[0] != $url[1]) {
        $indirecturl = 'href="'.$indirecturl.'"';
    }
    return $indirecturl;
}

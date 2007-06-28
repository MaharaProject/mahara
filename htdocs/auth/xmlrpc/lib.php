<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage auth-internal
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
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

    public $file = null;

    /**
     * Get the party started with an optional id
     * TODO: appraise
     * @param int $id   The auth instance id
     */
    public function __construct($id = null) {

        $this->has_config = true;
        $this->type                            = 'xmlrpc';

        $this->config['wwwroot']               = '';
        $this->config['wwwroot_orig']          = '';
        $this->config['shortname']             = '';
        $this->config['name']                  = '';
        $this->config['portno']                = 80;
        $this->config['xmlrpcserverurl']       = '';
        $this->config['changepasswordurl']     = '';
        $this->config['updateuserinfoonlogin'] = 1;
        $this->config['weautocreateusers']     = 0;
        $this->config['theyautocreateusers']   = 0;
        $this->config['wessoout']              = 1;
        $this->config['theyssoin']             = 0;
        $this->config['parent']                = null;
        $this->file = fopen('/tmp/out.txt', 'w');
        if(!empty($id)) {
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
        global $USER;
        $this->must_be_ready();
        $peer = get_peer($remotewwwroot);

        if($peer->deleted != 0 || $this->config['theyssoin'] != 1) {
            throw new MaharaException('We don\'t accept SSO connections from '.$peer->name );
        }

        $client = new Client();
        $client->set_method('auth/mnet/auth.php/user_authorise')
               ->add_param($token)
               ->add_param(sha1($_SERVER['HTTP_USER_AGENT']))
               ->send($remotewwwroot);

        $remoteuser = (object)$client->response;

        if (empty($remoteuser) or !property_exists($remoteuser, 'username')) {
            throw new MaharaException('Unknown error!');
        }

        $virgin = false;

        set_cookie('institution', $peer->institution, 0, get_mahara_install_subdirectory());
        $oldlastlogin = null;
        $create = false;
        $update = false;

        // Retrieve a $user object. If that fails, create a blank one.
        try {
            $user = new User;
            $user->find_by_instanceid_username($this->instanceid, $remoteuser->username);
            if ('1' == $this->config['updateuserinfoonlogin']) {
                $update = true;
            }
        } catch (Exception $e) {
            if (!empty($this->config['weautocreateusers'])) {
                $user = new User;
                $create = true;
            } else {
                return false;
            }
        }

        /*******************************************/

        if ($create) {

            $user->username           = $remoteuser->username;
            $user->institution        = $peer->institution;            
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
            $user->preferredname      = $remoteuser->firstname;
            $user->email              = $remoteuser->email;

            //TODO: import institution's per-user-quota?:
            //$user->quota              = $userrecord->quota;
            $user->authinstance       = empty($this->config['parent']) ? $this->instanceid : $this->parent;
            $user->commit();


            set_profile_field($user->id, 'firstname', $user->firstname);
            set_profile_field($user->id, 'lastname', $user->lastname);
            set_profile_field($user->id, 'email', $user->email);

        } elseif ($update) {

            if ($user->firstname != $remoteuser->firstname) {
                $user->firstname = $remoteuser->firstname;
                set_profile_field($user->id, 'firstname', $user->firstname);
            }

            if ($user->lastname != $remoteuser->lastname) {
                $user->lastname = $remoteuser->lastname;
                set_profile_field($user->id, 'lastname', $user->lastname);
            }

            if ($user->email != $remoteuser->email) {
                $user->email = $remoteuser->email;
                set_profile_field($user->id, 'email', $user->email);
            }

            $user->preferredname      = $remoteuser->firstname;
            $user->lastlogin          = time();

            //TODO: import institution's per-user-quota?:
            //$user->quota              = $userrecord->quota;
            $user->commit();
        }


        // See if we need to create/update a profile Icon image
        if ($create || $update) {

            $client->set_method('auth/mnet/auth.php/fetch_user_image')
                   ->add_param($user->username)
                   ->send($remotewwwroot);

            $imageobject = (object)$client->response;

            $u = preg_replace('/[^A-Za-z0-9 ]/', '', $user->username);
            $filename = '/tmp/'.intval($this->instanceid).'_'.$u;

            if (array_key_exists('f1', $client->response)) {
                $imagecontents = base64_decode($client->response['f1']);
                file_put_contents($filename, $imagecontents);
                $imageexists = false;
                $icons       = false;

                if ($update) {
                    $newchecksum = sha1_file($filename);
                    $icons = get_records_select_array('artefact', 'artefacttype = \'profileicon\' AND owner = ? ', array($user->id), '', 'id');
                    if (false != $icons) {
                        foreach ($icons as $icon) {
                            $iconfile = get_config('dataroot') . 'artefact/internal/profileicons/' . ($icon->id % 256) . '/'.$icon->id;
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
                        $error = get_string('profileiconuploadexceedsquota', 'artefact.internal', get_config('wwwroot'));
                    }

                    require_once('file.php');
                    $mime = get_mime_type($filename);
                    if (!is_image_mime_type($mime)) {
                        $error = get_string('filenotimage');
                    }

                    list($width, $height) = getimagesize($filename);
                    if ($width > 300 || $height > 300) {
                        $error = get_string('profileiconimagetoobig', 'artefact.internal', $width, $height);
                    }

                    try {
                        $user->quota_add($filesize);
                    }
                    catch (QuotaException $qe) {
                        $error =  get_string('profileiconuploadexceedsquota', 'artefact.internal', get_config('wwwroot'));
                    }

                    require_once(get_config('docroot') .'/artefact/lib.php');
                    require_once(get_config('docroot') .'/artefact/internal/lib.php');

                    // Entry in artefact table
                    $artefact = new ArtefactTypeProfileIcon();
                    $artefact->set('owner', $user->id);
                    $artefact->set('title', 'Profile Icon');
                    $artefact->set('note', 'Profile Icon');
                    $artefact->commit();

                    $id = $artefact->get('id');

                    // Move the file into the correct place.
                    $directory = get_config('dataroot') . 'artefact/internal/profileicons/' . ($id % 256) . '/';
                    check_dir_exists($directory);
                    rename($filename, $directory . $id);
                    if ($create || empty($icons)) {
                        $user->profileicon = $id;
                    }
                }

                $user->commit();
            }
        }

        /*******************************************/

        // We know who our user is now. Bring her back to life.
        $USER->reanimate($user->id, $this->instanceid);
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
            $userrecord = get_record('usr', 'LOWER(username)', strtolower($username), 'authinstance', $_instanceid);
        }

        if (empty($userrecord)) {
            $_instanceid = $this->instanceid;
            $userrecord = get_record('usr', 'LOWER(username)', strtolower($username), 'authinstance', $_instanceid);
        }

        if ($userrecord != false) {
            return $userrecord;
        }
        throw new AuthUnknownUserException("\"$username\" is not known to Auth");
    }

    /**
     * Given a user that we know about, return an array of information about them
     *
     * Used when a user who was otherwise unknown authenticates successfully,
     * or if getting userinfo on each login is enabled for this auth method.
     *
     * Does not need to be implemented for the internal authentication method,
     * because all users are already known about.
     */
    public function get_user_info($username) {
        $this->must_be_ready();
        
        $userdata = parent::get_user_info_cached($username);
        /**
         * Here, we will sift through the data returned by the XMLRPC server
         * and update any userdata properties that have changed
         */
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
        'parent'                => null
    );

    public static function has_config() {
        return true;
    }

    public static function get_config_options($institution, $instance = 0) {

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
                if ($someinstance->requires_parent == 1) {
                    continue;
                }
                $options[$someinstance->id] = $someinstance->instancename;
            }
        }

        // Get the current data (if any exists) for this auth instance
        if ($instance > 0) {
            $default = get_record('auth_instance', 'id', $instance);
            if($default == false) {
                throw new Exception(get_string('nodataforinstance', 'auth').$instance);
            }
            $current_config = get_records_menu('auth_instance_config', 'instance', $instance, '', 'field, value');

            if($current_config == false) {
                throw new Exception('No config data for instance: '.$instance);
            }

            foreach (self::$default_config as $key => $value) {
                if(array_key_exists($key, $current_config)) {
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

        $elements['parent'] = array(
            'type'                => 'select',
            'title'               => get_string('parent','auth'),
            'collapseifoneoption' => false,
            'options'             => $options,
            'defaultvalue'        => self::$default_config['parent'],
            'help'   => true
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

        $elements['name'] = array(
            'type' => 'text',
            'title' => get_string('name', 'auth'),
            'rules' => array(
                'required' => true
            ),
            'defaultvalue' => $peer->name,
            'help'   => true
        );

        $elements['appname'] = array(
            'type'                => 'select',
            'title'               => get_string('application','auth'),
            'collapseifoneoption' => true,
            'multiple'            => false,
            'options'             => $apparray,
            'defaultvalue'        => empty($peer->appname)? key($apparray) : $peer->appname,
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
            'help'   => true
        );

        $elements['wessoout'] = array(
            'type'         => 'checkbox',
            'title'        => get_string('wessoout', 'auth'),
            'defaultvalue' => self::$default_config['wessoout'],
            'help'   => true
        );

        $elements['theyssoin'] = array(
            'type'         => 'checkbox',
            'title'        => get_string('theyssoin', 'auth'),
            'defaultvalue' => self::$default_config['theyssoin'],
            'help'   => true
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
        
        return array(
            'elements' => $elements,
            'renderer' => 'table'
        );
    }

    public static function validate_config_options($values, $form) {

        $authinstance = new stdClass();
        $peer = new Peer();

        //TODO: test values and set appropriate errors on form
    }

    public static function save_config_options($values, $form) {

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

        $peer->wwwroot              = $values['wwwroot'];
        $peer->name                 = $values['name'];
        $peer->deleted              = $values['deleted'];
        $peer->portno               = $values['portno'];
        $peer->appname              = $values['appname'];
        $peer->institution          = $values['institution'];

        /**
         * The following properties are not user-updatable
        $peer->publickey            = $values['publickey'];
        $peer->publickeyexpires     = $values['publickeyexpires'];
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
                                        'updateuserinfoonlogin' => $values['updateuserinfoonlogin'],
                                        'weautocreateusers'     => $values['weautocreateusers'],
                                        'theyautocreateusers'   => $values['theyautocreateusers'],
                                        'parent'                => $values['parent'],
                                        'wessoout'              => $values['wessoout'],
                                        'theyssoin'             => $values['theyssoin']
                                        );

        foreach(self::$default_config as $field => $value) {
            $record = new stdClass();
            $record->instance = $values['instance'];
            $record->field    = $field;
            $record->value    = $value;

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

}

?>
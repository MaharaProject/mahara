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

/**
 * The XMLRPC authentication method, which authenticates users against the
 * ID Provider's XMLRPC service. This is special - it doesn't extend Auth, it's
 * not static, and it doesn't implement the expected methods. It doesn't replace
 * the user's existing Auth type, whatever that might be; it supplements it.
 */
class AuthXmlrpc extends Auth {

    /**
     * Get the party started with an optional id
     * TODO: appraise
     * @param int $id   The auth instance id
     */
    public function __construct($id = null) {

        $this->has_config = true;
        $this->type                            = 'xmlrpc';

        $this->config['host']                  = '';
        $this->config['shortname']             = '';
        $this->config['name']                  = '';
        $this->config['xmlrpcserverurl']       = '';
        $this->config['changepasswordurl']     = '';
        $this->config['updateuserinfoonlogin'] = 1;

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

    /**
     * Grab a delegate object for auth stuff
     */
    public function request_user_authorise($token, $remotewwwroot) {
        global $CFG, $USER;

        // get_peer will throw exception if server is unrecognised
        $peer = get_peer($remotewwwroot);

        if($peer->deleted != 0 || $peer->they_sso_in != 1) {
            throw new MaharaException('We don\'t accept SSO connections from '.$peer->name );
        }

        $client = new Client();
        $client->set_method('auth/mnet/auth.php/user_authorise')
               ->add_param($token)
               ->add_param(sha1($_SERVER['HTTP_USER_AGENT']))
               ->send($remotewwwroot);

        $remoteuser = (object)$client->response;

        if (empty($remoteuser) or empty($remoteuser['username'])) {
            throw new MaharaException('Unknown error!');
        }

        $virgin = false;

        $authtype = auth_get_authtype_for_institution($peer->institution);
        safe_require('auth', $authtype);
        $authclass = 'Auth' . ucfirst($authtype);

        set_cookie('institution', $peer->institution, 0, get_mahara_install_subdirectory());
        $oldlastlogin = null;

        if (!call_static_method($authclass, 'user_exists', $remoteuser->username)) {
            $remoteuser->picture;
            $remoteuser->imagehash;

            $remoteuser->institution   = $peer->institution;
            $remoteuser->preferredname = $remoteuser->firstname;
            $remoteuser->passwordchange = 0;
            $remoteuser->active = !(bool)$remoteuser->deleted;
            $remoteuser->lastlogin = db_format_timestamp(time());

            db_begin();
            $remoteuser->id = insert_record('usr', $remoteuser, 'id', true);

            // TODO: fetch image if it has changed
            //$directory = get_config('dataroot') . 'artefact/internal/profileicons/' . ($id % 256) . '/';
            //$dirname  = "{$CFG->dataroot}/users/{$localuser->id}";
            //$filename = "$dirname/f1.jpg";

            //$localhash = '';
            //if (file_exists($filename)) {
            //    $localhash = sha1(file_get_contents($filename));
            //} elseif (!file_exists($dirname)) {
            //    mkdir($dirname);
            //}

            // fetch image from remote host
            $client->set_method('auth/mnet/auth.php/fetch_user_image')
                   ->add_param($remoteuser->username)
                   ->send($remotewwwroot);

            //if (strlen($fetchrequest->response['f1']) > 0) {
            //    $imagecontents = base64_decode($fetchrequest->response['f1']);
            //    file_put_contents($filename, $imagecontents);
            //}
            /*
            if (strlen($fetchrequest->response['f2']) > 0) {
                $imagecontents = base64_decode($fetchrequest->response['f2']);
                file_put_contents($dirname.'/f2.jpg', $imagecontents);
            }
            */

            if (strlen($fetchrequest->response['f1']) > 0) {
                // Entry in artefact table
                $artefact = new ArtefactTypeProfileIcon();
                $artefact->set('owner', $remoteuser->id);
                $artefact->set('title', 'Profile Icon');
                $artefact->set('note', '');
                $artefact->commit();

                $id = $artefact->get('id');


                // Move the file into the correct place.
                $directory = get_config('dataroot') . 'artefact/internal/profileicons/' . ($id % 256) . '/';
                check_dir_exists($directory);
                $imagecontents = base64_decode($fetchrequest->response['f1']);
                file_put_contents($directory . $id, $imagecontents);

                $filesize = filesize($directory . $id);
                set_field('usr', 'quotaused', $filesize, 'id', $remoteuser->id);
                $remoteuser->quotaused = $filesize;
                $remoteuser->quota = get_config_plugin('artefact', 'file', 'defaultquota');
                set_field('usr', 'profileicon', $id, 'id', $remoteuser->id);
                $remoteuser->profileicon = $id;
            }
            else {
                $remoteuser->quotaused = 0;
                $remoteuser->quota = get_config_plugin('artefact', 'file', 'defaultquota');
            }
            db_commit();
            handle_event('createuser', $remoteuser);

            // Log the user in and send them to the homepage
            $USER->login($remoteuser);
            redirect();
        } else {
            $USER->login($remoteuser);
        }
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

    private static $default_config = array('host'=>'', 'name'=>'', 'shortname'=>'','xmlrpcserverurl'=>'', 'updateuserinfoonlogin'=>'', 'autocreateusers'=>'');

    public static function has_config() {
        return true;
    }

    public static function get_config_options($institution, $instance = 0) {

        if ($instance > 0) {
            $current        = get_records_array('auth_instance',        'id',       $instance, 'priority ASC');
            if($current == false) {
                throw new Exception(get_string('nodataforinstance', 'auth').$instance);
            }
            $default = $current[0];
            $current_config = get_records_menu('auth_instance_config', 'instance', $instance, '', 'field, value');

            if($current_config == false) {
                $current_config = array();
            }

            foreach (self::$default_config as $key => $value) {
                if(array_key_exists($key, $current_config)) {
                    self::$default_config[$key] = $current_config[$key];
                }
            }
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
            'defaultvalue' => $default->instancename,
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

        $elements['authname'] = array(
            'type' => 'hidden',
            'value' => 'xmlrpc'
        );

        $elements['host'] = array(
            'type' => 'text',
            'title' => get_string('host', 'auth'),
            'rules' => array(
                'required' => true
            ),
            'defaultvalue' => self::$default_config['host'],
            'help'   => true
        );

        $elements['name'] = array(
            'type' => 'text',
            'title' => get_string('name', 'auth'),
            'rules' => array(
                'required' => true
            ),
            'defaultvalue' => self::$default_config['name'],
            'help'   => true
        );

        $elements['shortname'] = array(
            'type' => 'text',
            'title' => get_string('shortname', 'auth'),
            'rules' => array(
                'required' => true
            ),
            'defaultvalue' => self::$default_config['shortname'],
            'help'   => true
        );

        $elements['xmlrpcserverurl'] = array(
            'type' => 'text',
            'title' => get_string('xmlrpcserverurl', 'auth'),
            'rules' => array(
                'required' => true
            ),
            'defaultvalue' => self::$default_config['xmlrpcserverurl'],
            'help'   => true
        );

        $elements['updateuserinfoonlogin'] = array(
            'type'         => 'checkbox',
            'title'        => get_string('updateuserinfoonlogin', 'auth'),
            'defaultvalue' => self::$default_config['updateuserinfoonlogin'],
            'help'   => true
        );

        $elements['autocreateusers'] = array(
            'type'         => 'checkbox',
            'title'        => get_string('autocreateusers', 'auth'),
            'defaultvalue' => self::$default_config['autocreateusers'],
            'help'   => true
        );

        return array(
            'elements' => $elements,
            'renderer' => 'table'
        );
    }

    public static function save_config_options($values) {

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

        if ($values['create']) {
            $values['instance'] = insert_record('auth_instance', $authinstance, 'id', true);
        } else {
            update_record('auth_instance', $authinstance, array('id' => $values['instance']));
        }

        if (empty($current)) {
            $current = array();
        }

        self::$default_config = array(  'host'                  => $values['host'],
                                        'name'                  => $values['name'],
                                        'shortname'             => $values['shortname'],
                                        'xmlrpcserverurl'       => $values['xmlrpcserverurl'],
                                        'updateuserinfoonlogin' => $values['updateuserinfoonlogin'],
                                        'autocreateusers'       => $values['autocreateusers']);

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

        return $values;
    }

}

?>
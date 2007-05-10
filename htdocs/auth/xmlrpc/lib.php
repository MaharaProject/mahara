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
        $userdata->surname .= 'X';
    }

}

/**
 * Plugin configuration class
 */
class PluginAuthXmlrpc extends PluginAuth {

    public static function has_config() {
        return false;
    }

    public static function get_config_options() {
        return array();
    }
}

?>
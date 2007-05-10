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
 * The internal authentication method, which authenticates users against the
 * Mahara database.
 */
class AuthImap extends Auth {

    public function __construct($id = null) {
        $this->type                         = 'imap';
        $this->has_config                   = true;

        $this->config['host']               = '';
        $this->config['port']               = '143';
        $this->config['protocol']           = '/imap';
        $this->config['changepasswordurl']  = '';

        if(!empty($id)) {
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
        global $CFG;

        if (! function_exists('imap_open')) {
            throw new ConfigException('IMAP is not available in your PHP environment. Check that it is properly installed');
        }

        $connectionstring = '{'.
                                $this->config['host']
                            .':'. 
                                $this->config['port']
                            .
                                $this->config['protocol']
                            .'}'; 

        try {
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

}

/**
 * Plugin configuration class
 */
class PluginAuthImap extends PluginAuth {

    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        // TODO: put these strings in a lang file
        $options['/imap'] = 'IMAP';
        $options['/imap/ssl'] = 'IMAP/SSL';
        $options['/imap/ssl/novalidate-cert'] = 'IMAP/SSL (self-signed certificate)';
        $options['/imap/tls'] = 'IMAP/TLS';

        $elements['protocol'] = array(
            'type' => 'select',
            'title' => get_string('protocol'),
            'options' => $options,
            'rules' => array(
                'required' => true
            )
        );

        $elements['host'] = array(
            'type' => 'text',
            'title' => get_string('host'),
            'rules' => array(
                'required' => true
            )
        );

        $elements['port'] = array(
            'type' => 'text',
            'title' => get_string('port'),
            'rules' => array(
                'required' => true,
                'integer' => true
            )
        );

        $elements['changepasswordurl'] = array(
            'type' => 'text',
            'title' => get_string('changepasswordurl'),
            'rules' => array(
                'required' => false
            )
        );

        return array(
            'elements' => $elements,
            'renderer' => 'table'
        );
    }

    public function save_config_options($values) {
        $current = get_records_assoc('auth_instance_config', 'instance', $this->id, '', 'field, value');

        if (empty($current)) {
            $current = array();
        }

        foreach($values as $key => $value) {
            $record = new stdClass();
            $record->instance = $this->id;
            $record->field    = $key;
            $record->value    = $value;
            if (array_key_exists($key, $current)) {
                update_record('auth_instance_config', $record, array('instance' => $this->id, 'field' => $key));
            } else {
                insert_record('auth_instance_config', $record);
            }
        }

        return true;
    }
}

?>
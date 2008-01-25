<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

// TODO : lib

defined('INTERNAL') || die();
require_once(get_config('libroot') .'hostset.php');

class Institution {

    const   UNINITIALIZED  = 0;
    const   INITIALIZED    = 1;
    const   PERSISTENT     = 2;

    protected $initialized = self::UNINITIALIZED;
    protected $hostset;
    protected $members = array(
        'name' => '',
        'displayname' => '',
        'registerallowed' => 1,
        'updateuserinfoonlogin' => 0,
        'theme' => 'default',
        'defaultmembershipperiod' => 0,
        'maxuseraccounts' => null
        ); 

    function __construct($name = null) {
        if (is_null($name)) {
            return $this;
        }

        if (!$this->findByName($name)) {
            throw new ParamOutOfRangeException('No such institution');
        }
    }

    function __get($name) {
        if ($name == 'hosts') {
            if (count($this->hosts) == 0 && $this->ready) {
                $this->getHostData();
            }
        }
        if (array_key_exists($name, $this->members)) {
            return $this->members[$name];
        }
        return null;
    }

    public function __set($name, $value) {
        if (!is_string($name) | !array_key_exists($name, $this->members)) {
            throw new ParamOutOfRangeException();
        }
        if ($name == 'name') {
            if (!is_string($value) || empty($value) || strlen($value) > 255) {
                throw new ParamOutOfRangeException("'name' should be a string between 1 and 255 characters in length");
            }
        } elseif ($name == 'displayname') {
            if (!is_string($value) || empty($value) || strlen($value) > 255) {
                throw new ParamOutOfRangeException("'displayname' ($value) should be a string between 1 and 255 characters in length");
            }
        } elseif ($name == 'registerallowed') {
            if (!is_numeric($value) || $value < 0 || $value > 1) {
                throw new ParamOutOfRangeException("'registerallowed' should be zero or one");
            }
        } elseif ($name == 'updateuserinfoonlogin') {
            if (!is_numeric($value) || $value < 0 || $value > 1) {
                throw new ParamOutOfRangeException("'updateuserinfoonlogin' should be zero or one");
            }
        } elseif ($name == 'theme') {
            if (!is_string($value) || empty($value) || strlen($value) > 255) {
                throw new ParamOutOfRangeException("'theme' ($value) should be a string between 1 and 255 characters in length");
            }
        } elseif ($name == 'defaultmembershipperiod') {
            if (!empty($value) && (!is_numeric($value) || $value < 0 || $value > 9999999999)) {
                throw new ParamOutOfRangeException("'defaultmembershipperiod' should be a number between 1 and 9,999,999,999");
            }
        } elseif ($name == 'maxuseraccounts') {
            if (!empty($value) && (!is_numeric($value) || $value < 0 || $value > 9999999999)) {
                throw new ParamOutOfRangeException("'maxuseraccounts' should be a number between 1 and 9,999,999,999");
            }
        }
        $this->members[$name] = $value;
    }

    function findByName($name) {

        if (!is_string($name) || strlen($name) < 1 || strlen($name) > 255) {
            throw new ParamOutOfRangeException("'name' must be a string.");
        }

        $result = get_record('institution', 'name', $name);

        if (false == $result) {
            return false;
        }

        $this->initialized = self::PERSISTENT;
        $this->populate($result);

        return $this;
    }

    function findByWwwroot($wwwroot) {

        if (!is_string($wwwroot) || strlen($wwwroot) < 1 || strlen($wwwroot) > 255) {
            throw new SystemException();
        }

        $this->hostset = new HostSet();
        $this->hostset->findByWwwroot($wwwroot);
        if (false == $a_host = $this->hostset->current()) {
            return false;
        }
        $institution = $a_host->institution;

        $result = get_record('institution', 'name', $institution);
        if (false == $result) {
            throw new SystemException('Invalid Institution name');
        }

        $this->initialized = self::PERSISTENT;
        $this->populate($result);
    }

    function getHostData() {
        $this->hostset = new HostSet();
        $this->hostset->findByInstitution($this->name);
    }

    function initialise($name, $displayname) {
        if (empty($name) || !is_string($name)) {
            return false;
        }

        $this->name = $name;
        if (empty($displayname) || !is_string($displayname)) {
            return false;
        }

        $this->displayname = $displayname;
        $this->initialized = max(self::INITIALIZED, $this->initialized);
        return true;
    }

    function verifyReady() {
        if (empty($this->members['name']) || !is_string($this->members['name'])) {
            return false;
        }
        if (empty($this->members['displayname']) || !is_string($this->members['displayname'])) {
            return false;
        }
        $this->initialized = max(self::INITIALIZED, $this->initialized);
        return true;
    }

    function commit() {
        if (!$this->verifyReady()) {
            throw new Exception();
        }

        $record = new stdClass();
        $record->name                         = $this->name;
        $record->displayname                  = $this->displayname;
        $record->updateuserinfoonlogin        = $this->updateuserinfoonlogin;
        $record->theme                        = $this->theme;
        $record->defaultmembershipperiod      = $this->defaultmembershipperiod;
        $record->maxuseraccounts              = $this->maxuseraccounts;

        if ($this->initialized == self::INITIALIZED) {
            return insert_record('institution', $record);
        } elseif ($this->initialized == self::PERSISTENT) {
            return update_record('institution', $record, array('name' => $this->name));
        }
        // Shouldn't happen but who noes?
        return false;
    }

    function findByHostset(HostSet $hostset) {

        // get the first host record:
        $host = reset($hostset);

        $result = get_record('institution', 'name', $host->institution);
        if (false == $result) {
            throw new SystemException('No institution for hostset '.addslashes($host->institution));
        }
        $this->populate($result);
    }

    protected function populate($result) {
        $this->name                         = $result->name;
        $this->displayname                  = $result->displayname;
        $this->registerallowed              = $result->registerallowed;
        $this->updateuserinfoonlogin        = $result->updateuserinfoonlogin;
        $this->theme                        = $result->theme;
        $this->defaultmembershipperiod      = $result->defaultmembershipperiod;
        $this->maxuseraccounts              = $result->maxuseraccounts;
        $this->verifyReady();
    }

    public function addUserAsMember($user) {
        if ($this->isFull()) {
            throw new SystemException('Trying to add a user to an institution that already has a full quota of members');
        }
        if (is_numeric($user)) {
            $user = get_record('usr', 'id', $user);
        }
        $userinst = new StdClass;
        $userinst->institution = $this->name;
        $studentid = get_field('usr_institution_request', 'studentid', 'usr', $user->id, 
                               'institution', $this->name);
        if (!empty($studentid)) {
            $userinst->studentid = $studentid;
        }
        else if (!empty($user->studentid)) {
            $userinst->studentid = $user->studentid;
        }
        $userinst->usr = $user->id;
        $now = time();
        $userinst->ctime = db_format_timestamp($now);
        $defaultexpiry = $this->defaultmembershipperiod;
        if (!empty($defaultexpiry)) {
            $userinst->expiry = db_format_timestamp($now + $defaultexpiry);
        }
        $message = (object) array(
            'users' => array($user->id),
            'subject' => get_string('institutionmemberconfirmsubject'),
            'message' => get_string('institutionmemberconfirmmessage', 'mahara', $this->displayname),
        );
        db_begin();
        if (!get_config('usersallowedmultipleinstitutions')) {
            delete_records('usr_institution', 'usr', $user->id);
            delete_records('usr_institution_request', 'usr', $user->id);
        }
        insert_record('usr_institution', $userinst);
        delete_records('usr_institution_request', 'usr', $userinst->usr, 'institution', $this->name);
        activity_occurred('maharamessage', $message);
        handle_event('updateuser', $userinst->usr);
        db_commit();
    }

    public function addRequestFromUser($user, $studentid = null) {
        $request = get_record('usr_institution_request', 'usr', $user->id, 'institution', $this->name);
        if (!$request) {
            $request = (object) array(
                'usr'          => $user->id,
                'institution'  => $this->name,
                'confirmedusr' => 1,
                'studentid'    => empty($studentid) ? $user->studentid : $studentid,
                'ctime'        => db_format_timestamp(time())
            );
            $message = (object) array(
                'messagetype' => 'request',
                'username' => $user->username,
                'fullname' => $user->firstname . ' ' . $user->lastname,
                'institution' => (object)array('name' => $this->name, 'displayname' => $this->displayname),
            );
            db_begin();
            if (!get_config('usersallowedmultipleinstitutions')) {
                delete_records('usr_institution_request', 'usr', $user->id);
            }
            insert_record('usr_institution_request', $request);
            activity_occurred('institutionmessage', $message);
            handle_event('updateuser', $user->id);
            db_commit();
        } else if ($request->confirmedinstitution) {
            $this->addUserAsMember($user);
        }
    }

    public function inviteUser($user) {
        $userid = is_object($user) ? $user->id : $user;
        db_begin();
        insert_record('usr_institution_request', (object) array(
            'usr' => $userid,
            'institution' => $this->name,
            'confirmedinstitution' => 1,
            'ctime' => db_format_timestamp(time())
        ));
        activity_occurred('institutionmessage', (object) array(
            'messagetype' => 'invite',
            'users' => array($userid),
            'institution' => (object)array('name' => $this->name, 'displayname' => $this->displayname),
        ));
        handle_event('updateuser', $userid);
        db_commit();
    }

    public function removeMembers($userids) {
        // Remove self last.
        global $USER;
        $users = get_records_select_array('usr', 'id IN (' . join(',', $userids) . ')');
        $removeself = false;
        foreach ($users as $user) {
            if ($user->id == $USER->id) {
                $removeself = true;
                continue;
            }
            $this->removeMember($user);
        }
        if ($removeself) {
            $USER->leave_institution($this->name);
        }
    }

    public function removeMember($user) {
        if (is_numeric($user)) {
            $user = get_record('usr', 'id', $user);
        }
        db_begin();
        // If the user is being authed by the institution they are
        // being removed from, change them to internal auth
        $authinstances = get_records_select_assoc('auth_instance', "
            institution IN ('mahara','" . $this->name . "')");
        $oldauth = $user->authinstance;
        if (isset($authinstances[$oldauth]) && $authinstances[$oldauth]->institution == $this->name) {
            foreach ($authinstances as $ai) {
                if ($ai->instancename == 'internal' && $ai->institution == 'mahara') {
                    $user->authinstance = $ai->id;
                    break;
                }
            }
            delete_records('auth_remote_user', 'authinstance', $oldauth, 'localusr', $user->id);
            // If the old authinstance was external, the user may need
            // to set a password
            if ($user->password == '') {
                log_debug('resetting pw for '.$user->id);
                $this->removeMemberSetPassword($user);
            }
            update_record('usr', $user);
        }
        delete_records('usr_institution', 'usr', $user->id, 'institution', $this->name);
        handle_event('updateuser', $user->id);
        db_commit();
    }

    /**
     * Reset user's password, and send them a password change email
     */
    private function removeMemberSetPassword(&$user) {
        global $SESSION, $USER;
        if ($user->id == $USER->id) {
            $user->passwordchange = 1;
            return;
        }
        try {
            $pwrequest = new StdClass;
            $pwrequest->usr = $user->id;
            $pwrequest->expiry = db_format_timestamp(time() + 86400);
            $pwrequest->key = get_random_key();
            $sitename = get_config('sitename');
            $fullname = display_name($user, null, true);
            email_user($user, null,
                get_string('noinstitutionsetpassemailsubject', 'mahara', $sitename, $this->displayname),
                get_string('noinstitutionsetpassemailmessagetext', 'mahara', $fullname, $this->displayname, $sitename, $user->username, $pwrequest->key, $sitename, $pwrequest->key),
                get_string('noinstitutionsetpassemailmessagehtml', 'mahara', $fullname, $this->displayname, $sitename, $user->username, $pwrequest->key, $pwrequest->key, $sitename, $pwrequest->key, $pwrequest->key));
            insert_record('usr_password_request', $pwrequest);
        }
        catch (SQLException $e) {
            $SESSION->add_error_msg(get_string('forgotpassemailsendunsuccessful'));
        }
        catch (EmailException $e) {
            $SESSION->add_error_msg(get_string('forgotpassemailsendunsuccessful'));
        }
    }

    public function countMembers() {
        return count_records_sql('
            SELECT COUNT(*) FROM {usr} u INNER JOIN {usr_institution} i ON u.id = i.usr
            WHERE i.institution = ? AND u.deleted = 0', array($this->name));
    }

    public function countInvites() {
        return count_records_sql('
            SELECT COUNT(*) FROM {usr} u INNER JOIN {usr_institution_request} r ON u.id = r.usr
            WHERE r.institution = ? AND u.deleted = 0 AND r.confirmedinstitution = 1',
            array($this->name));
    }

    /**
     * Returns true if the institution already has its full quota of users 
     * assigned to it.
     *
     * @return bool
     */
    public function isFull() {
        return ($this->maxuseraccounts != '') && ($this->countMembers() >= $this->maxuseraccounts);
    }
}

function get_institution_selector($includedefault = true) {
    global $USER;

    if ($USER->get('admin')) {
        if ($includedefault) {
            $institutions = get_records_array('institution');
        }
        else {
            $institutions = get_records_select_array('institution', "name != 'mahara'");
        }
    } else if ($USER->is_institutional_admin()) {
        $institutions = get_records_select_array('institution', 'name IN (' 
            . join(',', array_map('db_quote',$USER->get('admininstitutions'))) . ')');
    } else {
        return null;
    }

    if (empty($institutions)) {
        return null;
    }

    $options = array();
    foreach ($institutions as $i) {
        $options[$i->name] = $i->displayname;
    }
    $institution = key($options);
    $institutionelement = array(
        'type' => 'select',
        'title' => get_string('institution'),
        'defaultvalue' => $institution,
        'options' => $options,
        'rules' => array('regex' => '/^[a-zA-Z0-9]+$/')
    );

    return $institutionelement;
}

/* The institution selector does exactly the same thing in both
   institutionadmins.php and institutionstaff.php (in /admin/users/).
   This function creates the form for the page, setting
   $institutionselector and $INLINEJAVASCRIPT in the smarty object. */
function add_institution_selector_to_page($smarty, $institution, $page) {
    require_once('pieforms/pieform.php');
    $institutionelement = get_institution_selector(false);

    if (empty($institutionelement)) {
        return false;
    }

    global $USER;
    if (empty($institution) || !$USER->can_edit_institution($institution)) {
        $institution = empty($institutionelement['value']) ? $institutionelement['defaultvalue'] : $institutionelement['value'];
    }
    else {
        $institutionelement['defaultvalue'] = $institution;
    }
    
    $institutionselector = pieform(array(
        'name' => 'institutionselect',
        'elements' => array(
            'institution' => $institutionelement,
        )
    ));
    
    $js = <<< EOF
function reloadUsers() {
    var inst = '';
    if ($('institutionselect_institution')) {
        inst = '?institution='+$('institutionselect_institution').value;
    }
    window.location.href = '{$page}'+inst;
}
addLoadEvent(function() {
    if ($('institutionselect_institution')) {
        connect($('institutionselect_institution'), 'onchange', reloadUsers);
    }
});
EOF;
    
    $smarty->assign('institutionselector', $institutionselector);
    $smarty->assign('INLINEJAVASCRIPT', $js);

    return $institution;
}
?>

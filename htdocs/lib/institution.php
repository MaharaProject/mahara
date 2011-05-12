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

// TODO : lib

defined('INTERNAL') || die();

class Institution {

    const   UNINITIALIZED  = 0;
    const   INITIALIZED    = 1;
    const   PERSISTENT     = 2;

    protected $initialized = self::UNINITIALIZED;
    protected $members = array(
        'name' => '',
        'displayname' => '',
        'registerallowed' => 1,
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
        } elseif ($name == 'theme') {
            if (!empty($value) && is_string($value) && strlen($value) > 255) {
                throw new ParamOutOfRangeException("'theme' ($value) should be less than 255 characters in length");
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
            throw new SystemException('Commit failed');
        }

        $record = new stdClass();
        $record->name                         = $this->name;
        $record->displayname                  = $this->displayname;
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

    protected function populate($result) {
        $this->name                         = $result->name;
        $this->displayname                  = $result->displayname;
        $this->registerallowed              = $result->registerallowed;
        $this->theme                        = $result->theme;
        $this->defaultmembershipperiod      = $result->defaultmembershipperiod;
        $this->maxuseraccounts              = $result->maxuseraccounts;
        $this->verifyReady();
    }

    public function addUserAsMember($user) {
        global $USER;
        if ($this->isFull()) {
            throw new SystemException('Trying to add a user to an institution that already has a full quota of members');
        }
        if (is_numeric($user)) {
            $user = get_record('usr', 'id', $user);
        }
        if ($user instanceof User) {
            $lang = $user->get_account_preference('lang');
            if (empty($lang) || $lang == 'default') {
                $lang = get_config('lang');
            }
        }
        else { // stdclass object
            $lang = get_user_language($user->id);
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
            'subject' => get_string_from_language($lang, 'institutionmemberconfirmsubject'),
            'message' => get_string_from_language($lang, 'institutionmemberconfirmmessage', 'mahara', $this->displayname),
        );
        db_begin();
        if (!get_config('usersallowedmultipleinstitutions')) {
            delete_records('usr_institution', 'usr', $user->id);
            delete_records('usr_institution_request', 'usr', $user->id);
        }
        insert_record('usr_institution', $userinst);
        delete_records('usr_institution_request', 'usr', $userinst->usr, 'institution', $this->name);
        // Copy institution views to the user's portfolio
        $checkviewaccess = empty($user->newuser) && !$USER->get('admin');
        $userobj = new User();
        $userobj->find_by_id($user->id);
        $userobj->copy_views(get_column('view', 'id', 'institution', $this->name, 'copynewuser', 1), $checkviewaccess);
        require_once('activity.php');
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
            require_once('activity.php');
            activity_occurred('institutionmessage', $message);
            handle_event('updateuser', $user->id);
            db_commit();
        } else if ($request->confirmedinstitution) {
            $this->addUserAsMember($user);
        }
    }

    public function declineRequestFromUser($userid) {
        $lang = get_user_language($userid);
        $message = (object) array(
            'users' => array($userid),
            'subject' => get_string_from_language($lang, 'institutionmemberrejectsubject'),
            'message' => get_string_from_language($lang, 'institutionmemberrejectmessage', 'mahara', $this->displayname),
        );
        db_begin();
        delete_records('usr_institution_request', 'usr', $userid, 'institution', $this->name,
                       'confirmedusr', 1);
        require_once('activity.php');
        activity_occurred('maharamessage', $message);
        handle_event('updateuser', $userid);
        db_commit();
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
        require_once('activity.php');
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
        $users = get_records_select_array('usr', 'id IN (' . join(',', array_map('intval', $userids)) . ')');
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
        // being removed from, change them to internal auth, or if
        // we can't find that, some other no institution auth.
        $authinstances = get_records_select_assoc(
            'auth_instance',
            "institution IN ('mahara', ?)",
            array($this->name),
            "institution = 'mahara' DESC, authname = 'internal' DESC"
        );
        $oldauth = $user->authinstance;
        if (isset($authinstances[$oldauth]) && $authinstances[$oldauth]->institution == $this->name) {
            foreach ($authinstances as $ai) {
                if ($ai->authname == 'internal' && $ai->institution == 'mahara') {
                    $user->authinstance = $ai->id;
                    break;
                }
                else if ($ai->institution == 'mahara') {
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
                get_string('noinstitutionsetpassemailmessagetext', 'mahara', $fullname, $this->displayname, $sitename, $user->username, get_config('wwwroot'), $pwrequest->key, get_config('wwwroot'), $sitename, get_config('wwwroot'), $pwrequest->key),
                get_string('noinstitutionsetpassemailmessagehtml', 'mahara', $fullname, $this->displayname, $sitename, $user->username, get_config('wwwroot'), $pwrequest->key, get_config('wwwroot'), $pwrequest->key, get_config('wwwroot'), $sitename, get_config('wwwroot'), $pwrequest->key, get_config('wwwroot'), $pwrequest->key));
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

    /**
     * Returns the list of institutions, implements institution searching
     *
     * @param array   Limit the output to only institutions in this array (used for institution admins).
     * @param bool    Whether default institution should be listed in results.
     * @param string  Searching query string.
     * @param int     Limit of results (used for pagination).
     * @param int     Offset of results (used for pagination).
     * @param int     Returns the total number of results.
     * @return array  A data structure containing results looking like ...
     *   $institutions = array(
     *                       name => array(
     *                           displayname     => string
     *                           maxuseraccounts => integer
     *                           members         => integer
     *                           staff           => integer
     *                           admins          => integer
     *                           name            => string
     *                       ),
     *                       name => array(...),
     *                   );
     */
    public static function count_members($filter, $showdefault, $query='', $limit=null, $offset=null, &$count=null) {
        if ($filter) {
            $where = '
            AND ii.name IN (' . join(',', array_map('db_quote', $filter)) . ')';
        }
        else {
            $where = '';
        }

        $querydata = split(' ', preg_replace('/\s\s+/', ' ', strtolower(trim($query))));
        $namesql = '(
                ii.name ' . db_ilike() . ' \'%\' || ? || \'%\'
            )
            OR (
                ii.displayname ' . db_ilike() . ' \'%\' || ? || \'%\'
            )';
        $namesql = join(' OR ', array_fill(0, count($querydata), $namesql));
        $queryvalues = array();
        foreach ($querydata as $w) {
            $queryvalues = array_pad($queryvalues, count($queryvalues) + 2, $w);
        }

        $count = count_records_sql('SELECT COUNT(ii.name)
            FROM {institution} ii
            WHERE' . $namesql, $queryvalues
        );

        $institutions = get_records_sql_assoc('
            SELECT
                ii.name,
                ii.displayname,
                ii.maxuseraccounts,
                ii.suspended,
                COALESCE(a.members, 0) AS members,
                COALESCE(a.staff, 0) AS staff,
                COALESCE(a.admins, 0) AS admins
            FROM
                {institution} ii
                LEFT JOIN
                    (SELECT
                        i.name, i.displayname, i.maxuseraccounts,
                        COUNT(ui.usr) AS members, SUM(ui.staff) AS staff, SUM(ui.admin) AS admins
                    FROM
                        {institution} i
                        LEFT OUTER JOIN {usr_institution} ui ON (ui.institution = i.name)
                        LEFT OUTER JOIN {usr} u ON (u.id = ui.usr)
                    WHERE
                        (u.deleted = 0 OR u.id IS NULL)
                    GROUP BY
                        i.name, i.displayname, i.maxuseraccounts
                    ) a ON (a.name = ii.name)
                    WHERE (' . $namesql . ')' . $where . '
                    ORDER BY
                        ii.name = \'mahara\', ii.displayname', $queryvalues, $offset, $limit);

        if ($showdefault && $institutions && array_key_exists('mahara', $institutions)) {
            $defaultinstmembers = count_records_sql('
                SELECT COUNT(u.id) FROM {usr} u LEFT OUTER JOIN {usr_institution} i ON u.id = i.usr
                WHERE u.deleted = 0 AND i.usr IS NULL AND u.id != 0
            ');
            $institutions['mahara']->members = $defaultinstmembers;
            $institutions['mahara']->staff   = '';
            $institutions['mahara']->admins  = '';
        }
        return $institutions;
    }
}

function get_institution_selector($includedefault = true) {
    global $USER;

    if ($USER->get('admin')) {
        if ($includedefault) {
            $institutions = get_records_array('institution', '', '', 'displayname');
        }
        else {
            $institutions = get_records_select_array('institution', "name != 'mahara'", null, 'displayname');
        }
    } else if ($USER->is_institutional_admin()) {
        $institutions = get_records_select_array(
            'institution',
            'name IN (' . join(',', array_map('db_quote',$USER->get('admininstitutions'))) . ')',
            null, 'displayname'
        );
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
   This function creates the form for the page. */
function institution_selector_for_page($institution, $page) {
    require_once('pieforms/pieform.php');
    $institutionelement = get_institution_selector(false);

    if (empty($institutionelement)) {
        return array('institution' => false, 'institutionselector' => null, 'institutionselectorjs' => '');
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
    
    return array(
        'institution'           => $institution,
        'institutionselector'   => $institutionselector,
        'institutionselectorjs' => $js
    );
}

function build_institutions_html($filter, $showdefault, $query, $limit, $offset, &$count=null) {
    global $USER;

    $institutions = Institution::count_members($filter, $showdefault, $query, $limit, $offset, $count);

    $smarty = smarty_core();
    $smarty->assign('institutions', $institutions);
    $smarty->assign('siteadmin', $USER->get('admin'));
    $data['tablerows'] = $smarty->fetch('admin/users/institutionsresults.tpl');

    $pagination = build_pagination(array(
                'id' => 'adminstitutionslist_pagination',
                'datatable' => 'adminstitutionslist',
                'url' => get_config('wwwroot') . 'admin/users/institutions.php' . (!empty($query) ? '?query=' . urlencode($query) : ''),
                'jsonscript' => 'admin/users/institutions.json.php',
                'count' => $count,
                'limit' => $limit,
                'offset' => $offset,
                'resultcounttextsingular' => get_string('institution', 'admin'),
                'resultcounttextplural' => get_string('institutions', 'admin'),
            ));

    $data['pagination'] = $pagination['html'];
    $data['pagination_js'] = $pagination['javascript'];

    return $data;
}

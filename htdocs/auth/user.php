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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();
$put = array();


/**
 * The user class represents any user in the system.
 *
 */
class User {

    /**
     * Defaults for user information.
     *
     * @var array
     */
    protected $defaults;
    protected $stdclass;
    protected $authenticated = false;
    protected $changed       = false;
    protected $attributes    = array();

    /**
     * Sets defaults for the user object (only because PHP5 does not appear
     * to support private static const arrays), and resumes a session
     */
    public function __construct() {
        $this->defaults = array(
            'logout_time'      => 0,
            'id'               => 0,
            'username'         => '',
            'password'         => '',
            'salt'             => '',
            'passwordchange'   => 0,
            'active'           => 1,
            'deleted'          => 0,
            'expiry'           => null,
            'expirymailsent'   => 0,
            'lastlogin'        => null,
            'lastlastlogin'    => null,
            'lastaccess'       => null, /* Is not necessarily updated every request, see accesstimeupdatefrequency config variable */
            'inactivemailsent' => 0,
            'staff'            => 0,
            'admin'            => 0,
            'firstname'        => '',
            'lastname'         => '',
            'studentid'        => '',
            'preferredname'    => '',
            'email'            => '',
            'profileicon'      => null,
            'suspendedctime'   => null,
            'suspendedreason'  => null,
            'suspendedcusr'    => null,
            'quota'            => null,
            'quotaused'        => 0,
            'authinstance'     => 1,
            'sessionid'        => '', /* The real session ID that PHP knows about */
            'accountprefs'     => array(),
            'activityprefs'    => array(),
            'institutions'     => array(),
            'grouproles'       => array(),
            'theme'            => null,
            'admininstitutions' => array(),
            'staffinstitutions' => array(),
            'parentuser'       => null,
            'loginanyway'       => false,
            'sesskey'          => '',
            'ctime'            => null,
            'views'            => array(),
            'showhomeinfo'     => 1
        );
        $this->attributes = array();

    }

    /**
     * 
     */
    public function find_by_id($id) {

        if (!is_numeric($id) || $id < 0) {
            throw new InvalidArgumentException('parameter must be a positive integer to create a User object');
        }

        $sql = 'SELECT
                    *, 
                    ' . db_format_tsfield('expiry') . ', 
                    ' . db_format_tsfield('lastlogin') . ', 
                    ' . db_format_tsfield('lastlastlogin') . ',
                    ' . db_format_tsfield('lastaccess') . ',
                    ' . db_format_tsfield('suspendedctime') . ',
                    ' . db_format_tsfield('ctime') . '
                FROM
                    {usr}
                WHERE
                    id = ?';

        $user = get_record_sql($sql, array($id));

        if (false == $user) {
            throw new AuthUnknownUserException("User with id \"$id\" is not known");
        }

        $this->populate($user);
        $this->reset_institutions();
        $this->reset_grouproles();
        return $this;
    }

    /**
     * Populates this object with the user record identified by the given 
     * username
     *
     * @throws AuthUnknownUserException If the user cannot be found. Note that 
     *                                  deleted users _can_ be found
     */
    public function find_by_username($username) {

        if (!is_string($username)) {
            throw new InvalidArgumentException('username parameter must be a string to create a User object');
        }

        $sql = 'SELECT
                    *,
                    ' . db_format_tsfield('expiry') . ',
                    ' . db_format_tsfield('lastlogin') . ',
                    ' . db_format_tsfield('lastlastlogin') . ',
                    ' . db_format_tsfield('lastaccess') . ',
                    ' . db_format_tsfield('suspendedctime') . ',
                    ' . db_format_tsfield('ctime') . '
                FROM
                    {usr}
                WHERE
                    username = ?';

        $user = get_record_sql($sql, $username);

        if (false == $user) {
            throw new AuthUnknownUserException("User with username \"$username\" is not known");
        }

        $this->populate($user);
        $this->reset_institutions();
        return $this;
    }

    /**
     * Finds details for a user given a username and their authentication 
     * instance.
     *
     * If the authentication instance is a child or a parent, its relation is 
     * checked too, because the user can enter the system by either method.
     */
    public function find_by_instanceid_username($instanceid, $username, $remoteuser=false) {

        if (!is_numeric($instanceid) || $instanceid < 0) {
            throw new InvalidArgumentException('parameter must be a positive integer to create a User object');
        }

        $username = strtolower($username);
        if ($remoteuser) {
            // See if the user has either the child or the parent authinstance. 
            // Most of the time, it's the parent auth instance that is 
            // stored with the user, but if they were created by (for 
            // example) SSO with no parent, then it will be the child that 
            // is stored. Nevertheless, a parent could be added later, and 
            // that should not matter in finding the user
            $parentwhere = '';
            if ($parentid = get_field('auth_instance_config', 'value', 'field', 'parent', 'instance', $instanceid)) {
                $parentwhere = '
                            OR
                            (
                                LOWER(username) = (
                                    SELECT
                                        username
                                    FROM
                                        {usr} us
                                    JOIN
                                        {auth_remote_user} aru ON (us.id = aru.localusr)
                                    WHERE
                                        LOWER(aru.remoteusername) = ' . db_quote($username) . '
                                        AND us.authinstance = ' . db_quote($parentid) . '
                                )
                                AND
                                u.authinstance = ' . db_quote($parentid) . '
                            )
                    ';
            }

            $sql = 'SELECT
                        u.*, 
                        ' . db_format_tsfield('u.expiry', 'expiry') . ',
                        ' . db_format_tsfield('u.lastlogin', 'lastlogin') . ',
                        ' . db_format_tsfield('u.lastlastlogin', 'lastlastlogin') . ',
                        ' . db_format_tsfield('u.lastaccess', 'lastaccess') . ',
                        ' . db_format_tsfield('u.suspendedctime', 'suspendedctime') . ',
                        ' . db_format_tsfield('u.ctime', 'ctime') . '
                    FROM {usr} u
                    LEFT JOIN {auth_remote_user} r ON u.id = r.localusr
                    WHERE
                        (
                            (
                                LOWER(r.remoteusername) = ?
                                AND r.authinstance = ?
                            )'
                            . $parentwhere
                            . '
                        )';
            $user = get_record_sql($sql, array($username, $instanceid));
        }
        else {
            $sql = 'SELECT
                        *, 
                        ' . db_format_tsfield('expiry') . ',
                        ' . db_format_tsfield('lastlogin') . ',
                        ' . db_format_tsfield('lastlastlogin') . ',
                        ' . db_format_tsfield('lastaccess') . ',
                        ' . db_format_tsfield('suspendedctime') . ',
                        ' . db_format_tsfield('ctime') . '
                    FROM
                        {usr}
                    WHERE
                        LOWER(username) = ? AND
                        authinstance = ?';
            $user = get_record_sql($sql, array($username, $instanceid));
        }

        if (false == $user) {
            throw new AuthUnknownUserException("User with username \"$username\" is not known at auth instance \"$instanceid\"");
        }

        $this->populate($user);
        return $this;
    }

    /**
     * Populates this object with the user record identified by a mobile 'token'
     *
     * @throws AuthUnknownUserException If the user cannot be found. 
     */
    public function find_by_mobileuploadtoken($token, $username) {

        if (!is_string($token)) {
            throw new InvalidArgumentException('Input parameters must be strings to create a User object from token');
        }

        $sql = 'SELECT
                        u.*,
                        ' . db_format_tsfield('u.expiry', 'expiry') . ',
                        ' . db_format_tsfield('u.lastlogin', 'lastlogin') . ',
                        ' . db_format_tsfield('u.lastlastlogin', 'lastlastlogin') . ',
                        ' . db_format_tsfield('u.lastaccess', 'lastaccess') . ',
                        ' . db_format_tsfield('u.suspendedctime', 'suspendedctime') . ',
                        ' . db_format_tsfield('u.ctime', 'ctime') . '
                FROM
                    {usr} u
                    LEFT JOIN {usr_account_preference} p ON u.id = p.usr
                            WHERE p.field=\'mobileuploadtoken\' AND p.value = ? AND u.username = ?
		';

        $user = get_record_sql($sql, array($token, $username));

        if (false == $user) {
            throw new AuthUnknownUserException("User with mobile upload token \"$token\" is not known");
        }

        $this->populate($user);
        return $this;
    }

    /**
     * Refreshes a users mobile 'token' and returns it
     *
     */
    public function refresh_mobileuploadtoken() {
	$new_token = md5( uniqid() );
        $this->set_account_preference('mobileuploadtoken', $new_token);
        $this->set('lastaccess', time());
	$this->commit();
	return $new_token;
    }

    /**
     * Set stuff that needs to be initialised once before a user record is created.
     */
    public function create() {
        $this->set('ctime', time());
    }


    /**
     * Take a row object from the usr table and populate this object with the
     * values
     *
     * @param  object $data  The row data
     */
    protected function populate($data) {
        reset($this->defaults);
        while(list($key, ) = each($this->defaults)) {
            if (property_exists($data, $key)) {
                $this->set($key, $data->{$key});
            }
        }
    }

    /**
     * Gets the user property keyed by $key.
     *
     * @param string $key The key to get the value of
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function get($key) {
        if (!array_key_exists($key, $this->defaults)) {
            throw new InvalidArgumentException($key);
        }
        if (array_key_exists($key, $this->attributes) && null !== $this->attributes[$key]) {
            return $this->attributes[$key];
        }
        return $this->defaults[$key];
    }

    /**
     * Gets the user property keyed by $key.
     *
     * @param string $key The key to get the value of
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function __get($key) {
        return $this->get($key);
    }

    /**
     * Sets the property keyed by $key
     */
    protected function set($key, $value) {

        if (!array_key_exists($key, $this->defaults)) {
            throw new InvalidArgumentException($key);
        }

        $this->attributes[$key] = $value;

        // For now, these fields are saved to the DB elsewhere
        if ($key != 'activityprefs' && $key != 'accountprefs' && $key != 'views') {
            $this->changed = true;
        }
        return $this;
    }

    /**
     * Sets the property keyed by $key
     */
    public function __set($key, $value) {
        if ($key == 'quotaused') {
            throw new InvalidArgumentException('quotaused should be set via the quota_* methods');
        }

        $this->set($key, $value);
    }

    /**
     * Commit the USR record to the database
     */
    public function commit() {
        if ($this->changed == false) {
            return;
        }
        $record = $this->to_stdclass();
        if (is_numeric($this->id) && 0 < $this->id) {
            try {
                update_record('usr', $record, array('id' => $this->id));
            } catch (Exception $e) {
                throw $e;
                //var_dump($e);
            }
        } else {
            try {
                $this->set('id', insert_record('usr', $record, 'id', true));
            } catch (SQLException $e) {
                throw $e;
            }
        }
        $this->changed = false;
    }

    /** 
     * This function returns a method for a particular
     * activity type, or null if it's not set.
     * 
     * @param int $key the activity type id
     */
    public function get_activity_preference($key) {
        $activityprefs = $this->get('activityprefs');
        return isset($activityprefs[$key]) ? $activityprefs[$key] : null;
    }

    /** @todo document this method */
    public function set_activity_preference($activity, $method) {
        set_activity_preference($this->get('id'), $activity, $method);
        $activityprefs = $this->get('activityprefs');
        $activityprefs[$activity] = $method;
        $this->set('activityprefs', $activityprefs);
    }

    /** 
     * This function returns a value for a particular
     * account preference, or null if it's not set.
     * 
     * @param string $key the field name
     */
    public function get_account_preference($key) {
        $accountprefs = $this->get('accountprefs');
        return isset($accountprefs[$key]) ? $accountprefs[$key] : null;
    }

    /** @todo document this method */
    public function set_account_preference($field, $value) {
        set_account_preference($this->get('id'), $field, $value);
        $accountprefs = $this->get('accountprefs');
        $accountprefs[$field] = $value;
        $this->set('accountprefs', $accountprefs);
    }


    public function get_view_by_type($viewtype) {
        $views = $this->get('views');
        if (isset($views[$viewtype])) {
            $viewid = $views[$viewtype];
        }
        else {
            $viewid = get_field('view', 'id', 'type', $viewtype, 'owner', $this->get('id'));
        }
        if (!$viewid) {
            global $USER;
            if (!$USER->get('id')) {
                return null;
            }
            return $this->install_view($viewtype);
        }
        return new View($viewid);
    }

    /**
     * Return the profile view object for this user.
     *
     * If the user does not yet have a profile view, one is created for them.
     *
     * @return View
     */
    public function get_profile_view() {
        return $this->get_view_by_type('profile');
    }

    /**
     * Installs a user's profile view.
     *
     * @return View
     */
    protected function install_profile_view() {
        static $systemprofileviewid = null;

        db_begin();
        if (is_null($systemprofileviewid)) {
            $systemprofileviewid = get_field('view', 'id', 'owner', 0, 'type', 'profile');
        }

        require_once(get_config('libroot') . 'view.php');
        list($view) = View::create_from_template(array(
            'owner' => $this->get('id'),
            'title' => get_field('view', 'title', 'id', $systemprofileviewid),
            'description' => get_string('profiledescription'),
            'type'  => 'profile',
        ), $systemprofileviewid, $this->get('id'));

        // Add about me block
        $aboutme = new BlockInstance(0, array(
            'blocktype'  => 'profileinfo',
            'title'      => get_string('aboutme', 'blocktype.internal/profileinfo'),
            'view'       => $view->get('id'),
            'column'     => 1,
            'order'      => 1,
        ));
        $configdata = array('artefactids' => array());
        if ($intro = get_field('artefact', 'id', 'owner', $this->get('id'), 'artefacttype', 'introduction')) {
            $configdata['artefactids'][] = $intro;
        }
        else {
            $configdata['introtext'] = get_string('thisistheprofilepagefor', 'mahara', display_name($this, null, true));
        }
        if ($this->get('profileicon')) {
            $configdata['profileicon'] = $this->get('profileicon');
        }
        $aboutme->set('configdata', $configdata);
        $view->addblockinstance($aboutme);

        $view->set_access(array(
            array(
                'type'      => 'loggedin',
                'startdate' => null,
                'stopdate'  => null,
            ),
        ));
        db_commit();

        return $view;
    }

    /**
     * Return the dashboard view object for this user.
     *
     * If the user does not yet have a dashboard view, one is created for them.
     *
     * @return View
     */

    /**
     * Installs a user's dashboard view.
     *
     * @return View
     */
    protected function install_dashboard_view() {
        static $systemdashboardviewid = null;

        db_begin();
        if (is_null($systemdashboardviewid)) {
            $systemdashboardviewid = get_field('view', 'id', 'owner', 0, 'type', 'dashboard');
        }

        require_once(get_config('libroot') . 'view.php');
        list($view) = View::create_from_template(array(
            'owner' => $this->get('id'),
            'title' => get_field('view', 'title', 'id', $systemdashboardviewid),
            'description' => get_string('dashboarddescription'),
            'type'  => 'dashboard',
        ), $systemdashboardviewid, $this->get('id'));

        db_commit();

        return $view;
    }

    protected function install_view($viewtype) {
        $function = 'install_' . $viewtype . '_view';
        return $this->$function();
    }

    // Store the ids of the user's special views (profile, dashboard).  Users can have only
    // one each of these, so there really should be columns in the user table to store them.
    protected function load_views() {
        $types = array('profile', 'dashboard');
        $views = get_records_select_assoc(
            'view',
            '"owner" = ? AND type IN (' . join(',', array_map('db_quote', $types)) . ')',
            array($this->id),
            '',
            'type,id'
        );

        $specialviews = array();
        foreach ($types as $type) {
            if (!empty($views[$type])) {
                $specialviews[$type] = $views[$type]->id;
            }
            else {
                $view = $this->install_view($type);
                $specialviews[$type] = $view->get('id');
            }
        }
        $this->set('views', $specialviews);
    }

    /**
     * Determines if the user is currently logged in
     *
     * @return boolean
     */
    public function is_logged_in() {
        return ($this->get('logout_time') > 0 ? true : false);
    }

    public function to_stdclass() {
        $this->stdclass = new StdClass;
        reset($this->defaults);
        foreach (array_keys($this->defaults) as $k) {
            if ($k == 'expiry' || $k == 'lastlogin' || $k == 'lastlastlogin' || $k == 'lastaccess' || $k == 'suspendedctime' || $k == 'ctime') {
                $this->stdclass->{$k} = db_format_timestamp($this->get($k));
            } else {
                $this->stdclass->{$k} = $this->get($k);//(is_null($this->get($k))? 'NULL' : $this->get($k));
            }
        }
        return $this->stdclass;
    }

    public function quota_add($bytes) {
        if (!is_numeric($bytes) || $bytes < 0) {
            throw new InvalidArgumentException('parameter must be a positive integer to add to the quota');
        }
        if (!$this->quota_allowed($bytes)) {
            throw new QuotaExceededException('Adding ' . $bytes . ' bytes would exceed the user\'s quota');
        }
        $newquota = $this->get('quotaused') + $bytes;
        $this->set("quotaused", $newquota);
        return $this;
    }

    public function quota_remove($bytes) {
        if (!is_numeric($bytes) || $bytes < 0) {
            throw new InvalidArgumentException('parameter must be a positive integer to remove from the quota');
        }
        $newquota = $this->get('quotaused') - $bytes;
        if ($newquota < 0) {
            $newquota = 0;
        }
        $this->set("quotaused", $newquota);
        return $this;
    }

    public function quota_allowed($bytes) {
        if ($this->get('quotaused') + $bytes > $this->get('quota')) {
            return false;
        }

        return true;
    }

    public function quota_init() {
        if (!$this->get('quota')) {
            if ($defaultquota = get_config_plugin('artefact', 'file', 'defaultquota')) {
                $this->set('quota', $defaultquota);
            }
        }
    }

    public function quota_refresh() {
        $quotadata = get_record_sql('SELECT quota, quotaused FROM {usr} WHERE id = ?', array($this->get('id')));
        $this->set('quota', $quotadata->quota);
        $this->set("quotaused", $quotadata->quotaused);
    }

    public function join_institution($institution) {
        if ($institution != 'mahara' && !$this->in_institution($institution)) {
            require_once('institution.php');
            $institution = new Institution($institution);
            $institution->addUserAsMember($this);
            $this->reset_institutions();
        }
    }

    public function leave_institution($institution) {
        if ($institution != 'mahara' && $this->in_institution($institution)) {
            require_once('institution.php');
            $institution = new Institution($institution);
            $institution->removeMember($this->to_stdclass());
        }
    }

    public function in_institution($institution, $role = null) {
        $institutions = $this->get('institutions');
        return isset($institutions[$institution]) 
            && (is_null($role) || $institutions[$institution]->{$role});
    }

    public function is_institutional_admin($institution = null) {
        $a = $this->get('admininstitutions');
        if (is_null($institution)) {
            return !empty($a);
        }
        return isset($a[$institution]);
    }

    public function is_institutional_staff($institution = null) {
        $a = $this->get('staffinstitutions');
        if (is_null($institution)) {
            return !empty($a);
        }
        return isset($a[$institution]);
    }

    public function can_edit_institution($institution = null) {
        return $this->get('admin') || $this->is_institutional_admin($institution);
    }

    /**
     * Returns whether this user is allowed to perform administration type
     * actions on another user.
     *
     * @param mixed $user The user to check we can perform actions on. Can
     *                    either be a User object, a row from the usr table or
     *                    an ID
     */
    public function is_admin_for_user($user) {
        if ($this->get('admin')) {
            return true;
        }
        if (!$this->is_institutional_admin()) {
            return false;
        }

        // Check privileges for institutional admins now
        if ($user instanceof User) {
            $userobj = $user;
        }
        else if (is_numeric($user)) {
            $userobj = new User;
            $userobj->find_by_id($user);
        }
        else if (is_object($user)) {
            // Should be a row from the usr table
            $userobj = new User;
            $userobj->find_by_id($user->id);
        }
        else {
            throw new SystemException("Invalid argument pass to is_admin_for_user method");
        }

        if ($userobj->get('admin')) {
            return false;
        }

        foreach ($userobj->get('institutions') as $i) {
            if ($this->is_institutional_admin($i->institution)) {
                return true;
            }
        }
        return false;
    }

    public function is_staff_for_user($user) {
        if ($this->get('admin') || $this->get('staff')) {
            return true;
        }
        if (!$this->is_institutional_admin() && !$this->is_institutional_staff()) {
            return false;
        }
        if ($user instanceof User) {
            $userinstitutions = $user->get('institutions');
        } else {
            $userinstitutions = load_user_institutions($user->id);
        }
        foreach ($userinstitutions as $i) {
            if ($this->is_institutional_admin($i->institution)
                || $this->is_institutional_staff($i->institution)) {
                return true;
            }
        }
        return false;
    }

    public function add_institution_request($institution, $studentid = null) {
        if (empty($institution) || $institution == 'mahara') {
            return;
        }
        require_once('institution.php');
        $institution = new Institution($institution);
        $institution->addRequestFromUser($this, $studentid);
    }

    public function reset_institutions() {
        $institutions             = load_user_institutions($this->id);
        $admininstitutions = array();
        $staffinstitutions = array();
        $this->theme = get_config('theme');
        foreach ($institutions as $i) {
            if ($i->admin) {
                $admininstitutions[$i->institution] = $i->institution;
            }
            if ($i->staff) {
                $staffinstitutions[$i->institution] = $i->institution;
            }
            if (!empty($i->theme) && $this->theme == get_config('theme') && $i->theme != $this->theme) {
                $this->theme = $i->theme;
            }
        }
        if ($this->authinstance) {
            $authobj = AuthFactory::create($this->authinstance);
            if (isset($institutions[$authobj->institution])) {
                if ($t = $institutions[$authobj->institution]->theme) {
                    $this->theme = $t;
                }
            }
        }
        $this->institutions       = $institutions;
        $this->admininstitutions  = $admininstitutions;
        $this->staffinstitutions  = $staffinstitutions;
    }

    public function reset_grouproles() {
        $memberships = get_records_array('group_member', 'member', $this->get('id'));
        $roles = array();
        if ($memberships) {
            foreach ($memberships as $m) {
                $roles[$m->group] = $m->role;
            }
        }
        $this->set('grouproles', $roles);
    }

    public function can_view_artefact($a) {
        if ($this->get('admin')
            || ($this->get('id') and $this->get('id') == $a->get('owner'))
            || ($a->get('institution') and $this->is_institutional_admin($a->get('institution')))) {
            return true;
        }
        if ($a->get('group')) {
            // Only group artefacts can have artefact_access_role & artefact_access_usr records
            return (bool) count_records_sql("SELECT COUNT(*) FROM {artefact_access_role} ar
                INNER JOIN {group_member} g ON ar.role = g.role
                WHERE ar.artefact = ? AND g.member = ? AND ar.can_view = 1 AND g.group = ?", array($a->get('id'), $this->get('id'), $a->get('group')))
                || record_exists('artefact_access_usr', 'usr', $this->get('id'), 'artefact', $a->get('id'));
        }
        return false;
    }

    public function can_edit_artefact($a) {
        if ($this->get('admin')
            || ($this->get('id') and $this->get('id') == $a->get('owner'))
            || ($a->get('institution') and $this->is_institutional_admin($a->get('institution')))) {
            return true;
        }
        $group = $a->get('group');
        if ($group) {
            return count_records_sql("SELECT COUNT(*) FROM {artefact_access_role} ar
                INNER JOIN {group_member} g ON ar.role = g.role
                WHERE ar.artefact = ? AND g.member = ? AND ar.can_edit = 1 AND g.group = ?", array($a->get('id'), $this->get('id'), $group));
            /*
            require_once(get_config('docroot') . 'lib/group.php');
            $role = group_user_access($group, $this->get('id'));
            if ($role) {
                $aperms = $a->get('rolepermissions');
                return $aperms->{$role}->edit;
            } */
        }
        return false;
    }

    public function can_publish_artefact($a) {
        if (($this->get('id') and $this->get('id') == $a->get('owner'))
            || ($a->get('institution') and $this->is_institutional_admin($a->get('institution')))) {
            return true;
        }
        $group = $a->get('group');
        if ($group) {
            return count_records_sql("SELECT COUNT(*) FROM {artefact_access_role} ar
                INNER JOIN {group_member} g ON ar.role = g.role
                WHERE ar.artefact = ? AND g.member = ? AND ar.can_publish = 1 AND g.group = ?", array($a->get('id'), $this->get('id'), $group));
        }
        return false;
    }

    public function can_edit_view($v) {
        $owner = $v->get('owner');
        if ($owner > 0 && $owner == $this->get('id')) {
            return true;
        }
        $institution = $v->get('institution');
        if ($institution && $this->can_edit_institution($institution)) {
            return true;
        }
        $group = $v->get('group');
        if ($group) {
            $editroles = $v->get('editingroles');
            $this->reset_grouproles();
            if ($v->get('type') == 'grouphomepage' && $this->grouproles[$group] != 'admin') {
                return false;
            }
            return isset($this->grouproles[$group]) && in_array($this->grouproles[$group], $editroles);
        }
        return false;
    }

    /**
     * Function to check current user can edit collection
     *
     * This is fairly straightforward at the moment but it might require more
     * if groups are allowed collections and other amendments in the future
     */
    public function can_edit_collection($c) {
        $owner = $c->get('owner');
        if ($owner == $this->get('id')) {
            return true;
        }
        return false;
    }

    public function can_delete_self() {
        if (!$this->get('admin')) {
            // Users who belong to an institution that doesn't allow
            // registration cannot delete themselves.
            foreach ($this->get('institutions') as $i) {
                if (!$i->registerallowed) {
                    return false;
                }
            }
            return true;
        }
        // The last admin user should not be deleted.
        return count_records('usr', 'admin', 1, 'deleted', 0) > 1;
    }

    /**
     * Makes a literal copy of a list of views for this user.
     *
     * @param array $templateids A list of viewids to copy.
     */
    public function copy_views($templateids, $checkviewaccess=true) {
        if (!$templateids) {
            // Nothing to do
            return;
        }
        if (!is_array($templateids)) {
            throw new SystemException('User->copy_views: templateids must be a list of templates to copy for the user');
        }
        require_once(get_config('libroot') . 'view.php');

        $views = array();
        foreach (get_records_select_array('view', 'id IN (' . implode(', ', db_array_to_ph($templateids)) . ')', $templateids, '', 'id, title, description, type') as $result) {
            $views[$result->id] = $result;
        }

        db_begin();
        foreach ($templateids as $tid) {
            View::create_from_template(array(
                'owner' => $this->get('id'),
                'title' => $views[$tid]->title,
                'description' => $views[$tid]->description,
                'type' => $views[$tid]->type == 'profile' && $checkviewaccess ? 'portfolio' : $views[$tid]->type,
            ), $tid, $this->get('id'), $checkviewaccess);
        }
        db_commit();
    }


}


class LiveUser extends User {

    protected $SESSION;

    public function __construct() {

        parent::__construct();
        $this->SESSION = Session::singleton();

        if ($this->SESSION->is_live()) {
            $this->authenticated  = true;
            while(list($key,) = each($this->defaults)) {
                $this->get($key);
            }
        }
    }

    /**
     * Take a username and password and try to authenticate the
     * user
     *
     * @param  string $username
     * @param  string $password
     * @return bool
     */
    public function login($username, $password) {
        $sql = 'SELECT
                    *, 
                    ' . db_format_tsfield('expiry') . ',
                    ' . db_format_tsfield('lastlogin') . ',
                    ' . db_format_tsfield('lastlastlogin') . ',
                    ' . db_format_tsfield('lastaccess') . ',
                    ' . db_format_tsfield('suspendedctime') . ',
                    ' . db_format_tsfield('ctime') . '
                FROM
                    {usr}
                WHERE
                    LOWER(username) = ?';
        $user = get_record_sql($sql, array(strtolower($username)));

        if ($user == false) {
            throw new AuthUnknownUserException("\"$username\" is not known");
        }

        $siteclosedforupgrade = get_config('siteclosed');
        if ($siteclosedforupgrade && get_config('disablelogin')) {
            global $SESSION;
            $SESSION->add_error_msg(get_string('siteclosedlogindisabled', 'mahara', get_config('wwwroot') . 'admin/upgrade.php'), false);
            return false;
        }
        if (!$user->admin && ($siteclosedforupgrade || get_config('siteclosedbyadmin'))) {
            global $SESSION;
            $SESSION->add_error_msg(get_string('siteclosed'));
            return false;
        }

        // Authentication instances that have parents do so because they cannot 
        // use Mahara's normal login mechanism - for example, XMLRPC. If the 
        // user is using one of these authentication instances, we look and try 
        // to use the parent.
        //
        // There's no code here that prevents the authinstance being tried if 
        // it has no parent, mainly because that's an extra database lookup for 
        // the general case, and the authentication will probably just fail 
        // anyway. (XMLRPC, for example, leaves implementation of 
        // authenticate_user_account to the parent Auth class, which says 'not 
        // authorised' by default).
        $instanceid = $user->authinstance;
        if ($parentid = get_field('auth_instance_config', 'value', 'field', 'parent', 'instance', $instanceid)) {
            $instanceid = $parentid;
        }
        $auth = AuthFactory::create($instanceid);
        
        // catch the AuthInstanceException that allows authentication plugins to
        // fail but pass onto the next possible plugin
        try {
            if ($auth->authenticate_user_account($user, $password)) {
                $this->authenticate($user, $auth->instanceid);
                // Check for a suspended institution
                $authinstance = get_record_sql('
                    SELECT i.suspended, i.displayname
                    FROM {institution} i JOIN {auth_instance} a ON a.institution = i.name
                    WHERE a.id = ?', array($instanceid));
                if ($authinstance->suspended) {
                    $sitename = get_config('sitename');
                    throw new AccessTotallyDeniedException(get_string('accesstotallydenied_institutionsuspended', 'mahara', $authinstance->displayname, $sitename));
                    return false;
                }

                return true;
            }
        }
        catch (AuthInstanceException $e) {
            return false;
        }
        
        // Display a message to users who are only allowed to login via their
        // external application.
        if ($auth->authloginmsg != '') {
            global $SESSION;
            $SESSION->add_info_msg(clean_html($auth->authloginmsg), false);
        }

        return false;
    }

    /**
     * Logs the current user out
     */
    public function logout () {
        if ($this->changed == true) {
            log_debug('Destroying user with un-committed changes');
        }
        $this->set('logout_time', 0);
        if ($this->authenticated === true) {
            $this->SESSION->set('messages', array());
        }

        // Unset session variables related to authentication
        $this->SESSION->set('authinstance', null);
        if (get_config('installed') && !defined('INSTALLER') && $this->get('sessionid')
            && function_exists('table_exists') && table_exists('usr_session')) {
            delete_records('usr_session', 'session', $this->get('sessionid'));
        }

        reset($this->defaults);
        foreach (array_keys($this->defaults) as $key) {
            $this->set($key, $this->defaults[$key]);
        }
        // We don't want to commit the USER object after logout:
        $this->changed = false;
    }

    /**
     * Updates information in a users' session once we know their session is 
     * continuing
     */
    public function renew() {
        $time = time();
        $this->set('logout_time', $time + get_config('session_timeout'));
        $oldlastaccess = $this->get('lastaccess');
        // If there is an access time update frequency, we use a cookie to 
        // prevent updating before this time has expired.
        // If it is set to zero, we always update the accesstime.
        $accesstimeupdatefrequency = get_config('accesstimeupdatefrequency');
        if ($accesstimeupdatefrequency == 0) {
            $this->set('lastaccess', $time);
            $this->commit();
        }
        else if ($oldlastaccess + $accesstimeupdatefrequency < $time) {
            $this->set('lastaccess', $time);
            $this->commit();
        }
    }

    /**
     * When a user creates a security context by whatever method, we do some 
     * standard stuff
     *
     * @param  object $user          Record from the usr table
     * @param  integer $authinstance The ID of the authinstance that the user 
     *                               signed in with
     * @return void
     */
    protected function authenticate($user, $authinstance) {
        $this->authenticated  = true;

        // If the user has reauthenticated and they were an MNET user, we 
        // don't set these variables, because we wish to remember that they 
        // originally SSO-ed in from their other authinstance. See the 
        // session timeout code in auth_setup() for more info.
        if ($this->SESSION->get('mnetuser') != $user->id) {
            $this->SESSION->set('mnetuser', null);
            $this->SESSION->set('authinstance', $authinstance);
        }

        $this->populate($user);
        session_regenerate_id(true);
        $this->lastlastlogin      = $this->lastlogin;
        $this->lastlogin          = time();
        $this->lastaccess         = time();
        $this->sessionid          = session_id();
        $this->logout_time        = time() + get_config('session_timeout');
        $this->sesskey            = get_random_key();

        // We need a user->id before we load_c*_preferences
        if (empty($user->id)) $this->commit();
        $this->activityprefs      = load_activity_preferences($user->id);
        $this->accountprefs       = load_account_preferences($user->id);

        // If user has chosen a language while logged out, save it as their lang pref.
        $sessionlang = $this->SESSION->get('lang');
        if (!empty($sessionlang) && $sessionlang != 'default'
            && (empty($this->accountprefs['lang']) || $sessionlang != $this->accountprefs['lang'])) {
            $this->set_account_preference('lang', $sessionlang);
        }

        // Set language for the current request
        if (!empty($this->accountprefs['lang'])) {
            current_language($this->accountprefs['lang']);
        }

        $this->reset_institutions();
        $this->reset_grouproles();
        $this->load_views();
        $this->store_sessionid();

        $this->commit();

        // finally, after all is done, call the (maybe non existant) hook on their auth plugin
        $authobj = AuthFactory::create($authinstance);
        $authobj->login();
    }

    /**
     * When a user creates a security context by whatever method, we do some 
     * standard stuff
     *
     * @param  int  $user       User ID
     * @param  int  $instanceid Auth Instance ID
     * @return bool             True if user with given ID exists
     */
    public function reanimate($id, $instanceid) {
        if ($user = get_record('usr','id',$id)) {
            $this->authenticate($user, $instanceid);
            return true;
        }
        return false;
    }

    /**
     * Gets the user property keyed by $key.
     *
     * @param string $key The key to get the value of
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function get($key) {
        if (!array_key_exists($key, $this->defaults)) {
            throw new InvalidArgumentException("Invalid key: $key");
        }
        if (null !== ($value = $this->SESSION->get("user/$key"))) {
            return $value;
        }
        return $this->defaults[$key];
    }

    /**
     * Sets the property keyed by $key
     */
    protected function set($key, $value) {

        if (!array_key_exists($key, $this->defaults)) {
            throw new InvalidArgumentException($key);
        }

        // For now, these fields are saved to the DB elsewhere
        if ($key != 'activityprefs' && $key !=  'accountprefs') {
            $this->changed = true;
        }
        $this->SESSION->set("user/$key", $value);
        return $this;
    }

    protected function reloadLiveUser($id=null) {
        if (is_null($id)) {
            $id = $this->get('id');
        }
        $this->find_by_id($id);
        $this->activityprefs = load_activity_preferences($id);
        $this->accountprefs = load_account_preferences($id);
        $this->load_views();
    }

    public function change_identity_to($userid) {
        $user = new User;
        $user->find_by_id($userid);
        if (!$this->is_admin_for_user($user)) {
            throw new AccessDeniedException(get_string('loginasdenied', 'admin'));
        }
        $olduser = $this->get('parentuser');
        if (!is_null($olduser)) {
            throw new UserException(get_string('loginastwice', 'admin'));
        }

        $olduser = new StdClass;
        $olduser->id = $this->get('id');
        $olduser->name = $this->firstname . ' ' . $this->lastname;

        $this->reloadLiveUser($userid);

        $this->set('parentuser', $olduser);
    }

    public function restore_identity() {
        $id = $this->get('id');
        $olduser = $this->get('parentuser');
        if (empty($olduser) || empty($olduser->id)) {
            throw new UserException(get_string('loginasrestorenodata', 'admin'));
        }

        $this->reloadLiveUser($olduser->id);
        $this->set('parentuser', null);
        $this->set('loginanyway', false);

        return $id;
    }

    public function leave_institution($institution) {
        parent::leave_institution($institution);
        $this->find_by_id($this->get('id'));
        $this->reset_institutions();
    }

    public function update_theme() {
        $this->reset_institutions();
        $this->commit();
    }

    public function reset_institutions() {
        global $THEME;
        parent::reset_institutions();
        if (isset($THEME->basename) && $this->theme != $THEME->basename && !defined('INSTALLER')) {
            $THEME = new Theme($this->theme);
        }
    }

    private function store_sessionid() {
        $sessionid = $this->get('sessionid');
        delete_records('usr_session', 'session', $sessionid);
        insert_record('usr_session', (object) array(
            'usr' => $this->get('id'),
            'session' => $sessionid,
            'ctime' => db_format_timestamp(time()),
        ));
    }

}

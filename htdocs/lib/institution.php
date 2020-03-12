<?php
/**
 *
 * @package    mahara
 * @subpackage auth-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

// TODO : lib

defined('INTERNAL') || die();

require_once(get_config('libroot') . 'license.php');

class Institution {

    const   UNINITIALIZED  = 0;
    const   INITIALIZED    = 1;
    const   PERSISTENT     = 2;

    protected $initialized = self::UNINITIALIZED;

    /**
     * The institution properties stored in the institution table, and their default values. The
     * actual instance values will be in this->fields;
     *
     * Note that there's a dual system for institution properties. All required values and several
     * older ones are stored in the institution table itself. Optional and/or newer values are
     * stored in the institution_config table and go in $this->configs
     *
     * TODO: If we have problems with future developers adding columns and forgetting to add them
     * here, perhaps replace this with a system that determines the DB columns of the institution
     * table dynamically, by the same method as insert_record().
     *
     * @var unknown_type
     */
    static $dbfields = array(
        'id' => null,
        'name' => '',
        'displayname' => '',
        'registerallowed' => 0,
        'registerconfirm' => 1,
        'theme' => null,
        'defaultmembershipperiod' => null,
        'maxuseraccounts' => null,
        'expiry' => null,
        'expirymailsent' => 0,
        'suspended' => 0,
        'priority' => 1,
        'defaultquota' => null,
        'showonlineusers' => 2,
        'allowinstitutionpublicviews' => 1,
        'logo' => null,
        'style' => null,
        'licensedefault' => null,
        'licensemandatory' => 0,
        'dropdownmenu' => 0,
        'skins' => 1,
        'tags' => 0
    );

    // This institution's config settings
    protected $configs = array();

    // Configs that have been updated and need to be saved on commit
    protected $dirtyconfigs = array();

    // This institution's properties
    protected $fields = array();

    // Fields that have been updated and need to be saved on commit
    protected $dirtyfields = array();

    public function __construct($name = null) {
        $this->fields = self::$dbfields;

        if (is_null($name)) {
            return $this;
        }

        if (!$this->findByName($name)) {
            throw new ParamOutOfRangeException('No such institution: ' . $name);
        }
    }

    public function __get($name) {

        // If it's an institution DB field, use the setting from $this->fields or null if that's empty for whatever reason
        if (array_key_exists($name, self::$dbfields)) {
            if (array_key_exists($name, $this->fields)) {
                return $this->fields[$name];
            }
            else {
                return null;
            }
        }

        // If there's a config setting for it, use that
        if (array_key_exists($name, $this->configs)) {
            return $this->configs[$name];
        }

        return null;
    }


    public function __set($name, $value) {
        if (!is_string($name)) {
            throw new ParamOutOfRangeException();
        }

        // Validate the DB fields
        switch ($name) {
            // char 255
            case 'name':
            case 'displayname':
                if (!is_string($value) || empty($value) || strlen($value) > 255) {
                    throw new ParamOutOfRangeException("'{$name}' should be a string between 1 and 255 characters in length");
                }
                break;

            // int 1 (i.e. true/false)
            case 'registerallowed':
            case 'skins':
            case 'tags':
            case 'suspended':
            case 'licensemandatory':
            case 'expirymailsent':
                $value = $value ? 1 : 0;
                break;

            case 'id':
            case 'maxuseraccounts':
            case 'showonlineusers':
                $value = (int) $value;
                break;
            case 'defaultmembershipperiod':
                $value = is_null($value) ? null : (int) $value;
                break;
        }

        if (array_key_exists($name, self::$dbfields)) {
            if ($this->fields[$name] !== $value) {
                $this->fields[$name] = $value;
                $this->dirtyfields[$name] = true;
            }
        }
        else {
            // Anything else goes in institution_config.
            // Since it's a DB field, the value must be a number, string, or NULL.
            if (is_bool($value)) {
                $value = $value ? 1 : 0;
            }
            if ($value !== NULL && !is_float($value) && !is_int($value) && !is_string($value)) {
                throw new ParameterException("Attempting to set institution config field \"{$name}\" to a non-scalar value.");
            }

            // A NULL here means you should drop the config from the DB
            $existingvalue = array_key_exists($name, $this->configs) ? $this->configs[$name] : NULL;
            if ($value !== $existingvalue) {
                $this->configs[$name] = $value;
                $this->dirtyconfigs[$name] = true;
            }
        }
    }

    public function findByName($name) {

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

    public function initialise($name, $displayname) {
        if (!is_string($name)) {
            return false;
        }
        $name = strtolower($name);
        if (empty($name)) {
            return false;
        }

        $this->name = $name;

        if (empty($displayname) || !is_string($displayname)) {
            return false;
        }

        $this->displayname = $displayname;
        $this->initialized = max(self::INITIALIZED, $this->initialized);
        $this->dirtyfields = self::$dbfields;
        return true;
    }

    public function verifyReady() {
        if (empty($this->fields['name']) || !is_string($this->fields['name'])) {
            return false;
        }
        if (empty($this->fields['displayname']) || !is_string($this->fields['displayname'])) {
            return false;
        }
        $this->initialized = max(self::INITIALIZED, $this->initialized);
        return true;
    }

    public function commit() {
        if (!$this->verifyReady()) {
            throw new SystemException('Commit failed');
        }

        $result = true;
        if (count($this->dirtyfields)) {
            $record = new stdClass();
            foreach (array_keys($this->dirtyfields) as $fieldname) {
                $record->{$fieldname} = $this->{$fieldname};
            }

            if ($this->initialized == self::INITIALIZED) {
                $result = insert_record('institution', $record);
            }
            else if ($this->initialized == self::PERSISTENT) {
                $result = update_record('institution', $record, array('name' => $this->name));
            }
        }
        if ($result) {
            return $this->_commit_configs();
        }
        else {
            // Shouldn't happen but who noes?
            return false;
        }
    }

    /**
     * Commit the config values for this institution. Called as part of commit();
     */
    protected function _commit_configs() {
        $result = true;
        foreach (array_keys($this->dirtyconfigs) as $confkey) {
            $newvalue = $this->configs[$confkey];

            if ($newvalue === NULL) {
                delete_records('institution_config', 'institution', $this->name, 'field', $confkey);
            }
            else {

                $todb = new stdClass();
                $todb->institution = $this->name;
                $todb->field = $confkey;
                $todb->value = $this->configs[$confkey];

                if (!record_exists('institution_config', 'institution', $this->name, 'field', $confkey)) {
                    $result = $result && insert_record('institution_config', $todb);
                }
                else {
                    $result = $result && update_record('institution_config', $todb, array('institution', 'field'));
                }
            }
        }
        return $result;
    }

    protected function populate($result) {
        foreach (array_keys(self::$dbfields) as $fieldname) {
            $this->{$fieldname} = $result->{$fieldname};
        }
        try {
            $this->configs = get_records_menu('institution_config', 'institution', $result->name, 'field', 'field, value');
        }
        catch (SQLException $e) {
            $this->configs = false;
        }

        if (!$this->configs) {
            $this->configs = array();
        }
        $this->verifyReady();
    }

    public function addUserAsMember($user) {
        global $USER;
        if ($this->isFull()) {
            $this->send_admin_institution_is_full_message();
            die_info(get_string('institutionmaxusersexceeded', 'admin'));
        }
        if (is_numeric($user)) {
            $user = get_record('usr', 'id', $user);
        }

        $lang = get_account_preference($user->id, 'lang');
        if ($lang == 'default') {
            // The user does not have a preset lang preference so we will use the institution if it has one.
            $institution_lang = !empty($this->configs['lang']) ? $this->configs['lang'] : 'default';
            if ($institution_lang != 'default') {
                $lang = $institution_lang;
            }
            else {
                $lang = get_config('lang');
            }
        }

        $userinst = new stdClass();
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
        execute_sql("
            DELETE FROM {tag}
            WHERE resourcetype = ? AND resourceid = ? AND tag " . db_ilike() . " 'lastinstitution:%'",
            array('usr', $user->id)
        );
        // Copy institution views and collection to the user's portfolio
        $checkviewaccess = empty($user->newuser) && !$USER->get('admin');
        $userobj = new User();
        $userobj->find_by_id($user->id);
        $userobj->copy_institution_views_collections_to_new_member($this->name);
        require_once('activity.php');
        activity_occurred('maharamessage', $message);
        handle_event('updateuser', $userinst->usr);

        // Give institution members access to user's profile page
        require_once('view.php');
        if ($profileview = $userobj->get_profile_view()) {
            $profileview->add_owner_institution_access(array($this->name));
        }
        if (is_isolated() && !$userobj->get('admin')) {
            // If isolated institutions are on and this user is not an admin make sure their existing pages
            // are not shared with people outside their new institution
            $toremove1 = get_column_sql("SELECT va.id FROM {view} v JOIN {view_access} va ON va.view = v.id WHERE v.owner = ? AND (va.institution IS NOT NULL AND va.institution != ?)", array($user->id, $this->name));
            $toremove2 = get_column_sql("SELECT va.id FROM {view} v JOIN {view_access} va ON va.view = v.id WHERE v.owner = ? AND (va.usr IS NOT NULL AND va.usr NOT IN (SELECT usr FROM {usr_institution} WHERE institution = ?))", array($user->id, $this->name));
            $toremove3 = get_column_sql("SELECT va.id FROM {view} v JOIN {view_access} va ON va.view = v.id WHERE v.owner = ? AND (va.group IS NOT NULL AND va.group NOT IN (SELECT g.id FROM {group} g WHERE g.institution = ?))", array($user->id, $this->name));
            $toremove = array_merge($toremove1, $toremove2, $toremove3);
            if (!empty($toremove)) {
                delete_records_sql("DELETE FROM {view_access} WHERE id IN (" . join(',', array_map('db_quote', $toremove)) . ")");
            }
        }

        db_commit();
    }

    public function add_members($userids) {
        global $USER;

        if (empty($userids)) {
            return;
        }

        if (!$USER->can_edit_institution($this->name)) {
            throw new AccessDeniedException("Institution::add_members: access denied");
        }

        $values = array_map('intval', $userids);
        array_unshift($values, $this->name);
        $users = get_records_sql_array('
            SELECT u.*, r.confirmedusr
            FROM {usr} u LEFT JOIN {usr_institution_request} r ON u.id = r.usr AND r.institution = ?
            WHERE u.id IN (' . join(',', array_fill(0, count($values) - 1, '?')) . ') AND u.deleted = 0',
            $values
        );

        if (empty($users)) {
            return;
        }

        db_begin();
        foreach ($users as $user) {
            // If the user hasn't requested membership, allow them to be added to
            // the institution anyway so long as the logged-in user is a site admin
            // or institutional admin for the user (in some other institution).
            if (!$user->confirmedusr) {
                $userobj = new User;
                $userobj->from_stdclass($user);
                if (!$USER->is_admin_for_user($userobj)) {
                    continue;
                }
            }
            $this->addUserAsMember($user);
        }
        db_commit();

        foreach ($users as $user) {
            remove_user_sessions($user->id);
        }
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
                'institution' => (object)array('name' => $this->name, 'displayname' => $this->displayname, 'language' => $this->lang),
            );
            db_begin();
            if (!get_config('usersallowedmultipleinstitutions')) {
                delete_records('usr_institution_request', 'usr', $user->id);
            }
            insert_record('usr_institution_request', $request);
            require_once('activity.php');
            activity_occurred('institutionmessage', $message);
            handle_event('updateuser', $user->id);
            // If the total number of accounts has been reached, send an email to the institution
            // and site administrators notifying them of the fact.
            if ($this->isFull()) {
                $this->send_admin_institution_is_full_message();
            }
            db_commit();
        } else if ($request->confirmedinstitution) {
            $this->addUserAsMember($user);
        }
    }

    public function send_admin_institution_is_full_message(){
        // get the site admin and institution admin user records.
        $admins = $this->institution_and_site_admins();
        // check if there are admins - otherwise there are no site admins?!?!?
        if (count($admins) > 0) {
            require_once('activity.php');
            // send an email/message to each amdininistrator based on their specific language.
            foreach ($admins as $index => $id) {
                $lang = get_user_language($id);
                $user = new User();
                $user->find_by_id($id);
                $message = (object) array(
                    'users'   => array($id),
                    'subject' => get_string_from_language($lang, 'institutionmembershipfullsubject'),
                    'message' => get_string_from_language($lang, 'institutionmembershipfullmessagetext', 'mahara',
                            $user->firstname, $this->displayname, get_config('sitename'), get_config('sitename')),
                );
                activity_occurred('maharamessage', $message);
            }
        }
    }
    /**
     * Send a message to the site admin or to the institution admin when a user refuses the privacy statement.
     *
     * If the user is part of an institution and the institution has admin(s), send the message just to the inst. admin(s).
     * Else send the messege to the site admin(s).
     *
     * @param integer $studentid The id of the user who has refused the privacy statement.
     * @param string $reason The reson why the user refused the privacy statement.
     * @param array $whathasbeenrefused The content (privacy statement or terms or both) that the user has refused.
     */
    public function send_admin_institution_refused_privacy_message($studentid, $reason, $whathasbeenrefused) {
        $student = new User();
        $student->find_by_id($studentid);
        $studentname = display_name($student, null, true);

        // Get the institution admin user records.
        $admins = $this->admins();
        // If the user is not part of an institution OR his institution has no admin, send the message to the site admin.
        if (empty($admins)) {
            $admins = $this->institution_and_site_admins();
        }
        $thereasonis = '';
        if ($reason != '') {
            $thereasonis = get_string('thereasonis', 'mahara');
            $reason = '"' . urldecode($reason) . '"';
        }
        $contentrefused = count($whathasbeenrefused) > 1 ? 'privacyandtheterms' : $whathasbeenrefused[0];
        // check if there are admins - otherwise there are no site admins?!?!?
        if (count($admins) > 0) {
            require_once('activity.php');
            // send an email/message to each amdininistrator based on their specific language.
            foreach ($admins as $index => $id) {
                $lang = get_user_language($id);
                $user = new User();
                $user->find_by_id($id);
                $message = (object) array(
                    'users'   => array($id),
                    'subject' => $studentname . ' ' . get_string('hasrefused', 'admin', get_string($contentrefused, 'admin')),
                    'message' => get_string_from_language($lang, 'institutionmemberrefusedprivacy', 'mahara',
                        $user->firstname, $studentname, $student->username, get_string($contentrefused, 'admin'),
                        $thereasonis, $reason, $student->email, get_config('sitename')),
                );
                activity_occurred('maharamessage', $message);
            }
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

    public function decline_requests($userids) {
        global $USER;

        if (!$USER->can_edit_institution($this->name)) {
            throw new AccessDeniedException("Institution::decline_requests: access denied");
        }

        db_begin();
        foreach ($userids as $id) {
            $this->declineRequestFromUser($id);
        }
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
            'institution' => (object)array('name' => $this->name, 'displayname' => $this->displayname, 'language' => $this->lang),
        ));
        handle_event('updateuser', $userid);
        db_commit();
    }

    public function invite_users($userids) {
        global $USER;

        if (!$USER->can_edit_institution($this->name)) {
            throw new AccessDeniedException("Institution::invite_users: access denied");
        }

        db_begin();
        foreach ($userids as $id) {
            $this->inviteUser($id);
        }
        db_commit();
    }

    public function uninvite_users($userids) {
        global $USER;

        if (!$USER->can_edit_institution($this->name)) {
            throw new AccessDeniedException("Institution::uninvite_users: access denied");
        }

        if (!is_array($userids) || empty($userids)) {
            return;
        }

        $ph = array_map('intval', $userids);
        $ph[] = $this->name;

        delete_records_select(
            'usr_institution_request',
            'usr IN (' . join(',', array_fill(0, count($userids), '?')) . ') AND institution = ? AND confirmedinstitution = 1',
            $ph
        );
    }

    public function removeMembers($userids) {
        // Remove self last.
        global $USER;

        if (!$USER->can_edit_institution($this->name)) {
            throw new AccessDeniedException("Institution::removeMembers: access denied");
        }

        $users = get_records_select_array('usr', 'id IN (' . join(',', array_map('intval', $userids)) . ')');
        $removeself = false;
        db_begin();
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
        db_commit();
    }

    public function removeMember($user) {
        global $USER;

        if (is_numeric($user)) {
            $user = get_record('usr', 'id', $user);
        }
        db_begin();
        // If the user is being authed by the institution they are
        // being removed from, change them to internal auth, or if
        // we can't find that, some other no institution auth.
        $authinstances = get_records_select_assoc(
            'auth_instance',
            "institution IN ('mahara', ?) AND active = 1",
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
            else if ($authinstances[$oldauth]->authname != 'internal') {
                $sitename = get_config('sitename');
                $fullname = display_name($user, null, true);
                email_user($user, null,
                    get_string('noinstitutionoldpassemailsubject', 'mahara', $sitename, $this->displayname),
                    get_string('noinstitutionoldpassemailmessagetext', 'mahara', $fullname, $this->displayname, $sitename, $user->username, get_config('wwwroot'), get_config('wwwroot'), $sitename, get_config('wwwroot')),
                    get_string('noinstitutionoldpassemailmessagehtml', 'mahara', hsc($fullname), hsc($this->displayname), hsc($sitename), hsc($user->username), get_config('wwwroot'), get_config('wwwroot'), get_config('wwwroot'), hsc($sitename), get_config('wwwroot'), get_config('wwwroot')));
            }
            update_record('usr', $user);
        }

        // If this user has a favourites list which is updated by this institution, remove it
        // from this institution's control.
        // Don't delete it in case the user wants to keep it, but move it out of the way, so
        // another institution can create a new faves list with the same name.
        execute_sql("
            UPDATE {favorite}
            SET institution = NULL, shortname = substring(shortname from 1 for 100) || '.' || ?
            WHERE owner = ? AND institution = ?",
            array(substr($this->name, 0, 100) . '.' . get_random_key(), $user->id, $this->name)
        );

        execute_sql("
            DELETE FROM {tag}
            WHERE resourcetype = ? AND resourceid = ? AND tag " . db_ilike() . " 'lastinstitution:%'",
            array('usr', $user->id)
        );

        insert_record(
            'tag',
            (object) array(
                'resourcetype' => 'usr',
                'resourceid' => $user->id,
                'ownertype' => 'institution',
                'ownerid' => $this->name,
                'tag' => 'lastinstitution:' . strtolower($this->name),
                'ctime' => db_format_timestamp(time()),
                'editedby' => $USER->get('id'),
            )
        );

        // Need to change any user's "institution tag" tags for this institution
        // into normal user tags
        $typecast = is_postgres() ? '::varchar' : '';
        if ($userinstitutiontags = get_records_sql_array("
            SELECT t.id, t.tag, (SELECT t2.tag FROM {tag} t2 WHERE t2.id" . $typecast . " = SUBSTRING(t.tag, 7)) AS realtag
            FROM {tag} t
            WHERE ownertype = ? AND ownerid = ?
            AND tag LIKE 'tagid_%'", array('user', $user->id))) {

            foreach ($userinstitutiontags as $newtag) {
                execute_sql("UPDATE {tag} SET tag = ? WHERE id = ?", array($newtag->realtag, $newtag->id));
            }
        }

        // If the user's license default is set to "institution default", remove the pref
        delete_records('usr_account_preference', 'usr', $user->id, 'field', 'licensedefault', 'value', LICENSE_INSTITUTION_DEFAULT);

        delete_records('usr_institution', 'usr', $user->id, 'institution', $this->name);
        if (is_isolated() && !$user->admin) {
            // If isolated institutions are on and this user is not an admin make sure their existing pages
            // are not shared with people outside their new institution
            $toremove1 = get_column_sql("SELECT va.id FROM {view} v JOIN {view_access} va ON va.view = v.id WHERE v.owner = ? AND (va.institution IS NOT NULL AND va.institution = ?)", array($user->id, $this->name));
            $toremove2 = get_column_sql("SELECT va.id FROM {view} v JOIN {view_access} va ON va.view = v.id WHERE v.owner = ? AND (va.usr IS NOT NULL AND va.usr IN (SELECT usr FROM {usr_institution} WHERE institution = ?))", array($user->id, $this->name));
            $toremove3 = get_column_sql("SELECT va.id FROM {view} v JOIN {view_access} va ON va.view = v.id WHERE v.owner = ? AND (va.group IS NOT NULL AND va.group IN (SELECT g.id FROM {group} g WHERE g.institution = ?))", array($user->id, $this->name));
            $toremove = array_merge($toremove1, $toremove2, $toremove3);
            if (!empty($toremove)) {
                delete_records_sql("DELETE FROM {view_access} WHERE id IN (" . join(',', array_map('db_quote', $toremove)) . ")");
            }
        }
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
            $pwrequest = new stdClass();
            $pwrequest->usr = $user->id;
            $pwrequest->expiry = db_format_timestamp(time() + 86400);
            $pwrequest->key = get_random_key();
            $sitename = get_config('sitename');
            $fullname = display_name($user, null, true);
            email_user($user, null,
                get_string('noinstitutionsetpassemailsubject', 'mahara', $sitename, $this->displayname),
                get_string('noinstitutionsetpassemailmessagetext', 'mahara', $fullname, $this->displayname, $sitename, $user->username, get_config('wwwroot'), $pwrequest->key, get_config('wwwroot'), $sitename, get_config('wwwroot'), $pwrequest->key),
                get_string('noinstitutionsetpassemailmessagehtml', 'mahara', hsc($fullname), hsc($this->displayname), hsc($sitename), hsc($user->username), get_config('wwwroot'), hsc($pwrequest->key), get_config('wwwroot'), hsc($pwrequest->key), get_config('wwwroot'), hsc($sitename), get_config('wwwroot'), hsc($pwrequest->key), get_config('wwwroot'), hsc($pwrequest->key)));
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
     * Returns the current institution admin member records
     *
     * @return array  A data structure containing site admins
     */
    public function admins() {
        if ($results = get_records_sql_array('
            SELECT u.id FROM {usr} u INNER JOIN {usr_institution} i ON u.id = i.usr
            WHERE i.institution = ? AND u.deleted = 0 AND i.admin = 1', array($this->name))) {
            return array_map('extract_institution_user_id', $results);
        }
        return array();
    }

    /**
     * Returns the current institution and site admin records
     *
     * @return array  A data structure containing site and institution admins
     */
    public function institution_and_site_admins() {
        if ($results = get_records_sql_array('
            SELECT u.id FROM {usr} u INNER JOIN {usr_institution} i ON u.id = i.usr
            WHERE i.institution = ? AND u.deleted = 0 AND i.admin = 1
            UNION
            SELECT u.id FROM {usr} u
            WHERE u.deleted = 0 AND u.admin = 1', array($this->name))) {
            return array_map('extract_institution_user_id', $results);
        }
        return array();
    }

    /**
     * Returns the current institution staff member records
     *
     * @return array  A data structure containing staff
     */
    public function staff() {
        if ($results = get_records_sql_array('
            SELECT u.id FROM {usr} u INNER JOIN {usr_institution} i ON u.id = i.usr
            WHERE i.institution = ? AND u.deleted = 0 AND i.staff = 1', array($this->name))) {
            return array_map('extract_institution_user_id', $results);
        }
        return array();
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

        $querydata = explode(' ', preg_replace('/\s\s+/', ' ', strtolower(trim($query))));
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
            $defaultinstarray = get_records_sql_assoc('
                SELECT COUNT(u.id) AS members, COALESCE(SUM(u.staff), 0) AS staff, COALESCE(SUM(u.admin), 0) AS admins
                FROM {usr} u LEFT OUTER JOIN {usr_institution} i ON u.id = i.usr
                WHERE u.deleted = 0 AND i.usr IS NULL AND u.id != 0
            ', array());
            $defaultinst = current($defaultinstarray);
            $institutions['mahara']->members = $defaultinst->members;
            $institutions['mahara']->staff   = $defaultinst->staff;
            $institutions['mahara']->admins  = $defaultinst->admins;
            $institutions['mahara']->site = true;
            $institutions['mahara']->maxuseraccounts = 0;
        }
        return $institutions;
    }

    /*
    * returns: true if the institution requires admin approval before deleting a user account
    * or it doesn't have the value set for it in the configuration, but the site requires approval by default
    */
    public function requires_user_deletion_approval() {
        /* If site default is set to 'yes', it will be the value for the institutions
         * if it's set to 'no', then we take the value from the institution settings
         */
        return (get_config('defaultreviewselfdeletion') ||
               (isset($this->configs['reviewselfdeletion']) && $this->configs['reviewselfdeletion'])
            );
    }
}

/**
 * Returns an institution dropdown selector
 *
 * @param bool $includedefault           To include the 'mahara' institution in list
 * @param bool $assumesiteadmin          To call this function like you had site admin privileges
 * @param bool $includesitestaff         To allow site staff to see dropdown like the site admin would
 * @param bool $includeinstitutionstaff  To allow institution staff to see dropdown like institution admin would
 * @param bool $allselector              To add an 'all' option to the dropdown where it makes sense, eg in institution statistics page
 * @param bool $withactiveinstitutiontags To only fetch institutions which are configured to define their own tags
 *
 * @return null or array suitable for pieform element
 */
function get_institution_selector($includedefault = true, $assumesiteadmin=false, $includesitestaff=false, $includeinstitutionstaff=false,
    $allselector=false, $withactiveinstitutiontags=false) {
    global $USER;

    if (($assumesiteadmin || $USER->get('admin')) || ($includesitestaff && $USER->get('staff'))) {
        if ($includedefault) {
            $institutions = get_records_array('institution', '', '', 'displayname');
        }
        else {
            $institutions = get_records_select_array('institution', "name != 'mahara'", null, 'displayname');
        }
    }
    else if ($USER->is_institutional_admin() && ($USER->is_institutional_staff() && $includeinstitutionstaff)) {
        // if a user is both an admin for some institution and is a staff member for others
        $institutions = get_records_select_array(
            'institution',
            'name IN (' . join(',', array_map('db_quote',$USER->get('admininstitutions'))) .
                      ',' . join(',', array_map('db_quote',$USER->get('staffinstitutions'))) . ')',
            null, 'displayname'
        );
    }
    else if ($USER->is_institutional_admin()) {
        $institutions = get_records_select_array(
            'institution',
            'name IN (' . join(',', array_map('db_quote',$USER->get('admininstitutions'))) . ')',
            null, 'displayname'
        );
    }
    else if ($includeinstitutionstaff) {
        $institutions = get_records_select_array(
            'institution',
            'name IN (' . join(',', array_map('db_quote',$USER->get('staffinstitutions'))) . ')',
            null, 'displayname'
        );
    }
    else {
        return null;
    }

    if (empty($institutions)) {
        return null;
    }

    $options = array();
    if ($allselector) {
        $options['all'] = get_string('Allinstitutions', 'mahara');
    }
    if ($withactiveinstitutiontags) {
        foreach ($institutions as $i) {
            if ($i->tags) {
                $options[$i->name] = $i->displayname;
            }
        }
    }
    else {
        foreach ($institutions as $i) {
            $options[$i->name] = $i->displayname;
        }
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
    // Special case: $institution == 1 <-> any institution
    if ($institution == 1) {
        $institution = '';
    }
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
        'class' => 'form-inline',
        'checkdirtychange' => false,
        'elements' => array(
            'institution' => $institutionelement,
        )
    ));

    $page = json_encode($page);
    $js = <<< EOF
jQuery(function($) {
    function reloadUsers() {
        var urlstr = $page;
        var inst = '';
        if ($('#institutionselect_institution').length) {
            inst = 'institution=' + $('#institutionselect_institution').val();
            if (urlstr.indexOf('?') > 0) {
                urlstr = urlstr + '&' + inst;
            }
            else {
                urlstr = urlstr + '?' + inst;
            }
        }
        window.location.href = urlstr;
    }

    if ($('#institutionselect_institution').length) {
        $('#institutionselect_institution').on('change', reloadUsers);
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
    global $USER, $CFG;

    $institutions = Institution::count_members($filter, $showdefault, $query, $limit, $offset, $count);
    require_once($CFG->docroot . '/webservice/lib.php');


    $smarty = smarty_core();
    $smarty->assign('institutions', $institutions);
    $smarty->assign('siteadmin', $USER->get('admin'));
    $smarty->assign('webserviceconnections', (bool) count(webservice_connection_definitions()));
    $data['tablerows'] = $smarty->fetch('admin/users/institutionsresults.tpl');

    $pagination = build_pagination(array(
                'id' => 'adminstitutionslist_pagination',
                'datatable' => 'adminstitutionslist',
                'url' => get_config('wwwroot') . 'admin/users/institutions.php' . (($query != '') ? '?query=' . urlencode($query) : ''),
                'jsonscript' => 'admin/users/institutions.json.php',
                'count' => $count,
                'limit' => $limit,
                'offset' => $offset,
                'setlimit' => true,
                'jumplinks' => 4,
                'resultcounttextsingular' => get_string('institution', 'admin'),
                'resultcounttextplural' => get_string('institutions', 'admin'),
            ));

    $data['pagination'] = $pagination['html'];
    $data['pagination_js'] = $pagination['javascript'];

    return $data;
}

function institution_display_name($name) {
    return hsc(get_field('institution', 'displayname', 'name', $name));
}

/**
 * Generate a valid name for the institution.name column, based on the specified display name
 *
 * @param string $displayname
 * @return string
 */
function institution_generate_name($displayname) {
    // iconv can crash on strings that are too long, so truncate before converting
    $basename = mb_substr($displayname, 0, 255);
    $basename = iconv('UTF-8', 'ASCII//TRANSLIT', $displayname);
    $basename = strtolower($basename);
    $basename = preg_replace('/[^a-z]/', '', $basename);
    if (strlen($basename) < 2) {
        $basename = 'inst' . $basename;
    }
    else {
        $basename = substr($basename, 0, 255);
    }

    // Make sure the name is unique. If it is not, add a suffix and see if
    // that makes it unique
    $finalname = $basename;
    $suffix = 'a';
    while (record_exists('institution', 'name', $finalname)) {
        // Add the suffix but make sure the name length doesn't go over 255
        $finalname = substr($basename, 0, 255 - strlen($suffix)) . $suffix;

        // Will iterate a-z, aa-az, ba-bz, etc.
        // See: http://php.net/manual/en/language.operators.increment.php
        $suffix++;
    }

    return $finalname;
}

/**
 * Callback function to extract user ID from an object.
 * @param object $input
 */
function extract_institution_user_id($input) {
    return $input->id;
}

/**
 * Get institution settings elements from artefact plugins.
 *
 * @param Institution $institution
 * @return array
 */
function plugin_institution_prefs_form_elements(Institution $institution = null) {
    $elements = array();
    $installed = plugin_all_installed();
    foreach ($installed as $i) {
        if (!safe_require_plugin($i->plugintype, $i->name)) {
            continue;
        }
        $elements = array_merge($elements, call_static_method(generate_class_name($i->plugintype, $i->name),
                'get_institutionprefs_elements', $institution));
    }
    return $elements;
}

/**
 * Validate plugin institution form values.
 *
 * @param Pieform $form
 * @param array $values
 */
function plugin_institution_prefs_validate(Pieform $form, $values) {
    $elements = array();
    $installed = plugin_all_installed();
    foreach ($installed as $i) {
        if (!safe_require_plugin($i->plugintype, $i->name)) {
            continue;
        }
        call_static_method(generate_class_name($i->plugintype, $i->name), 'institutionprefs_validate', $form, $values);
    }
}

/**
 * Submit plugin institution form values.
 *
 * @param Pieform $form
 * @param array $values
 * @param Institution $institution
 * @return bool is page need to be refreshed
 */
function plugin_institution_prefs_submit(Pieform $form, $values, Institution $institution) {
    $elements = array();
    $installed = plugin_all_installed();
    foreach ($installed as $i) {
        if (!safe_require_plugin($i->plugintype, $i->name)) {
            continue;
        }
        call_static_method(generate_class_name($i->plugintype, $i->name), 'institutionprefs_submit', $form, $values, $institution);
    }
}

/**
 * Get current institution by theme.
 * If the user account theme is set use that otherwise use
 * the first institution the user belongs to.
 *
 * @return $string Name of institution
 */
function get_institution_by_current_theme() {
    global $USER;
    $usrtheme = $USER->get_account_preference('theme');
    if ($usrtheme) {
        $list = (explode('/', $usrtheme));
        if (count($list) > 1 && !empty($list[1])) {
            return $list[1];
        }
    }
    $institutions = $USER->institutions;
    if (!empty($institutions)) {
        return key(array_slice($institutions, 0, 1));
    }
    return 'mahara';
}

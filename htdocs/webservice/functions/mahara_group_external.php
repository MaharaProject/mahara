<?php
/**
 *
 * @package    mahara
 * @subpackage auth-webservice
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * External user API
 *
 * @package    auth
 * @subpackage webservice
 * @copyright  2009 Moodle Pty Ltd (http://moodle.com)
 * @copyright  Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Piers Harding
 */

require_once(get_config('docroot') . 'webservice/lib.php');
require_once(get_config('docroot') . 'webservice/rest/locallib.php');
require_once(get_config('docroot') . 'lib/user.php');
require_once(get_config('docroot') . 'lib/group.php');
require_once(get_config('docroot') . 'lib/institution.php');
require_once(get_config('docroot') . 'lib/searchlib.php');

global $WEBSERVICE_OAUTH_USER;

/**
 * Class container for core Mahara group related API calls
 */
class mahara_group_external extends external_api {

    // possible membership roles
    private static $member_roles = array('admin', 'tutor', 'member');

    /**
     * parameter definition for input of create_groups method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function create_groups_parameters() {

        $group_types = group_get_grouptypes();
        $group_edit_roles = array_keys(group_get_editroles_options());
        $notifyroles = array(get_string('none', 'admin')) + group_get_editroles_options(true);
        foreach ($notifyroles as $key => $role) {
            $group_notify_roles[] = $key . ' = ' . $role;
        }
        return new external_function_parameters(
        array(
                'groups' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'name'            => new external_value(PARAM_RAW, 'Group name'),
                                        'shortname'       => new external_value(PARAM_RAW, 'Group shortname for API only controlled groups', VALUE_OPTIONAL),
                                        'description'     => new external_value(PARAM_NOTAGS, 'Group description'),
                                        'institution'     => new external_value(PARAM_TEXT, 'Mahara institution - required for API controlled groups', VALUE_OPTIONAL),
                                        'grouptype'       => new external_value(PARAM_ALPHANUMEXT, 'Group type: ' . implode(',', $group_types)),
                                        'category'        => new external_value(PARAM_TEXT, 'Group category - the title of an existing group category', VALUE_OPTIONAL),
                                        'forcecategory'   => new external_value(PARAM_BOOL, 'Creates the group category if it does not already exist', VALUE_DEFAULT, '0'),
                                        'editroles'       => new external_value(PARAM_ALPHANUMEXT, 'Edit roles allowed: ' . implode(', ', $group_edit_roles), VALUE_OPTIONAL),
                                        'open'            => new external_value(PARAM_BOOL, 'Open - Users can join the group without approval from group administrators', VALUE_DEFAULT, '0'),
                                        'controlled'      => new external_value(PARAM_BOOL, 'Controlled - Group administrators can add users to the group without their consent, and members cannot choose to leave', VALUE_DEFAULT, '0'),
                                        'request'         => new external_value(PARAM_BOOL, 'Request - Users can send membership requests to group administrators', VALUE_DEFAULT, '0'),
                                        'submitpages'     => new external_value(PARAM_BOOL, 'Submit pages - Members can submit pages to the group', VALUE_DEFAULT),
                                        'public'          => new external_value(PARAM_BOOL, 'Public group', VALUE_DEFAULT),
                                        'viewnotify'      => new external_value(PARAM_INT, 'Shared page notifications allowed: ' . implode(', ', $group_notify_roles), VALUE_DEFAULT),
                                        'feedbacknotify'  => new external_value(PARAM_INT, 'Comment notifications allowed: ' . implode(', ', $group_notify_roles), VALUE_DEFAULT),
                                        'usersautoadded'  => new external_value(PARAM_BOOL, 'Auto-adding users', VALUE_DEFAULT),
                                        'hidden'          => new external_value(PARAM_BOOL, 'Hide group', VALUE_DEFAULT),
                                        'hidemembers'     => new external_value(PARAM_INT, 'Hide membership', VALUE_DEFAULT),
                                        'hidemembersfrommembers' => new external_value(PARAM_INT, 'Hide membership', VALUE_DEFAULT),
                                        'groupparticipationreports' => new external_value(PARAM_BOOL, 'Participation report', VALUE_DEFAULT),
                                        'members'         => new external_multiple_structure(
                                                                new external_single_structure(
                                                                    array(
                                                                        'id'       => new external_value(PARAM_NUMBER, 'member user Id', VALUE_OPTIONAL),
                                                                        'username' => new external_value(PARAM_RAW, 'member username', VALUE_OPTIONAL),
                                                                        'role'     => new external_value(PARAM_ALPHANUMEXT, 'member role: ' . implode(', ', self::$member_roles))
                                                                        ), 'Group membership')
                                                            ),
                                    )
                            )
                    )
            )
        );
    }

    /**
     * Create one or more group
     *
     * @param array $groups  An array of groups to create.
     * @return array An array of arrays describing groups
     */
    public static function create_groups($groups) {
        global $USER, $WEBSERVICE_INSTITUTION;

        // Do basic automatic PARAM checks on incoming data, using params description
        $params = self::validate_parameters(self::create_groups_parameters(), array('groups' => $groups));
        db_begin();
        $groupids = array();
        foreach ($params['groups'] as $group) {
            // Make sure that the group doesn't already exist
            if (!empty($group['name'])) {
                // don't checked deleted as the real function doesn't
                if (get_record('group', 'name', $group['name'])) {
                    throw new WebserviceInvalidParameterException(get_string('groupexists', 'auth.webservice', $group['name']));
                }
            }
            // special API controlled group creations
            else if (isset($group['shortname']) && strlen($group['shortname'])) {
                // check the institution is allowed
                if (isset($group['institution']) && strlen($group['institution'])) {
                    if ($WEBSERVICE_INSTITUTION != $group['institution']) {
                        throw new WebserviceInvalidParameterException('create_groups | ' . get_string('accessdeniedforinstgroup', 'auth.webservice', $group['institution'], $group['name']));
                    }
                    if (!$USER->can_edit_institution($group['institution'])) {
                        throw new WebserviceInvalidParameterException('create_groups | ' . get_string('accessdeniedforinstgroup', 'auth.webservice', $group['institution'], $group['name']));
                    }
                }
                else {
                    throw new WebserviceInvalidParameterException('create_groups | ' . get_string('instmustbeongroup', 'auth.webservice', $group['name'] . '/' . $group['shortname']));
                }
                // does the group exist?
                if (get_record('group', 'shortname', $group['shortname'], 'institution', $group['institution'])) {
                    throw new WebserviceInvalidParameterException(get_string('groupexists', 'auth.webservice', $group['shortname']));
                }
            }
            else {
                throw new WebserviceInvalidParameterException('create_groups | ' . get_string('noname', 'auth.webservice'));
            }

            // convert the category
            if (!empty($group['category'])) {
                $groupcategory = get_record('group_category','title', $group['category']);
                if (!empty($groupcategory)) {
                    $groupcategoryid = $groupcategory->id;
                }
                else if (!empty($group['forcecategory'])) {
                    $categorydata = new stdClass();
                    $categorydata->title = $group['category'];
                    $categorydata->displayorder = 0; // Place holder is updated when we call group_sort_categories.
                    $groupcategoryid = insert_record('group_category', $categorydata, 'id', true);
                    group_sort_categories();
                }
                else {
                    throw new WebserviceInvalidParameterException('create_groups | ' . get_string('catinvalid', 'auth.webservice', $group['category']));
                }
                $group['category'] = $groupcategoryid;
            }

            // validate the join type combinations
            if ($group['open'] && $group['request']) {
                throw new WebserviceInvalidParameterException('create_groups | ' . get_string('invalidjointype', 'auth.webservice', 'open+request'));
            }
            if ($group['open'] && $group['controlled']) {
                throw new WebserviceInvalidParameterException('create_groups | ' . get_string('invalidjointype', 'auth.webservice', 'open+controlled'));
            }

            if (!$group['open'] && !$group['request'] && !$group['controlled']) {
                throw new WebserviceInvalidParameterException('create_groups | ' . get_string('correctjointype', 'auth.webservice'));
            }
            if (isset($group['editroles']) && !in_array($group['editroles'], array_keys(group_get_editroles_options()))) {
                throw new WebserviceInvalidParameterException('create_groups | ' . get_string('groupeditroles', 'auth.webservice', $group['editroles'], implode(', ', array_keys(group_get_editroles_options()))));
            }

            // check that the members exist and we are allowed to administer them
            $members = array($USER->get('id') => 'admin');
            foreach ($group['members'] as $member) {
                if (!empty($member['id'])) {
                    $dbuser = get_record('usr', 'id', $member['id'], 'deleted', 0);
                }
                else if (!empty($member['username'])) {
                    $dbuser = get_record('usr', 'username', $member['username'], 'deleted', 0);
                }
                else {
                    throw new WebserviceInvalidParameterException('create_groups | ' . get_string('nousernameoridgroup', 'auth.webservice', $group['name']));
                }
                if (empty($dbuser)) {
                    throw new WebserviceInvalidParameterException('create_groups | ' . get_string('invalidusergroup', 'auth.webservice', $member['id'] . '/' . $member['username'], $group['name']));
                }

                // check user is in this institution if this is an institution controlled group
                if ((isset($group['shortname']) && strlen($group['shortname'])) && (isset($group['institution']) && strlen($group['institution']))) {
                    if (!mahara_external_in_institution($dbuser, $WEBSERVICE_INSTITUTION)) {
                        throw new WebserviceInvalidParameterException(get_string('notauthforuseridinstitutiongroup', 'auth.webservice', $dbuser->id, $WEBSERVICE_INSTITUTION, $group['shortname']));
                    }
                }
                else {
                    // Make sure auth is valid
                    if (!$authinstance = get_record('auth_instance', 'id', $dbuser->authinstance, 'active', 1)) {
                        throw new WebserviceInvalidParameterException(get_string('invalidauthtype', 'auth.webservice', $dbuser->authinstance));
                    }
                    // check the institution is allowed
                    // basic check authorisation to edit for the current institution of the user
                    if (!$USER->can_edit_institution($authinstance->institution)) {
                        throw new WebserviceInvalidParameterException('create_groups | ' . get_string('accessdeniedforinstuser', 'auth.webservice', $authinstance->institution, $dbuser->username));
                    }
                }
                // check the specified role
                if (!in_array($member['role'], self::$member_roles)) {
                    throw new WebserviceInvalidParameterException('create_groups | ' .  get_string('invalidmemroles', 'auth.webservice', $member['role'], $dbuser->username));
                }
                $members[$dbuser->id]= $member['role'];
            }

            // set the basic elements
            $create = array(
                'shortname'      => (isset($group['shortname']) ? $group['shortname'] : null),
                'name'           => (isset($group['name']) ? $group['name'] : null),
                'description'    => $group['description'],
                'institution'    => (isset($group['institution']) ? $group['institution'] : null),
                'grouptype'      => $group['grouptype'],
                'members'        => $members,
            );

            // check for the rest
            foreach (array('category', 'open', 'controlled', 'request', 'submitpages', 'editroles',
                           'hidemembers', 'invitefriends', 'suggestfriends', 'hidden', 'quota', 'groupparticipationreports',
                           'hidemembersfrommembers', 'public', 'usersautoadded', 'viewnotify', 'feedbacknotify') as $attr) {
                if (isset($group[$attr]) && $group[$attr] !== false && $group[$attr] !== null && strlen("" . $group[$attr])) {
                    $create[$attr] = $group[$attr];
                }
            }

            // create the group
            $create['retainshortname'] = true;
            // Internal function deals with 'submittableto' not 'submitpages'
            $create['submittableto'] = !empty($create['submitpages']) ? 1 : 0;
            $id = group_create($create);

            $groupids[] = array('id'=> $id, 'name'=> $group['name']);
        }
        db_commit();

        return $groupids;
    }

    /**
     * parameter definition for output of create_groups method
     *
     * Returns description of method result value
     * @return external_multiple_structure
     */
    public static function create_groups_returns() {
        return new external_multiple_structure(
                            new external_single_structure(
                                array(
                                        'id'       => new external_value(PARAM_INT, 'group id'),
                                        'name'     => new external_value(PARAM_RAW, 'group name'),
                                )
                            )
                        );
    }

    /**
     * parameter definition for input of delete_groups method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function delete_groups_parameters() {
        return new external_function_parameters(
                    array(
                            'groups' => new external_multiple_structure(
                                            new external_single_structure(
                                                array(
                                                        'id'              => new external_value(PARAM_NUMBER, 'ID of the group', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                                        'name'            => new external_value(PARAM_RAW, 'Group name', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                                        'shortname'       => new external_value(PARAM_RAW, 'Group shortname for API only controlled groups', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                                        'institution'     => new external_value(PARAM_TEXT, 'Mahara institution - required for API controlled groups', VALUE_OPTIONAL),
                                                )
                                            )
                                        )
                                    )
                                );
    }

    /**
     * Delete one or more groups
     *
     * @param array $groups
     */
    public static function delete_groups($groups) {
        global $USER, $WEBSERVICE_INSTITUTION;

        $params = self::validate_parameters(self::delete_groups_parameters(), array('groups'=>$groups));

        db_begin();
        foreach ($params['groups'] as $group) {
            // Make sure that the group already exists
            if (!empty($group['id'])) {
                if (!$dbgroup = get_group_by_id($group['id'])) {
                    throw new WebserviceInvalidParameterException('delete_groups | ' . get_string('groupnotexist', 'auth.webservice', $group['id']));
                }
            }
            else if (!empty($group['name'])) {
                if (!$dbgroup = get_record('group', 'name', $group['name'], 'deleted', 0)) {
                    throw new WebserviceInvalidParameterException('delete_groups | ' . get_string('groupnotexist', 'auth.webservice', $group['name']));
                }
            }
            else if (!empty($group['shortname'])) {
                if (empty($group['institution'])) {
                    throw new WebserviceInvalidParameterException('delete_groups | ' . get_string('instmustset', 'auth.webservice', $group['shortname']));
                }
                if (!$dbgroup = get_record('group', 'shortname', $group['shortname'], 'institution', $group['institution'], 'deleted', 0)) {
                    throw new WebserviceInvalidParameterException('delete_groups | ' . get_string('groupnotexist', 'auth.webservice', $group['shortname'] . '/' . $group['institution']));
                }
            }
            else {
                throw new WebserviceInvalidParameterException('delete_groups | ' . get_string('nogroup', 'auth.webservice'));
            }

            // are we allowed to delete for this institution
            if (!empty($dbgroup->institution)) {
                if ($WEBSERVICE_INSTITUTION != $dbgroup->institution) {
                    throw new WebserviceInvalidParameterException('delete_groups | ' . get_string('accessdeniedforinstgroup', 'auth.webservice', $group['institution'], $group['name']));
                }
                if (!$USER->can_edit_institution($dbgroup->institution)) {
                    throw new WebserviceInvalidParameterException('delete_groups | ' . get_string('accessdeniedforinstgroup', 'auth.webservice', $group['institution'], $group['shortname']));
                }
            }

            // now do the delete
            group_delete($dbgroup->id);
        }
        db_commit();

        return null;
    }

    /**
     * parameter definition for output of delete_groups method
     */
    public static function delete_groups_returns() {
        return null;
    }

    /**
     * parameter definition for input of update_groups method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function update_groups_parameters() {

        $group_types = group_get_grouptypes();
        $group_edit_roles = array_keys(group_get_editroles_options());
        $notifyroles = array(get_string('none', 'admin')) + group_get_editroles_options(true);
        foreach ($notifyroles as $key => $role) {
            $group_notify_roles[] = $key . ' = ' . $role;
        }
        return new external_function_parameters(
                    array(
                        'groups' =>
                            new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                            'id'              => new external_value(PARAM_NUMBER, 'ID of the group', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                            'name'            => new external_value(PARAM_RAW, 'Group name', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                            'shortname'       => new external_value(PARAM_RAW, 'Group shortname for API only controlled groups', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                            'description'     => new external_value(PARAM_NOTAGS, 'Group description'),
                                            'institution'     => new external_value(PARAM_TEXT, 'Mahara institution - required for API controlled groups', VALUE_OPTIONAL),
                                            'grouptype'       => new external_value(PARAM_ALPHANUMEXT, 'Group type: ' . implode(',', $group_types), VALUE_OPTIONAL),
                                            'category'        => new external_value(PARAM_TEXT, 'Group category - the title of an existing group category', VALUE_OPTIONAL),
                                            'forcecategory'   => new external_value(PARAM_BOOL, 'Creates the group category if it does not already exist', VALUE_DEFAULT, '0'),
                                            'editroles'       => new external_value(PARAM_ALPHANUMEXT, 'Edit roles allowed: ' . implode(', ', $group_edit_roles), VALUE_OPTIONAL),
                                            'open'            => new external_value(PARAM_BOOL, 'Open - Users can join the group without approval from group administrators', VALUE_DEFAULT),
                                            'controlled'      => new external_value(PARAM_BOOL, 'Controlled - Group administrators can add users to the group without their consent, and members cannot choose to leave', VALUE_DEFAULT),
                                            'request'         => new external_value(PARAM_BOOL, 'Request - Users can send membership requests to group administrators', VALUE_DEFAULT),
                                            'submitpages'     => new external_value(PARAM_BOOL, 'Submit pages - Members can submit pages to the group', VALUE_DEFAULT),
                                            'public'          => new external_value(PARAM_BOOL, 'Public group', VALUE_DEFAULT),
                                            'viewnotify'      => new external_value(PARAM_INT, 'Shared page notifications allowed: ' . implode(', ', $group_notify_roles), VALUE_DEFAULT),
                                            'feedbacknotify'  => new external_value(PARAM_INT, 'Comment notifications allowed: ' . implode(', ', $group_notify_roles), VALUE_DEFAULT),
                                            'usersautoadded'  => new external_value(PARAM_BOOL, 'Auto-adding users', VALUE_DEFAULT),
                                            'hidden'          => new external_value(PARAM_BOOL, 'Hide group', VALUE_DEFAULT),
                                            'hidemembers'     => new external_value(PARAM_INT, 'Hide membership', VALUE_DEFAULT),
                                            'hidemembersfrommembers' => new external_value(PARAM_INT, 'Hide membership', VALUE_DEFAULT),
                                            'groupparticipationreports' => new external_value(PARAM_BOOL, 'Participation report', VALUE_DEFAULT),
                                            'members'         => new external_multiple_structure(
                                                                    new external_single_structure(
                                                                        array(
                                                                                'id'       => new external_value(PARAM_NUMBER, 'member user Id', VALUE_OPTIONAL, null, NULL_ALLOWED, 'memberid'),
                                                                                'username' => new external_value(PARAM_RAW, 'member username', VALUE_OPTIONAL, null, NULL_ALLOWED, 'memberid'),
                                                                                'role'     => new external_value(PARAM_ALPHANUMEXT, 'member role: ' . implode(', ', self::$member_roles))
                                                                        ), 'Group membership')
                                                                    ),
                                                                )
                                                            )
                                                        )
                                                    )
                                                );
    }

    /**
     * update one or more groups
     *
     * @param array $groups
     */
    public static function update_groups($groups) {
        global $USER, $WEBSERVICE_INSTITUTION;

        // Do basic automatic PARAM checks on incoming data, using params description
        $params = self::validate_parameters(self::update_groups_parameters(), array('groups'=>$groups));

        db_begin();
        $groupids = array();
        foreach ($params['groups'] as $group) {
            // Make sure that the group already exists
            if (!empty($group['id'])) {
                if (!$dbgroup = get_group_by_id($group['id'])) {
                    throw new WebserviceInvalidParameterException('update_groups | ' . get_string('groupnotexist', 'auth.webservice', $group['id']));
                }
            }
            else if (!empty($group['shortname'])) {
                if (empty($group['institution'])) {
                    throw new WebserviceInvalidParameterException('update_groups | ' . get_string('instmustset', 'auth.webservice', $group['shortname']));
                }
                if (!$dbgroup = get_record('group', 'shortname', $group['shortname'], 'institution', $group['institution'], 'deleted', 0)) {
                    throw new WebserviceInvalidParameterException('update_groups | ' . get_string('groupnotexist', 'auth.webservice', $group['shortname'] . '/' . $group['institution']));
                }
            }
            else if (!empty($group['name'])) {
                if (!$dbgroup = get_record('group', 'name', $group['name'], 'deleted', 0)) {
                    throw new WebserviceInvalidParameterException('update_groups | ' . get_string('groupnotexist', 'auth.webservice', $group['name']));
                }
            }
            else {
                throw new WebserviceInvalidParameterException('update_groups | ' . get_string('nogroup', 'auth.webservice'));
            }

            // are we in correct institution to be allowed to update this group
            if ($WEBSERVICE_INSTITUTION != $dbgroup->institution) {
                throw new WebserviceInvalidParameterException('update_groups | ' . get_string('accessdeniedforinstgroup', 'auth.webservice', $group['institution'], $group['name']));
            }
            if (!$USER->can_edit_institution($dbgroup->institution)) {
                throw new WebserviceInvalidParameterException('update_groups | ' . get_string('accessdeniedforinstgroup', 'auth.webservice', $group['institution'], $group['shortname']));
            }

            // convert the category
            if (!empty($group['category'])) {
                $groupcategory = get_record('group_category','title', $group['category']);
                if (!empty($groupcategory)) {
                    $groupcategoryid = $groupcategory->id;
                }
                else if (!empty($group['forcecategory'])) {
                    $categorydata = new stdClass();
                    $categorydata->title = $group['category'];
                    $categorydata->displayorder = 0; // Place holder is updated when we call group_sort_categories.
                    $groupcategoryid = insert_record('group_category', $categorydata, 'id', true);
                    group_sort_categories();
                }
                else {
                    throw new WebserviceInvalidParameterException('update_groups | ' . get_string('catinvalid', 'auth.webservice', $group['category']));
                }
                $group['category'] = $groupcategoryid;
            }

            // validate the join type combinations
            if (isset($group['open']) || isset($group['request']) || isset($group['controlled'])) {
                foreach (array('open', 'request', 'controlled') as $membertype) {
                    if (!isset($group[$membertype]) || empty($group[$membertype])) {
                        $group[$membertype] = 0;
                    }
                }
                if ($group['open'] && $group['request']) {
                    throw new WebserviceInvalidParameterException('update_groups | ' . get_string('invalidjointype', 'auth.webservice', 'open+request'));
                }
                if ($group['open'] && $group['controlled']) {
                    throw new WebserviceInvalidParameterException('update_groups | ' . get_string('invalidjointype', 'auth.webservice', 'open+controlled'));
                }

                if (!$group['open'] && !$group['request'] && !$group['controlled']) {
                    throw new WebserviceInvalidParameterException('update_groups | ' . get_string('correctjointype', 'auth.webservice'));
                }
            }
            if (isset($group['editroles']) && !in_array($group['editroles'], array_keys(group_get_editroles_options()))) {
                throw new WebserviceInvalidParameterException('update_groups | ' . get_string('groupeditroles', 'auth.webservice', $group['editroles'], implode(', ', array_keys(group_get_editroles_options()))));
            }

            // check that the members exist and we are allowed to administer them
            $members = array($USER->get('id') => 'admin');
            foreach ($group['members'] as $member) {
                if (!empty($member['id'])) {
                    $dbuser = get_record('usr', 'id', $member['id'], 'deleted', 0);
                }
                else if (!empty($member['username'])) {
                    $dbuser = get_record('usr', 'username', $member['username'], 'deleted', 0);
                }
                else {
                    throw new WebserviceInvalidParameterException('update_groups | ' . get_string('nousernameoridgroup', 'auth.webservice', $group['name']));
                }
                if (empty($dbuser)) {
                    throw new WebserviceInvalidParameterException('update_groups | ' . get_string('invalidusergroup', 'auth.webservice', $member['id'] . '/' . $member['username'], $group['name']));
                }

                // check user is in this institution if this is an institution controlled group
                if (!empty($dbgroup->shortname) && !empty($dbgroup->institution)) {
                    if (!mahara_external_in_institution($dbuser, $WEBSERVICE_INSTITUTION)) {
                        throw new WebserviceInvalidParameterException('update_groups | ' . get_string('notauthforuseridinstitutiongroup', 'auth.webservice', $dbuser->id, $WEBSERVICE_INSTITUTION, $group['shortname']));
                    }
                }
                else {
                    // Make sure auth is valid
                    if (!$authinstance = get_record('auth_instance', 'id', $dbuser->authinstance, 'active', 1)) {
                        throw new WebserviceInvalidParameterException('update_groups | ' . get_string('invalidauthtype', 'auth.webservice', $dbuser->authinstance));
                    }
                    // check the institution is allowed
                    // basic check authorisation to edit for the current institution of the user
                    if (!$USER->can_edit_institution($authinstance->institution)) {
                        throw new WebserviceInvalidParameterException('update_groups | ' . get_string('accessdeniedforinstuser', 'auth.webservice', $authinstance->institution, $dbuser->username));
                    }
                }

                // check the specified role
                if (!in_array($member['role'], self::$member_roles)) {
                    throw new WebserviceInvalidParameterException('update_groups | ' .  get_string('invalidmemroles', 'auth.webservice', $member['role'], $dbuser->username));
                }
                $members[$dbuser->id] = $member['role'];
            }

            // build up the changes
            // not allowed to change these
            $newvalues = (object) array('id'  => $dbgroup->id,);
            foreach (array('name', 'description', 'grouptype', 'category', 'editroles',
                           'open', 'controlled', 'request', 'submitpages', 'quota',
                           'hidemembers', 'invitefriends', 'suggestfriends',
                           'hidden', 'hidemembersfrommembers', 'groupparticipationreports',
                           'usersautoadded', 'public', 'viewnotify', 'feedbacknotify') as $attr) {
                if (isset($group[$attr]) && $group[$attr] !== false && $group[$attr] !== null && strlen("" . $group[$attr])) {
                    $newvalues->{$attr} = $group[$attr];
                }
            }
            // Internal function deals with 'submittableto' not 'submitpages'
            $newvalues->submittableto = !empty($newvalues->submitpages) ? 1 : 0;
            group_update($newvalues);

            // now update the group membership
            group_update_members($dbgroup->id, $members);

        }
        db_commit();

        return null;
    }

    /**
     * parameter definition for output of update_groups method
     */
    public static function update_groups_returns() {
        return null;
    }

    /**
     * parameter definition for input of update_group_details method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function update_groups_details_parameters() {

        return new external_function_parameters (
            array(
                'groups' =>
                    new external_multiple_structure (
                        new external_single_structure (
                            array(
                                'id'              => new external_value(PARAM_NUMBER, 'ID of the group', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                'name'            => new external_value(PARAM_RAW, 'Group name', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                'shortname'       => new external_value(PARAM_RAW, 'Group shortname for API only controlled groups', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                'institution'     => new external_value(PARAM_TEXT, 'Mahara institution - required for API controlled groups', VALUE_OPTIONAL),
                                'description'     => new external_value(PARAM_NOTAGS, 'Group description'),
                                'category'        => new external_value(PARAM_TEXT, 'Group category - the title of an existing group category', VALUE_OPTIONAL),
                                'forcecategory'   => new external_value(PARAM_BOOL, 'Creates the group category if it does not already exist', VALUE_DEFAULT, '0'),
                            )
                        )
                    )
            )
        );
    }

    /**
     * update the basic details of one or more groups
     *
     * @param array $groups
     */
    public static function update_groups_details($groups) {
        global $USER, $WEBSERVICE_INSTITUTION;

        // Do basic automatic PARAM checks on incoming data, using params description
        $params = self::validate_parameters(self::update_groups_details_parameters(), array('groups' => $groups));

        db_begin();
        $groupids = array();
        foreach ($params['groups'] as $group) {
            // Make sure that the group already exists
            if (!empty($group['id'])) {
                if (!$dbgroup = get_group_by_id($group['id'])) {
                    throw new WebserviceInvalidParameterException('update_groups | ' . get_string('groupnotexist', 'auth.webservice', $group['id']));
                }
            }
            else if (!empty($group['shortname'])) {
                if (empty($group['institution'])) {
                    throw new WebserviceInvalidParameterException('update_groups | ' . get_string('instmustset', 'auth.webservice', $group['shortname']));
                }
                if (!$dbgroup = get_record('group', 'shortname', $group['shortname'], 'institution', $group['institution'], 'deleted', 0)) {
                    throw new WebserviceInvalidParameterException('update_groups | ' . get_string('groupnotexist', 'auth.webservice', $group['shortname'] . '/' . $group['institution']));
                }
            }
            else if (!empty($group['name'])) {
                if (!$dbgroup = get_record('group', 'name', $group['name'], 'deleted', 0)) {
                    throw new WebserviceInvalidParameterException('update_groups | ' . get_string('groupnotexist', 'auth.webservice', $group['name']));
                }
            }
            else {
                throw new WebserviceInvalidParameterException('update_groups | ' . get_string('nogroup', 'auth.webservice'));
            }

            // are we in correct institution to be allowed to update this group
            if ($WEBSERVICE_INSTITUTION != $dbgroup->institution) {
                throw new WebserviceInvalidParameterException('update_groups | ' . get_string('accessdeniedforinstgroup', 'auth.webservice', $group['institution'], $group['name']));
            }
            if (!$USER->can_edit_institution($dbgroup->institution)) {
                throw new WebserviceInvalidParameterException('update_groups | ' . get_string('accessdeniedforinstgroup', 'auth.webservice', $group['institution'], $group['shortname']));
            }

            // convert the category
            if (!empty($group['category'])) {
                $groupcategory = get_record('group_category','title', $group['category']);
                if (!empty($groupcategory)) {
                    $groupcategoryid = $groupcategory->id;
                }
                else if (!empty($group['forcecategory'])) {
                    $categorydata = new stdClass();
                    $categorydata->title = $group['category'];
                    $categorydata->displayorder = 0; // Place holder is updated when we call group_sort_categories.
                    $groupcategoryid = insert_record('group_category', $categorydata, 'id', true);
                    group_sort_categories();
                }
                else {
                    throw new WebserviceInvalidParameterException('update_groups | ' . get_string('catinvalid', 'auth.webservice', $group['category']));
                }
                $group['category'] = $groupcategoryid;
            }

            // build up the changes
            $newvalues = (object) array('id'  => $dbgroup->id);
            foreach (array('name', 'description', 'category') as $attr) {
                if (isset($group[$attr]) && $group[$attr] !== false && $group[$attr] !== null && strlen("" . $group[$attr])) {
                    $newvalues->{$attr} = $group[$attr];
                }
            }
            group_update($newvalues);
        }
        db_commit();

        return null;
    }

    /**
     * parameter definition for output of update_groups method
     */
    public static function update_groups_details_returns() {
        return null;
    }

    /**
     * parameter definition for input of update_group_members method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function update_group_members_parameters() {

        return new external_function_parameters(
                    array(
                            'groups' =>
                                new external_multiple_structure(
                                    new external_single_structure(
                                        array(
                                                'id'              => new external_value(PARAM_NUMBER, 'ID of the group', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                                'name'            => new external_value(PARAM_RAW, 'Group name', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                                'shortname'       => new external_value(PARAM_RAW, 'Group shortname for API only controlled groups', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                                'institution'     => new external_value(PARAM_TEXT, 'Mahara institution - required for API controlled groups', VALUE_OPTIONAL),
                                                'members'         => new external_multiple_structure(
                                                                        new external_single_structure(
                                                                            array(
                                                                                    'id'       => new external_value(PARAM_NUMBER, 'member user Id', VALUE_OPTIONAL, null, NULL_ALLOWED, 'memberid'),
                                                                                    'username' => new external_value(PARAM_RAW, 'member username', VALUE_OPTIONAL, null, NULL_ALLOWED, 'memberid'),
                                                                                    'role'     => new external_value(PARAM_ALPHANUMEXT, 'member role: admin, tutor, member', VALUE_OPTIONAL),
                                                                                    'action'   => new external_value(PARAM_ALPHANUMEXT, 'member action: add, or remove')
                                                                                ), 'Group membership actions')
                                                                        ),
                                                                    )
                                                                )
                                                            )
                                                        )
                                                    );
    }

    /**
     * update one or more sets of group membership
     *
     * @param array $groups
     */
    public static function update_group_members($groups) {
        global $USER, $WEBSERVICE_INSTITUTION;

        // Do basic automatic PARAM checks on incoming data, using params description
        $params = self::validate_parameters(self::update_group_members_parameters(), array('groups'=>$groups));

        db_begin();
        $groupids = array();
        foreach ($params['groups'] as $group) {
            // Make sure that the group doesn't already exist
            if (!empty($group['id'])) {
                if (!$dbgroup = get_group_by_id($group['id'])) {
                    throw new WebserviceInvalidParameterException('update_group_members | ' . get_string('groupnotexist', 'auth.webservice', $group['id']));
                }
            }
            else if (!empty($group['name'])) {
                if (!$dbgroup = get_record('group', 'name', $group['name'], 'deleted', 0)) {
                    throw new WebserviceInvalidParameterException('update_group_members | ' . get_string('groupnotexist', 'auth.webservice', $group['name']));
                }
            }
            else if (!empty($group['shortname'])) {
                if (empty($group['institution'])) {
                    throw new WebserviceInvalidParameterException('update_group_members | ' . get_string('instmustset', 'auth.webservice', $group['shortname']));
                }
                if (!$dbgroup = get_record('group', 'shortname', $group['shortname'], 'institution', $group['institution'], 'deleted', 0)) {
                    throw new WebserviceInvalidParameterException('update_group_members | ' . get_string('groupnotexist', 'auth.webservice', $group['shortname'] . '/' . $group['institution']));
                }
            }
            else {
                throw new WebserviceInvalidParameterException('update_group_members | ' . get_string('nogroup', 'auth.webservice'));
            }

            // are we allowed to administer this group
            if (!empty($dbgroup->institution) && $WEBSERVICE_INSTITUTION != $dbgroup->institution) {
                throw new WebserviceInvalidParameterException('update_group_members | ' . get_string('accessdeniedforinstgroup', 'auth.webservice', $group['institution'], $group['name']));
            }
            if (!empty($dbgroup->institution) && !$USER->can_edit_institution($dbgroup->institution)) {
                throw new WebserviceInvalidParameterException('update_group_members | ' . get_string('accessdeniedforinstgroup', 'auth.webservice', $group['institution'], $group['shortname']));
            }

            // get old members
            $oldmembers = get_records_array('group_member', 'group', $dbgroup->id, '', 'member,role');
            $existingmembers = array();
            if (!empty($oldmembers)) {
                foreach ($oldmembers as $member) {
                    $existingmembers[$member->member] = $member->role;
                }
            }

            // check that the members exist and we are allowed to administer them
            foreach ($group['members'] as $member) {
                if (!empty($member['id'])) {
                    $dbuser = get_record('usr', 'id', $member['id'], 'deleted', 0);
                }
                else if (!empty($member['username'])) {
                    $dbuser = get_record('usr', 'username', $member['username'], 'deleted', 0);
                }
                else {
                    throw new WebserviceInvalidParameterException('update_group_members | ' . get_string('nousernameoridgroup', 'auth.webservice', $group['name']));
                }
                if (empty($dbuser)) {
                    throw new WebserviceInvalidParameterException('update_group_members | ' . get_string('invalidusergroup', 'auth.webservice', $member['id'] . '/' . $member['username'], $group['name']));
                }


                // check user is in this institution if this is an institution controlled group
                if (!empty($dbgroup->shortname) && !empty($dbgroup->institution)) {
                    if (!mahara_external_in_institution($dbuser, $WEBSERVICE_INSTITUTION)) {
                        throw new WebserviceInvalidParameterException('update_group_members | ' . get_string('notauthforuseridinstitutiongroup', 'auth.webservice', $dbuser->id, $WEBSERVICE_INSTITUTION, $group['shortname']));
                    }
                }
                else {
                    // Make sure auth is valid
                    if (!$authinstance = get_record('auth_instance', 'id', $dbuser->authinstance, 'active', 1)) {
                        throw new WebserviceInvalidParameterException('update_group_members | ' . get_string('invalidauthtype', 'auth.webservice', $dbuser->authinstance));
                    }
                    // check the institution is allowed
                    // basic check authorisation to edit for the current institution of the user
                    if (!$USER->can_edit_institution($authinstance->institution)) {
                        throw new WebserviceInvalidParameterException('update_group_members | ' . get_string('accessdeniedforinstuser', 'auth.webservice', $authinstance->institution, $dbuser->username));
                    }
                }

                // determine the changes to the group membership
                if ($member['action'] == 'remove') {
                    if (isset($existingmembers[$dbuser->id])) {
                        unset($existingmembers[$dbuser->id]);
                    }
                    // silently fail
                }
                // add also can be used to update role
                else if ($member['action'] == 'add') {
                    // check the specified role
                    if (!in_array($member['role'], self::$member_roles)) {
                        throw new WebserviceInvalidParameterException('update_group_members | ' .  get_string('invalidmemroles', 'auth.webservice', $member['role'], $dbuser->username));
                    }
                    $existingmembers[$dbuser->id] = $member['role'];
                    // silently fail
                }
                else {
                    throw new WebserviceInvalidParameterException('update_group_members | ' . get_string('membersinvalidaction', 'auth.webservice', $member['action'], $dbuser->id . '/' . $dbuser->username, $group['name']));
                }
            }

            // now update the group membership
            group_update_members($dbgroup->id, $existingmembers);

        }
        db_commit();

        return null;
    }

    /**
     * parameter definition for output of update_group_members method
     */
    public static function update_group_members_returns() {
        return null;
    }

    /**
     * parameter definition for input of get_groups_by_id method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_groups_by_id_parameters() {
        return new external_function_parameters(
                    array(
                            'groups' => new external_multiple_structure(
                                            new external_single_structure(
                                                array(
                                                        'id'              => new external_value(PARAM_NUMBER, 'ID of the group', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                                        'shortname'       => new external_value(PARAM_RAW, 'Group shortname for API only controlled groups', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                                        'institution'     => new external_value(PARAM_TEXT, 'Mahara institution - required for API controlled groups', VALUE_OPTIONAL),
                                                    )
                                                )
                                            )
                                        )
                                    );
    }

    /**
     * Get user information for one or more groups
     *
     * @param array $groups  array of groups
     * @return array An array of arrays describing groups
     */
    public static function get_groups_by_id($groups) {
        global $WEBSERVICE_INSTITUTION, $USER;

        $params = self::validate_parameters(self::get_groups_by_id_parameters(),
        array('groups' => $groups));

        // if this is a get all users - then lets get them all
        if (empty($params['groups'])) {
            $params['groups'] = array();
            $dbgroups = get_records_sql_array('SELECT * FROM {group} WHERE institution = ? AND deleted = ?', array($WEBSERVICE_INSTITUTION, 0));
            if ($dbgroups) {
                foreach ($dbgroups as $dbgroup) {
                    $params['groups'][] = array('id' => $dbgroup->id);
                }
            }
        }

        // now process the ids
        $groups = array();
        foreach ($params['groups'] as $group) {
            // Make sure that the group doesn't already exist
            if (!empty($group['id'])) {
                if (!$dbgroup = get_group_by_id($group['id'])) {
                    throw new WebserviceInvalidParameterException('get_groups_by_id | ' . get_string('groupnotexist', 'auth.webservice', $group['id']));
                }
            }
            else if (!empty($group['shortname'])) {
                if (empty($group['institution'])) {
                    throw new WebserviceInvalidParameterException('get_groups_by_id | ' . get_string('instmustset', 'auth.webservice', $group['shortname']));
                }
                if (!$dbgroup = get_record('group', 'shortname', $group['shortname'], 'institution', $group['institution'], 'deleted', 0)) {
                    throw new WebserviceInvalidParameterException('get_groups_by_id | ' . get_string('groupnotexist', 'auth.webservice', $group['shortname']));
                }
            }
            else {
                throw new WebserviceInvalidParameterException('get_groups_by_id | ' . get_string('nogroup', 'auth.webservice'));
            }

            // must have access to the related institution
            if ($WEBSERVICE_INSTITUTION != $dbgroup->institution) {
                throw new WebserviceInvalidParameterException('get_group (' . $WEBSERVICE_INSTITUTION . ') | ' . get_string('accessdeniedforinstgroup', 'auth.webservice', $dbgroup->institution, $dbgroup->shortname));
            }
            if (!$USER->can_edit_institution($dbgroup->institution)) {
                throw new WebserviceInvalidParameterException('get_group | ' . get_string('accessdeniedforinstgroup', 'auth.webservice', $dbgroup->institution, $dbgroup->shortname));
            }

            // get the members
            $dbmembers = get_records_sql_array('SELECT gm.member AS userid, u.username AS username, gm.role AS role FROM {group_member} gm LEFT JOIN {usr} u ON gm.member = u.id WHERE "group" = ?', array($dbgroup->id));
            $members = array();
            if ($dbmembers) {
                foreach ($dbmembers as $member) {
                    $members []= array('id' => $member->userid, 'username' => $member->username, 'role' => $member->role);
                }
            }

            // form up the output
            $groups[]= array(
                        'id'             => $dbgroup->id,
                        'name'           => $dbgroup->name,
                        'shortname'      => $dbgroup->shortname,
                        'description'    => $dbgroup->description,
                        'institution'    => $dbgroup->institution,
                        'grouptype'      => $dbgroup->grouptype,
                        'category'       => ($dbgroup->category ? get_field('group_category', 'title', 'id', $dbgroup->category) : ''),
                        'editroles'      => $dbgroup->editroles,
                        'open'           => ($dbgroup->jointype == 'open' ? 1 : 0),
                        'controlled'     => ($dbgroup->jointype == 'controlled' ? 1 : 0),
                        'request'        => $dbgroup->request,
                        'submitpages'    => (isset($dbgroup->submittableto) ? $dbgroup->submittableto : 0),
                        'public'         => $dbgroup->public,
                        'viewnotify'     => $dbgroup->viewnotify,
                        'feedbacknotify' => $dbgroup->feedbacknotify,
                        'usersautoadded' => $dbgroup->usersautoadded,
                        'hidden'         => $dbgroup->hidden,
                        'hidemembers'    => $dbgroup->hidemembers,
                        'hidemembersfrommembers' => $dbgroup->hidemembersfrommembers,
                        'groupparticipationreports' => $dbgroup->groupparticipationreports,
                        'members'        => $members,
            );
        }
        return $groups;
    }

    /**
     * parameter definition for output of get_groups_by_id method
     *
     * Returns description of method result value
     * @return external_multiple_structure
     */
    public static function get_groups_by_id_returns() {
        $group_types = group_get_grouptypes();
        $group_edit_roles = array_keys(group_get_editroles_options());
        $notifyroles = array(get_string('none', 'admin')) + group_get_editroles_options(true);
        foreach ($notifyroles as $key => $role) {
            $group_notify_roles[] = $key . ' = ' . $role;
        }
        return new external_multiple_structure(
                    new external_single_structure(
                        array(
                                'id'              => new external_value(PARAM_NUMBER, 'ID of the group'),
                                'name'            => new external_value(PARAM_RAW, 'Group name'),
                                'shortname'       => new external_value(PARAM_RAW, 'Group shortname for API only controlled groups'),
                                'description'     => new external_value(PARAM_RAW, 'Group description'),
                                'institution'     => new external_value(PARAM_TEXT, 'Mahara institution - required for API controlled groups'),
                                'grouptype'       => new external_value(PARAM_ALPHANUMEXT, 'Group type: ' . implode(',', $group_types)),
                                'category'        => new external_value(PARAM_TEXT, 'Group category - the title of an existing group category'),
                                'editroles'       => new external_value(PARAM_ALPHANUMEXT, 'Edit roles allowed: ' . implode(', ', $group_edit_roles)),
                                'open'            => new external_value(PARAM_BOOL, 'Open - Users can join the group without approval from group administrators'),
                                'controlled'      => new external_value(PARAM_BOOL, 'Controlled - Group administrators can add users to the group without their consent, and members cannot choose to leave'),
                                'request'         => new external_value(PARAM_BOOL, 'Request - Users can send membership requests to group administrators'),
                                'submitpages'     => new external_value(PARAM_BOOL, 'Submit pages - Members can submit pages to the group'),
                                'public'          => new external_value(PARAM_BOOL, 'Public group'),
                                'viewnotify'      => new external_value(PARAM_INT, 'Shared page notifications'),
                                'feedbacknotify'  => new external_value(PARAM_INT, 'Comment notifications'),
                                'usersautoadded'  => new external_value(PARAM_BOOL, 'Auto-adding users'),
                                'hidden'          => new external_value(PARAM_BOOL, 'Hide group'),
                                'hidemembers'     => new external_value(PARAM_INT, 'Hide membership'),
                                'hidemembersfrommembers' => new external_value(PARAM_INT, 'Hide membership'),
                                'groupparticipationreports' => new external_value(PARAM_BOOL, 'Participation report'),
                                'members'         => new external_multiple_structure(
                                                        new external_single_structure(
                                                            array(
                                                                    'id' => new external_value(PARAM_NUMBER, 'member user Id'),
                                                                    'username' => new external_value(PARAM_RAW, 'member username'),
                                                                    'role' => new external_value(PARAM_ALPHANUMEXT, 'member role: admin, ')
                                                                    ), 'Group membership')
                                                                ),
                                                            )
                                                        )
                                                    );
    }

    /**
     * parameter definition for input of get_groups method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_groups_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Get group information for all groups
     *
     * @return array An array of arrays describing groups
     */
    public static function get_groups() {
        return self::get_groups_by_id(array());
    }

    /**
     * parameter definition for output of get_groups method
     *
     * Returns description of method result value
     * @return external_multiple_structure
     */
    public static function get_groups_returns() {
        return self::get_groups_by_id_returns();
    }
}

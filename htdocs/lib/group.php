<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();


/**
 * Checks whether a user is allowed to leave a group.
 *
 * This checks things like if they're the owner and the group membership type
 *
 * @param mixed $group  DB record or ID of group to check
 * @param int   $userid (optional, will default to logged in user)
 */
function group_user_can_leave($group, $userid=null) {
    static $result;

    $userid = optional_userid($userid);

    if (is_numeric($group)) {
        if (!$group = get_record('group', 'id', $group, 'deleted', 0)) {
            return false;
        }
    }

    // Return cached value if we have it
    if (isset($result[$group->id][$userid])) {
        return $result[$group->id][$userid];
    }
    
    if ($group->jointype == 'controlled') {
        return ($result[$group->id][$userid] = false);
    }

    if (group_is_only_admin($group->id, $userid)) {
        return ($result[$group->id][$userid] = false);
    }

    return ($result[$group->id][$userid] = true);
}

/**
 * removes a user from a group
 * removed view access given by the user to the group
 *
 * @param int $group id of group
 * @param int $user id of user to remove
 */
function group_remove_user($group, $userid) {    
    if (!group_user_can_leave($group, $userid)) {
        throw new AccessDeniedException(get_string('usercantleavegroup', 'group'));
    }
    db_begin();
    delete_records('group_member', 'group', $group, 'member', $userid);
    delete_records_sql(
        'DELETE FROM {view_access_group}
        WHERE "group" = ?
        AND view IN (
            SELECT v.id
            FROM {view} v
            WHERE v.owner = ?
        )',
        array($group, $userid)
    );
    db_commit();

    require_once(get_config('docroot') . 'interaction/lib.php');
    $interactions = get_column('interaction_instance', 'id', 'group', $group);
    foreach ($interactions as $interaction) {
        interaction_instance_from_id($interaction)->interaction_remove_user($userid);
    }
}

/**
 * Establishes what role a user has in a given group.
 *
 * If the user is not in the group, this returns false.
 *
 * @param mixed $groupid  ID of the group to check
 * @param mixed $userid   ID of the user to check. Defaults to the logged in 
 *                        user.
 * @return mixed          The role the user has in the group, or false if they 
 *                        have no role in the group
 */
function group_user_access($groupid, $userid=null) {
    // TODO: caching

    $groupid = (int)$groupid;

    if ($groupid == 0) {
        throw new InvalidArgumentException("group_user_access: group argument should be an integer");
    }

    if (is_null($userid)) {
        global $USER;
        $userid = (int)$USER->get('id');
    }
    else {
        $userid = (int)$userid;
    }

    if ($userid == 0) {
        throw new InvalidArgumentException("group_user_access: user argument should be an integer");
    }

    return get_field('group_member', 'role', 'group', $groupid, 'member', $userid);
}

/**
 * Returns whether the given user is the only administrator in the given group.
 *
 * If the user isn't in the group, or they're not an admin, or there is another admin, false 
 * is returned.
 *
 * @param int $groupid The ID of the group to check
 * @param int $userid  The ID of the user to check
 * @returns boolean
 */
function group_is_only_admin($groupid, $userid=null) {
    // TODO: caching

    $groupid = (int)$groupid;

    if ($groupid == 0) {
        throw new InvalidArgumentException("group_is_only_admin: group argument should be an integer");
    }

    if (is_null($userid)) {
        global $USER;
        $userid = (int)$USER->get('id');
    }
    else {
        $userid = (int)$userid;
    }

    if ($userid == 0) {
        throw new InvalidArgumentException("group_is_only_admin: user argument should be an integer");
    }

    return group_user_access($groupid, $userid) == 'admin'
        && count_records('group_member', 'group', $groupid, 'role', 'admin') == 1;
}

/**
 * Returns whether the given user is allowed to change their role to the 
 * requested role in the given group.
 *
 * This function is checking whether _role changes_ are allowed, not if a user 
 * is allowed to be added to a group.
 *
 * @param int $groupid The ID of the group to check
 * @param int $userid  The ID of the user to check
 * @param string $role The role the user wishes to switch to
 * @returns boolean
 */
function group_can_change_role($groupid, $userid, $role) {
    $groupid = (int)$groupid;

    if ($groupid == 0) {
        throw new InvalidArgumentException("group_can_change_role: group argument should be an integer");
    }

    if ($userid == 0) {
        throw new InvalidArgumentException("group_can_change_role: user argument should be an integer");
    }

    if (!group_user_access($groupid, $userid)) {
        return false;
    }

    // Sole remaining admins can never change their role
    if (group_is_only_admin($groupid, $userid)) {
        return false;
    }

    // Maybe one day more checks will be needed - they go here

    return true;
}

/**
 * Changes a user role in a group, if this is allowed.
 *
 * @param int $group The ID of the group
 * @param int $user  The ID of the user whose role needs changing
 * @param string $role The role the user wishes to switch to
 * @throws AccessDeniedException If the specified role change is not allowed. 
 *                               Check with group_can_change_role first if you 
 *                               need to.
 */
function group_change_role($group, $user, $role) {
    if (!group_can_change_role($group, $user, $role)) {
        throw new AccessDeniedException(get_string('usercannotchangetothisrole', 'group'));
    }

    set_field('group_member', 'role', $role, 'group', $group, 'member', $user);
}

function group_user_can_edit_views($groupid, $userid=null) {
    $groupid = (int)$groupid;

    if ($groupid == 0) {
        throw new InvalidArgumentException("group_user_access: group argument appears to be invalid: $groupid");
    }

    if (is_null($userid)) {
        global $USER;
        $userid = (int)$USER->get('id');
    }
    else {
        $userid = (int)$userid;
    }

    if ($userid == 0) {
        throw new InvalidArgumentException("group_user_access: user argument appears to be invalid: $userid");
    }

    return get_field_sql('
        SELECT
            r.edit_views
        FROM
            {group_member} m
            INNER JOIN {group} g ON (m.group = g.id AND g.deleted = 0)
            INNER JOIN {grouptype_roles} r ON (g.grouptype = r.grouptype AND m.role = r.role)
        WHERE
            m.group = ?
            AND m.member = ?', array($groupid, $userid));
}

/**
 * function to add a member to a group
 * doesn't do any jointype checking, that should be handled by the caller
 *
 * @param int $groupid
 * @param int $userid
 * @param string $role
 */
function group_add_user($groupid, $userid, $role=null) {
    $cm = new StdClass;
    $cm->member = $userid;
    $cm->group = $groupid;
    $cm->ctime =  db_format_timestamp(time());
    if (!$role) {
        $role = get_field_sql('SELECT gt.defaultrole FROM {grouptype} gt, {group} g WHERE g.id = ? AND g.grouptype = gt.name', array($groupid));
    }
    $cm->role = $role;
    insert_record('group_member', $cm);
    delete_records('group_member_request', 'group', $groupid, 'member', $userid);
    $user = optional_userobj($userid);
}

function group_has_members($groupid) {
    $sql = 'SELECT (
        (SELECT COUNT(*) FROM {group_member} WHERE "group" = ?)
        +
        (SELECT COUNT(*) FROM {group_member_request} WHERE "group" = ?)
    )';
    return count_records_sql($sql, array($groupid, $groupid));
}

function delete_group($groupid) {
    update_record('group', array('deleted' => 1), array('id' => $groupid));
}

/**
 * Returns a list of user IDs who are admins for a group
 *
 * @param int
 * @return array
 */
function group_get_admin_ids($group) {
    return (array)get_column_sql("SELECT member
        FROM {group_member}
        WHERE \"group\" = ?
        AND role = 'admin'", $group);
}


function group_get_join_form($name, $groupid) {
    return pieform(array(
        'name' => $name,
        'successcallback' => 'joingroup_submit',
        'elements' => array(
            'join' => array(
                'type' => 'submit',
                'value' => get_string('joingroup', 'group')
            ),
            'group' => array(
                'type' => 'hidden',
                'value' => $groupid
            )
        )
    ));
}

function group_get_accept_form($name, $groupid, $returnto) {
    return pieform(array(
       'name'     => $name,
       'renderer' => 'oneline',
       'successcallback' => 'group_invite_submit',
       'elements' => array(
            'accept' => array(
                'type'  => 'submit',
                'value' => get_string('acceptinvitegroup', 'group')
            ),
            'decline' => array(
                'type'  => 'submit',
                'value' => get_string('declineinvitegroup', 'group')
            ),
            'group' => array(
                'type' => 'hidden',
                'value' => $groupid
            ),
            'returnto' => array(
                'type' => 'hidden',
                'value' => $returnto
            )
        )
    ));
}

function group_get_adduser_form($userid, $groupid) {
    return pieform(array(
        'name'                => 'adduser', // TODO: is this safe? how many of these forms are shown on a page?
        'successcallback'     => 'group_adduser_submit',
        'renderer'            => 'div',
        'elements'            => array(
            'group' => array(
                'type'    => 'hidden',
                'value' => $groupid,
            ),
            'member' => array(
                'type'  => 'hidden',
                'value' => $userid,
            ),
            'submit' => array(
                'type'  => 'submit',
                'value' => get_string('add'),
            ),
        ),
    ));
}

function group_get_removeuser_form($userid, $groupid) {
    return pieform(array(
        'name'                => 'removeuser' . $userid,
        'validatecallback'    => 'group_removeuser_validate',
        'successcallback'     => 'group_removeuser_submit',
        'renderer'            => 'oneline',
        'elements'            => array(
            'group' => array(
                'type'    => 'hidden',
                'value' => $groupid,
            ),
            'member' => array(
                'type'  => 'hidden',
                'value' => $userid,
            ),
            'submit' => array(
                'type'  => 'submit',
                'value' => get_string('removefromgroup', 'group'),
            ),
        ),
    ));
}

/**
 * Sets up groups for display in mygroups.php and find.php
 *
 * @param array $groups    Initial group data, including the current user's 
 *                         membership type in each group. See mygroups.php for
 *                         the query to build this information.
 * @param string $returnto Where forms generated for display should be told to return to
 */
function group_prepare_usergroups_for_display($groups, $returnto='mygroups') {
    if (!$groups) {
        return;
    }

    // Retrieve a list of all the group admins, for placing in each $group object
    $groupadmins = array();
    $groupids = array_map(create_function('$a', 'return $a->id;'), $groups);
    if ($groupids) {
        $groupadmins = (array)get_records_sql_array('SELECT "group", member
            FROM {group_member}
            WHERE "group" IN (' . implode(',', db_array_to_ph($groupids)) . ")
            AND role = 'admin'", $groupids);
    }

    $i = 0;
    foreach ($groups as $group) {
        $group->admins = array();
        foreach ($groupadmins as $admin) {
            if ($admin->group == $group->id) {
                $group->admins[] = $admin->member;
            }
        }
        $group->description = str_shorten($group->description, 100, true);
        if ($group->membershiptype == 'member') {
            $group->canleave = group_user_can_leave($group->id);
        }
        else if ($group->jointype == 'open') {
            $group->groupjoin = group_get_join_form('joingroup' . $i++, $group->id);
        }
        else if ($group->membershiptype == 'invite') {
            $group->invite = group_get_accept_form('invite' . $i++, $group->id, $returnto);
        }
        else if ($group->membershiptype == 'admin' && $group->requests > 1) {
            $group->requests = array($group->requests);
        }
    }
}

function joingroup_submit(Pieform $form, $values) {
    global $SESSION, $USER;
    group_add_user($values['group'], $USER->get('id'));
    $SESSION->add_ok_msg(get_string('joinedgroup', 'group'));
    redirect('/group/view.php?id=' . $values['group']);
}

function group_invite_submit(Pieform $form, $values) {
    global $SESSION, $USER;
    $inviterecord = get_record('group_member_invite', 'member', $USER->get('id'), 'group', $values['group']);
    if ($inviterecord) {
        delete_records('group_member_invite', 'group', $values['group'], 'member', $USER->get('id'));
        if (isset($values['accept'])) {
            group_add_user($values['group'], $USER->get('id'), $inviterecord->role);
            $SESSION->add_ok_msg(get_string('groupinviteaccepted', 'group'));
            redirect('/group/view.php?id=' . $values['group']);
        }
        else {
            $SESSION->add_ok_msg(get_string('groupinvitedeclined', 'group'));
            redirect($values['returnto'] == 'find' ? '/group/find.php' : '/group/mygroups.php');
        }
    }
}

function group_removeuser_validate(Pieform $form, $values) {
    global $user, $group, $SESSION;
    if (!group_user_can_leave($values['group'], $values['member'])) {
        $form->set_error('submit', get_string('usercantleavegroup', 'group'));
    }
}

function group_adduser_submit(Pieform $form, $values) {
    global $SESSION;
    $group = (int)$values['group'];
    if (group_user_access($group) != 'admin') {
        $SESSION->add_error_msg(get_string('accessdenied', 'error'));
        redirect('/group/members.php?id=' . $group . '&membershiptype=request');
    }
    group_add_user($group, $values['member']);
    $SESSION->add_ok_msg(get_string('useradded', 'group'));
    if (count_records('group_member_request', 'group', $group)) {
        redirect('/group/members.php?id=' . $group . '&membershiptype=request');
    }
    redirect('/group/members.php?id=' . $group);
}

function group_removeuser_submit(Pieform $form, $values) {
    global $SESSION;
    $group = (int)$values['group'];
    if (group_user_access($group) != 'admin') {
        $SESSION->add_error_msg(get_string('accessdenied', 'error'));
        redirect('/group/members.php?id=' . $group);
    }
    group_remove_user($group, $values['member']);
    $SESSION->add_ok_msg(get_string('userremoved', 'group'));
    redirect('/group/members.php?id=' . $group);
}

function group_get_role_info($groupid) {
    $roles = get_records_sql_assoc('SELECT role, edit_views, see_submitted_views, gr.grouptype FROM {grouptype_roles} gr
        INNER JOIN {group} g ON g.grouptype = gr.grouptype
        WHERE g.id = ?', array($groupid));
    foreach ($roles as $role) {
        $role->display = get_string($role->role, 'grouptype.'.$role->grouptype);
        $role->name = $role->role;
    }
    return $roles;
}

function group_get_membersearch_data($group, $query, $offset, $limit, $membershiptype) {
    $results = get_group_user_search_results($group, $query, $offset, $limit, $membershiptype);

    $params = array();
    if (!empty($query)) {
        $params[] = 'query=' . $query;
    }
    $params[] = 'limit=' . $limit;
    if (!empty($membershiptype)) {
        $params[] = 'membershiptype=' . $membershiptype;
    }
    $searchurl = get_config('wwwroot') . 'group/members.php?id=' . $group . '&amp;' . join('&amp;', $params);

    $smarty = smarty_core();

    foreach ($results['data'] as &$r) {
        if (group_user_can_leave($group, $r['id'])) {
            $r['removeform'] = group_get_removeuser_form($r['id'], $group);
        }
        // NOTE: this is a quick approximation. We should really check whether, 
        // for each role in the group, that the user can change to it (using 
        // group_can_change_role).  This only controls whether the 'change 
        // role' link appears though, so it doesn't matter too much. If the 
        // user clicks on this link, changerole.php does the full check and 
        // sends them back here saying that the user has no roles they can 
        // change to anyway.
        $r['canchangerole'] = !group_is_only_admin($group, $r['id']);
    }

    if (!empty($membershiptype)) {
        if ($membershiptype == 'request') {
            foreach ($results['data'] as &$r) {
                $r['addform'] = group_get_adduser_form($r['id'], $group);
            }
        }
        $smarty->assign('membershiptype', $membershiptype);
    }

    $results['cdata'] = array_chunk($results['data'], 2);
    $results['roles'] = group_get_role_info($group);
    $smarty->assign_by_ref('results', $results);
    $smarty->assign('searchurl', $searchurl);
    $smarty->assign('pagebaseurl', $searchurl);
    $smarty->assign('caneditroles', group_user_access($group) == 'admin');
    $smarty->assign('group', $group);
    $html = $smarty->fetch('group/membersearchresults.tpl');

    $pagination = build_pagination(array(
        'id' => 'member_pagination',
        'class' => 'center',
        'url' => $searchurl,
        'count' => $results['count'],
        'limit' => $limit,
        'offset' => $offset,
        'datatable' => 'membersearchresults',
        'jsonscript' => 'group/membersearchresults.php',
        'firsttext' => '',
        'previoustext' => '',
        'nexttext' => '',
        'lasttext' => '',
        'numbersincludefirstlast' => false,
        'resultcounttextsingular' => get_string('member', 'group'),
        'resultcounttextplural' => get_string('members', 'group'),
    ));

    return array($html, $pagination, $results['count'], $offset, $membershiptype);
}


/**
 * Returns a list of available grouptypes
 */
function group_get_grouptypes() {
    static $grouptypes = null;

    if (is_null($grouptypes)) {
        $grouptypes = get_column('grouptype', 'name');
    }

    return $grouptypes;
}


function can_assess_submitted_views($userid, $groupid) {
    return get_field_sql('
        SELECT
            r.see_submitted_views
        FROM
            {group_member} m 
            INNER JOIN {group} g ON (m.group = g.id AND g.deleted = 0)
            INNER JOIN {grouptype_roles} r ON (g.grouptype = r.grouptype AND r.role = m.role)
        WHERE
            m.member = ?
            AND m.group = ?', array($userid, $groupid));
}


/**
 * Returns a list of grouptype & jointype options to be used in create
 * group/edit group drop-downs.
 * 
 * If there is more than one group type with the same join type,
 * prefix the join types with the group type for display.
 */
function get_grouptype_options() {
    $groupoptions = array();
    $jointypecount = array('open' => 0, 'invite' => 0, 'request' => 0, 'controlled' => 0);
    foreach (group_get_grouptypes() as $grouptype) {
        safe_require('grouptype', $grouptype);
        if (call_static_method('GroupType' . $grouptype, 'can_be_created_by_user')) {
            $grouptypename = get_string('name', 'grouptype.' . $grouptype);
            foreach (call_static_method('GroupType' . $grouptype, 'allowed_join_types') as $jointype) {
                $jointypecount[$jointype]++;
                $groupoptions['jointype']["$grouptype.$jointype"] = get_string('membershiptype.'.$jointype, 'group');
                $groupoptions['grouptype']["$grouptype.$jointype"] = $grouptypename . ': ' . get_string('membershiptype.'.$jointype, 'group');
            }
        }
    }
    $duplicates = array_reduce($jointypecount, create_function('$a, $b', 'return $a || $b > 1;'));
    if ($duplicates) {
        return $groupoptions['grouptype'];
    }
    return $groupoptions['jointype'];
}


function group_get_menu_tabs($group) {
    $menu = array(
        'info' => array(
            'url' => 'group/view.php?id='.$group->id,
            'title' => get_string('About', 'group'),
        ),
        'members' => array(
            'url' => 'group/members.php?id='.$group->id,
            'title' => get_string('Members', 'group'),
        ),
        'views' => array(
            'url' => 'view/groupviews.php?group='.$group->id,
            'title' => get_string('Views', 'group'),
        ),
    );
    if (!group_user_access($group->id)) {
        return $menu;
    }
    safe_require('grouptype', $group->grouptype);
    $artefactplugins = call_static_method('GroupType' . $group->grouptype, 'get_group_artefact_plugins');
    if ($plugins = get_records_array('artefact_installed', 'active', 1)) {
        foreach ($plugins as &$plugin) {
            if (!in_array($plugin->name, $artefactplugins)) {
                continue;
            }
            safe_require('artefact', $plugin->name);
            $plugin_menu = call_static_method(generate_class_name('artefact',$plugin->name), 'group_tabs', $group->id);
            $menu = array_merge($menu, $plugin_menu);
        }
    }
    return $menu;
}

?>

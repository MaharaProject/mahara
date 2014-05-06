<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Nathan Lewis <nathan.lewis@totaralms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();


/**
 * Get an array of sql snippets that restrict artefact records to only those that the viewer has access.
 * NOTE: Every snippet should link to the 'artefact' table.
 *
 * @param int $view id of the user that will be viewing the records
 * @param bool $areknownfriends true if we know beforehand that the viewer and owner of the base object are friends
 * @return array of access condition sql snipet and param list pairs
 */
function get_artefact_access_conditions($viewer, $areknownfriends = false) {
    $artefactAccessConditions = array();

    // Available to public.
    $artefactAccessConditions['public'] = array(
        'sql' => "JOIN {artefact_access} artefact_access
                    ON artefact.id = artefact_access.artefact
                   AND artefact_access.accesstype = 'public'",
        'params' => array());

    // Available to logged in users.
    $artefactAccessConditions['loggedin'] = array(
        'sql' => "JOIN {artefact_access} artefact_access
                    ON artefact.id = artefact_access.artefact
                   AND artefact_access.accesstype = 'loggedin'",
        'params' => array());

    if ($areknownfriends) {
        // Available to friends and we know that the owner and viewer are friends.
        $artefactAccessConditions['friends'] = array(
            'sql' => "JOIN {artefact_access} artefact_access
                        ON artefact.id = artefact_access.artefact
                       AND artefact_access.accesstype = 'friends'",
            'params' => array());

    }
    else {
        // Available to friends usr1->usr2.
        $artefactAccessConditions['friend12'] = array(
            'sql' => "JOIN {artefact_access} artefact_access
                        ON artefact.id = artefact_access.artefact
                       AND artefact_access.accesstype = 'friends'
                      JOIN {usr_friend} artefactfriendaccess
                        ON artefact.owner = artefactfriendaccess.usr1
                       AND artefactfriendaccess.usr2 = ?",
            'params' => array($viewer));

        // Available to friends usr2->usr1.
        $artefactAccessConditions['friend21'] = array(
            'sql' => "JOIN {artefact_access} artefact_access
                        ON artefact.id = artefact_access.artefact
                       AND artefact_access.accesstype = 'friends'
                      JOIN {usr_friend} artefactfriendaccess
                        ON artefact.owner = artefactfriendaccess.usr2
                       AND artefactfriendaccess.usr1 = ?",
            'params' => array($viewer));
    }

    // Available to specific user.
    $artefactAccessConditions['usr'] = array(
        'sql' => "JOIN {artefact_access} artefact_access
                    ON artefact.id = artefact_access.artefact
                   AND (artefact_access.usr = ?)",
        'params' => array($viewer));

    // Available to a group the user is in.
    $artefactAccessConditions['group'] = array(
        'sql' => "JOIN {artefact_access} artefact_access
                    ON artefact.id = artefact_access.artefact
                  JOIN {group_member} group_member
                    ON artefact_access.group = group_member.group
                   AND group_member.member = ?",
        'params' => array($viewer));

/* Disabled until mygroups sharing is implemented.
    // Available to my groups.
    $artefactAccessConditions['mygroups'] = array(
        'sql' => "JOIN {artefact_access} artefact_access
                    ON artefact.id = artefact_access.artefact
                   AND artefact_access.accesstype = 'groups'
                  JOIN {group_member} group_member_owner
                    ON artefact.owner = group_member_owner.member
                  JOIN {group_member} group_member_viewer
                    ON group_member_owner.group = group_member_viewer.group
                   AND group_member_viewer.member = ?",
        'params' => array($viewer));
*/

    // Available to an institution the user is in.
    $artefactAccessConditions['institution'] = array(
        'sql' => "JOIN {artefact_access} artefact_access
                    ON artefact.id = artefact_access.artefact
                  JOIN {usr_institution} usr_institution
                    ON artefact_access.institution = usr_institution.institution
                   AND usr_institution.usr = ?",
        'params' => array($viewer));

    return $artefactAccessConditions;
}


/**
 * Get an array of sql snippets that restrict view records to only those that the viewer has access.
 * NOTE: Every snippet should link to the 'view' table.
 *
 * @param int $view id of the user that will be viewing the records
 * @param bool $areknownfriends true if we know beforehand that the viewer and owner of the base object are friends
 * @return array of access condition sql snipet and param list pairs
 */
function get_view_access_conditions($viewer, $areknownfriends = false) {
    $viewAccessConditions = array();

    // Available to public.
    $viewAccessConditions['public'] = array(
        'sql' => "JOIN {view_access} view_access
                    ON view.id = view_access.view
                   AND view_access.accesstype = 'public'",
        'params' => array());

    // Available to logged in users.
    $viewAccessConditions['loggedin'] = array(
        'sql' => "JOIN {view_access} view_access
                    ON view.id = view_access.view
                   AND view_access.accesstype = 'loggedin'",
        'params' => array());

    if ($areknownfriends) {
        // Available to friends and we know that the owner and viewer are friends.
        $viewAccessConditions['friends'] = array(
            'sql' => "JOIN {view_access} view_access
                        ON view.id = view_access.view
                       AND view_access.accesstype = 'friends'",
            'params' => array());

    }
    else {
        // Available to friends usr1->usr2.
        $viewAccessConditions['friend12'] = array(
            'sql' => "JOIN {view_access} view_access
                        ON view.id = view_access.view
                       AND view_access.accesstype = 'friends'
                      JOIN {usr_friend} viewfriendaccess
                        ON view.owner = viewfriendaccess.usr1
                       AND viewfriendaccess.usr2 = ?",
            'params' => array($viewer));

        // Available to friends usr2->usr1.
        $viewAccessConditions['friend21'] = array(
            'sql' => "JOIN {view_access} view_access
                        ON view.id = view_access.view
                       AND view_access.accesstype = 'friends'
                      JOIN {usr_friend} viewfriendaccess
                        ON view.owner = viewfriendaccess.usr2
                       AND viewfriendaccess.usr1 = ?",
            'params' => array($viewer));
    }

    // Available to specific user.
    $viewAccessConditions['usr'] = array(
        'sql' => "JOIN {view_access} view_access
                    ON view.id = view_access.view
                   AND view_access.usr = ?",
        'params' => array($viewer));

    // Available to a group the user is in.
    $viewAccessConditions['group'] = array(
        'sql' => "JOIN {view_access} view_access
                    ON view.id = view_access.view
                  JOIN {group_member} group_member
                    ON view_access.group = group_member.group
                   AND group_member.member = ?",
        'params' => array($viewer));

/* Disabled until mygroups sharing is implemented.
    // Available to my groups.
    $viewAccessConditions['mygroups'] = array(
        'sql' => "JOIN {view_access} view_access
                    ON view.id = view_access.view
                   AND view_access.accesstype = 'groups'
                  JOIN {group_member} group_member_owner
                    ON view.owner = group_member_owner.member
                  JOIN {group_member} group_member_viewer
                    ON group_member_owner.group = group_member_viewer.group
                   AND group_member_viewer.member = ?",
        'params' => array($viewer));
*/

    // Available to an institution the user is in.
    $viewAccessConditions['institution'] = array(
        'sql' => "JOIN {view_access} view_access
                    ON view.id = view_access.view
                  JOIN {usr_institution} usr_institution
                    ON view_access.institution = usr_institution.institution
                   AND usr_institution.usr = ?",
        'params' => array($viewer));

    return $viewAccessConditions;
}

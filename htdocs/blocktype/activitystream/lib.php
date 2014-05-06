<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-activitystream
 * @author     Nathan Lewis <nathan.lewis@totaralms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

require_once(get_config('libroot') . 'access.php');
require_once(get_config('libroot') . 'activity.php');

class PluginBlocktypeActivitystream extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.activitystream');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.activitystream');
    }

    public static function single_only() {
        return true;
    }

    public static function get_categories() {
        return array('general');
    }

    public static function render_instance(BlockInstance $instance, $editing = false) {
        global $USER;
        safe_require('interaction', 'forum'); // Used for date formatting.

        $viewer = $USER->get('id');
        $view = $instance->get_view();
        $type = $view->get('type');
        $owner = $view->get('owner');
        $group = $view->get('group');
        $institution = $view->get('institution');
        $paginationactivityid = false;

        if (empty($owner) && empty($group) && empty($institution)) { // Template view - don't render any content.
            return '';
        }
        else if ($group) {
            $streamtype = 'groupstream';
        }
        else if ($institution) {
            $streamtype = 'institutionstream';
        }
        else if ($type == 'dashboard') {
            $streamtype = 'homestream';
        }
        else if ($type == 'portfolio' || $type == 'profile') {
            if ($owner == $viewer) {
                $streamtype = 'myindividualstream';
            }
            else {
                $streamtype = 'otherindividualstream';
            }
        }

        $activities = self::get_activities($streamtype, $viewer, $owner, $group, $institution, $paginationactivityid);

        $formattedactivities = array();
        foreach ($activities as $activity) {
            $classname = get_activity_type_classname($activity->activitytype);
            $result = new stdClass();
            $result->body = $classname::get_activity_body($activity);
            $result->ctime = relative_date(get_string('strftimerecentfullrelative', 'interaction.forum'),
                                           get_string('strftimerecentfull'), $activity->subactivity[0]->ctime);
            $primaryuser = get_user($activity->subactivity[0]->usr);
            $result->primaryuserurl = profile_url($primaryuser);
            $result->primaryusername = display_name($primaryuser, null, true);
            $result->primaryuser = $primaryuser;
            $formattedactivities[] = $result;
        }

        $smarty = smarty_core();

        if (empty($activities)) {
            if ($streamtype == 'homestream') {
                $url = get_config('wwwroot') . 'account/activity/preferences/index.php';
                $noactivities = get_string('noactivitieshomestream', 'blocktype.activitystream', $url);
            }
            else {
                $noactivities = get_string('noactivities', 'blocktype.activitystream');
            }
            $smarty->assign('noactivities', $noactivities);
        }

        $smarty->assign('activities', $formattedactivities);

        return $smarty->fetch('blocktype:activitystream:activitystream.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function postinst($prevversion) {
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    public static function allowed_in_view(View $view) {
        $institution = $view->get('institution');
        if ($institution == 'mahara') {
            // Activity stream cannot go on a site page (unless someone implements 'sitestream').
            return false;
        }
        else if ($institution) {
            // TODO: Remove this in the event that the institution stream is implemented.
            return false;
        }
        else {
            return true;
        }
    }

    /**
     * Indicate that the configure button should appear for instances of activity stream blocks.
     *
     * This allows users to rename the block (or remove the name completely) and change the retractable block settings.
     */
    public static function instance_config_form($instance) {
        return array();
    }

    /**
     * This is a public static method which gets filtered and grouped activity data which can be used to
     * display activities in an activity stream.
     *
     * Detailed explanation of how it works:
     * The function constructs an SQL query to gather data for an activity stream, then returns the data needed
     * to output the stream. The SQL is written as a series of nested subqueries from inside to outside, each
     * with a specific purpose. The subqueries are:
     * 1. Get the specific activities that the viewer should see on this stream.
     *    This is determined based on the stream type (home, group, user), the specific instance and the viewer.
     *    This is built as a UNION between lots of smaller queries each of which checks a particular condition.
     *    The result is a set of activity ids which are used as a subquery in the next step.
     * 2. Reduce the list to remove activities that will be shown together as a single item, then truncate for
     *    pagination.
     *    The purpose of this step is to ensure we return the right number of activities while taking grouping into
     *    account. Grouping allows us to combine activities of the same type on the same item within a specific
     *    timeframe. The reason for this is so you see "There were 3 new comments" instead of "There was a new
     *    comment" three times.
     *    To remove the duplicates, the query is grouped by the following fields:
     *       objecttype, objectid, activitytype, activitysubtype, activitydate
     *    And then pagination is applied (offset and limit) to return only the results that are needed.
     * 3. Expand the selection again to recover the grouped items.
     *    Now pagination has been applied we need to recover all the records for the remaining activities. The
     *    exception is the user stream where we only want to report on the owner's activities.
     *    This is done using a self join on all the fields used in the grouping in step 2.
     *
     * @param string $streamtype
     * @param int $viewer user id of the person viewing this stream
     * @param int $owner id of the user who owns this stream (e.g. owner of the view)
     * @param int $group id of the group that owns this stream (e.g. group that owns the view)
     * @param int $institution id of the institution that owns this stream (e.g. institution that owns the view)
     * @param mixed $paginationid either return activities with activityid lower than the specified paginationid, or false
     * @return array of activity data objects which can be used to display activities in an activity stream:
     *         activity->activityset the activityid of the primary activity used for grouping (== subactivity[0]->id)
     *                 ->activitytype
     *                 ->activitysubtype (optional)
     *                 ->objecttype the type of object that the activity was performed on
     *                 ->objectid the id of the object that the activity was performed on
     *                 ->subactivity[]->id the activity id of this subactivity
     *                                ->usr the user who performed this subactivity
     *                                ->additionalid (optional)
     *                                ->ctime the time of this subactivity
     *                 ->subactivitycount
     */
    public static function get_activities($streamtype, $viewer, $owner, $group, $institution, $paginationid = false) {
        // Get the conditions specific to the activity stream - this decides what activities may be seen.
        $individualstreamuser = null;
        switch ($streamtype) {
            case 'myindividualstream':
                $subqueries = static::get_myindividualstream_subqueries($viewer);
                $individualstreamuser = $viewer;
                break;
            case 'otherindividualstream':
                $subqueries = static::get_otherindividualstream_subqueries($viewer, $owner);
                $individualstreamuser = $owner;
                break;
            case 'groupstream':
                $subqueries = static::get_groupstream_subqueries($group);
                break;
            case 'institutionstream':
                $subqueries = static::get_institutionstream_subqueries($institution);
                break;
            case 'homestream':
                $subqueries = static::get_homestream_subqueries($viewer);
                break;
        }

        // Concatenate all subqueries together with UNION ALL and put all params into a single array (in order).
        $conditionssql = "";
        $conditionsparams = array();
        foreach ($subqueries as $subquery) {
            if ($conditionssql != "") {
                // We use "UNION ALL" because it avoids unnecessary sorting, and duplicates will be removed later.
                $conditionssql .= " UNION ALL ";
            }
            $conditionssql .= $subquery['sql'];
            $conditionsparams = array_merge($conditionsparams, $subquery['params']);
        }

        // For the home stream, add additional conditions to restrict by notification settings.
        if ($streamtype == 'homestream') {
            list($conditionssql, $conditionsparams) =
                    static::get_homestream_additional_conditions($conditionssql, $conditionsparams, $viewer);
        }

        // Group similar activities and limit to just those activities that are to be shown on the page.
        list($reducedactivitiessql, $reducedactivitesparams) =
                static::get_reduced_activities_sql($conditionssql, $conditionsparams, $paginationid);

        // Join the trimmed results back onto the activity table to get all related activities.
        // In an individual's stream, only show related actions which were performed by the stream owner.
        list($finalactivitiessql, $finalactivitiesparams) =
                static::get_related_activities_sql($reducedactivitiessql, $reducedactivitesparams, $individualstreamuser);

        // Execute the query and get the raw records back.
        $unformattedactivities =
                static::get_unformatted_records($finalactivitiessql, $finalactivitiesparams);

        // Iterate over the activity records and group similar activities together.
        $groupedactivities =
                static::group_activities($unformattedactivities);

        return $groupedactivities;
    }

    /**
     * Get all the activities that can be shown in the viewer's individual stream.
     *
     * @param int $viewer user id of the person viewing the stream
     * @return an array of sql and parameter set pairs which can select all available activities
     */
    public static function get_myindividualstream_subqueries($viewer) {
        $subqueries = array();

        // All activities that the user performed. No need to check sharing.
        $subqueries['mine'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                       WHERE activity.usr = ?",
            'params' => array($viewer));

        return $subqueries;
    }

    /**
     * Get all the activities that can be shown in the owner's individual stream, for the given viewer.
     *
     * @param int $viewer user id of the person viewing the stream
     * @param int $owner user id of the owner of the stream
     * @return an array of sql and parameter set pairs which can select all available activities
     */
    public static function get_otherindividualstream_subqueries($viewer, $owner) {
        $subqueries = array();

        // Pre-calculate if the viewer and the owner are friends.
        $areknownfriends =
                count_records_sql("SELECT COUNT(*) FROM {usr_friend} WHERE (usr1 = ? AND usr2 = ?) OR (usr1 = ? AND usr2 = ?)",
                                  array($viewer, $owner, $owner, $viewer));

        $artefactAccessConditions = static::get_artefact_access_conditions($viewer, $areknownfriends);
        $viewAccessConditions = static::get_view_access_conditions($viewer, $areknownfriends);

        // All system activities where the viewer is a friend of the owner.
        if ($areknownfriends) {
            $subqueries['system'] = array(
                'sql' => "SELECT activity.id
                            FROM {activity} activity
                           WHERE activity.objecttype = " . ActivityType::OBJECTTYPE_SYSTEM . "
                             AND activity.usr = ?",
                'params' => array($owner));
        }

        // All institution activities that the owner performed where the viewer is a member.
        $subqueries['institution'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {institution} institution
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_INSTITUTION . "
                         AND activity.objectid = institution.id
                         AND activity.usr = ?
                        JOIN {usr_institution} usr_institution
                          ON usr_institution.institution = institution.name
                         AND usr_institution.usr = ?",
            'params' => array($owner, $viewer));

        // All group activities that the owner performed where the viewer is a member.
        $subqueries['group'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {group_member} group_member
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_GROUP . "
                         AND activity.objectid = group_member.group
                         AND activity.usr = ?
                         AND group_member.member = ?",
            'params' => array($owner, $viewer));

        // All ineraction activities that the owner performed where the viewer is a member of the parent group.
        $subqueries['interaction'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {interaction_instance} interaction_instance
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_INTERACTION . "
                         AND activity.objectid = interaction_instance.id
                         AND activity.usr = ?
                        JOIN {group_member} group_member
                          ON interaction_instance.group = group_member.group
                         AND group_member.member = ?",
            'params' => array($owner, $viewer));

        // All forum topic activities that the owner performed where the viewer is a member of the ancestor group.
        $subqueries['forumtopic'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {interaction_forum_topic} interaction_forum_topic
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_FORUMTOPIC . "
                         AND activity.objectid = interaction_forum_topic.id
                         AND activity.usr = ?
                        JOIN {interaction_instance} interaction_instance
                          ON interaction_forum_topic.forum = interaction_instance.id
                        JOIN {group_member} group_member
                          ON interaction_instance.group = group_member.group
                         AND group_member.member = ?",
            'params' => array($owner, $viewer));

        // Activities on artefacts that are performed by the owner and that the viewer can see.
        foreach ($artefactAccessConditions as $key => $artefactAccessCondition) {
            $subqueries['artefact_' . $key] = array(
                'sql' => "SELECT activity.id
                            FROM {activity} activity
                            JOIN {artefact} artefact
                              ON activity.objecttype = " . ActivityType::OBJECTTYPE_ARTEFACT . "
                             AND activity.objectid = artefact.id
                             AND activity.usr = ?
                            {$artefactAccessCondition['sql']}",
                'params' => array($owner));
            foreach($artefactAccessCondition['params'] as $accessconditionparam) {
                $subqueries['artefact_' . $key]['params'][] = $accessconditionparam;
            }
        }

        // Activities on views that are performed by the owner and that the viewer can see.
        foreach ($viewAccessConditions as $key => $viewAccessCondition) {
            $subqueries['view_' . $key] = array(
                'sql' => "SELECT activity.id
                            FROM {activity} activity
                            JOIN {view} view
                              ON activity.objecttype = " . ActivityType::OBJECTTYPE_VIEW . "
                             AND activity.objectid = view.id
                             AND activity.usr = ?
                            {$viewAccessCondition['sql']}",
                'params' => array($owner));
            foreach($viewAccessCondition['params'] as $accessconditionparam) {
                $subqueries['view_' . $key]['params'][] = $accessconditionparam;
            }
        }

        return $subqueries;
    }

    /**
     * Get all the activities that can be shown in the group stream.
     *
     * We are not checking sharing because we assume that all actions and objects belonging to a group
     * are visible to all members of the group.
     *
     * @param int $groupid
     * @return an array of sql and parameter set pairs which can select all available activities
     */
    public static function get_groupstream_subqueries($group) {
        $subqueries = array();

        // Activities performed by the group itself.
        $subqueries['group'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                       WHERE activity.objecttype = " . ActivityType::OBJECTTYPE_GROUP . "
                         AND activity.objectid = ?",
            'params' => array($group));

        // Activities that were performed on artefacts belonging to the group.
        $subqueries['artefact'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {artefact} artefact
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_ARTEFACT . "
                         AND activity.objectid = artefact.id
                         AND artefact.group = ?",
            'params' => array($group));

        // Activities that were performed on views belonging to the group.
        $subqueries['view'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {view} view
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_VIEW . "
                         AND activity.objectid = view.id
                         AND view.group = ?",
            'params' => array($group));

        // Activities that were performed on interactions belonging to the group.
        $subqueries['interaction'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {interaction_instance} interaction_instance
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_INTERACTION . "
                         AND activity.objectid = interaction_instance.id
                         AND interaction_instance.group = ?",
            'params' => array($group));

        // Activities that were performed on forum topics belonging to the group.
        $subqueries['forumtopic'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {interaction_forum_topic} interaction_forum_topic
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_FORUMTOPIC . "
                         AND activity.objectid = interaction_forum_topic.id
                        JOIN {interaction_instance} interaction_instance
                          ON interaction_forum_topic.forum = interaction_instance.id
                         AND interaction_instance.group = ?",
            'params' => array($group));

        return $subqueries;
    }

    /**
     * Get all the activities that can be shown in the institution stream.
     *
     * @param int $institution
     * @return an array of sql and param set pairs which can select all available activities
     */
    public static function get_institutionstream_subqueries($institution) {
        // TODO: Write these subqueries, if/when some institution activities have been created.
        return array('null' => array('sql' => 'SELECT 0 AS id', 'params' => array()));
    }

    /**
     * Get all the activities that can be shown in a user's home stream (on their dashboard).
     *
     * This gets all activities, even if the user's notification settings exclude them. Notification
     * settings and watchlist filters are applied at a later stage (get_homestream_additional_conditions).
     *
     * @param int $viewer user id of the person viewing the stream
     * @return an array of sql and param set pairs which can select all available activities
     */
    public static function get_homestream_subqueries($viewer) {
        $subqueries = array();

        $artefactAccessConditions = static::get_artefact_access_conditions($viewer);
        $viewAccessConditions = static::get_view_access_conditions($viewer);

        // Activities I have performed.
        $subqueries['mine'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                       WHERE activity.usr = ?",
            'params' => array($viewer));

        // System activities that my friends performed (friends usr1->usr2).
        $subqueries['friend12'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {usr_friend} usr_friend
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_SYSTEM . "
                         AND activity.usr = usr_friend.usr1
                         AND usr_friend.usr2 = ?",
            'params' => array($viewer));

        // System activities that my friends performed (friends usr2->usr1).
        $subqueries['friend21'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {usr_friend} usr_friend
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_SYSTEM . "
                         AND activity.usr = usr_friend.usr2
                         AND usr_friend.usr1 = ?",
            'params' => array($viewer));

        // Activities on artefacts that I own.
        $subqueries['my_artefact'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {artefact} artefact
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_ARTEFACT . "
                         AND activity.objectid = artefact.id
                         AND artefact.owner = ?",
            'params' => array($viewer));

        // Activities on views that I own.
        $subqueries['my_view'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {view} view
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_VIEW . "
                         AND activity.objectid = view.id
                         AND view.owner = ?",
            'params' => array($viewer));

        // Activities that belong to my institutions.
        $subqueries['institution'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {institution} institution
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_INSTITUTION . "
                         AND activity.objectid = institution.id
                        JOIN {usr_institution} usr_institution
                          ON institution.name = usr_institution.institution
                         AND usr_institution.usr = ?",
            'params' => array($viewer));

        // Activities on artefacts that belong to my institutions.
        $subqueries['institution_artefact'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {artefact} artefact
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_ARTEFACT . "
                         AND activity.objectid = artefact.id
                        JOIN {usr_institution} usr_institution
                          ON usr_institution.institution = artefact.institution
                         AND usr_institution.usr = ?",
            'params' => array($viewer));

        // Activities on views that belong to my institutions.
        $subqueries['institution_view'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {view} view
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_VIEW . "
                         AND activity.objectid = view.id
                        JOIN {usr_institution} usr_institution
                          ON usr_institution.institution = view.institution
                         AND usr_institution.usr = ?",
            'params' => array($viewer));

        // Activities that belong to my groups.
        $subqueries['group'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {group_member} group_member
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_GROUP . "
                         AND activity.objectid = group_member.group
                         AND group_member.member = ?",
            'params' => array($viewer));

        // Activities on artefacts that belong to my groups.
        $subqueries['group_artefact'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {artefact} artefact
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_ARTEFACT . "
                         AND activity.objectid = artefact.id
                        JOIN {group_member} group_member
                          ON group_member.group = artefact.group
                         AND group_member.member = ?",
            'params' => array($viewer));

        // Activities on views that belong to my groups.
        $subqueries['group_view'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {view} view
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_VIEW . "
                         AND activity.objectid = view.id
                        JOIN {group_member} group_member
                          ON group_member.group = view.group
                         AND group_member.member = ?",
            'params' => array($viewer));

        // Activities on interactions that belong to my groups.
        $subqueries['group_interaction'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {interaction_instance} interaction_instance
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_INTERACTION . "
                         AND activity.objectid = interaction_instance.id
                        JOIN {group_member} group_member
                          ON group_member.group = interaction_instance.group
                         AND group_member.member = ?",
            'params' => array($viewer));

        // Activities on forum topics that belong to my groups.
        $subqueries['group_forumtopic'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {interaction_forum_topic} interaction_forum_topic
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_FORUMTOPIC . "
                         AND activity.objectid = interaction_forum_topic.id
                        JOIN {interaction_instance} interaction_instance
                          ON interaction_forum_topic.forum = interaction_instance.id
                        JOIN {group_member} group_member
                          ON interaction_instance.group = group_member.group
                         AND group_member.member = ?",
            'params' => array($viewer));

        // Activities on views that I am watching (users can only watch items that they have permission to access).
        $subqueries['watched_view'] = array(
            'sql' => "SELECT activity.id
                        FROM {activity} activity
                        JOIN {usr_watchlist_view} usr_watchlist_view
                          ON activity.objecttype = " . ActivityType::OBJECTTYPE_VIEW . "
                         AND activity.objectid = usr_watchlist_view.view
                         AND usr_watchlist_view.usr = ?",
            'params' => array($viewer));

        // Activities on artefacts that are owned by my connections and that I can see.
        foreach ($artefactAccessConditions as $key => $artefactAccessCondition) {
            // Friends usr1->usr2.
            $subqueries['friend12_artefact_' . $key] = array(
                'sql' => "SELECT activity.id
                            FROM {activity} activity
                            JOIN {artefact} artefact
                              ON activity.objecttype = " . ActivityType::OBJECTTYPE_ARTEFACT . "
                             AND activity.objectid = artefact.id
                            JOIN {usr_friend} artefactsoffriends
                              ON artefact.owner = artefactsoffriends.usr1
                             AND artefactsoffriends.usr2 = ?
                            {$artefactAccessCondition['sql']}",
                'params' => array($viewer));
            foreach($artefactAccessCondition['params'] as $accessconditionparam) {
                $subqueries['friend12_artefact_' . $key]['params'][] = $accessconditionparam;
            }
            // Friends usr2->usr1.
            $subqueries['friend21_artefact_' . $key] = array(
                'sql' => "SELECT activity.id
                            FROM {activity} activity
                            JOIN {artefact} artefact
                              ON activity.objecttype = " . ActivityType::OBJECTTYPE_ARTEFACT . "
                             AND activity.objectid = artefact.id
                            JOIN {usr_friend} artefactsoffriends
                              ON artefact.owner = artefactsoffriends.usr2
                             AND artefactsoffriends.usr1 = ?
                            {$artefactAccessCondition['sql']}",
                'params' => array($viewer));
            foreach($artefactAccessCondition['params'] as $accessconditionparam) {
                $subqueries['friend21_artefact_' . $key]['params'][] = $accessconditionparam;
            }
        }

        // Activities on views that are owned by my connections and that I can see.
        foreach ($viewAccessConditions as $key => $viewAccessCondition) {
            // Friends usr1->usr2.
            $subqueries['friend12_view_' . $key] = array(
                'sql' => "SELECT activity.id
                            FROM {activity} activity
                            JOIN {view} view
                              ON activity.objecttype = " . ActivityType::OBJECTTYPE_VIEW . "
                             AND activity.objectid = view.id
                            JOIN {usr_friend} viewsoffriends
                              ON view.owner = viewsoffriends.usr1
                             AND viewsoffriends.usr2 = ?
                            {$viewAccessCondition['sql']}",
                'params' => array($viewer));
            foreach($viewAccessCondition['params'] as $accessconditionparam) {
                $subqueries['friend12_view_' . $key]['params'][] = $accessconditionparam;
            }
            // Friends usr2->usr1.
            $subqueries['friend21_view_' . $key] = array(
                'sql' => "SELECT activity.id
                            FROM {activity} activity
                            JOIN {view} view
                              ON activity.objecttype = " . ActivityType::OBJECTTYPE_VIEW . "
                             AND activity.objectid = view.id
                            JOIN {usr_friend} viewsoffriends
                              ON view.owner = viewsoffriends.usr2
                             AND viewsoffriends.usr1 = ?
                            {$viewAccessCondition['sql']}",
                'params' => array($viewer));
            foreach($viewAccessCondition['params'] as $accessconditionparam) {
                $subqueries['friend21_view_' . $key]['params'][] = $accessconditionparam;
            }
        }

        return $subqueries;
    }

    /**
     * Applies notification preference and watchlist selection to visible activities.
     *
     * Activities will only be displayed in the home stream if the notification preference is set to 'homestream'.
     * Activities will show in the home stream if onlyapplyifwatched is false or onlyapplyifwatched is true and the
     * related object is being watched.
     *
     * @param string $conditionssql sql returned by the previous stage of query construction
     * @param array(int) $params parameters matching $conditionssql
     * @param int $viewer
     * @return array(string, array(int))
     */
    public static function get_homestream_additional_conditions($conditionssql, $params, $viewer) {
        $sql = "SELECT activity.id
                  FROM ({$conditionssql}) unfiltered
                  JOIN {activity} activity
                    ON unfiltered.id = activity.id
                  JOIN {activity_type} activity_type
                    ON activity.activitytype = activity_type.id
                  LEFT JOIN {usr_activity_preference} usr_activity_preference
                    ON activity.activitytype = usr_activity_preference.activity AND
                       usr_activity_preference.usr = ?
                  LEFT JOIN {usr_watchlist_view} usr_watchlist_view
                    ON activity.objecttype = " . ActivityType::OBJECTTYPE_VIEW . " AND
                       activity.objectid = usr_watchlist_view.view AND
                       usr_watchlist_view.usr = ?
                 WHERE (activity_type.onlyapplyifwatched = 0 OR
                        usr_watchlist_view.view IS NOT NULL) AND
                       (usr_activity_preference.method = 'homestream' OR
                        usr_activity_preference.usr IS NULL AND activity_type.defaultmethod = 'homestream')";
        $params[] = $viewer;
        $params[] = $viewer;

        return array($sql, $params);
    }

    /**
     * Given all activities that are visible, group similar activities, apply pagination (exclude activities
     * after and including the given paginationid) and limit to the specified number of results.
     *
     * As get_related_activities_sql, activities will be grouped together if they have the same:
     *    activity date, activitytype, activitysubtype, objecttype, objectid
     *
     * @param string $conditionssql sql returned by the previous stage of query construction
     * @param array(int) $params parameters matching $conditionssql
     * @param int $paginationid if specified, only return activities earlier than this
     * @return array(string, array(int))
     */
    public static function get_reduced_activities_sql($conditionssql, $params, $paginationid = false) {
        $limit = 20; // TODO: Someone may want to make this an admin or user configurable variable.

        if ($paginationid) {
            $paginationsql = "HAVING MAX(activity.id) < ?";
            $params[] = $paginationid;
        }
        else {
            $paginationsql = "";
        }

        $sql = "SELECT activity.objecttype, activity.objectid, activity.activitytype, activity.activitysubtype,
                       CAST(activity.ctime AS DATE) AS ctime, MAX(activity.id) AS activityset
                  FROM {activity} activity
                  JOIN ({$conditionssql}) visibleactivities
                    ON activity.id = visibleactivities.id
                 GROUP BY CAST(activity.ctime AS DATE), activity.objecttype, activity.objectid,
                       activity.activitytype, activity.activitysubtype
                 ORDER BY MAX(activity.id) DESC
                 {$paginationsql}
                 LIMIT {$limit}";

        return array($sql, $params);
    }

    /**
     * Given the reduced set of activities to return, join back to the activity table to get all related activities.
     *
     * As get_reduced_activities_sql, related activities are those that have the same:
     *    activity date, activitytype, activitysubtype, objecttype, objectid
     *
     * @param string $conditionssql sql returned by the previous stage of query construction
     * @param array(int) $params parameters matching $conditionssql
     * @param int $paginationid if specified, only return activities earlier than this
     * @return array(string, array(int))
    */
   public static function get_related_activities_sql($reducedactivitysql, $params, $individualstreamuser = false) {
        // If this is for an individual stream then filter to only those events performed by the owner.
        if ($individualstreamuser) {
            $userstreamfilter = "AND expandedactivity.usr = ?";
            $params[] = $individualstreamuser;
        }
        else {
            $userstreamfilter = "";
        }

        $sql = "SELECT expandedactivity.*, limitedactivity.activityset,
                       " . db_format_tsfield('expandedactivity.ctime', 'ctime') . "
                 FROM {activity} expandedactivity
                 JOIN ({$reducedactivitysql}) limitedactivity
                   ON limitedactivity.objecttype = expandedactivity.objecttype
                  AND limitedactivity.objectid = expandedactivity.objectid
                  AND limitedactivity.activitytype = expandedactivity.activitytype
                  AND limitedactivity.activitysubtype = expandedactivity.activitysubtype
                  AND limitedactivity.ctime = CAST(expandedactivity.ctime AS DATE) " .
                  $userstreamfilter .
               "ORDER BY limitedactivity.activityset DESC, expandedactivity.id DESC";

        return array($sql, $params);
    }

    /**
     * Given the final sql and parameters, excute the query and return the results.
     *
     * This function includes code which can be turned on to allow stream developers to test their queries.
     *
     * @param string $sql returned by the previous stage of query construction
     * @param array(int) $params parameters matching $sql
     * @return array of records
     */
    public static function get_unformatted_records($sql, $params) {
        $explain = false; // Show the query plan.
        $analyse = false; // Show performance analysis with the query plan. Overrides $explain.

        // Add the analyse or explain sql.
        if ($analyse) {
            $sql = "EXPLAIN ANALYSE " . $sql;
        }
        else if ($explain) {
            $sql = "EXPLAIN " . $sql;
        }

        // Execute the query.
        $starttime = microtime(true);
        $activities = get_records_sql_array($sql, $params);
        $endtime = microtime(true);

        // Render the results of an analyse or explain query.
        if ($explain || $analyse) {
            var_dump("Microseconds to execute: " . ($endtime - $starttime));
            printf("<pre>");
            foreach ($activities as $activity) {
                printf($activity->{"QUERY PLAN"} . '<br>');
            }
            var_dump($sql, $params);
            printf("</pre>");
            exit(0);
        }

        // Return the results.
        return $activities;
    }

    /**
     * Given a raw set of activity records, group the similar records to make them easier to process for output.
     *
     * Results are in reverse chronilogical order (both between activity sets and within subactivities).
     *
     * @param array of records $rawactivities
     * @return array of activity data objects which can be used to display activities in an activity stream:
     *         activity->activityset the activityid of the primary activity used for grouping (== subactivity[0]->id)
     *                 ->activitytype
     *                 ->activitysubtype (optional)
     *                 ->objecttype the type of object that the activity was performed on
     *                 ->objectid the id of the object that the activity was performed on
     *                 ->subactivity[]->id the activity id of this subactivity
     *                                ->usr the user who performed this subactivity
     *                                ->additionalid (optional)
     *                                ->ctime the time of this subactivity
     *                 ->subactivitycount
     */
    public static function group_activities($rawactivities) {
        if (empty($rawactivities)) {
            return array();
        }

        $asitems = 0;
        $previousactivityset = -1;
        $groupedactivities = array();
        foreach ($rawactivities as $activity) {
            if ($previousactivityset != $activity->activityset) {
                // If the activityset of this activity is different from the previous record then start a new 'set'.
                $previousactivityset = $activity->activityset;
                $set = new stdClass();
                $set->activityset = $activity->activityset;
                $set->activitytype = $activity->activitytype;
                $set->activitysubtype = $activity->activitysubtype;
                $set->objecttype = $activity->objecttype;
                $set->objectid = $activity->objectid;
                $set->subactivity = array();
                $set->subactivitycount = 0;
                $set->errors = ".";
                $groupedactivities[$previousactivityset] = $set;
                $asitems++;
            }
            unset($activity->activitytype);
            unset($activity->activitysubtype);
            unset($activity->objecttype);
            unset($activity->objectid);
            unset($activity->activityset);
            $set->subactivity[] = $activity;
            $set->subactivitycount++;
        }
        return $groupedactivities;
    }

    // The functions above need to use this alias so that it can be mocked for testing.
    public static function get_view_access_conditions($viewer, $areknownfriends = false) {
        return get_view_access_conditions($viewer, $areknownfriends);
    }

    // The functions above need to use this alias so that it can be mocked for testing.
    public static function get_artefact_access_conditions($viewer, $areknownfriends = false) {
        return get_artefact_access_conditions($viewer, $areknownfriends);
    }

}

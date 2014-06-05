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

require_once(get_config('libroot') . 'activity.php');

class Likes {

    /**
     * Get the information required to uniquely identify the activity or object it is based on.
     *
     * Likes on views and artefacts need to go on the view or artefact itself, not the activity.
     *
     * System activities and subactivities may have different meanings for 'objectid'. Because of this, we
     * put the like on the activity itself.
     *
     * TODO: Likes on group, institution and interaction activities are being treated the same as system
     * activities. This means that likes on these activities will be put on the activities. Someone may
     * want to change this behaviour in the future, particularly relating to forums (interactions).
     *
     * @param object $activity
     * @return object
     */
    private static function get_related_object($activity) {
        $object = new stdClass();
        if ($activity->objecttype == ActivityType::OBJECTTYPE_VIEW ||
                $activity->objecttype == ActivityType::OBJECTTYPE_ARTEFACT) {
            $object->objecttype = $activity->objecttype;
            $object->objectid = $activity->objectid;
        }
        else {
            $object->objecttype = ActivityType::OBJECTTYPE_ACTIVITY;
            $object->objectid = $activity->id;
        }
        return $activity;
    }

    /**
     * Creates an html string stating how many likes the activity or object it is based on has.
     *
     * @param object $activity
     * @return html string
     */
    public static function total_likes($activity) {
        $object = self::get_related_object($activity);

        $totallikes = count_records('likes',
                'objecttype', $object->objecttype,
                'objectid', $object->objectid);

        $totallikesstring = get_string("numberoflikes", 'blocktype.activitystream', $totallikes);

        return "<div class='as-totallikes' id='totallikes{$activity->objecttype}_{$activity->objectid}'>{$totallikesstring}</div>";
    }

    /**
     * Creates an html link which can be clicked to add or remove a like from the activity or object it is based on.
     *
     * @param object $activity
     * @return html string
     */
    public static function action_link($activity) {
        global $USER;

        $baseobject = self::get_related_object($activity);

        $isliked = record_exists('likes',
                'objecttype', $baseobject->objecttype,
                'objectid', $baseobject->objectid,
                'usr', $USER->get('id'));

        $action = $isliked ? 'unlike' : 'like';

        $label = self::action_label($action, $activity);

        return "<a class='as-actionlike' activityid='{$activity->id}' " .
                "id='actionlike{$activity->objecttype}_{$activity->objectid}' action='{$action}'>$label</a>";
    }

    /**
     * Get the string indicating the action that can be performed on the base activity or object.
     *
     * @param string $action 'like' or 'unlike'
     * @param object $activity
     * @return string
     */
    public static function action_label($action, $activity) {

        $baseobject = self::get_related_object($activity);

        $objecttypename = ActivityType::get_object_type_name($baseobject->objecttype, $baseobject->objectid);

        if ($objecttypename) {
            return get_string($action . 'thisobjecttypename', 'blocktype.activitystream', $objecttypename);
        }
        else {
            return get_string($action, 'blocktype.activitystream');
        }
    }

    /**
     * Add a like to the activity or object it is based on, for the given user.
     *
     * @param object $activity
     * @param int $userid
     */
    public static function add($activity, $userid) {
        $object = self::get_related_object($activity);

        // Check if it already exists.
        $recordexists = record_exists('likes',
                'objecttype', $object->objecttype,
                'objectid', $object->objectid,
                'usr', $userid);
        if (!$recordexists) {
            $like = new stdClass();
            $like->objecttype = $object->objecttype;
            $like->objectid = $object->objectid;
            $like->usr = $userid;
            $like->ctime = db_format_timestamp(time());
            insert_record('likes', $like);
        }
    }

    /**
     * Remove a like from the activity or object it is based on, for the given user.
     *
     * @param object $activity
     * @param int $userid
     */
    public static function remove($activity, $userid) {
        $object = self::get_related_object($activity);

        // Remove a like record (doesn't matter if it doesn't exist).
        delete_records('likes',
                'objecttype', $object->objecttype,
                'objectid', $object->objectid,
                'usr', $userid);
    }
}

<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginArtefactSocial extends PluginArtefact {

    public static function get_artefact_types() {
        return array('feedback',
                     'joingroup',
                     'makefriend',
                     );
    }

    public static function get_block_types() {
        return array();
    }

    public static function get_plugin_name() {
        return 'social';
    }

    public static function can_be_disabled() {
        return true;
    }

    public static function progressbar_link($artefacttype) {
        switch ($artefacttype) {
         case 'feedback':
            return 'view/sharedviews.php';
            break;
         case 'joingroup':
            return 'group/find.php';
            break;
         case 'makefriend':
            return 'user/find.php';
            break;
         default:
            return '';
        }
    }

    public static function progressbar_metaartefact_count($name) {
        global $USER;
        $meta = new StdClass();
        $meta->artefacttype = $name;
        $meta->completed = 0;
        switch ($name) {
            case 'feedback':
                $sql = "SELECT COUNT(*) AS completed
                         FROM {artefact}
                       WHERE artefacttype='comment'
                         AND owner <> ? AND author = ?";
                $count = get_records_sql_array($sql, array($USER->get('id'), $USER->get('id')));
                $meta->completed = $count[0]->completed;
                break;
            case 'joingroup':
                $sql = "SELECT COUNT(*) AS completed
                         FROM {group_member}
                       WHERE member = ?";
                $count = get_records_sql_array($sql, array($USER->get('id')));
                $meta->completed = $count[0]->completed;
                break;
            case 'makefriend':
                $sql = "SELECT COUNT(*) AS completed
                         FROM {usr_friend}
                       WHERE usr1 = ?";
                $count = get_records_sql_array($sql, array($USER->get('id')));
                $meta->completed = $count[0]->completed;
                break;
            default:
                return false;
        }
        return $meta;
    }
}

class ArtefactTypeFeedback extends ArtefactType {

    public static function get_icon($options=null) {

    }

    public static function is_singular() {

    }

    public static function get_links($id) {
        return array();
    }

    public static function is_countable_progressbar() {
        return true;
    }

    public static function is_metaartefact() {
        return true;
    }
}

class ArtefactTypeJoingroup extends ArtefactType {

    public static function get_icon($options=null) {

    }

    public static function is_singular() {

    }

    public static function get_links($id) {
        return array();
    }

    public static function is_countable_progressbar() {
        return true;
    }

    public static function is_metaartefact() {
        return true;
    }
}

class ArtefactTypeMakefriend extends ArtefactType {

    public static function get_icon($options=null) {

    }

    public static function is_singular() {

    }

    public static function get_links($id) {
        return array();
    }

    public static function is_countable_progressbar() {
        return true;
    }

    public static function is_metaartefact() {
        return true;
    }
}

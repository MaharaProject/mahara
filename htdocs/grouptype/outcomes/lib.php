<?php
/**
 *
 * @package    mahara
 * @subpackage grouptype-outcomes
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();
require_once(dirname(dirname(__FILE__)) . '/course/lib.php');

class PluginGrouptypeOutcomes extends PluginGrouptype {

    /**
     * Fetch the human readable name for the plugin
     *
     * @return string
     */
    public static function get_plugin_display_name() {
        return get_string('name', 'grouptype.outcomes');
    }

    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            parent::installgrouptype('GrouptypeOutcomes');
        }
        return true;
    }

}

class GrouptypeOutcomes extends GroupTypeCourse {

  public static function deny_access_for_role($group, $denyrole) {
    $accessrole = group_user_access($group->id);
    return !group_user_can_edit_views($group) || $accessrole == $denyrole;
  }
}

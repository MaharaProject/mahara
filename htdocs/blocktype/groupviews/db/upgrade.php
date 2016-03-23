<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-groupviews
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

safe_require('blocktype', 'groupviews');

function xmldb_blocktype_groupviews_upgrade($oldversion=0) {

    if ($oldversion < 2015020201) {

        // Get the group view block from the default group home page view.
        $sql = "SELECT bi.id, bi.configdata
                  FROM {block_instance} bi
            INNER JOIN {view} v ON v.id = bi.view
                 WHERE bi.blocktype = 'groupviews'
                       AND v.type = 'grouphomepage'
                       AND v.owner = 0";
        $defaultgrouppage = get_record_sql($sql, array());

        if ($defaultgrouppage) {
            log_debug("Processing default 'Group page' block to set the right shared pages/collections configurations");

            $configdata = unserialize($defaultgrouppage->configdata);
            $configdata['showsharedviews'] = 1;
            $configdata['showsharedcollections'] = 1;
            set_field('block_instance', 'configdata', serialize($configdata), 'id', $defaultgrouppage->id);
        }
    }

    return true;
}

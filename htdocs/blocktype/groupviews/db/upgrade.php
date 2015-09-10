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
    if ($oldversion < 2015090300) {

        $sql = "SELECT id, configdata FROM {block_instance} WHERE blocktype='groupviews'";
        $records = get_records_sql_array($sql, array());

        if ($records) {
            log_debug("Processing 'Group pages' blocks so they continue to display full list of pages/collections");
            $count = 0;
            $limit = 500;
            $total = count($records);
            foreach ($records as $r) {
                $configdata = unserialize($r->configdata);
                $updateconfig = false;
                // Do we need to update the setting for shared views
                if (!isset($configdata['showsharedviews']) || $configdata['showsharedviews'] == 1) {
                    $updateconfig = true;
                    $configdata['showsharedviews'] = 2;
                }
                // Do we need to update the setting for shared collections
                if (!isset($configdata['showsharedcollections']) || $configdata['showsharedcollections'] == 1) {
                    $updateconfig = true;
                    $configdata['showsharedcollections'] = 2;
                }

                if ($updateconfig) {
                    set_field('block_instance', 'configdata', serialize($configdata), 'id', $r->id);
                }
                $count++;
                if (($count % $limit) == 0 || $count == $total) {
                    log_debug("$count/$total");
                    set_time_limit(30);
                }
            }
        }
    }

    return true;
}

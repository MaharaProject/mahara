<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-newviews
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_blocktype_newviews_upgrade($oldversion=0) {
    if ($oldversion < 2020122200 ) {
        log_debug('Updating the newviews blocks to transfer the "Shared with ... " config');
        if ($newviewsblocks = get_records_sql_array("SELECT id FROM {block_instance} WHERE blocktype = ?", array('newviews'))) {
            $count = 0;
            $limit = 500;
            $total = count($newviewsblocks);
            foreach ($newviewsblocks as $block) {
                $bi = new BlockInstance($block->id);
                $configdata = $bi->get('configdata');
                $configdata['user'] = 1;
                $configdata['friend'] = 1;
                $configdata['group'] = 1;
                $bi->set('configdata', $configdata);
                $bi->commit();

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

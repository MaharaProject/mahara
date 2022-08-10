<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-watchlist
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_blocktype_watchlist_upgrade($oldversion=0) {
    if ($oldversion < 2020121000) {
        $prevsetting = get_config('watchlistnotification_delay');
        set_config_plugin('blocktype', 'watchlist', 'watchlistnotification_delay', (int)$prevsetting);
        execute_sql("DELETE FROM {config} WHERE field = ?", array('watchlistnotification_delay'));
    }
    return true;
}

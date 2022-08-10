<?php
/**
 *
 * @package    mahara
 * @subpackage elasticsearch
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

if (!defined('INTERNAL')) {
    die();
}

/**
 * @param int $oldversion The version we are checking against.
 *
 * @return boolean Was the process successful?
 */
function xmldb_search_elasticsearch7_upgrade($oldversion=0) {
    $location = get_config('docroot') . 'search/elasticsearch7/';

    if ($oldversion < 2021052000) {
        install_from_xmldb_file($location . 'db/install.xml');
    }

    return true;
}

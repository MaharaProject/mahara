<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-wall
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_blocktype_wall_upgrade($oldversion=0) {

    if ($oldversion < 2009021801) {
        set_config_plugin('blocktype', 'wall', 'defaultpostsizelimit', 1500); // 1500 characters
    }
    return true;
}

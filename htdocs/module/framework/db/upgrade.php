<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_module_framework_upgrade($oldversion=0) {

    if ($oldversion < 2016081900) {
        log_debug('Adding "active" column to "framework" table');
        $table = new XMLDBTable('framework');
        $field = new XMLDBField('active');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 1);
        add_field($table, $field);
    }

    return true;
}

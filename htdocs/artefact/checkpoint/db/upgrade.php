<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-checkpoint
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_artefact_checkpoint_upgrade($oldversion=0) {

    $success = true;

    if ($oldversion < 2023020900) {
        $table = new XMLDBTable('artefact_checkpoint_feedback');
        $field = new XMLDBField('deletedby');
        $field->setAttributes(XMLDB_TYPE_CHAR, 50);
        add_field($table, $field);
    }

    return $success;
}

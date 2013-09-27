<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-comment
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_artefact_comment_upgrade($oldversion=0) {

    $success = true;

    if ($oldversion < 2011011201) {
        $table = new XMLDBTable('artefact_comment_comment');
        $field = new XMLDBField('rating');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED);

        $success = $success && add_field($table, $field);
    }

    if ($oldversion < 2013072400) {
        $table = new XMLDBTable('artefact_comment_comment');
        $field = new XMLDBField('lastcontentupdate');
        $field->setAttributes(XMLDB_TYPE_DATETIME);
        $success = $success && add_field($table, $field);

        $success = $success && execute_sql(
            'update {artefact_comment_comment} acc
            set lastcontentupdate = (
                select a.mtime
                from {artefact} a
                where a.id = acc.artefact
            )'
        );
    }

    return $success;
}

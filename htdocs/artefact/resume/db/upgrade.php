<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_artefact_resume_upgrade($oldversion=0) {

    $status = true;

    if ($oldversion < 2009122100) {
        $table = new XMLDBTable('artefact_resume_employmenthistory');
        $field = new XMLDBField('employeraddress');
        $field->setAttributes(XMLDB_TYPE_TEXT);
        add_field($table, $field);

        $table = new XMLDBTable('artefact_resume_educationhistory');
        $field = new XMLDBField('institutionaddress');
        $field->setAttributes(XMLDB_TYPE_TEXT);
        add_field($table, $field);
    }

    if ($oldversion < 2010020300) {
        $table = new XMLDBTable('artefact_resume_educationhistory');
        $field = new XMLDBField('qualtype');
        $field->setAttributes(XMLDB_TYPE_TEXT);
        change_field_notnull($table, $field);

        $table = new XMLDBTable('artefact_resume_educationhistory');
        $field = new XMLDBField('qualname');
        $field->setAttributes(XMLDB_TYPE_TEXT);
        change_field_notnull($table, $field);
    }

    if ($oldversion < 2013071300) {
        $table = new XMLDBTable('artefact_resume_book');
        $field = new XMLDBField('url');
        $field->setAttributes(XMLDB_TYPE_TEXT);
        add_field($table, $field);
    }

    if ($oldversion < 2013072900) {
        execute_sql("UPDATE {blocktype_installed_category} SET category = 'internal' WHERE category = 'resume'");
    }

    if ($oldversion < 2017030600) {
        log_debug("Allow WYSIWYG HTML for the field 'description' of Resume Book, Certification, and Membership fields");
        // Escape all HTML tags in the old database
        $types = array('book', 'membership', 'certification', 'employmenthistory', 'educationhistory');
        foreach ($types as $type) {
            log_debug("Cleaning up data for " . $type);
            $total = count_records('artefact_resume_' . $type);
            $count = 0;
            $limit = 1000;
            for ($i = 0; $i <= $total; $i += $limit) {
                switch ($type) {
                    case 'employmenthistory':
                        $description = 'positiondescription';
                        break;
                    case 'educationhistory':
                        $description = 'qualdescription';
                        break;
                    default:
                        $description = 'description';
                }
                $sql = "
                    SELECT r.id, r." . $description . "
                    FROM {artefact_resume_" . $type . "} r
                    ORDER BY r.id";
                $resumes = get_records_sql_array($sql, array(), $i, $limit);
                if ($resumes) {
                    foreach ($resumes as $item) {
                        // Escape HTML tags in "description"
                        $item->{$description} = hsc($item->{$description});
                        set_field('artefact_resume_' . $type, $description, $item->{$description}, 'id', $item->id);
                        $count += $limit;
                    }
                    if (($count % $limit) == 0 || $count >= $total) {
                        if ($count > $total) {
                            $count = $total;
                        }
                        log_debug("$count/$total");
                        set_time_limit(30);
                    }
                }
            }
        }
    }

    return $status;
}

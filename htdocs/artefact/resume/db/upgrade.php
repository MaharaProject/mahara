<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
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

    if ($oldversion < 2022100700) {
        log_debug("Adjust some 'resume*' for embedded images to match their actual artefact type");
        $mapping = array('resumecoverletter' => 'coverletter',
                         'resumeinterest' => 'interest');
        foreach ($mapping as $mk => $mv) {
            log_debug("- for " . $mk);
            if ($records = get_records_sql_array("
                    SELECT a.id AS aid, a.owner, afe.id AS embedid
                    FROM {artefact_file_embedded} afe
                    JOIN {artefact} a ON a.owner = afe.resourceid
                    WHERE afe.resourcetype = ?
                    AND a.artefacttype = ?", array($mk, $mv))) {
                $count = 0;
                $limit = 500;
                $total = count($records);
                foreach ($records as $record) {
                    execute_sql("UPDATE {artefact_file_embedded} SET resourcetype = ? WHERE id = ?", array($mv, $record->embedid));
                    execute_sql("UPDATE {artefact} SET description = REPLACE(description, '" . $mk . "=', '" . $mv . "=') WHERE id = ?", array($record->aid));
                    $count++;
                    if (($count % $limit) == 0 || $count == $total) {
                        log_debug("$count/$total");
                        set_time_limit(30);
                    }
                }
            }
        }
        log_debug("Re-save some resume composite items so their embedded items are recorded in the database");
        $compositesdescriptions = array('book' => 'description',
                                        'certification' => 'description',
                                        'membership' => 'description',
                                        'employmenthistory' => 'positiondescription',
                                        'educationhistory' => 'qualdescription');
        $composites = array_keys($compositesdescriptions);
        require_once('embeddedimage.php');
        foreach ($composites as $composite) {
            $tablename = 'artefact_resume_' . $composite;
            if ($records = get_records_sql_array("SELECT a.owner, a.artefacttype, ac.* FROM " . db_table_name($tablename) . " ac JOIN {artefact} a ON a.id = ac.artefact")) {
                foreach ($records as $record) {
                    if (EmbeddedImage::has_embedded_image($record->{$compositesdescriptions[$composite]}, $composite, $record->owner)) {
                        $whereobject = (object)array(
                            'fileid' => $record->artefact,
                            'resourcetype' => $composite,
                            'resourceid' => $record->owner,
                        );
                        ensure_record_exists('artefact_file_embedded', $whereobject, $whereobject);
                        break;
                    }
                }
            }
        }

        log_debug("Re-save resume blocks so their embedded/attached items are associated with the view");
        require_once(get_config('docroot') . 'blocktype/lib.php');
        $artefacttypes = PluginArtefactResume::get_artefact_types();
        if ($users = get_column_sql("
            SELECT DISTINCT resourceid FROM {artefact_file_embedded}
            WHERE resourcetype IN (" . join(',', array_map('db_quote', array_values($artefacttypes))) . ")")) {
            $count = 0;
            $limit = 500;
            $total = count($users);
            foreach ($users as $userid) {
                // Update the resume blocks for this person
                if ($blocks = get_column_sql("SELECT DISTINCT va.block
                                              FROM {view_artefact} va
                                              JOIN {block_instance} bi ON bi.id = va.block
                                              JOIN {view} v ON v.id = va.view
                                              WHERE bi.blocktype IN ('entireresume', 'resumefield')
                                              AND v.owner = ?", array($userid))) {
                    log_debug("Re-saving resume blocks for person ID: " . $userid);
                    foreach ($blocks as $blockid) {
                        $bi = new BlockInstance($blockid);
                        $bi->set('dirty', true);
                        $bi->commit();
                    }
                }
                $count++;
                if (($count % $limit) == 0 || $count == $total) {
                     log_debug("$count/$total");
                     set_time_limit(30);
                }
            }
        }
    }

    return $status;
}

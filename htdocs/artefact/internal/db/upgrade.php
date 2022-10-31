<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-internal
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_artefact_internal_upgrade($oldversion=0) {

    $status = true;

    if ($oldversion < 2014022700) {
        // Remove the unnecessary Contact information block and change all current instances to Profile information
        execute_sql("UPDATE {block_instance} SET blocktype='profileinfo' WHERE blocktype='contactinfo'");
        execute_sql("DELETE FROM {blocktype_installed_viewtype} WHERE blocktype='contactinfo'");
        execute_sql("DELETE FROM {blocktype_installed_category} WHERE blocktype='contactinfo'");
        execute_sql("DELETE FROM {blocktype_installed} WHERE name='contactinfo'");
    }

    if ($oldversion < 2020121800) {
        set_config_plugin('artefact', 'internal', 'allowcomments', 'notes');
    }

    if ($oldversion < 2022100700) {
        log_debug("Adjust 'profileintrotext' embedded images to match their actual artefact type");
        $mapping = array('profileintrotext' => 'introduction');
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
        log_debug("Re-save profile blocks so their embedded items are associated with the view");
        require_once(get_config('docroot') . 'blocktype/lib.php');
        $artefacttypes = PluginArtefactInternal::get_artefact_types();
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
                                              WHERE bi.blocktype IN ('profileinfo')
                                              AND v.owner = ?", array($userid))) {
                    log_debug("Re-saving profileinfo blocks for person ID: " . $userid);
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

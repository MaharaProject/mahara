<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_artefact_internal_upgrade($oldversion=0) {
    
    $status = true;

    if ($oldversion < 2007042500) {
        // migrate everything we had to change to  make mysql happy
        execute_sql("ALTER TABLE {artefact_internal_profile_email} ALTER COLUMN email TYPE varchar(255)");
        execute_sql("ALTER TABLE {artefact_internal_profile_icon} ALTER COLUMN filename TYPE varchar(255)");

    }

    if ($oldversion < 2008101300) {
        execute_sql("DROP TABLE {artefact_internal_profile_icon}");
    }

    return $status;
}

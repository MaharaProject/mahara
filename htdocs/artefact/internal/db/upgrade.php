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

    if ($oldversion < 2014022700) {
        // Remove the unnecessary Contact information block and change all current instances to Profile information
        execute_sql("UPDATE {block_instance} SET blocktype='profileinfo' WHERE blocktype='contactinfo'");
        execute_sql("DELETE FROM {blocktype_installed_viewtype} WHERE blocktype='contactinfo'");
        execute_sql("DELETE FROM {blocktype_installed_category} WHERE blocktype='contactinfo'");
        execute_sql("DELETE FROM {blocktype_installed} WHERE name='contactinfo'");
    }

    return $status;
}

<?php
/**
 *
 * @package    mahara
 * @subpackage module-framework
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('module', 'framework');

if (!PluginModuleFramework::is_active()) {
    json_reply(true, get_string('pluginnotactive1', 'error', get_string('frameworknav', 'module.framework')));
}

if ($framework_id = param_integer('framework_id', null)) {
    if (record_exists('framework', 'id', $framework_id)) {
        $fw_data = get_smartevidence_framework($framework_id);
        json_reply(false, (object) array('data' => $fw_data));
    }
    else {
        json_reply(true, get_string('noframeworkfoundondb', 'module.framework'));
    }
}
else {
    json_reply(true, get_string('missingparamframeworkid', 'module.framework'));
}

/*
 * Get the framework from the database
 * @param integer $framework_id the id from the framework on the DB
 */
function get_smartevidence_framework($id) {
    // the return variable
    $data = array();
    // raw data from DB
    $fw_data = get_record('framework', 'id', $id);
    if (!$fw_data) {
        json_reply(true, get_string('missingrecordsdb', 'module.framework', 'framework'));
    }
    if ($fw_data->institution != 'all') {
        $fw_data->institution = get_field('institution', 'displayname', 'name', $fw_data->institution);
    }
    else {
        $fw_data->institution = get_string('all', 'module.framework');
    }

    $data['info'] = $fw_data;

    $data['evidencestatuses'] = get_records_array('framework_evidence_statuses', 'framework', $id);

    $fields = 'id, framework, shortname, name, description, priority';
    $standard_data = get_records_array('framework_standard', 'framework', $id, 'priority', $fields);
    if (!$standard_data) {
        json_reply(true, get_string('missingrecordsdb', 'module.framework', 'standard'));
    }
    $data['standards'] = $standard_data;

    $fields = 'id, standard, shortname, name, description, priority, parent';
    foreach ($standard_data as $sk => $standard) {
        if ($children_of_standard = get_records_array('framework_standard_element', 'standard', $standard->id, 'priority', $fields)) {
            foreach($children_of_standard as $se) {
                $se->depth = get_element_depth($se, $children_of_standard);
            }
            $data['standards']['element'][$sk] = $children_of_standard;
        }
    }
    return $data;
}

/*
 * Get the distance between the element and the standard
 * (how many parent element are between them)
 */
function get_element_depth($se, $children_of_standard) {
    $depth = 0;
    if ($se->parent !== null) {
        $parent_element = get_parent_element($children_of_standard, $se->parent);
        $depth = get_element_depth($parent_element, $children_of_standard) + 1;
    }
    return $depth;
}

/*
 * Given a standard element parent id, this will return the data
 * of that parent element
 */
function get_parent_element($children_of_standard, $parent_id) {
    foreach($children_of_standard as $se) {
        if ($se->id == $parent_id) {
            return $se;
        }
    }
}
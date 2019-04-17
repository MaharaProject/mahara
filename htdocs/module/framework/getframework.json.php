<?php
/**
 *
 * @package    mahara
 * @subpackage module-framework
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

//This file is for getting a framework from the db

define('INTERNAL', 1);
define('JSON',1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

safe_require('module', 'framework');
if (!PluginModuleFramework::is_active()) {
    json_reply(true, get_string('pluginnotactive1', 'error', get_string('frameworknav', 'module.framework')));
}

//@TODO - nothing done with errors
if (!$_POST) {
    //error
}
else {
    $form_data = $_POST;
}

$framework_id = param_variable('framework_id', null);
$table = 'framework';
//@TODO - check error invalid arg for foreach "framework", "6") -> ln 54
$fw_data = get_framework($table, $framework_id);

function get_framework($table, $select, $se_depth=0) {
    $data = array();
    if (record_exists_select($table, "id='$select'")) {
        $fw_data = get_record($table, 'id', $select);
        if ($fw_data->institution != 'all') {
            $displayname = get_field('institution', 'displayname', 'name', $fw_data->institution);
            $fw_data->institution = $displayname;
        }
        $data['title'] = $fw_data;
    }
    if (isset($fw_data->id)) {
        $evidence_statuses = get_records_array('framework_evidence_statuses', 'framework', $fw_data->id);
        $data['evidencestatuses'] = $evidence_statuses;
        $standard_data = get_records_array('framework_standard', 'framework', $fw_data->id, 'priority');
        $data['standards'] = $standard_data;
        $se_data = array();
        foreach ($standard_data as $sk => $se) {
            if ($se_data = get_records_array('framework_standard_element', 'standard', $se->id, 'priority')) {
                $se_depth = 0;
                foreach($se_data as $se) {
                    $se->depth = add_depth_to_ses($se, $se_data, $se_depth);
                }
                $data['standards']['element'][$sk] = $se_data;
            }
        }
    }
    return $data;
}

function add_depth_to_ses($se, $se_data, $se_depth) {
    if (($parent_id = $se->parent) !== null) {
        $se_depth ++;
        $se_with_id_of_parent = get_parent($se_data, $parent_id);
        add_depth_to_ses($se_with_id_of_parent, $se_data, $se_depth);
        return $se_depth;
    }
    else {
        return $se_depth;
    }
}
function get_parent($se_data, $parent_id) {
    foreach($se_data as $se) {
        if ($se->id == $parent_id) {
            return $se;
        }
    }
}

json_reply(false, (object) array('data' => $fw_data));

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
define('JSON',1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

safe_require('module', 'framework');
if (!PluginModuleFramework::is_active()) {
    json_reply(true, get_string('pluginnotactive1', 'error', get_string('frameworknav', 'module.framework')));
}

// if the fw_id is set, we are editing an existing framework
$fw_to_edit = param_variable('fw_id', null);

// save the DB ids if set on the form data, to check if we need to delete any from the DB
$fw_sids = array();
$fw_seids = array();

$form_data = format_input_framework($fw_to_edit);

if (is_null($form_data)) {
    json_reply(true, get_string('invalidjsonineditor', 'module.framework'));
}
else {
    if (empty($form_data->name) || empty($form_data->standards)) {
        json_reply(true, get_string('jsonmissingvars', 'module.framework'));
    }
    else {
        // format standards structure, set standard elements inside standards
        if (isset($form_data->standardelements)) {
            foreach ($form_data->standards as $key => $standard) {
                if (!isset($form_data->standards[$key]->standardelement)) {
                    $form_data->standards[$key]->standardelement = array();
                }
                foreach ($form_data->standardelements as $element) {
                    if ($standard->standardid === $element->standardid) {
                        $form_data->standards[$key]->standardelement[] = $element;
                    }
                }
            }
            unset($form_data->standardelements);
        }

        //adding extra array required for update
        //@TODO these should really work the same.
        if ($fw_to_edit) {
            $stds = array('standards' => $form_data->standards);
            $form_data->standards = $stds;
        }
        //put ok content into session variable
        $SESSION->set('jsoneditorcontent', $form_data);
    }
}

if ($fw_to_edit) {
    $framework = new Framework($fw_to_edit, $form_data);
    remove_deleted_items_from_db($fw_to_edit);
}
else {
    $form_data->active = false;
    $framework = new Framework(null, $form_data);
}

$framework->commit();
if (!$fw_to_edit) {
    $framework->set_config_fields();
}

$data['id'] = $framework->get('id');
$data['institution'] = $form_data->institution;
$data['name'] = $framework->get('name');
// need to also add uid with DB id of standards and elements
// when we are saving for the first time (new framework or copy of existing one)

json_reply(false, (object) array(
    'message' => get_string('successmessage', 'module.framework'),
    'data' => $data)
);

function format_input_framework($fw_to_edit=null) {
    global $fw_sids, $fw_seids;
    $form_data = (object) array_filter($_POST, function ($k) {
        $expected_data = [
            'institution',
            'name',
            'description',
            'selfassess',
            'evidencestatuses',
            'standards',
            'standardelements',
            'sesskey'
        ];
        if (in_array($k, $expected_data)) {
            return $k;
        }
    }, ARRAY_FILTER_USE_KEY);

    if ($form_data->institution != get_string('all', 'module.framework')) {
        $form_data->institution = get_field('institution', 'name', 'displayname', $form_data->institution);
    }
    else {
        $form_data->institution = 'all';
    }

    $form_data->selfassess = ($form_data->selfassess === "true" ? true : false);

    $evidencestatuses = $form_data->evidencestatuses;
    $form_data->evidencestatuses = array();
    foreach ($evidencestatuses as $key => $es) {
        $form_data->evidencestatuses[] = (object) array($key => $es);
    }

    // calculate priority (order of standards and elements)
    $priority = 0;
    $standards = array();
    foreach ($form_data->standards as $std) {
        $priority ++;
        $std = (object) $std;
        $std->priority = $priority;
        if ($fw_to_edit && isset($std->uid)) {
            $std->id = $std->uid;
            // save to later check if need to delete from DB
            array_push($fw_sids, $std->uid);
            unset($std->uid);
        }
        $standards[] = $std;
    }
    $form_data->standards = $standards;

    $priority = 0;
    $elements = array();
    foreach ($form_data->standardelements as $stdel) {
        $priority ++;
        $stdel = (object) $stdel;
        $stdel->priority = $priority;
        if ($fw_to_edit && isset($stdel->uid)) {
            $stdel->id = $stdel->uid;
            // save to later check if need to delete from DB
            array_push($fw_seids, $stdel->uid);
            unset($stdel->uid);
        }
        $elements[] = $stdel;
    }
    $form_data->standardelements = (object) $elements;

    return $form_data;
}

function remove_deleted_items_from_db($fw_to_edit) {
    global $fw_sids, $fw_seids;
    //need to delete records if edit is true and there is an id missing.
    //so get the ids expected from the db.
    // get standard ids from the DB
    $db_sids = array();
    $db_sids = get_column('framework_standard', 'id', 'framework', $fw_to_edit);

    // get standard element ids from the DB
    $db_stdels = array();
    $sql = 'SELECT id FROM {framework_standard_element}
        WHERE standard IN (' . join(',', array_fill(0, count($db_sids), '?')) . ')';
    $db_stdels = get_column_sql($sql, $db_sids);

    $stds_to_delete = array_values(array_diff($db_sids, $fw_sids));
    $ses_to_delete = array_values(array_diff($db_stdels, $fw_seids));

    if ($ses_to_delete) {
        foreach ($ses_to_delete as $se) {
            delete_records('framework_standard_element', 'id', $se);
        }
    }
    if ($stds_to_delete) {
        foreach ($stds_to_delete as $std) {
            if (!get_records_array('framework_standard_element', 'standard', $std)) {
                delete_records('framework_standard', 'id', $std);
            }
        }
    }
}
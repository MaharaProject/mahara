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

//This file is for submitting a framework to the db

define('INTERNAL', 1);
define('JSON',1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

safe_require('module', 'framework');
if (!PluginModuleFramework::is_active()) {
    json_reply(true, get_string('pluginnotactive1', 'error', get_string('frameworknav', 'module.framework')));
}

if (!$_POST) {
    //form not submitted
    json_reply(true, get_string('jsondatanotsubmitted', 'module.framework'));
}
else {
    //populate form_data with keys we expect to see
    $form_data = array_filter($_POST, function ($k) {
        $expected_data = [
            'institution',
            'name',
            'description',
            'selfassess',
            'evidencestatuses',
            'standards',
            'standardelements',
            'fw_id',
            'sesskey'
        ];
        if (in_array($k, $expected_data)) {
            return $k;
        }
    }, ARRAY_FILTER_USE_KEY);
}

//if true, we are editing an existing framework
$fw_to_edit = param_variable('fw_id', null);
unset($form_data['fw_id']);

if ($form_data['institution'] != 'all') {
    $institutionname = get_field('institution', 'name', 'displayname', $form_data['institution']);
    $form_data['institution'] = $institutionname;
}

 //change $selfassess from true/false string to true/false.
$selfassess = param_variable('selfassess', null);
if ($selfassess === "false") {
    $selfassess = false;
}
else if ($selfassess === "true") {
    $selfassess = true;
}
$form_data['selfassess'] = $selfassess;

$evidencestatuses = param_variable('evidencestatuses', null);

//@TODO - this is a mess - refactor!
//need to delete records if edit is true and there is an id missing.
//so get the ids expected from the db.
if ($fw_to_edit) {
    $std_ids = get_records_array('framework_standard','framework', $fw_to_edit, '', 'id');
    $db_sids = array();
    foreach ($std_ids as $sid) {
        array_push($db_sids, $sid->id);
    }
    $db_seids = array();
    foreach($db_sids as $sid) {
        $stdel_ids = get_records_array('framework_standard_element', 'standard', $sid, '', 'id');
        array_push($db_seids, $stdel_ids);
    }

    $db_stdels = array();
    foreach ($db_seids as $seids) {
        if ($seids != null) {
            foreach ($seids as $seid) {
                if (isset($seid->id)) {
                    array_push($db_stdels, $seid->id);
                }
            }
        }
    }

    $fw_sids = array();
    foreach( $form_data['standards'] as &$std) {
        if (isset($std['uid'])) {
            array_push($fw_sids, $std['uid']);
        }
    }
    $stds_to_delete = array_values(array_diff($db_sids, $fw_sids));

    $fw_seids = array();
    foreach ($form_data['standardelements'] as &$se) {
        if (isset($se['uid'])) {
            array_push($fw_seids, $se['uid']);
        }
    }
    $ses_to_delete = array();
    $ses_to_delete = array_values(array_diff($db_stdels, $fw_seids));
}

$std_count = 0;
foreach ($form_data['standards'] as &$std) {
    $std_count ++;
    $std['priority'] = $std_count;
    if (isset($std['uid'])) {
        if ($fw_to_edit) {
            $std['id'] = $std['uid'];
        }
        unset($std['uid']);
    }
}
$stdel_count = 0;
foreach ($form_data['standardelements'] as &$stdel) {
    $stdel_count ++;
    $stdel['priority'] = $stdel_count;
    if (isset($stdel['uid'])) {
        if ($fw_to_edit) {
            $stdel['id'] = $stdel['uid'];
        }
        unset($stdel['uid']);
    }
}

$matrix = json_encode($form_data);
$content = json_decode($matrix);

if (is_null($content)) {
    $ok['error'] = true;
    $ok['message'] = get_string('invalidjsonineditor', 'module.framework');
}
else {
    $content->evidencestatuses = array();
    $count = 0;
    foreach ($evidencestatuses as $key => $es) {
        $obj = new stdClass;
        $obj->$key = $es;
        $content->evidencestatuses[$count] = $obj;
        $count ++;
    }
    if (empty($content->name) || empty($content->standards)) {
        $ok['error'] = true;
        $ok['message'] = get_string('jsonmissingvars', 'module.framework');
    }
    else {
        $ok['content'] = $content;
        if (isset($content->standardelements)) {
            foreach ($content->standards as $key => $standard) {
                foreach ($content->standardelements as $k => $element) {
                    if ($standard->standardid === $element->standardid) {
                        if (!isset($content->standards[$key]->standardelement)) {
                            $content->standards[$key]->standardelement = array();
                        }
                        $content->standards[$key]->standardelement[] = $element;
                    }
                }
            }
            unset($content->standardelements);
        }
        //adding extra array required for update
        //@TODO these should really work the same.
        if ($fw_to_edit) {
            $stds = array('standards' => $content->standards);
            $content->standards = $stds;
        }
        //put ok content into session variable
        $SESSION->set('jsoneditorcontent', $content);
    }
}

if ($fw_to_edit) {
    $framework = new Framework($fw_to_edit, $content);
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
else {
    $content->active = false;
    $framework = new Framework(null, $content);
}

$framework->commit();
if (!$fw_to_edit) {
    $framework->set_config_fields();
}

$data['id'] = $framework->get('id');
$data['institution'] = $form_data['institution'];
$data['name'] = $framework->get('name');

$message = get_string('successmessage', 'module.framework');

json_reply(false, (object) array('message' => $message, 'data' => $data));

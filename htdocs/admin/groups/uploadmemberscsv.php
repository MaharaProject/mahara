<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'managegroups/uploadmemberscsv');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('uploadgroupmemberscsv', 'admin'));
require_once(get_config('libroot') . 'group.php');
require_once(get_config('libroot') . 'institution.php');
safe_require('artefact', 'internal');

// Turn on autodetecting of line endings, so mac newlines (\r) will work
ini_set('auto_detect_line_endings', 1);

$FORMAT = array();
$ALLOWEDKEYS = array(
    'shortname',
    'username',
    'role',
);
$MANDATORYFIELDS = array(
    'shortname',
    'username',
    'role',
);
$MEMBERS = array(); // Store the members
$GROUPS = array(); // Map gid to group shortnames

$form = array(
    'name' => 'uploadcsv',
    'elements' => array(
        'institution' => get_institution_selector(),
        'file' => array(
            'type' => 'file',
            'class' => 'last',
            'title' => get_string('csvfile', 'admin'),
            'description' => get_string('groupmemberscsvfiledescription', 'admin'),
            'accept' => '.csv, text/csv, application/csv, text/comma-separated-values',
            'rules' => array(
                'required' => true
            )
        ),
        'progress_meter_token' => array(
            'type' => 'hidden',
            'value' => 'uploadgroupmemberscsv',
            'readonly' => TRUE,
        ),
        'submit' => array(
            'class' => 'btn-primary',
            'type' => 'submit',
            'value' => get_string('uploadgroupmemberscsv', 'admin')
        )
    )
);


/**
 * The CSV file is parsed here so validation errors can be returned to the
 * user. The data from a successful parsing is stored in the <var>$CVSDATA</var>
 * array so it can be accessed by the submit function
 *
 * @param Pieform  $form   The form to validate
 * @param array    $values The values submitted
 */
function uploadcsv_validate(Pieform $form, $values) {
    global $CSVDATA, $ALLOWEDKEYS, $MANDATORYFIELDS, $FORMAT, $USER, $UPDATES, $MEMBERS, $GROUPS;

    // Don't even start attempting to parse if there are previous errors
    if ($form->has_errors()) {
        return;
    }

    if ($values['file']['size'] == 0) {
        $form->set_error('file', $form->i18n('rule', 'required', 'required', array()));
        return;
    }

    $institution = $values['institution'];
    if (!$USER->can_edit_institution($institution)) {
        $form->set_error('institution', get_string('notadminforinstitution', 'admin'));
        return;
    }

    require_once('csvfile.php');

    $csvgroups = new CsvFile($values['file']['tmp_name']);
    $csvgroups->set('allowedkeys', $ALLOWEDKEYS);

    $csvgroups->set('mandatoryfields', $MANDATORYFIELDS);
    $csvdata = $csvgroups->get_data();

    if (!empty($csvdata->errors['file'])) {
        $form->set_error('file', $csvdata->errors['file']);
        return;
    }

    $csverrors = new CSVErrors();

    $formatkeylookup = array_flip($csvdata->format);

    $shortnames = array();
    $hadadmin = array();

    $num_lines = count($csvdata->data);

    foreach ($csvdata->data as $key => $line) {
        // If headers exists, increment i = key + 2 for actual line number
        $i = ($csvgroups->get('headerExists')) ? ($key + 2) : ($key + 1);

        // In adding 5000 groups, this part was approx 8% of the wall time.
        if (!($key % 25)) {
            set_progress_info('uploadgroupmemberscsv', $key, $num_lines * 10, get_string('validating', 'admin'));
        }

        // Trim non-breaking spaces -- they get left in place by File_CSV
        foreach ($line as &$field) {
            $field = preg_replace('/^(\s|\xc2\xa0)*(.*?)(\s|\xc2\xa0)*$/', '$2', $field);
        }

        $shortname = $line[$formatkeylookup['shortname']];
        $username  = $line[$formatkeylookup['username']];
        $role      = $line[$formatkeylookup['role']];

        $gid = get_field('group', 'id', 'shortname', $shortname, 'institution', $institution);
        if (!$gid) {
            $csverrors->add($i, get_string('uploadgroupmemberscsverrornosuchshortname', 'admin', $i, $shortname, $institution));
            continue;
        }

        $uid = get_field_sql('SELECT id FROM {usr} WHERE LOWER(username) = ?', array(strtolower($username)));
        if (!$uid) {
            $csverrors->add($i, get_string('uploadgroupmemberscsverrornosuchusername', 'admin', $i, $username));
            continue;
        }

        if ($institution != 'mahara' && !record_exists('usr_institution', 'usr', $uid, 'institution', $institution)) {
            $csverrors->add($i, get_string('uploadgroupmemberscsverrorusernotininstitution', 'admin', $i, $username, $institution));
            continue;
        }

        if (!in_array($role, array_keys(group_get_role_info($gid)))) {
            $csverrors->add($i, get_string('uploadgroupmemberscsverrorinvalidrole', 'admin', $i, $role));
            continue;
        }

        if (!isset($MEMBERS[$gid])) {
            $MEMBERS[$gid] = array();
        }

        if (isset($MEMBERS[$gid][$uid])) {
            $csverrors->add($i, get_string('uploadgroupmemberscsverrorduplicateusername', 'admin', $i, $shortname, $username));
            continue;
        }

        $MEMBERS[$gid][$uid] = $role;
        $GROUPS[$gid] = $shortname;

        if ($role == 'admin') {
            $hasadmin[$shortname] = 1;
        }

    }

    foreach ($GROUPS as $shortname) {
        if (!isset($hasadmin[$shortname])) {
            $csverrors->add($i, get_string('uploadgroupmemberscsverrornoadminlisted', 'admin', $i, $shortname));
        }
    }

    if ($errors = $csverrors->process()) {
        $form->set_error('file', clean_html($errors), false);
        return;
    }

    $FORMAT = $csvdata->format;
    $CSVDATA = $csvdata->data;
}

/**
 * Add the users to the system. Make sure that they have to change their
 * password on next login also.
 */
function uploadcsv_submit(Pieform $form, $values) {
    global $SESSION, $CSVDATA, $FORMAT, $UPDATES, $USER, $MEMBERS, $GROUPS;

    $formatkeylookup = array_flip($FORMAT);

    $institution = $values['institution'];

    db_begin();

    $lines_done = 0;
    $num_lines = count($CSVDATA);

    foreach ($MEMBERS as $gid => $members) {
        $updates = group_update_members($gid, $members, $lines_done, $num_lines);
        $lines_done += sizeof($members);

        if (empty($updates)) {
            unset($UPDATES[$GROUPS[$gid]]);
        }
        else {
            $UPDATES[$GROUPS[$gid]] = $updates;
            log_debug('updated group members ' . $GROUPS[$gid] . ' (' . implode(', ', array_keys((array)$updates)) . ')');
        }
    }

    db_commit();

    // TODO: Fix this to show correct info
    $SESSION->add_ok_msg(get_string('csvfileprocessedsuccessfully', 'admin'));
    if ($UPDATES) {
        $updatemsg = smarty_core();
        $updatemsg->assign('updates', $UPDATES);
        $SESSION->add_info_msg($updatemsg->fetch('admin/groups/memberscsvupdatemessage.tpl'), false);
    }
    else {
        $SESSION->add_ok_msg(get_string('numbergroupsupdated', 'admin', 0));
    }
    set_progress_done('uploadgroupmemberscsv');
    redirect('/admin/groups/uploadmemberscsv.php');
}

$uploadcsvpagedescription = get_string('uploadgroupmemberscsvpagedescription3', 'admin',
                                            get_config('wwwroot') . 'admin/groups/uploadcsv.php',
                                            get_string('uploadgroupcsv', 'admin'));

$form = pieform($form);

set_progress_done('uploadgroupmemberscsv');

$smarty = smarty(array('adminuploadcsv'));
setpageicon($smarty, 'icon-users');

$smarty->assign('uploadcsvpagedescription', $uploadcsvpagedescription);
$smarty->assign('uploadcsvform', $form);
$smarty->display('admin/groups/uploadcsv.tpl');

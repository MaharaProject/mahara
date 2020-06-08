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
define('MENUITEM', 'managegroups/uploadcsv');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('uploadgroupcsv', 'admin'));
require_once(get_config('libroot') . 'group.php');
require_once(get_config('libroot') . 'institution.php');
safe_require('artefact', 'internal');

// Turn on autodetecting of line endings, so mac newlines (\r) will work
ini_set('auto_detect_line_endings', 1);

$FORMAT = array();
$ALLOWEDKEYS = group_get_allowed_group_csv_keys();

$MANDATORYFIELDS = array(
    'shortname',
    'displayname',
    'roles',
);
$UPDATES         = array(); // During validation, remember which group already exist
$GROUPTYPES = group_get_grouptype_options();
$EDITROLES  = group_get_editroles_options();

$form = array(
    'name' => 'uploadcsv',
    'jsform' => true,
    'jssuccesscallback' => 'pmeter_success',
    'jserrorcallback' => 'pmeter_error',
    'presubmitcallback' => 'pmeter_presubmit',
    'elements' => array(
        'institution' => get_institution_selector(),
        'file' => array(
            'type' => 'file',
            'title' => get_string('csvfile', 'admin'),
            'description' => get_string('groupcsvfiledescription', 'admin'),
            'accept' => '.csv, text/csv, application/csv, text/comma-separated-values',
            'rules' => array(
                'required' => true
            )
        ),
        'updategroups' => array(
            'type' => 'switchbox',
            'class' => 'last',
            'title' => get_string('updategroups', 'admin'),
            'description' => get_string('updategroupsdescription2', 'admin'),
            'defaultvalue' => false,
        ),
        'progress_meter_token' => array(
            'type' => 'hidden',
            'value' => 'uploadgroupscsv',
            'readonly' => TRUE,
        ),
        'submit' => array(
            'type' => 'submit',
            'class' => 'btn-primary',
            'value' => get_string('uploadgroupcsv', 'admin')
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
    global $CSVDATA, $ALLOWEDKEYS, $MANDATORYFIELDS, $GROUPTYPES, $FORMAT, $USER, $UPDATES, $EDITROLES;

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
    $num_lines = count($csvdata->data);

    if (!empty($csvdata->errors['file'])) {
        $form->set_error('file', $csvdata->errors['file']);
        return;
    }

    $csverrors = new CSVErrors();

    $formatkeylookup = array_flip($csvdata->format);

    $shortnames = array();
    $displaynames = array();

    foreach ($csvdata->data as $key => $line) {
        // If headers exists, increment i = key + 2 for actual line number
        $i = ($csvgroups->get('headerExists')) ? ($key + 2) : ($key + 1);

        // In adding 5000 groups, this part was approx 10% of the wall time.
        if (!($key % 5)) {
            set_progress_info('uploadgroupscsv', $key, $num_lines * 10, get_string('validating', 'admin'));
        }

        // Trim non-breaking spaces -- they get left in place by File_CSV
        foreach ($line as &$field) {
            $field = preg_replace('/^(\s|\xc2\xa0)*(.*?)(\s|\xc2\xa0)*$/', '$2', $field);
        }

        if (count($line) != count($csvdata->format)) {
            $csverrors->add($i, get_string('uploadcsverrorwrongnumberoffields', 'admin', $i));
            continue;
        }

        $shortname = $line[$formatkeylookup['shortname']];
        $displayname = $line[$formatkeylookup['displayname']];
        $grouptype = $line[$formatkeylookup['roles']];
        $open = isset($formatkeylookup['open']) && !empty($line[$formatkeylookup['open']]);
        $controlled = isset($formatkeylookup['controlled']) && !empty($line[$formatkeylookup['controlled']]);
        $request = isset($formatkeylookup['request']) && !empty($line[$formatkeylookup['request']]);
        $submitpages = isset($formatkeylookup['submitpages']) && !empty($line[$formatkeylookup['submitpages']]);
        if (isset($formatkeylookup['editroles'])) {
            $editroles = $line[$formatkeylookup['editroles']];
        }
        $viewnotify = isset($formatkeylookup['viewnotify']) && !empty($line[$formatkeylookup['viewnotify']]);
        $category = isset($formatkeylookup['category']) && !empty($line[$formatkeylookup['category']]);

        // Make sure these three mandatory fields are populated.
        if (empty($shortname)) {
            $csverrors->add($i, get_string('uploadcsverrormandatoryfieldnotspecified', 'admin', $i, 'shortname'));
        }
        if (empty($displayname)) {
            $csverrors->add($i, get_string('uploadcsverrormandatoryfieldnotspecified', 'admin', $i, 'displayname'));
        }
        if (empty($grouptype)) {
            $csverrors->add($i, get_string('uploadcsverrormandatoryfieldnotspecified', 'admin', $i, 'roles'));
        }

        if (!preg_match('/^[a-zA-Z0-9_.-]{2,255}$/', $shortname)) {
            $csverrors->add($i, get_string('uploadgroupcsverrorinvalidshortname', 'admin', $i, $shortname));
        }

        if (isset($shortnames[$shortname])) {
            // Duplicate shortname within this file.
            $validshortname = group_generate_shortname($displayname);
            $csverrors->add($i, get_string('uploadgroupcsverrorshortnamealreadytaken1', 'admin', $i, $shortname, $validshortname));
        }
        else if (!$values['updategroups']) {
            // The groupname must be new
            if (record_exists('group', 'shortname', $shortname)) {
                $validshortname = group_generate_shortname($displayname);
                $csverrors->add($i, get_string('uploadgroupcsverrorshortnamealreadytaken1', 'admin', $i, $shortname, $validshortname));
            }
        }
        else if ($values['updategroups']) {
            // The groupname needs to exist
            if (!record_exists('group', 'shortname', $shortname)) {
                $csverrors->add($i, get_string('uploadgroupcsverrorshortnamemissing', 'admin', $i, $shortname));
            }
        }
        $shortnames[$shortname] = array(
                'shortname'   => $shortname,
                'displayname' => $displayname,
                'roles'       => $grouptype,
                'lineno'      => $i,
                'raw'         => $line,
        );

        if (isset($displaynames[strtolower($displayname)])) {
            // Duplicate displayname within this file
            $csverrors->add($i, get_string('uploadgroupcsverrordisplaynamealreadyexists', 'admin', $i, $displayname));
        }
        else if (!$values['updategroups']) {
            // The displayname must be new
            if (get_records_sql_array('SELECT id FROM {group} WHERE LOWER(TRIM(name)) = ?', array(strtolower(trim($displayname))))) {
                $csverrors->add($i, get_string('uploadgroupcsverrordisplaynamealreadyexists', 'admin', $i, $displayname));
            }
        }
        else {
            // This displayname must be new if not tied to our shortname
            if (get_records_sql_array('
                    SELECT id FROM {group}
                    WHERE LOWER(TRIM(name)) = ?
                        AND NOT (shortname = ?)',
                    array(
                        strtolower(trim($displayname)),
                        $shortname
                    ))) {
                $csverrors->add($i, get_string('uploadgroupcsverrordisplaynamealreadyexists', 'admin', $i, $displayname));
            }
        }
        $displaynames[strtolower($displayname)] = 1;

        if (!isset($GROUPTYPES[$grouptype])) {
            $csverrors->add($i, get_string('uploadgroupcsverrorinvalidgrouptype', 'admin', $i, $grouptype));
        }

        if (isset($editroles) && !isset($EDITROLES[$editroles])) {
            $csverrors->add($i, get_string('uploadgroupcsverrorinvalideditroles', 'admin', $i, $editroles));
        }

        if ($open && $controlled) {
            $csverrors->add($i, get_string('uploadgroupcsverroropencontrolled', 'admin', $i));
        }
        if ($open && $request) {
            $csverrors->add($i, get_string('uploadgroupcsverroropenrequest', 'admin', $i));
        }
        if ($viewnotify) {
            $vn = $line[$formatkeylookup['viewnotify']];
            if (!is_numeric($vn) || !($vn >= GROUP_ROLES_NONE && $vn <= GROUP_ROLES_ADMIN)) {
                $csverrors->add($i, get_string('uploadgroupcsverrorviewnotifyrequest', 'admin', $i, GROUP_ROLES_NONE, GROUP_ROLES_ADMIN));
            }
        }
        if ($category) {
            if (!get_config('allowgroupcategories')) {
                $csverrors->add($i, get_string('uploadgroupcsverrordoesnotallowgroupcategory1', 'admin', $i));
            }
            $categorytitle = $line[$formatkeylookup['category']];
            // Check if this is a valid category name
            if ($categoryid = get_field('group_category', 'id', 'title', $categorytitle)) {
                // Make sure we store the id of the category and not the category name in the DB.
                $csvdata->data[$key][$formatkeylookup['category']] = $categoryid;
            }
            else {
                $csverrors->add($i, get_string('uploadgroupcsverrorcategorydoesnotexist', 'admin', $i, $categorytitle));
            }
        }

        if ($values['updategroups']) {

            foreach ($shortnames as $shortname => $data) {

                // TODO: Any other checks we have to do for updated groups

                $UPDATES[$shortname] = 1;

            }

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
    global $SESSION, $CSVDATA, $FORMAT, $UPDATES, $USER;

    $formatkeylookup = array_flip($FORMAT);

    $institution = $values['institution'];

    if ($values['updategroups']) {
        log_info('Updating groups from the CSV file');
    }
    else {
        log_info('Inserting groups from the CSV file');
    }
    db_begin();

    $addedgroups = array();

    $key = 0;
    $num_lines = count($CSVDATA);

    foreach ($CSVDATA as $record) {

        if (!($key % 5)) {
            set_progress_info('uploadgroupscsv', $num_lines + $key * 9, $num_lines * 10, get_string('committingchanges', 'admin'));
        }
        $key++;

        $group = new stdClass();
        $group->name        = $record[$formatkeylookup['displayname']];
        $group->shortname   = $record[$formatkeylookup['shortname']];
        $group->institution = $institution;
        $group->grouptype   = $record[$formatkeylookup['roles']];

        foreach ($FORMAT as $field) {
            if ($field == 'displayname' || $field == 'shortname' || $field == 'roles') {
                continue;
            }
            if ($field == 'submitpages') {
                $group->submittableto = $record[$formatkeylookup[$field]];
                continue;
            }
            if ($field == 'quota') {
                $group->quota = get_real_size($record[$formatkeylookup[$field]]);
                continue;
            }
            if ($field == 'category') {
                if (!empty($record[$formatkeylookup[$field]])) {
                    $group->category = $record[$formatkeylookup[$field]];
                }
                continue;
            }
            $group->{$field} = $record[$formatkeylookup[$field]];
        }

        if (!$values['updategroups'] || !isset($UPDATES[$group->shortname])) {
            $group->members = array($USER->id => 'admin');
            $group->retainshortname = true;
            $group->id = group_create((array)$group);

            $addedgroups[] = $group;
            log_debug('added group ' . $group->name);
        }
        else if (isset($UPDATES[$group->shortname])) {
            $shortname = $group->shortname;
            $updates = group_update($group);

            if (empty($updates)) {
                unset($UPDATES[$shortname]);
            }
            else {
                if (isset($updates['name'])) {
                    $updates['displayname'] = $updates['name'];
                    unset($updates['name']);
                }
                $UPDATES[$shortname] = $updates;
                log_debug('updated group ' . $group->name . ' (' . implode(', ', array_keys((array)$updates)) . ')');
            }
        }

    }

    db_commit();

    $SESSION->add_ok_msg(get_string('csvfileprocessedsuccessfully', 'admin'));
    if ($UPDATES) {
        $updatemsg = smarty_core();
        $updatemsg->assign('added', count($addedgroups));
        $updatemsg->assign('updates', $UPDATES);
        $SESSION->add_info_msg($updatemsg->fetch('admin/groups/csvupdatemessage.tpl'), false);
    }
    else {
        $SESSION->add_ok_msg(get_string('numbernewgroupsadded', 'admin', count($addedgroups)));
    }

    set_progress_done('uploadgroupscsv');

    $form->reply(PIEFORM_OK, array(
       'message'  => get_string('csvfileprocessedsuccessfully', 'admin'),
       'goto'     => get_config('wwwroot') . 'admin/groups/uploadcsv.php',
    ));
}

$grouptypes = "<ul class=fieldslist>\n";
foreach (array_keys($GROUPTYPES) as $grouptype) {
    $grouptypes .= '<li>' . hsc($grouptype) . "</li>\n";
}
$grouptypes .= "<div class=cl></div></ul>\n";

$editroles = "<ul class=fieldslist>\n";
foreach (array_keys($EDITROLES) as $editrole) {
    $editroles .= '<li>' . hsc($editrole) . "</li>\n";
}
$editroles .= "<div class=cl></div></ul>\n";

$grouptypes .= get_string('uploadgroupcsveditrolesdescription', 'admin', get_help_icon('core', 'groups', 'editgroup', 'editroles'), $editroles);

$fields = "<ul class='fieldslist column-list'>\n";
foreach ($ALLOWEDKEYS as $type) {
    $helplink = '';
    if ($type == 'public' || $type == 'usersautoadded' || $type == 'hidemembers' || $type == 'viewnotify' || $type == 'category') {
        $helplink = get_help_icon('core', 'groups', 'editgroup', $type);
    }
    $fields .= '<li>' . hsc($type) . $helplink . "</li>\n";
}
$fields .= "<div class=cl></div></ul>\n";
$uploadcsvpagedescription = get_string('uploadgroupcsvpagedescription2', 'admin', get_help_icon('core', 'groups', 'editgroup', 'grouptype'), $grouptypes, $fields);

$form = pieform($form);

set_progress_done('uploadgroupscsv');

$smarty = smarty(array('adminuploadcsv'));
setpageicon($smarty, 'icon-users');
$smarty->assign('uploadcsvpagedescription', $uploadcsvpagedescription);
$smarty->assign('uploadcsvform', $form);
$smarty->display('admin/groups/uploadcsv.tpl');

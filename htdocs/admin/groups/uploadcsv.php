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
require_once('pieforms/pieform.php');
require_once(get_config('libroot') . 'group.php');
require_once(get_config('libroot') . 'institution.php');
safe_require('artefact', 'internal');
raise_memory_limit("512M");

// Turn on autodetecting of line endings, so mac newlines (\r) will work
ini_set('auto_detect_line_endings', 1);

$FORMAT = array();
$ALLOWEDKEYS = array(
    'shortname',
    'displayname',
    'description',
    'open',
    'controlled',
    'request',
    'roles',
    'public',
    'submitpages',
    'allowarchives',
    'editroles',
    'hidden',
    'hidemembers',
    'hidemembersfrommembers',
    'invitefriends',
    'suggestfriends',
);
if ($USER->get('admin')) {
    $ALLOWEDKEYS[] = 'usersautoadded';
    $ALLOWEDKEYS[] = 'quota';
}
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
            'type' => 'checkbox',
            'title' => get_string('updategroups', 'admin'),
            'description' => get_string('updategroupsdescription', 'admin'),
            'defaultvalue' => false,
        ),
        'submit' => array(
            'type' => 'submit',
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

        if (!preg_match('/^[a-zA-Z0-9_.-]{2,255}$/', $shortname)) {
            $csverrors->add($i, get_string('uploadgroupcsverrorinvalidshortname', 'admin', $i, $shortname));
        }

        if (isset($shortnames[$shortname])) {
            // Duplicate shortname within this file.
            $csverrors->add($i, get_string('uploadgroupcsverrorshortnamealreadytaken', 'admin', $i, $shortname));
        }
        else if (!$values['updategroups']) {
            // The groupname must be new
            if (record_exists('group', 'shortname', $shortname, 'institution', $institution)) {
                $csverrors->add($i, get_string('uploadgroupcsverrorshortnamealreadytaken', 'admin', $i, $shortname));
            }
        }
        else if ($values['updategroups']) {
            // The groupname needs to exist
            if (!record_exists('group', 'shortname', $shortname, 'institution', $institution)) {
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
            $csverrors->add($i, get_string('uploadgroupcsverrorsgroupnamealreadyexists', 'admin', $i, $displayname));
        }
        else if (!$values['updategroups']) {
            // The displayname must be new
            if (get_records_sql_array('SELECT id FROM {group} WHERE LOWER(TRIM(name)) = ?', array(strtolower(trim($displayname))))) {
                $csverrors->add($i, get_string('uploadgroupcsverrorgroupnamealreadyexists', 'admin', $i, $displayname));
            }
        }
        else {
            // This displayname must be new if not our shortname/institution
            if (get_records_sql_array('
                    SELECT id FROM {group}
                    WHERE LOWER(TRIM(name)) = ?
                        AND NOT (shortname = ? AND institution = ?)',
                    array(
                        strtolower(trim($displayname)),
                        $shortname,
                        $institution
                    ))) {
                $csverrors->add($i, get_string('uploadgroupcsverrorgroupnamealreadyexists', 'admin', $i, $displayname));
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

        if ($values['updategroups']) {

            foreach ($shortnames as $shortname => $data) {

                // TODO: Any other checks we have to do for updated groups

                $UPDATES[$shortname] = 1;

            }

        }
    }

    if ($errors = $csverrors->process()) {
        $form->set_error('file', clean_html($errors));
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

    foreach ($CSVDATA as $record) {

        $group = new StdClass;
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
            $group->{$field} = $record[$formatkeylookup[$field]];
        }

        if (!$values['updategroups'] || !isset($UPDATES[$group->shortname])) {
            $group->members = array($USER->id => 'admin');
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
    redirect('/admin/groups/uploadcsv.php');
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

$fields = "<ul class=fieldslist>\n";
foreach ($ALLOWEDKEYS as $type) {
    $helplink = '';
    if ($type == 'public' || $type == 'usersautoadded') {
        $helplink = get_help_icon('core', 'groups', 'editgroup', $type);
    }
    $fields .= '<li>' . hsc($type) . $helplink . "</li>\n";
}
$fields .= "<div class=cl></div></ul>\n";
$uploadcsvpagedescription = get_string('uploadgroupcsvpagedescription2', 'admin', get_help_icon('core', 'groups', 'editgroup', 'grouptype'), $grouptypes, $fields);

$form = pieform($form);

$smarty = smarty(array('adminuploadcsv'));
$smarty->assign('uploadcsvpagedescription', $uploadcsvpagedescription);
$smarty->assign('uploadcsvform', $form);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('admin/groups/uploadcsv.tpl');

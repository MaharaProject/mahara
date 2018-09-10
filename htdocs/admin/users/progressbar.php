<?php
/**
 *
 * @package    mahara
 * @subpackge  admin
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2013 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'manageinstitutions/progressbar');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'progressbar');

require(dirname(dirname(dirname(__FILE__))).'/init.php');
require_once('institution.php');
define('TITLE', get_string('progressbar', 'admin'));
define('DEFAULTPAGE', 'home');

if (!is_logged_in()) {
    throw new AccessDeniedException();
}

$institutionelement = get_institution_selector(true, false, true, false);

if (empty($institutionelement)) {
    $smarty = smarty();
    $smarty->display('admin/users/noinstitutions.tpl');
    exit;
}

$institution = param_alphanum('institution', false);
if (!$institution || !$USER->can_edit_institution($institution, true)) {
    $institution = empty($institutionelement['value']) ? $institutionelement['defaultvalue'] : $institutionelement['value'];
}
else if (!empty($institution)) {
    $institutionelement['defaultvalue'] = $institution;
}
$institutionselector = pieform(array(
    'name' => 'progressbarselect',
    'class' => 'form-inline',
    'elements' => array(
        'institution' => $institutionelement,
    )
));


// Selected artefacts that count towards completing progress bar
$recs = get_records_select_array('institution_config', 'institution=? and field like \'progressbaritem_%\'', array($institution), 'field', 'field, value');
if ($recs) {
    $selected = array();
    foreach($recs as $rec) {
        $obj = new stdClass();
        $obj->raw = $rec->field;
        $parts = explode('_', $rec->field);
        // Check format
        if (count($parts) < 3) {
            continue;
        }
        $selected[$rec->field] = $rec->value;
    }
}
else {
    $selected = array();
}

// Locked artefacts (site locked and institution locked)
$sitelocked = (array) get_column('institution_locked_profile_field', 'profilefield', 'name', 'mahara');
$instlocked = (array) get_column('institution_locked_profile_field', 'profilefield', 'name', $institution);
$locked = array_merge($sitelocked, $instlocked);

// Figure out the form elements in the configuration form
safe_require('artefact', 'internal');
$elements = array();
$possibleitems = artefact_get_progressbar_items();
$possibleitemscount = count($possibleitems);
$i = 0;
foreach($possibleitems as $plugin => $itemlist) {
    $subelements = array();
    $fscollapsed = true;
    $class = $i === $possibleitemscount - 1 ? 'last' : '';
    $i++;

    foreach($itemlist as $artefact) {
        $pbname = "progressbaritem_{$artefact->plugin}_{$artefact->name}";

        // Check if this one is a locked profile field.
        if ($plugin == 'internal' && in_array($artefact->name, $locked)) {
            $islocked = true;
        }
        else {
            $islocked = false;
        }

        // Check if this one has a default value (i.e. a value stored in the DB)
        if (!$islocked && array_key_exists($pbname, $selected)) {
            $defaultvalue = $selected[$pbname];
        }
        else {
            $defaultvalue = null;
        }

        // If there are any selected elements in this fieldset, don't pre-collapse it.
        $fscollapsed = $fscollapsed && !$defaultvalue;

        if ($artefact->iscountable) {
            $options = array(
                0 => '0',
                1 => '1',
                2 => '2',
                3 => '3',
                4 => '4',
                5 => '5',
               10 => '10',
               15 => '15',
               20 => '20',
               25 => '25',
               50 => '50',
              100 => '100',
            );
            $subelements[$pbname] = array(
                'type' => 'select',
                'title' => $artefact->title,
                'disabled' => ($artefact->active && !$islocked ? false : true),
                'defaultvalue' => $defaultvalue,
                'options' => $options
            );
        }
        else {
            $subelements[$pbname] = array(
                'type' => 'switchbox',
                'title' => $artefact->title,
                'disabled' => ($artefact->active && !$islocked ? false : true),
                'defaultvalue' => $defaultvalue,
            );
        }
    }
    $elements["fs{$plugin}"] = array(
            'type' => 'fieldset',
            'class' => $class,
            'collapsible' => true,
            'collapsed' => $fscollapsed,
            'legend' => get_string('pluginname', "artefact.{$plugin}"),
            'elements' => $subelements,
    );
}

$elements['institution'] = array(
    'type' => 'hidden',
    'value' => $institution,
);
$elements['submit'] = array(
    'type' => 'submit',
    'class' => 'btn-primary',
    'value' => get_string('submit')
);

$form = pieform(array(
    'name'        => 'progressbarform',
    'renderer'    => 'div',
    'plugintype'  => 'core',
    'pluginname'  => 'admin',
    'elements'    => $elements,
));

function progressbarform_validate(Pieform $form, $values) {
    global $SESSION, $USER;
    $inst = $values['institution'];
    if (empty($inst) || !$USER->can_edit_institution($inst)) {
        $SESSION->add_error_msg(get_string('notadminforinstitution', 'admin'));
        redirect('/admin/users/progressbar.php');
    }
}

function progressbarform_submit(Pieform $form, $values) {
    global $SESSION, $USER, $possibleitems;

    $institution = $values['institution'];

    // Pre-fetching the current settings to reduce SELECT queries
    $currentsettings = get_records_select_assoc('institution_config', 'institution=? and field like \'progressbaritem_%\'', array($institution), 'field', 'field, value');
    if (!$currentsettings) {
        $currentsettings = array();
    }

    foreach ($possibleitems as $plugin => $pluginitems) {
        foreach ($pluginitems as $artefact) {
            $itemname = "progressbaritem_{$plugin}_{$artefact->name}";

            // Format the value into an integer or 0/1
            $val = $values[$itemname];
            if ($artefact->iscountable) {
                $val = (int) $val;
            }
            else {
                $val = (int)((bool) $val);
            }

            // Update the record if it already exists, or create the record if it doesn't
            if (array_key_exists($itemname, $currentsettings)) {
                if ($val) {
                    set_field('institution_config', 'value', $val, 'institution', $institution, 'field', $itemname);
                }
                else {
                    delete_records('institution_config', 'institution', $institution, 'field', $itemname);
                }
            }
            else {
                if ($val) {
                    insert_record('institution_config', (object) array('institution'=>$institution, 'field'=>$itemname, 'value'=>$val));
                }
            }
        }
    }

    $SESSION->add_ok_msg(get_string('progressbarsaved', 'admin'));
    redirect('/admin/users/progressbar.php?institution=' . $institution);
}


$wwwroot = get_config('wwwroot');
$js = <<< EOF
jQuery(function($) {
  function reloadBar() {
      window.location.href = '{$wwwroot}admin/users/progressbar.php?institution='+$('#progressbarselect_institution').val();
  }

  $('#progressbarselect_institution').on('change', reloadBar);
});
EOF;

$smarty = smarty(array(), array(), array(), array('sideblocks' => array(progressbar_sideblock(true))));
setpageicon($smarty, 'icon-university');

$smarty->assign('progressbarform', $form);
$smarty->assign('institution', $institution);
$smarty->assign('institutionselector', $institutionselector);
$smarty->assign('enabled', get_config('showprogressbar'));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('admin/users/progressbar.tpl');

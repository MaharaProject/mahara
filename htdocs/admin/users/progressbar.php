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
require_once('pieforms/pieform.php');
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
    'elements' => array(
        'institution' => $institutionelement,
    )
));


// Selected artefacts that count towards completing progress bar
if ($data = get_field('institution_data', 'value', 'institution', $institution, 'type', 'progressbar')) {
    $selected = unserialize($data);
}
else {
    $selected = array();
}

// Locked artefacts (site locked and institution locked)
$sitelocked = (array) get_column('institution_locked_profile_field', 'profilefield', 'name', 'mahara');
$instlocked = (array) get_column('institution_locked_profile_field', 'profilefield', 'name', $institution);
$locked = array_merge($sitelocked, $instlocked);


function build_artefact_options($name, $values) {
    global $locked;
    if (is_null($name)) {
        throw new InvalidArgumentException("Artefact category is expected, but not defined.");
    }
    else {
        // Select all possible artefacts for progressbar except for those artefactst
        // that wish to opt out. Also include special case options.
        $records = PluginArtefact::get_progressbar_options($name);

        $elements = array();
        foreach ($records as $artefact) {
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
                $elements[$artefact->name] = array(
                    'type' => 'select',
                    'title' => $artefact->title,
                    'disabled' => ($artefact->active && !in_array($artefact->name, $locked) ? false : true),
                    'defaultvalue' => (array_key_exists($artefact->name, $values) && isset($values[$artefact->name])
                                       && !in_array($artefact->name, $locked) ? $values[$artefact->name] : null),
                    'options' => $options
                );
            }
            else {
                $elements[$artefact->name] = array(
                    'type' => 'checkbox',
                    'title' => $artefact->title,
                    'disabled' => ($artefact->active && !in_array($artefact->name, $locked) ? false : true),
                    'defaultvalue' => (array_key_exists($artefact->name, $values) && isset($values[$artefact->name])
                                       && !in_array($artefact->name, $locked) ? $values[$artefact->name] : null),
                );
                if (in_array($artefact->name, array('email'))) {
                    $elements[$artefact->name]['defaultvalue'] = 1;
                    $elements[$artefact->name]['readonly'] = 1;
                }
            }
        }

        return $elements;
    }
}


$form = pieform(array(
    'name'        => 'progressbarform',
    'renderer'    => 'table',
    'plugintype'  => 'core',
    'pluginname'  => 'admin',
    'elements'    => array(
        'fsinternal' => array(
            'type'        => 'fieldset',
            'collapsible' => true,
            'collapsed'   => true,
            'legend'      => get_string('profile', 'artefact.internal'),
            'elements'    => build_artefact_options('internal', $selected)
        ),
        'fsresume' => array(
            'type'        => 'fieldset',
            'collapsible' => true,
            'collapsed'   => true,
            'legend'      => get_string('resume', 'artefact.resume'),
            'elements'    => build_artefact_options('resume', $selected)
        ),
        'fsplans' => array(
            'type'        => 'fieldset',
            'collapsible' => true,
            'collapsed'   => true,
            'legend'      => get_string('plan', 'artefact.plans'),
            'elements'    => build_artefact_options('plans', $selected)
        ),
        'fsblog' => array(
            'type'        => 'fieldset',
            'collapsible' => true,
            'collapsed'   => true,
            'legend'      => get_string('blog', 'artefact.blog'),
            'elements'    => build_artefact_options('blog', $selected)
        ),
        'fsfile' => array(
            'type'        => 'fieldset',
            'collapsible' => true,
            'collapsed'   => true,
            'legend'      => get_string('file', 'artefact.file'),
            'elements'    => build_artefact_options('file', $selected)
        ),
        'fssocial' => array(
            'type'        => 'fieldset',
            'collapsible' => true,
            'collapsed'   => true,
            'legend'      => get_string('Social', 'artefact.social'),
            'elements'    => build_artefact_options('social', $selected)
        ),
        'institution' => array(
            'type' => 'hidden',
            'value' => $institution,
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('submit')
        ),
    )
));


function progressbarform_submit(Pieform $form, $values) {
    global $SESSION, $USER;

    $inst = $values['institution'];
    if (empty($inst) || !$USER->can_edit_institution($inst)) {
        $SESSION->add_error_msg(get_string('notadminforinstitution', 'admin'));
        redirect('/admin/users/progressbar.php');
    }

    unset($values['submit']);
    unset($values['sesskey']);
    unset($values['institution']);
    $where = (object) array(
        'institution' => $inst,
        'type' => 'progressbar',
    );
    $data = (object) array(
        'ctime' => db_format_timestamp(time()),
        'institution' => $inst,
        'type' => 'progressbar',
        'value' => serialize($values)
    );

    ensure_record_exists('institution_data', $where, $data);
    $SESSION->add_ok_msg(get_string('progressbarsaved', 'admin'));
    redirect('/admin/users/progressbar.php?institution=' . $inst);
}


$wwwroot = get_config('wwwroot');
$js = <<< EOF
function reloadBar() {
    window.location.href = '{$wwwroot}admin/users/progressbar.php?institution='+$('progressbarselect_institution').value;
}
addLoadEvent(function() {
    connect($('progressbarselect_institution'), 'onchange', reloadBar);
});
EOF;

$smarty = smarty();
$smarty->assign('progressbarform', $form);
$smarty->assign('institution', $institution);
$smarty->assign('institutionselector', $institutionselector);
$smarty->assign('enabled', get_config('showprogressbar'));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('admin/users/progressbar.tpl');

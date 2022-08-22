<?php
/**
 *
 * @package    mahara
 * @subpackage module-framework
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>.
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configextensions/frameworks');
define('SECTION_PLUGINTYPE', 'module');
define('SECTION_PLUGINNAME', 'framework');
define('SECTION_PAGE', 'frameworks');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

define('TITLE', get_string('frameworknav', 'module.framework'));
safe_require('module', 'framework');
if (!PluginModuleFramework::is_active()) {
    throw new AccessDeniedException(get_string('pluginnotactive1', 'error', get_string('frameworknav', 'module.framework')));
}

$upload = param_boolean('upload');
$uploadmatrix = param_boolean('uploadmatrix');

if ($uploadmatrix) {
    import_matrix_file_section();
}
else if ($upload) {
    edit_framework_section();
}
else {
    management_section();
}

// on frameworks page (rubbish bin icon)
function framework_delete_submit(Pieform $form, $values) {
    global $SESSION;

    $framework = new Framework($values['framework']);
    if (!$framework->is_in_collections()) {
        $framework->delete();
        $SESSION->add_ok_msg(get_string('itemdeleted'));
    }
    else {
        $SESSION->add_error_msg(get_string('deletefailed', 'admin'));
    }

    redirect('/module/framework/frameworks.php');
}

// edit framework on main page
function framework_config_submit(Pieform $form, $values) {
    redirect(get_config('wwwroot') . 'module/framework/frameworkmanager.php?id=' . $values['framework']);
}

/**
 * Export framework on main page
 *
 * @params Pieform $form
 * @params array $values
 */
function framework_export_submit(Pieform $form, $values) {
    global $USER;

    $values['framework'] = intval($values['framework']);
    $f = new Framework($values['framework']);
    $json = $f->to_json();
    $tmpdir = get_config('dataroot') . 'temp';
    if (!check_dir_exists($tmpdir) || !is_writable($tmpdir)) {
        throw new SystemException(get_string('cli_tmpdir_notwritable', 'admin', $tmpdir));
    }
    $filename = 'framework_' . $values['framework'] . '_' . time() . '.matrix';
    $USER->set_download_file($json, $filename, 'application/json');
    redirect(get_config('wwwroot') . 'download.php');
}

function import_matrix_file_section() {
    //show Browse for matrix file form.
    define('SUBSECTIONHEADING', get_string('upload'));
    $active_tab = 'import';
    $form = upload_matrix_form();
    $helplink = get_manual_help_link_array(array('configextensions','frameworks','uploadmatrix'));
    $manuallink = $helplink['prefix'] .'/'.  $helplink['language'] . '/' . $helplink['version'] . '/' .  $helplink['suffix'];
    $branchname = $helplink['version'] . "_STABLE";
    $smarty = smarty();
    setpageicon($smarty, 'icon-th');
    $smarty->assign('wwwroot', get_config('wwwroot'));
    $smarty->assign('SUBPAGENAV', PluginModuleFramework::submenu_items($active_tab));
    $smarty->assign('branchname', $branchname);
    $smarty->assign('manuallink', $manuallink);
    $smarty->assign('form', $form);
    $smarty->display('module:framework:uploadframework.tpl');
    exit;
}

function edit_framework_section() {
    global $SESSION;

    define('SUBSECTIONHEADING', get_string('editor', 'module.framework'));
    $active_tab = 'editor';
    $fw = array();
    array_push($fw, get_string('copyframework', 'module.framework'));
    $copy_desc = get_string('copyframeworkdescription', 'module.framework');

    $fw_edit = array();
    array_push($fw_edit, get_string('editframework', 'module.framework'));
    $content = $SESSION->get('jsoneditorcontent');
    $content = !empty($content) ? json_encode($content) : '';

    $frameworks = Framework::get_frameworks('any');
    if ($frameworks) {
        foreach ($frameworks as $framework) {
            $framework = new Framework($framework->id);
            $framework_collections_count = count($framework->get_collectionids());
            $fw[$framework->get('id')] = $framework->get('name');
            if (!$framework->get('active') && !$framework_collections_count) {
                $fw_edit[$framework->get('id')] = $framework->get('name');
            }
        }
        if (count($fw_edit) < 2) {
            $edit_desc = get_string('editdescription1', 'module.framework');
        }
        else {
            $edit_desc = get_string('editdescription2', 'module.framework');
        }
    }
    //add strings needed to var strings on editor.js
    $jsoneditor_strings = array(
        'institution' => 'mahara',
        'instdescription' => 'module.framework',
        'titledesc' => 'module.framework',
        'Framework' => 'module.framework',
        'frameworknav' => 'module.framework',
        'name' => 'mahara',
        'frameworktitle' => 'module.framework',
        'description' => 'mahara',
        'defaultdescription' => 'module.framework',
        'descriptioninfo' => 'module.framework',
        'selfassessed' => 'module.framework',
        'evidencestatuses' => 'module.framework',
        'evidencedesc' => 'module.framework',
        'Begun' => 'module.framework',
        'begun' => 'module.framework',
        'Incomplete' => 'module.framework',
        'incomplete' => 'module.framework',
        'Partialcomplete' => 'module.framework',
        'partialcomplete' => 'module.framework',
        'Completed' => 'module.framework',
        'completed' => 'module.framework',
        'standard' => 'module.framework',
        'standards' => 'module.framework',
        'Shortname' => 'admin',
        'shortnamestandard' => 'module.framework',
        'titlestandard' => 'module.framework',
        'descstandard' => 'module.framework',
        'descstandarddefault' => 'module.framework',
        'standardid' => 'module.framework',
        'standardiddesc' => 'module.framework',
        'standardelements' => 'module.framework',
        'standardelement' => 'module.framework',
        'standardelementdesc' => 'module.framework',
        'standardelementdefault' => 'module.framework',
        'standardiddesc1' => 'module.framework',
        'elementid' => 'module.framework',
        'elementiddesc' => 'module.framework',
        'invalidjsonineditor' => 'module.framework',
        'validjson' => 'module.framework',
        'moveright' => 'module.framework',
        'moverightspecific' => 'module.framework',
        'moveleft' => 'module.framework',
        'moveleftspecific' => 'module.framework',
        'deletelast' => 'module.framework',
        'collapsespecific' => 'mahara',
        'collapse' => 'module.framework',
        'collapseform' => 'module.framework',
        'add' => 'mahara',
        'addstandard' => 'module.framework',
        'addstandardelement' => 'module.framework',
        'addspecific' => 'module.framework',
        'expand' => 'module.framework',
        'expandform' => 'module.framework',
        'expandspecific' => 'mahara',
        'delete' => 'mahara',
        'deletespecific' => 'mahara',
        'deleteall' => 'module.framework',
        'deleteallspecific' => 'module.framework',
        'deleteallstandardelements' => 'module.framework',
        'selfassesseddescription' => 'module.framework',
        'standardsdescription' => 'module.framework',
        'no' => 'mahara',
        'yes' => 'mahara',
        'parentelementid' => 'module.framework',
        'parentelementdesc' => 'module.framework',
        'standardelementsdescription' => 'module.framework',
        'all' => 'module.framework',
        'copyexistingframework' => 'module.framework',
        'editsavedframework' => 'module.framework',
        'removestandardorelementconfirm' => 'module.framework'
    );

    //set up variables for correct selection of framework from dropdowns
    $inst_names = get_column('institution', 'displayname');
    $inst_stg = get_string('all', 'module.framework') . ',';
    foreach ($inst_names as $inst) {
        $inst_stg .= $inst . ',';
    }
    $inst_stg = preg_replace('/(.*)\,$/', '$1', $inst_stg);
    $inlinejs = "var inst_names='{$inst_stg}';";

    //2nd nav should be this.
    $smarty = smarty(array('js/jsoneditor/src/dist/jsoneditor.js', 'module/framework/js/editor.js'), array(), $jsoneditor_strings);
    $smarty->assign('INLINEJAVASCRIPT', $inlinejs);
    setpageicon($smarty, 'icon-th');
    $smarty->assign('wwwroot', get_config('wwwroot'));
    $smarty->assign('SUBPAGENAV', PluginModuleFramework::submenu_items($active_tab));
    $smarty->assign('fw_edit', $fw_edit);
    $smarty->assign('edit_desc', $edit_desc);
    $smarty->assign('copy_desc', $copy_desc);
    $smarty->assign('fw', $fw);
    $smarty->display('module:framework:jsoneditor.tpl');
    exit;
}

function management_section() {
    define('SUBSECTIONHEADING', get_string('Management', 'module.framework'));
    $active_tab = 'overview';
    $frameworks = Framework::get_frameworks('any');
    if ($frameworks) {
        foreach ($frameworks as $framework) {
            $fk = new Framework($framework->id);
            $framework->institution_name = ($fk->get('institution_name')) ? $fk->get('institution_name') : '';
            if ($fk->get('active')) {
                $framework->active = array(
                    'title' => 'Enabled',
                    'classes' => 'icon icon-lg icon-check text-success displayicon'
                );
            }
            else {
                $framework->active = array(
                    'title' => 'Disabled',
                    'classes' => 'icon icon-lg icon-times text-danger displayicon'
                );
            }
            $framework_collections_count = count($fk->get_collectionids());
            $framework->collections = $framework_collections_count;
            $framework->delete = false;
            if (empty($framework_collections_count)) {
                $framework->delete = pieform(
                    array(
                        'name' => 'framework_delete_' . $framework->id,
                        'successcallback' => 'framework_delete_submit',
                        'renderer' => 'div',
                        'class' => 'btn-group-last last form-as-button',
                        'elements' => array(
                            'submit' => array(
                                'type'         => 'button',
                                'class'        => 'btn-secondary btn-sm button',
                                'usebuttontag' => true,
                                'value'        => '<span class="icon icon-trash-alt text-danger" role="presentation" aria-hidden="true"></span><span class="visually-hidden">'. get_string('delete') . '</span>',
                                'confirm'      => get_string('confirmdeletemenuitem', 'admin'),
                            ),
                            'framework'  => array(
                                'type'         => 'hidden',
                                'value'        => $framework->id,
                            )
                        ),
                    )
                );
            }
            $framework->config = pieform(
                array(
                    'name' => 'framework_config_' . $framework->id,
                    'successcallback' => 'framework_config_submit',
                    'renderer' => 'div',
                    'class' => 'btn-group-first first form-as-button',
                    'elements' => array(
                        'submit' => array(
                            'type'         => 'button',
                            'class'        => 'btn-secondary btn-sm button',
                            'usebuttontag' => true,
                            'value'        => '<span class="icon icon-cog" role="presentation" aria-hidden="true"></span><span class="visually-hidden">'. get_string('edit') . '</span>',
                        ),
                        'framework'  => array(
                            'type'         => 'hidden',
                            'value'        => $framework->id,
                        )
                    ),
                )
            );
            $framework->export = pieform(
                array(
                    'name' => 'framework_export_' . $framework->id,
                    'successcallback' => 'framework_export_submit',
                    'renderer' => 'div',
                    'class' => (empty($framework_collections_count) ? 'btn-group' : 'btn-group-last last form-as-button'),
                    'elements' => array(
                        'submit' => array(
                            'type'         => 'button',
                            'class'        => 'btn-secondary btn-sm button',
                            'usebuttontag' => true,
                            'value'        => '<span class="icon icon-download" role="presentation" aria-hidden="true"></span><span class="visually-hidden">'. get_string('Download', 'admin') . '</span>',
                        ),
                        'framework'  => array(
                            'type'         => 'hidden',
                            'value'        => $framework->id,
                        )
                    ),
                )
            );
        }
    }

    $smarty = smarty();
    setpageicon($smarty, 'icon-th');
    $smarty->assign('frameworks', $frameworks);
    $smarty->assign('SUBPAGENAV', PluginModuleFramework::submenu_items($active_tab));
    $smarty->assign('wwwroot', get_config('wwwroot'));
    $smarty->display('module:framework:frameworks.tpl');
}

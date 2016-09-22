<?php
/**
 *
 * @package    mahara
 * @subpackage module-framework
 * @author     Catalyst IT Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
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

define('TITLE', get_string('Framework', 'module.framework'));
safe_require('module', 'framework');
$upload = param_boolean('upload');

if ($upload) {
    define('SUBSECTIONHEADING', get_string('upload'));
    $form = upload_matrix_form();
    $smarty = smarty();
    setpageicon($smarty, 'icon-th');
    $smarty->assign('wwwroot', get_config('wwwroot'));
    $smarty->assign('form', $form);
    $smarty->display('module:framework:uploadframework.tpl');
    exit;
}

$frameworks = Framework::get_frameworks('any');
if ($frameworks) {
    foreach ($frameworks as $framework) {
        $framework->activationswitch = pieform(
            array(
                'name' => 'framework' . $framework->id,
                'successcallback' => 'framework_update_submit',
                'renderer' => 'div',
                'class' => 'form-inline pull-left framework',
                'jsform' => false,
                'checkdirtychange' => false,
                'elements' => array(
                    'plugintype' => array(
                        'type' => 'hidden',
                        'value' => 'module'
                    ),
                    'pluginname' => array(
                        'type' => 'hidden',
                        'value' => 'framework'
                    ),
                    'id' => array(
                        'type' => 'hidden',
                        'value' => $framework->id
                    ),
                    'enabled' => array(
                        'type' => 'switchbox',
                        'value' => $framework->active,
                    ),
                ),
            )
        );
        $fk = new Framework($framework->id);
        $framework->collections = count($fk->get_collectionids());
        $framework->delete = false;
        if (empty($framework->collections)) {
            $framework->delete = pieform(
                array(
                    'name' => 'framework_delete_' . $framework->id,
                    'successcallback' => 'framework_delete_submit',
                    'renderer' => 'div',
                    'class' => 'form-inline form-as-button pull-right framework',
                    'elements' => array(
                        'submit' => array(
                            'type'         => 'button',
                            'class'        => 'btn-default btn-sm',
                            'usebuttontag' => true,
                            'value'        => '<span class="icon icon-trash icon-lg text-danger" role="presentation" aria-hidden="true"></span><span class="sr-only">'. get_string('delete') . '</span>',
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
    }
}

function framework_update_submit(Pieform $form, $values) {
    global $SESSION;
    // Should not normally get here as the form has no submit button and is updated via ajax/frameworks.json.php
    // but in case one does
    if (!is_plugin_active('framework', 'module')) {
        $SESSION->add_error_msg(get_string('needtoactivate', 'module.framework'));
    }
    if (!$USER->get('admin')) {
        $SESSION->add_error_msg(get_string('accessdenied'));
    }
    else {
        $id = $values['id'];
        $enabledval = param_alphanum('enabled', false);
        $enabled = ($enabledval == 'on' || $enabledval == 1) ? 1 : 0;
        // need to update the active status
        if (set_field('framework', 'active', $enabled, 'id', $id)) {
            $SESSION->add_ok_msg(get_string('frameworkupdated', 'module.framework'));
        }
    }

    redirect('/module/framework/frameworks.php');
}

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

$smarty = smarty();
setpageicon($smarty, 'icon-th');
$smarty->assign('frameworks', $frameworks);
$smarty->assign('wwwroot', get_config('wwwroot'));
$smarty->display('module:framework:frameworks.tpl');

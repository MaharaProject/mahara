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
        $fk = new Framework($framework->id);
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
        $framework->collections = count($fk->get_collectionids());
        $framework->delete = false;
        if (empty($framework->collections)) {
            $framework->delete = pieform(
                array(
                    'name' => 'framework_delete_' . $framework->id,
                    'successcallback' => 'framework_delete_submit',
                    'renderer' => 'div',
                    'class' => 'form-inline pull-right framework',
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
        $framework->config = pieform(
            array(
                'name' => 'framework_config_' . $framework->id,
                'successcallback' => 'framework_config_submit',
                'renderer' => 'div',
                'class' => 'form-inline pull-right framework',
                'elements' => array(
                    'submit' => array(
                        'type'         => 'button',
                        'class'        => 'btn-default btn-sm',
                        'usebuttontag' => true,
                        'value'        => '<span class="icon icon-cog icon-lg" role="presentation" aria-hidden="true"></span><span class="sr-only">'. get_string('delete') . '</span>',

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

function framework_config_submit(Pieform $form, $values) {
    redirect(get_config('wwwroot') . 'module/framework/frameworkmanager.php?id=' . $values['framework']);
}

$smarty = smarty();
setpageicon($smarty, 'icon-th');
$smarty->assign('frameworks', $frameworks);
$smarty->assign('wwwroot', get_config('wwwroot'));
$smarty->display('module:framework:frameworks.tpl');

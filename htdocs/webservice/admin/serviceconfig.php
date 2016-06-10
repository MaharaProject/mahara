<?php
/**
 *
 * @package    mahara
 * @subpackage auth-webservice
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'webservices');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('pluginadmin', 'admin'));

$service  = param_integer('service', 0);
$dbservice = get_record('external_services', 'id', $service);
if (empty($dbservice)) {
    $SESSION->add_error_msg(get_string('invalidservice', 'auth.webservice'));
    redirect('/webservice/admin/index.php?open=webservices_function_groups');
}
$enabled = $dbservice->enabled;
$restrictedusers = ($dbservice->restrictedusers <= 0 ? 0 : 1);
$tokenusers = ($dbservice->tokenusers <= 0 ? 0 : 1);

$functions = array(
    'elements' => array(
        'enabledname' => array(
            'title' => ' ',
            'class' => 'heading',
            'type'  => 'html',
            'value' => get_string('enabled'),
        ),
        'classname' => array(
            'title' => ' ',
            'class' => 'heading',
            'type'  => 'html',
            'value' => get_string('classname', 'auth.webservice'),
        ),
        'methodname' => array(
            'title' => ' ',
            'class' => 'heading',
            'type'  => 'html',
            'value' => get_string('methodname', 'auth.webservice'),
        ),
    ),
);

$dbfunctions = get_records_array('external_functions', null, null, 'name');
foreach ($dbfunctions as $function) {
    $sfexists = record_exists('external_services_functions', 'externalserviceid', $dbservice->id, 'functionname', $function->name);
    $functions['elements']['id' . $function->id . '_enabled'] = array(
        'defaultvalue' => ($sfexists ? 'checked' : ''),
        'type'         => 'switchbox',
        'disabled'     => false,
        'title'        => $function->name,
    );

    $functions['elements']['id' . $function->id . '_class'] = array(
        'value'        =>  $function->classname,
        'type'         => 'html',
        'title'        => $function->name,
    );

    $functions['elements']['id' . $function->id . '_method'] = array(
        'value'        =>  '<a class="dialogue" href="' . get_config('wwwroot') . 'webservice/wsdoc.php?id=' . $function->id . '">' . $function->methodname . '</a>',
        'type'         => 'html',
        'title'        => $function->name,
    );
}

$functions['elements']['submit'] = array(
            'type'  => 'submitcancel',
            'class' => 'btn-primary submitcancel',
            'value' => array(get_string('save'), get_string('back')),
            'goto'  => get_config('wwwroot') . 'webservice/admin/index.php?open=webservices_function_groups',
        );

$elements = array(
    'service' => array(
        'type' => 'hidden',
        'value' => $dbservice->id
    ),
    // fieldset of master switch
    'webservicesmaster' => array(
        'type' => 'fieldset',
        'legend' => get_string('enableservice', 'auth.webservice'),
        'elements' =>  array(
            'enabled' => array(
                'type' => 'switchbox',
                'defaultvalue' => $enabled,
                'on_label' => get_string('enabled'),
                'off_label' => get_string('disabled'),
                'wrapperclass' => 'switch-wrapper-inline',
                'labelhtml' => '<span class="pseudolabel">' . get_string('servicename', 'auth.webservice') .'</span>',
            ),
            'restrictedusers' => array(
                'type' => 'switchbox',
                'defaultvalue' => $restrictedusers,
                'on_label' => get_string('usersonly', 'auth.webservice'),
                'off_label' => get_string('tokensonly', 'auth.webservice'),
                'wrapperclass' => 'switch-wrapper-inline',
                'labelhtml' => '<span class="pseudolabel">' . get_string('restrictedusers', 'auth.webservice') .'</span>',
            ),
            'tokenusers' => array(
                'type' => 'switchbox',
                'defaultvalue' => $tokenusers,
                'on_label' => get_string('enabled'),
                'off_label' => get_string('disabled'),
                'wrapperclass' => 'switch-wrapper-inline',
                'labelhtml' => '<span class="pseudolabel">' . get_string('fortokenusers', 'auth.webservice') .'</span>',
            ),
        ),
        'collapsible' => true,
        'collapsed'   => false,
    ),
    // fieldset for managing service function list
    'functions' => array(
        'type' => 'fieldset',
        'class' => 'last',
        'renderer' => 'multicolumnfieldsettable',
        'columns' => array('enabledname', 'classname', 'methodname'),
        'footer' => array('submit'),
        'legend' => get_string('servicefunctionlist', 'auth.webservice'),
        'comment' => get_string('sfldescription', 'auth.webservice'),
        'elements' => $functions['elements'],
        'collapsible' => true,
        'collapsed'   => false,
    ),
);

$form = array(
    'renderer' => 'div',
    'type' => 'div',
    'id' => 'maintable',
    'elementclasses'  => false,
    'elements' => $elements,
    'jsform' => false,
);

$heading = get_string('servicegroup', 'auth.webservice', $dbservice->name);
$form['name'] = 'serviceconfig';
$form['successcallback'] = 'serviceconfig_submit';
$form = pieform($form);
$inlinejs = <<<EOF
<script type="application/javascript">
jQuery(function($) {
    $(".dialogue").click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        // fetch the info for the method
        $.ajax({
            url: e.currentTarget.href + '&dialog=1',
        }).done(function(data) {
            // close any open dialogs
            $(".js-page-modal .modal-body").html(data).css("max-height", "80vh");
            $(".js-page-modal .modal-dialog").css("width", "80vw");
            $(".js-page-modal").modal('show');
        });
    });
});
</script>
EOF;
$headers[] = $inlinejs;
$smarty = smarty(array(), $headers, array('Close' => 'mahara', 'wsdoc' => 'auth.webservice'));
safe_require('auth', 'webservice');
$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', $heading);
$smarty->display('form.tpl');

function serviceconfig_submit(Pieform $form, $values) {
    global $SESSION, $service, $dbservice;

    if (isset($values['enabled'])) {
        $enabled = $values['enabled'] ? 1 : 0;
        $dbservice->enabled = $enabled;
        update_record('external_services', $dbservice);
    }
    if (isset($values['tokenusers'])) {
        $tokenusers = $values['tokenusers'] ? 1 : 0;
        $dbservice->tokenusers = $tokenusers;
        update_record('external_services', $dbservice);
    }
    if (isset($values['restrictedusers'])) {
        // flip flop
        $restrict = ($values['restrictedusers'] <= 0 ? 0 : 1);
        if ($restrict) {
            // must not disable token users
            $cnt = count_records('external_tokens', 'externalserviceid', $service);
            if ($cnt > 0) {
                $SESSION->add_error_msg(get_string('existingtokens', 'auth.webservice'));
                redirect('/webservice/admin/serviceconfig.php?service=' . $service);;
            }
        }
        else {
            // must not disable auth users
            $cnt = count_records('external_services_users', 'externalserviceid', $service);
            if ($cnt > 0) {
                $SESSION->add_error_msg(get_string('existingserviceusers', 'auth.webservice'));
                redirect('/webservice/admin/serviceconfig.php?service=' . $service);;
            }
        }
        $dbservice->restrictedusers = $restrict;
        update_record('external_services', $dbservice);
    }

    foreach (array_keys($values) as $key) {
        if (preg_match('/^id(\d+)\_enabled$/', $key, $matches)) {
            $function = $matches[1];
            $dbfunction = get_record('external_functions', 'id', $function);
            if (empty($dbfunction)) {
                $SESSION->add_error_msg(get_string('invalidinput', 'auth.webservice'));
                redirect('/webservice/admin/serviceconfig.php?service=' . $service);
            }
            $service_function = record_exists('external_services_functions', 'externalserviceid', $service, 'functionname',$dbfunction->name);
            // record should exist - so create if necessary
            if ($values[$key]) {
                if (!$service_function) {
                    $service_function = array('externalserviceid' => $service, 'functionname' => $dbfunction->name);
                    insert_record('external_services_functions', $service_function);
                    $dbservice->mtime = db_format_timestamp(time());
                    update_record('external_services', $dbservice);
                }
            }
            else {
                // disabled - record should not exist
                if ($service_function) {
                    delete_records('external_services_functions', 'externalserviceid', $service, 'functionname',$dbfunction->name);
                    $dbservice->mtime = db_format_timestamp(time());
                    update_record('external_services', $dbservice);
                }
            }
        }
    }
    $SESSION->add_ok_msg(get_string('configsaved', 'auth.webservice'));
    redirect('/webservice/admin/serviceconfig.php?service=' . $service);
}

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
$dbservice = get_record(
    'external_services',
    'id', $service,
    null, null, null, null,
    'id, name, shortname, component, restrictedusers, tokenusers, enabled'
);
if (empty($dbservice)) {
    $SESSION->add_error_msg(get_string('invalidservice', 'auth.webservice'));
    redirect('/webservice/admin/index.php?open=webservices_function_groups');
}
$enabled = $dbservice->enabled;
$restrictedusers = ($dbservice->restrictedusers <= 0 ? 0 : 1);
$tokenusers = ($dbservice->tokenusers <= 0 ? 0 : 1);
$ispluginservice = ('' !== $dbservice->component);

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

if (!$ispluginservice) {
    // Custom service - let the user add/remove functions
    $dbfunctions = get_records_array('external_functions', null, null, 'name');
}
else {
    // In a non-custom service, the list of functions can't be changed. So
    // only display the ones that are actually included in this service.
    $dbfunctions = get_records_sql_array(
        'SELECT ef.*
        FROM
            {external_services_functions} esf
            INNER JOIN {external_functions} ef
                ON esf.functionname = ef.name
        WHERE esf.externalserviceid = ?
        ORDER BY ef.name',
        array($dbservice->id)
    );
}
foreach ($dbfunctions as $function) {
    $sfexists = record_exists('external_services_functions', 'externalserviceid', $dbservice->id, 'functionname', $function->name);
    $functions['elements']['id' . $function->id . '_enabled'] = array(
        'defaultvalue' => ($sfexists ? 'checked' : ''),
        'type'         => 'switchbox',
        'disabled'     => $ispluginservice,
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
$heading = get_string('servicegroup', 'auth.webservice', $dbservice->name);

$elements = array(
    'service' => array(
        'type' => 'hidden',
        'value' => $dbservice->id
    ),
    // fieldset of name & shortname
    'namefieldset' => array(
        'legend' => $heading,
        'type' => 'fieldset',
        'elements' => array(
            'name' => array(
                'type'         => 'text',
                'title'        => get_string('servicenamelabel', 'auth.webservice'),
                'rules'        => array(
                    'required' => !$ispluginservice,
                    'maxlength' => 200
                ),
                'description'  => get_string('servicenamedesc', 'auth.webservice'),
                'defaultvalue' => $dbservice->name,
                'disabled' => $ispluginservice,
            ),
            'shortname' => array(
                'type' => 'text',
                'title' => get_string('serviceshortnamelabel', 'auth.webservice'),
                'rules' => array(
                    'maxlength' => 200
                ),
                'description' => get_string('serviceshortnamedesc', 'auth.webservice'),
                'defaultvalue' => $dbservice->shortname,
                'disabled' => $ispluginservice,
            ),
        ),
        'collapsible' => true,
        'collapsed' => false,
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

if ($ispluginservice) {
    $elements['namefieldset']['elements']['component'] = array(
        'type' => 'html',
        'value' => get_string('servicecomponentnote', 'auth.webservice', $dbservice->component)
    );
}

$form = array(
    'renderer' => 'div',
    'type' => 'div',
    'id' => 'maintable',
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

function serviceconfig_validate(Pieform $form, $values) {
    // Some fields can't be edited on a plugin's service. So don't bother validating
    // any inputs relating to them.
    $ispluginservice = ('' !== get_field('external_services', 'component', 'id', $values['service']));
    if (!$ispluginservice) {

        // name must be unique
        if (
            isset($values['name'])
            && record_exists_select(
                'external_services',
                'name = ? AND id <> ?',
                array($values['name'], $values['service'])
            )
        ) {
            $form->set_error('name', get_string('servicenamemustbeunique', 'auth.webservice'));
        }

        // component and shortname together must be unique
        if (
            isset($values['shortname'])
            && $values['shortname'] !== ''
            && record_exists_select(
                'external_services',
                'shortname = ? AND (component = \'\' OR component IS NULL) AND id <> ?',
                array($values['shortname'], $values['service'])
            )
        ) {
            $form->set_error('shortname', get_string('serviceshortnamemustbeunique', 'auth.webservice'));
        }
    }
}

/**
 * HACK: This function was not written following normal Mahara coding standards.
 * The following things should be cleaned up at some point:
 *
 * 1. It mixes submission and validation (throwing exceptions in the case of
 * a validation failure). The validation should be moved to a separate
 * Pieforms validation handler.
 *
 * 2. Remove the global $service. Instead, use $values['service'].
 *
 * 3. Use a clean "$todb" object to update only the changed fields in the
 * database (instead of using the global $dbservice, which is a whole-row record.
 *
 * 4. Remove the global $dbservice once it's not needed.
 *
 * 5. Instead of the backwards check for form fields that look like function
 * names and then checking the DB to see if each one exists; just do one
 * query to get all the functions, then check for a form field that looks like
 * each one.
 *
 * 6. Do one "update_record()" call at the end of the function, instead
 * of a separate one for each form field.
 *
 * @param Pieform $form
 * @param array $values
 */
function serviceconfig_submit(Pieform $form, $values) {
    global $SESSION, $service, $dbservice;

    // Indicates whether this service was generated by a plugin (in which case
    // the admin can change access to it, but can't change its function list
    // or its name & shortname
    $ispluginservice = ('' !== $dbservice->component);

    // Can't edit name of plugin-provided services
    if (!$ispluginservice && isset($values['name'])) {
        $dbservice->name = $values['name'];
        update_record('external_services', $dbservice);
    }

    // Can't edit shortname of plugin-provided services
    if (!$ispluginservice && isset($values['shortname'])) {
        $dbservice->shortname = $values['shortname'];
        update_record('external_services', $dbservice);
    }

    if (isset($values['enabled'])) {
        $enabled = $values['enabled'] ? 1 : 0;
        $dbservice->enabled = $enabled;
        update_record('external_services', $dbservice);
    }
    $tokenwarning = false;
    if (isset($values['tokenusers'])) {
        $tokenusers = $values['tokenusers'] ? 1 : 0;
        // We may need to issue a warning that this will cut off some users' access
        if ($dbservice->tokenusers && !$tokenusers) {
            $tokenwarning = true;
        }
        $dbservice->tokenusers = $tokenusers;
        update_record('external_services', $dbservice);
    }
    $restrictwarning = false;
    if (isset($values['restrictedusers'])) {
        // flip flop
        $restrict = ($values['restrictedusers'] <= 0 ? 0 : 1);
        // We may need to issue a warning that this will cut off some users' access
        if (!$dbservice->restrictedusers && $restrict) {
            $restrictwarning = true;
        }
        $dbservice->restrictedusers = $restrict;
        update_record('external_services', $dbservice);
    }

    if ($tokenwarning || $restrictwarning) {
        // Warn if disabling token users
        $cnt = count_records('external_tokens', 'externalserviceid', $service);
        if ($cnt > 0) {
            if ($tokenwarning) {
                $SESSION->add_error_msg(get_string('tokenuserswarning', 'auth.webservice', get_string('fortokenusers', 'auth.webservice')));
            }
            if ($restrictwarning) {
                $SESSION->add_error_msg(get_string('restricteduserswarning', 'auth.webservice', get_string('restrictedusers', 'auth.webservice')));
            }
        }
    }

    // Can't add/remove functions from a plugin's service
    if (!$ispluginservice) {
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
    }
    $SESSION->add_ok_msg(get_string('configsaved', 'auth.webservice'));
    redirect('/webservice/admin/serviceconfig.php?service=' . $service);
}

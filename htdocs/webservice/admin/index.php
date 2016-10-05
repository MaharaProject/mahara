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
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('MENUITEM', 'webservices/config');

$path = get_config('docroot') . 'lib/zend';
set_include_path($path . PATH_SEPARATOR . get_include_path());
require_once(get_config('docroot').'webservice/lib.php');
require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
define('TITLE', get_string('webservices_title', 'auth.webservice'));

safe_require('auth', 'webservice');
$heading = get_string('webservices_title', 'auth.webservice');
$form = webservice_admin_main_form();
$serviceenabled = get_string('webservicesenabled', 'auth.webservice');
$servicenotenabled = get_string('webservicesnotenabled', 'auth.webservice');

$inlinejs = <<<JS
    jQuery(function ($) {
        var webservicesenabled = '$serviceenabled';
        var webservicesnotenabled = '$servicenotenabled';

        if (!$('#needprotocolsreq').length) {
            $('#webservice_requester_enabled_form_enabled_container').append('<span id="needprotocolsreq" class="form-message-inline"></span>');
        }

        if (!$('#needprotocolspro').length) {
            $('#webservice_provider_enabled_form_enabled_container').append('<span id="needprotocolspro" class="form-message-inline"></span>');
        }

        function update_warning_message(){
          var target = $('.webservice-provider-protocol-form').find('input:checkbox');
          var masterreq = $('#webservice_requester_enabled_form').find('input:checkbox');//requester
          var masterpro = $('#webservice_provider_enabled_form').find('input:checkbox');//provider
          if ($(masterreq).filter(':checked').length) {
              if (target.filter(':checked').length === 0) {
                  $('#needprotocolsreq').removeClass('text-success').addClass('text-danger').text(webservicesnotenabled);
              }
              else {
                  $('#needprotocolsreq').removeClass('text-danger').addClass('text-success').text(webservicesenabled);
              }
          }
          else {
              $('#needprotocolsreq').removeClass('text-danger').removeClass('text-success').text('');
          }
          if ($(masterpro).filter(':checked').length) {
              if (target.filter(':checked').length === 0) {
                  $('#needprotocolspro').removeClass('text-success').addClass('text-danger').text(webservicesnotenabled);
              }
              else {
                  $('#needprotocolspro').removeClass('text-danger').addClass('text-success').text(webservicesenabled);
              }
          }
          else {
              $('#needprotocolspro').removeClass('text-danger').removeClass('text-success').text('');
          }
        }

        function save_protos_switch(name) {
            // at least one of the master switches is on
            update_warning_message()
            // Save new state
            if (!$('#ajax_' + name).length) {
                $('#webservice_provider_protocols_' + name).append('<input id="ajax_' + name + '" type="hidden" name="ajax" value="1">');
            }
            $.post('index.php', $('#webservice_provider_protocols_' + name).serialize());
        }

        // saving the form when switching the protocols
        $('#webservice_provider_protocols_soap_enabled').change(function() {
            save_protos_switch('soap');
        });
        $('#webservice_provider_protocols_xmlrpc_enabled').change(function() {
            save_protos_switch('xmlrpc');
        });
        $('#webservice_provider_protocols_rest_enabled').change(function() {
            save_protos_switch('rest');
        });
        $('#webservice_provider_protocols_oauth_enabled').change(function() {
            save_protos_switch('oauth');
        });

        $('#webservice_provider_enabled_form_enabled').on('change', function(){
            update_webservice_status();
            // save master switch form
            $('#webservice_provider_enabled_form').append('<input type="hidden" name="ajax" value="1">');
            $.post('index.php', $('#webservice_provider_enabled_form').serialize());
        });
        $('#webservice_requester_enabled_form_enabled').on('change', function() {
            update_webservice_status();
            // save master connection switch form
            $('#webservice_requester_enabled_form').append('<input type="hidden" name="ajax" value="1">');
            $.post('index.php', $('#webservice_requester_enabled_form').serialize());
        });

        function update_webservice_status() {
            var target = $('.webservice-provider-protocol-form').find('input:checkbox');
            if ($('#webservice_requester_enabled_form_enabled').is(':checked') ||
                $('#webservice_provider_enabled_form_enabled').is(':checked')) {
                // enable all protocols
                target.prop('disabled', false);
                // alert user to switch protocols on if none are active
                update_warning_message();
            }
            else {
                // turn all protocols off
                target.prop('checked', false);
                // if main switch is off, remove message
                $('#webservice_provider_enabled_form_pseudofieldset .form-message-inline').text('');
                // disable all protocols
                target.prop('disabled', true);
                // remove the needprotocols error message
                $('#needprotocolsreq').removeClass('text-danger').text('');
                $('#needprotocolspro').removeClass('text-danger').text('');
            }
        }
        update_webservice_status();
    });
JS;

$smarty = smarty();
setpageicon($smarty, 'icon-puzzle-piece');

$smarty->assign('form', $form);
$smarty->assign('opened', param_alphanumext('open', ''));
$smarty->assign('PAGEHEADING', $heading);
$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->assign('pagedescription', get_string('webservicesconfigdesc', 'auth.webservice'));
$smarty->display('auth:webservice:configform.tpl');

/**
 *  Custom webservices config page
 *  - activate/deactivate webservices comletely
 *  - activate/deactivat protocols - SOAP/XML-RPC/REST
 *  - manage service clusters
 *  - manage users and access tokens
 *
 *  @return pieforms $element array
 */
function webservice_admin_main_form() {

    $protosform = array(
        'name'            => 'webservice_provider_protocols_form',
        'elements'        => webservice_provider_protocols_form(),
    );
    $protos = pieform_instance($protosform);

    // certificate values from MNet
    $openssl = OpenSslRepo::singleton();

    $elements = array(
        // fieldset of master switch
        'webservicesmaster' => array(
            'name' => 'webservice_master_switches',
            'type' => 'fieldset',
            'legend' => get_string('webservice_master_switches', 'auth.webservice'),
            'elements' =>  array(
                'webservice_requester_enabled_label' =>  array(
                    'type' => 'html',
                    'value' => '<h4>' . get_string('webservice_requester_enabled_label', 'auth.webservice') . '</h4>',
                ),
                'webservice_requester_enabled_fieldset' => webservice_requester_enabled_form(),
                'webservice_provider_enabled_label' =>  array(
                    'type' => 'html',
                    'value' => '<h4>' . get_string('webservice_provider_enabled_label', 'auth.webservice') . '</h4>',
                ),
                'webservice_provider_enabled_fieldset' => webservice_provider_enabled_form(),
                'protos_help' =>  array(
                    'type' => 'html',
                    'value' => '<div><p>' . get_string('manage_protocols1', 'auth.webservice') . '</p></div>',
                ),
                'webservice_provider_protocols_form' =>  array(
                    'type' => 'html',
                    'value' => $protos->build(false),
                )
            ),
            'collapsible' => true,
            'collapsed'   => true,
        ),

        // System Certificates
        'certificates' => array(
            'name' => 'activate_webservices_networking',
            'type' => 'fieldset',
            'legend' => get_string('certificates', 'auth.webservice'),
            'elements' =>  array(
                'protos_help' =>  array(
                    'type' => 'html',
                    'value' => '<div><p>' . get_string('manage_certificates', 'auth.webservice', get_config('wwwroot') . 'admin/site/networking.php') . '</p></div>',
                ),

                'pubkey' => array(
                    'type'         => 'html',
                    'value'        => '<h3 class="title">' . get_string('publickey','admin') . '</h3>'
                                      . '<div class="detail">' . get_string('publickeydescription2', 'admin', 365) . '</div>'
                                      . '<pre style="font-size: 0.7em; white-space: pre;">' . $openssl->certificate . '</pre>'
                ),
                'sha1fingerprint' => array(
                    'type'         => 'html',
                    'value'        => '<div><p>' . get_string('sha1fingerprint', 'auth.webservice', $openssl->sha1_fingerprint) . '</p></div>',
                ),
                'md5fingerprint' => array(
                    'type'         => 'html',
                    'value'        => '<div><p>' . get_string('md5fingerprint', 'auth.webservice', $openssl->md5_fingerprint) . '</p></div>',
                ),
                'expires' => array(
                    'type'         => 'html',
                    'value'        => '<div><p>' . get_string('publickeyexpireson','auth.webservice', format_date($openssl->expires)) . '</p></div>'
                ),
            ),
            'collapsible' => true,
            'collapsed'   => true,
        ),

        // fieldset for managing service function groups
        'servicefunctiongroups' => array(
            'name' => 'webservices_function_groups',
            'type' => 'fieldset',
            'legend' => get_string('servicefunctiongroups', 'auth.webservice'),
            'elements' => array(
                'sfgdescription' => array(
                    'value' => '<div><p>' . get_string('sfgdescription', 'auth.webservice') . '</p></div>'
                ),
                'webservicesservicecontainer' => array(
                    'type'         => 'html',
                    'value' => webservice_function_groups_form(),
                )
            ),
            'collapsible' => true,
            'collapsed'   => true,
        ),


        // fieldset for managing service tokens
        'servicetokens' => array(
            'name' => 'webservices_token',
            'type' => 'fieldset',
            'legend' => get_string('servicetokens', 'auth.webservice'),
            'elements' => array(
                'stdescription' => array(
                    'value' => '<div><p>' . get_string('stdescription', 'auth.webservice') . '</p></div>'
                ),
                'webservicestokenscontainer' => array(
                    'type'         => 'html',
                    'value' => webservice_tokens_form()
                )
            ),
            'collapsible' => true,
            'collapsed'   => false,
        ),

        // fieldset for managing service tokens
        'serviceusers' => array(
            'name' => 'webservices_user',
            'type' => 'fieldset',
            'legend' => get_string('manageserviceusers', 'auth.webservice'),
            'elements' => array(
                'sudescription' => array(
                    'value' => '<div><p>' . get_string('sudescription', 'auth.webservice') . '</p></div>'
                ),
                'webservicesuserscontainer' => array(
                    'type'         => 'html',
                    'value' => webservice_users_form(),
                )
            ),
            'collapsible' => true,
            'collapsed'   => false,
        ),
    );

    $form = array(
        'renderer' => 'div',
        'type' => 'div',
        'elements' => $elements,
    );

    return $form;
}


/**
 * Form layout for the master switch to control whether this site can
 * use webservices provided by an external server.
 *
 * @return pieforms $element array
 */
function webservice_requester_enabled_form() {
    // enable/disable webservices completely
    $enabled = (get_config('webservice_requester_enabled') || 0);
    $element = array(
        'type'  => 'html',
        'value' =>
            pieform(
                array(
                    'name' => 'webservice_requester_enabled_form',
                    'elementclasses' => false,
                    'class'           => 'form-switch form-group',
                    'successcallback' => 'webservice_requester_enabled_submit',
                    'renderer' => 'div',
                    'jsform' => false,
                    'checkdirtychange' => false,
                    'elements' => array(
                        'plugintype' => array('type' => 'hidden', 'value' => 'auth'),
                        'type' => array('type' => 'hidden', 'value' => 'webservice'),
                        'pluginname' => array('type' => 'hidden', 'value' => 'webservice'),
                        'enabled' => array('type' => 'switchbox',
                            'class' => 'switchbox last',
                            'value' => $enabled,
                            'labelhtml' => '<span class="pseudolabel">'. get_string('webservice_requester_enabled_label2', 'auth.webservice') .'</span>',
                        ),
                    ),
                )
            ),
    );

    return $element;
}


/**
 * Pieforms submit callback for Webservices Requester form
 */
function webservice_requester_enabled_submit(Pieform $form, $values) {

    $enabled = $values['enabled'] ? 0 : 1;
    set_config('webservice_requester_enabled', $enabled);
    // Don't reload the page if this was submitted via ajax.
    if (param_boolean('ajax')) {
        if (!get_config('webservice_provider_enabled')) {
            $protos = array('soap','xmlrpc','rest','oauth');
            foreach ($protos as $proto) {
                set_config('webservice_provider_'.$proto.'_enabled', 0);
            }
        }
        exit;
    }
    redirect('/webservice/admin/index.php');
}


/**
 * Pieform for the master switch that controls whether Mahara will
 * be a webservices provider (i.e. allow incoming webservices requests
 * from other systems.)
 */
function webservice_provider_enabled_form() {
    // enable/disable webservices completely
    $enabled = (get_config('webservice_provider_enabled') || 0);
    $element = array(
        'type'  => 'html',
        'value' =>
            pieform(
                array(
                    'name' => 'webservice_provider_enabled_form',
                    'elementclasses' => false,
                    'class'           => 'form-switch',
                    'successcallback' => 'webservice_provider_enabled_submit',
                    'renderer' => 'div',
                    'jsform' => false,
                    'checkdirtychange' => false,
                    'elements' => array(
                        'plugintype' => array('type' => 'hidden', 'value' => 'auth'),
                        'type' => array('type' => 'hidden', 'value' => 'webservice'),
                        'pluginname' => array('type' => 'hidden', 'value' => 'webservice'),
                        'enabled' => array('type' => 'switchbox',
                            'class' => 'switchbox last',
                            'value' => $enabled,
                            'labelhtml' => '<span class="pseudolabel">'. get_string('webservice_provider_enabled_label2', 'auth.webservice') .'</span>',
                        ),
                    ),
                )
            ),
    );

    return $element;
}

/**
 * Pieform callback function for the "Publish webservices" master switch
 */
function webservice_provider_enabled_submit(Pieform $form, $values) {

    $enabled = $values['enabled'] ? 0 : 1;
    set_config('webservice_provider_enabled', $enabled);

    // reload/upgrade the web services configuration
    if ($enabled) {
        // ensure that we have a webservice auth_instance
        $authinstance = get_record('auth_instance', 'institution', 'mahara', 'authname', 'webservice');
        if (empty($authinstance)) {
            $authinstance = (object)array(
               'instancename' => 'webservice',
                'priority'     => 2,
                'institution'  => 'mahara',
                'authname'     => 'webservice',
            );
            insert_record('auth_instance', $authinstance);
        }
        external_reload_webservices();
    }
    if (param_boolean('ajax')) {
        if (!get_config('webservice_requester_enabled')) {
            $protos = array('soap','xmlrpc','rest','oauth');
            foreach ($protos as $proto) {
                set_config('webservice_provider_'.$proto.'_enabled', 0);
            }
        }
        exit;
    }
    redirect('/webservice/admin/index.php');
}

/**
 * Form layout for webservices protocol switch fieldset
 *
 * @return pieforms $element array
 */
function webservice_provider_protocols_form() {
    // enable/disable separate protocols of SOAP/XML-RPC/REST
    $elements = array();
    $elements['label'] = array(
        'title' => ' ',
        'type' => 'html',
        'class' => 'fake-form',
        'value' => '<h4 class="title">' . get_string('protocol', 'auth.webservice') . '</h4>'
    );

    foreach (array('soap', 'xmlrpc', 'rest', 'oauth') as $proto) {
        $enabled = (get_config('webservice_provider_' . $proto . '_enabled') || 0);
        $elements[$proto] = array(
            'title' => ' ',
            'type' => 'html',
            'class'            => 'fake-form',
            'value' => pieform(array(
                'name'            => 'webservice_provider_protocols_' . $proto,
                'elementclasses'  => false,
                'class'           => 'form-switch webservice-provider-protocol-form',
                'renderer'        => 'div',
                'successcallback' => 'webservice_provider_protocols_submit',
                'jsform'          => false,
                'checkdirtychange' => false,
                'elements' => array(
                    'plugintype' => array('type' => 'hidden', 'value' => 'auth'),
                    'type'       => array('type' => 'hidden', 'value' => 'webservice'),
                    'pluginname' => array('type' => 'hidden', 'value' => 'webservice'),
                    'protocol'   => array('type' => 'hidden', 'value' => $proto),
                    'enabled'    => array('type' => 'switchbox',
                        'class' => 'switchbox',
                        'value' => $enabled,
                        'labelhtml' => '<span class="pseudolabel">' .get_string($proto, 'auth.webservice') . ': </span>',
                    ),
                ),
            ))
        );
    }

    return $elements;
}


/**
 * Pieform callback function for the per-protocol master switches
 */
function webservice_provider_protocols_submit(Pieform $form, $values) {
    $enabled = $values['enabled'] ? 0 : 1;
    $proto = $values['protocol'];
    set_config('webservice_provider_'.$proto.'_enabled', $enabled);
    // Show/hide the account settings webservice tokens page
    clear_menu_cache();
    if (param_boolean('ajax')) {
        exit;
    }
    redirect('/webservice/admin/index.php');
}


/**
 * Service Function Groups edit form
 *
 * @return html
 */
function webservice_function_groups_form() {
    global $THEME;

    $editicon = 'icon icon-pencil left';
    $deleteicon = 'icon icon-trash left text-danger';

    $form = array(
        'name'            => 'webservices_function_groups',
        'elementclasses'  => false,
        'successcallback' => 'webservice_function_groups_submit',
        'renderer'   => 'multicolumntable',
        'elements'   => array(
            'servicegroup' => array(
                'title' => ' ',
                'datatable' => true,
                'type'  => 'html',
                'value' => get_string('service', 'auth.webservice'),
            ),
            'component' => array(
                'title' => ' ',
                'datatable' => true,
                'type'  => 'html',
                'value' => get_string('component', 'auth.webservice'),
            ),
            'enabled' => array(
                'title' => ' ',
                'datatable' => true,
                'type'  => 'html',
                'value' => get_string('enabled'),
            ),
            'restricted' => array(
                'title' => ' ',
                'datatable' => true,
                'type'  => 'html',
                'value' => get_string('restrictedusers', 'auth.webservice'),
            ),
            'tokenusers' => array(
                'title' => ' ',
                'datatable' => true,
                'type'  => 'html',
                'value' => get_string('fortokenusers', 'auth.webservice'),
            ),
            'functions' => array(
                'title' => ' ',
                'datatable' => true,
                'type'  => 'html',
                'value' => get_string('functions', 'auth.webservice'),
            ),
            'actions' => array(
                'title' => ' ',
                'datatable' => true,
                'type'  => 'html',
                'value' => '',
            ),
        ),
        );

    $dbservices = get_records_array(
        'external_services',
        null,
        null,
        'component, name',
        'id, name, shortname, component, enabled, restrictedusers, tokenusers'
    );
    if ($dbservices) {
        foreach ($dbservices as $service) {
            $iscustomservice = ($service->component === '');
            $form['elements']['id'. $service->id . '_service'] = array(
                'value'        =>  $service->name .
                    ($service->shortname ? ' (' . $service->shortname . ')' : ''),
                'type'         => 'html',
                'key'          => $service->name,
            );
            $form['elements']['id'. $service->id . '_component'] = array(
                'value'        => ($iscustomservice ? get_string('customservicegroup', 'auth.webservice') : $service->component),
                'type'         => 'html',
                'key'          => $service->name,
            );
            $form['elements']['id'. $service->id . '_enabled'] = array(
                'value'        => (($service->enabled == 1) ? display_icon('enabled') : display_icon('disabled')),
                'type'         => 'html',
                'class'        => 'center',
                'key'          => $service->name,
            );
            $form['elements']['id'. $service->id . '_restricted'] = array(
                'value' => (($service->restrictedusers == 1) ?  display_icon('enabled') : display_icon('disabled')),
                'type'         => 'html',
                'class'        => 'center',
                'key'          => $service->name,
            );
            $form['elements']['id'. $service->id . '_tokenusers'] = array(
                'value' => (($service->tokenusers == 1) ?  display_icon('enabled') : display_icon('disabled')),
                'type'         => 'html',
                'class'        => 'center',
                'key'          => $service->name,
            );
            $functions = get_records_array('external_services_functions', 'externalserviceid', $service->id);
            $function_list = array();
            if ($functions) {
                foreach ($functions as $function) {
                    $dbfunction = get_record('external_functions', 'name', $function->functionname);
                    $function_list[]= '<a href="' . get_config('wwwroot') . 'webservice/wsdoc.php?id=' . $dbfunction->id . '">' . $function->functionname . '</a>';
                }
            }
            $form['elements']['id'. $service->id . '_functions'] = array(
                'value'        =>  implode(', ', $function_list),
                'type'         => 'html',
                'key'          => $service->name,
            );

            // edit and delete buttons
            $form['elements']['id'. $service->id . '_actions'] = array(
                'value'        => pieform(array(
                    'name'            => 'webservices_function_groups_edit_' . $service->id,
                    'renderer'        => 'div',
                    'class'  => 'form-as-button pull-left',
                    'successcallback' => 'webservice_function_groups_submit',
                    'jsform'          => false,
                    'action'          => get_config('wwwroot') . 'webservice/admin/index.php',
                    'elements' => array(
                        'service'    => array('type' => 'hidden', 'value' => $service->id),
                        'action'     => array('type' => 'hidden', 'value' => 'edit'),
                        'submit'     => array(
                            'type'  => 'button',
                            'class' => 'btn-default btn-sm' . ($iscustomservice ? ' first' : ''),
                            'usebuttontag' => true,
                            'value'   => '<span class="'.$editicon.'"></span>' . get_string('edit', 'mahara'),
                            'elementtitle' => get_string('edit'),
                        ),
                    ),
                ))
                . (
                    $iscustomservice ?
                        pieform(array(
                            'name'            => 'webservices_function_groups_delete_' . $service->id,
                            'renderer'        => 'div',
                            'class'  => 'form-as-button pull-left',
                            'successcallback' => 'webservice_function_groups_submit',
                            'jsform'          => false,
                            'action'          => get_config('wwwroot') . 'webservice/admin/index.php',
                            'elements' => array(
                                'service'    => array('type' => 'hidden', 'value' => $service->id),
                                'action'     => array('type' => 'hidden', 'value' => 'delete'),
                                'submit'     => array(
                                    'type'  => 'button',
                                    'usebuttontag' => true,
                                    'class' => 'btn-default btn-sm last',
                                    'value'   => '<span class="'.$deleteicon.'"></span>' . get_string('delete', 'mahara'),
                                    'elementtitle' => get_string('delete'),
                                ),
                            ),
                        ))
                        : ''
                ),
                'type'         => 'html',
                'key'          => $service->name,
                'class'        => 'webserviceconfigcontrols btn-group',
            );
        }
    }

    $pieform = pieform_instance($form);
    return $pieform->build(false) . '<div class="function_add">' .
        pieform(array(
            'name'            => 'webservices_function_groups_add',
            'renderer'        => 'div',
            'successcallback' => 'webservice_function_groups_submit',
            'class'           => 'form-inline',
            'jsform'          => false,
            'action'          => get_config('wwwroot') . 'webservice/admin/index.php',
            'elements' => array(
                'service'    => array(
                    'type' => 'text',
                    'class' => 'last'
                ),
                'action'     => array('type' => 'hidden', 'value' => 'add'),
                'submit'     => array(
                    'type'  => 'submit',
                    'class' => 'submit btn-primary last',
                    'value' => get_string('add')
                ),
            ),
        )) .
        '</div>';
}


/**
 * Pieform callback for the "function groups" section
 */
function webservice_function_groups_submit(Pieform $form, $values) {
    global $SESSION;

    if ($values['action'] == 'add') {
        $service = preg_replace('/[^a-zA-Z0-9_ ]+/', '', $values['service']);
        $service = trim($service);
        if (empty($service) || record_exists('external_services', 'name', $service)) {
            $SESSION->add_error_msg(get_string('invalidinput', 'auth.webservice'));
        }
        else {
            $service = array('name' => $service, 'restrictedusers' => 0, 'enabled' => 0, 'tokenusers' => 0, 'component' => '', 'ctime' => db_format_timestamp(time()));
            insert_record('external_services', $service);
            $SESSION->add_ok_msg(get_string('configsaved', 'auth.webservice'));
        }
    }
    else {
        $service = get_record('external_services', 'id', $values['service']);
        if (!empty($service)) {
            if ($values['action'] == 'edit') {
                redirect('/webservice/admin/serviceconfig.php?service=' . $values['service']);
            }
            else if ($values['action'] == 'delete') {
                $component = get_field('external_services', 'component', 'id', $values['service']);
                // Can't manually delete plugin-provided services; only disable them.
                if ($component === '') {
                    // remove everything associated with a service
                    $params = array($values['service']);
                    delete_records_select('external_tokens', "externalserviceid  = ?", $params);
                    delete_records_select('external_services_users', "externalserviceid  = ?", $params);
                    delete_records_select('external_services_functions', "externalserviceid  = ?", $params);
                    delete_records('external_services', 'id', $values['service']);
                    $SESSION->add_ok_msg(get_string('configsaved', 'auth.webservice'));
                }
            }
        }
    }

    // default back to where we came from
    redirect('/webservice/admin/index.php?open=webservices_function_groups');
}


/**
 * Service Tokens Groups edit form
 *
 * @return html
 */
function webservice_tokens_form() {
    global $THEME, $USER;

    $editicon = 'icon icon-pencil left';
    $deleteicon = 'icon icon-trash left text-danger';

    $form = array(
        'name'            => 'webservices_tokens',
        'elementclasses'  => false,
        'successcallback' => 'webservice_tokens_submit',
        'renderer'   => 'multicolumntable',
        'elements'   => array(
            'token' => array(
                'title' => ' ',
                'datatable'  => true,
                'type'  => 'html',
                'value' => get_string('token', 'auth.webservice'),
            ),
            'institution' => array(
                'title' => ' ',
                'datatable'  => true,
                'type'  => 'html',
                'value' => get_string('institution'),
            ),
            'username' => array(
                'title' => ' ',
                'datatable'  => true,
                'type'  => 'html',
                'value' => get_string('username', 'auth.webservice'),
            ),
            'servicename' => array(
                'title' => ' ',
                'datatable'  => true,
                'type'  => 'html',
                'value' => get_string('servicename', 'auth.webservice'),
            ),
            'enabled' => array(
                'title' => ' ',
                'datatable'  => true,
                'type'  => 'html',
                'value' => get_string('enabled'),
            ),
            'wssigenc' => array(
                'title' => ' ',
                'datatable'  => true,
                'type'  => 'html',
                'value' => get_string('titlewssigenc', 'auth.webservice'),
            ),
            'functions' => array(
                'title' => ' ',
                'datatable'  => true,
                'type'  => 'html',
                'value' => get_string('functions', 'auth.webservice'),
            ),
            'actions' => array(
                'title' => ' ',
                'datatable'  => true,
                'type'  => 'html',
                'value' => '',
            ),
        ),
    );

    $dbtokens = get_records_sql_array('SELECT et.id as tokenid, et.wssigenc AS wssigenc, et.externalserviceid as externalserviceid, et.institution as institution, u.id as userid, u.username as username, et.token as token, es.name as name, es.enabled as enabled FROM {external_tokens} AS et LEFT JOIN {usr} AS u ON et.userid = u.id LEFT JOIN {external_services} AS es ON et.externalserviceid = es.id WHERE et.tokentype = ? ORDER BY u.username', array(EXTERNAL_TOKEN_PERMANENT));
    if (!empty($dbtokens)) {
        foreach ($dbtokens as $token) {
            $form['elements']['id'. $token->tokenid . '_token'] = array(
                'value'        =>  $token->token,
                'type'         => 'html',
                'key'          => $token->token,
            );
            $dbinstitution = get_record('institution', 'name', $token->institution);
            $form['elements']['id'. $token->tokenid . '_institution'] = array(
                'value'        =>  $dbinstitution->displayname,
                'type'         => 'html',
                'key'          => $token->token,
            );
            if ($USER->is_admin_for_user($token->userid)) {
                $user_url = get_config('wwwroot') . 'admin/users/edit.php?id=' . $token->userid;
            }
            else {
                $user_url = get_config('wwwroot') . 'user/view.php?id=' . $token->userid;
            }
            $form['elements']['id'. $token->tokenid . '_username'] = array(
                'value'        =>  '<a href="' . $user_url . '">' . $token->username . '</a>',
                'type'         => 'html',
                'key'          => $token->token,
            );
            $form['elements']['id'. $token->tokenid . '_servicename'] = array(
                'value'        =>  $token->name,
                'type'         => 'html',
                'key'          => $token->token,
            );
            $form['elements']['id'. $token->tokenid . '_enabled'] = array(
                'value' => (($token->enabled == 1) ? display_icon('enabled') : display_icon('disabled')),
                'type'         => 'html',
                'class'        => 'center',
                'key'          => $token->token,
            );
            $form['elements']['id'. $token->tokenid . '_wssigenc'] = array(
                'value' => (($token->wssigenc == 1) ? display_icon('enabled') : display_icon('disabled')),
                'type'         => 'html',
                'class'        => 'center',
                'key'          => $token->token,
            );

            $functions = get_records_array('external_services_functions', 'externalserviceid', $token->externalserviceid);
            $function_list = array();
            if ($functions) {
                foreach ($functions as $function) {
                    $dbfunction = get_record('external_functions', 'name', $function->functionname);
                    $function_list[]= '<a href="' . get_config('wwwroot') . 'webservice/wsdoc.php?id=' . $dbfunction->id . '">' . $function->functionname . '</a>';
                }
            }
            $form['elements']['id'. $token->tokenid . '_functions'] = array(
                'value'        =>  implode(', ', $function_list),
                'type'         => 'html',
                'key'          => $token->token,
            );

            // edit and delete buttons
            $form['elements']['id'. $token->tokenid . '_actions'] = array(
                'value'        => pieform(array(
                    'name'            => 'webservices_token_edit_' . $token->tokenid,
                    'renderer'        => 'div',
                    'elementclasses'  => false,
                    'successcallback' => 'webservice_token_submit',
                    'class'           => 'form-as-button pull-left',
                    'jsform'          => false,
                    'elements' => array(
                        'token'      => array('type' => 'hidden', 'value' => $token->tokenid),
                        'action'     => array('type' => 'hidden', 'value' => 'edit'),
                        'submit'     => array(
                            'type'  => 'button',
                            'usebuttontag' => true,
                            'class' => 'btn-default btn-sm first',
                            'value'   => '<span class="'.$editicon.'"></span>' . get_string('edit'),
                            'elementtitle' => get_string('editspecific', 'mahara', $token->tokenid),
                        ),
                    ),
                ))
                . pieform(array(
                    'name'            => 'webservices_token_delete_' . $token->tokenid,
                    'renderer'        => 'div',
                    'elementclasses'  => false,
                    'successcallback' => 'webservice_token_submit',
                    'class'           => 'form-as-button pull-left',
                    'jsform'          => false,
                    'elements' => array(
                        'token'      => array('type' => 'hidden', 'value' => $token->tokenid),
                        'action'     => array('type' => 'hidden', 'value' => 'delete'),
                        'submit'     => array(
                            'type'  => 'button',
                            'usebuttontag' => true,
                            'class' => 'btn-default btn-sm last',
                            'value'   => '<span class="'.$deleteicon.'"></span>' . get_string('delete'),
                            'elementtitle' => get_string('deletespecific', 'mahara', $token->tokenid),
                        ),
                    ),
                )),
                'type'         => 'html',
                'key'          => $token->token,
                'class'        => 'webserviceconfigcontrols btn-group',
            );
        }
    }
    else {
        // no results so hide headings
        $form['elements'] = array();
    }

    $username = '';
    if ($user  = param_integer('user', 0)) {
        $dbuser = get_record('usr', 'id', $user);
        if (!empty($dbuser)) {
            $username = $dbuser->username;
        }
    }
    else {
        $username = param_alphanum('username', '');
    }
    $pieform = pieform_instance($form);
    return $pieform->build(false) . '<div class="function_add">'
        . pieform(array(
            'name'            => 'webservices_token_generate',
            'renderer'        => 'div',
            'successcallback' => 'webservice_token_submit',
            'jsform'          => false,
            'action'          => get_config('wwwroot') . 'webservice/admin/index.php',
            'elements' => array(
                'userid' => array(
                    'type' => 'autocomplete',
                    'title' => get_string('username') . ': ',
                    'ajaxurl' => get_config('wwwroot') . 'webservice/admin/users.json.php',
                    'multiple' => true,
                    'allowclear' => true,
                    'ajaxextraparams' => array(),
                    'extraparams' => array(
                        'maximumSelectionLength' => 1
                    ),
                    'width' => '280px',
                ),
                'action'     => array('type' => 'hidden', 'value' => 'generate'),
                'submit'     => array(
                    'type'  => 'submit',
                    'class' => 'submit btn-primary last',
                    'value' => get_string('generate', 'auth.webservice')
                ),
            ),
        ))
        . '</div>';

}


/**
 * Pieform callback for the webservices token management form
 */
function webservice_token_submit(Pieform $form, $values) {
    global $SESSION, $USER;

    if ($values['action'] == 'generate') {
        if (!empty($values['userid'][0])) {
            $dbuser = get_record('usr', 'id', $values['userid'][0]);
            if (!empty($dbuser)) {
                $services = get_records_array('external_services', 'restrictedusers', 0);
                if (empty($services)) {
                    $SESSION->add_error_msg(get_string('noservices', 'auth.webservice'));
                }
                else {
                    // just pass the first one for the moment
                    $service = array_shift($services);
                    $token = webservice_generate_token(EXTERNAL_TOKEN_PERMANENT, $service, $dbuser->id);
                    $dbtoken = get_record('external_tokens', 'token', $token);
                    redirect('/webservice/admin/tokenconfig.php?token=' . $dbtoken->id);
                }
            }
            else {
                $SESSION->add_error_msg(get_string('invaliduserselected', 'auth.webservice'));
            }
        }
        else {
            $SESSION->add_error_msg(get_string('nouser', 'auth.webservice'));
        }

    }
    else {
        $token = get_record('external_tokens', 'id', $values['token']);
        if (!empty($token)) {
            if ($values['action'] == 'edit') {
                redirect('/webservice/admin/tokenconfig.php?token=' . $values['token']);
            }
            else if ($values['action'] == 'delete') {
                // remove everything associated with a service
                $params = array($values['token']);
                delete_records_select('external_tokens', "id = ?", $params);
                $SESSION->add_ok_msg(get_string('configsaved', 'auth.webservice'));
            }
        }
    }

    // default back to where we came from
    redirect('/webservice/admin/index.php?open=webservices_token');
}


/**
 * Service Users edit form
 *
 * @return html
 */
function webservice_users_form() {
    global $THEME, $USER;

    $editicon = 'icon icon-pencil left';
    $deleteicon = 'icon icon-trash left text-danger';

    $form = array(
        'name'            => 'webservices_users',
        'elementclasses'  => false,
        'successcallback' => 'webservice_users_submit',
        'renderer'   => 'multicolumntable',
        'elements'   => array(
            'username' => array(
                'title' => ' ',
                'datatable'  => true,
                'type'  => 'html',
                'value' => get_string('username', 'auth.webservice'),
            ),
            'institution' => array(
                'title' => ' ',
                'datatable'  => true,
                'type'  => 'html',
                'value' => get_string('institution'),
            ),
            'servicename' => array(
                'title' => ' ',
                'datatable'  => true,
                'type'  => 'html',
                'value' => get_string('servicename', 'auth.webservice'),
            ),
            'enabled' => array(
                'title' => ' ',
                'datatable'  => true,
                'type'  => 'html',
                'value' => get_string('enabled'),
            ),
            'wssigenc' => array(
                'title' => ' ',
                'datatable'  => true,
                'type'  => 'html',
                'value' => get_string('titlewssigenc', 'auth.webservice'),
            ),
            'functions' => array(
                'title' => ' ',
                'datatable'  => true,
                'type'  => 'html',
                'value' => get_string('functions', 'auth.webservice'),
            ),
            'actions' => array(
                'title' => ' ',
                'datatable'  => true,
                'type'  => 'html',
                'value' => '',
            ),
        ),
    );

    $dbusers = get_records_sql_array('SELECT eu.id as id, eu.userid as userid, eu.wssigenc AS wssigenc, eu.externalserviceid as externalserviceid, eu.institution as institution, u.username as username, es.name as name, es.enabled as enabled FROM {external_services_users} AS eu LEFT JOIN {usr} AS u ON eu.userid = u.id LEFT JOIN {external_services} AS es ON eu.externalserviceid = es.id ORDER BY eu.id', array());
    if (!empty($dbusers)) {
        foreach ($dbusers as $user) {
            $dbinstitution = get_record('institution', 'name', $user->institution);
            if ($USER->is_admin_for_user($user->id)) {
                $user_url = get_config('wwwroot') . 'admin/users/edit.php?id=' . $user->userid;
            }
            else {
                $user_url = get_config('wwwroot') . 'user/view.php?id=' . $user->userid;
            }
            $form['elements']['id'. $user->id . '_username'] = array(
                'value'        =>  '<a href="' . $user_url . '">' . $user->username . '</a>',
                'type'         => 'html',
                'key'          => $user->id,
            );
            $form['elements']['id'. $user->id . '_institution'] = array(
                'value'        =>  $dbinstitution->displayname,
                'type'         => 'html',
                'key'          => $user->id,
            );
            $form['elements']['id'. $user->id . '_servicename'] = array(
                'value'        =>  $user->name,
                'type'         => 'html',
                'key'          => $user->id,
            );
            $form['elements']['id'. $user->id . '_enabled'] = array(
                'value' => (($user->enabled == 1) ?  display_icon('enabled') : display_icon('disabled')),
                'type'         => 'html',
                'class'        => 'center',
                'key'          => $user->id,
            );
            $form['elements']['id'. $user->id . '_wssigenc'] = array(
                'value' => (($user->wssigenc == 1) ?  display_icon('enabled') : display_icon('disabled')),
                'type'         => 'html',
                'class'        => 'center',
                'key'          => $user->id,
            );

            $functions = get_records_array('external_services_functions', 'externalserviceid', $user->externalserviceid);
            $function_list = array();
            if ($functions) {
                foreach ($functions as $function) {
                    $dbfunction = get_record('external_functions', 'name', $function->functionname);
                    $function_list[]= '<a href="' . get_config('wwwroot') . 'webservice/wsdoc.php?id=' . $dbfunction->id . '">' . $function->functionname . '</a>';
                }
            }
            $form['elements']['id'. $user->id . '_functions'] = array(
                'value'        =>  implode(', ', $function_list),
                'type'         => 'html',
                'key'          => $user->id,
            );

            // edit and delete buttons
            $form['elements']['id'. $user->id . '_actions'] = array(
                'value'        => pieform(array(
                    'name'            => 'webservices_user_edit_' . $user->id,
                    'renderer'        => 'div',
                    'elementclasses'  => false,
                    'successcallback' => 'webservice_users_submit',
                    'class'           => 'form-as-button pull-left',
                    'jsform'          => false,
                    'elements' => array(
                        'suid'       => array('type' => 'hidden', 'value' => $user->id),
                        'action'     => array('type' => 'hidden', 'value' => 'edit'),
                        'submit'     => array(
                            'type'  => 'button',
                            'usebuttontag' => true,
                            'class' => 'btn-default btn-sm first',
                            'value'   => '<span class="'. $editicon.'" role="presentation" aria-hidden="true"></span>' . get_string('edit'),
                            'elementtitle' => get_string('editspecific', 'mahara', $user->username),
                        ),
                    ),
                ))
                . pieform(array(
                    'name'            => 'webservices_user_delete_' . $user->id,
                    'renderer'        => 'div',
                    'elementclasses'  => false,
                    'successcallback' => 'webservice_users_submit',
                    'class'           => 'form-as-button pull-left',
                    'jsform'          => false,
                    'elements' => array(
                        'suid'       => array('type' => 'hidden', 'value' => $user->id),
                        'action'     => array('type' => 'hidden', 'value' => 'delete'),
                        'submit'     => array(
                            'type'  => 'button',
                            'usebuttontag' => true,
                            'class' => 'btn-default btn-sm last',
                            'value'   => '<span class="'.$deleteicon.'" role="presentation" aria-hidden="true"></span>' . get_string('delete'),
                            'elementtitle' => get_string('deletespecific', 'mahara', $user->username),
                        ),
                    ),
                )),
                'type'         => 'html',
                'key'          => $user->id,
                'class'        => 'webserviceconfigcontrols btn-group',
            );
        }
    }
    else {
        // no results so hide headings
        $form['elements'] = array();
    }

    $username = '';
    if ($user  = param_integer('user', 0)) {
        $dbuser = get_record('usr', 'id', $user);
        if (!empty($dbuser)) {
            $username = $dbuser->username;
        }
    }
    else {
        $username = param_alphanum('username', '');
    }

    $pieform = pieform_instance($form);
    return $pieform->build(false) . '<div id="user_add">' .
        pieform(array(
            'name'            => 'webservices_user_generate',
            'renderer'        => 'div',
            'successcallback' => 'webservice_users_submit',
            'jsform'          => false,
            'action'          => get_config('wwwroot') . 'webservice/admin/index.php',
            'elements' => array(
                'userid' => array(
                    'type' => 'autocomplete',
                    'title' => get_string('username') . ': ',
                    'ajaxurl' => get_config('wwwroot') . 'webservice/admin/users.json.php',
                    'multiple' => true,
                    'allowclear' => true,
                    'ajaxextraparams' => array(),
                    'extraparams' => array(
                        'maximumSelectionLength' => 1
                    ),
                    'width' => '280px',
                ),
                'action'     => array('type' => 'hidden', 'value' => 'add'),
                'submit'     => array(
                    'type'  => 'submit',
                    'class' => 'submit btn-primary last',
                    'value' => get_string('add')
                ),
            ),
        ))
        . '</div>';
}


/**
 * Pieform callback for the user permissions section
 */
function webservice_users_submit(Pieform $form, $values) {
    global $SESSION, $USER;

    if ($values['action'] == 'add') {
        if (!empty($values['userid'][0])) {
            $dbuser = get_record('usr', 'id', $values['userid'][0]);
            if ($auth_instance = webservice_validate_user($dbuser)) {
                // make sure that this account is not already in use
                $existing = get_record('external_services_users', 'userid', $dbuser->id);
                if (empty($existing)) {
                    $services = get_records_array('external_services', 'restrictedusers', 1);
                    if (empty($services)) {
                      $SESSION->add_error_msg(get_string('noservices', 'auth.webservice'));
                    }
                    else {
                        // just pass the first one for the moment
                        $service = array_shift($services);
                        $dbserviceuser = (object) array(
                            'externalserviceid' => $service->id,
                            'userid' => $dbuser->id,
                            'institution' => $auth_instance->institution,
                            'ctime' => db_format_timestamp(time()),
                            'publickeyexpires' => time(),
                            'wssigenc' => 0,
                            'publickey' => '');
                        $dbserviceuser->id = insert_record('external_services_users', $dbserviceuser, 'id', true);
                        redirect('/webservice/admin/userconfig.php?suid=' . $dbserviceuser->id);
                    }
                }
                else {
                    $SESSION->add_error_msg(get_string('duplicateuser', 'auth.webservice'));
                }
            }
            else {
                $SESSION->add_error_msg(get_string('invaliduserselected', 'auth.webservice'));
            }
        }
        else {
            $SESSION->add_error_msg(get_string('nouser', 'auth.webservice'));
        }
    }
    else {
        $dbserviceuser = get_record('external_services_users', 'id', $values['suid']);
        if (!empty($dbserviceuser)) {
            if ($values['action'] == 'edit') {
                redirect('/webservice/admin/userconfig.php?suid=' . $values['suid']);
            }
            else if ($values['action'] == 'delete') {
                // remove everything associated with a service
                $params = array($values['suid']);
                delete_records_select('external_services_users', "id = ?", $params);
                $SESSION->add_ok_msg(get_string('configsaved', 'auth.webservice'));
            }
        }
    }

    // default back to where we came from
    redirect('/webservice/admin/index.php?open=webservices_user');
}

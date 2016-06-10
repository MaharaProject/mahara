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
define('MENUITEM', 'webservices/logs');
define('SECTION_PAGE', 'webservicelogs');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('webservicessearchlib.php');
define('TITLE', get_string('webservices_title', 'auth.webservice'));
define('SUBSECTIONHEADING', get_string('webservicelogs', 'auth.webservice'));

$userquery = param_variable('userquery', null);
if (is_array($userquery)) {
    $userquery = $userquery[0];
}
$username = (!empty($userquery)) ? get_field('usr', 'username', 'id', $userquery) : '';
$functionquery = param_variable('functionquery', null);
if (is_array($functionquery)) {
    $functionquery = $functionquery[0];
}
$functionname = (!empty($functionquery)) ? get_field('external_functions', 'name', 'id', $functionquery) : '';

$search = (object) array(
    'userquery'      => $username,
    'functionquery'  => $functionname,
    'protocol'       => trim(param_alphanumext('protocol', 'all')),
    'authtype'       => trim(param_alphanum('authtype', 'all')),
    'onlyerrors'     => (in_array(param_alphanum('onlyerrors', 0), array('on', '1'), true)),
    'sortby'         => param_alpha('sortby', 'timelogged'),
    'sortdir'        => param_alpha('sortdir', 'desc'),
    'offset'         => param_integer('offset', 0),
    'limit'          => param_integer('limit', 10),
);

if ($USER->get('admin')) {
    $institutions = get_records_array('institution', '', '', 'displayname');
    $search->institution = param_alphanum('institution', 'all');
}
else {
    $institutions = get_records_select_array('institution', "name IN ('" . join("','", array_keys($USER->get('admininstitutions'))) . "')", null, 'displayname');
    $search->institution_requested = param_alphanum('institution_requested', 'all');
}

list($html, $columns, $searchurl, $pagination) = build_webservice_log_search_results($search);

$institutionselect = '';
if (count($institutions) > 1) {
    $selecttype = $USER->get('admin') ? 'institution' : 'institution_requested';
    $options = array('all' => get_string('All'));
    foreach ($institutions as $institution) {
        $options[$institution->name] = $institution->displayname;
    }
    $institutionfield = array(
        $selecttype => array(
            'type' => 'select',
            'title' => get_string('Institution', 'admin'),
            'defaultvalue' => !empty($search->institution) ? $search->institution : 'all',
            'options' => $options,
        ),
    );
    $institutionselect = array_shift($institutionfield);
}

$protocoloptions = array('all' => get_string('All'));
$protocols = array('REST', 'XML-RPC', 'SOAP');
foreach ($protocols as $protocol) {
    $protocoloptions[$protocol] = $protocol;
}
$authtypes = array('TOKEN', 'USER', 'OAUTH');
$authtypeoptions = array('all' => get_string('All'));
foreach ($authtypes as $authtype) {
    $authtypeoptions[$authtype] = $authtype;
}

$form = array(
    'name' => 'logsearchform',
    'method' => 'post',
    'successcallback' => 'logsearchform_submit',
    'renderer' => 'div',
    'elements' => array(
        'userquery' => array(
            'type' => 'autocomplete',
            'title' => get_string('userauth', 'auth.webservice'),
            'defaultvalue' => !empty($userquery) ? $userquery : null,
            'ajaxurl' => get_config('wwwroot') . 'webservice/admin/users.json.php',
            'initfunction' => 'translate_ids_to_names',
            'multiple' => true,
            'ajaxextraparams' => array(),
            'extraparams' => array(
                'maximumSelectionLength' => 1
            ),
            'width' => '280px',
        ),
        $institutionselect,
        'protocol' => array(
            'type' => 'select',
            'title' => get_string('protocol', 'auth.webservice'),
            'defaultvalue' => !empty($search->protocol) ? $search->protocol : 'all',
            'options' => $protocoloptions,
        ),
        'authtype' => array(
            'type' => 'select',
            'title' => get_string('sauthtype', 'auth.webservice'),
            'defaultvalue' => !empty($search->authtype) ? $search->authtype : 'all',
            'options' => $authtypeoptions,
        ),
        'functionquery' => array(
            'type' => 'autocomplete',
            'title' => get_string('function', 'auth.webservice'),
            'defaultvalue' => !empty($functionquery) ? $functionquery : '',
            'ajaxurl' => get_config('wwwroot') . 'webservice/admin/functions.json.php',
            'initfunction' => 'translate_ids_to_functions',
            'multiple' => true,
            'ajaxextraparams' => array(),
            'extraparams' => array(
                'maximumSelectionLength' => 1
            ),
            'width' => '280px',
        ),
         'onlyerrors' => array(
            'type' => 'switchbox',
            'class' => 'last',
            'title' => get_string('errors', 'auth.webservice'),
            'defaultvalue' => $search->onlyerrors,
        ),
        'submit' => array(
            'type' => 'submit',
            'class' => 'btn-primary',
            'value' => get_string('go'),
        ),
    ),
);
unset($form['elements'][0]);
$form = pieform($form);
$smarty = smarty(array('paginator'));
setpageicon($smarty, 'icon-puzzle-piece');
safe_require('auth', 'webservice');

$smarty->assign('search', $search);
$smarty->assign('alphabet', explode(',', get_string('alphabet')));
$smarty->assign('cancel', get_string('cancel'));
$smarty->assign('institutions', $institutions);
$smarty->assign('protocols', array('REST', 'XML-RPC', 'SOAP'));
$smarty->assign('authtypes', array('TOKEN', 'USER', 'OAUTH'));
$smarty->assign('results', $html);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('pagination_js', $pagination['javascript']);
$smarty->assign('columns', $columns);
$smarty->assign('searchurl', $searchurl['url']);
$smarty->assign('sortby', $searchurl['sortby']);
$smarty->assign('sortdir', $searchurl['sortdir']);
$smarty->assign('form', $form);

$smarty->display('auth:webservice:webservicelogs.tpl');

function logsearchform_submit(Pieform $form, $values) {
    $query = array();
    $validoptions = array('userquery', 'protocol', 'authtype', 'functionquery', 'onlyerrors');
    foreach ($values as $key => $value) {
        if (in_array($key, $validoptions) === true && !empty($value)) {
            if ($key == 'userquery') {
                $query[$key] = $values['userquery'][0];
            }
            else if ($key == 'functionquery') {
                $query[$key] = $values['functionquery'][0];
            }
            else {
                $query[$key] = $value;
            }
        }
    }
    $goto = '/webservice/admin/webservicelogs.php?' . http_build_query($query);
    redirect($goto);
}

/**
 * Translate the supplied user id to it's display name
 *
 * @param array $ids  User id number
 * @return object $results containing id and text values
 */
function translate_ids_to_names(array $ids) {

    // for an empty list, the element '' is transmitted
    $ids = array_diff($ids, array(''));
    $results = array();
    foreach ($ids as $id) {
        $deleted = get_field('usr', 'deleted', 'id', $id);
        if (($deleted === '0') && is_numeric($id)) {
            $results[] = (object) array('id' => $id, 'text' => display_name($id));
        }
    }
    return $results;
}

/**
 * Translate the supplied id to the name from the external_functions table
 *
 * @param array $ids  external_functions table id number
 * @return object $results containing id and text values
 */
function translate_ids_to_functions(array $ids) {

    // for an empty list, the element '' is transmitted
    $ids = array_diff($ids, array(''));
    $results = array();
    foreach ($ids as $id) {
        if (is_numeric($id)) {
            $results[] = (object) array('id' => $id, 'text' => get_field('external_functions', 'name', 'id', $id));
        }
    }
    return $results;
}

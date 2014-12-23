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
define('MENUITEM', 'configextensions/pluginadminwebservices');
define('SECTION_PAGE', 'webservice');
require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('pluginadmin', 'admin'));
require_once('pieforms/pieform.php');
require_once(get_config('docroot') . 'webservice/lib.php');

$function  = param_integer('id', 0);
$dialog = param_integer('dialog', 0);
$dbfunction = get_record('external_functions', 'id', $function);
if (empty($dbfunction)) {
    $SESSION->add_error_msg(get_string('invalidfunction', 'auth.webservice'));
    redirect('/webservice/admin/index.php');
}
$fdesc = webservice_function_info($dbfunction->name);

$smarty = smarty(array(), array('<link rel="stylesheet" type="text/css" href="' . $THEME->get_url('style/webservice.css', false, 'auth/webservice') . '">',));
safe_require('auth', 'webservice');
PluginAuthWebservice::menu_items($smarty, 'webservice');
$smarty->assign('function', $dbfunction);
$smarty->assign('functiondescription', $fdesc->description);
$smarty->assign('fdesc', $fdesc);
$smarty->assign('xmlrpcactive', webservice_protocol_is_enabled('xmlrpc'));
$smarty->assign('restactive', webservice_protocol_is_enabled('rest'));
$smarty->assign('soapactive', webservice_protocol_is_enabled('soap'));
$heading = get_string('wsdoc', 'auth.webservice');
$smarty->assign('PAGEHEADING', $heading);
$smarty->assign('dialog', $dialog);
$smarty->display('auth:webservice:wsdoc.tpl');
die;

/**
 * Return documentation for a ws description object
 * ws description object can be 'external_multiple_structure', 'external_single_structure'
 * or 'external_value'
 * Example of documentation for moodle_group_create_groups function:
  list of (
  object {
  courseid int //id of course
  name string //multilang compatible name, course unique
  description string //group description text
  enrolmentkey string //group enrol secret phrase
  }
  )
 * @param object $params a part of parameter/return description
 * @return string the html to display
 */
function wsdoc_detailed_description_html($params) {
    /// retrieve the description of the description object
    $paramdesc = "";
    if (!empty($params->desc)) {
        $paramdesc .= '<span style="color:#2A33A6">';
        if ($params->required == VALUE_REQUIRED) {
            $required = '';
        }
        if ($params->required == VALUE_DEFAULT) {
            if ($params->default === null) {
                $params->default = "null";
            }
            $required = '<b>' .
                    get_string('default', 'auth.webservice', $params->default)
                    . '</b>';
        }
        if ($params->required == VALUE_OPTIONAL) {
            $required = '<b>' .
                    get_string('optional', 'auth.webservice') . '</b>';
        }
        $paramdesc .= " " . $required . " ";
        $paramdesc .= '<i>';
        $paramdesc .= "//";

        $paramdesc .= $params->desc;

        $paramdesc .= '</i>';

        $paramdesc .= '</span>';
        $paramdesc .= '<br/>';
    }

    /// description object is a list
    if ($params instanceof external_multiple_structure) {
        return $paramdesc . "list of ( " . '<br/>'
        . '    ' . wsdoc_detailed_description_html($params->content) . ")";
    }
    else if ($params instanceof external_single_structure) {
        /// description object is an object
        $singlestructuredesc = $paramdesc . "object {" . '<br/>';
        foreach ($params->keys as $attributname => $attribut) {
            $singlestructuredesc .= '<b>';
            $singlestructuredesc .= $attributname;
            $singlestructuredesc .= '</b>';
            $singlestructuredesc .= " " .
                    wsdoc_detailed_description_html($params->keys[$attributname]);
        }
        $singlestructuredesc .= "} ";
        $singlestructuredesc .= '<br/>';
        return $singlestructuredesc;
    }
    else {
        /// description object is a primary type (string, integer)
        switch ($params->type) {
            case PARAM_BOOL:
            case PARAM_INT:
                $type = 'int';
                break;
            case PARAM_FLOAT;
                $type = 'double';
                break;
            default:
                $type = 'string';
        }
        return $type . " " . $paramdesc;
    }
}

/**
 * xmlrpc function that starts it all off
 *
 * @param $paramname
 * @param $paramdescription
 */
function wsdoc_xmlrpc($paramname, $paramdescription) {
    return htmlspecialchars('[' . $paramname . '] =>' . wsdoc_xmlrpc_param_description_html($paramdescription));
}


/**
 * Create indented XML-RPC  param description
 * @param object $paramdescription
 * @param string $indentation composed by space only
 * @return string the html to diplay
 */
function wsdoc_xmlrpc_param_description_html($paramdescription, $indentation = "") {
    $indentation = $indentation . "    ";
    $brakeline = <<<EOF


EOF;
    /// description object is a list
    if ($paramdescription instanceof external_multiple_structure) {
        $return = $brakeline . $indentation . "Array ";
        $indentation = $indentation . "    ";
        $return .= $brakeline . $indentation . "(";
        $return .= $brakeline . $indentation . "[0] =>";
        $return .= wsdoc_xmlrpc_param_description_html($paramdescription->content, $indentation);
        $return .= $brakeline . $indentation . ")";
        return $return;
    }
    else if ($paramdescription instanceof external_single_structure) {
        /// description object is an object
        $singlestructuredesc = $brakeline . $indentation . "Array ";
        $keyindentation = $indentation . "    ";
        $singlestructuredesc .= $brakeline . $keyindentation . "(";
        foreach ($paramdescription->keys as $attributname => $attribut) {
            $singlestructuredesc .= $brakeline . $keyindentation . "[" . $attributname . "] =>" .
                    wsdoc_xmlrpc_param_description_html(
                            $paramdescription->keys[$attributname], $keyindentation) .
                    $keyindentation;
        }
        $singlestructuredesc .= $brakeline . $keyindentation . ")";
        return $singlestructuredesc;
    }
    else {
        /// description object is a primary type (string, integer)
        switch ($paramdescription->type) {
            case PARAM_BOOL:
            case PARAM_INT:
                $type = 'int';
                break;
            case PARAM_FLOAT;
                $type = 'double';
                break;
            default:
                $type = 'string';
        }
        return " " . $type;
    }
}

/**
 * rest function that starts it all off
 *
 * @param $paramname
 * @param $paramdescription
 */
function wsdoc_rest($paramname, $paramdescription) {
    return htmlspecialchars(wsdoc_rest_param_description_html($paramdescription, $paramname));
}

/**
 * function that displays rest valid response
 *
 * @param $paramname
 * @param $paramdescription
 */
function wsdoc_rest_response($paramname, $paramdescription) {
    $brakeline = <<<EOF


EOF;
    $restresponse = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>"
        . $brakeline . "<RESPONSE>" . $brakeline;
    $restresponse .= wsdoc_description_in_indented_xml_format(
                    $paramdescription);
    $restresponse .="</RESPONSE>" . $brakeline;
    return htmlspecialchars($restresponse);
}

/**
 * function that displays rest error response
 */
function wsdoc_rest_exception() {
    $errormessage = get_string('invalidparameter', 'auth.webservice');
    $restexceptiontext = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<EXCEPTION class="WebserviceInvalidParameterException">
    <MESSAGE>{$errormessage}</MESSAGE>
    <DEBUGINFO></DEBUGINFO>
</EXCEPTION>
EOF;

    return htmlspecialchars($restexceptiontext);
}

/**
 * Return indented REST param description
 * @param object $paramdescription  The structure information
 * @param string $paramstring       The key to display
 * @return string the html to diplay
 */
function wsdoc_rest_param_description_html($paramdescription, $paramstring) {
    $brakeline = <<<EOF


EOF;
    /// description object is a list
    if ($paramdescription instanceof external_multiple_structure) {
        $paramstring = $paramstring . '[0]';
        $return = wsdoc_rest_param_description_html($paramdescription->content, $paramstring);
        return $return;
    }
    else if ($paramdescription instanceof external_single_structure) {
        /// description object is an object
        $singlestructuredesc = "";
        $initialparamstring = $paramstring;
        foreach ($paramdescription->keys as $attributname => $attribut) {
            $paramstring = $initialparamstring . '[' . $attributname . ']';
            $singlestructuredesc .= wsdoc_rest_param_description_html(
                            $paramdescription->keys[$attributname], $paramstring);
        }
        return $singlestructuredesc;
    }
    else {
        /// description object is a primary type (string, integer)
        $paramstring = $paramstring . '=';
        switch ($paramdescription->type) {
            case PARAM_BOOL:
            case PARAM_INT:
                $type = 'int';
                break;
            case PARAM_FLOAT;
                $type = 'double';
                break;
            default:
                $type = 'string';
        }
        return $paramstring . " " . $type . $brakeline;
    }
}

/**
 * Return a description object in indented xml format (for REST response)
 * It is indented in order to be displayed into <pre> tag
 * @param object $returndescription
 * @param string $indentation composed by space only
 * @return string the html to diplay
 */
function wsdoc_description_in_indented_xml_format($returndescription, $indentation = "") {
    $indentation = $indentation . "    ";
    $brakeline = <<<EOF


EOF;
    /// description object is a list
    if ($returndescription instanceof external_multiple_structure) {
        $return = $indentation . "<MULTIPLE>" . $brakeline;
        $return .= wsdoc_description_in_indented_xml_format($returndescription->content,
                        $indentation);
        $return .= $indentation . "</MULTIPLE>" . $brakeline;
        return $return;
    }
    else if ($returndescription instanceof external_single_structure) {
        /// description object is an object
        $singlestructuredesc = $indentation . "<SINGLE>" . $brakeline;
        $keyindentation = $indentation . "    ";
        foreach ($returndescription->keys as $attributname => $attribut) {
            $singlestructuredesc .= $keyindentation . "<KEY name=\"" . $attributname . "\">"
                    . $brakeline .
                    wsdoc_description_in_indented_xml_format(
                            $returndescription->keys[$attributname], $keyindentation) .
                    $keyindentation . "</KEY>" . $brakeline;
        }
        $singlestructuredesc .= $indentation . "</SINGLE>" . $brakeline;
        return $singlestructuredesc;
    }
    else {
        /// description object is a primary type (string, integer)
        switch ($returndescription->type) {
            case PARAM_BOOL:
            case PARAM_INT:
                $type = 'int';
                break;
            case PARAM_FLOAT;
                $type = 'double';
                break;
            default:
                $type = 'string';
        }
        return $indentation . "<VALUE>" . $type . "</VALUE>" . $brakeline;
    }
}

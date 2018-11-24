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
require_once(get_config('docroot') . 'webservice/lib.php');

// The characters to use for indenting
define('WSDOC_INDENT_SPACE', '&nbsp;&nbsp;&nbsp;&nbsp;');

$functionname = param_alphanumext('functionname', '');
$functionid  = param_integer('id', 0);
$dialog = param_integer('dialog', 0);
if ($functionid) {
    // We've retained the id-based URL because it's used so many places. But it's
    // a lot more readable (i.e. in URL completions) to use the name-based URL,
    // so let's redirect to that.
    $functionname = get_field('external_functions', 'name', 'id', $functionid);
    if ($functionname) {
        $redirect = '/webservice/wsdoc.php?functionname=' . $functionname;
        if ($dialog) {
            $redirect .= '&dialog=1';
        }
        redirect($redirect);
    }
}
else if ($functionname) {
    $dbfunction = get_record('external_functions', 'name', $functionname);
}
if (empty($dbfunction)) {
    $SESSION->add_error_msg(get_string('invalidfunction', 'auth.webservice'));
    redirect('/webservice/admin/index.php');
}

define('TITLE', get_string('function', 'auth.webservice') . ': ' . $dbfunction->name);

$fdesc = webservice_function_info($dbfunction->name);

$smarty = smarty();
safe_require('auth', 'webservice');
PluginAuthWebservice::menu_items($smarty, 'webservice');
$smarty->assign('function', $dbfunction);
$smarty->assign('functiondescription', $fdesc->description);
$smarty->assign('fdesc', $fdesc);
$smarty->assign('xmlrpcactive', webservice_protocol_is_enabled('xmlrpc'));
$smarty->assign('restactive', webservice_protocol_is_enabled('rest'));
$smarty->assign('soapactive', webservice_protocol_is_enabled('soap'));
$smarty->assign('PAGEHEADING', get_string('wsdoc', 'auth.webservice'));
$smarty->assign('dialog', $dialog);
$smarty->display('auth:webservice:wsdoc.tpl');
die;

/**
 * Recursively generates an HTML-formatted description of a webservice external_description
 * object (i.e. webservice function parameters and return structures).
 * The output includes some HTML tags for syntax highlighting, so it should
 * not be htmlescaped.
 *
 * Example of documentation for module_mobileapi_get_notifications response structure
 *
 * object {
 *     synctime: int Current timestamp on server
 *     numnotifications: int Total number of unread notifications available
 *     notifications:
 *         list of (
 *             object {
 *                 id: int notification record id
 *                 subject: string Notification's subject line
 *                 message: string Notification's body
 *             }
 *         )
 * }
 * @param object $params A part of parameter/return description
 * @param integer $indentlevel The current level of indentation
 * @return string the html to display
 */
function wsdoc_detailed_description_html($params, $indentlevel = 0) {
    $nlsame = '</br>' . str_repeat(WSDOC_INDENT_SPACE, $indentlevel);
    $nlright = '</br>' . str_repeat(WSDOC_INDENT_SPACE, $indentlevel + 1);
    $comment = '';

    if (!empty($params->desc) || isset($params->required)) {
        $comment .= '<span class="wsdescription">';
        if (isset($params->required)) {
            switch ($params->required) {

                case VALUE_DEFAULT:
                    if ($params->default === null) {
                        $params->default = "(null)";
                    }
                    else if (is_string($params->default)) {
                        $params->default = '"' . $params->default . '"';
                    }
                    else if (is_bool($params->default)) {
                        $params->default = $params->default ? "(true)" : "(false)";
                    }
                    else if (!is_scalar($params->default)) {
                        $params->default = '&lt;' . gettype($params->default) . '&gt;';
                    }
                    $required = '<span class="wsoptional">' . get_string('default', 'auth.webservice', $params->default) . '</span> ';
                    break;

                case VALUE_OPTIONAL:
                    $required = '<span class="wsoptional">' .
                        get_string('optional', 'auth.webservice');
                    if (isset($params->oneof) && !empty($params->oneof)) {
                        $required .= ' (' . get_string('oneof', 'auth.webservice') . ')';
                    }
                    $required .= '</span> ';
                    break;

                case VALUE_REQUIRED:
                    $required = '<span class="wsrequired">' . get_string('required', 'auth.webservice') . '</span> ';
                    break;

                default:
                    $required = '';
            }
            $comment .= $required;
        }
        // If we have a default and a desc, put a space between them
        if (isset($params->required) && !empty($params->desc)) {
            $comment .= ' ';
        }
        // Print the description for the param
        if (!empty($params->desc)) {
            $comment .= "<span class='wsdescriptiontext'>{$params->desc}</span>";
        }
        $comment .= '</span>';
    }

    /// description object is a list
    if ($params instanceof external_multiple_structure) {
        return $comment
            . $nlsame
            // HACK: Normally a lang string like this should be parameterized
            // "list of (%)". But in this case the stuff in the parens could be huge,
            // and spaces are important for formatting. So just concatenating.
            . get_string('list', 'auth.webservice') . ' ('
            . ($params->content instanceof external_value ? $nlright : '')
            . wsdoc_detailed_description_html($params->content, $indentlevel + 1)
            . $nlsame
            . ')';
    }
    /// description object is an object
    else if ($params instanceof external_single_structure) {
        // Print comments (after attribute printed by parent)
        // Then go down one line, indent, print "object {"
        // Then down another line, indent again, and print each attribute one
        // per line.
        $returnstr =
            $comment
            . $nlsame
            . 'object {';
        if ($params->keys) {
            foreach ($params->keys as $attributename => $attribute) {
                $returnstr .=
                    $nlright
                    . "<span class='wsname'>$attributename:</span> ";
                $i = $indentlevel + 1;
                if (!$attribute instanceof external_value) {
                    $i++;
                }
                $returnstr .= wsdoc_detailed_description_html($attribute, $i);
            }
            $returnstr .= $nlsame;
        }
        $returnstr .= '}';
        return $returnstr;
    }
    /// description object is a primary type (string, integer)
    else {
        switch ($params->type) {
            case PARAM_BOOL:
                $type = 'bool';
                break;
            case PARAM_INT:
            case PARAM_INTEGER:
                $type = 'int';
                break;
            case PARAM_FLOAT:
            case PARAM_NUMBER:
                $type = 'double';
                break;
            case PARAM_RAW:
            case PARAM_RAW_TRIMMED:
            case PARAM_TEXT:
                $type = 'string';
                break;
            case PARAM_ALPHA:
            case PARAM_ALPHAEXT:
            case PARAM_ALPHANUM:
            case PARAM_ALPHANUMEXT:
            case PARAM_BASE64:
            case PARAM_CLEANHTML:
            case PARAM_CLEAN:
            case PARAM_EMAIL:
            case PARAM_FILE:
            case PARAM_HOST:
            case PARAM_LOCALURL:
            case PARAM_NOTAGS:
            case PARAM_PATH:
            case PARAM_PEM:
            case PARAM_RAW_TRIMMED:
            case PARAM_SAFEDIR:
            case PARAM_SAFEPATH:
            case PARAM_SAFEPATH:
            case PARAM_URL:
            case PARAM_STRINGID:
            default:
                // String with additional filters/restrictions
                $type = "string ({$params->type})";
        }
        return $type . ($comment ? ' ' : '') . $comment;
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

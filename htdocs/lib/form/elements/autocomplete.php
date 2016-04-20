<?php
/**
 *
 * @package    mahara
 * @subpackage artefact
 * @author     Yuliya Bozhko <yuliya.bozhko@totaralms.com>
 * @author     Aaron Wells <aaronw@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

// To use in forms:
// 'ELEMENTNAME'  => array(
//      'type'         => 'autocomplete',
//      'title'        => TEXT,
//      'ajaxurl'      => Absolute URL of the Ajax callback script that provides the autocompletion results.
//                        This script should take params "q", "page" (the page number, starts at 1), and "sesskey".
//                        It should return a JSON-encoded object that must contain fields "more" (which indicates
//                        whether there are more results to page through) and "results", which will be an array
//                        of result objects each of which must have an "id", a "text", and optionally a "disabled" flag.
//                        {
//                           more : true,
//                           results : [
//                              {id:1, text:'first result'},
//                              {id:2, text:'second result'},
//                              {id:3, text:'disabled result', disabled:true},
//                           ]
//                        }
//      'initfunction' => A PHP function (name or inline function) that translates selected values ("id"'s) into (id, text)
//                        pairs. This is used when initially displaying the menu, because Pieforms only gives us the
//                        value/id of the selected element, but Select2 also needs to know its label/text.
//                        For a multi-select this method should take an array of IDs and return an array of objects with
//                        "id" and "text" fields. For a non-multi-select it should take a single ID and return a single
//                        object.
//      'defaultvalue' => The value (ID) of the initial selected element(s). For a multi-select this should be an array
//                        of IDs; for a non-multi-select it should be a single ID. NOTE: This is *just* the ID(s),
//                        not the label (i.e. "text"). The label will be retrieved by passing this to the "initfunction"
//                        callback method.
//      'description'  => TEXT, // Optional description printed under the menu
//      'multiple'     => false, // Indicates whether we should allow the user to select multiple items
//      'hint'         => TEXT, // Optional placeholder text displayed before anything is selected
//      'mininputlength' => Default 1. User must enter this many characters before a search is fired off
//      'allowclear'   => Default false. Only for non-multiple selects. Set to "true" to add a button that clears
//                        the user's selection
//      'width'        => Default 300px. How wide to make the dropdown. Passed to the select2 "width" parameter.
//                        Since we're doing Ajax calls, the menu will be a little glitchy because it can't tell
//                        how wide the widest item it will need to display is. So it's best to pass it a hard-coded width,
//                        that you expect will be wide enough to display your entries.
//      'extraparams'  => array(key => value, key2 => value2,  ...), // Optional additional configuration parameters for
//                        the select2 ajax library.
//      'inblockconfig' => If the field is a block config field we need to handle the js autocomplete js slightly differently
// ),

defined('INTERNAL') || die();

/**
 * Autocomplete list selector element
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The HTML for the element
 */
function pieform_element_autocomplete(Pieform $form, $element) {
    global $USER;
    $wwwroot = get_config('wwwroot');
    $lang = pieform_element_autocomplete_language();

    $smarty = smarty_core();

    $smarty->left_delimiter = '{{';
    $smarty->right_delimiter = '}}';

    $value = $form->get_value($element);
    $multiple = !empty($element['multiple']);

    if (!empty($element['initfunction'])) {
        $initvalues = call_user_func($element['initfunction'], $value);
    }
    else {
        $initvalues = '[]';
    }

    if (array_key_exists('mininputlength', $element)) {
        $mininputlength = $element['mininputlength'];
    }
    else {
        $mininputlength = 1;
    }

    $extraparams = '';
    if (!empty($element['extraparams'])) {
        foreach ($element['extraparams'] as $k => $v) {
            if (!is_numeric($v) && !preg_match('/^function/', $v)) {
                if (preg_match('/^\'(.*)\'$/', $v, $match)) {
                    $v = $match[1];
                }
                $element['extraparams'][$k] = json_encode($v);
            }
            $extraparams .= $k . ': ' . $element['extraparams'][$k] . ',';
        }
    }

    $smarty->assign('id', $form->get_name() . '_' . $element['id']);
    $smarty->assign('name', $element['name']);
    $smarty->assign('initvalues', $initvalues);
    $smarty->assign('width', empty($element['width']) ? '300px' : $element['width']);
    $smarty->assign('multiple', $multiple ? 'true' : 'false');
    $smarty->assign('mininputlength', $mininputlength);
    $smarty->assign('allowclear', empty($element['allowclear']) ? 'false' : 'true');
    $smarty->assign('disabled', !empty($element['disabled']) ? 'true' : 'false');
    $smarty->assign('ajaxurl', $element['ajaxurl']);
    $smarty->assign('language', $lang);
    $smarty->assign('sesskey', $USER->get('sesskey'));
    $smarty->assign('hint', empty($element['hint']) ? get_string('defaulthint') : $element['hint']);
    $smarty->assign('extraparams', $extraparams);
    $smarty->assign('inblockconfig', !empty($element['inblockconfig']) ? 'true' : 'false');
    if (isset($element['description'])) {
        $smarty->assign('describedby', $form->element_descriptors($element));
    }

    return $smarty->fetch('form/autocomplete.tpl');
}

/**
 * Returns the current language that the user is viewing the form with
 * or 'en' if a corresponding select2 lang file is not found.
 *
 * @return string $langstr  A valid lang string
 */
function pieform_element_autocomplete_language() {
    global $THEME;
    // Add language file if required.
    $lang = current_language();
    // Replace '_' with '-' which is used in select2.
    $lang = str_replace('_', '-', substr($lang, 0, ((substr_count($lang, '_') > 0) ? 5 : 2)));
    if ($lang != 'en' && file_exists(get_config('docroot') . "js/select2/i18n/{$lang}.js")) {
        return $lang;
    }
    else {
        // Try parent language pack, which, for example, would be 'pt' for 'pt-BR'.
        $lang = substr($lang, 0, 2);
        if ($lang != 'en' && file_exists(get_config('docroot') . "js/select2/i18n/{$lang}.js")) {
            return $lang;
        }
    }

    return 'en';
}

/**
 * Returns code to go in <head> for the given autocomplete instance
 *
 * @param array $element The element to get <head> code for
 * @return array         An array of HTML elements to go in the <head>
 */
function pieform_element_autocomplete_get_headdata() {
    global $THEME;
    $cssfile = $THEME->get_url('style/select2.css');
    $lang = pieform_element_autocomplete_language();
    $langfile = '';
    if ($lang != 'en') {
        $langfile = '<script type="application/javascript" src="' .
            get_config('wwwroot') . "js/select2/i18n/{$lang}.js" .
            '"></script>';
    }
    $r = <<<JS
<link rel="stylesheet" href="{$cssfile}" />
{$langfile}
JS;
    return array($r);
}

/**
 * Translates the raw form data into PHP variables. Basically it just needs to
 * decide whether we should return an array (if this is a multi-select) or a
 * scalar (if this is not a multi-select)
 *
 * @param Pieform $form
 * @param array $element
 * @return mixed
 */
function pieform_element_autocomplete_get_value(Pieform $form, $element) {
    $global = ($form->get_property('method') == 'get') ? $_GET : $_POST;
    if (isset($element['value'])) {
        $values = $element['value'];
    }
    else if ($form->is_submitted() && isset($global[$element['name']])) {
        $values = $global[$element['name']];
    }
    else if (!$form->is_submitted() && isset($element['defaultvalue'])) {
        $values = $element['defaultvalue'];
    }
    else if (!empty($element['disabled']) && isset($element['defaultvalue'])) {
        $values = $element['defaultvalue'];
    }
    else {
        $values = null;
    }

    if (empty($element['multiple'])) {
        return $values;
    }
    else {
        // Defaultvalue will already be an array
        if (is_array($values)) {
            return $values;
        }

        // Values returned form the form will be a comma-separated list
        $r = explode(',', $values);
        if ($r === false) {
            return array();
        }
        else {
            return $r;
        }
    }
}

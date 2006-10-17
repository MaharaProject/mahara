<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage core
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Builds, validates and processes a form.
 *
 * Given a form definition, and as long as one or two functions are implemented
 * by the caller, this function will handle everything else.
 *
 * USAGE:
 *
 * <pre>
 * $form = array(
 *     'name' => 'myform',
 *     'action' => '/myscript.php',
 *     'method' => 'post',
 *     'elements' => array(
 *         // definition of elements in the form
 *     )
 * );
 *
 * $smarty->assign('myform', form($form));
 *
 * function myform_validate($form, $values) {
 *     // perform validation agains form elements here
 *     // some types of validation are conveniently available already as
 *     // as part of the form definition hash
 * }
 *
 * function myform_submit($values) {
 *     // perform action knowing that the values are valid, e.g. DB insert.
 * }
 * </pre>
 *
 */
function form($data) {
    return Form::process($data);
    //
    // TODO:
    // 
    //  - more form element types (inc. types like autocomplete and date picker and wyswiyg)
    //  - remove the # prefix - unnecessary
    //  - only truly supports "post" due to use of $_POST - should put the value in #sent
    //    or similar so it can be accessed
    //  - for elements like <select>, detect if an invalid option is submitted
    //  - support processing of data before validation occurs (e.g. trim())
    //  - Basic validation is possible as there's a callback function for checking,
    //    but some helper functions could be written to make people's job validating
    //    stuff much easier (form_validate_email, form_validate_date etc).
    //  - Collapsible js for fieldsets
    //  - Grippie for textareas
    //  - javascript validation
    //  - handle multiple submit buttons
    //  - handle multipage forms?
    //  
    //
    //  @todo: note somewhere that name, method, action are NOT html escaped, you have to
    //  do it yourself when buliding a form
}

// For general form exceptions
class FormException extends Form {}

class Form {
    public static $formtabindex = 1;

    private $name = '';
    private $method = 'get';
    private $action = '';
    private $tabindex = 1;
    private $data = array();
    private $fileupload = false; 

    public static function process($data) {
        $form = new Form($data);
        return $form->build();
    }

    public function __construct($data) {
        if (!isset($data['name']) || !preg_match('/^[a-z_][a-z0-9_]*$/', $data['name'])) {
            throw new FormException('Forms must have a name, and that name must be valid (validity test: could you give a PHP function the name?)');
        }
        if ($data['name'] == 'form') {
            throw new FormException('You cannot call your form "form" due to namespace collisions with the form library');
        }
        $this->name = $data['name'];

        // Assign defaults for form
        $form_defaults = array(
            'method' => 'post',
            'action' => '',
            'elements' => array()
        );
        $data = array_merge($form_defaults, $data);

        // Set the method - only get/post allowed
        $data['method'] = strtolower($data['method']);
        if ($data['method'] != 'post') {
            $data['method'] = 'get';
        }
        $this->method = $data['method'];

        // Set the action
        $this->action = $data['action'];

        // Set a default tabindex
        if (isset($data['tabindex'])) {
            $this->tabindex = intval($data['tabindex']);
        }
        else {
            $this->tabindex = self::$formtabindex++;
        }

        if (!is_array($data['elements'])) {
            throw new FormException('Forms must have a list of elements');
        }
        $this->elements = $data['elements'];

        // Add a hidden element to the form that can be used to check if it has
        // been submitted.
        $this->elements['form_' . $this->name] = array(
            'type' => 'hidden',
            'value' => ''
        );
        
        // Set some attributes for all elements
        foreach ($this->elements as $name => &$element) {
            if (!isset($element['type'])) {
                $element['type'] = 'markup';
            }
            if (!isset($element['title'])) {
                $element['title'] = '';
            }
            if ($element['type'] == 'file') {
                $this->fileupload = true;
            }
            if ($element['type'] == 'fieldset') {
                foreach ($element['elements'] as $subname => &$subelement) {
                    if (!isset($subelement['type'])) {
                        $subelement['type'] = 'markup';
                    }
                    if (!isset($subelement['title'])) {
                        $subelement['title'] = '';
                    }
                    if ($subelement['type'] == 'file') {
                        $this->fileupload = true;
                    }
                    $subelement['name'] = $subname;
                    $subelement['tabindex'] = $this->tabindex;
                }
            }
            else {
                $element['name'] = $name;
                $element['tabindex'] = $this->tabindex;
            }
        }

        // Check if the form was submitted
        if (isset($_POST['form_' . $this->name])) {
            // Get the values that were submitted
            $values = $this->get_submitted_values();
            // Perform general validation first
            $this->validate($values);
            // Then user specific validation if a function is available for that
            $function = $this->name . '_validate';
            if (function_exists($function)) {
                $function($this, $values);
            }

            // Submit the form if things went OK
            if (!$this->errors()) {
                $function = $this->name . '_submit';
                if (function_exists($function)) {
                    // Call the user defined function for processing a submit
                    // This function should really redirect/exit after done
                    $function($values);
                }
                else {
                    throw new FormException('No function registered to handle form submission for form ' . $this->name);
                }
            }
        }
    }

    /**
     * Builds the HTML for the form
     *
     * Note: the form action is NOT html escaped, to allow people to build their own
     */
    public function build() {
        $result = '<form';
        foreach (array('name', 'method', 'action') as $attribute) {
            $result .= ' ' . $attribute . '="' . $this->{$attribute} . '"';
        }
        if ($this->fileupload) {
            $result .= ' enctype="multipart/form-data"';
        }
        $result .= ">\n";
        foreach ($this->elements as $name => $elem) {
            $result .= form_render_element($elem, $this);
        }

        $result .= "</form>\n";
        return $result;
    }

    /**
     * Given an element, gets the value for it from this form
     */
    public function get_value($element) {
        // @todo consult $this->method to see whether to get from $_POST or $_GET
        if (isset($element['value'])) {
            return $element['value'];
        }
        else if (isset($_POST[$element['name']]) && $element['type'] != 'submit') {
            return $_POST[$element['name']];
        }
        else if (isset($element['defaultvalue'])) {
            return $element['defaultvalue'];
        }
        return null;
    }

    /**
     * Retrieves a list of elements in the form.
     *
     * This flattens fieldsets, and ignore the actual fieldset elements
     */ 
    public function get_elements() {
        $elements = array();
        foreach ($this->elements as $name => $element) {
            if ($element['type'] == 'fieldset') {
                foreach ($element['elements'] as $subelement) {
                    $elements[] = $subelement;
                }
            }
            elseif ($name != 'form_' . $this->name) {
                $elements[] = $element;
            }
        }
        return $elements;
    }

    /**
     * Retrieves submitted values from POST for the elements of this form
     */
    private function get_submitted_values() {
        $result = array();
        foreach ($this->get_elements() as $element) {
            $elementnames[] = $element['name'];
        }

        // @todo inspect $this->method for the array to use
        foreach ($_POST as $key => $value) {
            if (in_array($key, $elementnames)) {
                $result[$key] = $value;
            }
        }
        return $result;
    }


    /**
     * Performs simple validation based off the definition array
     */
    private function validate($values) {
        foreach ($this->get_elements() as $element) {
            if (!empty($element['required']) && (!isset($values[$element['name']]) || $values[$element['name']] == '')) {
                $this->set_error($element['name'], get_string('This field is required', 'mahara'));
            }
            if (!empty($element['minlength'])
                && (
                    !isset($values[$element['name']])
                    || strlen($values[$element['name']]) < intval($element['minlength'])
            )) {
                $this->set_error($element['name'], get_string('You must put a minimum of '
                    . intval($element['minlength']) . ' characters in this field', 'mahara'));
            }
            if (!empty($element['maxlength'])
                && (
                    !isset($values[$element['name']])
                    || strlen($values[$element['name']]) > intval($element['maxlength'])
            )) {
                $this->set_error($element['name'], get_string('You must put a maximum of '
                    . intval($element['maxlength']) . ' characters in this field', 'mahara'));
            }
        }
    }

    /**
     * Marks a field has having an error
     */
    public function set_error($name, $message) {
        foreach ($this->elements as &$element) {
            if ($element['type'] == 'fieldset') {
                foreach ($element['elements'] as &$subelement) {
                    if ($subelement['name'] == $name) {
                        $subelement['error'] = $message;
                        return;
                    }
                }
            }
            else {
                if ($element['name'] == $name) {
                    $element['error'] = $message;
                    return;
                }
            }
        }
    }

    /**
     * Return true if there are errors with the form
     */
    private function errors() {
        foreach ($this->get_elements() as $element) {
            if (isset($element['error'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Makes an ID for an element
     */
    public static function make_id($element) {
        if (isset($element['id'])) {
            return hsc($element['id']);
        }
        if (isset($element['name'])) {
            return hsc($element['name']);
        }
        return substr(md5(mt_rand()), 0, 4);
    }

    public static function make_class($element) {
        $classes = array();
        if (isset($element['class'])) {
            $classes[] = $element['class'];
        }
        if (!empty($element['required'])) {
            $classes[] = 'required';
        }
        if (!empty($element['error'])) {
            $classes[] = 'error';
        }
        return implode(' ', $classes);
    }

    /**
     * Given an element, returns a string representing the basic attribute
     * list for the element.
     *
     * This EXCLUDES the "value" attribute, as various form elements set
     * their value in different ways.
     */
    public static function element_attributes($element, $exclude=array()) {
        static $attributes = array('accesskey', 'class', 'dir', 'id', 'lang', 'maxlength', 'name', 'size', 'style', 'tabindex');
        $elementattributes = array_diff($attributes, $exclude);
        $result = '';
        foreach ($elementattributes as $attribute) {
            if (isset($element[$attribute]) && $element[$attribute] !== '') {
                $result .= ' ' . $attribute . '="' . hsc($element[$attribute]) . '"';
            }
        }

        foreach (array_diff(array('disabled', 'readonly'), $exclude) as $attribute) {
            if (!empty($element[$attribute])) {
                $result .= " $attribute=\"$attribute\"";
            }
        }
        
        return $result;
    }


}


/**
 * This is separate so that child element types can nest other elements inside
 * them (like the fieldset element does for example.
 *
 * Data guaranteed to be available:
 *   - type
 *   - title
 */
function form_render_element($element, $form) {
    $function = 'form_render_' . $element['type'];
    if (!function_exists($function)) {
        // Attempt to include the relevant file
        @include('form/elements/' . $element['type'] . '.php');
        if (!function_exists($function)) {
            throw new FormException('No such form element: ' . $element['type']);
        }
    }

    $element['id']    = Form::make_id($element);
    $element['class'] = Form::make_class($element);

    $result = '';
    if (isset($element['prefix'])) {
        $result .= $element['prefix'];
    }

    $result .= '<div';
    // Set the class of the enclosing <div> to match that of the element
    if ($element['class']) {
        $result .= ' class="' . $element['class'] . '"';
    }
    // For debugging only
    if (isset($element['error'])) {
        $result .= ' style="color:red;"';
    }
    $result .= '>';

    if (isset($element['title']) && $element['type'] != 'fieldset') {
        $result .= '<label for="' . $element['id'] . '">' . hsc($element['title']) . '</label>';
    }

    // Build the actual form widget
    $result .= $function($element, $form);

    // Contextual help
    if (isset($element['help'])) {
        $result .= ' <span class="help" style="font-size: smaller;"><a href="#" title="' . hsc($element['help']) . '">?</a></span>';
    }

    // Description - optional description of the element, or other note that should be visible
    // on the form itself (without the user having to hover over contextual help 
    if (isset($element['description'])) {
        $result .= '<div class="description" style="font-size: smaller;"> ' . hsc($element['description']) . "</div>";
    }

    if (isset($element['error'])) {
        $result .= '<div class="errmsg" style="font-size: smaller;">' . hsc($element['error']) . '</div>';
    }

    $result .= '</div>';
    if (isset($element['suffix'])) {
        $result .= $element['suffix'];
    }
    $result .= "\n";
    return $result;
}



function form_render_select($element) {
    if (!empty($element['#multiple'])) {
        $element['#name'] .= '[]';
    }
    $result = '<select'
        . form_element_attributes($element)
        . (!empty($element['#multiple']) ? ' multiple="multiple"' : '')
        . ">\n";
    if (!is_array($element['#options']) || count($element['#options']) < 1) {
        $result .= "\t<option></option>\n";
        log_warn('Select elements should have at least one option');
    }

    if (empty($element['#multiple'])) {
        $values = array(form_get_value($element)); 
    }
    else {
        if (isset($element['#value'])) {
            $values = (array) $element['#value'];
        }
        else if (isset($_POST[$element['#name']])) {
            $values = (array) $_POST[$element['#name']];
        }
        else if (isset($element['#defaultvalue'])) {
            $values = (array) $element['#defaultvalue'];
        }
        else {
            $values = array();
        }
    }
    foreach ($element['#options'] as $key => $value) {
        if (in_array($key, $values)) {
            $selected = ' selected="selected"';
        }
        else {
            $selected = '';
        }
        $result .= "\t<option value=\"" . hsc($key) . "\"$selected>" . hsc($value) . "</option>\n";
    }

    $result .= "</select>\n";
    return $result;
}

function form_render_textarea($element) {
    $rows = $cols = $style = '';
    if (isset($element['#height'])) {
        $style .= 'height:' . $element['#height'] . ';';
        $rows   = (intval($element['#height'] > 0)) ? ceil(intval($element['#height']) / 10) : 1;
    }
    elseif (isset($element['#rows'])) {
        $rows = $element['#rows'];
    }
    else {
        log_warn('No value for rows or height specified for textarea ' . $element['#name']);
    }

    if (isset($element['#width'])) {
        $style .= 'width:' . $element['#width'] . ';';
        $cols   = (intval($element['#width'] > 0)) ? ceil(intval($element['#width']) / 10) : 1;
    }
    elseif (isset($element['#cols'])) {
        $cols = $element['#cols'];
    }
    else {
        log_warn('No value for cols or width specified for textarea ' . $element['#name']);
    }
    $element['#style'] = (isset($element['#style'])) ? $style . $element['#style'] : $style;
    return '<textarea'
        . (($rows) ? ' rows="' . $rows . '"' : '')
        . (($cols) ? ' cols="' . $cols . '"' : '')
        . form_element_attributes($element, array('maxlength', 'size'))
        . '>' . hsc(form_get_value($element)) . '</textarea>';
}

function form_render_file($element) {
    return '<input type="file"'
        . form_element_attributes($element) . '>';
}






function hsc ($text) { return htmlspecialchars($text, ENT_COMPAT, 'UTF-8'); }

?>

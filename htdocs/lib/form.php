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
 * @subpackage form
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
class FormException extends Exception {}

class Form {
    public static $formtabindex = 1;

    private $name = '';
    private $method = 'get';
    private $action = '';
    private $tabindex = 1;
    private $data = array();
    private $renderer = 'div';
    private $fileupload = false;
    private $iscancellable = true;

    public static function process($data) {
        $form = new Form($data);
        return $form->build();
    }

    /**
     * Sets the attributes of the form according to the passed data, performing
     * validation on the way. If the form is submitted, this checks and processes
     * the form.
     */
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

        $this->iscancellable = (isset($data['iscancellable']) && !$data['iscancellable']) ? false : true;

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
        $global = ($this->method == 'get') ? $_GET: $_POST;
        if (isset($global['form_' . $this->name] )) {
            // Check if the form has been cancelled
            if ($this->iscancellable) {
                foreach ($global as $key => $value) {
                    if (substr($key, 0, 7) == 'cancel_') {
                        $function = $this->name . '_' . $key;
                        log_dbg($function);
                        if (!function_exists($function)) {
                            throw new FormException('Form "' . $this->name . '" does not have a cancel function handler for "' . substr($key, 7) . '"');
                        }
                        $function();
                        $element = $this->get_element(substr($key, 7));
                        if (!isset($element['goto'])) {
                            throw new FormException('Cancel element "' . $element['name'] . '" has no page to go to');
                        }
                        redirect($element['goto']);
                        return;
                    }
                }
            }

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

    function get_name() {
        return $this->name;
    }

    function get_renderer() {
        return $this->renderer;
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
        $global = ($this->method == 'get') ? $_GET : $_POST;
        if (isset($element['value'])) {
            return $element['value'];
        }
        else if (isset($global[$element['name']]) && $element['type'] != 'submit') {
            return $global[$element['name']];
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
     * Returns the element with the given name. Throws a FormException if the
     * element cannot be found.
     *
     * @param string $name The name of the element to find
     * @return array       The element
     * @throws FormException If the element could not be found
     */
    public function get_element($name) {
        foreach ($this->get_elements() as $element) {
            if ($element['name'] == $name) {
                return $element;
            }
        }
        throw new FormException('Element "' . $name . '" cannot be found');
    }

    /**
     * Retrieves submitted values from POST for the elements of this form
     */
    private function get_submitted_values() {
        $result = array();
        foreach ($this->get_elements() as $element) {
            $elementnames[] = $element['name'];
        }

        $global = ($this->method == 'get') ? $_GET : $_POST;
        foreach ($elementnames as $name) {
            if (isset($global[$name])) {
                $result[$name] = $global[$name];
            }
            else {
                $result[$name] = null;
            }
        }
        return $result;
    }


    /**
     * Performs simple validation based off the definition array
     */
    private function validate($values) {
        foreach ($this->get_elements() as $element) {
            if (isset($element['rules']) && is_array($element['rules'])) {
                foreach ($element['rules'] as $rule => $data) {
                    if (!$this->get_error($element['name'])) {
                        // Get the rule
                        $function = 'form_rule_' . $rule;
                        if (!function_exists($function)) {
                            @include_once('form/rules/' . $rule . '.php');
                            if (!function_exists($function)) {
                                throw new FormException('No such form rule "' . $rule . '"');
                            }
                        }
                        if ($error = $function($values[$element['name']], $data)) {
                            $this->set_error($element['name'], $error);
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns whether a field has an error marked on it.
     *
     * @param string $name The name of the element to check
     * @return bool        Whether the element has an error
     * @throws FormException If the element could not be found
     */
    public function get_error($name) {
        $element = $this->get_element($name);
        return isset($element['error']);
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
     * Checks if there are errors on any of the form elements.
     *
     * @return bool whether there are errors with the form
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
        if (!empty($element['rules']['required'])) {
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
    // Make sure that the function to render the element type is available
    $function = 'form_render_' . $element['type'];
    if (!function_exists($function)) {
        // Attempt to include the relevant file
        @include('form/elements/' . $element['type'] . '.php');
        if (!function_exists($function)) {
            throw new FormException('No such form element: ' . $element['type']);
        }
    }

    // If the element is hidden, don't bother passing it to the renderer.
    if ($element['type'] == 'hidden') {
        return form_render_hidden($element, $form) . "\n";
    }

    // Work out the renderer function required and make sure it exists
    if ($renderer = $form->get_renderer()) {
        $rendererfunction = 'form_renderer_' . $renderer;
        if (!function_exists($rendererfunction)) {
            @include('form/renderers/' . $renderer . '.php');
            if (!function_exists($rendererfunction)) {
                throw new FormException('No such form renderer: "' . $renderer . '"');
            }
        }
    }
    else {
        throw new FormException('No form renderer specified for form "' . $form->get_name() . '"');
    }


    $element['id']    = Form::make_id($element);
    $element['class'] = Form::make_class($element);

    // Prepare the prefix and suffix
    $prefix = (isset($element['prefix'])) ? $element['prefix'] : '';
    $suffix = (isset($element['suffix'])) ? $element['suffix'] : '';

    return $prefix . $rendererfunction($function($element, $form), $element) . $suffix;
}


function hsc ($text) { return htmlspecialchars($text, ENT_COMPAT, 'UTF-8'); }

?>

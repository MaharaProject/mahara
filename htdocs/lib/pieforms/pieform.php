<?php
/**
 * This program is part of Pieforms
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
 * @package    pieform
 * @subpackage core
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

$GLOBALS['_PIEFORM_REGISTRY'] = array();

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
 * Please see https://eduforge.org/wiki/wiki/mahara/wiki?pagename=FormAPI for
 * more information on creating and using forms.
 *
 */
function pieform($data) {
    return Pieform::process($data);
    //
    // @todo stuff to do for forms:
    // 
    //  - more form element types (inc. types like autocomplete and date picker and wyswiyg)
    //  - support processing of data before validation occurs (e.g. trim(), strtoupper())
    //  - Basic validation is possible as there's a callback function for checking,
    //    but some helper functions could be written to make people's job validating
    //    stuff much easier (form_validate_email, form_validate_date etc).
    //  - Collapsible js for fieldsets
    //  - Grippie for textareas
    //  - javascript validation
    //  - handle multipage forms?
    //  - handle a tabbed interface type of form?
    //  
}

if (!function_exists('json_encode')) {
    function json_encode($data) {
        require_once 'JSON/JSON.php';
        $json = new Services_JSON();
        return $json->encode($data);
    }
}

/**
 * Pieform throws PieformExceptions, so you can catch them specifically
 */
class PieformException extends Exception {}

/**
 * Represents an HTML form. Forms created using this class have a lot of the
 * legwork for forms abstracted away.
 *
 * The form API makes it really easy to build complex HTML forms, simply by
 * building a hash describing your form, and defining one or two callback
 * functions.
 *
 * For more information on how the form API works, please see the documentation
 * at https://eduforge.org/wiki/wiki/mahara/wiki?pagename=FormAPI
 */
class Pieform {

    /**
     * Data for the form
     *
     * @var array
     * @todo move all of the member fields here into this field
     */
    private $data = array();

    /**
     * Maintains a tab index across all created forms, to make it easy for
     * people to forget about it and have it just work for all of their forms.
     *
     * @var int
     */
    public static $formtabindex = 1;

    /**
     * The form name. This is required.
     *
     * @var string
     */
    private $name = '';

    /**
     * The method that the form will be submitted by. Either 'get' or 'post'.
     *
     * @var string
     */
    private $method = 'get';

    /**
     * The URL that the form will be submitted to.
     *
     * @var string
     */
    private $action = '';

    /**
     * Whether the form should be validated. Forms that are not validated are
     * also not submitted. This is useful if you just want to draw a form, and
     * have no validation rules apply to it.
     */
    private $validate = true;

    /**
     * Whether the form should be checked for submission. Forms can have
     * validate on and submit off in order to validate submitted data, but to
     * not bother with the submit.
     *  
     * @var bool
     */
    private $submit = true;

    /**
     * Whether to submit the form via ajax
     *
     * @todo rename this probably, because AJAX GET is supported too
     *
     * @var bool
     */
    private $ajaxpost = false;

    /**
     * A callback to call before submitting the form via AJAX
     *
     * @var string
     */
    private $preajaxsubmitcallback = '';

    /**
     * A callback to call after submitting the form via AJAX, regardless of
     * the result of the submission
     *
     * @var string
     */
    private $postajaxsubmitcallback = '';

    /**
     * Name of a javascript function to call on successful ajax submission
     *
     * @var string
     */
    private $ajaxsuccessfunction = '';

    /**
     * Name of a javascript function to call on failed ajax submission
     *
     * @var string
     */
    private $ajaxfailurefunction = '';

    /**
     * The tab index for this particular form.
     *
     * @var int
     */
    private $tabindex = 1;

    /**
     * Directories to look for elements, renderers and rules
     *
     * @var array
     */
    private $configdirs = array();

    /**
     * Whether to autofocus fields in this form, and if so, optionally which
     * field to focus.
     *
     * @var mixed
     */
    private $autofocus = false;

    /**
     * The renderer used to build the HTML for the form that each element sits
     * in. See the form/renderer package to find out the allowed types.
     *
     * @var string
     */
    private $renderer = 'table';

    /**
     * The language used for form rule error messages.
     *
     * @var string
     */
    private $language = 'en.utf8';

    /**
     * Language strings for rules
     *
     * @var array
     */
    private $language_strings = array(
        'en.utf8' => array(
            'required'  => 'This field is required',
            'email'     => 'E-mail address is invalid',
            'maxlength' => 'This field must be at most %d characters long',
            'minlength' => 'This field must be at least %d characters long', 
            'integer'   => 'The field must be an integer',
            'validateoptions' => 'The option "%s" is invalid',
            'regex'     => 'This field is not in valid form'
        )
    );

    /**
     * Whether this form includes a file element. If so, the enctype attribute
     * for the form will be specified as "multipart/mixed" as required. This
     * is auto-detected by the Form class.
     *
     * @var bool
     */
    private $fileupload = false;

    /**
     * Whether the form has been submitted. Available through the
     * {@link is_submitted} method.
     *
     * @var bool
     */
    private $submitted = false;

    /**
     * Whether the form is cancellable or not - that is, whether sending a
     * request to cancel the form will be honoured or not. This is useful for
     * the transient login form, where it must pass on cancel requests from
     * other forms sometimes.
     *
     * @var bool
     */
    private $iscancellable = true;

    /**
     * Name of validate function
     *
     * @var string
     */ 
    private $validatefunction = '';

    /**
     * Name of submit function
     *
     * @var string
     */
    private $submitfunction = '';

    /**
     * Processes the form. Called by the {@link pieform} function. It simply
     * builds the form (processing it if it has been submitted), and returns
     * the HTML to display the form
     *
     * @param array $data The form description hash
     * @return string     The HTML representing the form
     */
    public static function process($data) {
        $form = new Pieform($data);
        return $form->build();
    }

    /**
     * Sets the attributes of the form according to the passed data, performing
     * validation on the way. If the form is submitted, this checks and processes
     * the form.
     *
     * @param array $data The form description hash
     */
    public function __construct($data) {
        $GLOBALS['_PIEFORM_REGISTRY'][] = $this;

        if (!isset($data['name']) || !preg_match('/^[a-z_][a-z0-9_]*$/', $data['name'])) {
            throw new PieformException('Forms must have a name, and that name must be valid (validity test: could you give a PHP function the name?)');
        }
        $this->name = $data['name'];

        // If the form has global configuration, get it now
        if (function_exists('pieform_configure')) {
            $formconfig = pieform_configure();
            $defaultelements = (isset($formconfig['elements'])) ? $formconfig['elements'] : array();
            foreach ($defaultelements as $name => $element) {
                if (!isset($data['elements'][$name])) {
                    $data['elements'][$name] = $element;
                }
            }
        }
        else {
            $formconfig = array();
        }

        // Assign defaults for the form
        $formdefaults = array(
            'method'    => 'get',
            'action'    => '',
            'ajaxpost'  => false,
            'preajaxsubmitcallback'  => '',
            'postajaxsubmitcallback' => '',
            'ajaxsuccessfunction'    => '',
            'ajaxfailurefunction'    => '',
            'configdirs' => array(),
            'autofocus'  => false,
            'language'   => 'en.utf8',
            'validate'   => true,
            'submit'     => true,
            'elements'   => array(),
            'submitfunction' => '',
            'validatefunction' => '',
        );
        $data = array_merge($formdefaults, $formconfig, $data);
        $this->data = $data;

        // Set the method - only get/post allowed
        $data['method'] = strtolower($data['method']);
        if ($data['method'] != 'post') {
            $data['method'] = 'get';
        }
        $this->method     = $data['method'];
        $this->action     = $data['action'];
        $this->validate   = $data['validate'];
        $this->submit     = $data['submit'];
        $this->configdirs = array_map(
            create_function('$a', 'return substr($a, -1) == "/" ? substr($a, 0, -1) : $a;'),
            (array) $data['configdirs']);
        $this->autofocus  = $data['autofocus'];
        $this->language   = $data['language'];
        
        if ($data['submitfunction']) {
            $this->submitfunction = $data['submitfunction'];
        }
        else {
            $this->submitfunction = $this->name . '_submit';
        }

        if ($data['validatefunction']) {
            $this->validatefunction = $data['validatefunction'];
        }
        else {
            $this->validatefunction = $this->name . '_validate';
        }

        if ($data['ajaxpost']) {
            $this->ajaxpost = true;
            $this->preajaxsubmitcallback  = self::validate_js_callback($data['preajaxsubmitcallback']);
            $this->postajaxsubmitcallback = self::validate_js_callback($data['postajaxsubmitcallback']);
            // @todo rename to *callback instead of *function for consistency
            $this->ajaxsuccessfunction    = self::validate_js_callback($data['ajaxsuccessfunction']);
            $this->ajaxfailurefunction    = self::validate_js_callback($data['ajaxfailurefunction']);
        }

        if (isset($data['renderer'])) {
            $this->renderer = $data['renderer'];
        }

        if (isset($data['tabindex'])) {
            $this->tabindex = intval($data['tabindex']);
        }
        else {
            $this->tabindex = self::$formtabindex++;
        }

        $this->iscancellable = (isset($data['iscancellable']) && !$data['iscancellable']) ? false : true;

        if (!is_array($data['elements']) || count($data['elements']) == 0) {
            throw new PieformException('Forms must have a list of elements');
        }

        // Remove elements to ignore
        foreach ($data['elements'] as $name => $element) {
            if (isset($element['type']) && $element['type'] == 'fieldset') {
                foreach ($element['elements'] as $subname => $subelement) {
                    if (!empty($subelement['ignore'])) {
                        unset ($data['elements'][$name]['elements'][$subname]);
                    }
                }
            }
            else {
                if (!empty($element['ignore'])) {
                    unset($data['elements'][$name]);
                }
            }
        }

        $this->elements = $data['elements'];

        // Set some attributes for all elements
        $autofocusadded = false;
        foreach ($this->elements as $name => &$element) {
            // The name can be in the element itself. This is compatibility for the perl version
            if (isset($element['name'])) {
                $name = $element['name'];
            }
            if (count($element) == 0) {
                throw new PieformException('An element in form "' . $this->name . '" has no data (' . $name . ')');
            }
            if (!isset($element['type'])) {
                $element['type'] = 'markup';
                if (!isset($element['value'])) {
                    throw new PieformException('The markup element "'
                        . $name . '" has no value');
                }
            }
            if (!isset($element['title'])) {
                $element['title'] = '';
            }
            if ($element['type'] == 'file') {
                $this->fileupload = true;
                if ($this->method == 'get') {
                    $this->method = 'post';
                    self::info("Your form '$this->name' had the method 'get' and also a file element - it has been converted to 'post'");
                }
            }
            if ($element['type'] == 'fieldset') {
                foreach ($element['elements'] as $subname => &$subelement) {
                    // The name can be in the element itself. This is compatibility for the perl version
                    if (isset($subelement['name'])) {
                        $subname = $subelement['name'];
                    }
                    if (count($subelement) == 0) {
                        throw new PieformException('An element in form "' . $this->name . '" has no data (' . $subname . ')');
                    }
                    if (!isset($subelement['type'])) {
                        $subelement['type'] = 'markup';
                        if (!isset($subelement['value'])) {
                            throw new PieformException('The markup element "'
                                . $name . '" has no value');
                        }
                    }
                    if (!isset($subelement['title'])) {
                        $subelement['title'] = '';
                    }
                    if ($subelement['type'] == 'file') {
                        $this->fileupload = true;
                        if ($this->method == 'get') {
                            $this->method = 'post';
                            self::info("Your form '$this->name' had the method 'get' and also a file element - it has been converted to 'post'");
                        }
                    }
                    if (!$autofocusadded && $this->autofocus === true) {
                        $subelement['autofocus'] = true;
                        $autofocusadded = true;
                    }
                    else if (!empty($this->autofocus) && $this->autofocus !== true
                        && $subname == $this->autofocus) {
                        $subelement['autofocus'] = true;
                    }
                    $subelement['name'] = $subname;
                    $subelement['tabindex'] = $this->tabindex;

                    // Let each element set and override attributes if necessary
                    if ($subelement['type'] != 'markup') {
                        // This function can be defined by the application using Pieforms,
                        // and applies to all elements of this type
                        $function = 'pieform_configure_' . $subelement['type'];
                        if (function_exists($function)) {
                            $subelement = $function($subelement);
                        }

                        // This function is defined by the plugin itself, to set fields on
                        // the element that need to be set but should not be set by the
                        // application
                        $function = 'pieform_render_' . $subelement['type'] . '_set_attributes';
                        $this->include_plugin('element',  $subelement['type']);
                        if (function_exists($function)) {
                            $subelement = $function($subelement);
                        }
                    }
                }
            }
            else {
                if (!$autofocusadded && $this->autofocus === true) {
                    $element['autofocus'] = true;
                    $autofocusadded = true;
                }
                elseif (!empty($this->autofocus) && $this->autofocus !== true
                    && $name == $this->autofocus) {
                    $element['autofocus'] = true;
                }
                $element['name'] = $name;
                $element['tabindex'] = $this->tabindex;
            }

            // Let each element set and override attributes if necessary
            if ($element['type'] != 'markup') {
                $function = 'pieform_configure_' . $element['type'];
                if (function_exists($function)) {
                    $element = $function($element);
                }

                $function = 'pieform_render_' . $element['type'] . '_set_attributes';
                $this->include_plugin('element',  $element['type']);
                if (function_exists($function)) {
                    $element = $function($element);
                }
            }
        }

        // Check if the form was submitted, and if so, validate and process it
        $global = ($this->method == 'get') ? $_GET: $_POST;
        if ($this->validate && isset($global['pieform_' . $this->name] )) {
            if ($this->submit) {
                $this->submitted = true;
                // Check if the form has been cancelled
                if ($this->iscancellable) {
                    foreach ($global as $key => $value) {
                        if (substr($key, 0, 7) == 'cancel_') {
                            // Check for and call the cancel function handler
                            // @todo<nigel>: it might be that this function could be optional
                            $function = $this->name . '_' . $key;
                            if (!function_exists($function)) {
                                throw new PieformException('Form "' . $this->name . '" does not have a cancel function handler for "' . substr($key, 7) . '"');
                            }
                            $function();
                            $element = $this->get_element(substr($key, 7));
                            if (!isset($element['goto'])) {
                                throw new PieformException('Cancel element "' . $element['name'] . '" has no page to go to');
                            }
                            // @todo what happens in the case of ajax post?
                            redirect($element['goto']);
                            return;
                        }
                    }
                }
            }

            // Get the values that were submitted
            $values = $this->get_submitted_values();
            // Perform general validation first
            $this->validate($values);
            // Then user specific validation if a function is available for that
            if (function_exists($this->validatefunction)) {
                $function = $this->validatefunction;
                $function($this, $values);
            }

            // Submit the form if things went OK
            if ($this->submit && !$this->has_errors()) {
                $submitted = false;
                foreach ($this->get_elements() as $element) {
                    // @todo Rename 'ajaxmessages' to 'submitelement'
                    if (!empty($element['ajaxmessages']) == true && isset($values[$element['name']])) {
                        $function = "{$this->name}_submit_{$element['name']}";
                        if (function_exists($function)) {
                            $function($values);
                            $submitted = true;
                            break;
                        }
                    }
                }
                if (!$submitted && function_exists($this->submitfunction)) {
                    $function = $this->submitfunction;
                    // Call the user defined function for processing a submit
                    // This function should really redirect/exit after it has
                    // finished processing the form.
                    // @todo maybe it should do just that...
                    $function($values);
                    // This will only work if I can make the login_submit function stuff work in login_validate
                    //if ($this->ajaxpost) {
                    //    $message = 'Your ' . $this->name . '_submit function should output some json and exit';
                    //}
                    //else {
                    //    $message = 'Your ' . $this->name . '_submit function should redirect when it is finished';
                    //}
                    //throw new PieformException($message);
                }
                else if (!$submitted) {
                    throw new PieformException('No function registered to handle form submission for form "' . $this->name . '"');
                }
            }

            // Auto focus the first element with an error if required
            if ($this->autofocus !== false) {
                $this->auto_focus_first_error();
            }
            
            // If the form has been submitted by ajax, return ajax
            if ($this->ajaxpost) {
                $errors = $this->get_errors();
                $json = array();
                foreach ($errors as $element) {
                    $json[$element['name']] = $element['error'];
                }
                echo json_encode(array('error' => 'local', 'message' => '@todo allow forms to specify a local error message', 'errors' => $json));
                exit;
            }
        }
    }

    /**
     * Returns a generic property. This can be used to retrieve any property
     * set in the form data array, so developers can pass in random stuff and
     * get access to it.
     *
     * @param string The key of the property to return
     * @return mixed
     */
    public function get_property($key) {
        return $this->data[$key];
    }

    /**
     * Returns the form name
     *
     * @return string
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Returns the form submission method
     *
     * @return string
     */
    public function get_method() {
        return $this->method;
    }

    /**
     * Is the form being submitted by ajax?
     *
     * @return bool
     */
    public function get_ajaxpost() {
        return $this->ajaxpost;
    }

    /**
     * Returns the renderer used on to render the form
     *
     * @return string
     */
    public function get_renderer() {
        return $this->renderer;
    }

    /**
     * Returns whether the form has been submitted
     *
     * @return bool
     */
    public function is_submitted() {
        return $this->submitted;
    }

    /**
     * Builds and returns the HTML for the form, respecting the chosen renderer.
     *
     * Note that the "action" attribute for the form tag are NOT HTML escaped
     * for you. This allows you to build your own URLs, should you require. On
     * the other hand, this means you must be careful about escaping the URL,
     * especially if it has data from an external source in it.
     *
     * @return string The form as HTML
     */
    public function build() {
        $result = '<form';
        foreach (array('name', 'method', 'action') as $attribute) {
            $result .= ' ' . $attribute . '="' . $this->{$attribute} . '"';
        }
        $result .= ' id="' . $this->name . '"';
        if ($this->fileupload) {
            $result .= ' enctype="multipart/form-data"';
        }
        $result .= ">\n";

        // @todo masks attempts in pieform_render_element, including the error handling there
        $this->include_plugin('renderer',  $this->renderer);
        
        // Form header
        $function = 'pieform_renderer_' . $this->renderer . '_header';
        if (function_exists($function)) {
            $result .= $function();
        }

        // Render each element
        foreach ($this->elements as $name => $elem) {
            if ($elem['type'] != 'hidden') {
                $result .= pieform_render_element($elem, $this);
            }
        }

        // Form footer
        $function = 'pieform_renderer_' . $this->renderer . '_footer';
        if (function_exists($function)) {
            $result .= $function();
        }

        // Hidden elements
        $this->include_plugin('element', 'hidden');
        foreach ($this->get_elements() as $element) {
            if ($element['type'] == 'hidden') {
                $result .= pieform_render_hidden($element, $this);
            }
        }
        $element = array(
            'type'  => 'hidden',
            'name'  => 'pieform_' . $this->name,
            'value' => ''
        );
        $result .= pieform_render_hidden($element, $this);
        $result .= "</form>\n";

        if ($this->ajaxpost) {
            $result .= '<script language="javascript" type="text/javascript">';
            $result .= $this->submit_js();
            $result .=  "</script>\n";
        }

        return $result;
    }

    /**
     * Given an element, gets the value for it from this form
     *
     * @param  array $element The element to get the value for
     * @return mixed          The element's value. <kbd>null</kbd> if no value
     *                        is available for the element.
     */
    public function get_value($element) {
        $function = 'pieform_get_value_' . $element['type'];
        // @todo for consistency, reverse parameter order - always a Form object first
        if (function_exists($function)) {
            return $function($element, $this);
        }
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
     * This flattens fieldsets, and ignores the actual fieldset elements
     *
     * @return array The elements of the form
     */ 
    public function get_elements() {
        $elements = array();
        foreach ($this->elements as $name => $element) {
            if ($element['type'] == 'fieldset') {
                foreach ($element['elements'] as $subelement) {
                    $elements[] = $subelement;
                }
            }
            else {
                $elements[] = $element;
            }
        }
        return $elements;
    }
    
    /**
     * Returns the element with the given name. Throws a PieformException if the
     * element cannot be found.
     *
     * Fieldset elements are ignored. This might change if a valid case for
     * needing them is found.
     *
     * @param  string $name     The name of the element to find
     * @return array            The element
     * @throws PieformException If the element could not be found
     */
    public function get_element($name) {
        foreach ($this->get_elements() as $element) {
            if ($element['name'] == $name) {
                return $element;
            }
        }
        throw new PieformException('Element "' . $name . '" cannot be found');
    }

    /**
     * Retrieves submitted values from POST for the elements of this form.
     *
     * This takes into account that some elements may not even have been set,
     * for example if they were check boxes that were not checked upon
     * submission.
     *
     * A value is returned for every element (except fieldsets of course). If
     * an element was not set, the value set is <kbd>null</kbd>.
     *
     * @return array The submitted values
     */
    private function get_submitted_values() {
        $result = array();
        $global = ($this->method == 'get') ? $_GET : $_POST;
        foreach ($this->get_elements() as $element) {
            if ($element['type'] != 'markup') {
                $result[$element['name']] = $this->get_value($element);
            }
        }
        return $result;
    }

    /**
     * Performs simple validation based off the definition array.
     *
     * Rules can be added to <kbd>pieform/rules/</kbd> directory, and then
     * re-used in the 'rules' index of each element in the form definition
     * hash.
     *
     * More complicated validation is possible by defining an optional
     * callback with the name {$form->name}_validate. See the documentation for
     * more information.
     *
     * @param array $values The submitted values from the form
     */
    private function validate($values) {
        foreach ($this->get_elements() as $element) {
            if (isset($element['rules']) && is_array($element['rules'])) {
                foreach ($element['rules'] as $rule => $data) {
                    if (!$this->get_error($element['name'])) {
                        // Get the rule
                        $function = 'pieform_rule_' . $rule;
                        if (!function_exists($function)) {
                            $this->include_plugin('rule', $rule);
                            if (!function_exists($function)) {
                                throw new PieformException('No such form rule "' . $rule . '"');
                            }
                        }
                        if ($error = $function($this, $values[$element['name']], $element, $data)) {
                            $this->set_error($element['name'], $error);
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns a js function to submit an ajax form 
     * Expects formname_message() to be defined by the renderer,
     * and formname_validate() to be defined.
     */
    private function submit_js() {
        // @todo nigel should disable all buttons on this form while the submit is happening
        $result = <<<EOF

connect($('{$this->name}'), 'onsubmit', function (e) {
    // eventually we should check input types for wysiwyg before doing this
    // Also should only save wysiwyg elements in the form, not all of them...
    if (typeof(tinyMCE) != 'undefined') { tinyMCE.triggerSave(); } 

EOF;
        if (!empty($this->preajaxsubmitcallback)) {
            $result .= "    $this->preajaxsubmitcallback();\n";
        }
        $result .= <<<EOF
    var data = {};

EOF;
        // Get values for each element from the form via the DOM
        foreach ($this->get_elements() as $element) {
            if ($element['type'] != 'markup') {
                $function = 'pieform_get_value_js_' . $element['type'];
                if (function_exists($function)) {
                    // @todo reverse parameter order for consistency, PieForm first
                    $result .= $function($element, $this);
                }
                else {
                    $result .= "    data['" . $element['name'] . "'] = document.forms['$this->name'].elements['{$element['name']}'].value;\n";
                }
                if (!empty($element['ajaxmessages'])) {
                    $messageelement = $element['name'];
                }
            }
        }

        if (!isset($messageelement)) {
            throw new PieformException('At least one submit-type element is required for AJAX forms');
        }

        // Add the hidden element for detecting form submission
        $result .= "    data['pieform_{$this->name}'] = '';\n";

        $action = ($this->action) ? $this->action : basename($_SERVER['PHP_SELF']);
        $method = ($this->get_method() == 'get') ? 'GET' : 'POST';
        $result .= <<<EOF
    var req = getXMLHttpRequest();
    req.open('{$method}', '{$action}');
    req.setRequestHeader('Content-type','application/x-www-form-urlencoded'); 
    var d = sendXMLHttpRequest(req,queryString(data));
    d.addCallbacks(
    function (result) {
        {$this->name}_remove_all_errors();
        var data = evalJSONRequest(result);
        if (data.error) {
            {$this->name}_message(data.message, 'error');
            for (error in data.errors) {
                {$this->name}_set_error(data.errors[error], error);
            }

EOF;
        
        if (!empty($this->ajaxfailurefunction)) {
            $result .= "            {$this->ajaxfailurefunction}(data);\n";
        }
        $result .= <<<EOF
        }
        else {
            {$this->name}_message(data.message, 'ok');

EOF;

        if (!empty($this->ajaxsuccessfunction)) {
            $result .= "            {$this->ajaxsuccessfunction}(data);\n";
        }

        $result .= "            {$this->name}_remove_all_errors();\n";
        $result .= "        }\n";
        if (!empty($this->postajaxsubmitcallback)) {
            $result .= "    $this->postajaxsubmitcallback();\n";
        }

        $strunknownerror =   $this->i18n('ajaxunknownerror');
        $strprocessingform = $this->i18n('processingform');
        $result .= <<<EOF
    },
    function() {
        {$this->name}_message('{$strunknownerror}', 'error');

EOF;
        if (!empty($this->postajaxsubmitcallback)) {
            $result .= "        $this->postajaxsubmitcallback();\n";
        }
        $result .= <<<EOF
    });
    {$this->name}_message('{$strprocessingform}', 'info');
    e.stop();
});

EOF;

        $js_messages_function = 'pieform_renderer_' . $this->renderer . '_messages_js';
        if (!function_exists($js_messages_function)) {
            $this->include_plugin('renderer', $this->renderer);
            if (!function_exists($js_messages_function)) {
                throw new PieformException('No renderer message function "' . $js_messages_function . '"');
            }
        }

        return $result . $js_messages_function($this->name, $messageelement);
    }

    /**
     * Returns whether a field has an error marked on it.
     *
     * This method should be used in the custom validation functions, to see if
     * there is an error on an element before checking for any more validation.
     *
     * Example:
     *
     * <code>
     * if (!$form->get_error('name') && /* condition {@*}) {
     *     $form->set_error('name', 'error message');
     * }
     * </code>
     *
     * @param  string $name  The name of the element to check
     * @return bool          Whether the element has an error
     * @throws PieformException If the element could not be found
     */
    public function get_error($name) {
        $element = $this->get_element($name);
        return isset($element['error']);
    }

    /**
     * Marks a field has having an error.
     *
     * This method should be used to set an error on an element in a custom
     * validation function, if one has occured.
     *
     * Note that for the Mahara project, your error messages must be passed
     * through {@link get_string} to internationalise them.
     *
     * @param string $name    The name of the element to set an error on
     * @param string $message The error message
     * @throws PieformException  If the element could not be found
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
        throw new PieformException('Element "' . $name . '" could not be found');
    }

    /**
     * Makes an ID for an element.
     *
     * Element IDs are used for <label>s, so use this method to ensure that
     * an element gets an ID.
     *
     * The element's existing 'id' and 'name' attributes are checked first. If
     * they are not specified, a random ID is synthesised
     *
     * @param array $element The element to make an ID for
     * @return string        The ID for the element
     */
    public static function make_id($element) {
        if (isset($element['id'])) {
            return self::hsc($element['id']);
        }
        if (isset($element['name'])) {
            return self::hsc($element['name']);
        }
        return substr(md5(mt_rand()), 0, 4);
    }

    /**
     * Makes a class for an element.
     *
     * Elements can have several classes set on them depending on their state.
     * The classes are useful for (among other things), styling elements
     * differently if they are in these states.
     *
     * Currently, the states an element can be in are 'required' and 'error'.
     *
     * @param array $element The element to make a class for
     * @return string        The class for an element
     */
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
        // Please make sure that 'autofocus' is the last class added in this
        // method. Otherwise, improve the logic for removing 'autofocus' from
        // the elemnt class string in pieform_render_element
        if (!empty($element['autofocus'])) {
            $classes[] = 'autofocus';
        }
        return implode(' ', $classes);
    }

    /**
     * Given an element, returns a string representing the basic attribute
     * list for the element.
     *
     * This EXCLUDES the "value" attribute, as various form elements set
     * their value in different ways.
     *
     * This allows each element to have most of the standard HTML attributes
     * that you can normally set on a form element.
     *
     * The attributes generated by this method will include (if set for the
     * element itself), are <kbd>accesskey, class, dir, id, lang, maxlength,
     * name, size, style</kbd> and <kbd>tabindex</kbd>.
     *
     * The <kbd>class</kbd> and <kbd>id</kbd> attributes are typically built
     * beforehand with {@link make_class} and {@link make_id} respectively.
     * The <kbd>maxlength</kbd> attribute is only set if the element has a
     * "maxlength" rule on it.
     *
     * @param array $element The element to make attributes for
     * @param array $exclude Any attributes to explicitly exclude from adding
     * @return string        The attributes for the element
     */
    public function element_attributes($element, $exclude=array()) {
        static $attributes = array('accesskey', 'class', 'dir', 'id', 'lang', 'name', 'onclick', 'size', 'style', 'tabindex');
        $elementattributes = array_diff($attributes, $exclude);
        $result = '';
        foreach ($elementattributes as $attribute) {
            if (isset($element[$attribute]) && $element[$attribute] !== '') {
                if ($attribute == 'id') {
                    $element[$attribute] = $this->name . '_' . $element[$attribute];
                }
                $result .= ' ' . $attribute . '="' . self::hsc($element[$attribute]) . '"';
            }
        }

        if (!in_array('maxlength', $exclude) && isset($element['rules']['maxlength'])) {
            $result .= ' maxlength="' . intval($element['rules']['maxlength']) . '"';
        }

        foreach (array_diff(array('disabled', 'readonly'), $exclude) as $attribute) {
            if (!empty($element[$attribute])) {
                $result .= " $attribute=\"$attribute\"";
            }
        }
        
        return $result;
    }

    /**
     * Checks if there are errors on any of the form elements.
     *
     * @return bool Whether there are errors with the form
     */
    public function has_errors() {
        foreach ($this->get_elements() as $element) {
            if (isset($element['error'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Includes a plugin file, checking any configured plugin directories.
     *
     * @param string $type The type of plugin to include: 'element', 'renderer' or 'rule'
     * @param string $name The name of the plugin to include
     * @throws PieformException If the given type or plugin could not be found
     */
    public function include_plugin($type, $name) {
        if (!in_array($type, array('element', 'renderer', 'rule'))) {
            throw new PieformException("The type \"$type\" is not allowed for an include plugin");
        }

        // Check the configured include paths if they are specified
        foreach ($this->configdirs as $directory) {
            $file = "$directory/{$type}s/$name.php";
            if (is_readable($file)) {
                include_once($file);
                return;
            }
        }

        // Check the default include path
        $file = dirname(__FILE__) . "/pieform/{$type}s/{$name}.php";
        if (is_readable($file)) {
            include_once($file);
            return;
        }

        throw new PieformException("Could not find $type \"$name\"");
    }

    /**
     * Return an internationalised string based on the passed input key
     *
     * Returns english by default.
     *
     * @param string $key The language key to look up
     * @return string     The internationalised string
     */
    public function i18n($key) {
        $function = 'pieform_' . $key . '_i18n';
        if (function_exists($function)) {
            return $function($this->language);
        }
        if (isset($this->language_strings[$this->language][$key])) {
            return $this->language_strings[$this->language][$key];
        }
        return '[[' . $key . ']]';
    }

    /**
     * HTML-escapes the given value
     *
     * @param string $text The text to escape
     * @return string      The text, HTML escaped
     */
    public static function hsc($text) {
        return htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
    }

    /**
     * Hook for giving information back to the developer
     *
     * @param string $message The message to give to the developer
     */
    public static function info($message) {
        $function = 'pieform_info';
        if (function_exists($function)) {
            $function($message);
        }
        else {
            trigger_error($message, E_USER_NOTICE);
        }
    }

    private static function validate_js_callback($name) {
        if ($name == '') {
            return '';
        }
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $name)) {
            throw new PieformException("'$name' is not a valid javascript callback name");
        }
        return $name;
    }

    /**
     * Returns elements with errors on them
     *
     * @return array An array of elements with errors on them, the empty array
     *               in the result of no errors.
     */
    private function get_errors() {
        $result = array();
        foreach ($this->get_elements() as $element) {
            if (isset($element['error'])) {
                $result[] = $element;
            }
        }
        return $result;
    }

    /**
     * Sets the 'autofocus' property on the first element encountered that has
     * an error on it
     */
    private function auto_focus_first_error() {
        foreach ($this->elements as &$element) {
            if ($element['type'] == 'fieldset') {
                foreach ($element['elements'] as &$subelement) {
                    if (isset($subelement['error'])) {
                        $subelement['autofocus'] = true;
                        return;
                    }
                    unset($subelement['autofocus']);
                }
            }
            else {
                if (isset($element['error'])) {
                    $element['autofocus'] = true;
                    return;
                }
                unset($element['autofocus']);
            }
        }
    }
}


/**
 * Renders an element, and returns the result.
 *
 * This function looks in <kbd>pieform/renderers</kbd> for available overall form
 * renderers, and in <kbd>pieform/elements</kbd> for renderers for each form
 * element.
 *
 * If any of the renderers are not available, this function will throw a
 * PieformException.
 *
 * {@internal This is separate so that child element types can nest other
 * elements inside them (like the fieldset element does for example).}}
 *
 * @param array    $element The element to render
 * @param Pieform  $form    The form to render the element for
 * @return string           The rendered element
 */
function pieform_render_element($element, Pieform $form) {
    // If the element is pure markup, don't pass it to the renderer
    if ($element['type'] == 'markup') {
        return $element['value'] . "\n";
    }

    // Make sure that the function to render the element type is available
    $function = 'pieform_render_' . $element['type'];

    // Work out the renderer function required and make sure it exists
    if ($renderer = $form->get_renderer()) {
        $rendererfunction = 'pieform_renderer_' . $renderer;
        if (!function_exists($rendererfunction)) {
            $form->include_plugin('pieform/renderers/' . $renderer . '.php');
            if (!function_exists($rendererfunction)) {
                throw new PieformException('No such form renderer: "' . $renderer . '"');
            }
        }
    }
    else {
        throw new PieformException('No form renderer specified for form "' . $form->get_name() . '"');
    }

    $element['id']    = Pieform::make_id($element);
    $element['class'] = Pieform::make_class($element);
    // @todo reverse order of parameters for consistency, a Form object first
    $builtelement = $function($element, $form);

    // Remove the 'autofocus' class, because we only want it on the form input
    // itself, not the wrapping HTML
    $element['class'] = preg_replace('/\s?autofocus/', '', $element['class']);

    return $rendererfunction($form, $builtelement, $element);
}

function pieform_get_headdata() {
    $htmlelements = array();
    foreach ($GLOBALS['_PIEFORM_REGISTRY'] as $form) {
        foreach ($form->get_elements() as $element) {
            $function = 'pieform_get_headdata_' . $element['type'];
            if (function_exists($function)) {
                $elems = $function($element);
                $htmlelements = array_merge($htmlelements, $elems);
            }
        }
    }

    return array_unique($htmlelements);
}

?>

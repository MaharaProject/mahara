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

/** The form was processed successfully */
define('PIEFORM_OK', 0);
/** The form failed processing/validating */
define('PIEFORM_ERR', -1);
/** A cancel button was pressed */
define('PIEFORM_CANCEL', -2);

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
 *     'action' => 'myscript.php',
 *     'method' => 'post',
 *     'elements' => array(
 *         // definition of elements in the form
 *     )
 * );
 *
 * $smarty->assign('myform', pieform($form));
 *
 * function myform_validate(Pieform $form, $values) {
 *     // perform validation agains form elements here
 *     // some types of validation are conveniently available already as
 *     // as part of the form definition hash
 * }
 *
 * function myform_submit(Pieform $form, $values) {
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
    //  - more form element types (inc. types like autocomplete and wyswiyg)
    //  - support processing of data before validation occurs (e.g. trim(), strtoupper())
    //  - Basic validation is possible as there's a callback function for checking,
    //    but some helper functions could be written to make people's job validating
    //    stuff much easier (form_validate_email, form_validate_date etc).
    //  - Collapsible js for fieldsets
    //  - Grippie for textareas
    //  - handle multipage forms?
    //  - handle a tabbed interface type of form?
    //  
}

if (!function_exists('json_encode')) {
    require_once 'JSON/JSON.php';
    function json_encode($data) {
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
 * Pieforms makes it really easy to build complex HTML forms, simply by
 * building a hash describing your form, and defining one or two callback
 * functions.
 *
 * For more information on how Pieforms works, please see the documentation
 * at https://eduforge.org/wiki/wiki/mahara/wiki?pagename=FormAPI
 */
class Pieform {

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
     * Data for the form
     *
     * @var array
     */
    private $data = array();

    /**
     * Whether this form includes a file element. If so, the enctype attribute
     * for the form will be specified as "multipart/mixed" as required. This
     * is auto-detected by the Pieform class.
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
            // The method used to submit the form, should always be 'get' or 'post'
            'method' => 'get',

            // The form target. The vast majority of the time this should be blank,
            // as the functions that handle the submit should be in the same script
            // as the form definition
            'action' => '',

            // The form elements
            'elements' => array(),

            // The form renderer (see the pieform/renderers directory)
            'renderer' => 'table',

            // Whether to validate the form. Non validated forms have none of the
            // validate, success or error callbacks called on them
            'validate' => true,

            // Whether to process the submission of this form. The form will still
            // be validated. Handy if the code handling the submission is elsewhere
            'submit' => true,

            // The PHP callback called to validate the form. Optional
            'validatecallback' => '',

            // The PHP callback called to process the submission of the form.
            // Required, unless a success function is provided for each submit
            // button in the form
            'successcallback' => '',

            // The PHP callback called if there is any validation error. Optional
            'errorcallback' => '',

            // Whether this form should submit to a hidden iframe and use DOM
            // manipulation to insert error messages (faster than a normal submit,
            // supported in less browsers. Most modern browsers should be fine)
            'jsform' => false,

            // The javascript function called before submission of a form
            // (regardless of whether the form is a jsform)
            'presubmitcallback' => '',

            // The javascript function called after submission of a form. As non-js
            // forms will trigger a page load on submit, this has no effect for them. 
            'postsubmitcallback' => '',

            // The javascript function called if the form submission was successful
            'jssuccesscallback' => '',

            // The javascript function called if the form submission was unsuccessful
            'jserrorcallback' => '',

            // The javascript function called if the form submission returned an
            // unknown error code
            'globaljserrorcallback' => '',

            // The message to pass back as a reason for the form submission failing
            // if the form is a jsform. This can be used by your application however
            // you choose.
            'jserrormessage' => '',

            // Whether this form can be cancelled, regardless of the presence of
            // 'cancel' buttons or form inputs mischeviously named as to behave
            // like cancel buttons
            'iscancellable' => true,

            // Whether Pieforms should die after calling a submit function. Generally
            // this is a good idea, as it forces the user to reply to the form
            // submission. However, there are occasions where you might want to let
            // it continue, so this behaviour can be turned off
            'dieaftersubmit' => true,

            // Whether to auto-focus either the first field (if the value is true,
            // or the named field (if the value is a string) when the form is
            // displayed. If this has any value other than false and the form has
            // been submitted with an error, the first field with an error will
            // be focussed.
            'autofocus'  => false,

            // The directories to search for additional elements, renderers and
            // rules
            'configdirs' => array(),

            // The language to use for any form strings, such as those found in
            // rules.
            'language'   => 'en.utf8',

            // Any overriding language strings for rules
            'rulei18n'   => array(),

            // The tabindex for the form (managed automatically by Pieforms)
            'tabindex'   => false,

            // Whether to add a class of the type of the element to each element
            'elementclasses' => false
        );
        $data = array_merge($formdefaults, $formconfig, $data);
        $this->data = $data;

        // Set the method - only get/post allowed
        $this->data['method'] = strtolower($data['method']);
        if ($this->data['method'] != 'post') {
            $this->data['method'] = 'get';
        }

        // Make sure that the javascript callbacks are valid
        if ($this->data['jsform']) {
            $this->validate_js_callbacks();
        }

        if (!$this->data['validatecallback']) {
            $this->data['validatecallback'] = $this->name . '_validate';
        }

        if (!$this->data['successcallback']) {
            $this->data['successcallback'] = $this->name . '_submit';
        }

        $this->data['configdirs'] = array_map(
            create_function('$a', 'return substr($a, -1) == "/" ? substr($a, 0, -1) : $a;'),
            (array) $this->data['configdirs']);


        if (empty($this->data['tabindex'])) {
            $this->data['tabindex'] = self::$formtabindex++;
        }

        if (!is_array($this->data['elements']) || count($this->data['elements']) == 0) {
            throw new PieformException('Forms must have a list of elements');
        }

        // Rename all the keys to have nice compliant names
        // @todo:
        //   - This isn't done for elements inside fieldsets
        //   - There's no easy way for other things do do all this preprocessing if they
        //     need. It should be a method so that other things (like multirecord)
        //     can use it.
        $elements = array();
        foreach ($this->data['elements'] as $name => $element) {
            $newname = preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
            if (isset($elements[$name])) {
                throw new PieformException('Element "' . $name . '" has a dangerous name that interferes with another element');
            }
            $elements[$newname] = $element;
        }
        $this->data['elements'] = $elements;

        // Remove elements to ignore
        foreach ($this->data['elements'] as $name => $element) {
            if (isset($element['type']) && $element['type'] == 'fieldset') {
                foreach ($element['elements'] as $subname => $subelement) {
                    if (!empty($subelement['ignore'])) {
                        unset ($this->data['elements'][$name]['elements'][$subname]);
                    }
                }
            }
            else {
                if (!empty($element['ignore'])) {
                    unset($this->data['elements'][$name]);
                }
            }
        }

        // Set some attributes for all elements
        $autofocusadded = false;
        foreach ($this->data['elements'] as $name => &$element) {
            // @todo re-check ordering of this section
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
                if ($this->data['method'] == 'get') {
                    $this->data['method'] = 'post';
                    self::info("Your form '$this->name' had the method 'get' and also a file element - it has been converted to 'post'");
                }
            }
            if ($element['type'] == 'fieldset') {
                $this->include_plugin('element', 'fieldset');
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

                    // Configure some basics for real elements
                    if ($subelement['type'] != 'markup') {
                        // This function can be defined by the application using Pieforms,
                        // and applies to all elements of this type
                        $function = 'pieform_element_' . $subelement['type'] . '_configure';
                        if (function_exists($function)) {
                            $subelement = $function($subelement);
                        }

                        // This function is defined by the plugin itself, to set fields on
                        // the element that need to be set but should not be set by the
                        // application
                        $function = 'pieform_element_' . $subelement['type'] . '_set_attributes';
                        $this->include_plugin('element',  $subelement['type']);
                        if (function_exists($function)) {
                            $subelement = $function($subelement);
                        }

                        // Add the autofocus flag to the element if required
                        if (!$autofocusadded && $this->data['autofocus'] === true && empty($element['nofocus'])) {
                            $subelement['autofocus'] = true;
                            $autofocusadded = true;
                        }
                        else if (!empty($this->data['autofocus']) && $this->data['autofocus'] !== true
                            && $subname == $this->data['autofocus']) {
                            $subelement['autofocus'] = true;
                        }

                        // All elements should have some kind of title
                        if (!isset($subelement['title'])) {
                            $subelement['title'] = '';
                        }

                        // Force the form method to post if there is a file to upload.
                        if ($subelement['type'] == 'file') {
                            $this->fileupload = true;
                            if ($this->data['method'] == 'get') {
                                $this->data['method'] = 'post';
                                self::info("Your form '$this->name' had the method 'get' and also a file element - it has been converted to 'post'");
                            }
                        }

                        // All elements inherit the form tabindex
                        $subelement['tabindex'] = $this->data['tabindex'];
                    }
                    $subelement['name'] = $subname;

                }
            }
            else {
                // Let each element set and override attributes if necessary
                if ($element['type'] != 'markup') {
                    $function = 'pieform_element_' . $element['type'] . '_configure';
                    if (function_exists($function)) {
                        $element = $function($element);
                    }

                    $function = 'pieform_element_' . $element['type'] . '_set_attributes';
                    $this->include_plugin('element',  $element['type']);
                    if (function_exists($function)) {
                        $element = $function($element);
                    }

                    // Add the autofocus flag to the element if required
                    if (!$autofocusadded && $this->data['autofocus'] === true && empty($element['nofocus'])) {
                        $element['autofocus'] = true;
                        $autofocusadded = true;
                    }
                    elseif (!empty($this->data['autofocus']) && $this->data['autofocus'] !== true
                        && $name == $this->data['autofocus']) {
                        $element['autofocus'] = true;
                    }

                    $element['tabindex'] = $this->data['tabindex'];
                }
                $element['name'] = $name;
            }

        }

        // Check if the form was submitted, and if so, validate and process it
        $global = ($this->data['method'] == 'get') ? $_GET: $_POST;
        if ($this->data['validate'] && isset($global['pieform_' . $this->name] )) {
            if ($this->data['submit']) {
                $this->submitted = true;
                // Check if the form has been cancelled
                if ($this->data['iscancellable']) {
                    foreach ($global as $key => $value) {
                        if (substr($key, 0, 7) == 'cancel_') {
                            // Check for and call the cancel function handler, if defined
                            $function = $this->name . '_' . $key;
                            if (function_exists($function)) {
                                $function($this);
                            }

                            // Redirect the user to where they should go, if the cancel handler didn't already
                            $element = $this->get_element(substr($key, 7));
                            if (!isset($element['goto'])) {
                                throw new PieformException('Cancel element "' . $element['name'] . '" has no page to go to');
                            }
                            if ($this->data['jsform']) {
                                $this->json_reply(PIEFORM_CANCEL, $element['goto']);
                            }
                            header('HTTP/1.1 303 See Other');
                            header('Location:' . $element['goto']);
                            exit;
                        }
                    }
                }
            }

            // Get the values that were submitted
            $values = $this->get_submitted_values();
            // Perform general validation first
            $this->validate($values);

            // Submit the form if things went OK
            if ($this->data['submit'] && !$this->has_errors()) {
                $submitted = false;
                foreach ($this->get_elements() as $element) {
                    if (!empty($element['submitelement']) && isset($global[$element['name']])) {
                        $function = "{$this->name}_submit_{$element['name']}";
                        if (function_exists($function)) {
                            $function($this, $values);
                            $submitted = true;
                            break;
                        }
                    }
                }
                $function = $this->data['successcallback'];
                if (!$submitted && is_callable($function)) {
                    // Call the user defined function for processing a submit
                    // This function should really redirect/exit after it has
                    // finished processing the form.
                    // @todo maybe it should do just that...
                    call_user_func_array($function, array($this, $values));
                    if ($this->data['dieaftersubmit']) {
                        // This will only work if I can make the login_submit function stuff work in login_validate
                        if ($this->data['jsform']) {
                            $message = 'Your ' . $this->name . '_submit function should use $form->json_reply to send a response';
                        }
                        else {
                            $message = 'Your ' . $this->name . '_submit function should redirect or exit when it is done';
                        }
                        throw new PieformException($message);
                    }
                    else {
                        // Successful submission, and the user doesn't care about replying, so...
                        return;
                    }
                }
                else if (!$submitted) {
                    throw new PieformException('No function registered to handle form submission for form "' . $this->name . '"');
                }
            }

            // Auto focus the first element with an error if required
            if ($this->data['autofocus'] !== false) {
                $this->auto_focus_first_error();
            }

            // Call the user-defined PHP error function, if it exists
            $function = $this->data['errorcallback'];
            if (is_callable($function)) {
                call_user_func_array($function, array($this));
            }
            
            // If the form has been submitted by javascript, return json
            if ($this->data['jsform']) {
                $errors = $this->get_errors();
                $json = array();
                foreach ($errors as $element) {
                    $json[$element['name']] = $element['error'];
                }
                $message = $this->get_property('jserrormessage');
                $this->json_reply(PIEFORM_ERR, array('message' => $message, 'errors' => $json));
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
     * Returns whether the form has been submitted
     *
     * @return bool
     */
    public function is_submitted() {
        return $this->submitted;
    }

    /**
     * Returns the HTML for the <form...> tag
     *
     * @return string
     */
    public function get_form_tag() {
        $result = '<form class="pieform"';
        foreach (array('name', 'method', 'action') as $attribute) {
            $result .= ' ' . $attribute . '="' . $this->data[$attribute] . '"';
        }
        $result .= ' id="' . $this->name . '"';
        if ($this->fileupload) {
            $result .= ' enctype="multipart/form-data"';
        }
        $result .= '>';
        return $result;
    }

    /**
     * Builds and returns the HTML for the form, respecting the chosen renderer.
     *
     * Note that the "action" attribute for the form tag are NOT HTML escaped
     * for you. This allows you to build your own URLs, should you require. On
     * the other hand, this means you must be careful about escaping the URL,
     * especially if it has data from an external source in it.
     *
     * @param boolean Whether to include the <form...></form> tags in the output
     * @return string The form as HTML
     */
    public function build($outputformtags=true) {
        $result = '';
        if ($outputformtags) {
            $result = $this->get_form_tag() . "\n";
        }

        $this->include_plugin('renderer',  $this->data['renderer']);
        
        // Form header
        $function = 'pieform_renderer_' . $this->data['renderer'] . '_header';
        if (function_exists($function)) {
            $result .= $function();
        }

        // Render each element
        foreach ($this->data['elements'] as $name => $elem) {
            if ($elem['type'] != 'hidden') {
                $result .= pieform_render_element($this, $elem);
            }
        }

        // Form footer
        $function = 'pieform_renderer_' . $this->data['renderer'] . '_footer';
        if (function_exists($function)) {
            $result .= $function();
        }

        // Hidden elements
        $this->include_plugin('element', 'hidden');
        foreach ($this->get_elements() as $element) {
            if ($element['type'] == 'hidden') {
                $result .= pieform_element_hidden($element, $this);
            }
        }
        $element = array(
            'type'  => 'hidden',
            'name'  => 'pieform_' . $this->name,
            'value' => ''
        );
        $result .= pieform_element_hidden($element, $this);
        if ($outputformtags) {
            $result .= "</form>\n";
        }

        if ($this->data['jsform'] || $this->data['presubmitcallback']) {
            $result .= '<script type="text/javascript">';
            $result .= "\n" . $this->whichbutton_js();
        }
        if ($this->data['jsform']) {
            $result .= $this->submit_js();
        }
        else if ($this->data['presubmitcallback']) {
            $result .= 'connect(\'' . $this->name . '\', \'onsubmit\', '
                . 'function() { ' . $this->data['presubmitcallback'] . "('{$this->name}', {$this->name}_btn); });";
        }
        if ($this->data['jsform'] || $this->data['presubmitcallback']) {
            $result .=  "\n</script>\n";
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
        $function = 'pieform_element_' . $element['type'] . '_get_value';
        if (function_exists($function)) {
            return $function($this, $element);
        }
        $global = ($this->data['method'] == 'get') ? $_GET : $_POST;
        // If the element is a submit element and has its value in the request, return it
        // Otherwise, we don't return the value if the form has been submitted, as they
        // aren't normally returned using a standard form.
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
        foreach ($this->data['elements'] as $name => $element) {
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
     * Retrieves submitted values from the request for the elements of this form.
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
        $global = ($this->data['method'] == 'get') ? $_GET : $_POST;
        foreach ($this->get_elements() as $element) {
            if ($element['type'] != 'markup') {
                if (
                    (empty($element['submitelement']) && empty($element['cancelelement'])) ||
                    (
                        (!empty($element['submitelement']) || !empty($element['cancelelement']))
                        && isset($global[$element['name']])
                    )
                ) {
                    $result[$element['name']] = $this->get_value($element);
                }
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
        // Call the overall validation function if it is available
        if (function_exists('pieform_validate')) {
            pieform_validate($this, $values);
        }

        // Perform rule validation
        foreach ($this->get_elements() as $element) {
            if (isset($element['rules']) && is_array($element['rules'])) {
                foreach ($element['rules'] as $rule => $data) {
                    if (!$this->get_error($element['name'])) {
                        // See if this element has a function that describes
                        // how this rule should apply to it
                        $function = 'pieform_element_' . $element['type'] . '_rule_' . $rule;
                        if (!function_exists($function)) {
                            // Try instead the default rule function
                            $function = 'pieform_rule_' . $rule;
                            if (!function_exists($function)) {
                                $this->include_plugin('rule', $rule);
                                if (!function_exists($function)) {
                                    throw new PieformException('No such form rule "' . $rule . '"');
                                }
                            }
                        }
                        if ($error = $function($this, $values[$element['name']], $element, $data)) {
                            $this->set_error($element['name'], $error);
                        }
                    }
                }
            }
        }

        // Then user specific validation if a function is available for that
        $function = $this->data['validatecallback'];
        if (is_callable($function)) {
            call_user_func_array($function, array($this, $values));
        }
    }

    private function whichbutton_js() {
        $result = "var {$this->name}_btn = null;\n";

        $connecteventadded = false;
        foreach ($this->get_elements() as $element) {
            if (!empty($element['submitelement'])) {
                if (!$connecteventadded) {
                    $result .= "addLoadEvent(function() {\n";
                    $connecteventadded = true;
                }
                if (!empty($element['cancelelement'])) {
                    $cancelstr = 'cancel_';
                }
                else {
                    $cancelstr = '';
                }
                $result .= "    connect($('{$cancelstr}{$this->name}_{$element['name']}'), 'onclick', function() { {$this->name}_btn = '{$cancelstr}{$this->name}_{$element['name']}'; });\n";
            }
        }
        if ($connecteventadded) {
            $result .= "});\n";
        }

        return $result;
    }

    /**
     * Builds the javascript for submitting the form. Note that the iframe is
     * not hidden with display: none, as safari/konqueror/ns6 ignore things with
     * display: none. Positioning it absolute and 'hidden' has the same effect
     * without the breakage.
     */
    private function submit_js() {
        $strprocessingform = get_string('processingform');

        $result = <<<EOF
connect($('{$this->name}'), 'onsubmit', function(e) {
    if (typeof(tinyMCE) != 'undefined') { tinyMCE.triggerSave(); }

EOF;
        if (!empty($this->data['presubmitcallback'])) {
            $result .= "    {$this->data['presubmitcallback']}('{$this->name}', {$this->name}_btn);\n";
        }
        $result .= <<<EOF

    var iframe = $('{$this->name}_iframe');
    $('{$this->name}').target = '{$this->name}_iframe';
    if (!iframe) {
        iframe = createDOM('iframe', {
            'name': '{$this->name}_iframe',
            'id'  : '{$this->name}_iframe',
            'style': 'position: absolute; visibility: hidden;'
        });
        insertSiblingNodesAfter($('{$this->name}'), iframe);

        window.pieformHandler_{$this->name} = function(data) {

EOF;
        if (isset($this->data['processingstopcallback'])) {
            $result .= "            {$this->data['processingstopcallback']}('{$this->name}', {$this->name}_btn);\n";
        }

        $result .= <<<EOF
            evalJSONRequest(data);
            if (data.returnCode == 0) {
                {$this->name}_remove_all_errors();
                // The request completed successfully

EOF;
        if (!empty($this->data['jssuccesscallback'])) {
            $result .= "                {$this->data['jssuccesscallback']}('{$this->name}', data);\n";
        }

        $result .= <<<EOF
            }
            else {
                if (data.returnCode == -2) {
                    window.location = data.message;
                    return;
                }
                    
                {$this->name}_remove_all_errors();
                if (data.message.errors) {
                    for (error in data.message.errors) {
                        {$this->name}_set_error(data.message.errors[error], error);
                    }
                    // @todo only output when fieldsets are present
                    forEach(getElementsByTagAndClassName('fieldset', 'collapsed', '{$this->name}'), function(fieldset) {
                        if (getFirstElementByTagAndClassName(null, 'error', fieldset)) {
                            removeElementClass(fieldset, 'collapsed');
                        }
                    });
                }

                if (data.returnCode == -1) {

EOF;
        if (!empty($this->data['jserrorcallback'])) {
            $result .= "                    {$this->data['jserrorcallback']}('{$this->name}', data);\n";
        }
        $result .= <<<EOF
                }
                else {

EOF;
        if (!empty($this->data['globaljserrorcallback'])) {
            $result .= "                    {$this->data['globaljserrorcallback']}('{$this->name}', data);\n";
        }
        else {
            $result .= "                    alert('Developer: got error code ' + data.returnCode
                    + ', either fix your form to not use this code or define a global js error handler');\n";
        }
        $result .= <<<EOF
                }
            }

EOF;
        if (!empty($this->data['postsubmitcallback'])) {
            $result .= "            {$this->data['postsubmitcallback']}('{$this->name}', {$this->name}_btn);\n";
        }

        $result .= <<<EOF
            {$this->name}_btn = null;
        }
    }

EOF;
        $result .= "});\n\n";
        $function = 'pieform_renderer_' . $this->data['renderer'] . '_get_js';
        if (!function_exists($function)) {
            throw new PieformException('No renderer message function "' . $function . '"');
        }

        return $result . $function($this->name);
    }
    
    public function json_reply($returncode, $message=null) {
        $data = array(
            'returnCode' => intval($returncode),
            'message'    => $message
        );
        $result = json_encode($data);

        echo <<<EOF
<html><head><script type="text/javascript">function sendResult() { parent.pieformHandler_{$this->name}($result); }</script></head><body onload="sendResult(); "></body></html>
EOF;
        exit;
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
        foreach ($this->data['elements'] as &$element) {
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
    public function make_id($element) {
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
    public function make_class($element) {
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
        if ($this->data['elementclasses']) {
            $classes[] = $element['type'];
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
        foreach ($this->data['configdirs'] as $directory) {
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
     * @param string $plugin     The type of plugin (element, renderer, rule)
     * @param string $pluginname The name of the plugin to get the language
     *                           strings for
     * @param string $key        The language key to look up
     * @param array  $element    The element to get the string for. Elements
     *                           can specify there own i18n strings for rules
     * @return string            The internationalised string
     */
    public function i18n($plugin, $pluginname, $key, $element) {
        if (!in_array($plugin, array('element', 'renderer', 'rule'))) {
            throw new PieformException("Invalid plugin name '$plugin'");
        }

        // Check the element itself for the language string
        if ($plugin == 'rule' && isset($element['rulei18n'][$key])) {
            return $element['rulei18n'][$key];
        }

        // Check to see if a default was configured for the form
        if ($plugin == 'rule' && isset($this->data['rulei18n'][$key])) {
            return $this->data['rulei18n'][$key];
        }

        // Fall back to the default string
        $function = 'pieform_' . $plugin . '_' . $pluginname . '_i18n';
        if (function_exists($function)) {
            $strings = $function();
            return $strings[$this->data['language']][$key];
        }

        // We don't recognise this string
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

    /**
     * Makes sure that the javascript callbacks for this form are valid javascript
     * function names.
     */
    private function validate_js_callbacks() {
        foreach (array('presubmitcallback', 'postsubmitcallback', 'jssuccesscallback',
            'jserrorcallback', 'globaljserrorcallback') as $callback) {
            if ($this->data[$callback] != '' && !preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $this->data[$callback])) {
                throw new PieformException("'{$this->data[$callback]}' is not a valid javascript callback name for callback '$callback'");
            }
        }
    }

    /**
     * Returns elements with errors on them
     *
     * @return array An array of elements with errors on them, the empty array
     *               in the result of no errors.
     */
    public function get_errors() {
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
        foreach ($this->data['elements'] as &$element) {
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
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The rendered element
 */
function pieform_render_element(Pieform $form, $element) {
    // If the element is pure markup, don't pass it to the renderer
    if ($element['type'] == 'markup') {
        return $element['value'] . "\n";
    }

    // Make sure that the function to render the element type is available
    $function = 'pieform_element_' . $element['type'];

    $rendererfunction = 'pieform_renderer_' . $form->get_property('renderer');
    if (!function_exists($rendererfunction)) {
        throw new PieformException('No such form renderer function: "' . $rendererfunction . '"');
    }

    $element['id']    = $form->make_id($element);
    $element['class'] = $form->make_class($element);
    $builtelement = $function($form, $element);

    // Remove the 'autofocus' class, because we only want it on the form input
    // itself, not the wrapping HTML
    $element['class'] = preg_replace('/\s?autofocus/', '', $element['class']);

    return $rendererfunction($form, $builtelement, $element);
}

function pieform_get_headdata() {
    $htmlelements = array();
    foreach ($GLOBALS['_PIEFORM_REGISTRY'] as $form) {
        foreach ($form->get_elements() as $element) {
            $function = 'pieform_element_' . $element['type'] . '_get_headdata';
            if (function_exists($function)) {
                $elems = $function($element);
                $htmlelements = array_merge($htmlelements, $elems);
            }
        }
    }

    return array_unique($htmlelements);
}

?>
